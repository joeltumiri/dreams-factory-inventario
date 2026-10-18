<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Categoria;
use app\models\Oferta;
use app\models\Producto;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class PrecioOfertaController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['lista'],
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity
                            && (Yii::$app->user->identity->isGerente() || Yii::$app->user->identity->isEncargado() || Yii::$app->user->identity->isVendedor()),
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity
                            && Yii::$app->user->identity->isGerente(),
                    ],
                ],
            ],
        ];
    }

    /** Submódulo 2: Pantalla principal de creación / formulario */
    public function actionIndex(): string|\yii\web\Response
    {
        $model = new Oferta([
            'fecha_inicio' => date('Y-m-d'),
            'fecha_final'  => date('Y-m-d', strtotime('+30 days')),
        ]);
        // Pre-rellenar producto si viene desde la lista
        $preProductoId = (int)Yii::$app->request->get('producto_id', 0);
        if ($preProductoId > 0) {
            $model->producto_id = $preProductoId;
            $prod = Producto::findOne($preProductoId);
            if ($prod) {
                $model->precio = $prod->precio_venta > 0 ? round((float)$prod->precio_venta * 0.9, 2) : 0;
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            // Auto-asignar precio_venta desde el producto si está vacío
            if (empty($model->precio_venta) && $model->producto_id) {
                $prod = Producto::findOne($model->producto_id);
                if ($prod) $model->precio_venta = (float)$prod->precio_venta;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Oferta registrada.');
                return $this->redirect(['lista']);
            }
        }
        return $this->render('form', [
            'model'          => $model,
            'productos'      => $this->productosList(),
            'ciudades'       => $this->ciudadesList(),
            'titulo'         => 'Nueva oferta',
            'preciosJson'    => $this->productosPreciosJson(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->precio_venta) && $model->producto_id) {
                $prod = Producto::findOne($model->producto_id);
                if ($prod) $model->precio_venta = (float)$prod->precio_venta;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Oferta actualizada.');
                return $this->redirect(['lista']);
            }
        }
        return $this->render('form', [
            'model'       => $model,
            'productos'   => $this->productosList(),
            'ciudades'    => $this->ciudadesList(),
            'titulo'      => 'Editar oferta',
            'preciosJson' => $this->productosPreciosJson(),
        ]);
    }

    /** Submódulo 3: Lista Ofertas con filtros */
    public function actionLista(): string
    {
        $req = Yii::$app->request;
        $soloActivas = (string)$req->get('activas', '1') === '1';
        $ciudadId    = (int)$req->get('ciudad_id', 0);
        $categoriaId = (int)$req->get('categoria_id', 0);
        $desde       = trim((string)$req->get('desde', ''));
        $hasta       = trim((string)$req->get('hasta', ''));
        $hoy         = date('Y-m-d');

        $query = (new \yii\db\Query())
            ->select([
                'p.id',
                'p.nombre',
                'p.codigo',
                'p.precio_venta',
                'p.precio_compra',
                'categoria_nombre' => 'cat.nombre',
                'oferta_id'        => 'o.id',
                'oferta_precio'    => 'o.precio_oferta',
                'o.fecha_inicio',
                'o.fecha_final',
                'ciudad_nombre'    => 'ci.nombre',
            ])
            ->from(['p' => '{{%producto}}'])
            ->leftJoin(['cat' => '{{%categoria}}'], 'cat.id = p.categoria_id')
            ->leftJoin(['o'   => '{{%oferta}}'], 'o.producto_id = p.id'
                . ($ciudadId > 0 ? ' AND o.ciudad_id = ' . $ciudadId : '')
                . ($soloActivas ? ' AND o.fecha_inicio <= \'' . $hoy . '\' AND o.fecha_final >= \'' . $hoy . '\'' : '')
                . ($desde !== '' ? ' AND o.fecha_inicio >= \'' . addslashes($desde) . '\'' : '')
                . ($hasta !== '' ? ' AND o.fecha_final  <= \'' . addslashes($hasta) . '\'' : ''))
            ->leftJoin(['ci'  => '{{%ciudad}}'], 'ci.id = o.ciudad_id')
            ->where(['p.activo' => 1])
            ->andWhere(['IS NOT', 'o.id', null])
            ->orderBy(['p.nombre' => SORT_ASC]);

        if ($categoriaId > 0) $query->andWhere(['p.categoria_id' => $categoriaId]);

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels'  => $query->all(),
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('lista', [
            'dataProvider' => $dataProvider,
            'ciudades'     => $this->ciudadesList(),
            'categorias'   => $this->categoriasList(),
            'filtros'      => compact('soloActivas', 'ciudadId', 'categoriaId', 'desde', 'hasta'),
        ]);
    }

    private function findModel(int $id): Oferta
    {
        $m = Oferta::findOne($id);
        if ($m === null) throw new NotFoundHttpException('Oferta no encontrada.');
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

    /** Mapa id => {precio_venta, precio_compra} para prellenar el formulario */
    private function productosPreciosJson(): string
    {
        $rows = Producto::find()->select(['id', 'precio_venta', 'precio_compra'])->asArray()->all();
        $map  = [];
        foreach ($rows as $r) {
            $map[(int)$r['id']] = ['v' => (float)$r['precio_venta'], 'c' => (float)$r['precio_compra']];
        }
        return json_encode($map, JSON_THROW_ON_ERROR);
    }

    public function actionBuscarProducto(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $q = trim((string)Yii::$app->request->get('q', ''));
        if ($q === '') return $this->asJson([]);
        $rows = Producto::find()
            ->andWhere(['activo' => 1])
            ->andWhere(['or', ['like', 'nombre', $q], ['like', 'codigo', $q]])
            ->orderBy(['nombre' => SORT_ASC])
            ->limit(20)->all();
        return $this->asJson(array_map(fn(Producto $p) => [
            'id'            => $p->id,
            'codigo'        => $p->codigo ?: 'BG-P' . $p->id,
            'nombre'        => $p->nombre,
            'precio'        => (float)$p->precio_venta,
            'precio_compra' => (float)$p->precio_compra,
        ], $rows));
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
