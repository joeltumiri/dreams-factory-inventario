<?php

/** @var yii\web\View $this */
/** @var app\models\Cliente $model */
/** @var array $tipos */
/** @var array $ciudades */
/** @var array $departamentos */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
?>
<style>
/* sin estilos extra necesarios */
</style>
<?php $form = ActiveForm::begin(['id' => 'cliente-form']); ?>

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-store me-2 text-primary"></i>Datos del cliente</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><?= $form->field($model, 'tipo_id')->dropDownList($tipos) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'razon_social')->textInput(['maxlength' => true, 'placeholder' => 'Razón social de la farmacia/tienda']) ?></div>
                    <div class="col-md-3"><?= $form->field($model, 'nit')->textInput(['maxlength' => true]) ?></div>

                    <div class="col-md-6">
                        <?= $form->field($model, 'nombre_completo')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Ingrese nombre completo',
                        ])->label('Nombre Completo <span class="text-danger">*</span>', ['encode' => false]) ?>
                    </div>

                    <div class="col-md-6"><?= $form->field($model, 'nombre_factura')->textInput(['maxlength' => true]) ?></div>

                    <div class="col-md-4"><?= $form->field($model, 'email')->textInput(['type' => 'email', 'maxlength' => true]) ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'contacto')->textInput(['maxlength' => true, 'placeholder' => 'Celular / WhatsApp']) ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'zona')->textInput(['maxlength' => true, 'placeholder' => 'Zona de El Alto']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Ubicación</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><?= $form->field($model, 'departamento_id')->dropDownList($departamentos) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'ciudad_id')->dropDownList($ciudades) ?></div>
                    <div class="col-12"><?= $form->field($model, 'direccion')->textInput(['maxlength' => true]) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>Georreferenciación</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><?= $form->field($model, 'latitud')->textInput(['placeholder' => '-16.5000000']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'longitud')->textInput(['placeholder' => '-68.1500000']) ?></div>
                    <div class="col-12">
                        <?= $form->field($model, 'iframe')->textarea(['rows' => 3, 'placeholder' => 'Pegar enlace iframe de Google Maps']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
<script>
document.querySelectorAll('.tc-option').forEach(function (a) {
    a.addEventListener('click', function (e) {
        e.preventDefault();
        const val = this.dataset.value;
        document.getElementById('inp-tipo-contacto').value = val;
        const icon = val === 'Empleado'
            ? '<i class="fa-solid fa-user me-1"></i>Empleado'
            : '<i class="fa-solid fa-user-tie me-1"></i>Propietario';
        document.getElementById('tc-label').innerHTML = icon;
    });
});
</script>
