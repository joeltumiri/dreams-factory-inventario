<?php
/** @var \yii\web\View $this */
/** @var \app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Iniciar Sesión';
?>
<div class="card shadow-lg border-0" style="width:380px; border-radius:16px; overflow:hidden;">
    <div class="card-header text-white text-center py-4" style="background:#0d3b6e;">
        <i class="fa-solid fa-baby-carriage fa-2x mb-2"></i>
        <h5 class="mb-0 fw-bold">Dreams Factory</h5>
        <small class="opacity-75">Sistema de Gestión de Inventarios</small>
    </div>
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Usuario', 'class' => 'form-control'])->label('Usuario') ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Contraseña', 'class' => 'form-control'])->label('Contraseña') ?>
        <?= $form->field($model, 'rememberMe')->checkbox()->label('Recordarme') ?>
        <div class="d-grid mt-3">
            <?= Html::submitButton('<i class="fa-solid fa-right-to-bracket me-2"></i>Ingresar', ['class' => 'btn btn-primary btn-lg fw-bold']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="card-footer text-center text-muted small py-2">
        &copy; <?= date('Y') ?> Dreams Factory — Villa Dolores, El Alto
    </div>
</div>
