<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $ciudades */
/** @var array $categorias */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Precio Venta';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-tag me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nuevo precio', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Buscar producto</label>
                <input type="text" id="pv-prod-search" name="q" value="<?= Html::encode($filtros['q']) ?>"
                       class="form-control form-control-sm" placeholder="Código o nombre..." autocomplete="off">
                <input type="hidden" name="producto_id" id="pv-prod-id" value="">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Ciudad</label>
                <select name="ciudad_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($ciudades as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['ciudadId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">
                    Categoría <span id="pv-cat-icon" style="display:none;color:#4ade80">✅</span>
                </label>
                <select name="categoria_id" id="pv-cat-select" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['categoriaId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i></a>
            </div>
        </form>
        <script>
        (function () {
            dfProductoAutocomplete({
                inputId:    'pv-prod-search',
                hiddenId:   'pv-prod-id',
                catSelectId:'pv-cat-select',
                catIconId:  'pv-cat-icon',
                searchUrl:  '<?= \yii\helpers\Url::to(['/producto/buscar-producto']) ?>',
            });
        })();
        </script>

    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle'],
    'summary' => '<small class="text-muted">Mostrando {begin}-{end} de {totalCount} producto(s)</small>',
    'columns' => [
        [
            'header' => 'Código',
            'format' => 'raw',
            'value' => fn($m) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'
                . Html::encode($m['codigo'] ?: 'BG-P' . $m['id']) . '</span>',
        ],
        [
            'header' => 'Producto',
            'value'  => fn($m) => $m['nombre'],
        ],
        [
            'header' => 'Categoría',
            'value'  => fn($m) => $m['categoria_nombre'] ?: '—',
        ],
        [
            'header' => 'P. Compra',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions'  => ['class' => 'text-end'],
            'value'  => fn($m) => number_format((float)$m['precio_compra'], 2) . ' Bs',
        ],
        [
            'header' => 'P. Venta General',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end fw-bold text-success'],
            'headerOptions'  => ['class' => 'text-end'],
            'value'  => fn($m) => (float)$m['precio_venta'] > 0
                ? number_format((float)$m['precio_venta'], 2) . ' Bs'
                : '<span class="text-danger small">Sin precio</span>',
        ],
        [
            'header' => 'P. Ciudad',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions'  => ['class' => 'text-end'],
            'value'  => function ($m) {
                if (!empty($m['pv_id'])) {
                    return number_format((float)$m['pv_precio'], 2) . ' Bs'
                        . '<br><small class="text-muted">' . Html::encode($m['ciudad_nombre'] ?? '') . '</small>';
                }
                return '<span class="text-muted small">—</span>';
            },
        ],
        [
            'header' => 'Acciones',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions'  => ['class' => 'text-end', 'style' => 'width:160px'],
            'value' => function ($m) {
                $btnEditar = !empty($m['pv_id'])
                    ? Html::a('<i class="fa-solid fa-pen"></i> Editar', ['update', 'id' => $m['pv_id']], ['class' => 'btn btn-sm btn-outline-secondary'])
                    : Html::a('<i class="fa-solid fa-plus"></i> Asignar', ['create', 'producto_id' => $m['id']], ['class' => 'btn btn-sm btn-outline-primary']);
                return $btnEditar;
            },
        ],
    ],
]); ?>
