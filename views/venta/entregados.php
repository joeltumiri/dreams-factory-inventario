<?php
/**
 * @var yii\web\View             $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $clientes
 * @var array $vendedores
 * @var array $filtros
 */
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\helpers\Url;

$this->title = 'Pedidos Entregados';
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-circle-check me-2 text-success"></i><?= Html::encode($this->title) ?></h4>
</div>

<form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-md-3">
        <?= Html::dropDownList('cliente_id', $filtros['clienteId'], [0 => 'Todos los clientes'] + $clientes, ['class' => 'form-select form-select-sm']) ?>
    </div>
    <?php if (!empty($vendedores)): ?>
    <div class="col-md-3">
        <?= Html::dropDownList('user_id', $filtros['userId'], [0 => 'Todos los vendedores'] + $vendedores, ['class' => 'form-select form-select-sm']) ?>
    </div>
    <?php endif; ?>
    <div class="col-md-2">
        <input type="date" name="desde" class="form-control form-control-sm" value="<?= Html::encode($filtros['desde']) ?>">
    </div>
    <div class="col-md-2">
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= Html::encode($filtros['hasta']) ?>">
    </div>
    <div class="col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= Url::to(['entregados']) ?>" class="btn btn-sm btn-outline-secondary">✕</a>
    </div>
</form>

<div class="table-responsive">
<table class="table table-dark table-hover table-bordered align-middle">
    <thead>
        <tr>
            <th>#</th><th>Código</th><th>Cliente</th><th>Vendedor</th>
            <th>Fecha Pedido</th><th>Fecha Entrega Real</th><th>Tiempo total</th>
            <th class="text-end">Importe</th>
        </tr>
    </thead>
    <tbody>
    <?php $models = $dataProvider->getModels(); ?>
    <?php if (empty($models)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">
            <i class="fa-solid fa-inbox fa-2x d-block mb-2 opacity-30"></i>No hay pedidos entregados
        </td></tr>
    <?php else: ?>
    <?php foreach ($models as $idx => $m): ?>
    <?php
        $tiempoHtml = '—';
        if ($m->fecha_entrega_real && $m->fecha_pedido) {
            $pedido   = new DateTime($m->fecha_pedido);
            $entrega  = new DateTime($m->fecha_entrega_real);
            $dias     = (int)$entrega->diff($pedido)->days;
            // a tiempo: si fecha_entrega_real <= fecha_entrega comprometida
            $aTiempo  = !$m->fecha_entrega || $m->fecha_entrega_real <= $m->fecha_entrega . ' 23:59:59';
            $color    = $aTiempo ? 'success' : 'danger';
            $tiempoHtml = "<span class=\"badge bg-{$color}\">{$dias} día(s)</span>";
        }
    ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><span class="badge bg-success"><?= Html::encode($m->codigo) ?></span></td>
            <td><?= Html::encode($m->cliente?->getNombreDisplay() ?? '—') ?></td>
            <td><?= Html::encode($m->user?->getNombreCompleto() ?? '—') ?></td>
            <td><?= date('d/m/Y', strtotime($m->fecha_pedido)) ?></td>
            <td><?= $m->fecha_entrega_real ? date('d/m/Y H:i', strtotime($m->fecha_entrega_real)) : '—' ?></td>
            <td><?= $tiempoHtml ?></td>
            <td class="text-end">Bs. <?= number_format((float)$m->importe_total, 2) ?></td>
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
