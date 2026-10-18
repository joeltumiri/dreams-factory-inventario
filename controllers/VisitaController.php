<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Cliente;
use app\models\Visita;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class VisitaController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => static function (): bool {
                        $user = Yii::$app->user->identity;
                        return $user && ($user->isGerente() || $user->isVendedor());
                    },
                ]],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $req   = Yii::$app->request;
        $fecha = trim((string)$req->get('fecha', date('Y-m')));
        if (!preg_match('/^\d{4}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m');
        }
        $desde = $fecha . '-01';
        $hasta = date('Y-m-t', strtotime($desde));
        $userId = (int)$req->get('user_id', 0);
        $estado = (int)$req->get('estado', 0);
        $q      = trim((string)$req->get('q', ''));

        $identity  = Yii::$app->user->identity;
        $esGerente = $identity && $identity->isGerente();

        $query = Visita::find()
            ->joinWith(['user', 'cliente'], false)
            ->select('visita.*')
            ->andWhere(['between', 'visita.fecha_visita', $desde, $hasta])
            ->orderBy(['visita.fecha_visita' => SORT_DESC, 'visita.id' => SORT_DESC]);

        if (!$esGerente) {
            $query->andWhere(['visita.user_id' => Yii::$app->user->id]);
        } elseif ($userId > 0) {
            $query->andWhere(['visita.user_id' => $userId]);
        }

        if ($estado > 0) $query->andWhere(['visita.estado' => $estado]);
        if ($q !== '')   $query->andWhere(['like', 'cliente.razon_social', $q]);

        // KPIs mes actual
        $kpiBase = Visita::find()->andWhere(['between', 'fecha_visita', date('Y-m-01'), date('Y-m-d')]);
        if (!$esGerente) $kpiBase->andWhere(['user_id' => Yii::$app->user->id]);

        $kpiTotal     = (clone $kpiBase)->count();
        $kpiConPedido = (clone $kpiBase)->andWhere(['estado' => Visita::ESTADO_CON_PEDIDO])->count();
        $kpiSinPedido = (clone $kpiBase)->andWhere(['estado' => Visita::ESTADO_SIN_PEDIDO])->count();
        $kpiCerrado   = (clone $kpiBase)->andWhere(['estado' => Visita::ESTADO_CERRADO])->count();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
            'sort'       => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'vendedores'   => $esGerente ? $this->vendedoresList() : [],
            'filtros'      => compact('fecha', 'desde', 'hasta', 'userId', 'estado', 'q'),
            'esGerente'    => $esGerente,
            'kpiTotal'     => $kpiTotal,
            'kpiConPedido' => $kpiConPedido,
            'kpiSinPedido' => $kpiSinPedido,
            'kpiCerrado'   => $kpiCerrado,
        ]);
    }

    public function actionCreate()
    {
        $model = new Visita([
            'fecha_visita' => date('Y-m-d'),
            'hora_visita'  => date('H:i'),
            'estado'       => Visita::ESTADO_SIN_PEDIDO,
            'user_id'      => Yii::$app->user->id,
        ]);

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = time();
            if ((int)$model->estado !== Visita::ESTADO_CON_PEDIDO) {
                $model->venta_id = null;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Visita registrada correctamente.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model'    => $model,
            'clientes' => $this->clientesList(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model     = $this->findModel($id);
        $identity  = Yii::$app->user->identity;

        if ($model->user_id !== Yii::$app->user->id && !($identity && $identity->isGerente())) {
            Yii::$app->session->setFlash('danger', 'No tiene permiso para editar esta visita.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ((int)$model->estado !== Visita::ESTADO_CON_PEDIDO) {
                $model->venta_id = null;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Visita actualizada.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model'    => $model,
            'clientes' => $this->clientesList(),
        ]);
    }

    public function actionView(int $id): string
    {
        $model    = $this->findModel($id);
        $identity = Yii::$app->user->identity;

        if (!($identity && $identity->isGerente()) && $model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('Visita no encontrada.');
        }

        return $this->render('view', ['model' => $model]);
    }

    /** AJAX: ventas recientes de un cliente */
    public function actionVentasCliente(int $clienteId): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ventas = (new \yii\db\Query())
            ->select(['id', 'codigo', 'fecha_pedido', 'importe_total'])
            ->from('{{%venta}}')
            ->where(['cliente_id' => $clienteId, 'estado' => 1])
            ->orderBy(['fecha_pedido' => SORT_DESC])
            ->limit(10)
            ->all();
        return $this->asJson(array_map(fn($v) => [
            'id'   => $v['id'],
            'text' => 'VTA-' . str_pad((string)$v['id'], 3, '0', STR_PAD_LEFT)
                . ' | ' . $v['fecha_pedido']
                . ' | Bs. ' . number_format((float)$v['importe_total'], 2),
        ], $ventas));
    }

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function findModel(int $id): Visita
    {
        $m = Visita::findOne($id);
        if ($m === null) throw new NotFoundHttpException('Visita no encontrada.');
        return $m;
    }

    private function clientesList(): array
    {
        return ArrayHelper::map(
            Cliente::find()->orderBy(['razon_social' => SORT_ASC])->all(),
            'id',
            fn(Cliente $c) => $c->getNombreDisplay()
        );
    }

    private function vendedoresList(): array
    {
        $rows = (new \yii\db\Query())
            ->select(['id', 'nombre', 'paterno', 'materno', 'username'])
            ->from('{{%user}}')
            ->orderBy('username')
            ->all();
        $out = [];
        foreach ($rows as $r) {
            $nombre = trim(implode(' ', array_filter([$r['nombre'] ?? '', $r['paterno'] ?? '', $r['materno'] ?? '']))) ?: $r['username'];
            $out[(int)$r['id']] = $nombre;
        }
        return $out;
    }
}
