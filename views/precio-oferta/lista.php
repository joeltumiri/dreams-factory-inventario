<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $ciudades */
/** @var array $categorias */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Lista de Ofertas';
$hoy = date('Y-m-d');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-tags me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nueva oferta', ['index'], ['class' => 'btn btn-primary']) ?>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Mostrar</label>
                <select name="activas" class="form-select form-select-sm">
                    <option value="1" <?= $filtros['soloActivas'] ? 'selected' : '' ?>>Solo activas</option>
                    <option value="0" <?= !$filtros['soloActivas'] ? 'selected' : '' ?>>Todas</option>
                </select>
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
                <label class="form-label small text-muted mb-1">Categoría</label>
                <select name="categoria_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['categoriaId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
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
            <div class="col-md-12 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="<?= Url::to(['lista']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
            </div>
        </form>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle'],
    'summary' => '<small class="text-muted">Mostrando {begin}-{end} de {totalCount} producto(s)</small>',
    'emptyText' => 'Sin productos.',
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
            'header' => 'P. Venta',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions'  => ['class' => 'text-end'],
            'value'  => fn($m) => number_format((float)$m['precio_venta'], 2) . ' Bs',
        ],
        [
            'header' => 'Precio Oferta',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end fw-bold text-success'],
            'headerOptions'  => ['class' => 'text-end'],
            'value'  => fn($m) => !empty($m['oferta_id'])
                ? number_format((float)$m['oferta_precio'], 2) . ' Bs'
                : '<span class="text-muted small">—</span>',
        ],
        [
            'header' => 'Vigencia',
            'format' => 'raw',
            'value'  => fn($m) => !empty($m['oferta_id'])
                ? '<small>' . Html::encode($m['fecha_inicio']) . ' → ' . Html::encode($m['fecha_final']) . '</small>'
                : '<span class="text-muted small">—</span>',
        ],
        [
            'header' => 'Ciudad',
            'value'  => fn($m) => $m['ciudad_nombre'] ?: '—',
        ],
        [
            'header' => 'Estado Oferta',
            'format' => 'raw',
            'value'  => function ($m) use ($hoy) {
                if (empty($m['oferta_id'])) {
                    return '<span class="badge bg-light text-secondary border">Sin oferta</span>';
                }
                if ($m['fecha_inicio'] > $hoy) return '<span class="badge bg-info">Programada</span>';
                if ($m['fecha_final']  < $hoy) return '<span class="badge bg-secondary">Vencida</span>';
                return '<span class="badge bg-success">Activa</span>';
            },
        ],
        [
            'header' => 'Acciones',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions'  => ['class' => 'text-end', 'style' => 'width:120px'],
            'value' => fn($m) => Html::a('<i class="fa-solid fa-pen"></i> Editar', ['update', 'id' => $m['oferta_id']], ['class' => 'btn btn-sm btn-outline-secondary']),
        ],
    ],
]); ?>
