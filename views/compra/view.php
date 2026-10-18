<?php

/** @var yii\web\View $this */
/** @var app\models\Compra $model */

use app\models\Compra;
use yii\bootstrap5\Html;

$this->title = 'Compra #' . $model->id;
$this->registerCss('
.df-view .card { background: var(--df-card-bg) !important; border:1px solid var(--df-card-border) !important; color: var(--df-text); }
.df-view .card-header { background: var(--df-table-head) !important; color: var(--df-text) !important; border-bottom: 1px solid var(--df-card-border) !important; }
.df-view .card .table { background: transparent !important; color: var(--df-text) !important; margin-bottom:0; }
.df-view .card .table td, .df-view .card .table th { border-color: var(--df-table-border) !important; background: transparent !important; vertical-align: middle; }
.df-view .card .table th { color: var(--df-text-muted) !important; font-weight:500; }
.df-view .card .table tbody tr:hover td { background: var(--df-table-even) !important; }
.df-view .card .table thead th { background: var(--df-table-head) !important; color: var(--df-text-muted) !important; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--df-table-border) !important; }
.df-view .card .table tfoot td { background: var(--df-table-head) !important; color: var(--df-text) !important; border-top:1px solid var(--df-table-border) !important; }
');
?>
<div class="df-view">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-semibold">Datos</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Proveedor</th><td><?= $model->proveedor ? Html::encode($model->proveedor->nombre) : '-' ?></td></tr>
                    <tr><th>Fecha</th><td><?= Html::encode($model->fecha_compra) ?></td></tr>
                    <tr><th>Registrado por</th><td><?php
                        $u = $model->createdByUser;
                        if ($u) {
                            $n = trim(implode(' ', array_filter([$u->paterno, $u->materno, $u->nombre])));
                            echo Html::encode($n ?: $u->username);
                        } else { echo '—'; }
                    ?></td></tr>
                    <tr><th>Gestión</th><td><?= Html::encode((string)$model->gestion) ?></td></tr>
                    <tr><th>Estado</th><td>
                        <?php if ((int)$model->estado === Compra::ESTADO_CONFIRMADA): ?>
                            <span class="badge bg-success">Confirmada</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Borrador</span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><th>Total</th><td class="fw-bold text-primary fs-5"><?= Yii::$app->formatter->asCurrency($model->importe_total) ?></td></tr>
                </table>
            </div>
        </div>

        <?php if ($model->nota): ?>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header fw-semibold">Nota</div>
                <div class="card-body">
                    <?= Html::encode($model->nota) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-semibold">Detalle</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">P. unitario</th>
                        <th class="text-end">Importe</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($model->detalles as $d): ?>
                        <tr>
                            <td><?= $d->producto ? Html::encode($d->producto->nombre) : '-' ?></td>
                            <td class="text-center"><?= (int)$d->cantidad ?></td>
                            <td class="text-end"><?= Yii::$app->formatter->asCurrency($d->precio_unitario) ?></td>
                            <td class="text-end fw-bold"><?= Yii::$app->formatter->asCurrency($d->importe) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">TOTAL</td>
                        <td class="text-end fw-bold text-primary"><?= Yii::$app->formatter->asCurrency($model->importe_total) ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
