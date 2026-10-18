<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Cliente;
use app\models\DetalleVenta;
use app\models\Producto;
use app\models\Venta;
use app\services\VentaService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class VentaController extends Controller
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
                            return $user !== null;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Venta::find()
                ->with(['cliente', 'user'])
                ->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $gestionActual = (int)date('Y');
        if (\app\controllers\BalanceController::isGestionCerrada($gestionActual)) {
            Yii::$app->session->setFlash('danger', "La gestión {$gestionActual} está cerrada. No se pueden registrar nuevas ventas.");
            return $this->redirect(['index']);
        }

        $model = new Venta([
            'fecha_pedido'    => date('Y-m-d'),
            'estado_venta_id' => 1,  // Para Entregar
            'tipo_pago_id'    => 1,
            'estado_pago_id'  => 2,  // Siempre Pagado
        ]);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->load($post);

            // Auto-generar nro_documento para Nota de Remisión
            if ($model->documento === 'Nota de Remisión' && empty($model->nro_documento)) {
                $gestion = (int)date('Y');
                $last = (int)Yii::$app->db->createCommand(
                    "SELECT COUNT(*) FROM {{%venta}} WHERE documento = 'Nota de Remisión' AND gestion = :g",
                    [':g' => $gestion]
                )->queryScalar();
                $model->nro_documento = 'NR-' . $gestion . '-' . str_pad((string)($last + 1), 4, '0', STR_PAD_LEFT);
            }

            // Construir detalles desde POST
            $detalles = [];
            foreach (($post['DetalleVenta'] ?? []) as $row) {
                $d = new DetalleVenta();
                $d->producto_id    = (int)($row['producto_id'] ?? 0);
                $d->cantidad       = (int)($row['cantidad'] ?? 0);
                $d->precio_unitario = (float)($row['precio_unitario'] ?? 0);
                $d->precio_real    = (float)($row['precio_unitario'] ?? 0);
                $d->importe        = (float)($row['importe'] ?? 0);
                $detalles[]        = $d;
            }

            if (empty($detalles)) {
                Yii::$app->session->setFlash('danger', 'Agrega al menos un producto a la venta.');
            } else {
                try {
                    if (VentaService::guardarConfirmada($model, $detalles)) {
                        Yii::$app->session->setFlash('success', "Venta {$model->codigo} registrada correctamente.");
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                    Yii::$app->session->setFlash('danger', 'No se pudo registrar la venta. Verifica los datos.');
                } catch (\RuntimeException $e) {
                    Yii::$app->session->setFlash('danger', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model'        => $model,
            'clientes'     => $this->listaClientes(),
            'tiposPago'    => $this->listaTipoPago(),
            'estadosPago'  => $this->listaEstadoPago(),
            'productosJson' => $this->productosJson(),
            'usuarioNombre' => $this->usuarioNombreActual(),
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    // ── Flujo de estados ─────────────────────────────────────────────────────

    /** Pedidos CONFIRMADOS (estado_venta_id = 1) — solo Almacén y Gerente */
    public function actionPorEnviar(): string
    {
        $user = Yii::$app->user->identity;
        if (!$user->isEncargado() && !$user->isGerente()) {
            throw new \yii\web\ForbiddenHttpException('Sin acceso.');
        }

        $hoy    = date('Y-m-d');
        $query  = Venta::find()->with(['cliente', 'user'])
            ->where(['estado_venta_id' => Venta::ESTADO_CONFIRMADA])
            ->orderBy(['fecha_entrega' => SORT_ASC, 'fecha_pedido' => SORT_ASC]);

        // KPIs
        $base        = Venta::find()->where(['estado_venta_id' => Venta::ESTADO_CONFIRMADA]);
        $kpiTotal    = (clone $base)->count();
        $kpiHoy      = (clone $base)->andWhere(['fecha_entrega' => $hoy])->count();
        $kpiAtrasado = (clone $base)->andWhere(['<', 'fecha_entrega', $hoy])->andWhere(['IS NOT', 'fecha_entrega', null])->count();

        // Filtros
        $req       = Yii::$app->request;
        $qSearch   = trim((string)$req->get('q', ''));
        $clienteId = (int)$req->get('cliente_id', 0);
        $desde     = trim((string)$req->get('desde', ''));
        $hasta     = trim((string)$req->get('hasta', ''));

        if ($qSearch !== '') {
            $query->andWhere(['like', 'v.codigo', $qSearch]);
        }
        if ($clienteId > 0) $query->andWhere(['cliente_id' => $clienteId]);
        if ($desde !== '')  $query->andWhere(['>=', 'fecha_pedido', $desde]);
        if ($hasta !== '')  $query->andWhere(['<=', 'fecha_pedido', $hasta]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 25],
            'sort'       => false,
        ]);

        return $this->render('por-enviar', [
            'dataProvider' => $dataProvider,
            'kpiTotal'     => $kpiTotal,
            'kpiHoy'       => $kpiHoy,
            'kpiAtrasado'  => $kpiAtrasado,
            'clientes'     => $this->listaClientes(),
            'filtros'      => compact('qSearch', 'clienteId', 'desde', 'hasta'),
        ]);
    }

    /** Pedidos ENVIADOS (estado_venta_id = 2) — Vendedor (propios), Almacén y Gerente */
    public function actionEnviados(): string
    {
        $user = Yii::$app->user->identity;
        if (!$user->isVendedor() && !$user->isEncargado() && !$user->isGerente()) {
            throw new \yii\web\ForbiddenHttpException('Sin acceso.');
        }

        $req       = Yii::$app->request;
        $clienteId = (int)$req->get('cliente_id', 0);
        $desde     = trim((string)$req->get('desde', ''));
        $hasta     = trim((string)$req->get('hasta', ''));

        $query = Venta::find()->with(['cliente', 'user'])
            ->where(['estado_venta_id' => Venta::ESTADO_ENVIADA])
            ->orderBy(['fecha_envio' => SORT_DESC]);

        if ($user->isVendedor()) {
            $query->andWhere(['user_id' => $user->id]);
        }
        if ($clienteId > 0) $query->andWhere(['cliente_id' => $clienteId]);
        if ($desde !== '')  $query->andWhere(['>=', 'fecha_pedido', $desde]);
        if ($hasta !== '')  $query->andWhere(['<=', 'fecha_pedido', $hasta]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 25],
            'sort'       => false,
        ]);

        return $this->render('enviados', [
            'dataProvider' => $dataProvider,
            'clientes'     => $this->listaClientes(),
            'filtros'      => compact('clienteId', 'desde', 'hasta'),
        ]);
    }

    /** Pedidos ENTREGADOS (estado_venta_id = 3) — todos los roles */
    public function actionEntregados(): string
    {
        $user = Yii::$app->user->identity;

        $req       = Yii::$app->request;
        $clienteId = (int)$req->get('cliente_id', 0);
        $userId    = (int)$req->get('user_id', 0);
        $desde     = trim((string)$req->get('desde', ''));
        $hasta     = trim((string)$req->get('hasta', ''));

        $query = Venta::find()->with(['cliente', 'user'])
            ->where(['estado_venta_id' => Venta::ESTADO_ENTREGADA])
            ->orderBy(['fecha_entrega_real' => SORT_DESC]);

        if ($user->isVendedor()) {
            $query->andWhere(['user_id' => $user->id]);
        } elseif ($userId > 0) {
            $query->andWhere(['user_id' => $userId]);
        }
        if ($clienteId > 0) $query->andWhere(['cliente_id' => $clienteId]);
        if ($desde !== '')  $query->andWhere(['>=', 'fecha_pedido', $desde]);
        if ($hasta !== '')  $query->andWhere(['<=', 'fecha_pedido', $hasta]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 25],
            'sort'       => false,
        ]);

        $vendedores = $user->isGerente() ? ArrayHelper::map(
            (new \yii\db\Query())
                ->from(['v' => '{{%venta}}'])->innerJoin(['u' => '{{%user}}'], 'u.id = v.user_id')
                ->select(['id' => 'u.id', 'nombre' => "CONCAT_WS(' ', u.paterno, u.materno, u.nombre)"])
                ->distinct()->all(),
            'id', 'nombre'
        ) : [];

        return $this->render('entregados', [
            'dataProvider' => $dataProvider,
            'clientes'     => $this->listaClientes(),
            'vendedores'   => $vendedores,
            'filtros'      => compact('clienteId', 'userId', 'desde', 'hasta'),
        ]);
    }

    /** Marca un pedido como Enviado (CONFIRMADA → ENVIADA). Solo Almacén y Gerente */
    public function actionMarcarEnviado(int $id)
    {
        $user = Yii::$app->user->identity;
        if (!$user->isEncargado() && !$user->isGerente()) {
            throw new \yii\web\ForbiddenHttpException('Sin acceso.');
        }
        $model = $this->findModel($id);
        if ($model->estado_venta_id === Venta::ESTADO_CONFIRMADA) {
            $model->estado_venta_id = Venta::ESTADO_ENVIADA;
            $model->fecha_envio     = date('Y-m-d H:i:s');
            $model->save(false);
            Yii::$app->session->setFlash('success', "Pedido {$model->codigo} marcado como Enviado.");
        }
        return $this->redirect(['por-enviar']);
    }

    /** Marca un pedido como Entregado (ENVIADA → ENTREGADA). Almacén y Gerente */
    public function actionMarcarEntregado(int $id)
    {
        $user = Yii::$app->user->identity;
        if (!$user->isEncargado() && !$user->isGerente()) {
            throw new \yii\web\ForbiddenHttpException('Sin acceso.');
        }
        $model = $this->findModel($id);
        if ($user->isVendedor() && $model->user_id !== (int)$user->id) {
            throw new \yii\web\ForbiddenHttpException('Solo puedes marcar tus propios pedidos.');
        }
        if ($model->estado_venta_id === Venta::ESTADO_ENVIADA) {
            $model->estado_venta_id    = Venta::ESTADO_ENTREGADA;
            $model->fecha_entrega_real = date('Y-m-d H:i:s');
            $model->save(false);
            Yii::$app->session->setFlash('success', "Pedido {$model->codigo} marcado como Entregado.");
        }
        return $this->redirect(['enviados']);
    }

    /** Vista detalle completo de un pedido con líneas DetalleVenta */
    public function actionDetallePedido(int $id): string
    {
        $model = $this->findModel($id);
        return $this->render('detalle-pedido', ['model' => $model]);
    }

    /**
     * Listado general de entregas con filtros.
     */
    public function actionListado(): string
    {
        $req       = Yii::$app->request;
        $estadoId  = (int)$req->get('estado_venta_id', 0);
        $clienteId = (int)$req->get('cliente_id', 0);
        $userId    = (int)$req->get('user_id', 0);
        $desde     = trim((string)$req->get('desde', ''));
        $hasta     = trim((string)$req->get('hasta', ''));

        $query = Venta::find()
            ->with(['cliente', 'user'])
            ->orderBy(['fecha_pedido' => SORT_DESC, 'id' => SORT_DESC]);

        if ($estadoId > 0)  $query->andWhere(['estado_venta_id' => $estadoId]);
        if ($clienteId > 0) $query->andWhere(['cliente_id' => $clienteId]);
        if ($userId > 0)    $query->andWhere(['user_id' => $userId]);
        if ($desde !== '')  $query->andWhere(['>=', 'fecha_pedido', $desde]);
        if ($hasta !== '')  $query->andWhere(['<=', 'fecha_pedido', $hasta]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 30],
            'sort'       => false,
        ]);

        $clientes  = $this->listaClientes();
        $vendedores = ArrayHelper::map(
            (new \yii\db\Query())
                ->from(['v' => '{{%venta}}'])
                ->innerJoin(['u' => '{{%user}}'], 'u.id = v.user_id')
                ->select(['id' => 'u.id', 'nombre' => "CONCAT_WS(' ', u.paterno, u.materno, u.nombre)"])
                ->distinct()
                ->all(),
            'id', 'nombre'
        );

        return $this->render('listado', [
            'dataProvider' => $dataProvider,
            'clientes'     => $clientes,
            'vendedores'   => $vendedores,
            'filtros'      => compact('estadoId', 'clienteId', 'userId', 'desde', 'hasta'),
        ]);
    }

    /**
     * Imprime la orden de despacho de un pedido (layout especial sin sidebar).
     */
    public function actionImprimirOrden(int $id): string
    {
        $model = $this->findModel($id);
        $this->layout = '@app/views/layouts/print';
        return $this->render('orden-despacho', ['model' => $model]);
    }

    /** Endpoint AJAX: busca clientes por nombre o CI/NIT */
    public function actionBuscarCliente(string $q = ''): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = Cliente::find()->orderBy(['razon_social' => SORT_ASC]);

        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'razon_social', $q],
                ['like', 'nombre_completo', $q],
                ['like', 'nit', $q],
            ]);
        }

        $items = [];
        foreach ($query->limit(15)->all() as $c) {
            $items[] = [
                'id'       => $c->id,
                'nombre'   => $c->razon_social ?? '(sin nombre)',
                'ci_nit'   => $c->nit ?? '',
                'contacto' => $c->nombre_completo ?? '',
            ];
        }

        return $this->asJson($items);
    }

    /** Endpoint AJAX: busca productos por código o nombre */
    public function actionBuscarProducto(string $q = ''): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = Producto::find()
            ->where(['>', 'stock', 0])
            ->orderBy(['nombre' => SORT_ASC]);

        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'nombre', $q],
                ['like', 'codigo', $q],
            ]);
        }

        $items = [];
        foreach ($query->limit(30)->all() as $p) {
            $items[] = [
                'id'          => $p->id,
                'codigo'      => $p->codigo ?? '',
                'nombre'      => $p->nombre,
                'stock'       => (int)$p->stock,
                'stock_minimo'=> (int)$p->stock_minimo,
                'precio'      => (float)$p->precio_venta,
                'categoria'   => $p->categoria ? $p->categoria->nombre : '',
            ];
        }

        return $this->asJson($items);
    }

    /** Endpoint AJAX: lotes disponibles de un producto en orden LIFO (más reciente primero) */
    public function actionLotes(int $producto_id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rows = (new \yii\db\Query())
            ->from(['l' => '{{%lote}}'])
            ->leftJoin(['a' => '{{%almacen}}'], 'a.id = l.almacen_id')
            ->select([
                'id'                    => 'l.id',
                'fecha_ingreso'         => 'l.fecha_ingreso',
                'cantidad_inicial'      => 'l.cantidad_inicial',
                'cantidad_disponible'   => 'l.cantidad_disponible',
                'precio_compra_unitario' => 'l.precio_compra_unitario',
                'almacen'               => 'a.nombre',
            ])
            ->where(['l.producto_id' => $producto_id, 'l.estado' => 1])
            ->andWhere(['>', 'l.cantidad_disponible', 0])
            ->orderBy(['l.fecha_ingreso' => SORT_DESC, 'l.id' => SORT_DESC])
            ->all();

        return $this->asJson($rows);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function findModel(int $id): Venta
    {
        $model = Venta::find()->with(['cliente', 'user', 'detalles.producto'])->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('No existe la venta.');
        }
        return $model;
    }

    private function listaClientes(): array
    {
        return ArrayHelper::map(
            Cliente::find()->orderBy(['razon_social' => SORT_ASC])->all(),
            'id',
            static fn(Cliente $c): string => $c->getNombreDisplay()
        );
    }

    private function listaSimple(string $table): array
    {
        $rows = Yii::$app->db->createCommand("SELECT id, nombre FROM {{%{$table}}} ORDER BY id")->queryAll();
        return ArrayHelper::map($rows, 'id', 'nombre');
    }

    /** tipo_pago: solo Contado */
    private function listaTipoPago(): array
    {
        return [1 => 'Contado'];
    }

    /** estado_pago: solo Pagado */
    private function listaEstadoPago(): array
    {
        return [2 => 'Pagado'];
    }

    private function usuarioNombreActual(): string
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            return '';
        }
        return trim(implode(' ', array_filter([$user->paterno, $user->materno, $user->nombre]))) ?: $user->username;
    }

    private function productosJson(): array
    {
        $items = [];
        foreach (Producto::find()->with('categoria')->orderBy(['nombre' => SORT_ASC])->all() as $p) {
            $items[] = [
                'id'          => $p->id,
                'codigo'      => $p->codigo ?? 'BG-P' . $p->id,
                'nombre'      => $p->nombre,
                'stock'       => (int)$p->stock,
                'stock_minimo'=> (int)$p->stock_minimo,
                'precio'      => (float)$p->precio_venta,
                'categoria'   => $p->categoria ? $p->categoria->nombre : '',
            ];
        }
        return $items;
    }
}
