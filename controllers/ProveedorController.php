<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Compra;
use app\models\Categoria;
use app\models\DetalleCompra;
use app\models\Proveedor;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class ProveedorController extends Controller
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
                    'toggle' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $req = Yii::$app->request;
        $q        = trim((string)$req->get('q', ''));
        $ciudadId = (int)$req->get('ciudad_id', 0);
        $rubro    = trim((string)$req->get('rubro', ''));
        $estado   = $req->get('estado', '1'); // '1' activos por defecto, '0' inactivos, 'all'

        $query = Proveedor::find()->with(['ciudad'])->orderBy(['estado' => SORT_DESC, 'nombre' => SORT_ASC]);

        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'nombre', $q],
                ['like', 'razon_social', $q],
                ['like', 'nit', $q],
            ]);
        }
        if ($ciudadId > 0) {
            $query->andWhere(['ciudad_id' => $ciudadId]);
        }
        if ($rubro !== '') {
            $query->andWhere(['like', 'rubro', $rubro]);
        }
        if ($estado === '1' || $estado === '0') {
            $query->andWhere(['estado' => (int)$estado]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
            'sort'       => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'filtros'      => compact('q', 'ciudadId', 'rubro', 'estado'),
            'ciudades'     => $this->ciudadesList(),
            'rubros'       => $this->rubrosDistinct(),
        ]);
    }

    public function actionCreate()
    {
        $model = new Proveedor();
        $model->estado = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Proveedor creado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model'    => $model,
            'ciudades' => $this->ciudadesList(),
            'rubros'   => $this->rubrosCategorias(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Proveedor actualizado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model'    => $model,
            'ciudades' => $this->ciudadesList(),
            'rubros'   => $this->rubrosCategorias(),
        ]);
    }

    /**
     * Vista detalle del proveedor con historial de compras y productos habituales.
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        // Historial de compras
        $compras = Compra::find()
            ->where(['proveedor_id' => $id])
            ->orderBy(['fecha_compra' => SORT_DESC, 'id' => SORT_DESC])
            ->all();

        // Totales
        $totales = (new \yii\db\Query())
            ->from('{{%compra}}')
            ->select([
                'total_compras' => 'COUNT(*)',
                'monto_total'   => 'COALESCE(SUM(importe_total), 0)',
                'ultima_fecha'  => 'MAX(fecha_compra)',
            ])
            ->where(['proveedor_id' => $id])
            ->one() ?: ['total_compras' => 0, 'monto_total' => 0, 'ultima_fecha' => null];

        // Productos habituales (top 10 por cantidad acumulada)
        $habituales = (new \yii\db\Query())
            ->from(['dc' => '{{%detalle_compra}}'])
            ->innerJoin(['c' => '{{%compra}}'], 'c.id = dc.compra_id')
            ->innerJoin(['p' => '{{%producto}}'], 'p.id = dc.producto_id')
            ->select([
                'producto_id' => 'p.id',
                'codigo'      => 'p.codigo',
                'nombre'      => 'p.nombre',
                'veces'       => 'COUNT(*)',
                'cantidad'    => 'SUM(dc.cantidad)',
                'monto'       => 'SUM(dc.importe)',
                'ultima'      => 'MAX(c.fecha_compra)',
            ])
            ->where(['c.proveedor_id' => $id])
            ->groupBy(['p.id', 'p.codigo', 'p.nombre'])
            ->orderBy(['cantidad' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('view', [
            'model'      => $model,
            'compras'    => $compras,
            'totales'    => $totales,
            'habituales' => $habituales,
        ]);
    }

    public function actionToggle(int $id)
    {
        $model = $this->findModel($id);
        $model->estado = $model->estado ? 0 : 1;
        $model->save(false);
        Yii::$app->session->setFlash('info',
            $model->estado ? 'Proveedor activado.' : 'Proveedor desactivado.'
        );
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function findModel(int $id): Proveedor
    {
        $model = Proveedor::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('No existe el proveedor.');
        }
        return $model;
    }

    /**
     * Lista de ciudades con departamento como [id => "Ciudad (Depto)"].
     */
    private function ciudadesList(): array
    {
        return ArrayHelper::map(
            (new \yii\db\Query())
                ->from(['c' => '{{%ciudad}}'])
                ->leftJoin(['d' => '{{%departamento}}'], 'd.id = c.departamento_id')
                ->select(['id' => 'c.id', 'label' => new Expression(
                    "CONCAT(c.nombre, COALESCE(CONCAT(' (', d.sigla, ')'), ''))"
                )])
                ->orderBy(['c.nombre' => SORT_ASC])
                ->all(),
            'id', 'label'
        );
    }

    /**
     * Lista de rubros únicos ya registrados (para sugerencias de filtro).
     */
    private function rubrosDistinct(): array
    {
        return (new \yii\db\Query())
            ->from('{{%proveedor}}')
            ->select('rubro')
            ->distinct()
            ->where(['not', ['rubro' => null]])
            ->andWhere(['<>', 'rubro', ''])
            ->orderBy(['rubro' => SORT_ASC])
            ->column();
    }

    /**
     * Lista de categorías activas como rubros [nombre => nombre] para el dropdown.
     */
    private function rubrosCategorias(): array
    {
        $nombres = Categoria::find()
            ->select('nombre')
            ->where(['activo' => 1])
            ->orderBy(['nombre' => SORT_ASC])
            ->column();
        return array_combine($nombres, $nombres) ?: [];
    }
}
