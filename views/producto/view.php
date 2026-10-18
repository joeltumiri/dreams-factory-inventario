<?php
/**
 * @var yii\web\View $this
 * @var app\models\Producto $model
 */
use yii\bootstrap5\Html;

$this->title = ($model->codigo ?? 'BG-P' . $model->id) . ' | ' . $model->nombre;

$compras = $model->detallesCompra;
$ventas  = $model->detallesVenta;
?>
<style>
.producto-view .pv-label {
    width: 160px;
    background: #f1f3f5;
    color: #5f7993 !important;
}

html.df-dark .producto-view .pv-label {
    background: #1a2d47;
    color: #9dc1e1 !important;
}

html.df-dark .producto-view .badge.bg-light.text-dark {
    background: #1a2d47 !important;
    color: #9dc1e1 !important;
    border: 1px solid #2a4668;
}
</style>

<div class="producto-view">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">
        <i class="fa-solid fa-box me-2 text-primary"></i><?= Html::encode($this->title) ?>
        <?= $model->activo
            ? '<span class="badge bg-success ms-2" style="font-size:.7rem">Activo</span>'
            : '<span class="badge bg-secondary ms-2" style="font-size:.7rem">Inactivo</span>' ?>
    </h4>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Datos del producto -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white py-2 px-3 fw-bold" style="font-size:.88rem">
                <i class="fa-solid fa-circle-info me-2"></i>Datos del Producto
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0" style="font-size:.85rem">
                    <tr><th class="text-muted ps-3 pv-label">Número</th>
                        <td><?= Html::encode((string)$model->numero) ?></td></tr>
                    <tr><th class="text-muted ps-3 pv-label">Código</th>
                        <td><span class="badge bg-primary"><?= Html::encode((string)$model->codigo) ?></span></td></tr>
                    <tr><th class="text-muted ps-3 pv-label">Nombre</th>
                        <td><strong><?= Html::encode($model->nombre) ?></strong></td></tr>
                    <tr><th class="text-muted ps-3 pv-label">Categoría</th>
                        <td><?= Html::encode($model->categoria ? $model->categoria->nombre : '—') ?></td></tr>
                    <tr><th class="text-muted ps-3 pv-label">Precio Compra</th>
                        <td><?= number_format((float)$model->precio_compra, 2) ?> Bs.</td></tr>
                    <tr><th class="text-muted ps-3 pv-label">Precio Venta</th>
                        <td><?= number_format((float)$model->precio_venta, 2) ?> Bs.</td></tr>
                    <?php if ($model->descripcion): ?>
                    <tr><th class="text-muted ps-3 pv-label">Descripción</th>
                        <td><?= Html::encode($model->descripcion) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($model->nota): ?>
                    <tr><th class="text-muted ps-3 pv-label">Nota</th>
                        <td><?= Html::encode($model->nota) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Inventario + Stock mínimo -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white py-2 px-3 fw-bold" style="font-size:.88rem">
                <i class="fa-solid fa-boxes-stacked me-2"></i>Inventario
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col">
                        <div class="text-muted small">Stock actual</div>
                        <div class="fs-3 fw-bold text-primary"><?= $model->stock ?></div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Stock mínimo</div>
                        <div class="fs-3 fw-bold text-warning"><?= $model->stock_minimo ?></div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Estado</div>
                        <?php
                        $alert = '';
                        if ($model->stock <= 0) {
                            $alert = '<span class="badge bg-danger fs-6 mt-2">Sin stock</span>';
                        } elseif ($model->stock_minimo > 0 && $model->stock <= $model->stock_minimo) {
                            $alert = '<span class="badge bg-warning text-dark fs-6 mt-2">Bajo mínimo</span>';
                        } else {
                            $alert = '<span class="badge bg-success fs-6 mt-2">Adecuado</span>';
                        }
                        echo $alert;
                        ?>
                    </div>
                </div>

                <hr class="my-2">

                <form method="post" action="<?= Yii::$app->urlManager->createUrl(['/producto/config-stock', 'id' => $model->id]) ?>">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <label class="form-label small fw-bold text-muted mb-1">Configurar stock mínimo</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="stock_minimo" class="form-control" min="0"
                               value="<?= (int)$model->stock_minimo ?>">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Historial de Compras -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-info text-white py-2 px-3 fw-bold" style="font-size:.88rem">
        <i class="fa-solid fa-cart-arrow-down me-2"></i>Historial de Compras
        <span class="badge bg-light text-dark ms-2"><?= count($compras) ?></span>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem">
            <thead class="table-light">
            <tr>
                <th>#</th><th>Fecha</th><th>Proveedor</th>
                <th class="text-center">Cantidad</th>
                <th class="text-end">Precio Unit.</th>
                <th class="text-end">Importe</th>
                <th>Doc.</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($compras as $d):
                $c = $d->compra; ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $c && $c->fecha_compra ? date('d/m/Y', strtotime($c->fecha_compra)) : '—' ?></td>
                <td><?= Html::encode($c && $c->proveedor ? $c->proveedor->nombre : '—') ?></td>
                <td class="text-center"><strong><?= (int)$d->cantidad ?></strong></td>
                <td class="text-end"><?= number_format((float)$d->precio_unitario, 2) ?> Bs.</td>
                <td class="text-end fw-bold"><?= number_format((float)$d->importe, 2) ?> Bs.</td>
                <td><?= Html::encode($c && $c->nro_documento ? $c->nro_documento : '—') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($compras)): ?>
            <tr><td colspan="7" class="text-center text-muted py-3">Sin compras registradas</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Historial de Ventas -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-success text-white py-2 px-3 fw-bold" style="font-size:.88rem">
        <i class="fa-solid fa-cart-shopping me-2"></i>Historial de Ventas
        <span class="badge bg-light text-dark ms-2"><?= count($ventas) ?></span>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem">
            <thead class="table-light">
            <tr>
                <th>#</th><th>Código Venta</th><th>Fecha</th><th>Cliente</th>
                <th class="text-center">Cantidad</th>
                <th class="text-end">Precio Unit.</th>
                <th class="text-end">Importe</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; foreach ($ventas as $d):
                $v = $d->venta; ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $v ? '<span class="badge bg-primary">' . Html::encode($v->codigo) . '</span>' : '—' ?></td>
                <td><?= $v && $v->fecha_pedido ? date('d/m/Y', strtotime($v->fecha_pedido)) : '—' ?></td>
                <td><?= Html::encode($v && $v->cliente ? $v->cliente->getNombreDisplay() : '—') ?></td>
                <td class="text-center"><strong><?= (int)$d->cantidad ?></strong></td>
                <td class="text-end"><?= number_format((float)$d->precio_unitario, 2) ?> Bs.</td>
                <td class="text-end fw-bold"><?= number_format((float)$d->importe, 2) ?> Bs.</td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ventas)): ?>
            <tr><td colspan="7" class="text-center text-muted py-3">Sin ventas registradas</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
