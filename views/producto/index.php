<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $categorias
 * @var string $q
 * @var int $catId
 */

use app\models\Producto;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Productos';
$esGerente = Yii::$app->user->identity && Yii::$app->user->identity->isGerente();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-box-open me-2 text-primary"></i><?= Html::encode($this->title) ?>
    </h4>
    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> Nuevo Producto', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
</div>

<!-- ── Filtros ─────────────────────────────────────────────────────────── -->
<form method="get" id="filter-form" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Buscar (código o nombre)</label>
                <input type="text" name="q" id="q-input" class="form-control form-control-sm"
                       placeholder="Ej: BG-P5  ó  biberón vidrio"
                       value="<?= Html::encode($q) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Ingresar código exacto</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="codigo-exact" class="form-control form-control-sm"
                           placeholder="Ej: BG-A1">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-buscar-codigo">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                </div>
                <div id="codigo-not-found" class="text-danger small mt-1" style="display:none;">
                    No se encontró ningún producto con el código ingresado.
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Categoría</label>
                <select name="categoria_id" class="form-select form-select-sm">
                    <option value="">— Todas —</option>
                    <?php foreach ($categorias as $cid => $cnombre): ?>
                    <option value="<?= $cid ?>" <?= $catId === $cid ? 'selected' : '' ?>>
                        <?= Html::encode($cnombre) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                </button>
                <a href="<?= Yii::$app->urlManager->createUrl(['/producto/index']) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Limpiar
                </a>
            </div>
        </div>
    </div>
</form>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => '',
    'tableOptions' => ['class' => 'table df-separated-table table-hover align-middle', 'id' => 'productos-table'],
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label'  => 'Producto',
            'format' => 'raw',
            'value'  => function (Producto $m): string {
                return '<strong>' . Html::encode($m->nombre) . '</strong>';
            },
        ],
        [
            'label' => 'Categoría',
            'value' => fn(Producto $m): string => $m->categoria ? $m->categoria->nombre : '—',
        ],
        [
            'attribute'      => 'precio_venta',
            'label'          => 'P. Venta',
            'contentOptions' => ['class' => 'text-end'],
            'headerOptions'  => ['class' => 'text-end'],
            'value'          => fn(Producto $m): string => number_format((float)$m->precio_venta, 2) . ' Bs.',
        ],
        [
            'attribute'      => 'stock',
            'label'          => 'Stock',
            'format'         => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'headerOptions'  => ['class' => 'text-center'],
            'value'          => function (Producto $m): string {
                $cls = 'bg-success';
                if ($m->stock <= 0) {
                    $cls = 'bg-danger';
                } elseif ($m->stock_minimo > 0 && $m->stock <= $m->stock_minimo) {
                    $cls = 'bg-warning text-dark';
                }
                return '<span class="badge ' . $cls . '">' . $m->stock . '</span>';
            },
        ],
        [
            'attribute'      => 'activo',
            'label'          => 'Estado',
            'format'         => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'headerOptions'  => ['class' => 'text-center'],
            'value'          => fn(Producto $m): string => $m->activo
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-secondary">Inactivo</span>',
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'header'   => 'Acciones',
            'template' => $esGerente ? '{view} {update} {toggle} {delete}' : '{view} {update} {toggle}',
            'buttons'  => [
                'view' => fn($url, Producto $m) => Html::a(
                    '<i class="fa-solid fa-eye"></i>',
                    ['view', 'id' => $m->id],
                    ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Ver detalle']
                ),
                'update' => fn($url, Producto $m) => Html::a(
                    '<i class="fa-solid fa-pen"></i>',
                    ['update', 'id' => $m->id],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Editar']
                ),
                'toggle' => fn($url, Producto $m) => Html::a(
                    $m->activo ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>',
                    ['toggle', 'id' => $m->id],
                    [
                        'class' => 'btn btn-sm btn-outline-' . ($m->activo ? 'warning' : 'success'),
                        'title' => $m->activo ? 'Desactivar' : 'Activar',
                        'data'  => [
                            'confirm' => $m->activo
                                ? "Desactivar producto: {$m->nombre}"
                                : "Activar producto: {$m->nombre}",
                        ],
                    ]
                ),
                'delete' => fn($url, Producto $m) => Html::a(
                    '<i class="fa-solid fa-trash"></i>',
                    ['delete', 'id' => $m->id],
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'title' => 'Eliminar',
                        'data'  => [
                            'confirm' => "¿Eliminar definitivamente el producto «{$m->nombre}»? Solo se permite si no tiene compras ni ventas.",
                            'method'  => 'post',
                        ],
                    ]
                ),
            ],
        ],
    ],
]) ?>

<script>
// ── Filtro en tiempo real por nombre ──────────────────────────────
const qInput = document.getElementById('q-input');
if (qInput) {
    let timer;
    qInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            document.getElementById('filter-form').submit();
        }, 400);
    });
}

// ── Búsqueda por código exacto ────────────────────────────────────
const codigoInput = document.getElementById('codigo-exact');
const btnBuscar   = document.getElementById('btn-buscar-codigo');
const notFound    = document.getElementById('codigo-not-found');
const table       = document.getElementById('productos-table');

function buscarPorCodigo() {
    const val = (codigoInput?.value || '').trim().toLowerCase();
    notFound.style.display = 'none';
    if (!val || !table) return;

    let found = false;
    table.querySelectorAll('tbody tr').forEach(tr => {
        const badge  = tr.querySelector('.badge.bg-primary');
        const codigo = (badge?.textContent || '').trim().toLowerCase();
        if (codigo === val) {
            tr.style.display = '';
            found = true;
        } else {
            tr.style.display = 'none';
        }
    });

    if (!found) notFound.style.display = 'block';
}

btnBuscar?.addEventListener('click', buscarPorCodigo);
codigoInput?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); buscarPorCodigo(); }
});
</script>
