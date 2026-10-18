<?php
/**
 * @var yii\web\View          $this
 * @var array                 $productos       [id => "código | nombre"]
 * @var int                   $productoId
 * @var app\models\Producto|null $selectedProduct
 * @var array                 $movimientos     rows with tipo, fecha, precio, etc.
 * @var int                   $saldoInicial
 */

use yii\bootstrap5\Html;

$this->title = 'Reporte de Existencia por Producto';
?>
<style>
/* ── Reporte de Existencia styles ────────────────────────────────────────── */
.rp-prod-card {
    background: var(--df-card-bg);
    border: 1px solid var(--df-card-border);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    padding: 1rem 1.3rem;
    margin-bottom: 1rem;
}
.rp-prod-card .field-row { display: flex; flex-wrap: wrap; gap: .3rem 1.4rem; }
.rp-prod-field { display: flex; align-items: baseline; gap: .3rem; min-width: 160px; }
.rp-prod-field .lbl {
    font-size: .72rem; font-weight: 700; color: #fff;
    padding: .1rem .45rem; border-radius: 4px; white-space: nowrap;
}
.lbl-blue   { background: #0d3b6e; }
.lbl-green  { background: #198754; }
.lbl-orange { background: #fd7e14; }
.lbl-red    { background: #dc3545; }
.lbl-teal   { background: #0dcaf0; color: #000 !important; }
.lbl-purple { background: #6f42c1; }
.rp-prod-field .val { font-size: .83rem; font-weight: 600; color: var(--df-text); }
.rp-filter-bar {
    background: var(--df-card-bg);
    border: 1px solid var(--df-card-border);
    border-radius: 8px;
    padding: .75rem 1.1rem;
    margin-bottom: 1rem;
    display: flex; align-items: flex-end; gap: .75rem; flex-wrap: wrap;
}
.rp-filter-bar label { font-size: .77rem; font-weight: 600; color: var(--df-text-muted); display: block; margin-bottom: 2px; }
.rp-filter-bar input, .rp-filter-bar select {
    font-size: .82rem;
    background: var(--df-input-bg) !important;
    border-color: var(--df-input-border) !important;
    color: var(--df-input-text) !important;
}
.saldo-inicial {
    text-align: center; font-size: .92rem; font-weight: 700;
    color: var(--df-text); padding: .4rem 0;
    border-bottom: 1px solid var(--df-card-border);
    background: var(--df-table-head);
}
.exist-table { font-size: .81rem; border-collapse: separate; border-spacing: 0; width: 100%; }
.exist-table th, .exist-table td {
    padding: .45rem .6rem;
    border-right: 1px solid var(--df-table-border);
    border-bottom: 1px solid var(--df-table-border);
    white-space: nowrap; vertical-align: middle;
    color: var(--df-text);
}
.exist-table th:first-child, .exist-table td:first-child { border-left: 1px solid var(--df-table-border); }
.exist-table thead tr.group-header th {
    text-align: center; border-top: 1px solid var(--df-table-border);
    font-weight: 700; padding: .3rem .6rem;
}
.exist-table thead tr.col-header th {
    background: var(--df-table-head);
    border-top: 2px solid var(--df-table-border);
    border-bottom: 2px solid var(--df-table-border);
    font-weight: 700; padding: .5rem .6rem;
}
.th-ingreso { background: #d1f2d1 !important; color: #155724 !important; }
.th-salida  { background: #ffd6d6 !important; color: #721c24 !important; }
.th-neutral { background: var(--df-table-head); }
/* Row types */
.row-ingreso td { background: #f0fff4; }
.row-salida  td { background: #fff5f5; }
.row-ingreso td:first-child { border-left: 3px solid #198754 !important; }
.row-salida  td:first-child { border-left: 3px solid #dc3545 !important; }
/* Value cells with active highlight */
.ing-cell { font-weight: 600; color: #155724; }
.ing-active { background: #e8f8e8 !important; }
.sal-cell { font-weight: 600; color: #721c24; }
.sal-active { background: #fde8e8 !important; }
/* Stock & totals */
.stock-col { font-weight: 700; text-align: center; color: #0d3b6e; }
.exist-table tfoot td {
    background: var(--df-table-head); font-weight: 700;
    border-top: 2px solid var(--df-table-border); color: var(--df-text);
}
.tot-ing { color: #155724 !important; }
.tot-sal { color: #721c24 !important; }
.dot-ingreso { color: #198754; }
.dot-salida  { color: #dc3545; }
.rp-empty { text-align: center; color: var(--df-text-muted); padding: 2rem; font-size: .9rem; }
/* ── Dark mode ─────────────────────────────────────────────────────────── */
html.df-dark .th-ingreso { background: #1a3d28 !important; color: #6ee098 !important; }
html.df-dark .th-salida  { background: #3d1a1a !important; color: #f08080 !important; }
html.df-dark .row-ingreso td { background: #152d1e; }
html.df-dark .row-salida  td { background: #2d1515; }
html.df-dark .ing-active  { background: #1e3d28 !important; }
html.df-dark .sal-active  { background: #3d1e1e !important; }
html.df-dark .ing-cell    { color: #6ee098; }
html.df-dark .sal-cell    { color: #f08080; }
html.df-dark .tot-ing     { color: #6ee098 !important; }
html.df-dark .tot-sal     { color: #f08080 !important; }
html.df-dark .stock-col   { color: #7ab8f5; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>

<!-- ── Product selector form ─────────────────────────────────────────────── -->
<?php $action = Yii::$app->urlManager->createUrl(['/lote/index']); ?>
<form method="get" action="<?= $action ?>">
<div class="rp-filter-bar">
    <div>
        <label>Producto</label>
        <select name="producto_id" class="form-select form-select-sm" style="min-width:280px" onchange="this.form.submit()">
            <option value="">— Seleccione un producto —</option>
            <?php foreach ($productos as $pid => $pnombre): ?>
            <option value="<?= $pid ?>" <?= $productoId == $pid ? 'selected' : '' ?>>
                <?= Html::encode($pnombre) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Fecha inicio</label>
        <input type="date" name="fecha_ini" class="form-control form-control-sm"
               value="<?= Html::encode(Yii::$app->request->get('fecha_ini', '')) ?>">
    </div>
    <div>
        <label>Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control form-control-sm"
               value="<?= Html::encode(Yii::$app->request->get('fecha_fin', '')) ?>">
    </div>
    <div class="d-flex gap-2 align-items-end">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-filter me-1"></i> Filtrar
        </button>
        <a href="<?= $action ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-xmark me-1"></i> Limpiar
        </a>
    </div>
</div>
</form>

<?php if ($selectedProduct): ?>
<!-- ── Product info card ──────────────────────────────────────────────────── -->
<div class="rp-prod-card">
    <div class="field-row">
        <div class="rp-prod-field">
            <span class="lbl lbl-blue"><i class="fa-solid fa-barcode me-1"></i> CodInterno</span>
            <span class="val"><?= Html::encode($selectedProduct->codigo ?? 'BG-P' . $selectedProduct->id) ?></span>
        </div>
        <div class="rp-prod-field">
            <span class="lbl lbl-teal"><i class="fa-solid fa-tag me-1"></i> Categoría</span>
            <span class="val"><?= Html::encode($selectedProduct->categoria ? $selectedProduct->categoria->nombre : '—') ?></span>
        </div>
        <div class="rp-prod-field">
            <span class="lbl lbl-green"><i class="fa-solid fa-warehouse me-1"></i> Stock Límite</span>
            <span class="val"><?= Html::encode((string)$selectedProduct->stock_minimo) ?></span>
        </div>
        <div class="rp-prod-field">
            <span class="lbl lbl-blue"><i class="fa-solid fa-cube me-1"></i> Nombre</span>
            <span class="val"><?= Html::encode($selectedProduct->nombre) ?></span>
        </div>
        <div class="rp-prod-field">
            <span class="lbl lbl-orange">$ Precio Estándar</span>
            <span class="val"><?= number_format((float)$selectedProduct->precio_venta, 2) ?> Bs.</span>
        </div>
        <?php if ($selectedProduct->descripcion): ?>
        <div class="rp-prod-field" style="min-width:250px">
            <span class="lbl lbl-blue"><i class="fa-solid fa-align-left me-1"></i> Descripción</span>
            <span class="val"><?= Html::encode($selectedProduct->descripcion) ?></span>
        </div>
        <?php endif; ?>
        <div class="rp-prod-field">
            <span class="lbl lbl-green"><i class="fa-solid fa-circle-check me-1"></i> Estado</span>
            <span class="badge bg-success">Activo</span>
        </div>
        <div class="rp-prod-field">
            <span class="lbl lbl-purple"><i class="fa-solid fa-boxes-stacked me-1"></i> Stock Actual</span>
            <span class="val fw-bold text-primary"><?= Html::encode((string)$selectedProduct->stock) ?></span>
        </div>
    </div>
</div>

<!-- ── Movement table ────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="saldo-inicial">Saldo Inicial: <?= $saldoInicial ?></div>
    <div style="overflow-x:auto">
        <table class="exist-table">
            <thead>
            <tr class="group-header">
                <th class="th-neutral" colspan="2"></th>
                <th class="th-neutral"></th>
                <th class="th-ingreso" colspan="2">INGRESO</th>
                <th class="th-salida"  colspan="2">SALIDA</th>
                <th class="th-neutral" colspan="4"></th>
            </tr>
            <tr class="col-header">
                <th class="th-neutral">TIPO</th>
                <th class="th-neutral">FECHA</th>
                <th class="th-neutral">PRECIO</th>
                <th class="th-ingreso">CANTIDAD</th>
                <th class="th-ingreso">TOTAL</th>
                <th class="th-salida">CANTIDAD</th>
                <th class="th-salida">TOTAL</th>
                <th class="th-neutral stock-col">STOCK</th>
                <th class="th-neutral">TIPO MOVIMIENTO</th>
                <th class="th-neutral">ALMACÉN</th>
                <th class="th-neutral">RESPONSABLE</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $totalCantIng = 0; $totalTotalIng = 0.0;
            $totalCantSal = 0; $totalTotalSal = 0.0;

            if (empty($movimientos)):
            ?>
            <tr>
                <td colspan="11" class="rp-empty">
                    <i class="fa-solid fa-inbox me-2 opacity-50"></i>
                    No hay movimientos registrados para este producto
                </td>
            </tr>
            <?php else: foreach ($movimientos as $mov):
                $isIngreso = $mov['tipo'] === 'Ingreso';
                $rowClass  = $isIngreso ? 'row-ingreso' : 'row-salida';
                $dotClass  = $isIngreso ? 'dot-ingreso' : 'dot-salida';
                $totalCantIng  += $mov['cantidad_ingreso'];
                $totalTotalIng += $mov['total_ingreso'];
                $totalCantSal  += $mov['cantidad_salida'];
                $totalTotalSal += $mov['total_salida'];
                $fechaFmt = $mov['fecha'] ? date('d/m/Y H:i', strtotime((string)$mov['fecha'])) : '—';
            ?>
            <tr class="<?= $rowClass ?>">
                <td>
                    <i class="fa-solid fa-circle <?= $dotClass ?>" style="font-size:.55rem; vertical-align:middle"></i>
                    <?= Html::encode($mov['tipo']) ?>
                </td>
                <td><?= Html::encode($fechaFmt) ?></td>
                <td class="text-end"><?= number_format($mov['precio'], 2) ?></td>
                <td class="text-center ing-cell <?= $isIngreso ? 'ing-active' : '' ?>">
                    <?= $isIngreso ? Html::encode((string)$mov['cantidad_ingreso']) : '' ?>
                </td>
                <td class="text-end ing-cell <?= $isIngreso ? 'ing-active' : '' ?>">
                    <?= $isIngreso ? number_format($mov['total_ingreso'], 2) : '' ?>
                </td>
                <td class="text-center sal-cell <?= !$isIngreso ? 'sal-active' : '' ?>">
                    <?= !$isIngreso ? Html::encode((string)$mov['cantidad_salida']) : '' ?>
                </td>
                <td class="text-end sal-cell <?= !$isIngreso ? 'sal-active' : '' ?>">
                    <?= !$isIngreso ? number_format($mov['total_salida'], 2) : '' ?>
                </td>
                <td class="stock-col"><?= Html::encode((string)$mov['stock']) ?></td>
                <td><?= Html::encode($mov['tipo_movimiento']) ?></td>
                <td><?= Html::encode($mov['almacen']) ?></td>
                <td><?= Html::encode($mov['responsable'] ?: '—') ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($movimientos)): ?>
            <tfoot>
            <tr>
                <td colspan="3" class="text-end pe-2">Total</td>
                <td class="text-center tot-ing">
                    <?= $totalCantIng > 0 ? '<strong>' . $totalCantIng . '</strong>' : '' ?>
                </td>
                <td class="text-end tot-ing">
                    <?= $totalTotalIng > 0 ? '<strong>' . number_format($totalTotalIng, 2) . '</strong>' : '' ?>
                </td>
                <td class="text-center tot-sal">
                    <?= $totalCantSal > 0 ? '<strong>' . $totalCantSal . '</strong>' : '' ?>
                </td>
                <td class="text-end tot-sal">
                    <?= $totalTotalSal > 0 ? '<strong>' . number_format($totalTotalSal, 2) . '</strong>' : '' ?>
                </td>
                <td class="stock-col">
                    <?php $stockFinal = end($movimientos)['stock'] ?? 0; ?>
                    <strong><?= $stockFinal ?></strong>
                </td>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="fa-solid fa-arrow-pointer fa-2x mb-3 opacity-30 d-block"></i>
        Selecciona un producto para ver su reporte de existencia y movimientos
    </div>
</div>
<?php endif; ?>
