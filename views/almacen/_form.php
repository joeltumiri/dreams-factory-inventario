<?php
/** @var yii\web\View $this */
/** @var app\models\Almacen $model */
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
?>
<div class="card border-0 shadow-sm" style="max-width:560px">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

        <?= $form->field($model, 'nombre')->textInput(['maxlength' => 120, 'placeholder' => 'Ej: Almacén Principal']) ?>
        <?= $form->field($model, 'direccion')->textInput(['maxlength' => 255, 'placeholder' => 'Calle, Zona, Ciudad']) ?>
        <?= $form->field($model, 'contacto')->textInput(['maxlength' => 255, 'placeholder' => 'Teléfono o celular del encargado']) ?>

        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-xmark me-1"></i> Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
