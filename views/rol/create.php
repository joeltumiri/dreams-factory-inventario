<?php
/** @var \yii\web\View $this */
/** @var \app\models\Role $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Nuevo Rol';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 fw-bold" style="background:var(--df-azul);color:#fff;border-radius:10px 10px 0 0;">
                <i class="fa-solid fa-user-shield me-2"></i>Crear Nuevo Rol
            </div>
            <div class="card-body p-4">
                <?php $form = ActiveForm::begin(['id' => 'rol-form']); ?>

                <?= $form->field($model, 'role_name')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ej: Supervisor',
                    'class' => 'form-control',
                ])->label('Nombre del Rol <span class="text-danger">*</span>', ['encode' => false]) ?>

                <?= $form->field($model, 'role_value')->textInput([
                    'type' => 'number',
                    'min' => 4,
                    'placeholder' => 'Número único (≥ 4)',
                    'class' => 'form-control',
                ])->label('Valor Numérico <span class="text-danger">*</span>', ['encode' => false])
                  ->hint('Debe ser único. Los valores 1, 2 y 3 están reservados para el sistema.') ?>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i>Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i>Guardar', ['class' => 'btn btn-primary fw-semibold']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
