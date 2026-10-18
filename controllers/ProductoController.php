<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Categoria;
use app\models\Producto;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class ProductoController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['@'],
                        'matchCallback' => static function (): bool {
                            $user = Yii::$app->user->identity;
                            return $user && $user->isGerente();
                        },
                    ],
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

    public function actionIndex(): string
    {
        $request = Yii::$app->request;
        $q       = trim((string)$request->get('q', ''));
        $catId   = (int)$request->get('categoria_id', 0);

        $query = Producto::find()->with(['categoria'])->orderBy(['activo' => SORT_DESC, 'id' => SORT_DESC]);

        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'nombre', $q],
                ['like', 'codigo', $q],
            ]);
        }
        if ($catId > 0) {
            $query->andWhere(['categoria_id' => $catId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'categorias'   => $this->categorias(),
            'q'            => $q,
            'catId'        => $catId,
        ]);
    }

    public function actionCreate()
    {
        $model = new Producto(['activo' => 1, 'stock' => 0, 'stock_minimo' => 0]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Producto «{$model->nombre}» creado.");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model'      => $model,
            'categorias' => $this->categorias(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Producto «{$model->nombre}» actualizado.");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model'      => $model,
            'categorias' => $this->categorias(),
        ]);
    }

    public function actionView(int $id): string
    {
        $model = Producto::find()
            ->with([
                'categoria',
                'detallesCompra.compra.proveedor',
                'detallesVenta.venta.cliente',
            ])
            ->where(['id' => $id])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException('No existe el producto.');
        }
        return $this->render('view', ['model' => $model]);
    }

    /** Activa o desactiva el producto. */
    public function actionToggle(int $id)
    {
        $model = $this->findModel($id);
        $model->activo = $model->activo ? 0 : 1;
        $model->save(false);
        $estado = $model->activo ? 'activado' : 'desactivado';
        Yii::$app->session->setFlash('success', "Producto «{$model->nombre}» {$estado}.");
        return $this->redirect(['index']);
    }

    /** Elimina un producto si no tiene compras ni ventas asociadas. */
    public function actionDelete(int $id)
    {
        if (!Yii::$app->request->isPost) {
            throw new \yii\web\MethodNotAllowedHttpException();
        }
        $model = $this->findModel($id);

        $refs = [
            'compras'   => '{{%detalle_compra}}',
            'ventas'    => '{{%detalle_venta}}',
            'lotes'     => '{{%lote}}',
            'precios'   => '{{%precio_venta}}',
            'ofertas'   => '{{%oferta}}',
            'rotaciones' => '{{%rotacion_producto}}',
            'conteos'   => '{{%conteo_fisico}}',
        ];
        $bloqueos = [];
        foreach ($refs as $label => $table) {
            $n = (int)(new \yii\db\Query())->from($table)->where(['producto_id' => $model->id])->count();
            if ($n > 0) {
                $bloqueos[] = "{$n} {$label}";
            }
        }
        if ($bloqueos !== []) {
            Yii::$app->session->setFlash('danger', "No se puede eliminar «{$model->nombre}»: tiene " . implode(', ', $bloqueos) . ". Desactívalo en su lugar.");
            return $this->redirect(['index']);
        }
        $nombre = $model->nombre;
        $model->delete();
        Yii::$app->session->setFlash('success', "Producto «{$nombre}» eliminado.");
        return $this->redirect(['index']);
    }

    /**
     * AJAX autocomplete: devuelve productos que coincidan con el término.
     * GET ?q=texto  → JSON [{id, text, categoria_id, categoria_nombre, precio_compra}]
     */
    public function actionBuscarProducto(): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $q = trim((string)Yii::$app->request->get('q', ''));
        if (strlen($q) < 2) return $this->asJson([]);

        $rows = (new \yii\db\Query())
            ->select(['p.id', 'p.nombre', 'p.codigo', 'p.precio_compra', 'c.id AS cat_id', 'c.nombre AS cat_nombre'])
            ->from(['p' => '{{%producto}}'])
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->where(['p.activo' => 1])
            ->andWhere(['or',
                ['like', 'p.nombre', $q],
                ['like', 'p.codigo', $q],
            ])
            ->orderBy(['p.nombre' => SORT_ASC])
            ->limit(15)
            ->all();

        return $this->asJson(array_map(static function (array $r): array {
            $codigo = $r['codigo'] ?: 'BG-P' . $r['id'];
            return [
                'id'              => (int)$r['id'],
                'text'            => $codigo . ' | ' . $r['nombre'],
                'categoria_id'    => (int)$r['cat_id'],
                'categoria_nombre'=> (string)($r['cat_nombre'] ?? ''),
                'precio_compra'   => (float)$r['precio_compra'],
            ];
        }, $rows));
    }

    /**
     * AJAX: devuelve info de un producto por id.
     * GET ?id=N  → JSON {categoria_id, categoria_nombre, precio_compra}
     */
    public function actionGetProductoInfo(int $id): \yii\web\Response
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $row = (new \yii\db\Query())
            ->select(['p.precio_compra', 'c.id AS cat_id', 'c.nombre AS cat_nombre'])
            ->from(['p' => '{{%producto}}'])
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->where(['p.id' => $id, 'p.activo' => 1])
            ->one();

        if (!$row) {
            Yii::$app->response->statusCode = 404;
            return $this->asJson(['error' => 'No encontrado']);
        }

        return $this->asJson([
            'categoria_id'    => (int)$row['cat_id'],
            'categoria_nombre'=> (string)($row['cat_nombre'] ?? ''),
            'precio_compra'   => (float)$row['precio_compra'],
        ]);
    }

    /** Configura el stock mínimo de un producto (POST). */
    public function actionConfigStock(int $id)
    {

        $model = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $stockMin = (int)Yii::$app->request->post('stock_minimo', 0);
            if ($stockMin < 0) { $stockMin = 0; }
            $model->stock_minimo = $stockMin;
            $model->save(false);
            Yii::$app->session->setFlash('success', "Stock mínimo de «{$model->nombre}» configurado en {$stockMin}.");
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function findModel(int $id): Producto
    {
        $model = Producto::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('No existe el producto.');
        }
        return $model;
    }

    private function categorias(): array
    {
        return ArrayHelper::map(
            Categoria::find()->where(['activo' => 1])->orderBy(['nombre' => SORT_ASC])->all(),
            'id',
            'nombre'
        );
    }
}
