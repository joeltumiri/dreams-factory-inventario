<?php
/**
 * @var yii\web\View $this
 * @var app\models\Producto $model
 * @var array $categorias
 */
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$isNew = $model->isNewRecord;
?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

        <?php if (!$isNew): ?>
        <div class="row mb-2">
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">Número</label>
                <input type="text" class="form-control" value="<?= Html::encode((string)$model->numero) ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">Código</label>
                <input type="text" class="form-control" value="<?= Html::encode((string)$model->codigo) ?>" readonly>
                <small class="text-muted">Formato BG-P{n}</small>
            </div>
        </div>
        <?php else: ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'nombre')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ej: VASO CON BOMBILLA 9oz',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'categoria_id')->dropDownList($categorias, ['prompt' => '— Seleccione —']) ?>
            </div>
        </div>

        <div class="row">
            <?php if (!$isNew): ?>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">Precio compra</label>
                <input type="text" class="form-control" value="<?= number_format((float)$model->precio_compra, 2) ?>" readonly>
                <small class="text-muted">Se actualiza al registrar una compra</small>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'precio_venta')->input('number', ['min' => 0, 'step' => '0.01', 'placeholder' => '0.00'])->label('Precio venta') ?>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-muted small">Stock actual</label>
                <input type="text" class="form-control" value="<?= Html::encode((string)$model->stock) ?>" readonly>
                <small class="text-muted">Se actualiza con compras/ventas</small>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'stock_minimo')->input('number', ['min' => 0]) ?>
            </div>
            <?php else: ?>
            <div class="col-md-6">
                <?= $form->field($model, 'precio_venta')->input('number', ['min' => 0, 'step' => '0.01', 'placeholder' => '0.00'])->hint('El precio al que se venderá este producto.') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'precio_compra')->hiddenInput(['value' => 0])->label(false) ?>
            </div>
            <?php endif; ?>
        </div>

        <?= $form->field($model, 'descripcion')->textarea(['rows' => 3, 'placeholder' => 'Opcional']) ?>
        <?= $form->field($model, 'nota')->textInput(['maxlength' => true, 'placeholder' => 'Observaciones internas']) ?>

        <?php if (!$isNew): ?>
        <?= $form->field($model, 'activo')->dropDownList([1 => 'Activo', 0 => 'Inactivo']) ?>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-xmark me-1"></i> Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
