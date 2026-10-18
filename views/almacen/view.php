<?php
/** @var yii\web\View $this */
/** @var app\models\Almacen $model */
use yii\bootstrap5\Html;

$this->title = $model->nombre;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= \Yii::$app->urlManager->createUrl(['/almacen/index']) ?>"><i class="fa-solid fa-warehouse me-1"></i>Almacenes</a></li>
            <li class="breadcrumb-item active"><?= Html::encode($model->nombre) ?></li>
        </ol>
    </nav>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="fa-solid fa-pen me-1"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
        <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Volver', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px">
    <div class="card-header bg-primary text-white py-2 px-3">
        <i class="fa-solid fa-warehouse me-2"></i><strong><?= Html::encode($model->nombre) ?></strong>
    </div>
    <div class="card-body p-0">
        <table class="table table-borderless mb-0">
            <tbody>
                <tr>
                    <th class="text-muted ps-3" style="width:160px">Nombre</th>
                    <td><strong><?= Html::encode($model->nombre) ?></strong></td>
                </tr>
                <tr class="table-light">
                    <th class="text-muted ps-3"><i class="fa-solid fa-location-dot me-1 text-danger"></i>Dirección</th>
                    <td><?= Html::encode($model->direccion ?: '—') ?></td>
                </tr>
                <tr>
                    <th class="text-muted ps-3"><i class="fa-solid fa-phone me-1 text-success"></i>Fono / Cel.</th>
                    <td><?= Html::encode($model->contacto ?: '—') ?></td>
                </tr>
                <tr class="table-light">
                    <th class="text-muted ps-3">Creado Por</th>
                    <td><?= $model->createdBy ? Html::encode($model->createdBy->nombreCompleto) : '—' ?></td>
                </tr>
                <tr>
                    <th class="text-muted ps-3">Fecha Creación</th>
                    <td><?= $model->created_at ? date('d-m-Y', $model->created_at) : '—' ?></td>
                </tr>
                <tr class="table-light">
                    <th class="text-muted ps-3">Actualizado Por</th>
                    <td><?= $model->updatedBy ? Html::encode($model->updatedBy->nombreCompleto) : '—' ?></td>
                </tr>
                <tr>
                    <th class="text-muted ps-3">Fecha Actualización</th>
                    <td><?= $model->updated_at ? date('d-m-Y', $model->updated_at) : '—' ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small text-end">Datos Extraídos de Sistema-Almacén</div>
</div>
