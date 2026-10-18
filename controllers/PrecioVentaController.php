<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Categoria;
use app\models\PrecioVenta;
use app\models\Producto;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class PrecioVentaController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity
                            && (Yii::$app->user->identity->isGerente() || Yii::$app->user->identity->isEncargado() || Yii::$app->user->identity->isVendedor()),
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity
                            && (Yii::$app->user->identity->isGerente() || Yii::$app->user->identity->isEncargado()),
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $req = Yii::$app->request;
        $ciudadId    = (int)$req->get('ciudad_id', 0);
        $categoriaId = (int)$req->get('categoria_id', 0);
        $q           = trim((string)$req->get('q', ''));

        // Mostrar TODOS los productos con su precio_venta general.
        // Si tiene precio por ciudad, mostrarlo; si no, mostrar el de producto.precio_venta.
        $query = (new \yii\db\Query())
            ->select([
                'p.id',
                'p.nombre',
                'p.codigo',
                'p.precio_venta',
                'p.precio_compra',
                'categoria_nombre' => 'cat.nombre',
                'pv_id'            => 'pv.id',
                'pv_precio'        => 'pv.precio',
                'pv.ciudad_id',
                'ciudad_nombre'    => 'ci.nombre',
            ])
            ->from(['p' => '{{%producto}}'])
            ->leftJoin(['cat' => '{{%categoria}}'],  'cat.id = p.categoria_id')
            ->leftJoin(['pv'  => '{{%precio_venta}}'],'pv.producto_id = p.id' . ($ciudadId > 0 ? ' AND pv.ciudad_id = ' . $ciudadId : ''))
            ->leftJoin(['ci'  => '{{%ciudad}}'],     'ci.id = pv.ciudad_id')
            ->where(['p.activo' => 1])
            ->orderBy(['p.nombre' => SORT_ASC]);

        if ($categoriaId > 0) $query->andWhere(['p.categoria_id' => $categoriaId]);
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'p.nombre', $q],
                ['like', 'p.codigo', $q],
            ]);
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels'  => $query->all(),
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'ciudades'     => $this->ciudadesList(),
            'categorias'   => $this->categoriasList(),
            'filtros'      => compact('q', 'ciudadId', 'categoriaId'),
        ]);
    }

    public function actionCreate()
    {
        $model = new PrecioVenta();
        // Pre-rellenar producto si viene desde el índice
        $preProductoId = (int)Yii::$app->request->get('producto_id', 0);
        if ($preProductoId > 0 && $model->isNewRecord) {
            $model->producto_id = $preProductoId;
            // Pre-rellenar precio base del producto
            $prod = Producto::findOne($preProductoId);
            if ($prod && (float)$prod->precio_venta > 0) {
                $model->precio = $prod->precio_venta;
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Precio registrado.');
            return $this->redirect(['index']);
        }
        return $this->render('form', [
            'model'         => $model,
            'productos'     => $this->productosList(),
            'preciosBase'   => $this->preciosBaseList(),
            'ciudades'      => $this->ciudadesList(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Precio actualizado.');
            return $this->redirect(['index']);
        }
        return $this->render('form', [
            'model'         => $model,
            'productos'     => $this->productosList(),
            'preciosBase'   => $this->preciosBaseList(),
            'ciudades'      => $this->ciudadesList(),
        ]);
    }

    private function findModel(int $id): PrecioVenta
    {
        $m = PrecioVenta::findOne($id);
        if ($m === null) throw new NotFoundHttpException('Precio no encontrado.');
        return $m;
    }

    private function productosList(): array
    {
        return ArrayHelper::map(
            Producto::find()->orderBy(['nombre' => SORT_ASC])->all(),
            'id',
            fn(Producto $p) => ($p->codigo ?: 'BG-P' . $p->id) . ' | ' . $p->nombre
        );
    }

    /** @return array<int,float> id => precio_venta base */
    private function preciosBaseList(): array
    {
        return ArrayHelper::map(
            Producto::find()->select(['id', 'precio_venta'])->asArray()->all(),
            'id',
            fn(array $p) => (float)$p['precio_venta']
        );
    }

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

    private function categoriasList(): array
    {
        return ArrayHelper::map(Categoria::find()->orderBy(['nombre' => SORT_ASC])->all(), 'id', 'nombre');
    }
}
