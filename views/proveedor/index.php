<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $filtros
 * @var array $ciudades
 * @var string[] $rubros
 */
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Proveedores';
$f = $filtros;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-handshake me-2 text-primary"></i><?= Html::encode($this->title) ?>
        <small class="text-muted fs-6 ms-2">Gestión de proveedores</small>
    </h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nuevo Proveedor',
        ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
</div>

<?php foreach (['success', 'info', 'warning', 'error'] as $type):
    $msg = Yii::$app->session->getFlash($type);
    if ($msg):
        $cls = $type === 'error' ? 'danger' : $type;
?>
<div class="alert alert-<?= $cls ?> alert-dismissible fade show py-2" role="alert">
    <?= Html::encode($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; endforeach; ?>

<!-- Filtros -->
<form method="get" class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Buscar (nombre, razón social o NIT)</label>
                <input type="text" name="q" value="<?= Html::encode($f['q']) ?>"
                       class="form-control form-control-sm" placeholder="Ej: Distribuidora, 1234567...">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Ciudad</label>
                <select name="ciudad_id" class="form-select form-select-sm">
                    <option value="0">— Todas —</option>
                    <?php foreach ($ciudades as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $f['ciudadId'] == $id ? 'selected' : '' ?>>
                            <?= Html::encode($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Rubro</label>
                <input list="rubros-list" name="rubro" value="<?= Html::encode($f['rubro']) ?>"
                       class="form-control form-control-sm" placeholder="Cualquiera">
                <datalist id="rubros-list">
                    <?php foreach ($rubros as $r): ?>
                        <option value="<?= Html::encode($r) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="1"   <?= $f['estado'] === '1' ? 'selected' : '' ?>>Solo activos</option>
                    <option value="0"   <?= $f['estado'] === '0' ? 'selected' : '' ?>>Solo inactivos</option>
                    <option value="all" <?= $f['estado'] === 'all' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
            <div class="col-md-1 d-grid gap-1">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-eraser"></i> Limpiar
                </a>
            </div>
        </div>
    </div>
</form>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle mb-0'],
    'options'      => ['class' => 'card border-0 shadow-sm'],
    'summary'      => '<div class="px-3 pt-2 small text-muted">Mostrando <b>{begin}</b>-<b>{end}</b> de <b>{totalCount}</b> proveedores</div>',
    'emptyText'    => '<div class="text-center text-muted p-4">Sin proveedores que coincidan</div>',
    'columns' => [
        [
            'header' => 'Proveedor',
            'format' => 'raw',
            'value'  => static function ($p): string {
                $nombre = '<strong>' . \yii\bootstrap5\Html::encode($p->nombre) . '</strong>';
                $razon  = $p->razon_social
                    ? '<br><small class="text-muted">' . \yii\bootstrap5\Html::encode($p->razon_social) . '</small>'
                    : '';
                return $nombre . $razon;
            },
        ],
        [
            'header' => 'NIT',
            'format' => 'raw',
            'value'  => static fn($p): string => $p->nit
                ? '<code>' . \yii\bootstrap5\Html::encode((string)$p->nit) . '</code>'
                : '<span class="text-muted">—</span>',
        ],
        [
            'header' => 'Rubro',
            'value'  => static fn($p): string => $p->rubro ?: '—',
        ],
        [
            'header' => 'Ciudad',
            'value'  => static fn($p): string => $p->ciudad ? $p->ciudad->nombre : '—',
        ],
        [
            'header' => 'Contacto',
            'format' => 'raw',
            'value'  => static function ($p): string {
                $parts = [];
                if ($p->contacto)  $parts[] = '<i class="fa-solid fa-user me-1 text-muted"></i>' . \yii\bootstrap5\Html::encode($p->contacto);
                if ($p->celular)   $parts[] = '<i class="fa-brands fa-whatsapp me-1 text-success"></i>' . \yii\bootstrap5\Html::encode($p->celular);
                if ($p->telefono)  $parts[] = '<i class="fa-solid fa-phone me-1 text-muted"></i>' . \yii\bootstrap5\Html::encode($p->telefono);
                return $parts ? implode('<br>', $parts) : '<span class="text-muted">—</span>';
            },
        ],
        [
            'header' => 'Estado',
            'format' => 'raw',
            'value'  => static fn($p): string => (int)$p->estado === 1
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-secondary">Inactivo</span>',
        ],
        [
            'header'        => 'Acciones',
            'format'        => 'raw',
            'headerOptions' => ['class' => 'text-center', 'style' => 'width:160px'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static function ($p): string {
                $btnVer = \yii\bootstrap5\Html::a('<i class="fa-solid fa-eye"></i>',
                    ['view', 'id' => $p->id],
                    ['class' => 'btn btn-sm btn-outline-info', 'title' => 'Ver detalle e historial']);
                $btnEd = \yii\bootstrap5\Html::a('<i class="fa-solid fa-pen"></i>',
                    ['update', 'id' => $p->id],
                    ['class' => 'btn btn-sm btn-outline-warning ms-1', 'title' => 'Editar']);
                $url = \yii\helpers\Url::to(['toggle', 'id' => $p->id]);
                $iconCls = (int)$p->estado === 1 ? 'fa-toggle-on text-success' : 'fa-toggle-off text-secondary';
                $title = (int)$p->estado === 1 ? 'Desactivar' : 'Activar';
                $btnTog = '<form method="post" action="' . $url . '" style="display:inline" onsubmit="return confirm(\'Cambiar estado del proveedor?\');">'  
                    . '<input type="hidden" name="' . Yii::$app->request->csrfParam . '" value="' . Yii::$app->request->csrfToken . '">'
                    . '<button type="submit" class="btn btn-sm btn-outline-secondary ms-1" title="' . $title . '">'
                    . '<i class="fa-solid ' . $iconCls . '"></i></button></form>';
                return $btnVer . $btnEd . $btnTog;
            },
        ],
    ],
]) ?>
