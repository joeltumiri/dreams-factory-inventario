<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var string $tab
 * @var array $counts
 */
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Stock Productos';

$tabs = [
    'disponible' => ['label' => 'A Disposición', 'icon' => 'fa-circle-check',     'cls' => 'success'],
    'alerta'     => ['label' => 'Alerta Stock',  'icon' => 'fa-triangle-exclamation','cls' => 'warning'],
    'bajo'       => ['label' => 'Bajo Stock',    'icon' => 'fa-circle-xmark',     'cls' => 'danger'],
];
$current = $tabs[$tab];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-boxes-stacked me-2 text-primary"></i><?= Html::encode($this->title) ?>
        <small class="text-muted fs-6 ms-2">Control por niveles de stock</small>
    </h4>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['exportar-excel', 'tab' => $tab]) ?>" class="btn btn-success btn-sm">
            <i class="fa-solid fa-file-excel me-1"></i> Excel
        </a>
        <a href="<?= Url::to(['exportar-pdf', 'tab' => $tab]) ?>" target="_blank" class="btn btn-danger btn-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> PDF
        </a>
        <a href="<?= Url::to(['orden-sugerida']) ?>" class="btn btn-warning btn-sm text-dark">
            <i class="fa-solid fa-cart-plus me-1"></i> Orden de Compra Sugerida
        </a>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-0">
    <?php foreach ($tabs as $key => $t):
        $active = $key === $tab; ?>
    <li class="nav-item">
        <a class="nav-link <?= $active ? 'active fw-bold' : '' ?>"
           href="<?= Url::to(['index', 'tab' => $key]) ?>">
            <i class="fa-solid <?= $t['icon'] ?> text-<?= $t['cls'] ?> me-1"></i>
            <?= $t['label'] ?>
            <span class="badge bg-<?= $t['cls'] ?> ms-1"><?= (int)$counts[$key] ?></span>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<!-- separador visual tabs/tabla -->

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle mb-0'],
    'options'      => ['class' => 'card border-0 shadow-sm rounded-0'],
    'summary'      => '<div class="px-3 pt-2 small text-muted">Mostrando <b>{begin}</b>-<b>{end}</b> de <b>{totalCount}</b> (orden: stock menor a mayor)</div>',
    'emptyText'    => '<div class="text-center text-muted p-4">No hay productos en esta categoría de stock</div>',
    'columns' => [
        [
            'header' => 'Código',
            'format' => 'raw',
            'value'  => static fn($p): string =>
                '<span class="badge bg-primary">' . Html::encode((string)$p->codigo) . '</span>',
        ],
        [
            'header' => 'Categoría',
            'value'  => static fn($p): string => $p->categoria ? $p->categoria->nombre : '—',
        ],
        [
            'header' => 'Producto',
            'format' => 'raw',
            'value'  => static fn($p): string => '<strong>' . Html::encode($p->nombre) . '</strong>',
        ],
        [
            'header'        => 'Stock Mínimo',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static fn($p): int => (int)$p->stock_minimo,
        ],
        [
            'header'        => 'Stock Actual',
            'format'        => 'raw',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static function ($p) use ($tab): string {
                $cls = $tab === 'disponible' ? 'bg-success'
                    : ($tab === 'alerta' ? 'bg-warning text-dark' : 'bg-danger');
                return '<span class="badge ' . $cls . ' fs-6">' . (int)$p->stock . '</span>';
            },
        ],
    ],
]) ?>
