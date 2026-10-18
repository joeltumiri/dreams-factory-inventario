<?php
/**
 * @var yii\web\View    $this
 * @var app\models\Venta $model
 */

use yii\bootstrap5\Html;

$this->title = $model->codigo;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-receipt me-2 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="row g-3">
    <!-- ── Sale summary card ─────────────────────────────────────────────── -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="fa-solid fa-circle-info me-1 text-primary"></i> Datos de la Venta
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="ps-3">Código</th>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-semibold">
                                <?= Html::encode($model->codigo) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Fecha pedido</th>
                        <td><?= Html::encode(date('d/m/Y', strtotime($model->fecha_pedido))) ?></td>
                    </tr>
                    <?php if ($model->fecha_entrega): ?>
                    <tr>
                        <th class="ps-3">Fecha entrega</th>
                        <td><?= Html::encode(date('d/m/Y', strtotime($model->fecha_entrega))) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th class="ps-3">Cliente</th>
                        <td><?= $model->cliente ? Html::encode($model->cliente->getNombreDisplay()) : '—' ?></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Vendedor</th>
                        <td><?= $model->user ? Html::encode($model->user->getNombreCompleto()) : '—' ?></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Tipo pago</th>
                        <td>
                            <?php
                            $tpNames = [1 => 'Efectivo', 2 => 'Crédito', 3 => 'Transferencia'];
                            echo Html::encode($tpNames[(int)$model->tipo_pago_id] ?? '-');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Estado pago</th>
                        <td>
                            <?php if ((int)$model->estado_pago_id === 2): ?>
                                <span class="badge bg-success">Pagado</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Estado venta</th>
                        <td>
                            <?php
                            $evColors = [1 => 'warning', 2 => 'info', 3 => 'success'];
                            $evNames  = [1 => 'Para Entregar', 2 => 'Enviada', 3 => 'Entregada'];
                            $evId     = (int)$model->estado_venta_id;
                            echo '<span class="badge bg-' . ($evColors[$evId] ?? 'secondary') . '">'
                                . Html::encode($evNames[$evId] ?? '-') . '</span>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Gestión</th>
                        <td><?= Html::encode((string)$model->gestion) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ── Cost summary ──────────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold">
                <i class="fa-solid fa-receipt me-1 text-success"></i> Resumen de Costo
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-semibold"><?= number_format((float)$model->importe_total, 2) ?> Bs.</span>
                </div>
                <?php if ((float)$model->descuento > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Descuento</span>
                    <span class="text-danger fw-semibold">-<?= number_format((float)$model->descuento, 2) ?> Bs.</span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between border-top pt-2 mt-1">
                    <span class="fw-bold text-primary">Total</span>
                    <span class="fw-bold text-primary fs-5"><?= number_format((float)$model->total_efectivo, 2) ?> Bs.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Line items ───────────────────────────────────────────────────── -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="fa-solid fa-list-ul me-1 text-primary"></i> Detalle de Productos
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-3">Código</th>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio unit.</th>
                        <th class="text-end pe-3">Importe</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($model->detalles as $d): ?>
                    <tr>
                        <td class="ps-3">
                            <?php if ($d->producto): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size:.75rem">
                                    <?= Html::encode($d->producto->codigo ?? 'BG-P' . $d->producto->id) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= $d->producto ? Html::encode($d->producto->nombre) : Html::encode($d->producto_recibo_venta ?? '-') ?></td>
                        <td class="text-center"><?= (int)$d->cantidad ?></td>
                        <td class="text-end"><?= number_format((float)$d->precio_unitario, 2) ?> Bs.</td>
                        <td class="text-end pe-3 fw-bold"><?= number_format((float)$d->importe, 2) ?> Bs.</td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold pe-2">TOTAL</td>
                        <td class="text-end pe-3 fw-bold text-success">
                            <?= number_format((float)$model->total_efectivo, 2) ?> Bs.
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if ($model->nota): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold">
                <i class="fa-solid fa-note-sticky me-1"></i> Observación
            </div>
            <div class="card-body text-muted">
                <?= Html::encode($model->nota) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
