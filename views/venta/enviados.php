<?php
/**
 * @var yii\web\View             $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $clientes
 * @var array $filtros
 */
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\helpers\Url;
use app\models\Venta;

$this->title = 'Pedidos Enviados';
$hoy = new DateTime();
/** @var \app\models\User $user */
$user = Yii::$app->user->identity;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-truck me-2 text-info"></i><?= Html::encode($this->title) ?></h4>
</div>

<form method="get" class="row g-2 mb-3 align-items-end">
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
        <a href="<?= Url::to(['enviados']) ?>" class="btn btn-sm btn-outline-secondary">✕</a>
    </div>
</form>

<div class="table-responsive">
<table class="table table-dark table-hover table-bordered align-middle">
    <thead>
        <tr>
            <th>#</th><th>Código</th><th>Cliente</th><th>Vendedor</th>
            <th>Fecha Pedido</th><th>Fecha Envío</th><th>Días tránsito</th>
            <th class="text-end">Importe</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php $models = $dataProvider->getModels(); ?>
    <?php if (empty($models)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">
            <i class="fa-solid fa-inbox fa-2x d-block mb-2 opacity-30"></i>No hay pedidos enviados
        </td></tr>
    <?php else: ?>
    <?php foreach ($models as $idx => $m): ?>
    <?php
        $diasTransito = '—';
        if ($m->fecha_envio) {
            $envio = new DateTime($m->fecha_envio);
            $diasTransito = $hoy->diff($envio)->days . ' día(s)';
        }
        $puedeEntregarBtn = $user->isEncargado() || $user->isGerente();
    ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><span class="badge bg-info text-dark"><?= Html::encode($m->codigo) ?></span></td>
            <td><?= Html::encode($m->cliente?->getNombreDisplay() ?? '—') ?></td>
            <td><?= Html::encode($m->user?->getNombreCompleto() ?? '—') ?></td>
            <td><?= date('d/m/Y', strtotime($m->fecha_pedido)) ?></td>
            <td><?= $m->fecha_envio ? date('d/m/Y H:i', strtotime($m->fecha_envio)) : '—' ?></td>
            <td><span class="badge bg-secondary"><?= $diasTransito ?></span></td>
            <td class="text-end">Bs. <?= number_format((float)$m->importe_total, 2) ?></td>
            <td>
                <div class="d-flex gap-1 flex-wrap">
                    <?= Html::a('<i class="fa-solid fa-eye"></i>', ['detalle-pedido', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-info', 'title' => 'Ver detalle']) ?>
                    <?php if ($puedeEntregarBtn): ?>
                    <?= Html::a('<i class="fa-solid fa-circle-check me-1"></i>Entregado', ['marcar-entregado', 'id' => $m->id], [
                        'class' => 'btn btn-sm btn-primary',
                        'data'  => ['confirm' => "¿Confirmar que el pedido {$m->codigo} fue entregado al cliente?"],
                    ]) ?>
                    <?php endif; ?>
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
