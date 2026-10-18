<?php

/** @var yii\web\View $this */
/** @var array $top */
/** @var float $totalGlobal */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Reporte Ventas: Resumen Clientes (Top 10)';
$max = 0;
foreach ($top as $r) $max = max($max, (float)$r['total']);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-trophy me-2 text-warning"></i><?= Html::encode($this->title) ?></h4>
    <span class="text-muted small">Solo ventas <strong>Entregadas</strong></span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Desde</label>
                <input type="date" name="desde" value="<?= Html::encode($filtros['desde']) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Hasta</label>
                <input type="date" name="hasta" value="<?= Html::encode($filtros['hasta']) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Aplicar</button>
                <a href="<?= Url::to(['resumen-clientes']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small">Total ventas (entregadas) en el periodo</div>
                <div class="fs-3 fw-bold text-success"><?= number_format($totalGlobal, 2) ?> Bs</div>
            </div>
            <div class="text-end">
                <div class="text-muted small">Top 10 representa</div>
                <?php
                    $top10sum = 0;
                    foreach ($top as $r) $top10sum += (float)$r['total'];
                    $pctTop = $totalGlobal > 0 ? ($top10sum * 100 / $totalGlobal) : 0;
                ?>
                <div class="fs-4 fw-bold text-primary"><?= number_format($pctTop, 1) ?>%</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (!$top): ?>
            <div class="text-center text-muted py-4">Sin ventas en el periodo seleccionado.</div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($top as $i => $r):
                    $total = (float)$r['total'];
                    $pctRel = $max > 0 ? ($total * 100 / $max) : 0;
                    $pctTot = $totalGlobal > 0 ? ($total * 100 / $totalGlobal) : 0;
                    $color = $i === 0 ? 'bg-warning' : ($i === 1 ? 'bg-secondary' : ($i === 2 ? 'bg-danger-subtle' : 'bg-primary'));
                    $medal = $i === 0 ? '<i class="fa-solid fa-trophy text-warning"></i>'
                           : ($i === 1 ? '<i class="fa-solid fa-medal text-secondary"></i>'
                           : ($i === 2 ? '<i class="fa-solid fa-medal" style="color:#cd7f32"></i>' : ('<span class="fw-bold text-muted">' . ($i + 1) . '°</span>')));
                ?>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="me-2" style="display:inline-block;width:32px;text-align:center"><?= $medal ?></span>
                                <strong><?= Html::encode($r['cliente']) ?></strong>
                                <small class="text-muted ms-2">
                                    <?= Html::encode($r['tipo'] ?: '—') ?>
                                    <?php if ($r['ciudad']): ?>· <?= Html::encode($r['ciudad']) ?><?php endif; ?>
                                    · <?= (int)$r['pedidos'] ?> pedidos
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success"><?= number_format($total, 2) ?> Bs</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1"><?= number_format($pctTot, 1) ?>%</span>
                            </div>
                        </div>
                        <div class="progress" style="height:18px">
                            <div class="progress-bar <?= $color ?>" role="progressbar"
                                 style="width: <?= max(2, $pctRel) ?>%"
                                 aria-valuenow="<?= $pctRel ?>" aria-valuemin="0" aria-valuemax="100">
                                <small><?= number_format($pctRel, 1) ?>%</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
