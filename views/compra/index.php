<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Compra;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Compras';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Nueva compra', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table table-hover'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'id',
        [
            'attribute' => 'fecha_compra',
            'format' => ['date', 'php:Y-m-d'],
        ],
        [
            'label' => 'Proveedor',
            'value' => static fn($m) => $m->proveedor ? $m->proveedor->nombre : '',
        ],
        [
            'label' => 'Registrado por',
            'value' => static function ($m) {
                $u = $m->createdByUser;
                if ($u === null) return '—';
                $nombre = trim(implode(' ', array_filter([$u->paterno, $u->materno, $u->nombre])));
                return $nombre ?: $u->username;
            },
        ],
        'importe_total:currency',
        [
            'attribute' => 'estado',
            'format' => 'raw',
            'value' => static function (Compra $m) {
                if ((int)$m->estado === Compra::ESTADO_CONFIRMADA) {
                    return '<span class="badge bg-success">Confirmada</span>';
                }
                return '<span class="badge bg-secondary">Borrador</span>';
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
        ],
    ],
]) ?>
