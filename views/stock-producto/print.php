<?php
/**
 * @var yii\web\View $this
 * @var array $items
 * @var string $titulo
 * @var string $tab
 */
use yii\bootstrap5\Html;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= Html::encode($titulo) ?> - <?= date('Ymd') ?></title>
<style>
    * { font-family: Arial, sans-serif; }
    body { margin: 20px; color: #222; }
    h1 { font-size: 18px; margin: 0 0 4px 0; color: #0d3b6e; }
    h2 { font-size: 13px; margin: 0 0 16px 0; color: #555; font-weight: normal; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th { background: #0d3b6e; color: #fff; padding: 6px; text-align: left; }
    td { padding: 5px 6px; border-bottom: 1px solid #ddd; }
    tr:nth-child(even) td { background: #f6f8fb; }
    .right { text-align: right; }
    .center { text-align: center; }
    .footer { margin-top: 18px; font-size: 10px; color: #777; text-align: right; }
    .btn-print { padding: 6px 12px; background: #0d3b6e; color: #fff; border: 0; cursor: pointer; }
    @media print { .no-print { display: none; } body { margin: 8mm; } }
</style>
</head>
<body>
<div class="no-print" style="margin-bottom:12px">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar como PDF</button>
    <button class="btn-print" style="background:#888" onclick="window.close()">Cerrar</button>
</div>

<h1>Dreams Factory — Reporte de Stock: <?= Html::encode($titulo) ?></h1>
<h2>Generado: <?= date('d/m/Y H:i') ?> — Total: <?= count($items) ?> productos</h2>

<table>
    <thead>
    <tr>
        <th style="width:90px">Código</th>
        <th style="width:130px">Categoría</th>
        <th>Producto</th>
        <th class="center" style="width:90px">Stock Mín.</th>
        <th class="center" style="width:90px">Stock Actual</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $p): ?>
    <tr>
        <td><strong><?= Html::encode((string)$p->codigo) ?></strong></td>
        <td><?= Html::encode($p->categoria ? $p->categoria->nombre : '—') ?></td>
        <td><?= Html::encode($p->nombre) ?></td>
        <td class="center"><?= (int)$p->stock_minimo ?></td>
        <td class="center"><strong><?= (int)$p->stock ?></strong></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
    <tr><td colspan="5" class="center" style="color:#999;padding:20px">Sin productos en esta categoría</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="footer">Dreams Factory — Sistema de Almacén · Página generada automáticamente</div>

<script>setTimeout(function(){ window.print(); }, 300);</script>
</body>
</html>
