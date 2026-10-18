<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $productos
 * @var array $categorias
 * @var array $almacenes
 * @var array $filtros
 * @var int   $kpiLotes
 * @var int   $kpiUnidades
 * @var float $kpiValor
 * @var int   $kpiSinMovimiento
 * @var array $ultimaVenta        [producto_id => 'YYYY-MM-DD ...']
 * @var array $ventasPorProducto  [producto_id => total_vendido]
 */
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\helpers\Url;

$this->title = 'Rotación de Inventario';
$f = $filtros;
$lotes = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$today = new DateTimeImmutable('today');
?>

<style>
.rot-kpi-card {
    background: #1e2a3a;
    border-radius: 8px;
    border-left: 4px solid #0d6efd;
    padding: 16px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.35);
}
.rot-kpi-card .kpi-num { font-size: 2rem; font-weight: 700; line-height: 1.1; color: #f0f4fa; }
.rot-kpi-card .kpi-lbl { font-size: .78rem; color: #7a9fc2; margin-top: 2px; }
.rot-kpi-icon { font-size: 1.6rem; margin-bottom: 4px; }
</style>

<!-- ── Encabezado ── -->
<div class="mb-3">
    <h4 class="m-0 fw-bold">🔄 <?= Html::encode($this->title) ?></h4>
    <p class="text-muted small mb-0">Los productos ingresados primero se despachan primero</p>
</div>

<!-- ── KPIs ── -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-color:#0d6efd">
            <div class="kpi-num"><?= $kpiLotes ?></div>
            <div class="kpi-lbl">Lotes activos</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-color:#28a745">
            <div class="kpi-num"><?= number_format($kpiUnidades) ?></div>
            <div class="kpi-lbl">Unidades disponibles</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-color:#ffc107">
            <div class="kpi-num"><?= number_format($kpiValor, 0) ?> Bs.</div>
            <div class="kpi-lbl">Valor de inventario</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-color:<?= $kpiSinMovimiento > 0 ? '#dc3545' : '#28a745' ?>">
            <div class="kpi-num" style="color:<?= $kpiSinMovimiento > 0 ? '#f87171' : '#4ade80' ?>"><?= $kpiSinMovimiento ?></div>
            <div class="kpi-lbl">Productos sin movimiento</div>
        </div>
    </div>
</div>

<!-- ── Filtros ── -->
<div class="card border-0 shadow-sm mb-3" style="background:var(--df-card-bg)">
    <div class="card-body p-3">
        <form method="get" id="lotes-filter-form">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Producto</label>
                    <?php
                    // Si hay un producto seleccionado, precargamos el label
                    $prodLabel = $f['productoId'] > 0 && isset($productos[$f['productoId']])
                        ? $productos[$f['productoId']] : '';
                    ?>
                    <input type="text" id="lotes-prod-search" class="form-control form-control-sm"
                           placeholder="Escribir producto..." value="<?= Html::encode($prodLabel) ?>"
                           autocomplete="off">
                    <input type="hidden" name="producto_id" id="lotes-prod-id" value="<?= (int)$f['productoId'] ?: '' ?>">
                    <div class="invalid-feedback" id="lotes-prod-error">Selecciona un producto de la lista</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        Categoría <span id="lotes-cat-icon" style="display:none;color:#4ade80">✅</span>
                    </label>
                    <select name="categoria_id" id="lotes-cat-select" class="form-select form-select-sm">
                        <option value="0">— Todas —</option>
                        <?php foreach ($categorias as $id => $label): ?>
                            <option value="<?= $id ?>" <?= $f['categoriaId'] == $id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Almacén</label>
                    <select name="almacen_id" class="form-select form-select-sm">
                        <option value="0">— Todos —</option>
                        <?php foreach ($almacenes as $id => $label): ?>
                            <option value="<?= $id ?>" <?= $f['almacenId'] == $id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold mb-1">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="1"   <?= $f['estado'] === '1' ? 'selected' : '' ?>>Activo</option>
                        <option value="0"   <?= $f['estado'] === '0' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="all" <?= $f['estado'] === 'all' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Fecha Desde</label>
                    <input type="date" name="desde" value="<?= Html::encode($f['desde']) ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Fecha Hasta</label>
                    <input type="date" name="hasta" value="<?= Html::encode($f['hasta']) ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrar</button>
                    <a href="<?= Url::to(['/lote/lotes']) ?>" class="btn btn-outline-secondary btn-sm">🧹</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    dfProductoAutocomplete({
        inputId:    'lotes-prod-search',
        hiddenId:   'lotes-prod-id',
        catSelectId:'lotes-cat-select',
        catIconId:  'lotes-cat-icon',
        searchUrl:  '<?= \yii\helpers\Url::to(['/producto/buscar-producto']) ?>',
    });

    // Si hay producto preseleccionado y categoría ya cargada, mostrar ícono
    <?php if ($f['productoId'] > 0 && $f['categoriaId'] > 0): ?>
    document.getElementById('lotes-cat-select').disabled = true;
    document.getElementById('lotes-cat-icon').style.display = 'inline';
    <?php endif; ?>

    // Al limpiar con el enlace, disabled del select no se envía → ok
})();
</script>


<!-- ── Tabla ── -->
<div class="card border-0 shadow-sm" style="background:var(--df-card-bg)">
    <div class="card-body p-0">
        <div style="overflow-x:auto">
            <table class="table table-dark table-hover table-bordered align-middle mb-0" style="font-size:.85rem">
                <thead style="background:var(--df-table-head)">
                    <tr>
                        <th>CÓDIGO</th>
                        <th>PRODUCTO</th>
                        <th>CATEGORÍA</th>
                        <th>FECHA INGRESO</th>
                        <th class="text-center">STOCK INICIAL</th>
                        <th class="text-center">DISPONIBLE</th>
                        <th class="text-center">% VENDIDO</th>
                        <th class="text-center">ROTACIÓN</th>
                        <th class="text-center">DÍAS SIN VENTA</th>
                        <th class="text-end">P. COMPRA</th>
                        <th>ALMACÉN</th>
                        <th class="text-center">ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$lotes): ?>
                    <tr><td colspan="12" class="text-center text-muted py-4">Sin lotes que coincidan con los filtros</td></tr>
                <?php else: foreach ($lotes as $l):
                    $pid      = (int)$l->producto_id;
                    $ini      = (int)$l->cantidad_inicial;
                    $disp     = (int)$l->cantidad_disponible;
                    $vendido  = $ini - $disp;

                    // % vendido
                    $pctVendido = $ini > 0 ? round($vendido / $ini * 100, 1) : 0.0;
                    $pctColor   = $pctVendido >= 50 ? 'text-success' : ($pctVendido >= 20 ? 'text-warning' : 'text-danger');

                    // Rotación = vendido / stock_promedio
                    $stockProm  = ($ini + $disp) / 2;
                    $rotacion   = $stockProm > 0 ? round($vendido / $stockProm, 2) : 0.0;
                    if ($vendido <= 0 || empty($ventasPorProducto[$pid])) {
                        $rotBadge = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#dc3545;margin-right:5px"></span><span class="text-danger">Sin movimiento</span>';
                    } elseif ($rotacion > 4) {
                        $rotBadge = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#28a745;margin-right:5px"></span><span class="text-success">' . $rotacion . '</span>';
                    } elseif ($rotacion >= 2) {
                        $rotBadge = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#ffc107;margin-right:5px"></span><span class="text-warning">' . $rotacion . '</span>';
                    } else {
                        $rotBadge = '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#dc3545;margin-right:5px"></span><span class="text-danger">' . $rotacion . '</span>';
                    }

                    // Barra de progreso disponible
                    $pctDisp    = $ini > 0 ? min(100, round($disp / $ini * 100)) : 0;
                    $barColor   = $pctDisp > 50 ? 'bg-success' : ($pctDisp >= 20 ? 'bg-warning' : 'bg-danger');

                    // Días sin venta
                    $ultima = $ultimaVenta[$pid] ?? null;
                    if ($ultima === null) {
                        $diasHtml = '<span class="text-danger">Sin ventas</span>';
                    } else {
                        $ultDate = new DateTimeImmutable(substr($ultima, 0, 10));
                        $dias    = (int)$today->diff($ultDate)->days;
                        if ($dias === 0) {
                            $diasHtml = '<span class="text-success fw-bold">Hoy</span>';
                        } elseif ($dias <= 7) {
                            $diasHtml = '<span class="text-warning">' . $dias . ' días</span>';
                        } else {
                            $diasHtml = '<span class="text-danger">' . $dias . ' días</span>';
                        }
                    }
                ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary"><?= Html::encode((string)($l->producto->codigo ?? 'BG-P' . $pid)) ?></span>
                        </td>
                        <td class="text-white"><?= Html::encode($l->producto ? $l->producto->nombre : '—') ?></td>
                        <td><?= Html::encode($l->producto && $l->producto->categoria ? $l->producto->categoria->nombre : '—') ?></td>
                        <td class="text-nowrap" style="color:#9ab8d8">
                            <?= $l->fecha_ingreso ? date('d/m/Y H:i', strtotime((string)$l->fecha_ingreso)) : '—' ?>
                        </td>
                        <td class="text-center"><?= $ini ?></td>
                        <td class="text-center" style="min-width:90px">
                            <div class="mb-1"><?= $disp ?></div>
                            <div class="progress" style="height:6px">
                                <div class="progress-bar <?= $barColor ?>" style="width:<?= $pctDisp ?>%"></div>
                            </div>
                        </td>
                        <td class="text-center <?= $pctColor ?> fw-bold"><?= $pctVendido ?>%</td>
                        <td class="text-center"><?= $rotBadge ?></td>
                        <td class="text-center"><?= $diasHtml ?></td>
                        <td class="text-end"><?= number_format((float)$l->precio_compra_unitario, 2) ?> Bs.</td>
                        <td><?= Html::encode($l->almacen ? $l->almacen->nombre : '—') ?></td>
                        <td class="text-center">
                            <?= (int)$l->estado === 1
                                ? '<span class="badge bg-success">Activo</span>'
                                : '<span class="badge bg-danger">Inactivo</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pie: info + paginación + exportar -->
    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2 py-2" style="background:var(--df-card-bg)">
        <span class="text-muted small">
            <?php if (false): ?>
                Mostrando <b><?= $begin ?></b> - <b><?= $end ?></b> de <b><?= $pagination->totalCount ?></b> lotes
            <?php else: ?>
            <?php endif; ?>
        </span>
        <div class="d-flex align-items-center gap-3">
            <?php if ($pagination && $pagination->totalCount > 0): ?>
                <?= LinkPager::widget(['pagination' => $pagination, 'options' => ['class' => 'pagination pagination-sm mb-0']]) ?>
            <?php endif; ?>
            <a href="<?= Url::to(array_merge(['/lote/lotes'], $f)) ?>&export=csv" class="btn btn-success btn-sm">
                <i class="fa-solid fa-file-excel"></i> Exportar a Excel
            </a>
        </div>
    </div>
</div>

<!-- ── Leyenda ── -->
<div class="mt-3 d-flex flex-wrap gap-3 small text-muted">
    <span>Rotación alta (&gt; 4) — se vende rápido</span>
    <span>Rotación normal (2 - 4) — movimiento aceptable</span>
    <span>Rotación baja (&lt; 2) — necesita atención</span>
    <span>Sin movimiento — intervención inmediata</span>
</div>

