<?php
/**
 * @var yii\web\View $this
 * @var app\models\Proveedor $model
 * @var app\models\Compra[] $compras
 * @var array $totales
 * @var array $habituales
 */
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = $model->nombre;
$this->registerCss('
.df-view .card { background: var(--df-card-bg) !important; border:1px solid var(--df-card-border) !important; color: var(--df-text); }
.df-view .card .table { background: transparent !important; color: var(--df-text) !important; margin-bottom:0; }
.df-view .card .table td, .df-view .card .table th { border-color: var(--df-table-border) !important; background: transparent !important; vertical-align: middle; }
.df-view .card .table tbody tr:hover td { background: var(--df-table-even) !important; }
.df-view .thead-custom th { background: var(--df-table-head) !important; color: var(--df-text-muted) !important; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; border-bottom: 1px solid var(--df-table-border) !important; }
.df-view .card .table .text-secondary { color: var(--df-text-muted) !important; font-weight:500; }
');
?>
<div class="df-view">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-handshake me-2 text-primary"></i><?= Html::encode($model->nombre) ?>
        <?= (int)$model->estado === 1
            ? '<span class="badge bg-success ms-2" style="font-size:.7rem">Activo</span>'
            : '<span class="badge bg-secondary ms-2" style="font-size:.7rem">Inactivo</span>' ?>
        <?php if ($model->rubro): ?>
            <span class="badge bg-info ms-1" style="font-size:.7rem"><?= Html::encode($model->rubro) ?></span>
        <?php endif; ?>
    </h4>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Editar',
            ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver',
            ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>

<!-- KPIs -->
<div class="row g-2 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:38px;height:38px">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                </div>
                <div>
                    <div class="text-muted small">Total compras</div>
                    <div class="fs-5 fw-bold"><?= (int)$totales['total_compras'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-2" style="width:38px;height:38px">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="text-muted small">Monto total comprado</div>
                    <div class="fs-5 fw-bold"><?= number_format((float)$totales['monto_total'], 2) ?> Bs.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width:38px;height:38px">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="text-muted small">Última compra</div>
                    <div class="fs-6 fw-bold">
                        <?= $totales['ultima_fecha']
                            ? date('d/m/Y', strtotime((string)$totales['ultima_fecha']))
                            : '<span class="text-muted">Nunca</span>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Datos del proveedor -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white py-2 px-3 fw-bold" style="font-size:.88rem">
                <i class="fa-solid fa-circle-info me-2"></i>Datos del Proveedor
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0" style="font-size:.85rem">
                    <tr><th class="text-secondary ps-3" style="width:160px">Razón social</th>
                        <td><?= Html::encode($model->razon_social ?: '—') ?></td></tr>
                    <tr><th class="text-secondary ps-3">NIT</th>
                        <td><?= $model->nit ? '<code>' . Html::encode($model->nit) . '</code>' : '—' ?></td></tr>
                    <tr><th class="text-secondary ps-3">Rubro</th>
                        <td><?= Html::encode($model->rubro ?: '—') ?></td></tr>
                    <tr><th class="text-secondary ps-3">Dirección</th>
                        <td><?= Html::encode($model->direccion ?: '—') ?></td></tr>
                    <tr><th class="text-secondary ps-3">Ciudad</th>
                        <td><?= Html::encode($model->ciudad ? $model->ciudad->nombre : '—') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white py-2 px-3 fw-bold" style="font-size:.88rem">
                <i class="fa-solid fa-address-book me-2"></i>Contacto
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0" style="font-size:.85rem">
                    <tr><th class="text-secondary ps-3" style="width:160px">Persona</th>
                        <td><?= Html::encode($model->contacto ?: '—') ?></td></tr>
                    <tr><th class="text-secondary ps-3"><i class="fa-solid fa-phone me-1"></i>Teléfono fijo</th>
                        <td><?= Html::encode($model->telefono ?: '—') ?></td></tr>
                    <tr><th class="text-secondary ps-3"><i class="fa-brands fa-whatsapp text-success me-1"></i>Celular / WhatsApp</th>
                        <td><?= Html::encode($model->celular ?: '—') ?></td></tr>
                    <tr><th class="text-secondary ps-3"><i class="fa-solid fa-envelope me-1"></i>Email</th>
                        <td><?= $model->email
                            ? '<a href="mailto:' . Html::encode($model->email) . '">' . Html::encode($model->email) . '</a>'
                            : '—' ?></td></tr>
                    <tr><th class="text-secondary ps-3">Notas</th>
                        <td><?= Html::encode($model->nota ?: '—') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Productos habituales -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-info text-white py-2 px-3 fw-bold" style="font-size:.88rem">
        <i class="fa-solid fa-star me-2"></i>Productos Habituales (Top 10)
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem">
            <thead class="thead-custom">
            <tr>
                <th>#</th><th>Código</th><th>Producto</th>
                <th class="text-center">Veces</th>
                <th class="text-center">Cantidad acum.</th>
                <th class="text-end">Monto acum.</th>
                <th>Última compra</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($habituales as $h): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><span class="badge bg-primary"><?= Html::encode((string)$h['codigo']) ?></span></td>
                <td><?= Html::encode((string)$h['nombre']) ?></td>
                <td class="text-center"><?= (int)$h['veces'] ?></td>
                <td class="text-center fw-bold"><?= (int)$h['cantidad'] ?></td>
                <td class="text-end"><?= number_format((float)$h['monto'], 2) ?> Bs.</td>
                <td><?= $h['ultima'] ? date('d/m/Y', strtotime((string)$h['ultima'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($habituales)): ?>
            <tr><td colspan="7" class="text-center text-muted py-3">Aún no hay compras a este proveedor</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Historial de compras -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark py-2 px-3 fw-bold" style="font-size:.88rem">
        <i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de Compras
        <span class="badge bg-light text-dark ms-2"><?= count($compras) ?></span>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem">
            <thead class="thead-custom">
            <tr>
                <th>#</th><th>Fecha</th><th>Documento</th>
                <th class="text-end">Importe Total</th>
                <th>Nota</th>
                <th class="text-center">Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($compras as $c): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $c->fecha_compra ? date('d/m/Y', strtotime((string)$c->fecha_compra)) : '—' ?></td>
                <td>
                    <?= Html::encode((string)($c->documento ?: '—')) ?>
                    <?php if ($c->nro_documento): ?>
                        <br><small class="text-muted"><?= Html::encode((string)$c->nro_documento) ?></small>
                    <?php endif; ?>
                </td>
                <td class="text-end fw-bold"><?= number_format((float)$c->importe_total, 2) ?> Bs.</td>
                <td><?= Html::encode((string)($c->nota ?: '—')) ?></td>
                <td class="text-center">
                    <?= Html::a('<i class="fa-solid fa-eye"></i>',
                        Url::to(['/compra/view', 'id' => $c->id]),
                        ['class' => 'btn btn-sm btn-outline-info', 'title' => 'Ver compra']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($compras)): ?>
            <tr><td colspan="6" class="text-center text-muted py-3">Sin compras registradas</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
