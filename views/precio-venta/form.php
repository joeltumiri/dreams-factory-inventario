<?php

/** @var yii\web\View $this */
/** @var app\models\PrecioVenta $model */
/** @var array $productos */
/** @var array<int,float> $preciosBase */
/** @var array $ciudades */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = $model->isNewRecord ? 'Nuevo precio de venta' : 'Editar precio de venta';

$productoOptions = [];
foreach ($productos as $pid => $label) {
    $productoOptions[$pid] = [
        'data-precio' => number_format((float)($preciosBase[$pid] ?? 0), 2, '.', ''),
    ];
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-md-8"><?= $form->field($model, 'producto_id')->dropDownList($productos, [
                'prompt' => 'Seleccione...',
                'options' => $productoOptions,
                'id' => 'pv-producto',
            ]) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'ciudad_id')->dropDownList($ciudades, ['prompt' => 'Seleccione...']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'precio')->textInput([
                'type' => 'number', 'step' => '0.01', 'min' => '0', 'id' => 'pv-precio',
            ])->hint('<span id="pv-precio-hint" class="text-muted small">Se autocompleta con el precio base del producto.</span>', ['encode' => false]) ?></div>
            <div class="col-md-8"><?= $form->field($model, 'obs')->textInput(['maxlength' => true, 'placeholder' => 'Opcional...']) ?></div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
(function(){
    var sel = document.getElementById('pv-producto');
    var inp = document.getElementById('pv-precio');
    var hint = document.getElementById('pv-precio-hint');
    if (!sel || !inp) return;
    function basePrice(){
        var opt = sel.options[sel.selectedIndex];
        return opt ? parseFloat(opt.getAttribute('data-precio') || '0') : 0;
    }
    function fill(force){
        var p = basePrice();
        if (force || !inp.value || parseFloat(inp.value) === 0) {
            inp.value = p > 0 ? p.toFixed(2) : '';
        }
        if (hint) {
            hint.textContent = p > 0
                ? 'Precio base del producto: ' + p.toFixed(2) + ' Bs (puedes editarlo).'
                : 'Este producto no tiene precio base configurado.';
        }
    }
    sel.addEventListener('change', function(){ fill(true); });
    fill(false);
})();
JS;
$this->registerJs($js);
