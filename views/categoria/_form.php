<?php

/** @var yii\web\View $this */
/** @var app\models\Categoria $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'nota')->textInput(['maxlength' => true]) ?>

        <div class="d-flex gap-2">
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
