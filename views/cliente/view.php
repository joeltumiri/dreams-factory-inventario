<?php

/** @var yii\web\View $this */
/** @var app\models\Cliente $model */
/** @var array $totales */
/** @var array $ventas */
/** @var array $habituales */

use yii\bootstrap5\Html;

$this->title = $model->getNombreDisplay();
$fmt = Yii::$app->formatter;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="m-0">
            <i class="fa-solid fa-store me-2 text-primary"></i>
            <?= Html::encode($this->title) ?>
            <?php if ($model->tipo): ?>
                <span class="badge bg-info-subtle text-info border border-info-subtle ms-2"><?= Html::encode($model->tipo->farmacia) ?></span>
            <?php endif; ?>
        </h4>
        <small class="text-muted">
            <?= Html::encode($model->ciudad?->nombre ?: '—') ?>
            <?= $model->zona ? ' · Zona ' . Html::encode($model->zona) : '' ?>
        </small>
    </div>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total comprado</div>
                <div class="display-6 fw-bold text-primary">Bs <?= $fmt->asDecimal((float)$totales['monto_total'], 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">N° de pedidos</div>
                <div class="display-6 fw-bold"><?= (int)$totales['total_pedidos'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Última compra</div>
                <div class="h4 fw-bold mb-0">
                    <?= $totales['ultima_fecha'] ? Html::encode($fmt->asDate($totales['ultima_fecha'], 'medium')) : '—' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Datos -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Datos del cliente</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">Razón social</dt><dd class="col-sm-8"><?= Html::encode($model->razon_social ?: '—') ?></dd>
                    <dt class="col-sm-4">Empleado / Propietario</dt><dd class="col-sm-8"><?= Html::encode($model->nombre_completo ?: '—') ?></dd>
                    <dt class="col-sm-4">Tipo de Contacto</dt>
                    <dd class="col-sm-8">
                        <?php if ($model->tipo_contacto === 'Propietario'): ?>
                            <span class="badge bg-primary"><i class="fa-solid fa-user-tie me-1"></i>Propietario</span>
                        <?php elseif ($model->tipo_contacto === 'Empleado'): ?>
                            <span class="badge bg-secondary"><i class="fa-solid fa-user me-1"></i>Empleado</span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>
                    <dt class="col-sm-4">NIT</dt><dd class="col-sm-8"><?= Html::encode($model->nit ?: '—') ?></dd>
                    <dt class="col-sm-4">Nombre factura</dt><dd class="col-sm-8"><?= Html::encode($model->nombre_factura ?: '—') ?></dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= $model->email ? Html::a(Html::encode($model->email), 'mailto:' . $model->email) : '—' ?></dd>
                    <dt class="col-sm-4">Celular</dt><dd class="col-sm-8"><?= Html::encode($model->contacto ?: '—') ?></dd>
                    <dt class="col-sm-4">Departamento</dt><dd class="col-sm-8"><?= Html::encode($model->departamento?->nombre ?: '—') ?></dd>
                    <dt class="col-sm-4">Ciudad</dt><dd class="col-sm-8"><?= Html::encode($model->ciudad?->nombre ?: '—') ?></dd>
                    <dt class="col-sm-4">Zona</dt><dd class="col-sm-8"><?= Html::encode($model->zona ?: '—') ?></dd>
                    <dt class="col-sm-4">Dirección</dt><dd class="col-sm-8"><?= Html::encode($model->direccion ?: '—') ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Mapa -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-map-location-dot me-2"></i>Ubicación</span>
                <?php if ($model->latitud && $model->longitud): ?>
                    <a class="btn btn-sm btn-outline-success" target="_blank"
                       href="https://www.google.com/maps?q=<?= $model->latitud ?>,<?= $model->longitud ?>">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0" style="min-height:280px">
                <?php if (!empty($model->iframe)): ?>
                    <div class="ratio ratio-16x9"><?= $model->iframe ?></div>
                <?php elseif ($model->latitud && $model->longitud): ?>
                    <iframe class="w-100 h-100" style="min-height:280px;border:0"
                            src="https://www.google.com/maps?q=<?= $model->latitud ?>,<?= $model->longitud ?>&output=embed"
                            allowfullscreen loading="lazy"></iframe>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">Sin georreferenciación.</div>
                <?php endif; ?>
            </div>
            <?php if ($model->latitud && $model->longitud): ?>
                <div class="card-footer bg-white small text-muted">
                    Lat: <?= Html::encode((string)$model->latitud) ?> · Long: <?= Html::encode((string)$model->longitud) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Productos habituales -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-cart-shopping me-2"></i>Productos que compra habitualmente (TOP 10)</div>
    <div class="card-body p-0">
        <table class="table df-separated-table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-end">Veces</th>
                    <th class="text-end">Cant. total</th>
                    <th class="text-end">Monto total</th>
                    <th>Última compra</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$habituales): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">Sin compras registradas.</td></tr>
            <?php else: foreach ($habituales as $h): ?>
                <tr>
                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">BG-P<?= (int)preg_replace('/\D/', '', (string)$h['codigo']) ?: Html::encode((string)$h['codigo']) ?></span></td>
                    <td><?= Html::encode((string)$h['nombre']) ?></td>
                    <td class="text-end"><?= (int)$h['veces'] ?></td>
                    <td class="text-end"><?= (int)$h['cantidad'] ?></td>
                    <td class="text-end">Bs <?= $fmt->asDecimal((float)$h['monto'], 2) ?></td>
                    <td><?= $h['ultima'] ? Html::encode($fmt->asDate($h['ultima'], 'medium')) : '—' ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Historial de pedidos -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-receipt me-2"></i>Historial de pedidos</div>
    <div class="card-body p-0">
        <table class="table df-separated-table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Código</th>
                    <th class="text-end">Importe</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$ventas): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Sin pedidos.</td></tr>
            <?php else: foreach ($ventas as $v): ?>
                <tr>
                    <td>#<?= (int)$v['id'] ?></td>
                    <td><?= $v['fecha_pedido'] ? Html::encode($fmt->asDate($v['fecha_pedido'], 'medium')) : '—' ?></td>
                    <td><?= Html::encode((string)($v['codigo'] ?? '—')) ?></td>
                    <td class="text-end">Bs <?= $fmt->asDecimal((float)$v['importe_total'], 2) ?></td>
                    <td><span class="badge bg-secondary-subtle text-secondary">#<?= (int)($v['estado_venta_id'] ?? 0) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
