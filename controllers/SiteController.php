<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\LoginForm;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

final class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'index'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\\web\\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        // Vendedor → ir directamente a ventas
        if ($user && $user->isVendedor()) {
            return $this->redirect(['/venta/index']);
        }

        return $this->render('index', [
            'tendencia' => $this->tendenciaVentas6Meses(),
        ]);
    }

    /**
     * Calcula la evolución de ventas de los últimos 6 meses
     * con dirección (up/down/flat) y % de variación vs mes anterior.
     *
     * @return array{labels:string[], data:float[], direccion:string, variacion:float, ultimo:float, anterior:float}
     */
    private function tendenciaVentas6Meses(): array
    {
        $mesesCortos = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $ini = date('Y-m-01', strtotime("-{$i} month"));
            $fin = date('Y-m-t',  strtotime("-{$i} month"));
            $monto = (float)(new Query())
                ->from('{{%venta}}')
                ->where(['estado' => 1])
                ->andWhere(['between', 'fecha_pedido', $ini, $fin])
                ->sum('total_efectivo');
            $labels[] = $mesesCortos[(int)date('n', strtotime($ini))] . ' ' . date('y', strtotime($ini));
            $data[]   = round($monto, 2);
        }

        $len = count($data);
        $ultimo   = $data[$len - 1] ?? 0.0;
        $anterior = $data[$len - 2] ?? 0.0;
        $variacion = $anterior > 0 ? round((($ultimo - $anterior) / $anterior) * 100, 1) : 0.0;
        $direccion = 'flat';
        if ($ultimo > $anterior) $direccion = 'up';
        elseif ($ultimo < $anterior) $direccion = 'down';

        return compact('labels', 'data', 'direccion', 'variacion', 'ultimo', 'anterior');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}