<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Almacen;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class AlmacenController extends Controller
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
                            return $user !== null && ($user->isGerente() || $user->isEncargado());
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Almacen::find()->orderBy(['id' => SORT_ASC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new Almacen();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Almacén «{$model->nombre}» registrado correctamente.");
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Almacén «{$model->nombre}» actualizado.");
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    private function findModel(int $id): Almacen
    {
        $model = Almacen::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Almacén no encontrado.');
        }
        return $model;
    }
}
