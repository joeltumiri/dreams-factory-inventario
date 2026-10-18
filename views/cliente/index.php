<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $tipos */
/** @var array $ciudades */
/** @var array $zonas */
/** @var array $filtros */

use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Clientes';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="m-0"><i class="fa-solid fa-store me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
        <small class="text-muted">Farmacias y tiendas — Dreams Factory · El Alto</small>
    </div>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-chart-bar me-1"></i> Reporte gestión', ['reporte'], ['class' => 'btn btn-outline-info']) ?>
        <?= Html::a('<i class="fa-solid fa-file-export me-1"></i> Exportar CSV', ['exportar'], ['class' => 'btn btn-outline-success']) ?>
        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nuevo cliente', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="text" name="q" value="<?= Html::encode($filtros['q']) ?>" class="form-control form-control-sm" placeholder="Nombre, NIT, propietario...">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Tipo</label>
                <select name="tipo_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['tipoId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Ciudad</label>
                <select name="ciudad_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($ciudades as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $filtros['ciudadId'] === (int)$id ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Zona</label>
                <input type="text" name="zona" list="zonas-dl" value="<?= Html::encode($filtros['zona']) ?>" class="form-control form-control-sm">
                <datalist id="zonas-dl">
                    <?php foreach ($zonas as $z): ?><option value="<?= Html::encode($z) ?>"><?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i></a>
            </div>
        </form>
    </div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle'],
    'summary' => '<small class="text-muted">Mostrando {begin}-{end} de {totalCount} cliente(s)</small>',
    'emptyText' => 'No se encontraron clientes.',
    'columns' => [
        [
            'header' => 'Cliente',
            'format' => 'raw',
            'value' => function ($m) {
                $name = Html::encode($m->getNombreDisplay());
                $prop = $m->propietario ? '<small class="text-muted d-block">' . Html::encode($m->propietario) . '</small>' : '';
                return '<strong>' . $name . '</strong>' . $prop;
            },
        ],
        [
            'header' => 'Tipo',
            'format' => 'raw',
            'value' => fn($m) => $m->tipo
                ? '<span class="badge bg-info-subtle text-info border border-info-subtle">' . Html::encode($m->tipo->farmacia) . '</span>'
                : '—',
        ],
        [
            'header' => 'NIT',
            'attribute' => 'nit',
            'value' => fn($m) => $m->nit ?: '—',
        ],
        [
            'header' => 'Ciudad / Zona',
            'format' => 'raw',
            'value' => function ($m) {
                $ciudad = $m->ciudad ? Html::encode($m->ciudad->nombre) : '—';
                $zona = $m->zona ? '<small class="text-muted d-block">' . Html::encode($m->zona) . '</small>' : '';
                return $ciudad . $zona;
            },
        ],
        [
            'header' => 'Contacto',
            'format' => 'raw',
            'value' => function ($m) {
                $tel = $m->contacto ? '<i class="fa-solid fa-phone me-1 text-muted"></i>' . Html::encode($m->contacto) : '';
                $email = $m->email ? '<small class="d-block text-muted"><i class="fa-solid fa-envelope me-1"></i>' . Html::encode($m->email) . '</small>' : '';
                return ($tel ?: '—') . $email;
            },
        ],
        [
            'header' => 'Acciones',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions' => ['class' => 'text-end', 'style' => 'width:160px'],
            'value' => function ($m) {
                $view = Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver']);
                $upd  = Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Editar']);
                $map  = '';
                if ($m->latitud && $m->longitud) {
                    $url = "https://www.google.com/maps?q={$m->latitud},{$m->longitud}";
                    $map = Html::a('<i class="fa-solid fa-map-location-dot"></i>', $url, ['class' => 'btn btn-sm btn-outline-success', 'target' => '_blank', 'title' => 'Ver en mapa']);
                }
                return $view . ' ' . $upd . ' ' . $map;
            },
        ],
    ],
]); ?>
