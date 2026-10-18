<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Almacen;
use app\models\Categoria;
use app\models\Lote;
use app\models\Producto;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class LoteController extends Controller
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
        ];
    }

    /**
     * Listado general de lotes activos con selector de producto.
     */
    public function actionIndex(): string
    {
        $productoId = (int)Yii::$app->request->get('producto_id', 0);

        $productos = ArrayHelper::map(
            Producto::find()->orderBy(['nombre' => SORT_ASC])->all(),
            'id',
            static fn(Producto $p): string => ($p->codigo ?? 'BG-P' . $p->id) . ' | ' . $p->nombre
        );

        $selectedProduct = $productoId ? Producto::findOne($productoId) : null;

        // Build movement history (ingresos + salidas) for selected product
        $movimientos = [];
        $saldoInicial = 0;

        if ($productoId) {
            $movimientos = $this->buildMovimientos($productoId);
        }

        return $this->render('index', [
            'productos'       => $productos,
            'productoId'      => $productoId,
            'selectedProduct' => $selectedProduct,
            'movimientos'     => $movimientos,
            'saldoInicial'    => $saldoInicial,
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────
    /**
     * Pantalla de Rotación de Inventario: gestión de lotes con filtros.
     */
    public function actionLotes(): string
    {
        $req = Yii::$app->request;
        $productoId  = (int)$req->get('producto_id', 0);
        $categoriaId = (int)$req->get('categoria_id', 0);
        $almacenId   = (int)$req->get('almacen_id', 0);
        $estado      = $req->get('estado', '1');     // '1' activo, '0' agotado, 'all' todos
        $desde       = (string)$req->get('desde', '');
        $hasta       = (string)$req->get('hasta', '');

        $query = Lote::find()
            ->alias('l')
            ->joinWith(['producto p', 'almacen a'])
            ->orderBy(['l.fecha_ingreso' => SORT_DESC, 'l.id' => SORT_DESC]);

        if ($productoId > 0) $query->andWhere(['l.producto_id' => $productoId]);
        if ($categoriaId > 0) $query->andWhere(['p.categoria_id' => $categoriaId]);
        if ($almacenId > 0)  $query->andWhere(['l.almacen_id' => $almacenId]);
        if ($estado === '1' || $estado === '0') $query->andWhere(['l.estado' => (int)$estado]);
        if ($desde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde))
            $query->andWhere(['>=', 'l.fecha_ingreso', $desde . ' 00:00:00']);
        if ($hasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))
            $query->andWhere(['<=', 'l.fecha_ingreso', $hasta . ' 23:59:59']);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 25],
            'sort'       => false,
        ]);

        // ── KPIs globales (sin paginación) ───────────────────────────────────
        $kpiQuery = clone $query;
        $kpiQuery->offset(null)->limit(null);
        $allLotes = $kpiQuery->all();
        $kpiLotes    = count($allLotes);
        $kpiUnidades = 0;
        $kpiValor    = 0.0;
        foreach ($allLotes as $l) {
            $kpiUnidades += (int)$l->cantidad_disponible;
            $kpiValor    += (float)$l->precio_compra_unitario * (int)$l->cantidad_disponible;
        }

        // ── Última venta por producto_id ─────────────────────────────────────
        $ultimaVentaRows = (new \yii\db\Query())
            ->select(['dv.producto_id', 'ultima' => 'MAX(v.fecha_pedido)'])
            ->from(['dv' => '{{%detalle_venta}}'])
            ->innerJoin(['v' => '{{%venta}}'], 'v.id = dv.venta_id AND v.estado = 1')
            ->groupBy('dv.producto_id')
            ->all();
        $ultimaVenta = [];
        foreach ($ultimaVentaRows as $row) {
            $ultimaVenta[(int)$row['producto_id']] = $row['ultima'];
        }

        // ── Total vendido por producto_id ────────────────────────────────────
        $ventasRows = (new \yii\db\Query())
            ->select(['dv.producto_id', 'total_vendido' => 'SUM(dv.cantidad)'])
            ->from(['dv' => '{{%detalle_venta}}'])
            ->innerJoin(['v' => '{{%venta}}'], 'v.id = dv.venta_id AND v.estado = 1')
            ->groupBy('dv.producto_id')
            ->all();
        $ventasPorProducto = [];
        foreach ($ventasRows as $row) {
            $ventasPorProducto[(int)$row['producto_id']] = (int)$row['total_vendido'];
        }

        // ── Productos sin movimiento (nunca vendidos) ────────────────────────
        $kpiSinMovimiento = 0;
        $productosVistos = [];
        foreach ($allLotes as $l) {
            $pid = (int)$l->producto_id;
            if (!isset($productosVistos[$pid])) {
                $productosVistos[$pid] = true;
                if (empty($ventasPorProducto[$pid])) $kpiSinMovimiento++;
            }
        }

        $productos = ArrayHelper::map(
            Producto::find()->orderBy(['nombre' => SORT_ASC])->all(),
            'id',
            static fn(Producto $p): string => ($p->codigo ?? 'BG-P' . $p->id) . ' | ' . $p->nombre
        );
        $categorias = ArrayHelper::map(
            Categoria::find()->orderBy(['nombre' => SORT_ASC])->all(),
            'id', 'nombre'
        );
        $almacenes = ArrayHelper::map(
            Almacen::find()->orderBy(['nombre' => SORT_ASC])->all(),
            'id', 'nombre'
        );

        return $this->render('lotes', [
            'dataProvider'      => $dataProvider,
            'productos'         => $productos,
            'categorias'        => $categorias,
            'almacenes'         => $almacenes,
            'filtros'           => compact('productoId', 'categoriaId', 'almacenId', 'estado', 'desde', 'hasta'),
            'kpiLotes'          => $kpiLotes,
            'kpiUnidades'       => $kpiUnidades,
            'kpiValor'          => $kpiValor,
            'kpiSinMovimiento'  => $kpiSinMovimiento,
            'ultimaVenta'       => $ultimaVenta,
            'ventasPorProducto' => $ventasPorProducto,
        ]);
    }
    /**
     * Builds a unified movement list for a product: ingresos (lotes) + salidas (movimiento_fifo).
     * Returns rows ordered by fecha ASC with running stock balance.
     */
    private function buildMovimientos(int $productoId): array
    {
        $db   = Yii::$app->db;
        $rows = [];

        // ── Ingresos: from lote table ──────────────────────────────────────
        $ingresos = $db->createCommand('
            SELECT
                l.id,
                l.fecha_ingreso    AS fecha,
                l.precio_compra_unitario AS precio,
                l.cantidad_inicial AS cantidad_ingreso,
                (l.precio_compra_unitario * l.cantidad_inicial) AS total_ingreso,
                0                  AS cantidad_salida,
                0                  AS total_salida,
                l.gestion,
                u.nombre           AS responsable,
                u.paterno          AS responsable_ap,
                a.nombre           AS almacen
            FROM {{%lote}} l
            LEFT JOIN {{%user}}    u ON u.id = l.created_by
            LEFT JOIN {{%almacen}} a ON a.id = l.almacen_id
            WHERE l.producto_id = :pid
            ORDER BY l.fecha_ingreso ASC, l.id ASC
        ', [':pid' => $productoId])->queryAll();

        foreach ($ingresos as $r) {
            $rows[] = [
                'tipo'            => 'Ingreso',
                'fecha'           => $r['fecha'],
                'precio'          => (float)$r['precio'],
                'cantidad_ingreso'=> (int)$r['cantidad_ingreso'],
                'total_ingreso'   => (float)$r['total_ingreso'],
                'cantidad_salida' => 0,
                'total_salida'    => 0.0,
                'tipo_movimiento' => 'COMPRA (NORMAL)',
                'almacen'         => $r['almacen'] ?? 'Almacén Principal',
                'responsable'     => trim(($r['responsable'] ?? '') . ' ' . ($r['responsable_ap'] ?? '')),
            ];
        }

        // ── Salidas: from movimiento_fifo table ────────────────────────────
        $salidas = $db->createCommand('
            SELECT
                mf.id,
                mf.fecha_salida    AS fecha,
                mf.precio_compra_unitario AS precio,
                0                  AS cantidad_ingreso,
                0                  AS total_ingreso,
                mf.cantidad_salida AS cantidad_salida,
                (mf.precio_compra_unitario * mf.cantidad_salida) AS total_salida,
                u.nombre           AS responsable,
                u.paterno          AS responsable_ap,
                a.nombre           AS almacen
            FROM {{%movimiento_fifo}} mf
            JOIN  {{%lote}}        l  ON l.id  = mf.lote_id
            LEFT JOIN {{%user}}    u  ON u.id  = mf.created_by
            LEFT JOIN {{%almacen}} a  ON a.id  = l.almacen_id
            WHERE l.producto_id = :pid
            ORDER BY mf.fecha_salida ASC, mf.id ASC
        ', [':pid' => $productoId])->queryAll();

        foreach ($salidas as $r) {
            $rows[] = [
                'tipo'            => 'Salida',
                'fecha'           => $r['fecha'],
                'precio'          => (float)$r['precio'],
                'cantidad_ingreso'=> 0,
                'total_ingreso'   => 0.0,
                'cantidad_salida' => (int)$r['cantidad_salida'],
                'total_salida'    => (float)$r['total_salida'],
                'tipo_movimiento' => 'VENTA (Salida LIFO)',
                'almacen'         => $r['almacen'] ?? 'Almacén Principal',
                'responsable'     => trim(($r['responsable'] ?? '') . ' ' . ($r['responsable_ap'] ?? '')),
            ];
        }

        // Sort by fecha ASC
        usort($rows, static fn($a, $b) => strcmp((string)$a['fecha'], (string)$b['fecha']));

        // Compute running stock
        $stock = 0;
        foreach ($rows as &$row) {
            $stock += $row['cantidad_ingreso'] - $row['cantidad_salida'];
            $row['stock'] = $stock;
        }
        unset($row);

        return $rows;
    }
}
