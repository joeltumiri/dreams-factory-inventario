<?php

/** @var yii\web\View $this */
/** @var app\models\User $model */

use app\models\User;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$tiposUsuario = [
    User::TIPO_USUARIO_VENTA => 'venta',
    User::TIPO_USUARIO_ALMACEN => 'almacen',
    User::TIPO_USUARIO_ENTREGA => 'entrega',
    User::TIPO_USUARIO_SUPERVISOR => 'supervisor',
];

$estados = [
    User::STATUS_ACTIVE => 'Activo',
    User::STATUS_INACTIVE => 'Inactivo',
];
?>

<?php $form = ActiveForm::begin([
    'id' => 'usuario-form',
    'enableClientValidation' => true,
    'enableAjaxValidation' => false,
]); ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="fa-solid fa-id-card me-2 text-primary"></i>Datos del usuario
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'username')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Nombre de usuario',
                ]) ?>
            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'password')->passwordInput([
                    'maxlength' => true,
                    'placeholder' => 'Mínimo 8 caracteres',
                ]) ?>
            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'email')->input('email', [
                    'maxlength' => true,
                    'placeholder' => 'correo@ejemplo.com',
                ]) ?>
            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'tipo_usuario')->dropDownList($tiposUsuario, [
                    'prompt' => 'Seleccione un tipo de usuario',
                ]) ?>
            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'status')->dropDownList($estados, [
                ]) ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Cancelar', ['/usuario/index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>