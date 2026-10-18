<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

use app\models\Categoria;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Categorías';
$esGerente = Yii::$app->user->identity && Yii::$app->user->identity->isGerente();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-tags me-2 text-primary"></i><?= Html::encode($this->title) ?>
    </h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nueva Categoría', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => '',
    'tableOptions' => ['class' => 'table df-separated-table table-hover align-middle'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'id',
            'label'     => 'ID',
            'contentOptions' => ['style' => 'width:60px'],
        ],
        [
            'label'  => 'Categoría',
            'format' => 'raw',
            'value'  => fn(Categoria $m): string =>
                '<i class="fa-solid fa-tag text-primary me-2"></i><strong>' . Html::encode($m->nombre) . '</strong>',
        ],
        [
            'attribute' => 'nota',
            'label'     => 'Descripción',
            'value'     => fn(Categoria $m): string => $m->nota ?: '—',
        ],
        [
            'label'          => 'Productos',
            'format'         => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'headerOptions'  => ['class' => 'text-center'],
            'value'          => fn(Categoria $m): string =>
                '<span class="badge bg-info text-dark" style="font-size:.78rem">'
                . $m->getProductosCount() . ' producto' . ($m->getProductosCount() === 1 ? '' : 's')
                . '</span>',
        ],
        [
            'attribute'      => 'activo',
            'label'          => 'Estado',
            'format'         => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'headerOptions'  => ['class' => 'text-center'],
            'value'          => fn(Categoria $m): string => $m->activo
                ? '<span class="badge bg-success">Activa</span>'
                : '<span class="badge bg-secondary">Inactiva</span>',
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'header'   => 'Acciones',
            'template' => $esGerente ? '{update} {toggle} {delete}' : '{update} {toggle}',
            'buttons'  => [
                'update' => fn($url, Categoria $m) => Html::a(
                    '<i class="fa-solid fa-pen"></i>',
                    ['update', 'id' => $m->id],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Editar']
                ),
                'toggle' => fn($url, Categoria $m) => Html::a(
                    $m->activo ? '<i class="fa-solid fa-toggle-on text-success"></i>' : '<i class="fa-solid fa-toggle-off text-secondary"></i>',
                    ['toggle', 'id' => $m->id],
                    [
                        'class' => 'btn btn-sm btn-outline-' . ($m->activo ? 'warning' : 'success'),
                        'title' => $m->activo ? 'Desactivar' : 'Activar',
                        'data'  => [
                            'confirm' => $m->activo
                                ? "Desactivar categoria: {$m->nombre}"
                                : "Activar categoria: {$m->nombre}",
                        ],
                    ]
                ),
                'delete' => fn($url, Categoria $m) => Html::a(
                    '<i class="fa-solid fa-trash"></i>',
                    ['delete', 'id' => $m->id],
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'title' => 'Eliminar',
                        'data'  => [
                            'confirm' => "¿Eliminar definitivamente la categoría «{$m->nombre}»?",
                            'method'  => 'post',
                        ],
                    ]
                ),
            ],
        ],
    ],
]) ?>
