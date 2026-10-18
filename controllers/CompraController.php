<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Compra;
use app\models\DetalleCompra;
use app\models\Producto;
use app\models\Proveedor;
use app\services\CompraService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class CompraController extends Controller
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

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Compra::find()->with(['proveedor', 'createdByUser'])->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $gestionActual = (int)date('Y');
        if (\app\controllers\BalanceController::isGestionCerrada($gestionActual)) {
            Yii::$app->session->setFlash('danger', "La gestión {$gestionActual} está cerrada. No se pueden registrar nuevas compras.");
            return $this->redirect(['index']);
        }

        $model = new Compra([
            'fecha_compra' => date('Y-m-d'),
            'gestion' => $gestionActual,
            'estado' => Compra::ESTADO_BORRADOR,
        ]);

        $almacenId = 1;
        $detalles = [new DetalleCompra(['almacen_id' => $almacenId, 'cantidad' => 1])];

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->load($post);

            $detalles = [];
            foreach (($post['DetalleCompra'] ?? []) as $row) {
                $detalle = new DetalleCompra();
                $detalle->load(['DetalleCompra' => $row]);

                $detalle->almacen_id = $almacenId;
                $detalle->gestion = (int)$model->gestion;
                $detalle->proveedor_id = (int)$model->proveedor_id;
                // placeholder para que la validación pase; se setea al guardar
                $detalle->compra_id = 0;
                $detalle->estado = 1;

                $producto = Producto::findOne(['id' => (int)$detalle->producto_id]);
                if ($producto !== null) {
                    $detalle->categoria_id = (int)$producto->categoria_id;
                }

                $detalle->importe = (float)($row['importe'] ?? 0);
                $cantidad = max(1, (int)$detalle->cantidad);
                $precioRow = isset($row['precio_unitario']) ? (float)$row['precio_unitario'] : 0.0;
                if ($precioRow > 0) {
                    $detalle->precio_unitario = round($precioRow, 4);
                    $detalle->importe = round($precioRow * $cantidad, 2);
                } else {
                    $detalle->precio_unitario = round($detalle->importe / $cantidad, 4);
                }
                $detalles[] = $detalle;
            }

            if ($detalles === []) {
                Yii::$app->session->setFlash('danger', 'Agrega al menos un producto al detalle.');
                return $this->render('create', [
                    'model' => $model,
                    'detalles' => [new DetalleCompra(['almacen_id' => $almacenId, 'cantidad' => 1])],
                    'proveedores' => ArrayHelper::map(Proveedor::find()->where(['estado' => 1])->orderBy(['nombre' => SORT_ASC])->all(), 'id', 'nombre'),
                    'productos' => ArrayHelper::map(Producto::find()->orderBy(['codigo' => SORT_ASC, 'nombre' => SORT_ASC])->all(), 'id', static function (Producto $p): string {
                        return trim(($p->codigo ?: '') . ' | ' . $p->nombre);
                    }),
                ]);
            }

            $isValid = $model->validate();
            foreach ($detalles as $detalle) {
                $isValid = $detalle->validate() && $isValid;
            }

            if ($isValid && CompraService::guardarConfirmada($model, $detalles)) {
                Yii::$app->session->setFlash('success', 'Compra registrada correctamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            Yii::$app->session->setFlash('danger', 'No se pudo registrar la compra. Revisa los datos.');
        }

        return $this->render('create', [
            'model' => $model,
            'detalles' => $detalles,
            'proveedores' => ArrayHelper::map(Proveedor::find()->where(['estado' => 1])->orderBy(['nombre' => SORT_ASC])->all(), 'id', 'nombre'),
            'productos' => ArrayHelper::map(Producto::find()->orderBy(['codigo' => SORT_ASC, 'nombre' => SORT_ASC])->all(), 'id', static function (Producto $p): string {
                return trim(($p->codigo ?: '') . ' | ' . $p->nombre);
            }),
        ]);
    }

    public function actionBuscarProducto(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string)Yii::$app->request->get('q', ''));
        if ($q === '') {
            return $this->asJson([]);
        }
        $rows = Producto::find()
            ->andWhere(['activo' => 1])
            ->andWhere(['or',
                ['like', 'nombre', $q],
                ['like', 'codigo', $q],
            ])
            ->orderBy(['codigo' => SORT_ASC])
            ->limit(20)
            ->all();

        return $this->asJson(array_map(static function (Producto $p): array {
            return [
                'id'            => $p->id,
                'codigo'        => $p->codigo ?: '',
                'nombre'        => $p->nombre,
                'precio_compra' => (float)$p->precio_compra,
                'stock'         => (int)$p->stock,
            ];
        }, $rows));
    }

    public function actionView(int $id): string
    {
        $model = Compra::find()->with(['proveedor', 'detalles.producto'])->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('No existe la compra.');
        }
        return $this->render('view', ['model' => $model]);
    }
}
