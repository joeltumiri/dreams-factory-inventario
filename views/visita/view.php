<?php
/** @var yii\web\View $this */
/** @var app\models\Visita $model */

use app\models\Visita;
use yii\bootstrap5\Html;

$this->title = 'Detalle de Visita #' . $model->id;
$estadoColor = Visita::estadoColor($model->estado);
$estadoLabel = Visita::estadoLabel($model->estado);
$estadoEmoji = Visita::estadoEmoji($model->estado);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-map-location-dot me-2"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
</div>

<div class="card border-0 shadow-sm" style="max-width:700px">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:#1a2d47">
        <strong><?= Html::encode($model->cliente?->getNombreDisplay() ?? '�') ?></strong>
        <span class="badge bg-<?= $estadoColor ?> fs-6"><?= $estadoEmoji ?> <?= $estadoLabel ?></span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Fecha de visita</small>
                <strong><?= date('d/m/Y', strtotime($model->fecha_visita)) ?></strong>
                <?php if ($model->hora_visita): ?>
                    <span class="text-muted ms-2"><?= Html::encode(substr($model->hora_visita, 0, 5)) ?></span>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Vendedor</small>
                <strong><?= Html::encode($model->user?->username ?? '�') ?></strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Cliente</small>
                <strong><?= Html::encode($model->cliente?->getNombreDisplay() ?? '�') ?></strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Estado</small>
                <span class="badge bg-<?= $estadoColor ?>"><?= $estadoEmoji ?> <?= $estadoLabel ?></span>
            </div>
            <?php if ($model->obs): ?>
            <div class="col-12">
                <small class="text-muted d-block">Observaci�n</small>
                <p class="mb-0"><?= nl2br(Html::encode($model->obs)) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($model->venta_id && $model->venta): ?>
            <div class="col-12">
                <div class="card border-success border-opacity-25 p-3">
                    <h6 class="text-success mb-2">Venta vinculada</h6>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <small class="text-muted d-block">C�digo</small>
                            <strong>VTA-<?= str_pad((string)$model->venta_id, 3, '0', STR_PAD_LEFT) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Fecha pedido</small>
                            <strong><?= Html::encode($model->venta->fecha_pedido ?? '�') ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Importe total</small>
                            <strong class="text-success">Bs. <?= number_format((float)($model->venta->importe_total ?? 0), 2) ?></strong>
                        </div>
                    </div>
                    <div class="mt-2">
                        <?= Html::a('Ver venta completa', ['/venta/view', 'id' => $model->venta_id], ['class' => 'btn btn-sm btn-outline-success', 'target' => '_blank']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
        <?= Html::a('? Volver al listado', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>
