<?php
/**
 * @var yii\web\View $this
 * @var app\models\Proveedor $model
 * @var array $ciudades
 * @var array $rubros
 */
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$f = ActiveForm::begin([
    'options' => ['class' => 'card border-0 shadow-sm'],
    'fieldConfig' => [
        'options' => ['class' => 'mb-2'],
        'inputOptions' => ['class' => 'form-control form-control-sm'],
        'labelOptions' => ['class' => 'form-label small fw-bold mb-1'],
        'errorOptions' => ['class' => 'invalid-feedback d-block small'],
    ],
]);
?>
<div class="card-body p-3">
    <div class="row g-2">
        <div class="col-md-6">
            <?= $f->field($model, 'nombre')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Ej: Distribuidora Bolivia SRL']) ?>
        </div>
        <div class="col-md-6">
            <?= $f->field($model, 'razon_social')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-3">
            <?= $f->field($model, 'nit')->textInput(['maxlength' => true, 'placeholder' => '1234567890']) ?>
        </div>
        <div class="col-md-3">
            <?= $f->field($model, 'rubro')->dropDownList($rubros ?? [], [
                'prompt' => '— Seleccione categoría —',
                'class'  => 'form-select form-select-sm',
            ])->hint('Corresponde a una categoría de productos') ?>
        </div>
        <div class="col-md-3">
            <?= $f->field($model, 'telefono')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $f->field($model, 'celular')->textInput(['maxlength' => true, 'placeholder' => '+591 7XXXXXXX']) ?>
        </div>

        <div class="col-md-6">
            <?= $f->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'contacto@empresa.com']) ?>
        </div>
        <div class="col-md-6">
            <?= $f->field($model, 'contacto')->textInput(['maxlength' => true, 'placeholder' => 'Nombre de la persona de contacto']) ?>
        </div>

        <div class="col-md-12">
            <?= $f->field($model, 'direccion')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-6">
            <?= $f->field($model, 'ciudad_id')->dropDownList($ciudades, [
                'prompt' => '— Seleccione ciudad —',
                'class'  => 'form-select form-select-sm',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $f->field($model, 'estado')->dropDownList([
                1 => 'Activo',
                0 => 'Inactivo',
            ], ['class' => 'form-select form-select-sm']) ?>
        </div>

        <div class="col-md-12">
            <?= $f->field($model, 'nota')->textarea(['rows' => 2, 'maxlength' => true]) ?>
        </div>
    </div>
</div>
<div class="card-footer bg-light py-2 d-flex justify-content-end gap-2">
    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> Cancelar',
        ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar',
        ['class' => 'btn btn-primary btn-sm']) ?>
</div>
<?php ActiveForm::end(); ?>
