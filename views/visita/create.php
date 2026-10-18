<?php
/** @var yii\web\View $this */
/** @var app\models\Visita $model */
/** @var array $clientes */

use app\models\Visita;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = '➕ Registrar Nueva Visita';
$vendedorNombre = Yii::$app->user->identity?->username ?? '—';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
</div>

<div class="card border-0 shadow-sm" style="max-width:780px">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'form-visita']); ?>
        <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

        <div class="row g-3">
            <!-- Columna izquierda -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Cliente <span class="text-danger">*</span></label>
                    <div style="position:relative">
                        <input type="text" id="vis-cliente-text" class="form-control"
                               placeholder="Escribir nombre o NIT..." autocomplete="off">
                        <ul id="vis-cliente-list" style="display:none;position:absolute;z-index:9999;width:100%;max-height:220px;overflow-y:auto;background:var(--df-card-bg,#1a2535);border:1px solid var(--df-card-border,#2e3a4a);border-radius:0 0 6px 6px;padding:0;margin:0;list-style:none"></ul>
                    </div>
                    <?= Html::activeHiddenInput($model, 'cliente_id', ['id' => 'vis-cliente']) ?>
                    <?php if ($model->hasErrors('cliente_id')): ?>
                        <div class="text-danger small"><?= $model->getFirstError('cliente_id') ?></div>
                    <?php endif; ?>
                </div>
                <div id="vis-cliente-info" class="text-muted small mt-1" style="min-height:18px"></div>

                <?= $form->field($model, 'fecha_visita')->textInput([
                    'type' => 'date',
                    'value' => date('Y-m-d'),
                ])->label('Fecha de visita') ?>

                <?= $form->field($model, 'hora_visita')->textInput([
                    'type' => 'time',
                    'value' => date('H:i'),
                ])->label('Hora de visita') ?>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Vendedor</label>
                    <input type="text" class="form-control" value="<?= Html::encode($vendedorNombre) ?>" readonly>
                    <small class="text-muted">Asignado automáticamente</small>
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
                    <?php echo $form->field($model, 'estado')->hiddenInput(['id' => 'vis-estado-val'])->label(false); ?>
                </div>
            </div>

            <!-- Fila completa: observación -->
            <div class="col-12">
                <?= $form->field($model, 'obs')->textarea([
                    'rows' => 3,
                    'placeholder' => 'Motivo del cierre / Por qué no hizo pedido / Productos que le interesaron...',
                ])->label('Observaciones') ?>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <?= Html::submitButton('Guardar Visita', ['class' => 'btn btn-success']) ?>
            <?= Html::a('← Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$ventasUrl  = Url::to(['/visita/ventas-cliente']);
$clienteUrl = Url::to(['/venta/buscar-cliente']);
$this->registerJs("
(function(){
    var CLIENTE_URL = " . json_encode($clienteUrl) . ";
    var VENTAS_URL  = " . json_encode($ventasUrl) . ";

    // ── Autocomplete cliente ────────────────────────────────────────────
    var inp   = document.getElementById('vis-cliente-text');
    var list  = document.getElementById('vis-cliente-list');
    var hidId = document.getElementById('vis-cliente');
    var timer;

    inp.addEventListener('input', function(){
        clearTimeout(timer);
        hidId.value = '';
        var q = inp.value.trim();
        if (q.length < 1) { list.style.display='none'; return; }
        timer = setTimeout(function(){
            fetch(CLIENTE_URL + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    list.innerHTML = '';
                    if (!data.length) { list.style.display='none'; return; }
                    data.forEach(c => {
                        var li = document.createElement('li');
                        li.style.cssText = 'padding:8px 12px;cursor:pointer;border-bottom:1px solid var(--df-card-border,#2e3a4a);font-size:.9rem';
                        li.innerHTML = '<strong>' + c.nombre + '</strong>' + (c.contacto ? ' <small class=\"text-muted\">- ' + c.contacto + '</small>' : '');
                        li.addEventListener('mousedown', function(e){ e.preventDefault(); });
                        li.addEventListener('click', function(){
                            inp.value   = c.nombre;
                            hidId.value = c.id;
                            list.style.display = 'none';
                            if (parseInt(estadoInput.value) === 3) cargarVentas();
                        });
                        list.appendChild(li);
                    });
                    list.style.display = '';
                });
        }, 250);
    });

    inp.addEventListener('blur', function(){ setTimeout(()=>{ list.style.display='none'; }, 200); });
    inp.addEventListener('focus', function(){ if (list.children.length) list.style.display=''; });

    // ── Botones de estado ────────────────────────────────────────────────
    var estadoInput = document.getElementById('vis-estado-val');

    document.querySelectorAll('.btn-estado').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.btn-estado').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            estadoInput.value = btn.dataset.val;
        });
    });

    function toggleVentaBox(val){
        if (parseInt(val) === 3) {
            ventaBox.style.display = '';
            cargarVentas();
        } else {
            ventaBox.style.display = 'none';
            ventaSel.innerHTML = '<option value=\"\">Seleccione una venta...</option>';
        }
    }

    function cargarVentas(){
        var clienteId = document.getElementById('vis-cliente').value;
        if (!clienteId) return;
        fetch(VENTAS_URL + '?clienteId=' + clienteId)
            .then(r => r.json())
            .then(data => {
                ventaSel.innerHTML = '<option value=\"\">Seleccione una venta...</option>';
                data.forEach(v => {
                    var opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.text;
                    ventaSel.appendChild(opt);
                });
            });
    }

    toggleVentaBox(estadoInput.value || '2');
    document.querySelectorAll('[data-bs-toggle=\"tooltip\"]').forEach(el => new bootstrap.Tooltip(el));
})();
");
?>
