<?php

/** @var yii\web\View $this */
/** @var array $meses */
/** @var int $anio */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Reporte: Ventas vs Compras Mensual';
$mesesNombre = [
    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
    7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
];

$totV = 0; $totC = 0; $totT = 0;
foreach ($meses as $m) { $totV += $m['total_ventas']; $totC += $m['total_compras']; $totT += $m['transacciones']; }
$margen = $totV - $totC;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Año</label>
                <input type="number" name="anio" min="2020" max="2099" value="<?= (int)$anio ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Aplicar</button>
                <a href="<?= Url::to(['mensual']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Año</div>
            <div class="kpi-value text-primary"><?= (int)$anio ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Total Ventas</div>
            <div class="kpi-value text-success"><?= number_format($totV, 2) ?> Bs</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Total Compras</div>
            <div class="kpi-value text-danger"><?= number_format($totC, 2) ?> Bs</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Margen</div>
            <div class="kpi-value <?= $margen >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= number_format($margen, 2) ?> Bs
            </div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table df-separated-table align-middle">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="text-end">Total Ventas</th>
                    <th class="text-end">Total Compras</th>
                    <th class="text-end">Margen</th>
                    <th class="text-center"># Transacciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meses as $m): ?>
                    <tr>
                        <td><strong><?= $mesesNombre[$m['mes']] ?></strong></td>
                        <td class="text-end text-success"><?= number_format($m['total_ventas'], 2) ?> Bs</td>
                        <td class="text-end text-danger"><?= number_format($m['total_compras'], 2) ?> Bs</td>
                        <td class="text-end fw-bold <?= $m['margen'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($m['margen'], 2) ?> Bs
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <?= (int)$m['transacciones'] ?>
                            </span>
                            <small class="text-muted d-block">
                                V:<?= (int)$m['num_ventas'] ?> &middot; C:<?= (int)$m['num_compras'] ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td>Total <?= (int)$anio ?></td>
                    <td class="text-end text-success"><?= number_format($totV, 2) ?> Bs</td>
                    <td class="text-end text-danger"><?= number_format($totC, 2) ?> Bs</td>
                    <td class="text-end <?= $margen >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($margen, 2) ?> Bs</td>
                    <td class="text-center"><?= (int)$totT ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
