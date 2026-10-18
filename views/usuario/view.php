<?php

/** @var yii\web\View $this */
/** @var app\models\User $model */

use yii\bootstrap5\Html;

$this->title = 'Ver usuario';
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-badge me-2 text-primary"></i><?= Html::encode($model->username) ?></span>
        <span class="text-muted small"><?= Html::encode($this->title) ?></span>
    </div>

    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nombre de usuario</dt>
            <dd class="col-sm-9"><?= Html::encode($model->username) ?></dd>

            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9"><?= Html::encode($model->email) ?></dd>

            <dt class="col-sm-3">Tipo de usuario</dt>
            <dd class="col-sm-9">
                <span class="badge bg-success">
                    <?= Html::encode($model->tipo_usuario) ?>
                </span>
            </dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <span class="badge bg-<?= Html::encode($model->statusBadgeClass()) ?>">
                    <?= Html::encode($model->statusLabel()) ?>
                </span>
            </dd>

            <dt class="col-sm-3">Fecha de creación</dt>
            <dd class="col-sm-9">
                <?= Html::encode($model->createdAtFormatted()) ?>
            </dd>
        </dl>
    </div>

    <div class="card-footer bg-white d-flex gap-2">
        <?= Html::a('Editar', ['/usuario/update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('Volver al listado', ['/usuario/index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>