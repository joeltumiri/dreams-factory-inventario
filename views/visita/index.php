<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $vendedores */
/** @var array $filtros */
/** @var bool $esGerente */
/** @var int $kpiTotal */
/** @var int $kpiConPedido */
/** @var int $kpiSinPedido */
/** @var int $kpiCerrado */

use app\models\Visita;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\helpers\Url;

$this->title = 'Registro de Visitas a Clientes';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
        <small class="text-muted">Seguimiento de visitas a farmacias y tiendas de El Alto</small>
    </div>
    <?= Html::a('Registrar visita', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-left:4px solid #3b82f6">
            <div class="small text-muted mb-1">Total visitas del mes</div>
            <div class="fs-2 fw-bold text-primary"><?= $kpiTotal ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-left:4px solid #22c55e">
            <div class="small text-muted mb-1">Con pedido</div>
            <div class="fs-2 fw-bold text-success"><?= $kpiConPedido ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-left:4px solid #f59e0b">
            <div class="small text-muted mb-1">Sin pedido</div>
            <div class="fs-2 fw-bold text-warning"><?= $kpiSinPedido ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rot-kpi-card" style="border-left:4px solid #ef4444">
            <div class="small text-muted mb-1">Cerrados del mes</div>
            <div class="fs-2 fw-bold text-danger"><?= $kpiCerrado ?></div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Buscar cliente</label>
                <input type="text" name="q" value="<?= Html::encode($filtros['q']) ?>" class="form-control form-control-sm" placeholder="Nombre de farmacia...">
            </div>
            <?php if ($esGerente): ?>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Vendedor</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="0">Todos</option>
                    <?php foreach ($vendedores as $uid => $nombre): ?>
                        <option value="<?= $uid ?>" <?= $filtros['userId'] === $uid ? 'selected' : '' ?>><?= Html::encode($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="0">Todos</option>
                    <option value="<?= Visita::ESTADO_CON_PEDIDO ?>" <?= $filtros['estado'] === Visita::ESTADO_CON_PEDIDO ? 'selected' : '' ?>>Con pedido</option>
                    <option value="<?= Visita::ESTADO_SIN_PEDIDO ?>" <?= $filtros['estado'] === Visita::ESTADO_SIN_PEDIDO ? 'selected' : '' ?>>Sin pedido</option>
                    <option value="<?= Visita::ESTADO_CERRADO ?>" <?= $filtros['estado'] === Visita::ESTADO_CERRADO ? 'selected' : '' ?>>Cerrado</option>
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
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary" title="Filtrar"></button>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary" title="Limpiar"></a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover table-bordered mb-0 align-middle">
                <thead style="background:#1a2d47">
                    <tr>
                        <th class="text-center" style="width:40px">#</th>
                        <th>Fecha visita</th>
                        <?php if ($esGerente): ?><th>Vendedor</th><?php endif; ?>
                        <th>Cliente</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Pedido vinculado</th>
                        <th>Observación</th>
                        <th class="text-center" style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $models = $dataProvider->getModels();
                $pagination = $dataProvider->getPagination();
                $offset = $pagination ? $pagination->getOffset() : 0;

                if (empty($models)):
                ?>
                <tr><td colspan="<?= $esGerente ? 8 : 7 ?>" class="text-center text-muted py-4">Sin visitas para los filtros seleccionados.</td></tr>
                <?php else: foreach ($models as $i => $v): /** @var Visita $v */ ?>
                <tr>
                    <td class="text-center text-muted small"><?= $offset + $i + 1 ?></td>
                    <td>
                        <span class="fw-semibold"><?= date('d/m/Y', strtotime($v->fecha_visita)) ?></span>
                        <?php if ($v->hora_visita): ?>
                        <br><small class="text-muted"><?= Html::encode(substr($v->hora_visita, 0, 5)) ?></small>
                        <?php endif; ?>
                    </td>
                    <?php if ($esGerente): ?>
                    <td><small><?= Html::encode($v->user?->username ?? '—') ?></small></td>
                    <?php endif; ?>
                    <td><?= Html::encode($v->cliente?->getNombreDisplay() ?? '—') ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= Visita::estadoColor($v->estado) ?>">
                            <?= Visita::estadoEmoji($v->estado) ?> <?= Visita::estadoLabel($v->estado) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($v->venta_id): ?>
                            <?= Html::a('VTA-' . str_pad((string)$v->venta_id, 3, '0', STR_PAD_LEFT), ['/venta/view', 'id' => $v->venta_id], ['class' => 'text-info small', 'target' => '_blank']) ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($v->obs): ?>
                            <?php $texto = Html::encode($v->obs); ?>
                            <?php if (mb_strlen($v->obs) > 40): ?>
                                <span data-bs-toggle="tooltip" title="<?= $texto ?>">
                                    <?= mb_substr($texto, 0, 40) ?>...
                                </span>
                            <?php else: ?>
                                <?= $texto ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?= Html::a('', ['view', 'id' => $v->id], ['class' => 'btn btn-sm btn-outline-info', 'title' => 'Ver']) ?>
                        <?= Html::a('', ['update', 'id' => $v->id], ['class' => 'btn btn-sm btn-outline-warning', 'title' => 'Editar']) ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">
            Mostrando <?= count($models) ?> de <?= $dataProvider->getTotalCount() ?> visitas
        </small>
        <div class="d-flex align-items-center gap-3">
            <?php if ($pagination): ?>
                <?= LinkPager::widget(['pagination' => $pagination, 'options' => ['class' => 'pagination pagination-sm mb-0']]) ?>
            <?php endif; ?>
            <a href="#" class="btn btn-sm btn-success">
                Exportar Excel
            </a>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
// Activar tooltips Bootstrap en esta vista
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
JS);
?>