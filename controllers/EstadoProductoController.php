<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\data\SqlDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class EstadoProductoController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static function (): bool {
                            $user = Yii::$app->user->identity;
                            return $user && ($user->isGerente() || $user->isEncargado());
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'registrar-conteo' => ['post'],
                    'ajustar-stock'    => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $db = Yii::$app->db;

        $buscar = trim(Yii::$app->request->get('buscar', ''));

        $where = $buscar !== '' ? "WHERE p.nombre LIKE :buscar" : '';
        $params = $buscar !== '' ? [':buscar' => '%' . $buscar . '%'] : [];

        $totalCount = (int)$db->createCommand(
            "SELECT COUNT(*) FROM {{%producto}} p $where", $params
        )->queryScalar();

        $sql = <<<SQL
SELECT
  p.id,
  p.codigo,
  p.nombre AS producto,
  c.nombre AS categoria,
  p.stock AS cantidad,
  p.stock_minimo,
  COALESCE(cf.total_fisico, 0)        AS total_fisico,
  cf.fecha                            AS ultima_actualizacion,
  cf.diferencia                       AS diferencia,
  COALESCE(vs.entregados, 0)          AS entregados,
  COALESCE(vs.enviados, 0)            AS enviados,
  COALESCE(vs.confirmados, 0)         AS confirmados,
  COALESCE(vs.pendiente, 0)           AS pendiente
FROM {{%producto}} p
JOIN {{%categoria}} c ON c.id = p.categoria_id
LEFT JOIN (
  SELECT cf1.producto_id,
         cf1.cantidad_fisica AS total_fisico,
         cf1.diferencia,
         cf1.fecha
  FROM {{%conteo_fisico}} cf1
  INNER JOIN (
      SELECT producto_id, MAX(id) AS max_id
      FROM {{%conteo_fisico}}
      GROUP BY producto_id
  ) latest ON latest.producto_id = cf1.producto_id AND latest.max_id = cf1.id
) cf ON cf.producto_id = p.id
LEFT JOIN (
  SELECT dv.producto_id,
    SUM(CASE WHEN ev.nombre = 'Entregadas'    THEN dv.cantidad ELSE 0 END) AS entregados,
    SUM(CASE WHEN ev.nombre = 'Enviadas'      THEN dv.cantidad ELSE 0 END) AS enviados,
    SUM(CASE WHEN ev.nombre = 'Para Entregar' THEN dv.cantidad ELSE 0 END) AS pendiente,
    SUM(CASE WHEN ep.nombre = 'Pagado'        THEN dv.cantidad ELSE 0 END) AS confirmados
  FROM {{%detalle_venta}} dv
  JOIN {{%venta}} v ON v.id = dv.venta_id
  JOIN {{%estado_venta}} ev ON ev.id = v.estado_venta_id
  JOIN {{%estado_pago}} ep ON ep.id = v.estado_pago_id
  GROUP BY dv.producto_id
) vs ON vs.producto_id = p.id
$where
ORDER BY p.id DESC
SQL;

        $dataProvider = new SqlDataProvider([
            'sql'        => $sql,
            'params'     => $params,
            'totalCount' => $totalCount,
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'buscar'       => $buscar,
        ]);
    }

    /**
     * Registra el conteo físico de inventario para un producto.
     * Si la diferencia supera el 5% (positivo o negativo) marca alerta
     * pero no ajusta automáticamente el stock.
     */
    public function actionRegistrarConteo(int $id): \yii\web\Response
    {
        $db = Yii::$app->db;

        $producto = $db->createCommand(
            'SELECT id, stock FROM {{%producto}} WHERE id = :id',
            [':id' => $id]
        )->queryOne();

        if (!$producto) {
            throw new NotFoundHttpException('Producto no encontrado.');
        }

        $cantidad = (int)Yii::$app->request->post('cantidad_fisica', -1);
        $nota     = trim((string)Yii::$app->request->post('nota', ''));

        if ($cantidad < 0) {
            Yii::$app->session->setFlash('error', 'Cantidad física inválida.');
            return $this->redirect(['index']);
        }

        $sistema   = (int)$producto['stock'];
        $diferencia = $cantidad - $sistema;

        $db->createCommand()->insert('{{%conteo_fisico}}', [
            'producto_id'     => (int)$producto['id'],
            'cantidad_fisica' => $cantidad,
            'cantidad_sistema'=> $sistema,
            'diferencia'      => $diferencia,
            'ajustado'        => 0,
            'nota'            => $nota !== '' ? $nota : null,
            'fecha'           => date('Y-m-d H:i:s'),
            'created_by'      => Yii::$app->user->id,
        ])->execute();

        $porc = $sistema > 0 ? abs($diferencia) * 100.0 / $sistema : ($diferencia !== 0 ? 100.0 : 0.0);
        if ($porc > 5.0) {
            Yii::$app->session->setFlash(
                'warning',
                "⚠ Diferencia significativa detectada: {$diferencia} unidades ("
                . number_format($porc, 1) . "%). Revise el inventario."
            );
        } else {
            Yii::$app->session->setFlash(
                'success',
                "Conteo registrado. Diferencia: {$diferencia} unidades."
            );
        }

        return $this->redirect(['index']);
    }

    /**
     * Ajusta el stock del producto según el último conteo físico registrado.
     */
    public function actionAjustarStock(int $id): \yii\web\Response
    {
        $db = Yii::$app->db;

        $ultimo = $db->createCommand(
            'SELECT id, cantidad_fisica, ajustado FROM {{%conteo_fisico}}
             WHERE producto_id = :pid ORDER BY id DESC LIMIT 1',
            [':pid' => $id]
        )->queryOne();

        if (!$ultimo) {
            Yii::$app->session->setFlash('error', 'No hay conteos físicos registrados para este producto.');
            return $this->redirect(['index']);
        }

        if ((int)$ultimo['ajustado'] === 1) {
            Yii::$app->session->setFlash('info', 'Este conteo ya fue ajustado al sistema.');
            return $this->redirect(['index']);
        }

        $tx = $db->beginTransaction();
        try {
            $db->createCommand()->update(
                '{{%producto}}',
                ['stock' => (int)$ultimo['cantidad_fisica'], 'updated_at' => date('Y-m-d H:i:s')],
                ['id' => $id]
            )->execute();

            $db->createCommand()->update(
                '{{%conteo_fisico}}',
                ['ajustado' => 1],
                ['id' => (int)$ultimo['id']]
            )->execute();

            $tx->commit();
            Yii::$app->session->setFlash('success', "Stock ajustado a {$ultimo['cantidad_fisica']} unidades según conteo físico.");
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'Error al ajustar stock: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }
}
