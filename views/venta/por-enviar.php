<?php
/**
 * @var yii\web\View             $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var int   $kpiTotal
 * @var int   $kpiHoy
 * @var int   $kpiAtrasado
 * @var array $clientes
 * @var array $filtros
 */
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\helpers\Url;

$this->title = 'Pedidos por Enviar';
$hoy = date('Y-m-d');
$feBadge = static function(?string $fe) use ($hoy): string {
    if ($fe === null) return '<span class="text-muted">—</span>';
    if ($fe < $hoy)   return '<span class="text-danger fw-bold">ATRASADO<br><small>' . date('d/m/Y', strtotime($fe)) . '</small></span>';
    if ($fe === $hoy) return '<span class="text-warning fw-bold">HOY<br><small>' . date('d/m/Y', strtotime($fe)) . '</small></span>';
    return '<span class="text-success">' . date('d/m/Y', strtotime($fe)) . '</span>';
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-box-open me-2 text-warning"></i><?= Html::encode($this->title) ?></h4>
</div>
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="rot-kpi-card border-danger">
            <div class="rot-kpi-val text-danger"><?= $kpiTotal ?></div>
            <div class="rot-kpi-label">Total pendientes</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="rot-kpi-card border-warning">
            <div class="rot-kpi-val text-warning"><?= $kpiHoy ?></div>
            <div class="rot-kpi-label"><i class="fa-solid fa-clock text-warning me-1"></i>Urgentes hoy</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="rot-kpi-card border-danger <?= $kpiAtrasado > 0 ? 'kpi-blink' : '' ?>">
            <div class="rot-kpi-val text-danger"><?= $kpiAtrasado ?></div>
            <div class="rot-kpi-label"><i class="fa-solid fa-triangle-exclamation text-danger me-1"></i>Atrasados</div>
        </div>
    </div>
</div>
<form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-md-3">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Código venta..." value="<?= Html::encode($filtros['qSearch']) ?>">
    </div>
    <div class="col-md-3">
        <?= Html::dropDownList('cliente_id', $filtros['clienteId'], [0 => 'Todos los clientes'] + $clientes, ['class' => 'form-select form-select-sm']) ?>
    </div>
    <div class="col-md-2">
        <input type="date" name="desde" class="form-control form-control-sm" value="<?= Html::encode($filtros['desde']) ?>">
    </div>
    <div class="col-md-2">
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= Html::encode($filtros['hasta']) ?>">
    </div>
    <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= Url::to(['por-enviar']) ?>" class="btn btn-sm btn-outline-secondary">✕</a>
    </div>
</form>
<div class="table-responsive">
<table class="table table-dark table-hover table-bordered align-middle">
    <thead>
        <tr>
            <th>#</th><th>Código</th><th>Cliente</th><th>Vendedor</th>
            <th>Fecha Pedido</th><th>Fecha Entrega</th>
            <th class="text-end">Importe</th><th class="text-center">Items</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php $models = $dataProvider->getModels(); ?>
    <?php if (empty($models)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">
            <i class="fa-solid fa-inbox fa-2x d-block mb-2 opacity-30"></i>No hay pedidos pendientes de envío
        </td></tr>
    <?php else: ?>
    <?php foreach ($models as $idx => $m): ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><span class="badge bg-warning text-dark"><?= Html::encode($m->codigo) ?></span></td>
            <td><?= Html::encode($m->cliente?->getNombreDisplay() ?? '—') ?></td>
            <td><?= Html::encode($m->user?->getNombreCompleto() ?? '—') ?></td>
            <td><?= date('d/m/Y', strtotime($m->fecha_pedido)) ?></td>
            <td><?= $feBadge($m->fecha_entrega) ?></td>
            <td class="text-end">Bs. <?= number_format((float)$m->importe_total, 2) ?></td>
            <td class="text-center"><span class="badge bg-secondary"><?= count($m->detalles) ?></span></td>
            <td>
                <div class="d-flex gap-1 flex-wrap">
                    <?= Html::a('<i class="fa-solid fa-eye"></i>', ['detalle-pedido', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-info', 'title' => 'Ver detalle']) ?>
                    <?= Html::a('<i class="fa-solid fa-truck me-1"></i>Enviar', ['marcar-enviado', 'id' => $m->id], ['class' => 'btn btn-sm btn-success', 'data' => ['confirm' => "¿Confirmar que el pedido {$m->codigo} fue despachado?"]]) ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted"><?= $dataProvider->getTotalCount() ?> pedido(s)</small>
    <?= LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
</div>
<?php $this->registerCss('.kpi-blink{animation:blink-border 1.2s step-start infinite}@keyframes blink-border{50%{border-color:transparent!important}}'); ?>
