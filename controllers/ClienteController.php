<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Cliente;
use app\models\Departamento;
use app\models\Tipo;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class ClienteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@'],
                ]],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $req = Yii::$app->request;
        $q        = trim((string)$req->get('q', ''));
        $zona     = trim((string)$req->get('zona', ''));
        $tipoId   = (int)$req->get('tipo_id', 0);
        $ciudadId = (int)$req->get('ciudad_id', 0);

        $query = Cliente::find()
            ->with(['ciudad', 'departamento', 'tipo'])
            ->orderBy(['id' => SORT_DESC]);

        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'razon_social', $q],
                ['like', 'propietario', $q],
                ['like', 'nit', $q],
                ['like', 'nombre_factura', $q],
            ]);
        }
        if ($zona !== '')   $query->andWhere(['like', 'zona', $zona]);
        if ($tipoId > 0)    $query->andWhere(['tipo_id' => $tipoId]);
        if ($ciudadId > 0)  $query->andWhere(['ciudad_id' => $ciudadId]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
            'sort'       => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tipos'        => $this->tiposList(),
            'ciudades'     => $this->ciudadesList(),
            'zonas'        => $this->zonasDistinct(),
            'filtros'      => compact('q', 'zona', 'tipoId', 'ciudadId'),
        ]);
    }

    public function actionCreate()
    {
        $model = new Cliente();
        // Defaults: La Paz / El Alto / Farmacia
        $model->departamento_id = (int)(Departamento::find()->select('id')->scalar() ?: 1);
        $model->ciudad_id       = (int)(\Yii::$app->db->createCommand(
            "SELECT id FROM {{%ciudad}} ORDER BY (nombre = 'El Alto') DESC, id ASC LIMIT 1"
        )->queryScalar() ?: 1);
        $model->tipo_id         = (int)(\Yii::$app->db->createCommand(
            "SELECT id FROM {{%tipo}} ORDER BY id ASC LIMIT 1"
        )->queryScalar() ?: 1);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Cliente registrado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model'         => $model,
            'tipos'         => $this->tiposList(),
            'ciudades'      => $this->ciudadesList(),
            'departamentos' => $this->departamentosList(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Cliente actualizado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model'         => $model,
            'tipos'         => $this->tiposList(),
            'ciudades'      => $this->ciudadesList(),
            'departamentos' => $this->departamentosList(),
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        $totales = (new \yii\db\Query())
            ->from('{{%venta}}')
            ->select([
                'total_pedidos' => 'COUNT(*)',
                'monto_total'   => 'COALESCE(SUM(importe_total), 0)',
                'ultima_fecha'  => 'MAX(fecha_pedido)',
            ])
            ->where(['cliente_id' => $id])
            ->one() ?: ['total_pedidos' => 0, 'monto_total' => 0, 'ultima_fecha' => null];

        $ventas = (new \yii\db\Query())
            ->from('{{%venta}}')
            ->where(['cliente_id' => $id])
            ->orderBy(['fecha_pedido' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(50)
            ->all();

        $habituales = (new \yii\db\Query())
            ->from(['dv' => '{{%detalle_venta}}'])
            ->innerJoin(['v' => '{{%venta}}'], 'v.id = dv.venta_id')
            ->innerJoin(['p' => '{{%producto}}'], 'p.id = dv.producto_id')
            ->select([
                'codigo'   => 'p.codigo',
                'nombre'   => 'p.nombre',
                'veces'    => 'COUNT(*)',
                'cantidad' => 'SUM(dv.cantidad)',
                'monto'    => 'SUM(dv.importe)',
                'ultima'   => 'MAX(v.fecha_pedido)',
            ])
            ->where(['v.cliente_id' => $id])
            ->groupBy(['p.id', 'p.codigo', 'p.nombre'])
            ->orderBy(['cantidad' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('view', [
            'model'      => $model,
            'totales'    => $totales,
            'ventas'     => $ventas,
            'habituales' => $habituales,
        ]);
    }

    /**
     * Exporta clientes a CSV (Excel-compatible).
     */
    public function actionExportar(): Response
    {
        $items = Cliente::find()->with(['ciudad', 'departamento', 'tipo'])->all();

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $resp->headers->set('Content-Disposition',
            'attachment; filename="clientes_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Listado de Clientes - ' . date('d/m/Y H:i')]);
        fputcsv($out, []);
        fputcsv($out, [
            'ID', 'Tipo', 'Razón Social', 'Regente', 'NIT', 'Nombre Factura',
            'Email', 'Celular', 'Departamento', 'Ciudad', 'Zona', 'Dirección',
            'Latitud', 'Longitud',
        ]);

        foreach ($items as $c) {
            fputcsv($out, [
                $c->id,
                $c->tipo ? $c->tipo->farmacia : '',
                (string)$c->razon_social,
                (string)$c->propietario,
                (string)$c->nit,
                (string)$c->nombre_factura,
                (string)$c->email,
                (string)$c->contacto,
                $c->departamento ? $c->departamento->nombre : '',
                $c->ciudad ? $c->ciudad->nombre : '',
                (string)$c->zona,
                (string)$c->direccion,
                $c->latitud !== null ? (string)$c->latitud : '',
                $c->longitud !== null ? (string)$c->longitud : '',
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        $resp->content = $csv;
        return $resp;
    }

    /**
     * Reporte: clientes con más compras por gestión.
     */
    public function actionReporte(): string
    {
        $gestion = (int)Yii::$app->request->get('gestion', date('Y'));
        $ini = $gestion . '-01-01';
        $fin = $gestion . '-12-31';

        $rows = (new \yii\db\Query())
            ->select([
                'cliente_id'    => 'v.cliente_id',
                'razon_social'  => 'COALESCE(NULLIF(cl.razon_social,""), cl.propietario)',
                'zona'          => 'cl.zona',
                'pedidos'       => 'COUNT(DISTINCT v.id)',
                'total'         => 'COALESCE(SUM(v.importe_total), 0)',
                'ultima_compra' => 'MAX(v.fecha_pedido)',
            ])
            ->from(['v' => '{{%venta}}'])
            ->leftJoin(['cl' => '{{%cliente}}'], 'cl.id = v.cliente_id')
            ->where(['between', 'v.fecha_pedido', $ini, $fin])
            ->groupBy(['v.cliente_id', 'cl.razon_social', 'cl.propietario', 'cl.zona'])
            ->orderBy(['total' => SORT_DESC])
            ->all();

        // Gestiones disponibles
        $gestiones = (new \yii\db\Query())
            ->select(['gestion' => 'YEAR(fecha_pedido)'])
            ->distinct()
            ->from('{{%venta}}')
            ->orderBy(['gestion' => SORT_DESC])
            ->column();

        return $this->render('reporte', [
            'rows'     => $rows,
            'gestion'  => $gestion,
            'gestiones' => $gestiones ?: [date('Y')],
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function findModel(int $id): Cliente
    {
        $model = Cliente::find()->with(['ciudad', 'departamento', 'tipo'])->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('No existe el cliente.');
        }
        return $model;
    }

    private function tiposList(): array
    {
        return ArrayHelper::map(Tipo::find()->orderBy(['id' => SORT_ASC])->all(), 'id', 'farmacia');
    }

    private function ciudadesList(): array
    {
        return ArrayHelper::map(
            (new \yii\db\Query())
                ->from(['c' => '{{%ciudad}}'])
                ->leftJoin(['d' => '{{%departamento}}'], 'd.id = c.departamento_id')
                ->select(['id' => 'c.id', 'label' => new Expression(
                    "CONCAT(c.nombre, COALESCE(CONCAT(' (', d.sigla, ')'), ''))"
                ), 'departamento_id' => 'c.departamento_id'])
                ->orderBy(['c.nombre' => SORT_ASC])
                ->all(),
            'id', 'label'
        );
    }

    private function departamentosList(): array
    {
        return ArrayHelper::map(
            Departamento::find()->orderBy(['nombre' => SORT_ASC])->all(),
            'id', 'nombre'
        );
    }

    private function zonasDistinct(): array
    {
        return (new \yii\db\Query())
            ->from('{{%cliente}}')
            ->select('zona')
            ->distinct()
            ->where(['not', ['zona' => null]])
            ->andWhere(['<>', 'zona', ''])
            ->orderBy(['zona' => SORT_ASC])
            ->column();
    }
}
