<?php

/** @var yii\web\View $this */
/** @var array $rows */
/** @var array $gestiones */
/** @var array $vendedores */
/** @var array $clientes */
/** @var array $estadosVenta */
/** @var array $estadosPago */
/** @var array $totales */
/** @var array $filtros */
/** @var bool  $cerrada */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Balance: Historial de Ventas';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-receipt me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?php if ($cerrada): ?>
        <span class="badge bg-secondary"><i class="fa-solid fa-lock me-1"></i>Gestión <?= (int)$filtros['gestion'] ?> cerrada</span>
    <?php else: ?>
        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-lock-open me-1"></i>Gestión <?= (int)$filtros['gestion'] ?> abierta</span>
    <?php endif; ?>
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
                <label class="form-label small text-muted mb-1">Vendedor</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($vendedores as $v): ?>
                        <option value="<?= (int)$v['id'] ?>" <?= (int)$filtros['vendedorId'] === (int)$v['id'] ? 'selected' : '' ?>><?= Html::encode($v['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cliente</label>
                <select name="cliente_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$filtros['clienteId'] === (int)$c['id'] ? 'selected' : '' ?>><?= Html::encode($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estado venta</label>
                <select name="estado_venta_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($estadosVenta as $e): ?>
                        <option value="<?= (int)$e['id'] ?>" <?= (int)$filtros['estadoVentaId'] === (int)$e['id'] ? 'selected' : '' ?>><?= Html::encode($e['nombre']) ?></option>
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
                <a href="<?= Url::to(['ventas']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Total vendido · gestión <?= (int)$filtros['gestion'] ?></div>
            <div class="kpi-value text-primary"><?= number_format($totales['vendido'], 2) ?> Bs</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Total Cobrado</div>
            <div class="kpi-value text-success"><?= number_format($totales['cobrado'], 2) ?> Bs</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card"><div class="card-body">
            <div class="text-muted small">Pendiente de cobro</div>
            <div class="kpi-value text-warning"><?= number_format($totales['pendiente'], 2) ?> Bs</div>
        </div></div>
    </div>
</div>

<?php
$mapEV = [1 => ['Para Entregar','warning'], 2 => ['Enviadas','info'], 3 => ['Entregadas','success']];
?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table df-separated-table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th class="text-end">Importe</th>
                    <th class="text-end">Descuento</th>
                    <th class="text-center">Estado venta</th>
                    <th class="text-center">Estado pago</th>
                    <th class="text-center">Gestión</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Sin ventas para los filtros seleccionados.</td></tr>
                <?php else: foreach ($rows as $r):
                    $ev = $mapEV[(int)$r['estado_venta_id']] ?? [$r['estado_venta'] ?? '—', 'secondary'];
                ?>
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= Html::encode($r['codigo']) ?></span></td>
                        <td><?= Html::encode($r['fecha_pedido']) ?></td>
                        <td><?= Html::encode($r['cliente']) ?></td>
                        <td><?= Html::encode($r['vendedor'] ?: '—') ?></td>
                        <td class="text-end fw-bold"><?= number_format((float)$r['total_efectivo'], 2) ?> Bs</td>
                        <td class="text-end text-muted"><?= number_format((float)$r['descuento'], 2) ?> Bs</td>
                        <td class="text-center"><span class="badge bg-<?= $ev[1] ?>"><?= Html::encode($ev[0]) ?></span></td>
                        <td class="text-center">
                            <?php if ((int)$r['estado_pago_id'] === 2): ?>
                                <span class="badge bg-success">Pagado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= (int)$r['gestion'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
