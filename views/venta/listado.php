<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $clientes */
/** @var array $vendedores */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Listado de Entregas';

$estados = [
    1 => ['Para Entregar', 'warning'],
    2 => ['Enviada',       'info'],
    3 => ['Entregada',     'success'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-clipboard-list me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select name="estado_venta_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($estados as $id => [$label]): ?>
                        <option value="<?= $id ?>" <?= $filtros['estadoId'] === $id ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cliente</label>
                <select name="cliente_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($clientes as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['clienteId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Vendedor</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($vendedores as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['userId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode((string)$label) ?></option>
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
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter"></i></button>
                <a href="<?= Url::to(['listado']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle'],
    'summary' => '<small class="text-muted">Mostrando {begin}-{end} de {totalCount} pedido(s)</small>',
    'emptyText' => 'Sin pedidos para los filtros seleccionados.',
    'columns' => [
        [
            'header' => 'Código',
            'format' => 'raw',
            'value' => fn($m) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">' . Html::encode($m->codigo) . '</span>',
        ],
        [
            'header' => 'Fecha pedido',
            'value' => fn($m) => $m->fecha_pedido ? date('d/m/Y', strtotime($m->fecha_pedido)) : '—',
        ],
        [
            'header' => 'Fecha entrega',
            'value' => fn($m) => $m->fecha_entrega ? date('d/m/Y', strtotime($m->fecha_entrega)) : '—',
        ],
        [
            'header' => 'Cliente',
            'value' => fn($m) => $m->cliente ? $m->cliente->getNombreDisplay() : '—',
        ],
        [
            'header' => 'Vendedor',
            'value' => fn($m) => $m->user ? $m->user->getNombreCompleto() : '—',
        ],
        [
            'header' => 'Importe',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions' => ['class' => 'text-end'],
            'value' => fn($m) => number_format((float)$m->total_efectivo, 2) . ' Bs',
        ],
        [
            'header' => 'Estado',
            'format' => 'raw',
            'value' => function ($m) use ($estados) {
                $id = (int)$m->estado_venta_id;
                [$label, $color] = $estados[$id] ?? ['—', 'secondary'];
                return '<span class="badge bg-' . $color . '">' . Html::encode($label) . '</span>';
            },
        ],
        [
            'header' => 'Acciones',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions' => ['class' => 'text-end', 'style' => 'width:160px'],
            'value' => function ($m) {
                $btns = Html::a('<i class="fa-solid fa-eye"></i>', ['detalle-pedido', 'id' => $m->id],
                    ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver']);
                $btns .= ' ' . Html::a('<i class="fa-solid fa-print"></i>', ['imprimir-orden', 'id' => $m->id],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Orden de despacho', 'target' => '_blank']);
                return $btns;
            },
        ],
    ],
]); ?>
