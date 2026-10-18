<?php
/**
 * @var yii\web\View              $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

use app\models\Venta;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Historial de Ventas';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nueva Venta', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table table-hover'],
    'summary'      => '',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'codigo',
            'format'    => 'raw',
            'value'     => static fn(Venta $m) =>
                '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-semibold">'
                . Html::encode($m->codigo) . '</span>',
        ],
        [
            'attribute' => 'fecha_pedido',
            'format'    => ['date', 'php:d/m/Y'],
        ],
        [
            'label' => 'Cliente',
            'value' => static fn(Venta $m) => $m->cliente ? $m->cliente->getNombreDisplay() : '-',
        ],
        [
            'label' => 'Vendedor',
            'value' => static fn(Venta $m) => $m->user ? $m->user->getNombreCompleto() : '-',
        ],
        [
            'attribute' => 'importe_total',
            'format'    => 'raw',
            'value'     => static fn(Venta $m) =>
                '<span class="fw-bold text-success">' . number_format((float)$m->importe_total, 2) . ' Bs.</span>',
        ],
        [
            'attribute' => 'descuento',
            'format'    => 'raw',
            'value'     => static fn(Venta $m) =>
                (float)$m->descuento > 0
                ? '<span class="text-danger">-' . number_format((float)$m->descuento, 2) . ' Bs.</span>'
                : '<span class="text-muted">—</span>',
        ],
        [
            'attribute' => 'total_efectivo',
            'label'     => 'Total',
            'format'    => 'raw',
            'value'     => static fn(Venta $m) =>
                '<strong>' . number_format((float)$m->total_efectivo, 2) . ' Bs.</strong>',
        ],
        [
            'label'  => 'Estado Venta',
            'format' => 'raw',
            'value'  => static function (Venta $m): string {
                $badges = [1 => 'warning', 2 => 'info', 3 => 'success'];
                $names  = [1 => 'Para Entregar', 2 => 'Enviada', 3 => 'Entregada'];
                $id     = (int)$m->estado_venta_id;
                $color  = $badges[$id] ?? 'secondary';
                $name   = $names[$id] ?? '-';
                return '<span class="badge bg-' . $color . '">' . Html::encode($name) . '</span>';
            },
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{view} {print}',
            'buttons'  => [
                'print' => static fn($url, Venta $m) => Html::a(
                    '<i class="fa-solid fa-print"></i>',
                    ['imprimir-orden', 'id' => $m->id],
                    [
                        'title'        => 'Imprimir',
                        'class'        => 'text-primary',
                        'target'       => '_blank',
                        'data-pjax'    => '0',
                    ]
                ),
            ],
        ],
    ],
]) ?>
