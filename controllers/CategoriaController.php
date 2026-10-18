<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Categoria;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class CategoriaController extends Controller
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
        $dataProvider = new ActiveDataProvider([
            'query' => Categoria::find()->orderBy(['activo' => SORT_DESC, 'nombre' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Categoria(['activo' => 1]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Categoría «{$model->nombre}» creada.");
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Categoría «{$model->nombre}» actualizada.");
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    /** Activa o desactiva una categoría (toggle). */
    public function actionToggle(int $id)
    {
        $model = $this->findModel($id);
        $model->activo = $model->activo ? 0 : 1;
        $model->save(false);
        $estado = $model->activo ? 'activada' : 'desactivada';
        Yii::$app->session->setFlash('success', "Categoría «{$model->nombre}» {$estado}.");
        return $this->redirect(['index']);
    }

    /** Elimina una categoría si no tiene productos asociados. */
    public function actionDelete(int $id)
    {
        if (!Yii::$app->request->isPost) {
            throw new \yii\web\MethodNotAllowedHttpException();
        }
        $model = $this->findModel($id);
        if ($model->getProductosCount() > 0) {
            Yii::$app->session->setFlash('danger', "No se puede eliminar «{$model->nombre}»: tiene productos asociados. Desactívala en su lugar.");
            return $this->redirect(['index']);
        }
        $nombre = $model->nombre;
        $model->delete();
        Yii::$app->session->setFlash('success', "Categoría «{$nombre}» eliminada.");
        return $this->redirect(['index']);
    }

    private function findModel(int $id): Categoria
    {
        $model = Categoria::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('No existe la categoría.');
        }
        return $model;
    }
}
