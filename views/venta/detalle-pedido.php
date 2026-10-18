<?php
/**
 * @var yii\web\View    $this
 * @var app\models\Venta $model
 */
use yii\bootstrap5\Html;

$this->title = 'Detalle Pedido: ' . $model->codigo;

$estadoVentaLabel = match ((int)$model->estado_venta_id) {
    1 => ['Para Entregar', 'warning',  'text-dark'],
    2 => ['Enviada',       'info',     'text-dark'],
    3 => ['Entregada',     'success',  ''],
    default => ['Desconocido', 'secondary', ''],
};
$estadoPagoLabel = $model->estado_pago_id === 2
    ? ['Pagado',    'success']
    : ['Pendiente', 'secondary'];
$tipoPagoLabel = match ((int)$model->tipo_pago_id) {
    1 => 'Efectivo',
    2 => 'Crédito',
    3 => 'Transferencia',
    default => '—',
};
?>

<style>
.dp-title-icon { color: var(--df-azul2); }
.dp-card { background: var(--df-card-bg); border-color: var(--df-card-border); }
.dp-card-header-primary {
    background: var(--df-azul2);
    color: #fff;
    font-size: .88rem;
}
.dp-card-header-success {
    background: #198754;
    color: #fff;
    font-size: .88rem;
}
.dp-card-header-neutral {
    background: var(--df-table-head);
    color: var(--df-text);
    font-size: .88rem;
    border-bottom: 1px solid var(--df-table-border);
}
.dp-table,
.dp-table th,
.dp-table td {
    color: var(--df-text);
}
.dp-table tbody th {
    background: var(--df-table-head) !important;
    color: var(--df-text-muted) !important;
    border-bottom: 1px solid var(--df-table-border);
}
.dp-table tbody td {
    background: var(--df-card-bg) !important;
    border-bottom: 1px solid var(--df-table-border);
}
.dp-table tfoot th,
.dp-table tfoot td {
    background: var(--df-table-head) !important;
    border-top: 1px solid var(--df-table-border);
}
.dp-table th.text-muted,
.dp-table td.text-muted {
    color: var(--df-text-muted) !important;
}
.dp-total-row {
    background: var(--df-table-head) !important;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-file-invoice me-2 dp-title-icon"></i><?= Html::encode($this->title) ?>
    </h4>
    <div class="d-flex gap-2">
        <?php
        // Back button based on estado
        $backUrl  = match ((int)$model->estado_venta_id) {
            1 => ['por-enviar'],
            2 => ['enviados'],
            3 => ['entregados'],
            default => ['index'],
        };
        ?>
        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', $backUrl, ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Resumen del pedido -->
    <div class="col-md-6">
        <div class="card dp-card border-0 shadow-sm h-100">
            <div class="card-header dp-card-header-primary py-2 px-3 fw-bold">
                <i class="fa-solid fa-receipt me-2"></i>Resumen del Pedido
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 dp-table" style="font-size:.85rem">
                    <tr>
                        <th class="text-muted ps-3" style="width:150px">Código</th>
                        <td><span class="badge bg-primary"><?= Html::encode($model->codigo) ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Cliente</th>
                        <td><strong><?= Html::encode($model->cliente ? $model->cliente->getNombreDisplay() : '—') ?></strong></td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Fecha Pedido</th>
                        <td><?= $model->fecha_pedido ? date('d/m/Y', strtotime($model->fecha_pedido)) : '—' ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Entrega</th>
                        <td><?= $model->fecha_entrega ? date('d/m/Y', strtotime($model->fecha_entrega)) : '—' ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Vendedor</th>
                        <td><?= Html::encode($model->user ? $model->user->getNombreCompleto() : '—') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Tipo Pago</th>
                        <td><?= Html::encode($tipoPagoLabel) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Estado Venta</th>
                        <td>
                            <span class="badge bg-<?= $estadoVentaLabel[1] ?> <?= $estadoVentaLabel[2] ?>">
                                <?= Html::encode($estadoVentaLabel[0]) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Estado Pago</th>
                        <td>
                            <span class="badge bg-<?= $estadoPagoLabel[1] ?>">
                                <?= Html::encode($estadoPagoLabel[0]) ?>
                            </span>
                        </td>
                    </tr>
                    <?php if ($model->nota): ?>
                    <tr>
                        <th class="text-muted ps-3">Nota</th>
                        <td><?= Html::encode($model->nota) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Resumen de costos -->
    <div class="col-md-6">
        <div class="card dp-card border-0 shadow-sm h-100">
            <div class="card-header dp-card-header-success py-2 px-3 fw-bold">
                <i class="fa-solid fa-coins me-2"></i>Detalle de Costos
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 dp-table" style="font-size:.85rem">
                    <tr>
                        <th class="text-muted ps-3" style="width:160px">Subtotal</th>
                        <td class="text-end pe-3"><?= number_format($model->importe_total + $model->descuento, 2) ?> Bs.</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Descuento</th>
                        <td class="text-end pe-3 text-danger">
                            <?= $model->descuento > 0 ? '− ' . number_format($model->descuento, 2) . ' Bs.' : '—' ?>
                        </td>
                    </tr>
                    <tr class="dp-total-row">
                        <th class="ps-3 text-primary fw-bold">Total Final</th>
                        <td class="text-end pe-3 fw-bold text-primary"><?= number_format($model->importe_total, 2) ?> Bs.</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Total Efectivo</th>
                        <td class="text-end pe-3"><?= number_format($model->total_efectivo, 2) ?> Bs.</td>
                    </tr>
                    <tr>
                        <th class="text-muted ps-3">Gestión</th>
                        <td><?= Html::encode((string)$model->gestion) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Líneas del pedido -->
<div class="card dp-card border-0 shadow-sm">
    <div class="card-header dp-card-header-neutral py-2 px-3 fw-bold">
        <i class="fa-solid fa-list me-2"></i>Productos del Pedido
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-hover table-bordered align-middle mb-0 dp-table" style="font-size:.83rem">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Código Venta</th>
                <th>Código Producto</th>
                <th>Detalle (Nombre)</th>
                <th class="text-center">Cantidad</th>
                <th class="text-end">Precio Unit.</th>
                <th class="text-end">Importe</th>
                <th>Descripción / Notas</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            foreach ($model->detalles as $d):
                $prod = $d->producto;
                $codigoProd = $prod ? ($prod->codigo ?? 'BG-P' . $prod->id) : '—';
                $nombre = $prod ? $prod->nombre : Html::encode($d->producto_recibo_venta ?? '—');
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><span class="badge bg-primary" style="font-size:.73rem"><?= Html::encode($model->codigo) ?></span></td>
                <td>
                    <span class="badge bg-secondary" style="font-size:.73rem"><?= Html::encode($codigoProd) ?></span>
                    <?php if ($prod): ?>
                    <span class="text-muted" style="font-size:.73rem">| <?= Html::encode($prod->nombre) ?></span>
                    <?php endif; ?>
                </td>
                <td><strong><?= Html::encode($nombre) ?></strong></td>
                <td class="text-center"><?= $d->cantidad ?></td>
                <td class="text-end"><?= number_format((float)$d->precio_unitario, 2) ?> Bs.</td>
                <td class="text-end fw-bold"><?= number_format((float)$d->importe, 2) ?> Bs.</td>
                <td class="text-muted"><?= Html::encode($d->descripcion ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($model->detalles)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Sin líneas de detalle</td></tr>
            <?php endif; ?>
            </tbody>
            <tfoot class="table-light">
            <tr>
                <th colspan="6" class="text-end pe-3">Total</th>
                <th class="text-end text-primary"><?= number_format((float)$model->importe_total, 2) ?> Bs.</th>
                <th></th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php
/** @var \app\models\User $authUser */
$authUser = Yii::$app->user->identity;
?>

<?php if ((int)$model->estado_venta_id === \app\models\Venta::ESTADO_CONFIRMADA && ($authUser->isEncargado() || $authUser->isGerente())): ?>
<div class="mt-4 text-center">
    <button type="button" class="btn btn-success btn-lg px-5" data-bs-toggle="modal" data-bs-target="#modalConfirmarEnvio">
        <i class="fa-solid fa-truck me-2"></i>CONFIRMAR ENVÍO
    </button>
</div>

<!-- Modal confirmar envío -->
<div class="modal fade" id="modalConfirmarEnvio" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--df-card-bg);border-color:var(--df-card-border)">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fa-solid fa-truck me-2 text-success"></i>Confirmar Envío</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-box fa-3x text-success mb-3 d-block"></i>
                <p class="fs-5">¿El paquete está listo y fue despachado?</p>
                <p class="text-muted small">Pedido: <strong><?= Html::encode($model->codigo) ?></strong><br>
                Cliente: <strong><?= Html::encode($model->cliente?->getNombreDisplay() ?? '—') ?></strong></p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <?= Html::a('<i class="fa-solid fa-circle-check me-1"></i>Sí, confirmar', ['marcar-enviado', 'id' => $model->id], ['class' => 'btn btn-success btn-lg px-4']) ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ((int)$model->estado_venta_id === \app\models\Venta::ESTADO_ENVIADA && ($authUser->isVendedor() && $model->user_id === (int)$authUser->id || $authUser->isGerente())): ?>
<div class="mt-4 text-center">
    <button type="button" class="btn btn-primary btn-lg px-5" data-bs-toggle="modal" data-bs-target="#modalConfirmarEntrega">
        <i class="fa-solid fa-circle-check me-2"></i>CONFIRMAR ENTREGA
    </button>
</div>

<!-- Modal confirmar entrega -->
<div class="modal fade" id="modalConfirmarEntrega" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--df-card-bg);border-color:var(--df-card-border)">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fa-solid fa-circle-check me-2 text-primary"></i>Confirmar Entrega</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-handshake fa-3x text-primary mb-3 d-block"></i>
                <p class="fs-5">¿El cliente recibió el pedido?</p>
                <p class="text-muted small">Pedido: <strong><?= Html::encode($model->codigo) ?></strong><br>
                Cliente: <strong><?= Html::encode($model->cliente?->getNombreDisplay() ?? '—') ?></strong></p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <?= Html::a('<i class="fa-solid fa-circle-check me-1"></i>Sí, entregado', ['marcar-entregado', 'id' => $model->id], ['class' => 'btn btn-primary btn-lg px-4']) ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

