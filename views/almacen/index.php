<?php
/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Almacenes';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-warehouse me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nuevo Almacén', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover table-bordered align-middle df-separated-table'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'nombre',
            'label'     => 'Nombre del Almacén',
            'value'     => fn($m) => $m->nombre,
            'content'   => fn($m) => '<strong>' . Html::encode($m->nombre) . '</strong>',
            'format'    => 'raw',
        ],
        [
            'attribute' => 'direccion',
            'label'     => 'Dirección',
            'content'   => fn($m) => $m->direccion
                ? '<i class="fa-solid fa-location-dot me-1 text-danger"></i>' . Html::encode($m->direccion)
                : '<span class="text-muted">—</span>',
            'format'    => 'raw',
        ],
        [
            'attribute' => 'contacto',
            'label'     => 'Fono / Cel.',
            'content'   => fn($m) => $m->contacto
                ? '<i class="fa-solid fa-phone me-1 text-success"></i>' . Html::encode($m->contacto)
                : '<span class="text-muted">—</span>',
            'format'    => 'raw',
        ],
        [
            'label'  => 'Fecha Creación',
            'value'  => fn($m) => $m->created_at ? date('d-m-Y', $m->created_at) : '—',
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{view} {update}',
            'buttons'  => [
                'view'   => fn($url, $m) => Html::a('<i class="fa-solid fa-eye"></i>', ['view',   'id' => $m->id], ['class' => 'btn btn-sm btn-outline-primary',  'title' => 'Ver']),
                'update' => fn($url, $m) => Html::a('<i class="fa-solid fa-pen"></i>',  ['update', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Editar']),
            ],
        ],
    ],
]) ?>
