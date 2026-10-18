<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Role;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class UsuarioController extends Controller
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
                            return $user !== null && $user->isGerente();
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $query = User::find()->orderBy(['id' => SORT_DESC]);

        $search = trim((string)Yii::$app->request->get('q', ''));
        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'username', $search],
                ['like', 'email', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
        ]);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->status = User::STATUS_ACTIVE;
        $model->setDefaultRole();
        $model->tipo_usuario = User::TIPO_USUARIO_VENTA;
        $model->status_id = 1;
        $model->role_id = Role::VENDEDOR;
        $model->user_type_id = 3;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->status === null || $model->status === '') {
                $model->status = User::STATUS_ACTIVE;
            }

            $model->auth_key = Yii::$app->security->generateRandomString(32);
            $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            $model->status_id = (int)$model->status === User::STATUS_ACTIVE ? 1 : 2;
            $model->role_id = $this->roleIdFromTipoUsuario((string)$model->tipo_usuario);
            $model->user_type_id = $this->userTypeIdFromEnum((string)$model->tipo_usuario);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Usuario registrado correctamente.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $model->password = '';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->password !== '') {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            }

            $model->status_id = (int)$model->status === User::STATUS_ACTIVE ? 1 : 2;
            $model->user_type_id = $this->userTypeIdFromEnum((string)$model->tipo_usuario);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Usuario actualizado correctamente.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionCambiarRol(int $id): Response
    {
        $model = $this->findModel($id);
        $this->protegerPropioGerente($model);

        $roleId = (int)Yii::$app->request->post('role_id');

        if (!Role::findOne($roleId)) {
            Yii::$app->session->setFlash('danger', 'Rol no válido.');
            return $this->redirect(['index']);
        }

        $model->role_id = $roleId;
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', "Rol de «{$model->username}» actualizado.");
        } else {
            Yii::$app->session->setFlash('danger', 'No se pudo actualizar el rol.');
        }

        return $this->redirect(['index']);
    }

    public function actionToggleStatus(int $id): Response
    {
        $model = $this->findModel($id);
        $this->protegerPropioGerente($model);

        $model->status_id = $model->status_id === 1 ? 2 : 1;
        if ($model->save(false)) {
            $estado = $model->status_id === 1 ? 'activado' : 'desactivado';
            Yii::$app->session->setFlash('success', "Usuario «{$model->username}» $estado.");
        } else {
            Yii::$app->session->setFlash('danger', 'No se pudo cambiar el estado.');
        }

        return $this->redirect(['index']);
    }

    public function actionEliminar(int $id): Response
    {
        $model = $this->findModel($id);
        $this->protegerPropioGerente($model);

        $username = $model->username;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Usuario «' . $username . '» eliminado.');

        return $this->redirect(['index']);
    }

    // ── helpers ────────────────────────────────────────────────────────────

    private function findModel(int $id): User
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Usuario no encontrado.');
        }
        return $model;
    }

    /** Impide modificar/eliminar la propia cuenta del gerente en sesión */
    private function protegerPropioGerente(User $model): void
    {
        if ($model->id === (int)Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', 'No puedes modificar tu propia cuenta desde aquí.');
            Yii::$app->response->redirect(['/usuario/index'])->send();
            Yii::$app->end();
        }
    }

    private function roleIdFromTipoUsuario(string $tipoUsuario): int
    {
        return match ($tipoUsuario) {
            User::TIPO_USUARIO_ALMACEN => Role::ALMACEN,
            User::TIPO_USUARIO_SUPERVISOR => Role::GERENTE,
            default => Role::VENDEDOR,
        };
    }

    private function userTypeIdFromEnum(string $tipoUsuario): int
    {
        return match ($tipoUsuario) {
            User::TIPO_USUARIO_ALMACEN => 2,
            User::TIPO_USUARIO_SUPERVISOR => 1,
            default => 3,
        };
    }
}
