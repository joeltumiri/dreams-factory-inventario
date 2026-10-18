<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Role;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class RolController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => static function () {
                            $user = Yii::$app->user->identity;
                            return $user !== null && $user->isGerente();
                        },
                    ],
                ],
                'denyCallback' => function () {
                    throw new ForbiddenHttpException('Solo el Gerente puede gestionar roles.');
                },
            ],
        ];
    }

    public function actionIndex(): string
    {
        $roles = Role::find()->orderBy('role_value')->all();
        return $this->render('index', ['roles' => $roles]);
    }

    public function actionCreate(): mixed
    {
        $model = new Role();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Rol creado correctamente.');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionDelete(int $id): mixed
    {
        $model = Role::findOne($id);
        if ($model === null) {
            Yii::$app->session->setFlash('danger', 'Rol no encontrado.');
            return $this->redirect(['index']);
        }

        // Protect default roles from deletion
        if (in_array($model->role_value, [Role::GERENTE, Role::ALMACEN, Role::VENDEDOR], true)) {
            Yii::$app->session->setFlash('warning', 'No se pueden eliminar los roles del sistema.');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Rol eliminado.');
        return $this->redirect(['index']);
    }
}
