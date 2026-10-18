<?php
/**
 * @var yii\web\View $this
 * @var yii\data\SqlDataProvider $dataProvider
 */
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Estado Productos';

$rows = $dataProvider->getModels();
$esGerente = Yii::$app->user->identity && Yii::$app->user->identity->isGerente();

// KPIs globales
$totProductos = $dataProvider->getTotalCount();
$conDiferencia = 0;
$alertasGraves = 0;
$sinConteo = 0;
foreach ($rows as $r) {
    if (empty($r['ultima_actualizacion'])) {
        $sinConteo++;
        continue;
    }
    $dif = (int)$r['diferencia'];
    if ($dif !== 0) {
        $conDiferencia++;
        $sis = (int)$r['cantidad'];
        $porc = $sis > 0 ? abs($dif) * 100.0 / $sis : 100.0;
        if ($porc > 5.0) $alertasGraves++;
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-clipboard-check me-2 text-primary"></i><?= Html::encode($this->title) ?>
        <small class="text-muted fs-6 ms-2">Cuadre de inventario físico vs sistema</small>
    </h4>
</div>

<!-- Filtro por nombre -->
<form method="get" class="mb-3 d-flex gap-2" style="max-width:400px">
    <input type="text" name="buscar" value="<?= Html::encode($buscar ?? '') ?>"
           class="form-control form-control-sm" placeholder="Buscar por nombre de producto...">
    <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-search"></i></button>
    <?php if (!empty($buscar)): ?>
        <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-sm btn-outline-secondary">✕</a>
    <?php endif; ?>
</form>

<?php foreach (['success', 'warning', 'info', 'error'] as $type):
    $msg = Yii::$app->session->getFlash($type);
    if ($msg):
        $cls = $type === 'error' ? 'danger' : $type;
?>
<div class="alert alert-<?= $cls ?> alert-dismissible fade show py-2" role="alert">
    <?= Html::encode($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; endforeach; ?>

<!-- KPIs -->
<?php if ($esGerente): ?>
<div class="row g-2 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                     style="width:38px;height:38px"><i class="fa-solid fa-box"></i></div>
                <div>
                    <div class="text-muted small">Total productos</div>
                    <div class="fs-5 fw-bold"><?= $totProductos ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2"
                     style="width:38px;height:38px"><i class="fa-solid fa-question"></i></div>
                <div>
                    <div class="text-muted small">Sin conteo físico</div>
                    <div class="fs-5 fw-bold"><?= $sinConteo ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-2"
                     style="width:38px;height:38px"><i class="fa-solid fa-scale-unbalanced"></i></div>
                <div>
                    <div class="text-muted small">Con diferencia</div>
                    <div class="fs-5 fw-bold"><?= $conDiferencia ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex align-items-center">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2"
                     style="width:38px;height:38px"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="text-muted small">Alertas graves (&gt;5%)</div>
                    <div class="fs-5 fw-bold"><?= $alertasGraves ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; // esGerente KPIs ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table df-separated-table align-middle mb-0', 'style' => 'font-size:.83rem'],
    'options'      => ['class' => 'card border-0 shadow-sm'],
    'summary'      => '',
    'emptyText'    => '<div class="text-center text-muted p-4">Sin productos registrados</div>',
    'pager'        => [
        'options'           => ['class' => 'pagination pagination-lg justify-content-center mt-3'],
        'linkOptions'       => ['class' => 'page-link px-3 py-2', 'style' => 'font-size:1rem'],
        'disabledPageCssClass' => 'disabled',
        'activePageCssClass'   => 'active',
        'linkContainerOptions' => ['class' => 'page-item'],
        'prevPageLabel'     => '‹ Anterior',
        'nextPageLabel'     => 'Siguiente ›',
        'firstPageLabel'    => '« Primera',
        'lastPageLabel'     => 'Última »',
        'maxButtonCount'    => 8,
    ],
    'columns' => [
        [
            'header' => 'Código',
            'format' => 'raw',
            'value'  => static fn($r): string =>
                '<span class="badge bg-primary">' . Html::encode((string)$r['codigo']) . '</span>',
        ],
        [
            'header' => 'Producto',
            'format' => 'raw',
            'value'  => static fn($r): string =>
                '<strong>' . Html::encode((string)$r['producto']) . '</strong>'
                . '<br><small class="text-muted">' . Html::encode((string)$r['categoria']) . '</small>',
        ],
        [
            'header'        => 'Cantidad',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center fw-bold'],
            'value'         => static fn($r): int => (int)$r['cantidad'],
        ],
        [
            'header'        => 'Entregados',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static fn($r): int => (int)$r['entregados'],
        ],
        [
            'header'        => 'Total Físico',
            'format'        => 'raw',
            'headerOptions' => ['class' => 'text-center', 'style' => $esGerente ? '' : 'display:none'],
            'contentOptions'=> ['class' => 'text-center', 'style' => $esGerente ? '' : 'display:none'],
            'value'         => static function ($r) use ($esGerente): string {
                if (!$esGerente) return '';
                if (empty($r['ultima_actualizacion'])) {
                    return '<span class="text-muted small">Sin contar</span>';
                }
                return '<span class="badge bg-info">' . (int)$r['total_fisico'] . '</span>';
            },
        ],
        [
            'header'        => 'Enviados',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static fn($r): int => (int)$r['enviados'],
        ],
        [
            'header'        => 'Confirmados',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static fn($r): int => (int)$r['confirmados'],
        ],
        [
            'header'        => 'Pendiente',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions'=> ['class' => 'text-center'],
            'value'         => static fn($r): int => (int)$r['pendiente'],
        ],
        [
            'header'        => 'Diferencia',
            'format'        => 'raw',
            'headerOptions' => ['class' => 'text-center', 'style' => $esGerente ? '' : 'display:none'],
            'contentOptions'=> ['class' => 'text-center', 'style' => $esGerente ? '' : 'display:none'],
            'value'         => static function ($r) use ($esGerente): string {
                if (!$esGerente) return '';
                if (empty($r['ultima_actualizacion'])) {
                    return '<span class="text-muted">—</span>';
                }
                $dif = (int)$r['diferencia'];
                $sis = (int)$r['cantidad'];
                $porc = $sis > 0 ? abs($dif) * 100.0 / $sis : ($dif !== 0 ? 100.0 : 0.0);
                if ($dif === 0) {
                    return '<span class="badge bg-success">OK 0</span>';
                }
                $cls = $porc > 5.0 ? 'bg-danger' : 'bg-warning text-dark';
                $sign = $dif > 0 ? '+' : '';
                $icon = $porc > 5.0 ? '<i class="fa-solid fa-triangle-exclamation me-1"></i>' : '';
                return '<span class="badge ' . $cls . '">' . $icon . $sign . $dif
                    . ' (' . number_format($porc, 1) . '%)</span>';
            },
        ],
        [
            'header'        => 'Última Actualización',
            'headerOptions' => ['class' => 'text-nowrap', 'style' => $esGerente ? '' : 'display:none'],
            'contentOptions'=> ['class' => 'text-nowrap small', 'style' => $esGerente ? '' : 'display:none'],
            'format'        => 'raw',
            'value'         => static function ($r) use ($esGerente): string {
                if (!$esGerente) return '';
                return empty($r['ultima_actualizacion'])
                    ? '<span class="text-muted">Nunca</span>'
                    : date('d/m/Y H:i', strtotime((string)$r['ultima_actualizacion']));
            },
        ],
        [
            'header'        => 'Acciones',
            'format'        => 'raw',
            'headerOptions' => ['class' => 'text-center', 'style' => ($esGerente ? '' : 'display:none') . ';width:130px'],
            'contentOptions'=> ['class' => 'text-center', 'style' => $esGerente ? '' : 'display:none'],
            'value'         => static function ($r) use ($esGerente): string {
                if (!$esGerente) return '';
                $btnContar = '<button type="button" class="btn btn-sm btn-outline-primary"'
                    . ' data-bs-toggle="modal" data-bs-target="#modalConteo'
                    . (int)$r['id'] . '" title="Registrar conteo">'
                    . '<i class="fa-solid fa-clipboard-list"></i></button>';

                $btnAjustar = '';
                if (!empty($r['ultima_actualizacion']) && (int)$r['diferencia'] !== 0) {
                    $url = \yii\helpers\Url::to(['/estado-producto/ajustar-stock', 'id' => (int)$r['id']]);
                    $btnAjustar = '<form method="post" action="' . $url
                        . '" style="display:inline" onsubmit="return confirm(\'Ajustar stock del sistema al valor del conteo fisico?\');">'
                        . '<input type="hidden" name="' . Yii::$app->request->csrfParam . '" value="'
                        . Yii::$app->request->csrfToken . '">'
                        . '<button type="submit" class="btn btn-sm btn-outline-warning ms-1" title="Ajustar stock">'
                        . '<i class="fa-solid fa-scale-balanced"></i></button></form>';
                }
                return $btnContar . $btnAjustar;
            },
        ],
    ],
]) ?>

<!-- Modales por producto (solo Gerente) -->
<?php if ($esGerente): ?>
<?php foreach ($rows as $r):
    $pid = (int)$r['id'];
?>
<div class="modal fade" id="modalConteo<?= $pid ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="<?= \yii\helpers\Url::to(['/estado-producto/registrar-conteo', 'id' => $pid]) ?>" class="modal-content">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title">
                    <i class="fa-solid fa-clipboard-list me-2"></i>
                    Registrar Conteo Físico — <?= Html::encode((string)$r['codigo']) ?>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><strong><?= Html::encode((string)$r['producto']) ?></strong></div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Stock en sistema</div>
                        <div class="fs-5 fw-bold text-primary"><?= (int)$r['cantidad'] ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Último conteo</div>
                        <div class="fs-6">
                            <?= empty($r['ultima_actualizacion'])
                                ? '<span class="text-muted">Nunca</span>'
                                : date('d/m/Y H:i', strtotime((string)$r['ultima_actualizacion'])) ?>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Cantidad física contada *</label>
                    <input type="number" name="cantidad_fisica" class="form-control" min="0" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Nota (opcional)</label>
                    <textarea name="nota" class="form-control" rows="2" maxlength="255"></textarea>
                </div>
                <div class="alert alert-info py-2 mb-0 small">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Si la diferencia supera el 5% del stock se generará una alerta.
                    El stock NO se ajusta automáticamente — use el botón de balance.
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Registrar conteo
                </button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php endif; // esGerente modales ?>
