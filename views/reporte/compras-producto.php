<?php

/** @var yii\web\View $this */
/** @var array $rows */
/** @var array $proveedores */
/** @var array $categorias */
/** @var array $gestiones */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Reporte: Compras-Producto';

$totalGeneral = 0;
$totalCantidad = 0;
foreach ($rows as $r) {
    $totalGeneral += (float)$r['importe'];
    $totalCantidad += (int)$r['cantidad'];
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-truck me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($rows) ?> registro(s)</span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Proveedor</label>
                <select name="proveedor_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (int)$filtros['proveedorId'] === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Categoría</label>
                <select name="categoria_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$filtros['categoriaId'] === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= Html::encode($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Gestión</label>
                <select name="gestion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($gestiones as $g): ?>
                        <option value="<?= (int)$g ?>" <?= (int)$filtros['gestion'] === (int)$g ? 'selected' : '' ?>><?= (int)$g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Desde</label>
                <input type="date" name="desde" value="<?= Html::encode($filtros['desde']) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Hasta</label>
                <input type="date" name="hasta" value="<?= Html::encode($filtros['hasta']) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-12 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="<?= Url::to(['compras-producto']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Líneas de compra</div>
            <div class="kpi-value text-primary"><?= count($rows) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Cantidad total</div>
            <div class="kpi-value"><?= number_format($totalCantidad) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Importe total</div>
            <div class="kpi-value text-success"><?= number_format($totalGeneral, 2) ?> Bs</div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table df-separated-table align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">P. Unitario</th>
                    <th class="text-end">Importe</th>
                    <th class="text-center">Gestión</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Sin compras para los filtros seleccionados.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= Html::encode($r['fecha_compra']) ?></td>
                            <td><i class="fa-solid fa-truck-field me-1 text-secondary"></i><?= Html::encode($r['proveedor']) ?></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">
                                    <?= Html::encode($r['producto_codigo'] ?: ('BG-P' . (int)$r['producto_id'])) ?>
                                </span>
                                <?= Html::encode($r['producto_nombre']) ?>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= Html::encode($r['categoria']) ?></span></td>
                            <td class="text-center fw-bold"><?= (int)$r['cantidad'] ?></td>
                            <td class="text-end"><?= number_format((float)$r['precio_unitario'], 2) ?> Bs</td>
                            <td class="text-end fw-bold text-success"><?= number_format((float)$r['importe'], 2) ?> Bs</td>
                            <td class="text-center"><?= (int)$r['gestion'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if ($rows): ?>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="4" class="text-end">Total general</td>
                    <td class="text-center"><?= number_format($totalCantidad) ?></td>
                    <td></td>
                    <td class="text-end text-success"><?= number_format($totalGeneral, 2) ?> Bs</td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
