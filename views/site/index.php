<?php

/** @var yii\web\View $this */
/** @var array{labels:string[],data:float[],direccion:string,variacion:float,ultimo:float,anterior:float} $tendencia */

use yii\bootstrap5\Html;

$this->title = 'Inicio';

$user = Yii::$app->user->identity;
$nombre = $user ? trim(($user->nombre ?? '') . ' ' . ($user->paterno ?? '')) : '';
if ($nombre === '' && $user) {
    $nombre = $user->username ?? 'usuario';
}

$dir = $tendencia['direccion'];
$pct = abs((float)$tendencia['variacion']);
if ($dir === 'up') {
    $badgeBg = '#0e3d28';
    $badgeColor = '#6dccaa';
    $badgeIcon = 'fa-arrow-trend-up';
    $badgeMsg = 'Las ventas crecieron ' . $pct . '% respecto al mes anterior';
} elseif ($dir === 'down') {
    $badgeBg = '#3d1018';
    $badgeColor = '#f08090';
    $badgeIcon = 'fa-arrow-trend-down';
    $badgeMsg = 'Las ventas bajaron ' . $pct . '% respecto al mes anterior';
} else {
    $badgeBg = '#2a2008';
    $badgeColor = '#e0c060';
    $badgeIcon = 'fa-minus';
    $badgeMsg = 'Las ventas se mantienen estables';
}

$labelsJson = json_encode($tendencia['labels'], JSON_UNESCAPED_UNICODE);
$dataJson   = json_encode($tendencia['data'],   JSON_UNESCAPED_UNICODE);
?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h5 class="mb-1"><i class="fa-solid fa-house me-2 text-primary"></i>Bienvenido<?= $nombre ? ', ' . Html::encode($nombre) : '' ?></h5>
        <p class="text-muted mb-0">Resumen rápido de la evolución de tus ventas.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
                <h6 class="mb-1"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Tendencia de ventas</h6>
                <div class="text-muted small">Evolución mensual de ventas (últimos 6 meses)</div>
            </div>
            <div class="text-end">
                <div class="small text-muted">Último mes</div>
                <div class="fw-bold fs-5">Bs. <?= number_format((float)$tendencia['ultimo'], 2, '.', ',') ?></div>
            </div>
        </div>

        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded mb-3"
             style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; font-weight: 600;">
            <i class="fa-solid <?= $badgeIcon ?>"></i>
            <span><?= Html::encode($badgeMsg) ?></span>
        </div>

        <div style="position: relative; height: 320px;">
            <canvas id="chartTendenciaInicio"></canvas>
        </div>
    </div>
</div>

<script src="/libs/chartjs/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;
    const ctx = document.getElementById('chartTendenciaInicio');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $labelsJson ?>,
            datasets: [{
                label: 'Ventas mensuales (Bs.)',
                data: <?= $dataJson ?>,
                borderColor: '#0d6efd',
                borderWidth: 3,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                fill: true,
                backgroundColor: 'rgba(13,110,253,0.12)',
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (c) => 'Bs. ' + Number(c.parsed.y || 0).toLocaleString('es-BO',
                            { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => 'Bs. ' + Number(v).toLocaleString('es-BO') }
                }
            }
        }
    });
})();
</script>
