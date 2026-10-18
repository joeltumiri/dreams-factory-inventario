<?php

/** @var yii\web\View $this */
/** @var array $rows */
/** @var array $tipos */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Reporte Ventas: Por Cliente';

$totVol = 0; $totPed = 0;
foreach ($rows as $r) { $totVol += (float)$r['volumen']; $totPed += (int)$r['num_pedidos']; }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-people-group me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <span class="text-muted small">Solo ventas <strong>Entregadas</strong></span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Tipo</label>
                <select name="tipo_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= (int)$filtros['tipoId'] === (int)$t['id'] ? 'selected' : '' ?>><?= Html::encode($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Desde</label>
                <input type="date" name="desde" value="<?= Html::encode($filtros['desde']) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Hasta</label>
                <input type="date" name="hasta" value="<?= Html::encode($filtros['hasta']) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="<?= Url::to(['ventas-clientes']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Clientes activos</div>
            <div class="kpi-value text-primary"><?= count($rows) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Pedidos totales</div>
            <div class="kpi-value"><?= (int)$totPed ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Volumen total</div>
            <div class="kpi-value text-success"><?= number_format($totVol, 2) ?> Bs</div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table df-separated-table align-middle">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Ciudad</th>
                    <th class="text-center"># Pedidos</th>
                    <th class="text-end">Volumen Total</th>
                    <th class="text-end">Promedio / Pedido</th>
                    <th>Última compra</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Sin ventas en el periodo seleccionado.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><i class="fa-solid fa-store me-1 text-secondary"></i><?= Html::encode($r['cliente']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= Html::encode($r['tipo'] ?: '—') ?></span></td>
                        <td><?= Html::encode($r['ciudad'] ?: '—') ?></td>
                        <td class="text-center"><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= (int)$r['num_pedidos'] ?></span></td>
                        <td class="text-end fw-bold text-success"><?= number_format((float)$r['volumen'], 2) ?> Bs</td>
                        <td class="text-end"><?= number_format((float)$r['promedio'], 2) ?> Bs</td>
                        <td><?= Html::encode($r['ultima_fecha']) ?></td>
                        <td>
                            <?= Html::a('<i class="fa-solid fa-file-pdf"></i>', Url::to(['reporte/pdf-cliente', 'id' => $r['cliente_id'], 'desde' => $filtros['desde'], 'hasta' => $filtros['hasta']]), ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'Ver PDF', 'target' => '_blank']) ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
