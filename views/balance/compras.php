<?php

/** @var yii\web\View $this */
/** @var array $rows */
/** @var array $gestiones */
/** @var array $proveedores */
/** @var array $estadosPago */
/** @var array $totales */
/** @var array $filtros */
/** @var bool  $cerrada */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Balance: Historial de Compras';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <div>
        <?php if ($cerrada): ?>
            <span class="badge bg-secondary"><i class="fa-solid fa-lock me-1"></i>Gestión <?= (int)$filtros['gestion'] ?> cerrada</span>
        <?php else: ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-lock-open me-1"></i>Gestión <?= (int)$filtros['gestion'] ?> abierta</span>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Gestión</label>
                <select name="gestion" class="form-select form-select-sm">
                    <?php $existe = false; foreach ($gestiones as $g): if ((int)$g === (int)$filtros['gestion']) $existe = true; ?>
                        <option value="<?= (int)$g ?>" <?= (int)$filtros['gestion'] === (int)$g ? 'selected' : '' ?>><?= (int)$g ?></option>
                    <?php endforeach; ?>
                    <?php if (!$existe): ?><option value="<?= (int)$filtros['gestion'] ?>" selected><?= (int)$filtros['gestion'] ?></option><?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Proveedor</label>
                <select name="proveedor_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (int)$filtros['proveedorId'] === (int)$p['id'] ? 'selected' : '' ?>><?= Html::encode($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estado pago</label>
                <select name="estado_pago_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($estadosPago as $e): ?>
                        <option value="<?= (int)$e['id'] ?>" <?= (int)$filtros['estadoPagoId'] === (int)$e['id'] ? 'selected' : '' ?>><?= Html::encode($e['nombre']) ?></option>
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
                <a href="<?= Url::to(['compras']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Total comprado · gestión <?= (int)$filtros['gestion'] ?></div>
            <div class="kpi-value text-primary"><?= number_format($totales['gestion'], 2) ?> Bs</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Total Pagado</div>
            <div class="kpi-value text-success"><?= number_format($totales['pagado'], 2) ?> Bs</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Pendiente de pago</div>
            <div class="kpi-value text-warning"><?= number_format($totales['pendiente'], 2) ?> Bs</div>
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
                    <th>Documento</th>
                    <th class="text-end">Importe</th>
                    <th class="text-center">Estado pago</th>
                    <th>Usuario</th>
                    <th class="text-center">Gestión</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Sin compras para los filtros seleccionados.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= Html::encode($r['fecha_compra']) ?></td>
                        <td><?= Html::encode($r['proveedor']) ?></td>
                        <td>
                            <?php if ($r['documento']): ?><span class="badge bg-light text-dark border me-1"><?= Html::encode($r['documento']) ?></span><?php endif; ?>
                            <?= Html::encode($r['nro_documento'] ?: '—') ?>
                        </td>
                        <td class="text-end fw-bold"><?= number_format((float)$r['importe_total'], 2) ?> Bs</td>
                        <td class="text-center">
                            <?php if ((int)$r['estado_pago_id'] === 2): ?>
                                <span class="badge bg-success">Pagado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td><i class="fa-solid fa-user me-1 text-secondary"></i><?= Html::encode($r['usuario'] ?: '—') ?></td>
                        <td class="text-center"><?= (int)$r['gestion'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
