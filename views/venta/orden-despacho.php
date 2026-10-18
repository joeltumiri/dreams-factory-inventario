<?php

/** @var yii\web\View $this */
/** @var app\models\Venta $model */

use yii\helpers\Html;

$this->title = 'Orden de despacho — ' . $model->codigo;
$fmt = Yii::$app->formatter;
?>
<div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-3">
    <div>
        <h2 class="m-0 fw-bold">Dreams Factory</h2>
        <small class="text-muted">El Alto · Bolivia</small>
    </div>
    <div class="text-end">
        <h4 class="m-0 text-primary">ORDEN DE DESPACHO</h4>
        <div class="fw-bold"><?= Html::encode($model->codigo) ?></div>
        <small class="text-muted">Emisión: <?= date('d/m/Y H:i') ?></small>
    </div>
</div>

<table class="table table-sm">
    <tr>
        <th style="width:25%">Cliente</th>
        <td><?= Html::encode($model->cliente?->getNombreDisplay() ?: '—') ?></td>
        <th style="width:18%">Fecha pedido</th>
        <td><?= $model->fecha_pedido ? Html::encode(date('d/m/Y', strtotime($model->fecha_pedido))) : '—' ?></td>
    </tr>
    <tr>
        <th>Dirección</th>
        <td><?= Html::encode(($model->cliente?->direccion ?: '—') . ' · ' . ($model->cliente?->zona ?: '')) ?></td>
        <th>Fecha entrega</th>
        <td><?= $model->fecha_entrega ? Html::encode(date('d/m/Y', strtotime($model->fecha_entrega))) : '—' ?></td>
    </tr>
    <tr>
        <th>Contacto</th>
        <td><?= Html::encode($model->cliente?->contacto ?: '—') ?></td>
        <th>Vendedor</th>
        <td><?= Html::encode($model->user?->getNombreCompleto() ?: '—') ?></td>
    </tr>
</table>

<h6 class="mt-4 mb-2 fw-bold">Detalle de productos a despachar</h6>
<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Código</th>
            <th>Producto</th>
            <th class="text-center">Cantidad</th>
            <th class="text-end">P. Unitario</th>
            <th class="text-end">Importe</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model->detalles as $i => $d): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= Html::encode($d->producto?->codigo ?: 'BG-P' . (int)$d->producto_id) ?></td>
                <td><?= Html::encode($d->producto?->nombre ?: '—') ?></td>
                <td class="text-center"><?= (int)$d->cantidad ?></td>
                <td class="text-end"><?= number_format((float)$d->precio_unitario, 2) ?> Bs</td>
                <td class="text-end"><?= number_format((float)$d->importe, 2) ?> Bs</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-end">Subtotal</th>
            <th class="text-end"><?= number_format((float)$model->importe_total, 2) ?> Bs</th>
        </tr>
        <?php if ((float)$model->descuento > 0): ?>
        <tr>
            <th colspan="5" class="text-end">Descuento</th>
            <th class="text-end text-danger">-<?= number_format((float)$model->descuento, 2) ?> Bs</th>
        </tr>
        <?php endif; ?>
        <tr>
            <th colspan="5" class="text-end">TOTAL</th>
            <th class="text-end fs-5"><?= number_format((float)$model->total_efectivo, 2) ?> Bs</th>
        </tr>
    </tfoot>
</table>

<div class="row mt-5 pt-4">
    <div class="col-6 text-center">
        <div style="border-top:1px solid #444; width:80%; margin:0 auto; padding-top:.4rem;">
            <small>Entrega · Encargado de despacho</small>
        </div>
    </div>
    <div class="col-6 text-center">
        <div style="border-top:1px solid #444; width:80%; margin:0 auto; padding-top:.4rem;">
            <small>Recibe conforme · Cliente</small>
        </div>
    </div>
</div>

<script>window.addEventListener('load', () => setTimeout(() => window.print(), 350));</script>
