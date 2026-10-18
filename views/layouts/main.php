<?php
/** @var \yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);

$user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
$controllerId = Yii::$app->controller->id;

$isCatalogo      = in_array($controllerId, ['categoria', 'producto'], true);
$isAbastecimiento= in_array($controllerId, ['proveedor', 'compra'], true);
$isInventario    = in_array($controllerId, ['estado-producto', 'stock-producto', 'lote'], true);
$isPrecios       = in_array($controllerId, ['precio-venta', 'precio-oferta'], true);
$isVentas        = in_array($controllerId, ['venta'], true);
$isAlmacenMod    = in_array($controllerId, ['almacen'], true);
$actionId        = Yii::$app->controller->action->id ?? '';

$isAlmacenGroup  = in_array($controllerId, ['almacen', 'proveedor', 'compra'], true) || ($isVentas && in_array($actionId, ['por-enviar','enviados','entregados','detalle-pedido','listado'], true));
$isVentasGroup   = in_array($controllerId, ['cliente', 'visita'], true) || ($isVentas && in_array($actionId, ['create','index','update','view','por-enviar','enviados','entregados','listado'], true));
$isEntregaGroup  = $isVentas && in_array($actionId, ['por-enviar','enviados','entregados','detalle-pedido','listado'], true);
$isReportesGroup = $controllerId==='reporte';
$isBalanceGroup  = $controllerId==='balance';
$isRolGroup      = $controllerId==='rol';

// Badge: pedidos CONFIRMADOS pendientes de envío
$badgePorEnviar = 0;
if ($user && !Yii::$app->user->isGuest) {
    $badgePorEnviar = (int)\app\models\Venta::find()
        ->where(['estado_venta_id' => \app\models\Venta::ESTADO_CONFIRMADA])
        ->count();
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — Dreams Factory</title>
    <?php $this->head() ?>
    <script>
        if(localStorage.getItem('df_dark_mode')==='1'){document.documentElement.classList.add('df-dark');}
        if(localStorage.getItem('df_sidebar_collapsed')==='1'){document.documentElement.classList.add('df-sb-collapsed');}
    </script>
    <style>
        :root { --df-azul:#0d3b6e; --df-azul2:#1a5fa8; --df-gris:#f4f6f9; --df-card-bg:#ffffff; --df-card-border:#dee2e6; --df-text:#212529; --df-text-muted:#6c757d; --df-topbar-bg:#ffffff; --df-topbar-shadow:0 1px 4px rgba(0,0,0,.08); --df-table-head:#e9eef5; --df-table-border:#b6c2d1; --df-table-even:#f8fbff; --df-code-bg:#f1f3f5; --df-input-bg:#fff; --df-input-border:#ced4da; --df-input-text:#212529; }
        html.df-dark { --df-gris:#0f1a2b; --df-card-bg:#16243a; --df-card-border:#233452; --df-text:#d8eaf8; --df-text-muted:#7a9fc2; --df-topbar-bg:#0c1624; --df-topbar-shadow:0 1px 8px rgba(0,0,0,.55); --df-table-head:#1a2d47; --df-table-border:#253d5e; --df-table-even:#19304d; --df-code-bg:#0f1e30; --df-input-bg:#152238; --df-input-border:#2a4668; --df-input-text:#c4dcf4; }
        body { background:var(--df-gris); font-family:'Segoe UI',sans-serif; color:var(--df-text); transition:background .25s,color .25s; }
        /* Dark mode global overrides */
        html.df-dark .card, html.df-dark .modal-content { background:var(--df-card-bg) !important; border-color:var(--df-card-border) !important; color:var(--df-text) !important; }
        html.df-dark .form-control, html.df-dark .form-select { background:var(--df-input-bg) !important; border-color:var(--df-input-border) !important; color:var(--df-input-text) !important; }
        html.df-dark .form-control::placeholder { color:#6a7f9a !important; }
        html.df-dark .table, html.df-dark .grid-view table { color:var(--df-text) !important; }
        html.df-dark .content-wrapper .grid-view thead th, html.df-dark .content-wrapper .table.df-separated-table thead th { background:var(--df-table-head) !important; border-top-color:var(--df-table-border) !important; border-bottom-color:var(--df-table-border) !important; border-right-color:var(--df-table-border) !important; color:var(--df-text) !important; }
        html.df-dark .content-wrapper .grid-view tbody td, html.df-dark .content-wrapper .table.df-separated-table tbody td { border-right-color:var(--df-table-border) !important; border-bottom-color:var(--df-table-border) !important; color:var(--df-text) !important; }
        html.df-dark .content-wrapper .grid-view tbody tr:nth-child(even) td, html.df-dark .content-wrapper .table.df-separated-table tbody tr:nth-child(even) td { background:var(--df-table-even) !important; }
        html.df-dark .content-wrapper .grid-view table,
        html.df-dark .content-wrapper .table.df-separated-table { background:var(--df-card-bg) !important; }
        html.df-dark .content-wrapper .grid-view tbody tr:nth-child(odd) td,
        html.df-dark .content-wrapper .table.df-separated-table tbody tr:nth-child(odd) td { background:#1c3050 !important; }
        html.df-dark .content-wrapper .grid-view tbody tr:nth-child(even) td,
        html.df-dark .content-wrapper .table.df-separated-table tbody tr:nth-child(even) td { background:var(--df-table-even) !important; }
        html.df-dark .content-wrapper .grid-view tbody tr:hover td,
        html.df-dark .content-wrapper .table.df-separated-table tbody tr:hover td { background:#224070 !important; }
        html.df-dark .btn-outline-secondary { border-color:#2a4668; color:#7aa8cc; } html.df-dark .btn-outline-secondary:hover { background:#1a2d47; color:#c4dcf4; }
        html.df-dark .btn-outline-danger { border-color:#6a2535; color:#f08090; } html.df-dark .btn-outline-danger:hover { background:#4a1825; }
        html.df-dark .alert-success { background:#0e2a1c; border-color:#1a4a30; color:#6dccaa; }
        html.df-dark .alert-danger  { background:#2e1018; border-color:#561820; color:#f09098; }
        html.df-dark .alert-warning { background:#2a2008; border-color:#5a4010; color:#e0c060; }
        html.df-dark .alert-info    { background:#0e2035; border-color:#174a68; color:#60b8d8; }
        html.df-dark code { background:var(--df-code-bg); color:#60c8e8; padding:2px 6px; border-radius:4px; border:1px solid #253d5e; }
        html.df-dark select option { background:var(--df-input-bg); color:var(--df-input-text); }
        html.df-dark .text-muted { color:var(--df-text-muted) !important; }
        html.df-dark .badge.bg-primary { background:#1a5fa8 !important; }
        html.df-dark .bg-light { background:var(--df-card-bg) !important; }
        html.df-dark .bg-white { background:var(--df-card-bg) !important; color:var(--df-text) !important; }
        html.df-dark .card-header, html.df-dark .card-footer { background:var(--df-table-head) !important; color:var(--df-text) !important; border-color:var(--df-card-border) !important; }
        html.df-dark .card-header.bg-primary { background:#0d3b6e !important; color:#fff !important; }
        html.df-dark .card-header.bg-success { background:#0e3d28 !important; color:#cfead9 !important; }
        html.df-dark .card-header.bg-info    { background:#0e3a4d !important; color:#cfeaf2 !important; }
        html.df-dark .card-header.bg-warning { background:#3a2e0c !important; color:#f0d68a !important; }
        html.df-dark .card-header.bg-danger  { background:#3d1018 !important; color:#f0bcc4 !important; }
        html.df-dark .badge.bg-light, html.df-dark .badge.bg-warning, html.df-dark .badge.bg-info { color:#13243a !important; }
        html.df-dark .badge.bg-light { background:#cfdbe8 !important; border-color:#aab8c9 !important; }
        html.df-dark input.form-control[readonly], html.df-dark input.bg-light { background:#1a2d47 !important; color:var(--df-input-text) !important; border-color:var(--df-input-border) !important; }
        html.df-dark .table-light, html.df-dark .table-light>td, html.df-dark .table-light>th { background:var(--df-table-head) !important; color:var(--df-text) !important; border-color:var(--df-table-border) !important; }
        html.df-dark .table thead th { background:var(--df-table-head) !important; color:var(--df-text) !important; border-color:var(--df-table-border) !important; }
        html.df-dark .table tbody td, html.df-dark .table tbody th { background:var(--df-card-bg) !important; color:var(--df-text) !important; border-color:var(--df-table-border) !important; }
        html.df-dark .table tfoot td, html.df-dark .table tfoot th { background:var(--df-table-head) !important; color:var(--df-text) !important; border-color:var(--df-table-border) !important; }
        html.df-dark .topbar { background:var(--df-topbar-bg) !important; box-shadow:var(--df-topbar-shadow) !important; }
        html.df-dark .page-title { color:#90c4f0 !important; }
        /* Settings dropdown */
        .df-settings-dropdown .dropdown-menu { min-width:220px; padding:.5rem; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,.15); }
        html.df-dark .df-settings-dropdown .dropdown-menu { background:#16243a; border-color:#233452; color:#c4dcf4; }
        html.df-dark .df-settings-dropdown .dropdown-item { color:#c4dcf4; } html.df-dark .df-settings-dropdown .dropdown-item:hover { background:#1a2d47; }
        html.df-dark .df-settings-dropdown .dropdown-divider { border-color:#233452; }
        .df-theme-toggle { cursor:pointer; background:none; border:none; width:38px; height:20px; border-radius:10px; position:relative; transition:background .2s; flex-shrink:0; }
        .df-theme-toggle::after { content:''; position:absolute; top:3px; left:3px; width:14px; height:14px; border-radius:50%; background:#fff; transition:transform .2s; }
        .df-theme-toggle[data-on="true"] { background:#1a5fa8; }
        .df-theme-toggle[data-on="false"] { background:#adb5bd; }
        .df-theme-toggle[data-on="true"]::after { transform:translateX(18px); }
        /* ── Sidebar toggle ─────────────────────────────────────────── */
        #sidebar { width:255px; min-height:100vh; max-height:100vh; background:var(--df-azul); position:fixed; top:0; left:0; z-index:1000; overflow-y:auto; overflow-x:hidden; overscroll-behavior:contain; scrollbar-width:thin; scrollbar-color:rgba(255,255,255,.45) rgba(255,255,255,.12); transition:transform .28s cubic-bezier(.4,0,.2,1); }
        html.df-sb-collapsed #sidebar { transform:translateX(-255px); }
        html.df-sb-collapsed #main-content { margin-left:0; }
        #df-sidebar-toggle { background:none; border:none; color:var(--df-azul); font-size:1.18rem; padding:4px 6px; border-radius:6px; cursor:pointer; line-height:1; transition:background .15s; }
        #df-sidebar-toggle:hover { background:rgba(0,0,0,.06); }
        html.df-dark #df-sidebar-toggle { color:#90c4f0; }
        html.df-dark #df-sidebar-toggle:hover { background:rgba(255,255,255,.08); }
        /* Overlay para móvil */
        #df-sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; }
        @media (max-width:768px) {
            #sidebar { transform:translateX(-255px); }
            html.df-sb-collapsed #sidebar { transform:translateX(-255px); }
            html.df-sb-open #sidebar { transform:translateX(0); }
            html.df-sb-open #df-sidebar-overlay { display:block; }
            #main-content { margin-left:0 !important; }
        }
        #sidebar::-webkit-scrollbar { width:10px; }
        #sidebar::-webkit-scrollbar-track { background:rgba(255,255,255,.12); border-radius:10px; }
        #sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,.45); border-radius:10px; border:2px solid rgba(0,0,0,0); background-clip:padding-box; }
        #sidebar::-webkit-scrollbar-thumb:hover { background:rgba(255,255,255,.65); border:2px solid rgba(0,0,0,0); background-clip:padding-box; }
        #sidebar .sidebar-brand { padding:1.2rem 1rem; color:#fff; font-size:1.1rem; font-weight:700; border-bottom:1px solid rgba(255,255,255,.15); }
        #sidebar .nav-link { color:rgba(255,255,255,.8); padding:.5rem 1.1rem; border-radius:6px; margin:2px 8px; font-size:.86rem; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background:var(--df-azul2); color:#fff; }
        #sidebar .nav-section { font-size:.68rem; text-transform:uppercase; color:rgba(255,255,255,.4); padding:.8rem 1.2rem .2rem; letter-spacing:1px; }
        #sidebar .nav-link i { margin-right:.45rem; width:17px; text-align:center; }
        #main-content { margin-left:255px; min-height:100vh; transition:margin-left .28s cubic-bezier(.4,0,.2,1); }
        .topbar { background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:.65rem 1.5rem; display:flex; align-items:center; justify-content:space-between; }
        .topbar .page-title { font-weight:600; color:var(--df-azul); font-size:1.05rem; }
        .content-wrapper { padding:1.4rem; }
        .kpi-card { border:none; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.07); }
        .kpi-value { font-size:1.9rem; font-weight:700; }

        #sidebar .nav-sub { margin-left: .65rem; border-left: 1px solid rgba(255,255,255,.15); }
        #sidebar .nav-sub .nav-link { padding:.45rem 1.1rem; font-size:.82rem; opacity:.95; }
        #sidebar .nav-sub .nav-link i { margin-right:.4rem; width:16px; text-align:center; }

        /* Header de grupo desplegable */
        #sidebar .nav-group-toggle { cursor:pointer; user-select:none; transition: background-color .2s ease; }
        #sidebar .nav-group-toggle .chevron { transition: transform .25s ease; font-size:.75rem; opacity:.7; }
        #sidebar .nav-group-toggle[aria-expanded="true"] .chevron { transform: rotate(180deg); }
        #sidebar .nav-group-toggle[aria-expanded="true"] { background: rgba(255,255,255,.08); color:#fff; }
        #sidebar .collapse, #sidebar .collapsing { transition: height .25s ease; }

        /* Tabla legible global: separadores notorios en todos los modulos */
        .content-wrapper .grid-view { overflow-x: auto; }
        .content-wrapper .grid-view table,
        .content-wrapper .table.df-separated-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: max-content;
            background: #fff;
        }
        .content-wrapper .grid-view thead th,
        .content-wrapper .table.df-separated-table thead th {
            background: #e9eef5;
            border-top: 1px solid #b6c2d1 !important;
            border-bottom: 2px solid #aab8c9 !important;
            border-right: 1px solid #b6c2d1 !important;
            white-space: nowrap;
            padding-top: .68rem;
            padding-bottom: .68rem;
            font-weight: 700;
        }
        .content-wrapper .grid-view tbody td,
        .content-wrapper .table.df-separated-table tbody td {
            border-right: 1px solid #c8d2df !important;
            border-bottom: 1px solid #d3dce8 !important;
            white-space: nowrap;
            vertical-align: middle;
            padding-top: .56rem;
            padding-bottom: .56rem;
        }
        .content-wrapper .grid-view thead th:first-child,
        .content-wrapper .grid-view tbody td:first-child,
        .content-wrapper .table.df-separated-table thead th:first-child,
        .content-wrapper .table.df-separated-table tbody td:first-child {
            border-left: 1px solid #b6c2d1 !important;
        }
        .content-wrapper .grid-view tbody tr:nth-child(even) td,
        .content-wrapper .table.df-separated-table tbody tr:nth-child(even) td {
            background: #f8fbff;
        }
    </style>
    <link rel="stylesheet" href="/libs/fontawesome/css/all.min.css"/>
</head>
<body>
<?php $this->beginBody() ?>

<?php if ($user): ?>
<nav id="sidebar">
    <div class="sidebar-brand"><i class="fa-solid fa-baby-carriage me-2"></i>Dreams Factory</div>
    <ul class="nav flex-column mt-1 pb-4">
        <li class="nav-item">
            <a class="nav-link <?= $controllerId==='site' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/site/index']) ?>">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
        </li>

        <?php if ($user->isGerente()): ?>
        <li class="nav-item">
            <a class="nav-link <?= $controllerId==='bi' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/bi/index']) ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
        </li>
        <?php endif; ?>

        <?php if ($user->isGerente() || $user->isEncargado()): ?>
            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isCatalogo ? 'active' : '' ?>" data-bs-toggle="collapse" href="#df-catalogo" role="button" aria-expanded="<?= $isCatalogo ? 'true' : 'false' ?>" aria-controls="df-catalogo">
                    <span><i class="fa-solid fa-list"></i> Catálogo</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isCatalogo ? 'show' : '' ?>" id="df-catalogo">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item"><a class="nav-link <?= $controllerId==='categoria' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/categoria/index']) ?>"><i class="fa-solid fa-angles-right"></i> Categorías</a></li>
                        <li class="nav-item"><a class="nav-link <?= $controllerId==='producto' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/producto/index']) ?>"><i class="fa-solid fa-angles-right"></i> Productos</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isAlmacenGroup ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-gestion-almacen" role="button"
                   aria-expanded="<?= $isAlmacenGroup ? 'true' : 'false' ?>" aria-controls="df-gestion-almacen">
                    <span><i class="fa-solid fa-truck-ramp-box"></i> Gestionar Almacén</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isAlmacenGroup ? 'show' : '' ?>" id="df-gestion-almacen">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= $isAlmacenMod ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/almacen/index']) ?>">
                                <i class="fa-solid fa-warehouse"></i> Almacén
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='proveedor' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/proveedor/index']) ?>">
                                <i class="fa-solid fa-handshake"></i> Proveedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='compra' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/compra/index']) ?>">
                                <i class="fa-solid fa-cart-arrow-down"></i> Compra Productos
                            </a>
                        </li>
                        <?php if ($user->isEncargado() || $user->isGerente()): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center <?= ($isVentas && $actionId==='por-enviar') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/por-enviar']) ?>">
                                <span>Pedidos por Enviar</span>
                                <?php if ($badgePorEnviar > 0): ?><span class="badge bg-danger rounded-pill"><?= $badgePorEnviar ?></span><?php endif; ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($user->isEncargado() || $user->isVendedor() || $user->isGerente()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='enviados') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/enviados']) ?>">
                                <i class="fa-solid fa-truck"></i> Pedidos Enviados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='entregados') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/entregados']) ?>">
                                <i class="fa-solid fa-circle-check"></i> Pedidos Entregados
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isInventario ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-inventario" role="button"
                   aria-expanded="<?= $isInventario ? 'true' : 'false' ?>" aria-controls="df-inventario">
                    <span><i class="fa-solid fa-warehouse"></i> Inventario</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isInventario ? 'show' : '' ?>" id="df-inventario">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='estado-producto' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/estado-producto/index']) ?>">
                                <i class="fa-solid fa-chart-column"></i> Estado Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='stock-producto' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/stock-producto/index']) ?>">
                                <i class="fa-solid fa-boxes-stacked"></i> Stock Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='lote' && $actionId==='index' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/lote/index']) ?>">
                                <i class="fa-solid fa-layer-group"></i> Existencia por Producto
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='lote' && $actionId==='lotes' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/lote/lotes']) ?>">
                                <i class="fa-solid fa-arrows-rotate"></i> Rotación de Inventario
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <?php if ($user->isGerente()): ?>
            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isVentasGroup ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-ventas" role="button"
                   aria-expanded="<?= $isVentasGroup ? 'true' : 'false' ?>" aria-controls="df-ventas">
                    <span><i class="fa-solid fa-cash-register"></i> Ventas</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isVentasGroup ? 'show' : '' ?>" id="df-ventas">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='cliente' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/cliente/index']) ?>">
                                <i class="fa-solid fa-store"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='visita' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/visita/index']) ?>">
                                <i class="fa-solid fa-map-location-dot"></i> Visitas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='create') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/create']) ?>">
                                <i class="fa-solid fa-cart-plus"></i> Nueva Venta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='index') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/index']) ?>">
                                <i class="fa-solid fa-receipt"></i> Historial Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='listado') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/listado']) ?>">
                                <i class="fa-solid fa-list-check"></i> Seguimiento Entregas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='stock-producto' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/stock-producto/index']) ?>">
                                <i class="fa-solid fa-boxes-stacked"></i> Stock Disponible
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isPrecios ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-precios" role="button"
                   aria-expanded="<?= $isPrecios ? 'true' : 'false' ?>" aria-controls="df-precios">
                    <span><i class="fa-solid fa-money-bill-trend-up"></i> Precios</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isPrecios ? 'show' : '' ?>" id="df-precios">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='precio-venta' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/precio-venta/index']) ?>">
                                <i class="fa-solid fa-tag"></i> Precio Venta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='precio-oferta' && $actionId !== 'lista') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/precio-oferta/index']) ?>">
                                <i class="fa-solid fa-tags"></i> Ofertas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='precio-oferta' && $actionId === 'lista') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/precio-oferta/lista']) ?>">
                                <i class="fa-solid fa-list"></i> Lista Ofertas
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <?php if ($user->isGerente()): ?>
            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isReportesGroup ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-reportes" role="button"
                   aria-expanded="<?= $isReportesGroup ? 'true' : 'false' ?>"
                   aria-controls="df-reportes">
                    <span><i class="fa-solid fa-chart-line"></i> Reportes Gestión</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isReportesGroup ? 'show' : '' ?>" id="df-reportes">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='reporte' && $actionId==='personal') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/reporte/personal']) ?>">
                                <i class="fa-solid fa-ranking-star"></i> Por Personal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='reporte' && $actionId==='mensual') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/reporte/mensual']) ?>">
                                <i class="fa-solid fa-chart-column"></i> Por Mes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='reporte' && $actionId==='compras-producto') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/reporte/compras-producto']) ?>">
                                <i class="fa-solid fa-truck"></i> Compras-Producto
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='reporte' && $actionId==='ventas-clientes') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/reporte/ventas-clientes']) ?>">
                                <i class="fa-solid fa-people-group"></i> Ventas Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='reporte' && $actionId==='resumen-clientes') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/reporte/resumen-clientes']) ?>">
                                <i class="fa-solid fa-trophy"></i> Resumen Clientes
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= $isBalanceGroup ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-balance" role="button"
                   aria-expanded="<?= $isBalanceGroup ? 'true' : 'false' ?>"
                   aria-controls="df-balance">
                    <span><i class="fa-solid fa-scale-balanced"></i> Balance Gestión</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= $isBalanceGroup ? 'show' : '' ?>" id="df-balance">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='balance' && $actionId==='compras') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/balance/compras']) ?>">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Historial Compras
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='balance' && $actionId==='ventas') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/balance/ventas']) ?>">
                                <i class="fa-solid fa-receipt"></i> Historial Ventas
                            </a>
                        </li>
                        <?php if ($user && $user->isGerente()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='balance' && $actionId==='cerrar-gestion') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/balance/cerrar-gestion']) ?>">
                                <i class="fa-solid fa-lock"></i> Cerrar Gestión
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($user->isVendedor()): ?>
            <li class="nav-item">
                <a class="nav-link nav-group-toggle d-flex justify-content-between align-items-center <?= ($isVentasGroup || $isEntregaGroup || $controllerId==='stock-producto' || $isPrecios) ? 'active' : '' ?>"
                   data-bs-toggle="collapse" href="#df-ventas-vendedor" role="button"
                   aria-expanded="<?= ($isVentasGroup || $isEntregaGroup || $controllerId==='stock-producto' || $isPrecios) ? 'true' : 'false' ?>"
                   aria-controls="df-ventas-vendedor">
                    <span><i class="fa-solid fa-cash-register"></i> Ventas</span>
                    <i class="fa-solid fa-chevron-down chevron"></i>
                </a>
                <div class="collapse <?= ($isVentasGroup || $isEntregaGroup || $controllerId==='stock-producto' || $isPrecios) ? 'show' : '' ?>" id="df-ventas-vendedor">
                    <ul class="nav flex-column nav-sub">
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='cliente' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/cliente/index']) ?>">
                                <i class="fa-solid fa-store"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='create') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/create']) ?>">
                                <i class="fa-solid fa-cart-plus"></i> Nueva Venta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='index') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/index']) ?>">
                                <i class="fa-solid fa-receipt"></i> Historial Ventas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='enviados') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/enviados']) ?>">
                                <i class="fa-solid fa-truck"></i> Pedidos Enviados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='entregados') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/entregados']) ?>">
                                <i class="fa-solid fa-circle-check"></i> Pedidos Entregados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($isVentas && $actionId==='listado') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/venta/listado']) ?>">
                                <i class="fa-solid fa-list-check"></i> Seguimiento Entregas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='stock-producto' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/stock-producto/index']) ?>">
                                <i class="fa-solid fa-boxes-stacked"></i> Stock Disponible
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='visita' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/visita/index']) ?>">
                                <i class="fa-solid fa-map-location-dot"></i> Visitas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $controllerId==='precio-venta' ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/precio-venta/index']) ?>">
                                <i class="fa-solid fa-tag"></i> Precio Venta
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($controllerId==='precio-oferta' && $actionId==='lista') ? 'active' : '' ?>" href="<?= Yii::$app->urlManager->createUrl(['/precio-oferta/lista']) ?>">
                                <i class="fa-solid fa-percent"></i> Lista Ofertas
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<div id="df-sidebar-overlay"></div>
<div id="main-content">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button id="df-sidebar-toggle" title="Mostrar/ocultar menú" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="page-title"><?= Html::encode($this->title) ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
                <i class="fa-solid fa-user-circle me-1"></i>
                <?= Html::encode($user->username) ?>
            </span>
            <!-- Botón Configuración -->
            <div class="dropdown df-settings-dropdown">
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" title="Configuración">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <div class="d-flex align-items-center px-3 py-2 gap-2" style="cursor:default;">
                            <i class="fa-solid fa-moon" style="color:#6c757d;width:18px;"></i>
                            <span class="me-auto small" style="white-space:nowrap;">Modo oscuro</span>
                            <button class="df-theme-toggle" id="df-dark-toggle" data-on="false"
                                    aria-label="Activar modo oscuro" title="Modo oscuro"></button>
                        </div>
                    </li>
                    <?php if ($user->isGerente()): ?>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item small <?= $isRolGroup ? 'fw-semibold' : '' ?>"
                           href="<?= Yii::$app->urlManager->createUrl(['/rol/index']) ?>">
                            <i class="fa-solid fa-user-shield me-2" style="width:18px;color:var(--df-azul2);"></i>Gestión de Roles
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item small <?= $controllerId==='usuario' ? 'fw-semibold' : '' ?>"
                           href="<?= Yii::$app->urlManager->createUrl(['/usuario/index']) ?>">
                            <i class="fa-solid fa-users-gear me-2" style="width:18px;color:var(--df-azul2);"></i>Usuarios
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php echo Html::beginForm(['/site/logout'], 'post', ['class'=>'d-inline']); ?>
            <?php echo Html::submitButton('<i class="fa-solid fa-right-from-bracket"></i> Salir', ['class'=>'btn btn-sm btn-outline-danger']); ?>
            <?php echo Html::endForm(); ?>
        </div>
    </div>
    <div class="content-wrapper">
        <?php foreach(['success','danger','warning','info'] as $t): ?>
            <?php if(Yii::$app->session->hasFlash($t)): ?>
            <div class="alert alert-<?= $t ?> alert-dismissible fade show">
                <?= Html::encode(Yii::$app->session->getFlash($t)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?= $content ?>
    </div>
</div>
<?php else: ?>
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#0d3b6e,#1a5fa8);">
    <?= $content ?>
</div>
<?php endif; ?>

<?php $this->endBody() ?>
<script>
/* ── Dark mode ──────────────────────────────────────────────── */
(function () {
    const KEY = 'df_dark_mode';
    const html = document.documentElement;
    const btn  = document.getElementById('df-dark-toggle');
    if (!btn) return;

    function apply(dark) {
        if (dark) {
            html.classList.add('df-dark');
        } else {
            html.classList.remove('df-dark');
        }
        btn.setAttribute('data-on', dark ? 'true' : 'false');
        btn.setAttribute('aria-label', dark ? 'Desactivar modo oscuro' : 'Activar modo oscuro');
    }

    const saved = localStorage.getItem(KEY) === '1';
    apply(saved);

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const now = html.classList.contains('df-dark');
        apply(!now);
        localStorage.setItem(KEY, !now ? '1' : '0');
    });
})();

/* ── Sidebar toggle ─────────────────────────────────────────── */
(function () {
    const SK      = 'df_sidebar_collapsed';
    const html    = document.documentElement;
    const toggleBtn  = document.getElementById('df-sidebar-toggle');
    const overlay    = document.getElementById('df-sidebar-overlay');
    const isMobile   = () => window.innerWidth <= 768;

    function isCollapsed() {
        if (isMobile()) return !html.classList.contains('df-sb-open');
        return html.classList.contains('df-sb-collapsed');
    }

    function setDesktop(collapsed) {
        if (collapsed) {
            html.classList.add('df-sb-collapsed');
        } else {
            html.classList.remove('df-sb-collapsed');
        }
        localStorage.setItem(SK, collapsed ? '1' : '0');
    }

    function setMobile(open) {
        if (open) {
            html.classList.add('df-sb-open');
        } else {
            html.classList.remove('df-sb-open');
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (isMobile()) {
                setMobile(!html.classList.contains('df-sb-open'));
            } else {
                setDesktop(!html.classList.contains('df-sb-collapsed'));
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            setMobile(false);
        });
    }
})();
</script>

<script>
/* ── dfProductoAutocomplete ─────────────────────────────────────
 * Activa un campo de texto con sugerencias de productos y
 * auto-rellena el select de categoría al seleccionar.
 *
 * Uso:
 *   dfProductoAutocomplete({
 *     inputId:       'df-prod-search',   // input de texto visible
 *     hiddenId:      'df-prod-id',       // input hidden con el id
 *     catSelectId:   'df-cat-select',    // select de categoría
 *     catIconId:     'df-cat-icon',      // span del ícono ✅
 *     searchUrl:     '/producto/buscar-producto',
 *   });
 * ──────────────────────────────────────────────────────────── */
window.dfProductoAutocomplete = function (opts) {
    const inp      = document.getElementById(opts.inputId);
    const hidden   = document.getElementById(opts.hiddenId);
    const catSel   = document.getElementById(opts.catSelectId);
    const catIcon  = document.getElementById(opts.catIconId);
    if (!inp || !hidden || !catSel) return;

    let dropdown = null;
    let timer    = null;
    let selected = false;  // flag: user chose from list

    function clearCat() {
        catSel.value    = '';
        catSel.disabled = false;
        if (catIcon) catIcon.style.display = 'none';
    }

    function fillCat(item) {
        // find or add option
        let opt = catSel.querySelector('option[value="' + item.categoria_id + '"]');
        if (!opt) {
            opt = document.createElement('option');
            opt.value = item.categoria_id;
            opt.textContent = item.categoria_nombre;
            catSel.appendChild(opt);
        }
        catSel.value    = item.categoria_id;
        catSel.disabled = true;
        if (catIcon) { catIcon.style.display = 'inline'; }
    }

    function removeDropdown() {
        if (dropdown) { dropdown.remove(); dropdown = null; }
    }

    function buildDropdown(items) {
        removeDropdown();
        if (!items.length) return;

        dropdown = document.createElement('ul');
        dropdown.className = 'df-autocomplete-list';
        dropdown.style.cssText = [
            'position:absolute', 'z-index:9999', 'background:#1e2a3a',
            'border:1px solid #2e4060', 'border-radius:6px', 'list-style:none',
            'padding:4px 0', 'margin:0', 'min-width:320px', 'max-height:220px',
            'overflow-y:auto', 'box-shadow:0 4px 16px rgba(0,0,0,.45)',
        ].join(';');

        items.forEach(function (item) {
            const li = document.createElement('li');
            li.textContent = item.text;
            li.style.cssText = 'padding:7px 14px;cursor:pointer;color:#d8eaf8;font-size:.85rem;';
            li.addEventListener('mouseenter', function () { li.style.background = '#253d5e'; });
            li.addEventListener('mouseleave', function () { li.style.background = ''; });
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                inp.value    = item.text;
                hidden.value = item.id;
                selected     = true;
                fillCat(item);
                removeDropdown();
            });
            dropdown.appendChild(li);
        });

        inp.parentElement.style.position = 'relative';
        inp.parentElement.appendChild(dropdown);
    }

    inp.addEventListener('input', function () {
        selected     = false;
        hidden.value = '';
        clearCat();
        clearTimeout(timer);
        const q = inp.value.trim();
        if (q.length < 2) { removeDropdown(); return; }
        timer = setTimeout(function () {
            fetch(opts.searchUrl + '?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(buildDropdown)
                .catch(function () {});
        }, 250);
    });

    inp.addEventListener('blur', function () {
        setTimeout(function () {
            removeDropdown();
            // if user typed but didn't pick from list, clear
            if (!selected && inp.value.trim() !== '') {
                inp.value    = '';
                hidden.value = '';
                clearCat();
            }
        }, 200);
    });

    inp.addEventListener('keydown', function (e) {
        if (!dropdown) return;
        const items = dropdown.querySelectorAll('li');
        const active = dropdown.querySelector('li.df-ac-active');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const next = active ? (active.nextElementSibling || items[0]) : items[0];
            if (active) active.classList.remove('df-ac-active'), active.style.background = '';
            if (next)   { next.classList.add('df-ac-active'); next.style.background = '#253d5e'; }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prev = active ? (active.previousElementSibling || items[items.length - 1]) : items[items.length - 1];
            if (active) active.classList.remove('df-ac-active'), active.style.background = '';
            if (prev)   { prev.classList.add('df-ac-active'); prev.style.background = '#253d5e'; }
        } else if (e.key === 'Enter' && active) {
            e.preventDefault();
            active.dispatchEvent(new MouseEvent('mousedown'));
        } else if (e.key === 'Escape') {
            removeDropdown();
        }
    });
};
</script>
</body>
</html>
<?php $this->endPage() ?>
