<?php

/** @var yii\web\View $this */
/** @var array $rows */
/** @var int $mes */
/** @var int $anio */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Reporte: Ranking Vendedores';
$mesesNombre = [
    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
    7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
];
$totalGeneral = 0;
foreach ($rows as $r) $totalGeneral += (float)$r['monto_total'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-ranking-star me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <span class="text-muted small">Se cuentan ventas <strong>Enviadas</strong> y <strong>Entregadas</strong></span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Mes</label>
                <select name="mes" class="form-select form-select-sm">
                    <?php foreach ($mesesNombre as $i => $n): ?>
                        <option value="<?= $i ?>" <?= $i === $mes ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Año</label>
                <input type="number" name="anio" min="2020" max="2099" value="<?= (int)$anio ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Aplicar</button>
                <a href="<?= Url::to(['personal']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="text-muted small">Periodo</div>
                <div class="kpi-value text-primary"><?= $mesesNombre[$mes] ?? $mes ?> <?= $anio ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="text-muted small">Vendedores</div>
                <div class="kpi-value"><?= count($rows) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card">
            <div class="card-body">
                <div class="text-muted small">Total Vendido</div>
                <div class="kpi-value text-success"><?= number_format($totalGeneral, 2) ?> Bs</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table df-separated-table align-middle">
            <thead>
                <tr>
                    <th style="width:60px" class="text-center">#</th>
                    <th>Vendedor</th>
                    <th class="text-center">Ventas Efectivas</th>
                    <th class="text-end">Monto Total</th>
                    <th class="text-end">Promedio / Venta</th>
                    <th>Última Venta</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Sin ventas efectivas en el periodo seleccionado.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td class="text-center fw-bold">
                                <?php if ($i === 0): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-trophy"></i> 1°</span>
                                <?php elseif ($i === 1): ?>
                                    <span class="badge bg-secondary">2°</span>
                                <?php elseif ($i === 2): ?>
                                    <span class="badge" style="background:#cd7f32;color:#fff">3°</span>
                                <?php else: ?>
                                    <?= $i + 1 ?>°
                                <?php endif; ?>
                            </td>
                            <td><i class="fa-solid fa-user me-1 text-secondary"></i><?= Html::encode($r['vendedor']) ?></td>
                            <td class="text-center"><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= (int)$r['num_ventas'] ?></span></td>
                            <td class="text-end fw-bold text-success"><?= number_format((float)$r['monto_total'], 2) ?> Bs</td>
                            <td class="text-end"><?= number_format((float)$r['promedio'], 2) ?> Bs</td>
                            <td><?= $r['fecha_ultima'] ? date('d/m/Y', strtotime($r['fecha_ultima'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
