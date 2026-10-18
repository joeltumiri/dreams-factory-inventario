<?php
/**
 * @var yii\web\View $this
 * @var string $periodoLabel
 * @var ?string $desde
 * @var ?string $hasta
 * @var array   $kpi
 * @var array   $serieVC
 * @var array   $stockCategoria
 * @var array   $vendedores
 * @var array   $topProductos
 * @var array   $topClientes
 * @var array   $stockCritico
 */
use yii\helpers\Html;

$this->title = 'Dashboard BI - Dreams Factory';
$totalStockCat = max(1, (int)array_sum(array_column($stockCategoria, 'stock')));
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= Html::encode($this->title) ?></title>
<style>
* { box-sizing:border-box; }
body { font-family: Arial, Helvetica, sans-serif; color:#222; margin:24px; font-size:12px; }
h1 { color:#0d3b6e; margin:0 0 4px; font-size:20px; }
h2 { color:#0d3b6e; margin:20px 0 8px; font-size:14px; border-bottom:2px solid #0d3b6e; padding-bottom:4px; }
.meta { color:#666; margin-bottom:16px; font-size:11px; }
.kpi-row { display:flex; gap:8px; margin-bottom:16px; }
.kpi { flex:1; padding:10px 12px; border-radius:8px; color:#fff; }
.kpi.green  { background:#16a34a; }
.kpi.blue   { background:#1a5fa8; }
.kpi.orange { background:#f97316; }
.kpi.red    { background:#dc2626; }
.kpi h6 { margin:0 0 4px; font-size:10px; text-transform:uppercase; opacity:.85; }
.kpi .v { font-size:18px; font-weight:700; }
.kpi .s { font-size:10px; opacity:.9; }
table { width:100%; border-collapse:collapse; margin-bottom:14px; }
th, td { border:1px solid #ddd; padding:5px 7px; text-align:left; font-size:11px; }
th { background:#f1f5f9; color:#0d3b6e; }
td.r, th.r { text-align:right; }
.print-btn { float:right; padding:6px 14px; background:#0d3b6e; color:#fff; border:none; border-radius:6px; cursor:pointer; }
@media print { .print-btn { display:none; } body { margin:8mm; } }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimir / PDF</button>
<h1>Dreams Factory - Dashboard de Inteligencia de Negocios</h1>
<div class="meta">
    Periodo: <strong><?= Html::encode($periodoLabel) ?></strong>
    &nbsp;|&nbsp; Generado: <?= date('Y-m-d H:i') ?>
</div>

<div class="kpi-row">
    <div class="kpi green">
        <h6>Ventas del periodo</h6>
        <div class="v">Bs <?= number_format((float)$kpi['ventasPeriodo'], 2) ?></div>
        <div class="s"><?= number_format((float)$kpi['variacionVentas'], 1) ?>% vs. anterior</div>
    </div>
    <div class="kpi blue">
        <h6>Compras del periodo</h6>
        <div class="v">Bs <?= number_format((float)$kpi['comprasPeriodo'], 2) ?></div>
        <div class="s"><?= number_format((float)$kpi['variacionCompras'], 1) ?>% vs. anterior</div>
    </div>
    <div class="kpi orange">
        <h6>Stock total</h6>
        <div class="v"><?= number_format((int)$kpi['stockTotal']) ?></div>
        <div class="s"><?= (int)$kpi['productosDiferentes'] ?> productos</div>
    </div>
    <div class="kpi red">
        <h6>Alertas críticas</h6>
        <div class="v"><?= (int)$kpi['alertasCriticas'] ?></div>
        <div class="s">productos bajo mínimo</div>
    </div>
</div>

<h2>Ventas vs. Compras (últimos 6 meses)</h2>
<table>
<thead><tr><th>Mes</th><th class="r">Ventas (Bs)</th><th class="r">Compras (Bs)</th><th class="r">Margen (Bs)</th></tr></thead>
<tbody>
<?php foreach ($serieVC as $r): ?>
<tr>
    <td><?= Html::encode($r['label']) ?></td>
    <td class="r"><?= number_format((float)$r['ventas'],  2) ?></td>
    <td class="r"><?= number_format((float)$r['compras'], 2) ?></td>
    <td class="r"><?= number_format((float)$r['ventas'] - (float)$r['compras'], 2) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>

<h2>Distribución de stock por categoría</h2>
<table>
<thead><tr><th>Categoría</th><th class="r">Stock</th><th class="r">Productos</th><th class="r">% del total</th></tr></thead>
<tbody>
<?php foreach ($stockCategoria as $r): ?>
<tr>
    <td><?= Html::encode((string)($r['categoria'] ?? '—')) ?></td>
    <td class="r"><?= (int)$r['stock'] ?></td>
    <td class="r"><?= (int)$r['productos'] ?></td>
    <td class="r"><?= number_format(((int)$r['stock'] * 100) / $totalStockCat, 2) ?>%</td>
</tr>
<?php endforeach; ?>
</tbody></table>

<h2>Rendimiento por vendedor</h2>
<table>
<thead><tr><th>Vendedor</th><th class="r">Monto (Bs)</th><th class="r">Trans.</th><th class="r">Promedio (Bs)</th></tr></thead>
<tbody>
<?php foreach ($vendedores as $v): ?>
<tr>
    <td><?= Html::encode((string)$v['vendedor']) ?></td>
    <td class="r"><?= number_format((float)$v['monto'], 2) ?></td>
    <td class="r"><?= (int)$v['transacciones'] ?></td>
    <td class="r"><?= number_format((float)$v['promedio'], 2) ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$vendedores): ?><tr><td colspan="4" style="text-align:center;color:#666">Sin datos</td></tr><?php endif; ?>
</tbody></table>

<h2>Top 5 productos más vendidos</h2>
<table>
<thead><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th class="r">Cantidad</th><th class="r">Monto (Bs)</th></tr></thead>
<tbody>
<?php foreach ($topProductos as $p): ?>
<tr>
    <td><?= Html::encode((string)($p['codigo'] ?? '—')) ?></td>
    <td><?= Html::encode((string)$p['nombre']) ?></td>
    <td><?= Html::encode((string)($p['categoria'] ?? '—')) ?></td>
    <td class="r"><?= (int)$p['cantidad'] ?></td>
    <td class="r"><?= number_format((float)$p['monto'], 2) ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$topProductos): ?><tr><td colspan="5" style="text-align:center;color:#666">Sin datos</td></tr><?php endif; ?>
</tbody></table>

<h2>Top 5 clientes por volumen</h2>
<table>
<thead><tr><th>Cliente</th><th class="r">Pedidos</th><th class="r">Total (Bs)</th><th class="r">% del total</th></tr></thead>
<tbody>
<?php foreach ($topClientes as $c): ?>
<tr>
    <td><?= Html::encode((string)$c['cliente']) ?></td>
    <td class="r"><?= (int)$c['pedidos'] ?></td>
    <td class="r"><?= number_format((float)$c['total'], 2) ?></td>
    <td class="r"><?= number_format((float)$c['porcentaje'], 2) ?>%</td>
</tr>
<?php endforeach; ?>
<?php if (!$topClientes): ?><tr><td colspan="4" style="text-align:center;color:#666">Sin datos</td></tr><?php endif; ?>
</tbody></table>

<h2>Productos con stock crítico</h2>
<table>
<thead><tr><th>Código</th><th>Nombre</th><th class="r">Actual</th><th class="r">Mínimo</th><th class="r">Diferencia</th><th>Estado</th></tr></thead>
<tbody>
<?php foreach ($stockCritico as $s): ?>
<tr>
    <td><?= Html::encode((string)($s['codigo'] ?? '—')) ?></td>
    <td><?= Html::encode((string)$s['nombre']) ?></td>
    <td class="r"><?= (int)$s['stock'] ?></td>
    <td class="r"><?= (int)$s['stock_minimo'] ?></td>
    <td class="r" style="color:#dc2626;font-weight:bold"><?= (int)$s['diferencia'] ?></td>
    <td style="color:#dc2626;font-weight:bold">Crítico</td>
</tr>
<?php endforeach; ?>
<?php if (!$stockCritico): ?><tr><td colspan="6" style="text-align:center;color:#16a34a">Sin alertas</td></tr><?php endif; ?>
</tbody></table>

<script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
</body>
</html>
