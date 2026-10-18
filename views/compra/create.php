<?php

/** @var yii\web\View $this */
/** @var app\models\Compra $model */
/** @var app\models\DetalleCompra[] $detalles */
/** @var array $proveedores */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Nueva compra';
$buscarUrl = Url::to(['/compra/buscar-producto']);
?>
<style>
.ac-wrap { position: relative; }
.ac-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 0 0 6px 6px;
    z-index: 9999;
    max-height: 260px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
    display: none;
}
html.df-dark .ac-dropdown { background:#16243a; border-color:#2a4668; }
.ac-item {
    padding: 7px 12px;
    cursor: pointer;
    font-size: .88rem;
    border-bottom: 1px solid #f0f0f0;
}
html.df-dark .ac-item { border-bottom-color:#253d5e; color:#d8eaf8; }
.ac-item:hover, .ac-item.active { background: #e8f0fe; }
html.df-dark .ac-item:hover, html.df-dark .ac-item.active { background:#1a2d47; }
.ac-item .ac-code { font-weight:700; color:#0d6efd; margin-right:6px; }
.ac-item .ac-stock-zero { color:#aaa; font-size:.8rem; }
.ac-item .ac-stock-ok  { color:#28a745; font-size:.8rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0"><?= Html::encode($this->title) ?></h4>
    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php $form = ActiveForm::begin(['id' => 'compra-form']); ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Datos de la compra</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><?= $form->field($model, 'proveedor_id')->dropDownList($proveedores, ['prompt' => 'Seleccione...']) ?></div>
            <div class="col-md-2"><?= $form->field($model, 'fecha_compra')->textInput(['type' => 'date']) ?></div>
            <div class="col-md-12"><?= $form->field($model, 'nota')->textarea(['rows' => 2]) ?></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span>Detalle de productos</span>
        <button type="button" class="btn btn-sm btn-success" id="add-row">+ Agregar fila</button>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0" id="detalle-table">
            <thead class="table-light">
            <tr>
                <th style="width:45%">Producto</th>
                <th style="width:13%">Cantidad</th>
                <th style="width:18%">Precio unitario</th>
                <th style="width:14%">Importe</th>
                <th style="width:8%"></th>
            </tr>
            </thead>
            <tbody id="detalle-body">
            <tr class="detalle-row" data-index="0">
                <td>
                    <input type="hidden" name="DetalleCompra[0][producto_id]" class="producto-id-input">
                    <div class="ac-wrap">
                        <input type="text" class="form-control form-control-sm ac-input" placeholder="Buscar por nombre o código..." autocomplete="off">
                        <div class="ac-dropdown"></div>
                    </div>
                </td>
                <td><input type="number" name="DetalleCompra[0][cantidad]" class="form-control form-control-sm cantidad" min="1" step="1" value="1"></td>
                <td><input type="number" name="DetalleCompra[0][precio_unitario]" class="form-control form-control-sm precio-unitario" step="0.01" min="0" value="0"></td>
                <td><input type="number" name="DetalleCompra[0][importe]" class="form-control form-control-sm importe bg-light" step="0.01" min="0" value="0" readonly></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger del-row">Quitar</button></td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="table-light">
                <td colspan="5" class="text-end fw-bold">TOTAL</td>
                <td class="fw-bold text-primary"><span id="total-compra">0.00</span></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="d-flex gap-2">
    <?= Html::submitButton('Confirmar compra', ['class' => 'btn btn-primary btn-lg']) ?>
    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
</div>

<?php ActiveForm::end(); ?>

<script>
const BUSCAR_URL = <?= json_encode($buscarUrl) ?>;
let rowIndex = 1;

/* ---- recalc ---- */
function recalcRow(tr) {
    const cant  = parseFloat(tr.querySelector('.cantidad')?.value) || 0;
    const price = parseFloat(tr.querySelector('.precio-unitario')?.value) || 0;
    const imp   = tr.querySelector('.importe');
    if (imp) imp.value = (cant * price).toFixed(2);
}
function recalcTotal() {
    let total = 0;
    document.querySelectorAll('.detalle-row').forEach(tr => {
        recalcRow(tr);
        total += parseFloat(tr.querySelector('.importe')?.value) || 0;
    });
    document.getElementById('total-compra').textContent = total.toFixed(2);
}

/* ---- autocomplete ---- */
let acTimers = {};

function initAC(tr) {
    const idx      = tr.dataset.index;
    const input    = tr.querySelector('.ac-input');
    const hidden   = tr.querySelector('.producto-id-input');
    const dropdown = tr.querySelector('.ac-dropdown');
    const stockEl  = tr.querySelector('.stock-badge');
    const precioEl = tr.querySelector('.precio-unitario');
    const cantEl   = tr.querySelector('.cantidad');

    let results = [];
    let active  = -1;

    function renderDropdown() {
        if (!results.length) { dropdown.style.display = 'none'; return; }
        dropdown.innerHTML = results.map((p, i) => {
            return `<div class="ac-item${i === active ? ' active' : ''}" data-i="${i}">
                ${p.nombre}
            </div>`;
        }).join('');
        dropdown.style.display = 'block';

        dropdown.querySelectorAll('.ac-item').forEach(el => {
            el.addEventListener('mousedown', e => {
                e.preventDefault();
                selectItem(parseInt(el.dataset.i));
            });
        });
    }

    function selectItem(i) {
        const p = results[i];
        if (!p) return;
        input.value        = p.nombre;
        hidden.value       = p.id;
        precioEl.value     = p.precio_compra.toFixed(2);
        dropdown.style.display = 'none';
        recalcTotal();
        cantEl.focus();
    }

    input.addEventListener('input', function () {
        const q = this.value.trim();
        hidden.value = '';
        active = -1;
        clearTimeout(acTimers[idx]);
        if (!q) { dropdown.style.display = 'none'; return; }
        acTimers[idx] = setTimeout(() => {
            fetch(`${BUSCAR_URL}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    results = data;
                    active  = -1;
                    renderDropdown();
                });
        }, 200);
    });

    input.addEventListener('keydown', function (e) {
        if (!results.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            active = Math.min(active + 1, results.length - 1);
            renderDropdown();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            active = Math.max(active - 1, 0);
            renderDropdown();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (active >= 0) selectItem(active);
            else if (results.length === 1) selectItem(0);
        } else if (e.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    });

    input.addEventListener('blur', () => {
        setTimeout(() => { dropdown.style.display = 'none'; }, 150);
    });
}

/* ---- bind row events ---- */
function bindEvents(tr) {
    initAC(tr);
    tr.querySelectorAll('.cantidad, .precio-unitario').forEach(el =>
        el.addEventListener('input', recalcTotal)
    );
    tr.querySelector('.del-row')?.addEventListener('click', function () {
        if (document.querySelectorAll('.detalle-row').length > 1) {
            tr.remove(); recalcTotal();
        }
    });
}

/* ---- add row ---- */
document.getElementById('add-row').addEventListener('click', function () {
    const tbody = document.getElementById('detalle-body');
    const tr = document.createElement('tr');
    tr.className = 'detalle-row';
    tr.dataset.index = rowIndex;
    tr.innerHTML = `
        <td>
            <input type="hidden" name="DetalleCompra[${rowIndex}][producto_id]" class="producto-id-input">
            <div class="ac-wrap">
                <input type="text" class="form-control form-control-sm ac-input" placeholder="Buscar por nombre o código..." autocomplete="off">
                <div class="ac-dropdown"></div>
            </div>
        </td>
        <td><input type="number" name="DetalleCompra[${rowIndex}][cantidad]" class="form-control form-control-sm cantidad" min="1" step="1" value="1"></td>
        <td><input type="number" name="DetalleCompra[${rowIndex}][precio_unitario]" class="form-control form-control-sm precio-unitario" step="0.01" min="0" value="0"></td>
        <td><input type="number" name="DetalleCompra[${rowIndex}][importe]" class="form-control form-control-sm importe bg-light" step="0.01" min="0" value="0" readonly></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger del-row">Quitar</button></td>
    `;
    tbody.appendChild(tr);
    rowIndex++;
    bindEvents(tr);
    recalcTotal();
    tr.querySelector('.ac-input').focus();
});

document.querySelectorAll('.detalle-row').forEach(bindEvents);
recalcTotal();
</script>
