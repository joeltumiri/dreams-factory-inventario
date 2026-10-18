<?php

/** @var yii\web\View $this */
/** @var app\models\Oferta $model */
/** @var array $productos */
/** @var array $ciudades */
/** @var string $titulo */
/** @var string $preciosJson */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = $titulo;
$buscarUrl   = \yii\helpers\Url::to(['/precio-oferta/buscar-producto']);

// Precio compra del producto actual (modo edicion)
$precioCompraActual = 0.0;
if ($model->producto_id) {
    $prod = \app\models\Producto::findOne($model->producto_id);
    if ($prod) $precioCompraActual = (float)$prod->precio_compra;
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><i class="fa-solid fa-tags me-2 text-primary"></i><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Ver lista de ofertas', ['lista'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'oferta-form']); ?>

        <!-- Fila 1: Busqueda -->
        <div class="row g-3 mb-1">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Buscar por nombre <span class="text-danger">*</span></label>
                <input type="text" id="pof-search" class="form-control" placeholder="Escribe el nombre del producto..." autocomplete="off"
                    value="<?= $model->producto_id && isset($productos[$model->producto_id]) ? Html::encode($productos[$model->producto_id]) : '' ?>">
                <?= Html::activeHiddenInput($model, 'producto_id', ['id' => 'pof-producto-id']) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Buscar por codigo</label>
                <input type="text" id="pof-codigo" class="form-control" placeholder="Ej: BG-P005" autocomplete="off">
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ciudad_id')->dropDownList($ciudades, ['prompt' => 'Seleccione...']) ?>
            </div>
        </div>

        <hr class="my-3">

        <!-- Fila 2: Precios -->
        <div class="row g-3 align-items-start">
            <div class="col-md-3">
                <label class="form-label text-muted small">P. Compra (Bs)</label>
                <input type="text" id="pof-precio-compra" class="form-control bg-secondary bg-opacity-25" readonly
                    value="<?= $precioCompraActual > 0 ? number_format($precioCompraActual, 2) : '' ?>" placeholder="--">
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'precio_venta')->textInput([
                    'type'     => 'text',
                    'id'       => 'inp-precio-venta',
                    'readonly' => true,
                    'class'    => 'form-control bg-secondary bg-opacity-25',
                    'placeholder' => 'Se llena al seleccionar',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'precio_oferta', ['enableClientValidation' => false])->textInput([
                    'type'        => 'number',
                    'step'        => '0.01',
                    'min'         => '0',
                    'id'          => 'inp-precio-oferta',
                    'placeholder' => 'Ej: 9.50',
                ]) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Ganancia por unidad</label>
                <div id="pof-ganancia-box" class="p-2 rounded border text-center" style="min-height:38px">
                    <span id="pof-ganancia" class="fw-bold">--</span>
                </div>
                <div id="pof-ganancia-msg" class="small mt-1"></div>
            </div>
        </div>

        <!-- Indicadores -->
        <div class="row g-2 mb-1 mt-1">
            <div class="col-auto">
                <span class="text-muted small me-1">Descuento:</span>
                <span id="pof-descuento" class="fw-bold">--</span>
            </div>
            <div class="col-auto">
                <span class="text-muted small me-1">Estado:</span>
                <span id="pof-estado" class="badge">--</span>
            </div>
        </div>

        <hr class="my-3">

        <!-- Fila 3: Fechas -->
        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'fecha_inicio')->textInput(['type' => 'date', 'id' => 'inp-fecha-ini']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'fecha_final')->textInput(['type' => 'date', 'id' => 'inp-fecha-fin']) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'obs')->textarea(['rows' => 2]) ?>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> Guardar', ['class' => 'btn btn-primary', 'id' => 'btn-guardar-oferta']) ?>
            <?= Html::a('Cancelar', ['lista'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div id="pof-dropdown" style="display:none;position:fixed;z-index:9999;background:#1e2a3a;border:1px solid #3a4a5a;border-radius:4px;max-height:220px;overflow-y:auto;min-width:320px"></div>

<script>
(function () {
    var BUSCAR_URL    = <?= json_encode($buscarUrl) ?>;
    var preciosMap    = <?= $preciosJson ?>;
    var searchInp     = document.getElementById('pof-search');
    var codigoInp     = document.getElementById('pof-codigo');
    var hiddenId      = document.getElementById('pof-producto-id');
    var dropdown      = document.getElementById('pof-dropdown');
    var ventaInp      = document.getElementById('inp-precio-venta');
    var compraInp     = document.getElementById('pof-precio-compra');
    var ofertaInp     = document.getElementById('inp-precio-oferta');
    var gananciaEl    = document.getElementById('pof-ganancia');
    var gananciaMsgEl = document.getElementById('pof-ganancia-msg');
    var gananciaBox   = document.getElementById('pof-ganancia-box');
    var descuentoEl   = document.getElementById('pof-descuento');
    var estadoEl      = document.getElementById('pof-estado');
    var fechaIniEl    = document.getElementById('inp-fecha-ini');
    var fechaFinEl    = document.getElementById('inp-fecha-fin');
    var btnGuardar    = document.getElementById('btn-guardar-oferta');
    var timer;

    function positionDropdown(anchor) {
        var r = anchor.getBoundingClientRect();
        dropdown.style.left  = r.left + 'px';
        dropdown.style.top   = (r.bottom + 2) + 'px';
        dropdown.style.width = r.width + 'px';
    }

    function selectItem(id, nombre, precioVenta, precioCompra) {
        searchInp.value = nombre;
        hiddenId.value  = id;
        ventaInp.value  = parseFloat(precioVenta).toFixed(2);
        compraInp.value = parseFloat(precioCompra).toFixed(2);
        codigoInp.value = '';
        dropdown.style.display = 'none';
        calcular();
    }

    function renderDropdown(items, anchor) {
        if (!items.length) { dropdown.style.display = 'none'; return; }
        dropdown.innerHTML = items.map(function(p) {
            return '<div class="pof-item" style="padding:.45rem .75rem;cursor:pointer;font-size:.84rem;border-bottom:1px solid #3a4a5a;color:#e0e0e0"'
                + ' data-id="' + p.id + '"'
                + ' data-nombre="' + p.nombre.replace(/"/g, '&quot;') + '"'
                + ' data-venta="' + p.precio + '"'
                + ' data-compra="' + p.precio_compra + '">'
                + p.nombre + '</div>';
        }).join('');
        positionDropdown(anchor);
        dropdown.style.display = 'block';
        dropdown.querySelectorAll('.pof-item').forEach(function(el) {
            el.addEventListener('mousedown', function(e) {
                e.preventDefault();
                selectItem(el.dataset.id, el.dataset.nombre, el.dataset.venta, el.dataset.compra);
            });
        });
    }

    function doSearch(q, anchor) {
        if (!q) { dropdown.style.display = 'none'; return; }
        clearTimeout(timer);
        timer = setTimeout(function() {
            fetch(BUSCAR_URL + '?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) { renderDropdown(data, anchor); });
        }, 200);
    }

    searchInp.addEventListener('input', function() {
        hiddenId.value = ''; ventaInp.value = ''; compraInp.value = '';
        doSearch(this.value.trim(), searchInp); calcular();
    });
    codigoInp.addEventListener('input', function() {
        hiddenId.value = ''; ventaInp.value = ''; compraInp.value = '';
        doSearch(this.value.trim(), codigoInp); calcular();
    });
    document.addEventListener('click', function(e) {
        if (e.target !== searchInp && e.target !== codigoInp && !dropdown.contains(e.target))
            dropdown.style.display = 'none';
    });

    function calcular() {
        var venta  = parseFloat(ventaInp.value)  || 0;
        var compra = parseFloat(compraInp.value) || 0;
        var oferta = parseFloat(ofertaInp.value) || 0;

        // Descuento %
        if (venta > 0 && oferta > 0) {
            var pct = ((1 - oferta / venta) * 100).toFixed(1);
            descuentoEl.textContent = pct + '%';
            descuentoEl.style.color = oferta > venta ? '#f87171' : (parseFloat(pct) > 0 ? '#4ade80' : '#94a3b8');
        } else {
            descuentoEl.textContent = '--'; descuentoEl.style.color = '';
        }

        // Bloquear si oferta > venta
        if (btnGuardar) btnGuardar.disabled = (oferta > 0 && venta > 0 && oferta > venta);

        // Ganancia = oferta - compra
        if (oferta > 0 && compra > 0) {
            var gan = oferta - compra;
            gananciaEl.textContent = 'Bs ' + gan.toFixed(2);
            if (gan > 0) {
                gananciaEl.style.color = '#4ade80'; gananciaBox.style.borderColor = '#4ade80';
                gananciaMsgEl.textContent = 'La oferta genera ganancia.'; gananciaMsgEl.style.color = '#4ade80';
            } else if (gan === 0) {
                gananciaEl.style.color = '#fbbf24'; gananciaBox.style.borderColor = '#fbbf24';
                gananciaMsgEl.textContent = 'La oferta se encuentra al costo.'; gananciaMsgEl.style.color = '#fbbf24';
            } else {
                gananciaEl.style.color = '#f87171'; gananciaBox.style.borderColor = '#f87171';
                gananciaMsgEl.textContent = 'Atencion: la oferta genera perdida.'; gananciaMsgEl.style.color = '#f87171';
            }
        } else {
            gananciaEl.textContent = '--'; gananciaEl.style.color = '';
            gananciaBox.style.borderColor = ''; gananciaMsgEl.textContent = '';
        }
    }

    function calcularEstado() {
        var hoy = new Date(); hoy.setHours(0,0,0,0);
        var ini = fechaIniEl && fechaIniEl.value ? new Date(fechaIniEl.value) : null;
        var fin = fechaFinEl && fechaFinEl.value ? new Date(fechaFinEl.value) : null;
        if (!ini || !fin) { estadoEl.textContent = '--'; estadoEl.className = 'badge'; return; }
        if (ini > hoy)      { estadoEl.textContent = 'Programada'; estadoEl.className = 'badge bg-warning text-dark'; }
        else if (fin < hoy) { estadoEl.textContent = 'Finalizada';  estadoEl.className = 'badge bg-secondary'; }
        else                { estadoEl.textContent = 'Activa';      estadoEl.className = 'badge bg-success'; }
    }

    ofertaInp.addEventListener('input', calcular);
    if (fechaIniEl) fechaIniEl.addEventListener('change', calcularEstado);
    if (fechaFinEl) fechaFinEl.addEventListener('change', calcularEstado);

    // Prellenar en modo edicion
    if (hiddenId.value) {
        var pid = parseInt(hiddenId.value);
        if (pid && preciosMap[pid]) {
            if (!ventaInp.value)  ventaInp.value  = preciosMap[pid].v.toFixed(2);
            if (!compraInp.value) compraInp.value = preciosMap[pid].c.toFixed(2);
        }
    }
    calcular();
    calcularEstado();
})();
</script>
