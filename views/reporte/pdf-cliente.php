<?php

/** @var yii\web\View $this */
/** @var array $cliente   { id, razon_social, propietario, telefono, ciudad, direccion } */
/** @var array $ventas    Rows from query */
/** @var float $total */
/** @var string $desde */
/** @var string $hasta */

use yii\helpers\Html;

$label = Html::encode($cliente['razon_social']);
?>
<style>
    .pv-info {
        padding: 16px 24px 10px;
        font-size: 12px;
        line-height: 1.9;
        color: #222;
    }
    .pv-info strong { font-weight: 700; }
    .pv-info .lbl { font-weight: 700; color: #555; letter-spacing: .5px; font-size: 10px; text-transform: uppercase; }

    .pv-table-wrap { padding: 10px 24px 20px; }
    .pv-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11.5px;
    }
    .pv-table thead tr {
        background: #e0e0e0;
    }
    .pv-table thead th {
        padding: 7px 10px;
        text-align: left;
        font-weight: 700;
        font-size: 11px;
        letter-spacing: .4px;
        border-bottom: 2px solid #bbb;
    }
    .pv-table thead th.num { text-align: center; }
    .pv-table thead th.money { text-align: right; }
    .pv-table tbody tr:nth-child(even) { background: #f2f2f2; }
    .pv-table tbody tr:nth-child(odd)  { background: #ffffff; }
    .pv-table tbody td {
        padding: 6px 10px;
        border-bottom: 1px solid #e2e2e2;
        vertical-align: middle;
    }
    .pv-table tbody td.num   { text-align: center; }
    .pv-table tbody td.money { text-align: right; }
    .pv-table tfoot td {
        padding: 8px 10px;
        font-weight: 700;
        font-size: 13px;
        text-align: right;
        border-top: 2px solid #aaa;
    }

    @media print {
        .pv-info, .pv-table-wrap { padding-left: 16px; padding-right: 16px; }
    }
</style>

<div class="print-title-band">
    <h2>Detalle Ventas Cliente</h2>
</div>

<div class="pv-info">
    <div><span class="lbl">Farmacia:</span>&nbsp; <strong><?= $label ?></strong></div>
    <div><span class="lbl">Regente Encargado:</span>&nbsp; <?= Html::encode($cliente['propietario']) ?></div>
    <div><span class="lbl">Contacto:</span>&nbsp; <?= Html::encode($cliente['telefono'] ?: '—') ?></div>
    <div><span class="lbl">Dirección:</span>&nbsp; <?= Html::encode($cliente['direccion'] ?: '—') ?><?= $cliente['ciudad'] ? ' | ' . Html::encode($cliente['ciudad']) : '' ?></div>
    <div><span class="lbl">Ventas Generadas:</span>&nbsp; <?= count($ventas) ?></div>
    <?php if ($desde || $hasta): ?>
        <div><span class="lbl">Desde Fecha:</span>&nbsp; <?= Html::encode($desde ?: '—') ?></div>
        <div><span class="lbl">Hasta Fecha:</span>&nbsp; <?= Html::encode($hasta ?: '—') ?></div>
    <?php endif; ?>
</div>

<div class="pv-table-wrap">
    <table class="pv-table">
        <thead>
            <tr>
                <th class="num">ITEM</th>
                <th>FECHA_PEDIDO</th>
                <th>FECHA_ENTREGA</th>
                <th>ESTADO_VENTA</th>
                <th>ESTADO_PAGO</th>
                <th class="money">IMPORTE</th>
                <th class="money">DESCUENTO</th>
                <th class="money">IMPORTE_PAGADO</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$ventas): ?>
            <tr><td colspan="8" style="text-align:center;padding:20px;color:#888;">Sin ventas en el periodo seleccionado.</td></tr>
        <?php else: foreach ($ventas as $i => $v): ?>
            <tr>
                <td class="num"><?= $i + 1 ?></td>
                <td><?= Html::encode($v['fecha_pedido']) ?></td>
                <td><?= Html::encode($v['fecha_entrega'] ?: '—') ?></td>
                <td><strong><?= Html::encode(strtoupper($v['estado_venta'] ?: '—')) ?></strong></td>
                <td><?= Html::encode(strtoupper($v['estado_pago'] ?: '—')) ?></td>
                <td class="money"><?= number_format((float)$v['importe'], 2) ?></td>
                <td class="money"><?= number_format((float)$v['descuento'], 2) ?></td>
                <td class="money"><?= number_format((float)$v['importe_pagado'], 2) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right;">Total Bs:</td>
                <td><?= number_format($total, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>
