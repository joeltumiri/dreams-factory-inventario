<?php
/**
 * @var yii\web\View   $this
 * @var array          $kpiVisual
 * @var array          $rotacionVisual
 * @var array          $rankingVisual
 * @var array          $tendenciaVisual
 * @var array          $serieVC
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Analítica de Datos';

$ventasVariacion  = (float)($kpiVisual['ventasVariacion'] ?? 0);
$alertaStock      = (int)($kpiVisual['alertaStock'] ?? 0);
$pedidosAtrasados = (int)($kpiVisual['pedidosAtrasados'] ?? 0);
$mejorVendedor    = $kpiVisual['mejorVendedor'] ?? ['nombre' => 'Sin datos', 'monto' => 0];

$tendenciaLabels = json_encode($tendenciaVisual['labels'] ?? [], JSON_UNESCAPED_UNICODE);
$tendenciaData   = json_encode($tendenciaVisual['data']   ?? [], JSON_UNESCAPED_UNICODE);
$tendenciaMeses  = json_encode($tendenciaVisual['meses']  ?? [], JSON_UNESCAPED_UNICODE);
$tendenciaAnios  = json_encode($tendenciaVisual['anios']  ?? [], JSON_UNESCAPED_UNICODE);
$rotacionJson    = json_encode($rotacionVisual ?? [],            JSON_UNESCAPED_UNICODE);
$rankingJson     = json_encode($rankingVisual  ?? [],            JSON_UNESCAPED_UNICODE);
$serieVCJson     = json_encode($serieVC        ?? [],            JSON_UNESCAPED_UNICODE);

// Tendencia mensaje
$tendData = $tendenciaVisual['data'] ?? [];
$len = count($tendData);
$tendPct = 0;
if ($len >= 2 && $tendData[$len - 2] > 0) {
    $tendPct = round((($tendData[$len - 1] - $tendData[$len - 2]) / $tendData[$len - 2]) * 100, 1);
}
$tendDir = $tendenciaVisual['direccion'] ?? 'flat';
?>

<style>
/* ============================================================
   DASHBOARD — Dreams Factory Analytics
   ============================================================ */
.df-dash {
    background: #f8f9fa;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

html.df-dark .df-dash {
    background: #0f1a2b;
    color: #d8eaf8;
}

/* --- KPI cards --- */
.df-kpi {
    background: #fff;
    border-radius: 10px;
    border-left: 5px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    padding: 18px 20px;
    height: 100%;
}
html.df-dark .df-kpi { background: #16243a; box-shadow: 0 2px 8px rgba(0,0,0,.3); }

.df-kpi.kpi-sales  { border-left-color: #28a745; }
.df-kpi.kpi-stock  { border-left-color: #ffc107; }
.df-kpi.kpi-orders { border-left-color: #17a2b8; }
.df-kpi.kpi-seller { border-left-color: #0d6efd; }

.df-kpi .kpi-label {
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6c757d;
    margin-bottom: 4px;
}
html.df-dark .df-kpi .kpi-label { color: #7a9fc2; }

.df-kpi .kpi-value {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.1;
}
.df-kpi .kpi-sub {
    font-size: .85rem;
    margin-top: 6px;
    color: #6c757d;
}
html.df-dark .df-kpi .kpi-sub { color: #7a9fc2; }

/* --- Generic card --- */
.df-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    padding: 18px 20px;
    height: 100%;
}
html.df-dark .df-card { background: #16243a; box-shadow: 0 2px 8px rgba(0,0,0,.3); }

.df-card-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 2px;
}
.df-card-hint {
    font-size: .82rem;
    color: #6c757d;
    margin-bottom: 14px;
}
html.df-dark .df-card-hint { color: #7a9fc2; }

/* --- Chart wrappers --- */
.df-chart-h { position: relative; height: 300px; }
.df-chart-h-sm { position: relative; height: 260px; }

/* --- Stats table --- */
.df-stats-table td {
    padding: 7px 0;
    font-size: .9rem;
    border-bottom: 1px solid #f0f0f0;
    color: inherit;
}
html.df-dark .df-stats-table td { border-bottom-color: #253d5e; }
.df-stats-table td:last-child { text-align: right; font-weight: 600; }

/* --- Ranking list --- */
.df-rank-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
html.df-dark .df-rank-item { border-bottom-color: #253d5e; }
.df-rank-item:last-child { border-bottom: none; }
.df-rank-num {
    font-size: 1.2rem;
    font-weight: 800;
    width: 28px;
    flex-shrink: 0;
    text-align: center;
}
.df-rank-name { flex: 1; font-weight: 600; font-size: .9rem; }
.df-rank-bar-wrap { width: 120px; }
.df-rank-bar-bg {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}
html.df-dark .df-rank-bar-bg { background: #253d5e; }
.df-rank-bar-fill { height: 100%; border-radius: 4px; }
.df-rank-monto { font-size: .85rem; font-weight: 700; min-width: 80px; text-align: right; }
.df-rank-var { font-size: .78rem; min-width: 44px; text-align: right; }

/* --- Trend badge --- */
.df-trend-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: .85rem;
    font-weight: 600;
    margin-bottom: 12px;
}
.df-trend-badge.up   { background: #d4edda; color: #155724; }
.df-trend-badge.down { background: #f8d7da; color: #721c24; }
.df-trend-badge.flat { background: #e2e3e5; color: #383d41; }

/* --- Legend dots --- */
.df-legend { display: flex; gap: 18px; flex-wrap: wrap; font-size: .82rem; margin-top: 10px; }
.df-legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 4px; }

@media (max-width: 767px) {
    .df-kpi .kpi-value { font-size: 1.6rem; }
    .df-chart-h, .df-chart-h-sm { height: 220px; }
}
</style>

<div class="df-dash">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Datos — Dreams Factory</h4>
            <div class="text-muted small">Actualizado al <?= date('d/m/Y') ?></div>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="<?= Url::to(['/bi/index']) ?>">
            <i class="bi bi-arrow-clockwise me-1"></i>Recargar
        </a>
    </div>

    <!-- FILA 1: KPIs -->
    <div class="row g-3 mb-3">

        <!-- Ventas del mes -->
        <div class="col-lg-3 col-md-6">
            <div class="df-kpi kpi-sales">
                <div class="kpi-label"><i class="bi bi-graph-up-arrow me-1"></i>Ventas del mes</div>
                <div class="kpi-value" style="color:#28a745;">
                    Bs. <?= number_format((float)($kpiVisual['ventasMes'] ?? 0), 0, '.', ',') ?>
                </div>
                <div class="kpi-sub">
                    <?php if ($ventasVariacion >= 0): ?>
                        <span class="text-success fw-bold"><i class="bi bi-arrow-up"></i> <?= number_format($ventasVariacion, 1) ?>%</span>
                    <?php else: ?>
                        <span class="text-danger fw-bold"><i class="bi bi-arrow-down"></i> <?= number_format(abs($ventasVariacion), 1) ?>%</span>
                    <?php endif; ?>
                    vs mes anterior
                </div>
            </div>
        </div>

        <!-- Alerta de stock -->
        <div class="col-lg-3 col-md-6">
            <div class="df-kpi kpi-stock">
                <div class="kpi-label"><i class="bi bi-exclamation-triangle me-1"></i>Alerta de stock</div>
                <div class="kpi-value" style="color:#ffc107;"><?= $alertaStock ?></div>
                <div class="kpi-sub">productos bajo mínimo</div>
            </div>
        </div>

        <!-- Pedidos atrasados -->
        <div class="col-lg-3 col-md-6">
            <div class="df-kpi kpi-orders">
                <div class="kpi-label"><i class="bi bi-clock-history me-1"></i>Pedidos atrasados</div>
                <div class="kpi-value" style="color:<?= $pedidosAtrasados === 0 ? '#28a745' : '#dc3545' ?>;">
                    <?= $pedidosAtrasados ?>
                </div>
                <div class="kpi-sub">
                    <?= $pedidosAtrasados === 0 ? 'sin retrasos hoy' : 'sin entregar a tiempo' ?>
                </div>
            </div>
        </div>

        <!-- Mejor vendedor -->
        <div class="col-lg-3 col-md-6">
            <div class="df-kpi kpi-seller">
                <div class="kpi-label"><i class="bi bi-trophy me-1"></i>Mejor vendedor</div>
                <div class="kpi-value" style="font-size:1.45rem;color:#0d6efd;">
                    <?= Html::encode((string)($mejorVendedor['nombre'] ?? 'Sin datos')) ?>
                </div>
                <div class="kpi-sub">
                    <span class="text-success fw-bold">Bs. <?= number_format((float)($mejorVendedor['monto'] ?? 0), 0, '.', ',') ?></span>
                    este mes
                </div>
            </div>
        </div>

    </div><!-- /FILA 1 -->

    <!-- FILA 2: Tendencia + Estadísticas -->
    <div class="row g-3 mb-3">

        <div class="col-lg-8">
            <div class="df-card">
                <div class="df-card-title"><i class="bi bi-bar-chart-line me-1"></i>Tendencia de ventas</div>
                <div class="df-card-hint">¿Las ventas están creciendo o bajando?</div>
                <?php
                    $badgeClass = $tendDir === 'up' ? 'up' : ($tendDir === 'down' ? 'down' : 'flat');
                    if ($tendDir === 'up') {
                        $msg = 'Las ventas crecieron ' . abs($tendPct) . '% respecto al mes anterior';
                    } elseif ($tendDir === 'down') {
                        $msg = 'Las ventas bajaron ' . abs($tendPct) . '% respecto al mes anterior';
                    } else {
                        $msg = 'Las ventas se mantienen estables';
                    }
                ?>
                <div class="df-trend-badge <?= $badgeClass ?>">
                    <?= Html::encode($msg) ?>
                </div>
                <div class="df-chart-h">
                    <canvas id="chartTendencia"></canvas>
                </div>
                <div class="mt-2 d-flex align-items-center justify-content-between">
                    <div class="df-legend">
                        <span><span class="df-legend-dot" style="background:#0d6efd;"></span><span id="tend-legend-label">Ventas mensuales (Bs.)</span></span>
                    </div>
                    <button id="btn-tend-volver" class="btn btn-sm btn-outline-secondary d-none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="df-card">
                <div class="df-card-title"><i class="bi bi-clipboard-data me-1"></i>Estadísticas del mes</div>
                <div class="df-card-hint">Resumen operativo</div>

                <!-- Mini donut: Ventas vs Compras -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:90px;height:90px;flex-shrink:0;">
                        <canvas id="chartDonutVC"></canvas>
                    </div>
                    <div style="font-size:.82rem;line-height:1.7;">
                        <div><span class="df-legend-dot" style="background:#0d6efd;display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:4px;"></span>Ventas <strong>Bs. <?= number_format((float)($kpiVisual['ventasMes'] ?? 0), 0, '.', ',') ?></strong></div>
                        <div><span class="df-legend-dot" style="background:#fd7e14;display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:4px;"></span>Compras <strong>Bs. <?= number_format((float)($kpiVisual['totalComprasMes'] ?? 0), 0, '.', ',') ?></strong></div>
                        <div class="text-success mt-1"><i class="fa-solid fa-arrow-trend-up me-1"></i>Margen <strong>Bs. <?= number_format(max(0,(float)($kpiVisual['ventasMes']??0)-(float)($kpiVisual['totalComprasMes']??0)),0,'.', ',') ?></strong></div>
                    </div>
                </div>

                <!-- Barra: Pedidos completados -->
                <div class="mb-2" style="font-size:.82rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fa-solid fa-box-open me-1 text-primary"></i>Pedidos completados</span>
                        <strong><?= (int)($kpiVisual['pedidosCompletados'] ?? 0) ?></strong>
                    </div>
                    <div style="height:7px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?= min(100, (int)($kpiVisual['pedidosCompletados'] ?? 0) * 5) ?>%;background:#0d6efd;border-radius:4px;transition:width .6s;"></div>
                    </div>
                </div>

                <!-- Barra: Clientes atendidos -->
                <div class="mb-3" style="font-size:.82rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fa-solid fa-users me-1 text-success"></i>Clientes atendidos</span>
                        <strong><?= (int)($kpiVisual['clientesAtendidos'] ?? 0) ?></strong>
                    </div>
                    <div style="height:7px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?= min(100, (int)($kpiVisual['clientesAtendidos'] ?? 0) * 5) ?>%;background:#28a745;border-radius:4px;transition:width .6s;"></div>
                    </div>
                </div>

                <!-- Mini bar: Promedio por venta -->
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:70px;height:70px;flex-shrink:0;">
                        <canvas id="chartPromedio"></canvas>
                    </div>
                    <div style="font-size:.82rem;">
                        <div class="text-muted">Promedio por venta</div>
                        <div class="fs-5 fw-bold text-warning">Bs. <?= number_format((float)($kpiVisual['promedioPorVenta'] ?? 0), 0, '.', ',') ?></div>
                        <div class="text-muted"><?= (int)($kpiVisual['productosActivos'] ?? 0) ?> productos activos</div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /FILA 2 -->

    <!-- FILA 3: Rotación + Ranking -->
    <div class="row g-3 mb-3">

        <div class="col-lg-7">
            <div class="df-card">
                <div class="df-card-title"><i class="bi bi-bar-chart-fill me-1"></i>Rotación de productos</div>
                <div class="df-card-hint">Cantidad vendida por producto este mes</div>
                <div class="df-chart-h">
                    <canvas id="chartRotacion"></canvas>
                </div>
                <div class="df-legend">
                    <span><span class="df-legend-dot" style="background:#28a745;"></span>Alta rotación</span>
                    <span><span class="df-legend-dot" style="background:#ffc107;"></span>Media rotación</span>
                    <span><span class="df-legend-dot" style="background:#dc3545;"></span>Baja rotación</span>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="df-card">
                <div class="df-card-title"><i class="bi bi-people me-1"></i>Ranking de vendedores</div>
                <div class="df-card-hint">Rendimiento comercial del mes</div>
                <?php
                $maxMonto = 0;
                foreach (($rankingVisual ?? []) as $rv) {
                    if ((float)($rv['monto'] ?? 0) > $maxMonto) $maxMonto = (float)$rv['monto'];
                }
                $rankColors = ['#28a745', '#0d6efd', '#fd7e14', '#6c757d', '#17a2b8'];
                ?>
                <?php if (empty($rankingVisual)): ?>
                    <div class="text-muted text-center py-4">Sin datos de ventas este mes</div>
                <?php else: ?>
                    <?php foreach (array_slice($rankingVisual, 0, 5) as $idx => $rv): ?>
                        <?php
                            $pct   = $maxMonto > 0 ? round(((float)$rv['monto'] / $maxMonto) * 100) : 0;
                            $var   = (float)($rv['variacion'] ?? 0);
                            $color = $rankColors[$idx] ?? '#6c757d';
                        ?>
                        <div class="df-rank-item">
                            <div class="df-rank-num" style="color:<?= $color ?>;"><?= $idx + 1 ?></div>
                            <div class="df-rank-name"><?= Html::encode((string)($rv['vendedor'] ?? '')) ?></div>
                            <div class="df-rank-bar-wrap">
                                <div class="df-rank-bar-bg">
                                    <div class="df-rank-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                                </div>
                            </div>
                            <div class="df-rank-monto" style="color:<?= $color ?>;">
                                Bs. <?= number_format((float)($rv['monto'] ?? 0), 0, '.', ',') ?>
                            </div>
                            <div class="df-rank-var <?= $var >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $var >= 0 ? '+' : '' ?><?= number_format($var, 1) ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /FILA 3 -->

    <!-- FILA 4: Ventas vs Compras -->
    <div class="row g-3">
        <div class="col-12">
            <div class="df-card">
                <div class="df-card-title"><i class="bi bi-bar-chart me-1"></i>Ventas vs Compras por mes</div>
                <div class="df-card-hint">Comparativa de los últimos 6 meses — permite ver el margen del negocio</div>
                <div class="df-chart-h-sm">
                    <canvas id="chartVC"></canvas>
                </div>
                <div class="df-legend mt-2">
                    <span><span class="df-legend-dot" style="background:#4a90d9;border-radius:2px;"></span>Ventas (ingresos)</span>
                    <span><span class="df-legend-dot" style="background:#e88080;border-radius:2px;"></span>Compras (inversión)</span>
                </div>
            </div>
        </div>
    </div><!-- /FILA 4 -->

</div><!-- /df-dash -->

<script src="/libs/chartjs/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

    const fmtBs = (v) => 'Bs. ' + Number(v || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 0, maximumFractionDigits: 0
    });

    /* ---- Tendencia ---- */
    const tendCtx = document.getElementById('chartTendencia');
    if (tendCtx) {
        const tendMeses = <?= $tendenciaMeses ?>;
        const tendAnios = <?= $tendenciaAnios ?>;
        const urlDiaria = <?= json_encode(\yii\helpers\Url::to(['/bi/tendencia-diaria'])) ?>;
        let tendChart = null;
        let enDetalle = false;

        function buildTendChart(labels, data, labelTexto, clickable) {
            if (tendChart) tendChart.destroy();
            tendChart = new Chart(tendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelTexto,
                        data: data,
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
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (c) => fmtBs(c.parsed.y),
                                footer: clickable ? () => 'Clic para ver detalle diario' : undefined,
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: (v) => 'Bs. ' + Number(v).toLocaleString('es-BO') } }
                    },
                    onClick: clickable ? (evt, elements) => {
                        if (!elements.length) return;
                        const idx = elements[0].index;
                        const mes  = tendMeses[idx];
                        const anio = tendAnios[idx];
                        fetch(urlDiaria + '?mes=' + mes + '&anio=' + anio)
                            .then(r => r.json())
                            .then(json => {
                                enDetalle = true;
                                document.getElementById('btn-tend-volver').classList.remove('d-none');
                                document.getElementById('tend-legend-label').textContent = 'Ventas diarias — ' + json.titulo;
                                buildTendChart(json.labels, json.data, 'Ventas diarias (Bs.)', false);
                            });
                    } : undefined,
                    cursor: clickable ? 'pointer' : 'default',
                }
            });
        }

        buildTendChart(<?= $tendenciaLabels ?>, <?= $tendenciaData ?>, 'Ventas mensuales (Bs.)', true);

        document.getElementById('btn-tend-volver').addEventListener('click', () => {
            enDetalle = false;
            document.getElementById('btn-tend-volver').classList.add('d-none');
            document.getElementById('tend-legend-label').textContent = 'Ventas mensuales (Bs.)';
            buildTendChart(<?= $tendenciaLabels ?>, <?= $tendenciaData ?>, 'Ventas mensuales (Bs.)', true);
        });
    }

    /* ---- Mini donut Ventas vs Compras ---- */
    const donutVC = document.getElementById('chartDonutVC');
    if (donutVC) {
        const ventas  = <?= (float)($kpiVisual['ventasMes'] ?? 0) ?>;
        const compras = <?= (float)($kpiVisual['totalComprasMes'] ?? 0) ?>;
        new Chart(donutVC, {
            type: 'doughnut',
            data: {
                labels: ['Ventas', 'Compras'],
                datasets: [{ data: [ventas || 0.01, compras || 0.01], backgroundColor: ['#0d6efd','#fd7e14'], borderWidth: 0, hoverOffset: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => fmtBs(c.parsed) } } }
            }
        });
    }

    /* ---- Mini gauge Promedio ---- */
    const promedioCtx = document.getElementById('chartPromedio');
    if (promedioCtx) {
        const promedio = <?= (float)($kpiVisual['promedioPorVenta'] ?? 0) ?>;
        const pedidos  = <?= (int)($kpiVisual['pedidosCompletados'] ?? 0) ?>;
        new Chart(promedioCtx, {
            type: 'doughnut',
            data: {
                datasets: [{ data: [pedidos || 0.01, Math.max(0, 20 - pedidos)], backgroundColor: ['#ffc107','rgba(255,255,255,.07)'], borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }

    /* ---- Rotación ---- */
    const rotRows = <?= $rotacionJson ?>;
    const rotCtx = document.getElementById('chartRotacion');
    if (rotCtx && rotRows.length) {
        new Chart(rotCtx, {
            type: 'bar',
            data: {
                labels: rotRows.map(r => r.producto),
                datasets: [{
                    label: 'Unidades vendidas',
                    data: rotRows.map(r => r.vendido),
                    backgroundColor: rotRows.map(r => r.color),
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (c) => {
                                const r = rotRows[c.dataIndex] || {};
                                return r.vendido + ' unidades — ' + r.estado;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Unidades vendidas' } },
                    x: { ticks: { maxRotation: 55, minRotation: 35, font: { size: 11 } } }
                }
            }
        });
    }

    /* ---- Ventas vs Compras ---- */
    const vcRows = <?= $serieVCJson ?>;
    const vcCtx = document.getElementById('chartVC');
    if (vcCtx && vcRows.length) {
        new Chart(vcCtx, {
            type: 'bar',
            data: {
                labels: vcRows.map(r => r.label),
                datasets: [
                    {
                        label: 'Ventas (ingresos)',
                        data: vcRows.map(r => r.ventas),
                        backgroundColor: '#4a90d9',
                        borderRadius: 5,
                    },
                    {
                        label: 'Compras (inversión)',
                        data: vcRows.map(r => r.compras),
                        backgroundColor: '#e88080',
                        borderRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => c.dataset.label + ': ' + fmtBs(c.parsed.y) } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (v) => 'Bs. ' + Number(v).toLocaleString('es-BO') } }
                }
            }
        });
    }
})();
</script>


