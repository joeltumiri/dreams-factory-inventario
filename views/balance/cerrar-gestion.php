<?php

/** @var yii\web\View $this */
/** @var int $gestion */
/** @var array $resumen */
/** @var bool  $cerrada */
/** @var array $gestiones */
/** @var array $cierres */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Cerrar Gestión';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-lock me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <span class="text-muted small">Solo el <strong>Gerente</strong> puede cerrar gestiones.</span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Gestión</label>
                <select name="gestion" class="form-select form-select-sm">
                    <?php foreach ($gestiones as $g): ?>
                        <option value="<?= (int)$g ?>" <?= (int)$gestion === (int)$g ? 'selected' : '' ?>><?= (int)$g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-eye"></i> Ver resumen</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <strong>Reporte consolidado · Gestión <?= (int)$gestion ?></strong>
        <?php if ($cerrada): ?>
            <span class="badge bg-secondary"><i class="fa-solid fa-lock me-1"></i>Cerrada</span>
        <?php else: ?>
            <span class="badge bg-success"><i class="fa-solid fa-lock-open me-1"></i>Abierta</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Compras</div>
                    <div class="fs-4 fw-bold text-primary"><?= number_format($resumen['total_compras'], 2) ?> Bs</div>
                    <small class="text-muted"><?= (int)$resumen['num_compras'] ?> registro(s)</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Ventas</div>
                    <div class="fs-4 fw-bold text-success"><?= number_format($resumen['total_ventas'], 2) ?> Bs</div>
                    <small class="text-muted"><?= (int)$resumen['num_ventas'] ?> registro(s)</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Margen (V − C)</div>
                    <div class="fs-4 fw-bold <?= $resumen['margen'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($resumen['margen'], 2) ?> Bs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Descuentos otorgados</div>
                    <div class="fs-4 fw-bold text-warning"><?= number_format($resumen['total_descuentos'], 2) ?> Bs</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Total cobrado / pendiente cobro</div>
                    <div class="fs-5">
                        <span class="text-success fw-bold"><?= number_format($resumen['total_cobrado'], 2) ?> Bs</span>
                        <span class="text-muted">/</span>
                        <span class="text-warning fw-bold"><?= number_format(max(0, $resumen['total_ventas'] - $resumen['total_cobrado']), 2) ?> Bs</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Total pagado / pendiente pago</div>
                    <div class="fs-5">
                        <span class="text-success fw-bold"><?= number_format($resumen['total_pagado'], 2) ?> Bs</span>
                        <span class="text-muted">/</span>
                        <span class="text-warning fw-bold"><?= number_format(max(0, $resumen['total_compras'] - $resumen['total_pagado']), 2) ?> Bs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$cerrada): ?>
<div class="card border-warning shadow-sm mb-4">
    <div class="card-header bg-warning-subtle text-warning-emphasis fw-bold">
        <i class="fa-solid fa-triangle-exclamation me-1"></i> Cierre definitivo
    </div>
    <div class="card-body">
        <p class="mb-2">Al cerrar la gestión <strong><?= (int)$gestion ?></strong>:</p>
        <ul class="mb-3">
            <li>Se <strong>bloquea la modificación</strong> de compras y ventas de ese año.</li>
            <li>Se genera un <strong>reporte consolidado</strong> permanente con los totales actuales.</li>
            <li>Los totales quedarán como definitivos y no podrán recalcularse.</li>
            <li>Esta acción <strong>no se puede deshacer</strong>.</li>
        </ul>
        <?= Html::beginForm(['confirmar-cierre'], 'post', ['onsubmit' => "return confirm('Confirmar cierre DEFINITIVO de la gestion {$gestion}. Esta accion no se puede revertir.');"])?>  
            <?= Html::hiddenInput('gestion', $gestion) ?>
            <div class="mb-2">
                <label class="form-label small text-muted">Observaciones (opcional)</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas del cierre..."></textarea>
            </div>
            <button type="submit" class="btn btn-warning fw-bold">
                <i class="fa-solid fa-lock me-1"></i> Cerrar gestión <?= (int)$gestion ?>
            </button>
        <?= Html::endForm() ?>
    </div>
</div>
<?php else: ?>
<div class="alert alert-secondary">
    <i class="fa-solid fa-circle-info me-1"></i> Esta gestión ya fue cerrada. Las compras y ventas asociadas quedan bloqueadas para edición.
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light fw-bold"><i class="fa-solid fa-clock-rotate-left me-1"></i> Gestiones cerradas</div>
    <div class="card-body">
        <table class="table df-separated-table align-middle">
            <thead>
                <tr>
                    <th class="text-center">Gestión</th>
                    <th>Fecha de cierre</th>
                    <th>Cerrado por</th>
                    <th class="text-end">Compras</th>
                    <th class="text-end">Ventas</th>
                    <th class="text-end">Margen</th>
                    <th class="text-center">Trans.</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$cierres): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay gestiones cerradas todavía.</td></tr>
                <?php else: foreach ($cierres as $c): ?>
                    <tr>
                        <td class="text-center fw-bold"><?= (int)$c['gestion'] ?></td>
                        <td><?= Html::encode($c['fecha_cierre']) ?></td>
                        <td><i class="fa-solid fa-user me-1 text-secondary"></i><?= Html::encode($c['usuario'] ?: '—') ?></td>
                        <td class="text-end"><?= number_format((float)$c['total_compras'], 2) ?> Bs</td>
                        <td class="text-end"><?= number_format((float)$c['total_ventas'], 2) ?> Bs</td>
                        <td class="text-end fw-bold <?= (float)$c['margen'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format((float)$c['margen'], 2) ?> Bs</td>
                        <td class="text-center"><small><?= (int)$c['num_compras'] ?>C / <?= (int)$c['num_ventas'] ?>V</small></td>
                        <td><small class="text-muted"><?= Html::encode($c['observaciones'] ?: '—') ?></small></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
