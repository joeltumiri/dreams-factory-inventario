<?php
/** @var yii\web\View $this */
/** @var app\models\Visita $model */
/** @var array $clientes */

use app\models\Visita;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Editar Visita #' . $model->id;
$vendedorNombre = $model->user?->username ?? Yii::$app->user->identity?->username ?? '�';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
</div>

<div class="card border-0 shadow-sm" style="max-width:780px">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'form-visita']); ?>
        <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

        <div class="row g-3">
            <div class="col-md-6">
                <?= $form->field($model, 'cliente_id')->dropDownList($clientes, [
                    'prompt' => 'Seleccione cliente...',
                    'id'     => 'vis-cliente',
                ])->label('Cliente') ?>

                <?= $form->field($model, 'fecha_visita')->textInput(['type' => 'date'])
                    ->label('Fecha de visita') ?>

                <?= $form->field($model, 'hora_visita')->textInput(['type' => 'time'])
                    ->label('Hora de visita') ?>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Vendedor</label>
                    <input type="text" class="form-control" value="<?= Html::encode($vendedorNombre) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Estado de visita</label>
                    <div id="btn-estado-group" class="d-flex gap-2 mt-1">
                        <?php
                        $estados = [
                            Visita::ESTADO_CERRADO    => ['label' => 'CERRADO',    'icon' => 'fa-solid fa-circle-xmark',  'color' => 'danger'],
                            Visita::ESTADO_SIN_PEDIDO => ['label' => 'SIN PEDIDO', 'icon' => 'fa-solid fa-clock',         'color' => 'warning'],
                            Visita::ESTADO_CON_PEDIDO => ['label' => 'VISITADO',   'icon' => 'fa-solid fa-circle-check',  'color' => 'success'],
                        ];
                        foreach ($estados as $val => $opt):
                            $isActive = (int)$model->estado === $val;
                        ?>
                        <button type="button"
                            class="btn btn-<?= $isActive ? '' : 'outline-' ?><?= $opt['color'] ?> btn-estado flex-fill py-2"
                            data-val="<?= $val ?>"
                            style="font-size:.82rem;font-weight:600;letter-spacing:.03em">
                            <i class="<?= $opt['icon'] ?> me-1"></i><?= $opt['label'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?= $form->field($model, 'estado')->hiddenInput(['id' => 'vis-estado-val'])->label(false) ?>
                </div>
            </div>

            <div class="col-12">
                <?= $form->field($model, 'obs')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Motivo del cierre / Por qu� no hizo pedido / Productos que le interesaron...',
                ])->label('Observaciones') ?>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <?= Html::submitButton('Guardar cambios', ['class' => 'btn btn-success']) ?>
            <?= Html::a('? Cancelar', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$this->registerJs(<<<JS
(function(){
    var estadoInput = document.getElementById('vis-estado-val');

    document.querySelectorAll('.btn-estado').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.btn-estado').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            estadoInput.value = btn.dataset.val;
        });
    });
})();
JS);
?>
