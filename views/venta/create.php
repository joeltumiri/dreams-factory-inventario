<?php
/**
 * @var yii\web\View         $this
 * @var app\models\Venta     $model
 * @var array                $clientes      [id => nombre]
 * @var array                $tiposPago     [id => nombre]
 * @var array                $estadosPago   [id => nombre]
 * @var array                $productosJson array of product objects
 * @var string               $usuarioNombre nombre del usuario logueado
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Nueva Venta';
?>
<style>
/* ── POS Layout ──────────────────────────────────────────────────────────── */
.pos-header {
    background: var(--df-azul);
    color: #fff;
    padding: .75rem 1.4rem;
    border-radius: 10px 10px 0 0;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.pos-wrapper {
    background: var(--df-card-bg, #f4f6f9);
    border-radius: 0 0 10px 10px;
    box-shadow: 0 2px 14px rgba(0,0,0,.09);
    overflow: hidden;
}
.pos-fields {
    background: var(--df-gris, #eceff4);
    padding: .9rem 1.2rem;
    border-bottom: 1px solid var(--df-card-border, #d0d8e4);
}
.pos-main {
    display: flex;
    min-height: 340px;
}
/* ── Cart panel ─────────────────────────────────────────────────── */
.cart-panel {
    flex: 1 1 58%;
    padding: 1rem 1.1rem;
    border-right: 1px solid var(--df-card-border, #d0d8e4);
    overflow-x: auto;
    background: var(--df-card-bg, #f4f6f9);
}
.cart-table { font-size: .83rem; border-collapse: separate; border-spacing: 0; width: 100%; }
.cart-table thead th {
    background: var(--df-table-head, #dde3ee);
    border-top: 1px solid var(--df-table-border, #b6c2d1);
    border-bottom: 2px solid var(--df-table-border, #aab8c9);
    border-right: 1px solid var(--df-table-border, #b6c2d1);
    white-space: nowrap;
    padding: .5rem .65rem;
    font-weight: 700;
    color: var(--df-text, #212529);
}
.cart-table thead th:first-child { border-left: 1px solid var(--df-table-border, #b6c2d1); }
.cart-table tbody td {
    border-right: 1px solid var(--df-table-border, #d0d8e4);
    border-bottom: 1px solid var(--df-table-border, #d0d8e4);
    padding: .4rem .55rem;
    vertical-align: middle;
    white-space: nowrap;
    color: var(--df-text, #212529);
    background: var(--df-card-bg, #f4f6f9);
}
.cart-table tbody td:first-child { border-left: 1px solid var(--df-table-border, #d0d8e4); }
.cart-table tbody tr:nth-child(even) td { background: var(--df-table-even, #edf2fa); }
.cart-empty-row td {
    text-align: center;
    color: var(--df-text-muted, #8899aa);
    padding: 2.2rem 1rem;
    font-size: .88rem;
    border: 1px solid var(--df-card-border, #d0d8e4);
}
/* ── Product list panel ─────────────────────────────────────────── */
.prod-panel {
    flex: 0 0 42%;
    display: flex;
    flex-direction: column;
    background: var(--df-card-bg, #f4f6f9);
}
.prod-panel-header {
    background: var(--df-azul2);
    color: #fff;
    padding: .6rem 1rem;
    font-weight: 700;
    font-size: .88rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.prod-search-box {
    padding: .55rem .8rem;
    border-bottom: 1px solid var(--df-card-border, #d0d8e4);
    background: var(--df-gris, #eceff4);
}
.prod-search-box input {
    font-size: .83rem;
    border-radius: 20px;
}
.prod-list {
    flex: 1;
    overflow-y: auto;
    max-height: 340px;
    background: var(--df-card-bg, #f4f6f9);
}
.prod-item {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .5rem .85rem;
    border-bottom: 1px solid var(--df-card-border, #dde5ef);
    cursor: pointer;
    transition: background .12s;
}
.prod-item:hover { background: var(--df-table-even, #e4ecf8); }
.prod-item:last-child { border-bottom: none; }
.prod-icon {
    width: 32px; height: 32px;
    background: var(--df-table-head, #dde5f5);
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: .9rem;
    color: var(--df-azul2);
}
.prod-info { flex: 1; min-width: 0; }
.prod-name  { font-size: .82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--df-text, #212529); }
.prod-codigo{ font-size: .72rem; color: var(--df-text-muted, #6c757d); }
.prod-badge-stock {
    font-size: .76rem; font-weight: 700;
    min-width: 42px; text-align: right;
}
.sk-ok   { color: #198754; }
.sk-warn { color: #fd7e14; }
.sk-low  { color: #dc3545; }
.prod-cat { font-size: .7rem; color: var(--df-text-muted, #8899aa); min-width: 90px; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
/* ── Bottom cost panel ──────────────────────────────────────────── */
.pos-bottom {
    display: flex;
    align-items: stretch;
    border-top: 2px solid var(--df-card-border, #d0d8e4);
    background: var(--df-gris, #eceff4);
}
.costo-panel {
    flex: 0 0 38%;
    padding: 1rem 1.3rem;
    border-right: 1px solid var(--df-card-border, #d0d8e4);
}
.costo-panel .costo-title {
    font-weight: 700;
    color: var(--df-azul);
    font-size: .88rem;
    margin-bottom: .6rem;
}
.costo-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: .35rem;
    font-size: .85rem;
}
.costo-row label { color: var(--df-text-muted, #555); }
.costo-row .val { font-weight: 700; color: var(--df-text, #212529); }
.costo-total { border-top: 2px solid var(--df-azul); margin-top: .5rem; padding-top: .5rem; }
.costo-total label { font-weight: 700; color: var(--df-azul); }
.costo-total .val { font-size: 1.15rem; color: var(--df-azul); }
.pago-panel {
    flex: 0 0 30%;
    padding: 1rem 1.2rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: .65rem;
    border-right: 1px solid var(--df-card-border, #d0d8e4);
}
.pago-row { display: flex; align-items: center; gap: .5rem; font-size: .88rem; }
.pago-row label { min-width: 65px; font-weight: 600; color: var(--df-text, #212529); }
.btn-panel {
    flex: 1;
    padding: 1rem 1.2rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: .6rem;
}
.btn-registrar {
    background: #198754; border: none;
    color: #fff; font-weight: 700; font-size: .95rem;
    padding: .7rem 1.2rem; border-radius: 8px;
    cursor: pointer; transition: background .14s;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
}
.btn-registrar:hover { background: #157347; }
.btn-cancelar {
    background: #dc3545; border: none;
    color: #fff; font-weight: 700; font-size: .95rem;
    padding: .7rem 1.2rem; border-radius: 8px;
    cursor: pointer; text-decoration: none; text-align: center;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    transition: background .14s;
}
.btn-cancelar:hover { background: #bb2d3b; color: #fff; }
.field-label { font-size: .78rem; font-weight: 600; color: var(--df-text-muted, #444); margin-bottom: 2px; display: block; }
.pos-fields .form-control, .pos-fields .form-select { font-size: .83rem; }
</style>

<!-- ── POS Header ──────────────────────────────────────────────────────────── -->
<div class="pos-header">
    <i class="fa-solid fa-cart-shopping"></i>
    <strong>VENTA</strong>
    <span class="opacity-75 ms-1">| Fecha: <?= date('d/m/Y') ?></span>
</div>

<?php $form = ActiveForm::begin(['id' => 'pos-form', 'options' => ['autocomplete' => 'off']]); ?>

<div class="pos-wrapper">

    <!-- ── Form fields ────────────────────────────────────────────────────── -->
    <div class="pos-fields">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="field-label"><i class="fa-solid fa-store me-1" style="color:var(--df-azul2)"></i> Cliente *</label>
                <div class="autocomplete-wrap" style="position:relative">
                    <input type="text" id="cliente-text" class="form-control form-control-sm"
                           placeholder="Escribir nombre..." autocomplete="off">
                    <?= Html::activeHiddenInput($model, 'cliente_id', ['id' => 'venta-cliente_id']) ?>
                    <ul id="cliente-list" class="autocomplete-list" style="display:none;position:absolute;z-index:9999;width:100%;max-height:220px;overflow-y:auto;background:var(--df-card-bg,#fff);border:1px solid var(--df-card-border,#ccc);border-radius:0 0 6px 6px;padding:0;margin:0;list-style:none"></ul>
                </div>
            </div>
            <div class="col-md-2">
                <label class="field-label"><i class="fa-solid fa-file-invoice me-1" style="color:#6610f2"></i> Documento *</label>
                <?= Html::activeDropDownList($model, 'documento', [
                    'Factura' => 'FACTURA',
                    'Nota de Remisión' => 'NOTA DE REMISIÓN',
                ], [
                    'class' => 'form-select form-select-sm',
                    'prompt' => 'Seleccione...',
                    'id' => 'sel-documento',
                ]) ?>
            </div>
            <div class="col-md-2" id="wrap-nro-doc">
                <label class="field-label"><i class="fa-solid fa-hashtag me-1" style="color:#6610f2"></i> Nro. Documento *</label>
                <?= Html::activeInput('text', $model, 'nro_documento', [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'Ej: 001-0001',
                    'id' => 'inp-nro-doc',
                ]) ?>
                <small id="nro-doc-auto" class="text-muted d-none">Se generará automáticamente</small>
            </div>
            <div class="col-md-2">
                <label class="field-label"><i class="fa-solid fa-credit-card me-1" style="color:#1a5fa8"></i> Tipo Pago</label>
                <input type="text" class="form-control form-control-sm" value="Contado" readonly tabindex="-1" style="background:#f0f4fa;color:#333;">
                <?= Html::activeHiddenInput($model, 'tipo_pago_id') ?>
            </div>
            <?= Html::activeHiddenInput($model, 'estado_pago_id') ?>
            <div class="col-md-2">
                <label class="field-label"><i class="fa-regular fa-calendar me-1" style="color:#fd7e14"></i> Fecha entrega</label>
                <?= Html::activeInput('date', $model, 'fecha_entrega', ['class' => 'form-control form-control-sm', 'id' => 'inp-fecha-entrega', 'required' => true]) ?>
            </div>
            <div class="col-md-2">
                <label class="field-label"><i class="fa-solid fa-note-sticky me-1" style="color:#6c757d"></i> Observación</label>
                <?= Html::activeInput('text', $model, 'nota', ['class' => 'form-control form-control-sm', 'placeholder' => 'Nota opcional...']) ?>
            </div>
            <div class="col-md-2">
                <label class="field-label"><i class="fa-solid fa-user me-1" style="color:#0d6efd"></i> Registrado por</label>
                <input type="text" class="form-control form-control-sm" value="<?= Html::encode($usuarioNombre) ?>" readonly tabindex="-1" style="background:#f0f4fa;color:#333;">
            </div>
        </div>
    </div>

    <!-- ── Main POS area ──────────────────────────────────────────────────── -->
    <div class="pos-main">

        <!-- Left: Cart -->
        <div class="cart-panel">
            <table class="cart-table" id="cart-table">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center" style="width:80px">Cantidad</th>
                    <th class="text-end">Precio</th>
                    <th class="text-end">Subtotal</th>
                    <th style="width:36px"></th>
                </tr>
                </thead>
                <tbody id="cart-body">
                <tr id="cart-empty-row">
                        <td colspan="7" class="cart-empty-row">
                            <i class="fa-solid fa-basket-shopping me-2 opacity-40"></i>
                            Selecciona productos desde la lista de la derecha
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Right: Product list -->
        <div class="prod-panel">
            <div class="prod-panel-header">
                <span><i class="fa-solid fa-box-open me-1"></i> LISTA DE PRODUCTOS</span>
                <span id="prod-count" class="badge bg-white text-primary fw-bold">0</span>
            </div>
            <div class="prod-search-box">
                <input type="text" class="form-control form-control-sm"
                       id="prod-search"
                       placeholder="&#128269; Buscar por código o nombre...">
            </div>
            <div class="prod-list" id="prod-list">
                <!-- JS renders rows -->
            </div>
        </div>

    </div><!-- /pos-main -->

    <!-- ── Bottom: Cost + Payment ─────────────────────────────────────────── -->
    <div class="pos-bottom">

        <!-- Detalle de Costo -->
        <div class="costo-panel">
            <div class="costo-title"><i class="fa-solid fa-receipt me-1"></i> Detalle de Costo</div>
            <div class="costo-row">
                <label>Subtotal</label>
                <span class="val" id="disp-subtotal">0.00 Bs.</span>
            </div>
            <div class="costo-row">
                <label>Descuento</label>
                <div class="d-flex align-items-center gap-1">
                    <input type="number" id="inp-descuento" min="0" step="0.01" value="0"
                           class="form-control form-control-sm text-center"
                           style="width:78px">
                    <select id="sel-descuento-modo" class="form-select form-select-sm" style="width:55px;font-size:.78rem">
                        <option value="pct">%</option>
                        <option value="bs">Bs</option>
                    </select>
                    <span class="val ms-1 text-danger" id="disp-descuento">0.00 Bs.</span>
                </div>
            </div>
            <div class="costo-row costo-total">
                <label>Monto Final</label>
                <span class="val" id="disp-total">0.00 Bs.</span>
            </div>
        </div>

        <!-- Pago / Cambio -->
        <div class="pago-panel">
            <div class="pago-row">
                <label> Método</label>
                <?= Html::activeDropDownList($model, 'metodo_pago', [
                    'Efectivo'     => 'Efectivo',
                    'Transferencia'=> 'Transferencia',
                ], ['class' => 'form-select form-select-sm', 'id' => 'sel-metodo-pago', 'prompt' => 'Seleccione...']) ?>
            </div>
            <div class="pago-row">
                <label> Pago</label>
                <input type="number" id="inp-pago" min="0" step="0.01" value="0"
                       class="form-control form-control-sm" style="width:110px">
                <span class="fw-bold text-muted">Bs.</span>
            </div>
            <div class="pago-row">
                <label> Cambio</label>
                <span id="disp-cambio" class="fw-bold text-success fs-6">0.00 Bs.</span>
            </div>
        </div>

        <!-- Buttons -->
        <div class="btn-panel">
            <button type="submit" class="btn-registrar" id="btn-registrar">
                <i class="fa-solid fa-check-circle"></i> REGISTRAR VENTA
            </button>
            <?= Html::a('<i class="fa-solid fa-xmark"></i> CANCELAR', ['index'], ['class' => 'btn-cancelar']) ?>
        </div>

    </div><!-- /pos-bottom -->

</div><!-- /pos-wrapper -->

<!-- Hidden inputs built by JS -->
<div id="hidden-inputs"></div>

<?php ActiveForm::end(); ?>

<!-- Modal: lotes ──────────────────────────────────────────────────────────── -->
<div class="modal fade" id="lotesModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white py-2">
        <h6 class="modal-title m-0"><i class="fa-solid fa-layer-group me-2"></i>Lotes disponibles</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div class="px-3 pt-3 pb-2">
          <strong id="lotes-prod-name"></strong>
        </div>
        <table class="table table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Fecha ingreso</th>
              <th>Almacén</th>
              <th class="text-end">Cant. inicial</th>
              <th class="text-end">Disponible</th>
              <th class="text-end">P. compra</th>
            </tr>
          </thead>
          <tbody id="lotes-tbody">
            <tr><td colspan="6" class="text-center py-3 text-muted">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
/* ═══════════════════════════════════════════════════════════════════════════
   Dreams Factory POS — JavaScript
═══════════════════════════════════════════════════════════════════════════ */

/* ── Autocomplete clientes ────────────────────────────────────────────── */
(function () {
    const inp   = document.getElementById('cliente-text');
    const hidId = document.getElementById('venta-cliente_id');
    const list  = document.getElementById('cliente-list');
    if (!inp) return;

    let timer, activeIdx = -1;

    function renderList(items) {
        list.innerHTML = '';
        activeIdx = -1;
        if (!items.length) { list.style.display = 'none'; return; }
        items.forEach((c, i) => {
            const li = document.createElement('li');
            li.style.cssText = 'padding:.45rem .75rem;cursor:pointer;font-size:.83rem;border-bottom:1px solid var(--df-card-border,#e0e0e0)';
            li.innerHTML = '<strong>' + c.nombre + '</strong>'
                + (c.contacto ? '<span style="color:var(--df-text-muted,#888);font-size:.78rem"> — ' + c.contacto + '</span>' : '');
            li.addEventListener('mousedown', () => selectCliente(c));
            li.addEventListener('mouseenter', () => { activeIdx = i; highlightItem(); });
            list.appendChild(li);
        });
        list.style.display = 'block';
    }

    function highlightItem() {
        list.querySelectorAll('li').forEach((li, i) => {
            li.style.background = i === activeIdx ? 'var(--df-azul,#0d6efd)' : '';
            li.style.color      = i === activeIdx ? '#fff' : '';
        });
    }

    function selectCliente(c) {
        inp.value   = c.nombre;
        hidId.value = c.id;
        list.style.display = 'none';
    }

    inp.addEventListener('input', function () {
        hidId.value = '';
        clearTimeout(timer);
        const q = inp.value.trim();
        if (q.length < 1) { list.style.display = 'none'; return; }
        timer = setTimeout(() => {
            fetch('<?= \yii\helpers\Url::to(['/venta/buscar-cliente']) ?>?q=' + encodeURIComponent(q))
                .then(r => r.json()).then(renderList);
        }, 200);
    });

    inp.addEventListener('keydown', function (e) {
        const lis = list.querySelectorAll('li');
        if (e.key === 'ArrowDown')  { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, lis.length - 1); highlightItem(); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); highlightItem(); }
        else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); lis[activeIdx].dispatchEvent(new Event('mousedown')); }
        else if (e.key === 'Escape') { list.style.display = 'none'; }
    });

    document.addEventListener('click', e => { if (!inp.contains(e.target)) list.style.display = 'none'; });
})();

const PRODS = <?= json_encode($productosJson, JSON_UNESCAPED_UNICODE) ?>;

// cart: { [productoId]: { id, codigo, nombre, stock, stock_minimo, precio, categoria, cantidad, descPct } }
const cart = {};

/* ── Helpers ─────────────────────────────────────────────────────────────── */
function esc(s) {
    return String(s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function stockClass(p) {
    if (p.stock <= 0)                        return 'sk-low';
    if (p.stock <= p.stock_minimo)           return 'sk-low';
    if (p.stock <= p.stock_minimo * 1.2)     return 'sk-warn';
    return 'sk-ok';
}

/* ── Render product list ─────────────────────────────────────────────────── */
function renderProds(q) {
    const lc = (q || '').toLowerCase();
    let html = '', count = 0;

    for (const p of PRODS) {
        if (lc && !p.codigo.toLowerCase().includes(lc) && !p.nombre.toLowerCase().includes(lc)) continue;
        const sc = stockClass(p);
        html += `
        <div class="prod-item" onclick="addToCart(${p.id})" title="Agregar: ${esc(p.nombre)}">
            <div class="prod-icon"><i class="fa-solid fa-cube"></i></div>
            <div class="prod-info">
                <div class="prod-name">${esc(p.nombre)}</div>
                <div class="prod-codigo">${esc(p.codigo)}</div>
            </div>
            <div class="prod-badge-stock ${sc}">${p.stock}</div>
            <div class="prod-cat">${esc(p.categoria)}</div>
        </div>`;
        count++;
    }

    if (!html) html = '<div class="text-center text-muted py-3 small">Sin resultados</div>';
    document.getElementById('prod-list').innerHTML = html;
    document.getElementById('prod-count').textContent = count;
}

/* ── Cart management ─────────────────────────────────────────────────────── */
function addToCart(id) {
    const p = PRODS.find(x => x.id === id);
    if (!p) return;
    if (p.stock <= 0) { alert('Sin stock: ' + p.nombre); return; }

    if (cart[id]) {
        const next = cart[id].cantidad + 1;
        if (next > p.stock) { alert('Stock máximo disponible: ' + p.stock); return; }
        cart[id].cantidad = next;
        refreshRow(id);
    } else {
        cart[id] = { ...p, cantidad: 1 };
        appendRow(id);
    }
    recalc();
    updateSaveBtn();
}

function appendRow(id) {
    document.getElementById('cart-empty-row')?.remove();
    const tr = document.createElement('tr');
    tr.id = 'cr-' + id;
    tr.innerHTML = rowHtml(id);
    document.getElementById('cart-body').appendChild(tr);
    bindRow(id);
}

function refreshRow(id) {
    const tr = document.getElementById('cr-' + id);
    if (!tr) return;
    tr.innerHTML = rowHtml(id);
    bindRow(id);
}

function rowHtml(id) {
    const p   = cart[id];
    const sub = rowSub(p);
    return `
    <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size:.75rem">${esc(p.codigo)}</span></td>
    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis">${esc(p.nombre)}</td>
    <td class="text-center">${p.stock}</td>
    <td class="text-center">
        <input type="number" class="form-control form-control-sm text-center cart-qty"
               style="width:68px" min="1" max="${p.stock}" value="${p.cantidad}">
    </td>
    <td class="text-end">${p.precio.toFixed(2)}</td>
    <td class="text-end fw-bold cart-sub">${sub.toFixed(2)}</td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-info py-0 btn-lotes me-1" title="Ver lotes">
            <i class="fa-solid fa-layer-group" style="font-size:.75rem"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger py-0 btn-del" title="Quitar">
            <i class="fa-solid fa-trash-can" style="font-size:.75rem"></i>
        </button>
    </td>`;
}

function rowSub(p) {
    return p.cantidad * p.precio;
}

function bindRow(id) {
    const tr = document.getElementById('cr-' + id);
    if (!tr) return;
    const p = cart[id];

    tr.querySelector('.cart-qty').addEventListener('input', function () {
        let v = parseInt(this.value) || 1;
        if (v < 1) v = 1;
        if (v > p.stock) { v = p.stock; this.value = v; }
        cart[id].cantidad = v;
        tr.querySelector('.cart-sub').textContent = rowSub(cart[id]).toFixed(2);
        recalc();
    });

    tr.querySelector('.btn-del').addEventListener('click', function () {
        delete cart[id];
        tr.remove();
        if (Object.keys(cart).length === 0) {
            document.getElementById('cart-body').innerHTML =
                '<tr id="cart-empty-row"><td colspan="7" class="cart-empty-row">' +
                '<i class="fa-solid fa-basket-shopping me-2 opacity-40"></i>' +
                'Selecciona productos desde la lista de la derecha</td></tr>';
        }
        recalc();
        updateSaveBtn();
    });

    tr.querySelector('.btn-lotes').addEventListener('click', function () {
        showLotes(id);
    });
}

/* ── Totals ──────────────────────────────────────────────────────────────── */
function recalc() {
    let subtotal = 0;
    for (const p of Object.values(cart)) subtotal += rowSub(p);

    const modo  = document.getElementById('sel-descuento-modo').value;
    const dval  = parseFloat(document.getElementById('inp-descuento').value) || 0;
    let desc    = (modo === 'pct') ? (subtotal * dval / 100) : dval;
    if (desc > subtotal) desc = subtotal;
    if (desc < 0) desc = 0;
    const total   = subtotal - desc;
    const metodo  = document.getElementById('sel-metodo-pago').value;
    const inpPago = document.getElementById('inp-pago');
    if (metodo !== 'Transferencia') {
        inpPago.value = total.toFixed(2);
    }
    const pago    = parseFloat(inpPago.value) || 0;
    const cambio  = Math.max(0, pago - total);

    document.getElementById('disp-subtotal').textContent = subtotal.toFixed(2) + ' Bs.';
    document.getElementById('disp-descuento').textContent = desc.toFixed(2) + ' Bs.';
    document.getElementById('disp-total').textContent = total.toFixed(2) + ' Bs.';
    document.getElementById('disp-cambio').textContent = cambio.toFixed(2) + ' Bs.';

    buildHiddenInputs(subtotal, desc, total);
}

function buildHiddenInputs(subtotal, descuento, total) {
    const c = document.getElementById('hidden-inputs');
    let html = '';
    html += `<input type="hidden" name="Venta[importe_total]" value="${subtotal.toFixed(2)}">`;
    html += `<input type="hidden" name="Venta[descuento]"    value="${descuento.toFixed(2)}">`;
    html += `<input type="hidden" name="Venta[total_efectivo]" value="${total.toFixed(2)}">`;
    let i = 0;
    for (const [id, p] of Object.entries(cart)) {
        const imp = rowSub(p).toFixed(2);
        html += `<input type="hidden" name="DetalleVenta[${i}][producto_id]"     value="${id}">`;
        html += `<input type="hidden" name="DetalleVenta[${i}][cantidad]"         value="${p.cantidad}">`;
        html += `<input type="hidden" name="DetalleVenta[${i}][precio_unitario]"  value="${p.precio.toFixed(2)}">`;
        html += `<input type="hidden" name="DetalleVenta[${i}][importe]"          value="${imp}">`;
        i++;
    }
    c.innerHTML = html;
}

/* ── Events ──────────────────────────────────────────────────────────────── */
document.getElementById('prod-search').addEventListener('input', function () {
    renderProds(this.value);
});

document.getElementById('inp-descuento').addEventListener('input', recalc);
document.getElementById('sel-descuento-modo').addEventListener('change', recalc);

document.getElementById('inp-pago').addEventListener('input', function () {
    const total = parseFloat(document.getElementById('disp-total').textContent) || 0;
    const pago  = parseFloat(this.value) || 0;
    document.getElementById('disp-cambio').textContent = Math.max(0, pago - total).toFixed(2) + ' Bs.';
});

document.getElementById('sel-metodo-pago').addEventListener('change', function () {
    const esTransf = this.value === 'Transferencia';
    const rowPago   = document.getElementById('inp-pago').closest('.pago-row');
    const rowCambio = document.getElementById('disp-cambio').closest('.pago-row');
    if (esTransf) {
        rowPago.classList.add('d-none');
        rowCambio.classList.add('d-none');
    } else {
        rowPago.classList.remove('d-none');
        rowCambio.classList.remove('d-none');
        const inpPago = document.getElementById('inp-pago');
        delete inpPago.dataset.manual;
        const total = parseFloat(document.getElementById('disp-total').textContent) || 0;
        inpPago.value = total.toFixed(2);
        document.getElementById('disp-cambio').textContent = '0.00 Bs.';
    }
});

/* ── Documento: toggle nro_documento ────────────────────────────────────── */
document.getElementById('sel-documento').addEventListener('change', function () {
    const esNR  = this.value === 'Nota de Remisión';
    const inp   = document.getElementById('inp-nro-doc');
    const auto  = document.getElementById('nro-doc-auto');
    if (esNR) {
        inp.value = '';
        inp.disabled = true;
        inp.classList.add('d-none');
        auto.classList.remove('d-none');
    } else {
        inp.disabled = false;
        inp.classList.remove('d-none');
        auto.classList.add('d-none');
    }
    updateSaveBtn();
});

/* ── Fecha entrega: mínimo = primer día del mes actual ───────────────────── */
(function () {
    const inp = document.getElementById('inp-fecha-entrega');
    if (inp) {
        const now  = new Date();
        const min  = now.getFullYear() + '-' +
                     String(now.getMonth() + 1).padStart(2, '0') + '-01';
        inp.setAttribute('min', min);
    }
})();

/* ── Deshabilitar botón guardar si faltan datos ──────────────────────────── */
function updateSaveBtn() {
    const btn        = document.getElementById('btn-registrar');
    const cliente    = document.getElementById('venta-cliente_id').value;
    const documento  = document.getElementById('sel-documento').value;
    const nroDoc     = document.getElementById('inp-nro-doc').value.trim();
    const fechaEnt   = document.getElementById('inp-fecha-entrega').value;
    const esNR       = documento === 'Nota de Remisión';
    const tieneCart  = Object.keys(cart).length > 0;

    const ok = cliente && documento && (esNR || nroDoc !== '') && tieneCart && fechaEnt !== '';

    btn.disabled = !ok;
    btn.style.opacity = ok ? '1' : '0.5';
    btn.style.cursor  = ok ? 'pointer' : 'not-allowed';
}

/* Conectar updateSaveBtn a cambios de campos clave */
['venta-cliente_id', 'sel-documento', 'inp-nro-doc', 'inp-fecha-entrega']
    .forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', updateSaveBtn);
        if (el) el.addEventListener('input',  updateSaveBtn);
    });

document.getElementById('pos-form').addEventListener('submit', function (e) {
    if (Object.keys(cart).length === 0) {
        e.preventDefault();
        alert('Debes agregar al menos un producto para registrar la venta.');
    }
});

/* ── Init ─────────────────────────────────────────────────────────────────── */
renderProds('');
buildHiddenInputs(0, 0, 0);
updateSaveBtn();

/* ── Lotes modal ──────────────────────────────────────────────────────────── */
const URL_LOTES = <?= json_encode(\yii\helpers\Url::to(['venta/lotes'])) ?>;
function showLotes(productoId) {
    const p = cart[productoId] || PRODS.find(x => x.id === productoId);
    document.getElementById('lotes-prod-name').textContent = p ? (p.codigo + ' — ' + p.nombre) : '';
    document.getElementById('lotes-tbody').innerHTML =
        '<tr><td colspan="6" class="text-center py-3 text-muted">Cargando...</td></tr>';
    const modal = new bootstrap.Modal(document.getElementById('lotesModal'));
    modal.show();

    fetch(URL_LOTES + '?producto_id=' + productoId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(rows => {
            if (!rows.length) {
                document.getElementById('lotes-tbody').innerHTML =
                    '<tr><td colspan="6" class="text-center py-3 text-muted">Sin lotes disponibles.</td></tr>';
                return;
            }
            let html = '';
            rows.forEach((l, i) => {
                html += `<tr>
                    <td>${i + 1}</td>
                    <td>${esc(l.fecha_ingreso || '')}</td>
                    <td>${esc(l.almacen || '—')}</td>
                    <td class="text-end">${l.cantidad_inicial}</td>
                    <td class="text-end fw-bold">${l.cantidad_disponible}</td>
                    <td class="text-end">${parseFloat(l.precio_compra_unitario).toFixed(2)}</td>
                </tr>`;
            });
            document.getElementById('lotes-tbody').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('lotes-tbody').innerHTML =
                '<tr><td colspan="6" class="text-center py-3 text-danger">Error al cargar lotes.</td></tr>';
        });
}
</script>
