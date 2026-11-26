<?php
require_once __DIR__ . "/../../controladores/menu-digital.controlador.php";
require_once __DIR__ . "/../../controladores/configuracion.controlador.php";
if (file_exists(__DIR__ . "/../../controladores/factus.controlador.php")) {
    require_once __DIR__ . "/../../controladores/factus.controlador.php";
}
if (file_exists(__DIR__ . "/../../modelos/factus.modelo.php")) {
    require_once __DIR__ . "/../../modelos/factus.modelo.php";
}

$configGeneral = ControladorConfiguracion::ctrObtenerConfiguracion();
$configFactus = class_exists("ControladorFactus") ? ControladorFactus::ctrObtenerConfiguracion() : null;

// 1. DETERMINAR NOMBRE DE LA EMPRESA
// Prioridad: "Datos para la Factura" (nombre_comercial / nombre_empresa) -> Configuración General -> Fallback "KONTROL POS"
$nombreNegocio = "";
if (!empty($configFactus["nombre_comercial"])) {
    $nombreNegocio = trim($configFactus["nombre_comercial"]);
} elseif (!empty($configFactus["nombre_empresa"])) {
    $nombreNegocio = trim($configFactus["nombre_empresa"]);
} elseif (!empty($configGeneral["nombre_empresa"])) {
    $nombreNegocio = trim($configGeneral["nombre_empresa"]);
}

if (empty($nombreNegocio)) {
    $nombreNegocio = "KONTROL POS";
}

// 2. DETERMINAR LOGO DE LA EMPRESA
// Fallback oficial KONTROL POS
$logoDefault = "vistas/img/plantilla/logo-ppal.png";
if (!file_exists($logoDefault)) {
    $logoDefault = "vistas/img/plantilla/icono-negro1.png";
}
if (!file_exists($logoDefault)) {
    $logoDefault = "vistas/img/plantilla/icono-negro.png";
}

// Prioridad: "Datos para la Factura" (logo_empresa) -> Configuración General (logo) -> Fallback Logo Kontrol POS
$logoNegocio = "";
if (!empty($configFactus["logo_empresa"]) && file_exists($configFactus["logo_empresa"])) {
    $logoNegocio = $configFactus["logo_empresa"];
} elseif (!empty($configGeneral["logo"]) && file_exists($configGeneral["logo"])) {
    $logoNegocio = $configGeneral["logo"];
}

if (empty($logoNegocio)) {
    $logoNegocio = $logoDefault;
}

$moneda = !empty($configGeneral["moneda"]) ? $configGeneral["moneda"] : "$";
$telefonoNegocio = !empty($configFactus["telefono_empresa"]) ? $configFactus["telefono_empresa"] : (!empty($configGeneral["telefono"]) ? $configGeneral["telefono"] : "");
$direccionNegocio = !empty($configFactus["direccion_empresa"]) ? $configFactus["direccion_empresa"] : (!empty($configGeneral["direccion"]) ? $configGeneral["direccion"] : "");

$mesa = isset($_GET["mesa"]) ? trim($_GET["mesa"]) : "";

// Configuración de Pasarela Bold
$configBold = ControladorMenuDigital::ctrObtenerConfiguracionBold();
$boldActivo = (!empty($configBold["bold_activo"]) && intval($configBold["bold_activo"]) === 1 && !empty($configBold["bold_api_key"]) && !empty($configBold["bold_secret_key"]));

$ordenRetornoBold = null;
if (isset($_GET["bold_session"]) && !empty($_GET["bold_session"])) {
    $sessionRetorno = trim($_GET["bold_session"]);
    $resConfirmacion = ModeloMenuDigital::mdlConfirmarPagoYCrearOrden($sessionRetorno, 'RETORNO_CLIENTE');
    if ($resConfirmacion && $resConfirmacion["status"] === "ok" && !empty($resConfirmacion["codigo"])) {
        $ordenRetornoBold = intval($resConfirmacion["codigo"]);
    }
} elseif (isset($_GET["bold_orden"])) {
    $ordenRetornoBold = intval($_GET["bold_orden"]);
}

// Obtener solo productos marcados para menú digital
$productos = ControladorMenuDigital::ctrMostrarProductosMenu(true);

// Agrupar categorías
$categorias = [];
if (!empty($productos)) {
    foreach ($productos as $p) {
        $cat = !empty($p["categoria"]) ? $p["categoria"] : "General";
        if (!in_array($cat, $categorias)) {
            $categorias[] = $cat;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?= htmlspecialchars($nombreNegocio) ?> - Menú Digital</title>

  <!-- Favicon -->
  <link rel="icon" href="<?= $logoNegocio ?>">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 3.3.7 & FontAwesome -->
  <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">

  <!-- Bold Checkout Library -->
  <script src="https://checkout.bold.co/library/boldPaymentButton.js"></script>

  <style>
    :root {
      --primary: #2563eb;
      --primary-hover: #1d4ed8;
      --accent: #10b981;
      --bg-body: #f8fafc;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --card-bg: #ffffff;
      --border-color: #e2e8f0;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
      --shadow-md: 0 4px 14px rgba(0,0,0,0.08);
      --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.12);
    }

    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--bg-body);
      color: var(--text-main);
      margin: 0;
      padding: 0;
      padding-bottom: 95px; /* Espacio para el carrito flotante */
    }

    /* HEADER MODERNO */
    .menu-header {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      color: #ffffff;
      padding: 14px 20px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .header-inner {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .brand-box {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 0;
    }

    .brand-logo-wrapper {
      width: 52px;
      height: 52px;
      border-radius: 12px;
      background: #ffffff;
      padding: 3px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.25);
      border: 2px solid rgba(255,255,255,0.85);
      flex-shrink: 0;
    }

    .brand-logo {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      border-radius: 9px;
    }

    .brand-info {
      min-width: 0;
    }

    .brand-title {
      font-size: 19px;
      font-weight: 800;
      color: #ffffff;
      margin: 0 0 4px 0;
      line-height: 1.2;
      letter-spacing: -0.3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .brand-meta-row {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .status-indicator {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 600;
      color: #34d399;
      background: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.3);
      padding: 2px 8px;
      border-radius: 20px;
    }

    .pulse-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #10b981;
      box-shadow: 0 0 6px #10b981;
    }

    .meta-item {
      font-size: 11.5px;
      color: #94a3b8;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .mesa-pill {
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
      border-radius: 20px;
      padding: 5px 12px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    /* ESTRUCTURA PRINCIPAL ESCRITORIO CON SIDEBAR CARRITO */
    .menu-layout-wrapper {
      max-width: 1280px;
      margin: 20px auto 40px auto;
      padding: 0 20px;
      display: flex;
      gap: 28px;
      align-items: flex-start;
    }

    .menu-catalog-area {
      flex: 1;
      min-width: 0;
    }

    /* SIDEBAR DEL CARRITO EN ESCRITORIO (>= 992px) */
    .menu-sidebar-cart {
      width: 360px;
      min-width: 360px;
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 20px;
      position: sticky;
      top: 85px;
      box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
    }

    @media (min-width: 992px) {
      /* Ocultar barra flotante inferior en pantallas de escritorio */
      .floating-cart-bar {
        display: none !important;
      }
    }

    @media (max-width: 991px) {
      .menu-layout-wrapper {
        display: block;
        padding: 0 16px;
        margin: 16px auto;
      }
      .menu-sidebar-cart {
        display: none !important;
      }
    }

    /* HERO SEARCH BAR */
    .search-container {
      margin-bottom: 14px;
    }

    .search-input-wrapper {
      position: relative;
    }

    .search-input-wrapper i {
      position: absolute;
      left: 14px;
      top: 13px;
      color: var(--text-muted);
      font-size: 15px;
    }

    .menu-search-input {
      width: 100%;
      height: 44px;
      padding-left: 42px;
      padding-right: 14px;
      border: 1px solid var(--border-color);
      border-radius: 12px;
      background: #ffffff;
      font-size: 14px;
      font-weight: 500;
      outline: none;
      transition: all 0.2s;
      box-shadow: var(--shadow-sm);
    }

    .menu-search-input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    /* CATEGORÍAS TABS (HORIZONTAL SCROLL) */
    .categories-wrapper {
      position: sticky;
      top: 80px;
      background: var(--bg-body);
      z-index: 90;
      padding: 6px 0 10px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 16px;
    }

    .categories-scroll {
      display: flex;
      gap: 8px;
      overflow-x: auto;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
    }

    .categories-scroll::-webkit-scrollbar {
      display: none;
    }

    .cat-pill {
      background: #ffffff;
      color: var(--text-muted);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 7px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
    }

    .cat-pill:hover,
    .cat-pill.active {
      background: var(--primary);
      color: #ffffff;
      border-color: var(--primary);
      box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    /* GRID DE PRODUCTOS */
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 18px;
    }

    @media (max-width: 640px) {
      .menu-container,
      .menu-layout-wrapper {
        padding: 0 10px;
        margin: 10px auto 70px auto;
      }

      /* Cuadrícula de 2 columnas para celular */
      .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
      }

      /* Tarjeta vertical ultra compacta */
      .product-card {
        flex-direction: column;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        background: var(--card-bg);
      }

      .product-card:hover {
        transform: none;
      }

      /* Imagen 100% CUADRADA (1:1) */
      .product-img-box {
        width: 100%;
        height: auto;
        aspect-ratio: 1 / 1;
        position: relative;
        overflow: hidden;
        background: #f8fafc;
      }

      .product-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .product-cat-tag {
        font-size: 8px;
        padding: 2px 5px;
        top: 6px;
        left: 6px;
        border-radius: 4px;
        letter-spacing: 0.2px;
      }

      /* Cuerpo compacto de la tarjeta */
      .product-body {
        padding: 8px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
      }

      .product-name {
        font-size: 13px;
        font-weight: 700;
        margin: 0 0 3px 0;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 32px;
      }

      .product-caract {
        font-size: 10px;
        line-height: 1.3;
        padding: 3px 6px;
        margin-bottom: 6px;
        border-radius: 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .product-footer {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 5px;
        margin-top: auto;
        padding-top: 5px;
        border-top: 1px solid #f1f5f9;
      }

      .product-price {
        font-size: 14px;
        font-weight: 800;
        color: #059669;
        line-height: 1.1;
      }

      .btn-add-cart {
        width: 100%;
        justify-content: center;
        padding: 6px 4px;
        font-size: 11.5px;
        font-weight: 700;
        border-radius: 8px;
        gap: 4px;
      }
    }

    /* CARD DE PRODUCTO ESCRITORIO */
    .product-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 14px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
      transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
    }

    .product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 28px -4px rgba(15, 23, 42, 0.1);
      border-color: #cbd5e1;
    }

    .product-img-box {
      width: 100%;
      height: 185px;
      background: #f1f5f9;
      position: relative;
      overflow: hidden;
    }

    .product-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.35s ease;
    }

    .product-card:hover .product-img {
      transform: scale(1.04);
    }

    .product-cat-tag {
      position: absolute;
      top: 10px;
      left: 10px;
      background: rgba(15, 23, 42, 0.72);
      backdrop-filter: blur(6px);
      color: #ffffff;
      font-size: 10.5px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .product-body {
      padding: 14px;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    .product-name {
      font-size: 15px;
      font-weight: 700;
      color: var(--text-main);
      margin: 0 0 6px 0;
      line-height: 1.3;
    }

    /* CARACTERÍSTICAS / INGREDIENTES */
    .product-caract {
      font-size: 12px;
      color: var(--text-muted);
      line-height: 1.4;
      margin-bottom: 12px;
      background: #f8fafc;
      padding: 7px 10px;
      border-radius: 8px;
      border-left: 3px solid var(--primary);
    }

    .product-caract i {
      color: var(--primary);
      margin-right: 4px;
    }

    .product-footer {
      margin-top: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding-top: 8px;
      border-top: 1px solid #f1f5f9;
    }

    .product-price {
      font-size: 17px;
      font-weight: 800;
      color: #059669;
    }

    .btn-add-cart {
      background: var(--primary);
      color: #ffffff;
      border: none;
      border-radius: 10px;
      padding: 7px 14px;
      font-size: 13px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-add-cart:hover,
    .btn-add-cart:active {
      background: var(--primary-hover);
      color: #fff;
      transform: scale(0.98);
    }

    /* BARRA FLOTANTE DEL CARRITO */
    .floating-cart-bar {
      position: fixed;
      bottom: 16px;
      left: 16px;
      right: 16px;
      max-width: 500px;
      margin: 0 auto;
      background: #0f172a;
      color: #ffffff;
      border-radius: 16px;
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35);
      z-index: 1000;
      transform: translateY(150%);
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .floating-cart-bar.visible {
      transform: translateY(0);
    }

    .cart-summary {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .cart-badge-count {
      background: var(--primary);
      color: #ffffff;
      font-weight: 800;
      font-size: 13px;
      width: 32px;
      height: 32px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cart-info-labels {
      display: flex;
      flex-direction: column;
    }

    .cart-info-total {
      font-size: 16px;
      font-weight: 800;
      color: #34d399;
      line-height: 1.2;
    }

    .cart-info-text {
      font-size: 11px;
      color: #94a3b8;
    }

    .btn-view-cart {
      background: #ffffff;
      color: #0f172a;
      border: none;
      border-radius: 10px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }

    .btn-view-cart:hover {
      background: #f1f5f9;
    }

    /* MODAL DE CARRITO Y CHECKOUT */
    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }

    .modal-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .modal-title {
      font-size: 16px;
      font-weight: 800;
      color: var(--text-main);
      margin: 0;
    }

    .cart-item-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f1f5f9;
      gap: 10px;
    }

    .cart-item-info {
      flex: 1;
      min-width: 0;
    }

    .cart-item-name {
      font-size: 13px;
      font-weight: 700;
      color: var(--text-main);
      margin: 0 0 2px 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .cart-item-price {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
    }

    .cart-stepper {
      display: flex;
      align-items: center;
      gap: 6px;
      background: #f1f5f9;
      padding: 3px;
      border-radius: 8px;
    }

    .btn-step {
      width: 26px;
      height: 26px;
      border-radius: 6px;
      border: none;
      background: #ffffff;
      color: var(--text-main);
      font-weight: 700;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }

    .step-qty {
      font-size: 13px;
      font-weight: 700;
      min-width: 22px;
      text-align: center;
    }

    .cart-item-subtotal {
      font-size: 13px;
      font-weight: 700;
      color: #059669;
      min-width: 65px;
      text-align: right;
    }

    .btn-remove-item {
      color: #ef4444;
      background: transparent;
      border: none;
      font-size: 13px;
      cursor: pointer;
      padding: 4px;
    }

    .checkout-form label {
      font-size: 12px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 4px;
      display: block;
    }

    .checkout-form .form-control {
      height: 42px;
      border-radius: 10px;
      border-color: var(--border-color);
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 12px;
    }

    .checkout-form .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-muted);
    }

    .empty-state i {
      font-size: 48px;
      color: #cbd5e1;
      margin-bottom: 12px;
    }
  </style>
</head>
<body>

  <!-- HEADER DEL MENÚ MODERNO -->
  <header class="menu-header">
    <div class="header-inner">
      <div class="brand-box">
        <div class="brand-logo-wrapper">
          <img src="<?= $logoNegocio ?>" alt="<?= htmlspecialchars($nombreNegocio) ?>" class="brand-logo" onerror="this.src='<?= $logoDefault ?>'">
        </div>
        <div class="brand-info">
          <h1 class="brand-title"><?= htmlspecialchars($nombreNegocio) ?></h1>
          <div class="brand-meta-row">
            <span class="status-indicator">
              <span class="pulse-dot"></span> Abierto para Pedidos
            </span>
            <?php if (!empty($telefonoNegocio)): ?>
              <span class="meta-item hidden-xs"><i class="fa fa-phone"></i> <?= htmlspecialchars($telefonoNegocio) ?></span>
            <?php endif; ?>
            <?php if (!empty($direccionNegocio)): ?>
              <span class="meta-item hidden-xs"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($direccionNegocio) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!empty($mesa)): ?>
        <div class="mesa-pill">
          <i class="fa fa-map-marker"></i> <?= htmlspecialchars($mesa) ?>
        </div>
      <?php endif; ?>
    </div>
  </header>

  <!-- CONTENEDOR PRINCIPAL: CATÁLOGO Y SIDEBAR CARRITO EN ESCRITORIO -->
  <div class="menu-layout-wrapper">
    
    <!-- COLUMNA DEL CATÁLOGO DE PRODUCTOS -->
    <div class="menu-catalog-area">

      <!-- BUSCADOR RÁPIDO -->
      <div class="search-container">
        <div class="search-input-wrapper">
          <i class="fa fa-search"></i>
          <input type="text" id="buscadorProductos" class="menu-search-input" placeholder="Buscar en el menú...">
        </div>
      </div>

      <!-- CATEGORÍAS (BARRA HORIZONTAL SCROLL) -->
      <?php if (!empty($categorias)): ?>
        <div class="categories-wrapper">
          <div class="categories-scroll">
            <button type="button" class="cat-pill active" data-cat="todas">
              <i class="fa fa-th-large"></i> Todos (<?= count($productos) ?>)
            </button>
            <?php foreach ($categorias as $catItem): ?>
              <button type="button" class="cat-pill" data-cat="<?= htmlspecialchars($catItem) ?>">
                <?= htmlspecialchars($catItem) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- CONTENEDOR DE PRODUCTOS -->
      <?php if (empty($productos)): ?>
        <div class="empty-state">
          <i class="fa fa-cutlery"></i>
          <h4 style="font-weight: 700; color: #1e293b;">Menú en preparación</h4>
          <p>Pronto estarán disponibles nuestros productos. ¡Vuelve a visitarnos!</p>
        </div>
      <?php else: ?>
        <div class="products-grid" id="gridProductos">
          <?php foreach ($productos as $prod): 
            $fotoProd = (!empty($prod["imagen"]) && file_exists($prod["imagen"])) ? $prod["imagen"] : "vistas/img/productos/default/anonymous.png";
            $categoriaNombre = !empty($prod["categoria"]) ? $prod["categoria"] : "General";
            $caract = trim($prod["caracteristicas_menu"] ?? "");
            $tieneVariantes = (!empty($prod["tiene_variantes"]) && intval($prod["tiene_variantes"]) === 1 && !empty($prod["variantes"]));
            $jsonVariantes = $tieneVariantes ? htmlspecialchars(json_encode($prod["variantes"]), ENT_QUOTES, 'UTF-8') : '[]';
          ?>
            <div class="product-card" 
                 data-id="<?= $prod['id'] ?>" 
                 data-nombre="<?= htmlspecialchars($prod['descripcion']) ?>" 
                 data-precio="<?= floatval($prod['precio_venta']) ?>" 
                 data-cat="<?= htmlspecialchars($categoriaNombre) ?>"
                 data-busqueda="<?= mb_strtolower(htmlspecialchars($prod['descripcion'] . ' ' . $categoriaNombre . ' ' . $caract)) ?>">
              
              <div class="product-img-box">
                <img src="<?= $fotoProd ?>" alt="<?= htmlspecialchars($prod['descripcion']) ?>" class="product-img" onerror="this.src='vistas/img/productos/default/anonymous.png'">
                <span class="product-cat-tag"><?= htmlspecialchars($categoriaNombre) ?></span>
                <?php if ($tieneVariantes): ?>
                  <span class="product-badge-options hidden-xs"><i class="fa fa-sliders"></i> Opciones</span>
                <?php endif; ?>
              </div>

              <div class="product-body">
                <h3 class="product-name"><?= htmlspecialchars($prod['descripcion']) ?></h3>

                <!-- Características / Ingredientes para el Menú -->
                <?php if (!empty($caract)): ?>
                  <div class="product-caract" title="Características / Ingredientes">
                    <i class="fa fa-info-circle"></i> <?= htmlspecialchars($caract) ?>
                  </div>
                <?php endif; ?>

                <div class="product-footer">
                  <div class="product-price">
                    <?php if ($tieneVariantes): ?>
                      <span style="font-size: 11px; font-weight: 600; color: #64748b; display: block; line-height: 1; margin-bottom: 2px;">Desde</span>
                    <?php endif; ?>
                    <?= $moneda ?> <?= number_format($prod["precio_venta"], 2, ',', '.') ?>
                  </div>

                  <?php if ($tieneVariantes): ?>
                    <button type="button" class="btn-add-cart btnAbrirModalVariantes" style="background: #4f46e5;" 
                            data-id="<?= $prod['id'] ?>" 
                            data-nombre="<?= htmlspecialchars($prod['descripcion']) ?>" 
                            data-precio-base="<?= floatval($prod['precio_venta']) ?>"
                            data-foto="<?= $fotoProd ?>"
                            data-variantes='<?= $jsonVariantes ?>'>
                      <i class="fa fa-sliders"></i> Opciones
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn-add-cart btnAgregarCarrito" 
                            data-id="<?= $prod['id'] ?>" 
                            data-nombre="<?= htmlspecialchars($prod['descripcion']) ?>" 
                            data-precio="<?= floatval($prod['precio_venta']) ?>">
                      <i class="fa fa-plus"></i> Agregar
                    </button>
                  <?php endif; ?>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>

    <!-- ASIDE: SIDEBAR DEL CARRITO EN ESCRITORIO (>= 992px) -->
    <aside class="menu-sidebar-cart" id="sidebarCarritoDesktop">
      <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 14px; border-bottom: 1px solid var(--border-color); margin-bottom: 14px;">
        <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i class="fa fa-shopping-bag" style="color: var(--primary);"></i> Tu Pedido
        </h3>
        <span class="badge" id="sidebarBadgeCount" style="background: var(--primary); font-size: 11px; padding: 4px 8px; border-radius: 10px;">0</span>
      </div>

      <!-- Estado vacío -->
      <div id="sidebarEstadoVacio" style="text-align: center; padding: 36px 14px; color: #94a3b8;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto;">
          <i class="fa fa-cutlery" style="font-size: 22px; color: #cbd5e1;"></i>
        </div>
        <h5 style="font-size: 14px; font-weight: 700; color: #475569; margin: 0 0 4px 0;">Tu pedido está vacío</h5>
        <p style="font-size: 12px; margin: 0; line-height: 1.4;">Selecciona tus platos o productos favoritos del catálogo para agregarlos.</p>
      </div>

      <!-- Estado con items -->
      <div id="sidebarEstadoConItems" style="display: none;">
        <!-- Lista de productos -->
        <div id="contenedorSidebarItems" style="max-height: calc(100vh - 320px); overflow-y: auto; padding-right: 4px; margin-bottom: 14px;">
          <!-- Dinámico vía JS -->
        </div>

        <!-- Resumen de cuenta -->
        <div style="background: #f8fafc; border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; border: 1px solid #f1f5f9;">
          <div style="display: flex; justify-content: space-between; font-size: 13px; color: #64748b; margin-bottom: 6px;">
            <span>Subtotal</span>
            <span id="sidebarSubtotalText" style="font-weight: 700; color: #1e293b;">$ 0</span>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 6px; border-top: 1px solid #e2e8f0;">
            <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Total estimado</span>
            <span id="sidebarTotalText" style="font-size: 18px; font-weight: 800; color: #059669;">$ 0</span>
          </div>
        </div>

        <!-- Botón de checkout -->
        <button type="button" class="btn btn-success btn-block" id="btnSidebarCheckout" style="background: #059669; border-color: #059669; height: 46px; font-size: 14px; font-weight: 800; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);">
          <span>Confirmar y Enviar Pedido</span>
          <i class="fa fa-arrow-right"></i>
        </button>
      </div>
    </aside>

  </div>

  <!-- BARRA FLOTANTE DEL CARRITO -->
  <div class="floating-cart-bar" id="barraFlotanteCarrito">
    <div class="cart-summary">
      <div class="cart-badge-count" id="badgeCantidadTotal">0</div>
      <div class="cart-info-labels">
        <span class="cart-info-total" id="labelTotalCarrito"><?= $moneda ?> 0</span>
        <span class="cart-info-text">Total estimado</span>
      </div>
    </div>
    <button type="button" class="btn-view-cart" id="btnAbrirModalCarrito">
      <i class="fa fa-shopping-cart"></i> Ver Carrito
    </button>
  </div>

  <!-- MODAL: SELECCIONAR VARIANTE DEL PRODUCTO -->
  <div class="modal fade" id="modalSeleccionarVariante" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 440px; margin: 30px auto;">
      <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);">
        
        <div class="modal-header" style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
          <h4 class="modal-title" style="font-weight: 800; font-size: 16px; color: #1e293b; margin: 0;">
            <i class="fa fa-sliders text-primary" style="margin-right: 6px;"></i> Personalizar Pedido
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="font-size: 24px; color: #94a3b8; outline: none; margin: 0; line-height: 1;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 20px;">
          <!-- Detalle de Producto Base -->
          <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border-color);">
            <img id="modalVarFoto" src="vistas/img/productos/default/anonymous.png" alt="Producto" style="width: 65px; height: 65px; border-radius: 12px; object-fit: cover; border: 1px solid var(--border-color);">
            <div>
              <h4 id="modalVarNombre" style="font-weight: 800; font-size: 15px; color: #1e293b; margin: 0 0 4px 0;">--</h4>
              <span id="modalVarPrecioBase" style="font-size: 13px; font-weight: 700; color: #059669;">--</span>
            </div>
          </div>

          <!-- Opciones de Variantes -->
          <div>
            <label style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 10px; display: block;">
              Selecciona una opción:
            </label>
            <div id="contenedorListaVariantes" style="display: flex; flex-direction: column; gap: 8px; max-height: 260px; overflow-y: auto;">
              <!-- Se inyecta dinámicamente -->
            </div>
          </div>

          <!-- Cantidad -->
          <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; font-weight: 700; color: #334155;">Cantidad:</span>
            <div class="cart-stepper" style="background: #f1f5f9; border-radius: 10px; padding: 2px;">
              <button type="button" class="btn-step" id="btnMenosVarQty" style="width: 32px; height: 32px;">-</button>
              <span class="step-qty" id="labelVarQty" style="font-size: 14px; font-weight: 700; min-width: 32px; text-align: center;">1</span>
              <button type="button" class="btn-step" id="btnMasVarQty" style="width: 32px; height: 32px;">+</button>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid var(--border-color);">
          <button type="button" class="btn btn-primary btn-block btn-lg" id="btnConfirmarVarianteCarrito" style="border-radius: 12px; font-weight: 800; font-size: 15px; background: var(--primary); border: none; padding: 12px;">
            <i class="fa fa-shopping-basket"></i> Agregar al Carrito (<span id="btnVarTotalPrecio"><?= $moneda ?> 0</span>)
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- MODAL 1: VER DETALLE DEL CARRITO -->
  <div class="modal fade" id="modalCarrito" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 460px; margin: 30px auto;">
      <div class="modal-content">
        
        <div class="modal-header">
          <h4 class="modal-title">
            <i class="fa fa-shopping-basket text-primary" style="margin-right: 6px;"></i> Tu Carrito de Pedido
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="font-size: 22px;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 16px 20px;">
          <div id="contenedorItemsCarrito" style="max-height: 340px; overflow-y: auto;">
            <!-- Renderizado dinámico vía JavaScript -->
          </div>

          <div style="border-top: 2px dashed var(--border-color); margin-top: 15px; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 14px; font-weight: 700; color: #475569;">Total a Pagar:</span>
            <span style="font-size: 20px; font-weight: 800; color: #059669;" id="modalTotalCarrito"><?= $moneda ?> 0</span>
          </div>
        </div>

        <div class="modal-footer" style="padding: 14px 20px; background: #f8fafc; display: flex; justify-content: space-between; gap: 10px;">
          <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 10px; font-weight: 600;">
            Seguir explorando
          </button>
          <button type="button" class="btn btn-primary" id="btnIrACheckout" style="border-radius: 10px; font-weight: 700; background: var(--primary); border: none; padding: 8px 18px;">
            Continuar con el Pedido <i class="fa fa-arrow-right"></i>
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- MODAL 2: COMPLETAR DATOS DEL CLIENTE Y CONFIRMAR -->
  <div class="modal fade" id="modalCheckout" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 480px; margin: 30px auto;">
      <div class="modal-content">
        
        <div class="modal-header">
          <h4 class="modal-title">
            <i class="fa fa-user-circle text-primary" style="margin-right: 6px;"></i> Datos de Entrega del Pedido
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="font-size: 22px;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 20px;">
          <!-- PASO 1: FORMULARIO DE CHECKOUT -->
          <div id="seccionFormularioCheckout">
            <form id="formCheckoutPedido" class="checkout-form">
              
              <!-- Nombre Completo -->
              <div>
                <label><i class="fa fa-user text-primary"></i> Nombre y Apellido *</label>
                <input type="text" id="checkoutNombre" class="form-control" placeholder="Ej: Carlos Pérez" required>
              </div>

              <!-- Celular / Teléfono (Con el que se verifica o crea el cliente) -->
              <div>
                <label><i class="fa fa-phone text-primary"></i> Número de Celular / WhatsApp *</label>
                <input type="tel" id="checkoutCelular" class="form-control" placeholder="Ej: 3001234567" required>
                <small class="help-block" style="font-size: 11px; margin-top: -8px; margin-bottom: 10px; color: #64748b;">
                  Utilizamos tu número para contactarte y verificar el estado de tu pedido.
                </small>
              </div>

              <!-- Dirección / Mesa -->
              <div>
                <label><i class="fa fa-map-marker text-primary"></i> Dirección de Entrega o Número de Mesa *</label>
                <input type="text" id="checkoutDireccion" class="form-control" placeholder="Ej: Calle 10 # 20-30 o Mesa 4" value="<?= htmlspecialchars($mesa) ?>" required>
              </div>

              <!-- Notas Adicionales -->
              <div>
                <label><i class="fa fa-commenting-o text-primary"></i> Notas Adicionales (Opcional)</label>
                <textarea id="checkoutNotas" class="form-control" rows="2" placeholder="Ej: Sin cebolla, bebida fría, envolver para regalo..." style="height: auto; border-radius: 10px;"></textarea>
              </div>

              <?php if ($boldActivo): ?>
              <!-- Método de Pago -->
              <div style="margin-top: 14px; margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 8px; display: block;">
                  <i class="fa fa-credit-card text-primary"></i> Método de Pago *
                </label>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                  <!-- Opción 1: Bold Online -->
                  <label class="metodo-pago-card" style="border: 2px solid #2563eb; background: #eff6ff; border-radius: 10px; padding: 11px 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                      <input type="radio" name="metodoPagoCheckout" value="bold" checked style="accent-color: #2563eb; width: 18px; height: 18px; margin: 0;">
                      <div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                          Pagar en línea con Bold
                        </div>
                        <div style="font-size: 11px; color: #64748b;">
                          Tarjetas Crédito/Débito, PSE, Nequi, Daviplata, Bancolombia
                        </div>
                      </div>
                    </div>
                    <span class="badge" style="background: #2563eb; color: #fff; font-size: 10px; padding: 4px 8px; border-radius: 6px;">Recomendado</span>
                  </label>

                  <!-- Opción 2: Efectivo / Contra entrega -->
                  <label class="metodo-pago-card" style="border: 1px solid #cbd5e1; background: #ffffff; border-radius: 10px; padding: 11px 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                      <input type="radio" name="metodoPagoCheckout" value="efectivo" style="accent-color: #059669; width: 18px; height: 18px; margin: 0;">
                      <div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                          Pago contra entrega / En mesa
                        </div>
                        <div style="font-size: 11px; color: #64748b;">
                          Paga en efectivo o datáfono al recibir tu pedido
                        </div>
                      </div>
                    </div>
                    <i class="fa fa-money text-success" style="font-size: 20px;"></i>
                  </label>
                </div>
              </div>
              <?php else: ?>
              <input type="hidden" name="metodoPagoCheckout" value="efectivo">
              <?php endif; ?>

              <!-- Resumen Total -->
              <div style="background: #f1f5f9; padding: 12px 14px; border-radius: 10px; margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; font-weight: 700; color: #334155;">Total del Pedido:</span>
                <span style="font-size: 18px; font-weight: 800; color: #059669;" id="checkoutTotalText"><?= $moneda ?> 0</span>
              </div>

              <div style="margin-top: 18px;">
                <button type="submit" class="btn btn-success btn-block btn-lg" id="btnConfirmarPedidoFinal" style="border-radius: 12px; font-weight: 800; font-size: 15px; background: #059669; border: none; padding: 12px;">
                  <i class="fa fa-check-circle"></i> Confirmar y Enviar Pedido
                </button>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- MODAL 3: PEDIDO ENVIADO CON ÉXITO -->
  <div class="modal fade" id="modalExitoPedido" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog text-center" role="document" style="max-width: 440px; margin: 40px auto;">
      <div class="modal-content" style="padding: 30px 20px;">
        
        <div style="width: 70px; height: 70px; background: #d1fae5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 34px; margin: 0 auto 15px;">
          <i class="fa fa-check"></i>
        </div>

        <h3 style="font-weight: 800; color: #1e293b; margin-top: 0; margin-bottom: 6px;">¡Pedido Recibido!</h3>
        <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">
          Tu pedido ha llegado al sistema y nuestro equipo ha comenzado a prepararlo.
        </p>

        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; text-align: left; margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="color: #64748b; font-size: 12px;">Código de Orden:</span>
            <b style="color: #1e293b; font-size: 14px;" id="exitoCodigoOrden">#----</b>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="color: #64748b; font-size: 12px;">Cliente:</span>
            <b style="color: #1e293b; font-size: 13px;" id="exitoNombreCliente">--</b>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: #64748b; font-size: 12px;">Entrega / Ubicación:</span>
            <b style="color: #1e293b; font-size: 13px;" id="exitoDireccionCliente">--</b>
          </div>
        </div>

        <button type="button" class="btn btn-primary btn-block btn-lg" id="btnCerrarExito" style="border-radius: 12px; font-weight: 700; background: var(--primary); border: none;">
          Aceptar y Nuevo Pedido
        </button>

      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
  <script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="vistas/plugins/sweetalert2/sweetalert2.all.min.js"></script>

  <script>
    var MONEDA = "<?= $moneda ?>";
    var MESA_URL = "<?= htmlspecialchars($mesa) ?>";

    // Manejo de carrito en localStorage
    var carrito = JSON.parse(localStorage.getItem('menu_carrito_pos') || '[]');

    function guardarCarrito() {
      localStorage.setItem('menu_carrito_pos', JSON.stringify(carrito));
      actualizarBarraCarrito();
    }

    function formatearPrecio(monto) {
      return MONEDA + ' ' + parseFloat(monto).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function actualizarBarraCarrito() {
      var cantTotal = 0;
      var totalDinero = 0;

      carrito.forEach(function(item) {
        cantTotal += item.cantidad;
        totalDinero += (item.cantidad * item.precio);
      });

      $('#badgeCantidadTotal').text(cantTotal);
      $('#labelTotalCarrito').text(formatearPrecio(totalDinero));
      $('#modalTotalCarrito').text(formatearPrecio(totalDinero));
      $('#checkoutTotalText').text(formatearPrecio(totalDinero));

      // Sidebar en Escritorio
      $('#sidebarBadgeCount').text(cantTotal);
      $('#sidebarSubtotalText').text(formatearPrecio(totalDinero));
      $('#sidebarTotalText').text(formatearPrecio(totalDinero));

      if (cantTotal > 0) {
        $('#barraFlotanteCarrito').addClass('visible');
        $('#sidebarEstadoVacio').hide();
        $('#sidebarEstadoConItems').fadeIn(150);
        $('#btnSidebarCheckout').prop('disabled', false);
      } else {
        $('#barraFlotanteCarrito').removeClass('visible');
        $('#sidebarEstadoVacio').show();
        $('#sidebarEstadoConItems').hide();
        $('#btnSidebarCheckout').prop('disabled', true);
      }

      renderizarSidebarDesktop();
    }

    function renderizarSidebarDesktop() {
      var contenedor = $('#contenedorSidebarItems');
      if (!contenedor.length) return;
      contenedor.empty();

      if (carrito.length === 0) return;

      carrito.forEach(function(item, idx) {
        var subtotal = item.cantidad * item.precio;
        var row = $(
          '<div class="cart-item-row" style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 8px;">' +
            '<div class="cart-item-info" style="flex: 1; min-width: 0;">' +
              '<h5 class="cart-item-name" style="font-size: 13px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="' + item.nombre + '">' + item.nombre + '</h5>' +
              '<span class="cart-item-price" style="font-size: 11.5px; font-weight: 600; color: #64748b;">' + formatearPrecio(item.precio) + ' c/u</span>' +
            '</div>' +
            '<div class="cart-stepper" style="display: flex; align-items: center; gap: 4px; background: #f1f5f9; padding: 2px 4px; border-radius: 8px;">' +
              '<button type="button" class="btn-step btnMenosItem" data-idx="' + idx + '" style="width: 24px; height: 24px; border-radius: 6px; border: none; background: #fff; font-weight: 700; font-size: 11px; cursor: pointer;">-</button>' +
              '<span class="step-qty" style="font-size: 12px; font-weight: 700; min-width: 18px; text-align: center;">' + item.cantidad + '</span>' +
              '<button type="button" class="btn-step btnMasItem" data-idx="' + idx + '" style="width: 24px; height: 24px; border-radius: 6px; border: none; background: #fff; font-weight: 700; font-size: 11px; cursor: pointer;">+</button>' +
            '</div>' +
            '<div class="cart-item-subtotal" style="font-size: 13px; font-weight: 700; color: #059669; min-width: 60px; text-align: right;">' + formatearPrecio(subtotal) + '</div>' +
            '<button type="button" class="btn-remove-item btnEliminarItem" data-idx="' + idx + '" title="Eliminar" style="color: #ef4444; background: transparent; border: none; font-size: 13px; cursor: pointer; padding: 3px;"><i class="fa fa-trash-o"></i></button>' +
          '</div>'
        );
        contenedor.append(row);
      });
    }

    function renderizarItemsCarrito() {
      var contenedor = $('#contenedorItemsCarrito');
      contenedor.empty();

      if (carrito.length === 0) {
        contenedor.html('<div class="empty-state" style="padding: 20px;"><i class="fa fa-shopping-basket"></i><p>Tu carrito está vacío.</p></div>');
        $('#btnIrACheckout').prop('disabled', true);
        return;
      }

      $('#btnIrACheckout').prop('disabled', false);

      carrito.forEach(function(item, idx) {
        var subtotal = item.cantidad * item.precio;
        var row = $('<div class="cart-item-row"></div>');

        var info = $('<div class="cart-item-info"></div>');
        info.append('<h5 class="cart-item-name">' + item.nombre + '</h5>');
        info.append('<span class="cart-item-price">' + formatearPrecio(item.precio) + ' c/u</span>');

        var stepper = $('<div class="cart-stepper"></div>');
        stepper.append('<button type="button" class="btn-step btnMenosItem" data-idx="' + idx + '">-</button>');
        stepper.append('<span class="step-qty">' + item.cantidad + '</span>');
        stepper.append('<button type="button" class="btn-step btnMasItem" data-idx="' + idx + '">+</button>');

        var subtotalEl = $('<div class="cart-item-subtotal">' + formatearPrecio(subtotal) + '</div>');
        var btnEliminar = $('<button type="button" class="btn-remove-item btnEliminarItem" data-idx="' + idx + '" title="Eliminar"><i class="fa fa-trash-o"></i></button>');

        row.append(info).append(stepper).append(subtotalEl).append(btnEliminar);
        contenedor.append(row);
      });
    }

    // =============================================
    // GESTIÓN DE PRODUCTOS CON VARIANTES
    // =============================================
    var productoVarianteActual = null;
    var varianteSeleccionada = null;
    var cantidadVarianteModal = 1;

    $(document).on('click', '.btnAbrirModalVariantes', function() {
      var id = parseInt($(this).attr('data-id'));
      var nombre = $(this).attr('data-nombre');
      var precioBase = parseFloat($(this).attr('data-precio-base'));
      var foto = $(this).attr('data-foto');
      var variantes = JSON.parse($(this).attr('data-variantes') || '[]');

      if (variantes.length === 0) {
        mostrarAlerta('warning', 'Sin opciones', 'Este producto no tiene opciones activas en este momento.');
        return;
      }

      productoVarianteActual = {
        id: id,
        nombre: nombre,
        precioBase: precioBase,
        foto: foto,
        variantes: variantes
      };

      cantidadVarianteModal = 1;
      $('#labelVarQty').text('1');
      $('#modalVarNombre').text(nombre);
      $('#modalVarPrecioBase').text('Base: ' + formatearPrecio(precioBase));
      $('#modalVarFoto').attr('src', foto);

      var contenedor = $('#contenedorListaVariantes');
      contenedor.empty();

      variantes.forEach(function(v, idx) {
        var isChecked = (idx === 0) ? 'checked' : '';
        var bgStyle = (idx === 0) ? 'border: 2px solid #2563eb; background: #eff6ff;' : 'border: 1px solid #cbd5e1; background: #ffffff;';
        
        var opcionHtml = $(
          '<label class="var-opcion-card" style="' + bgStyle + ' border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin: 0; transition: all 0.2s;">' +
            '<div style="display: flex; align-items: center; gap: 10px;">' +
              '<input type="radio" name="opcionVarianteRadio" value="' + v.id + '" ' + isChecked + ' style="accent-color: #2563eb; width: 18px; height: 18px; margin: 0;">' +
              '<div>' +
                '<div style="font-size: 14px; font-weight: 700; color: #1e293b;">' + v.nombre_opcion + '</div>' +
                (v.precio_adicional > 0 ? '<div style="font-size: 11px; color: #64748b;">(+' + formatearPrecio(v.precio_adicional) + ')</div>' : '') +
              '</div>' +
            '</div>' +
            '<span style="font-size: 14px; font-weight: 800; color: #059669;">' + formatearPrecio(v.precio_final) + '</span>' +
          '</label>'
        );
        contenedor.append(opcionHtml);
      });

      varianteSeleccionada = variantes[0];
      actualizarTotalModalVariante();

      $('#modalSeleccionarVariante').modal('show');
    });

    $(document).on('change', 'input[name="opcionVarianteRadio"]', function() {
      $('.var-opcion-card').css({ 'border': '1px solid #cbd5e1', 'background': '#ffffff' });
      var radio = $(this);
      if (radio.is(':checked')) {
        radio.closest('.var-opcion-card').css({ 'border': '2px solid #2563eb', 'background': '#eff6ff' });
      }
      var varId = parseInt(radio.val());
      if (productoVarianteActual && productoVarianteActual.variantes) {
        varianteSeleccionada = productoVarianteActual.variantes.find(function(v) { return v.id === varId; });
        actualizarTotalModalVariante();
      }
    });

    $('#btnMasVarQty').on('click', function() {
      cantidadVarianteModal++;
      $('#labelVarQty').text(cantidadVarianteModal);
      actualizarTotalModalVariante();
    });

    $('#btnMenosVarQty').on('click', function() {
      if (cantidadVarianteModal > 1) {
        cantidadVarianteModal--;
        $('#labelVarQty').text(cantidadVarianteModal);
        actualizarTotalModalVariante();
      }
    });

    function actualizarTotalModalVariante() {
      if (!varianteSeleccionada) return;
      var total = varianteSeleccionada.precio_final * cantidadVarianteModal;
      $('#btnVarTotalPrecio').text(formatearPrecio(total));
    }

    // CONFIRMAR VARIANTE SELECCIONADA Y AGREGAR AL CARRITO
    $('#btnConfirmarVarianteCarrito').on('click', function() {
      if (!productoVarianteActual || !varianteSeleccionada) return;

      var id = productoVarianteActual.id;
      var idVar = varianteSeleccionada.id;
      var nombreCompleto = productoVarianteActual.nombre + ' (' + varianteSeleccionada.nombre_opcion + ')';
      var precio = varianteSeleccionada.precio_final;
      var cant = cantidadVarianteModal;

      var existe = carrito.find(function(i) {
        return i.id === id && i.id_variante === idVar;
      });

      if (existe) {
        existe.cantidad += cant;
      } else {
        carrito.push({
          id: id,
          nombre: nombreCompleto,
          precio: precio,
          cantidad: cant,
          es_variante: 1,
          id_variante: idVar,
          nombre_opcion: varianteSeleccionada.nombre_opcion
        });
      }

      guardarCarrito();
      $('#modalSeleccionarVariante').modal('hide');

      mostrarAlerta('success', '¡Agregado!', nombreCompleto + ' (' + cant + ') se agregó a tu pedido.', 1400);
    });

    // AGREGAR AL CARRITO PRODUCTO SIMPLE
    $(document).on('click', '.btnAgregarCarrito', function() {
      var id = parseInt($(this).attr('data-id'));
      var nombre = $(this).attr('data-nombre');
      var precio = parseFloat($(this).attr('data-precio'));

      var existe = carrito.find(function(i) { return i.id === id && !i.es_variante; });
      if (existe) {
        existe.cantidad++;
      } else {
        carrito.push({
          id: id,
          nombre: nombre,
          precio: precio,
          cantidad: 1,
          es_variante: 0,
          id_variante: null
        });
      }

      guardarCarrito();

      // Pequeña animación en el botón
      var btn = $(this);
      btn.html('<i class="fa fa-check"></i> Agregado');
      btn.css('background', '#059669');
      setTimeout(function() {
        btn.html('<i class="fa fa-plus"></i> Agregar');
        btn.css('background', '');
      }, 800);
    });

    // ABRIR MODAL CARRITO
    $('#btnAbrirModalCarrito').on('click', function() {
      renderizarItemsCarrito();
      $('#modalCarrito').modal('show');
    });

    // CAMBIAR CANTIDAD (+ / -)
    $(document).on('click', '.btnMasItem', function() {
      var idx = parseInt($(this).attr('data-idx'));
      if (carrito[idx]) {
        carrito[idx].cantidad++;
        guardarCarrito();
        renderizarItemsCarrito();
      }
    });

    $(document).on('click', '.btnMenosItem', function() {
      var idx = parseInt($(this).attr('data-idx'));
      if (carrito[idx]) {
        if (carrito[idx].cantidad > 1) {
          carrito[idx].cantidad--;
        } else {
          carrito.splice(idx, 1);
        }
        guardarCarrito();
        renderizarItemsCarrito();
      }
    });

    // ELIMINAR ITEM
    $(document).on('click', '.btnEliminarItem', function() {
      var idx = parseInt($(this).attr('data-idx'));
      if (carrito[idx]) {
        carrito.splice(idx, 1);
        guardarCarrito();
        renderizarItemsCarrito();
      }
    });

    // CONTINUAR A CHECKOUT
    $('#btnIrACheckout').on('click', function() {
      $('#modalCarrito').modal('hide');
      setTimeout(function() {
        $('#modalCheckout').modal('show');
      }, 350);
    });

    // CONTINUAR A CHECKOUT DESDE SIDEBAR ESCRITORIO
    $(document).on('click', '#btnSidebarCheckout', function() {
      if (carrito.length === 0) return;
      $('#modalCheckout').modal('show');
    });

    // FILTRO DE CATEGORÍAS
    $('.cat-pill').on('click', function() {
      $('.cat-pill').removeClass('active');
      $(this).addClass('active');

      var cat = $(this).attr('data-cat');
      var busqueda = $('#buscadorProductos').val().toLowerCase().trim();

      filtrarProductos(cat, busqueda);
    });

    // BUSCADOR EN TIEMPO REAL
    $('#buscadorProductos').on('keyup input', function() {
      var busqueda = $(this).val().toLowerCase().trim();
      var cat = $('.cat-pill.active').attr('data-cat') || 'todas';

      filtrarProductos(cat, busqueda);
    });

    function filtrarProductos(cat, busqueda) {
      $('.product-card').each(function() {
        var cardCat = $(this).attr('data-cat');
        var cardBusqueda = $(this).attr('data-busqueda');

        var coincideCat = (cat === 'todas' || cardCat === cat);
        var coincideTexto = (busqueda === '' || cardBusqueda.indexOf(busqueda) !== -1);

        if (coincideCat && coincideTexto) {
          $(this).fadeIn(150);
        } else {
          $(this).fadeOut(150);
        }
      });
    }

    // CAMBIO DE MÉTODO DE PAGO EN CHECKOUT
    $(document).on('change', 'input[name="metodoPagoCheckout"]', function() {
      $('.metodo-pago-card').css({ 'border': '1px solid #cbd5e1', 'background': '#ffffff' });
      var radio = $(this);
      if (radio.is(':checked')) {
        radio.closest('.metodo-pago-card').css({ 'border': '2px solid #2563eb', 'background': '#eff6ff' });
      }
      var valorMetodo = $('input[name="metodoPagoCheckout"]:checked').val();
      if (valorMetodo === 'bold') {
        $('#btnConfirmarPedidoFinal').html('<i class="fa fa-credit-card"></i> Proceder al Pago con Bold').css('background', '#2563eb');
      } else {
        $('#btnConfirmarPedidoFinal').html('<i class="fa fa-check-circle"></i> Confirmar y Enviar Pedido').css('background', '#059669');
      }
    });

    function mostrarAlerta(tipo, titulo, texto, timer) {
      if (typeof swal === 'function') {
        var opts = { type: tipo, title: titulo, text: texto };
        if (timer) {
          opts.timer = timer;
          opts.showConfirmButton = false;
        }
        swal(opts);
      } else if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        var opts = { icon: tipo, title: titulo, text: texto };
        if (timer) {
          opts.timer = timer;
          opts.showConfirmButton = false;
        }
        Swal.fire(opts);
      } else {
        alert(titulo + ": " + texto);
      }
    }

    // ENVIAR PEDIDO FINAL
    $('#formCheckoutPedido').on('submit', function(e) {
      e.preventDefault();

      if (carrito.length === 0) {
        mostrarAlerta('warning', 'Carrito vacío', 'Debes agregar al menos un producto al carrito.');
        return;
      }

      var nombre = $('#checkoutNombre').val().trim();
      var celular = $('#checkoutCelular').val().trim();
      var direccion = $('#checkoutDireccion').val().trim();
      var notas = $('#checkoutNotas').val().trim();
      var metodoPago = $('input[name="metodoPagoCheckout"]:checked').val() || $('input[name="metodoPagoCheckout"]').val() || 'efectivo';

      if (!nombre || !celular || !direccion) {
        mostrarAlerta('warning', 'Campos incompletos', 'Por favor ingresa Nombre, Celular y Dirección/Mesa.');
        return;
      }

      var boton = $('#btnConfirmarPedidoFinal');
      var textoOriginal = (metodoPago === 'bold') ? '<i class="fa fa-credit-card"></i> Proceder al Pago con Bold' : '<i class="fa fa-check-circle"></i> Confirmar y Enviar Pedido';
      var textoCargando = (metodoPago === 'bold') ? 'Conectando con pasarela Bold...' : 'Enviando tu pedido al restaurante...';
      boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + textoCargando);

      var datos = new FormData();
      datos.append('accion', 'procesar_pedido');
      datos.append('nombre', nombre);
      datos.append('celular', celular);
      datos.append('direccion', direccion);
      datos.append('notas', notas);
      datos.append('mesa', MESA_URL);
      datos.append('metodo_pago', metodoPago);
      datos.append('productos', JSON.stringify(carrito));

      $.ajax({
        url: 'ajax/menu-digital.ajax.php',
        method: 'POST',
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
          if (res.status === 'ok') {
            // Flujo con Pasarela Bold: Abrir pasarela oficial de Bold (Modal seguro integrado)
            if (res.pago_online && res.bold) {
              boton.prop('disabled', false).html(textoOriginal);
              $('#modalCheckout').modal('hide');

              var iniciarBold = function() {
                try {
                  var checkout = new BoldCheckout({
                    orderId: res.bold.order_id,
                    currency: res.bold.currency,
                    amount: String(res.bold.amount),
                    apiKey: res.bold.api_key,
                    integritySignature: res.bold.integrity_signature,
                    description: res.bold.description || "Pedido Menu Digital",
                    redirectionUrl: res.bold.redirection_url
                  });
                  checkout.open();
                } catch (errBold) {
                  console.error("Error al inicializar BoldCheckout:", errBold);
                  if (res.bold.checkout_url) {
                    window.location.href = res.bold.checkout_url;
                  }
                }
              };

              if (typeof BoldCheckout !== 'undefined') {
                iniciarBold();
              } else {
                var sBold = document.createElement('script');
                sBold.src = 'https://checkout.bold.co/library/boldPaymentButton.js';
                sBold.onload = function() {
                  if (typeof BoldCheckout !== 'undefined') {
                    iniciarBold();
                  } else if (res.bold.checkout_url) {
                    window.location.href = res.bold.checkout_url;
                  }
                };
                sBold.onerror = function() {
                  if (res.bold.checkout_url) {
                    window.location.href = res.bold.checkout_url;
                  } else {
                    mostrarAlerta('error', 'Error Pasarela', 'No se pudo cargar la librería de Bold. Por favor desactiva extensiones de bloqueo de publicidad e intenta de nuevo.');
                  }
                };
                document.head.appendChild(sBold);
              }
              return;
            }

            // Flujo normal (Pago en efectivo o contra entrega)
            boton.prop('disabled', false).html(textoOriginal);
            carrito = [];
            guardarCarrito();

            $('#modalCheckout').modal('hide');
            $('#exitoCodigoOrden').text('#' + res.codigo);
            $('#exitoNombreCliente').text(nombre);
            $('#exitoDireccionCliente').text(direccion);

            setTimeout(function() {
              $('#modalExitoPedido').modal('show');
            }, 400);
          } else {
            boton.prop('disabled', false).html(textoOriginal);
            mostrarAlerta('error', 'Error al enviar pedido', res.mensaje || 'No se pudo registrar la orden en este momento. Intenta de nuevo.');
          }
        },
        error: function(xhr, status, error) {
          boton.prop('disabled', false).html(textoOriginal);
          var msg = 'Ocurrió un problema de comunicación con el servidor. Revisa tu conexión.';
          if (xhr.responseJSON && xhr.responseJSON.mensaje) {
            msg = xhr.responseJSON.mensaje;
          }
          mostrarAlerta('error', 'Error de conexión', msg);
        }
      });
    });

    $('#btnCerrarExito').on('click', function() {
      $('#modalExitoPedido').modal('hide');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Restablecer vista del modal al cerrarse
    $('#modalCheckout').on('hidden.bs.modal', function() {
      var valorMetodo = $('input[name="metodoPagoCheckout"]:checked').val();
      if (valorMetodo === 'bold') {
        $('#btnConfirmarPedidoFinal').prop('disabled', false).html('<i class="fa fa-credit-card"></i> Proceder al Pago con Bold').css('background', '#2563eb');
      } else {
        $('#btnConfirmarPedidoFinal').prop('disabled', false).html('<i class="fa fa-check-circle"></i> Confirmar y Enviar Pedido').css('background', '#059669');
      }
    });

    // Inicializar estado del carrito al cargar
    $(document).ready(function() {
      actualizarBarraCarrito();

      <?php if (!empty($ordenRetornoBold)): ?>
      // Notificación de confirmación de pago retornado desde Bold
      localStorage.removeItem('menu_carrito_pos');
      carrito = [];
      actualizarBarraCarrito();
      swal({
        type: 'success',
        title: '¡Pago Exitoso!',
        text: 'Tu transacción ha sido aprobada por Bold y tu pedido #<?= $ordenRetornoBold ?> ya fue confirmado y enviado al restaurante.',
        confirmButtonColor: '#059669',
        confirmButtonText: 'Ver Menú'
      }).then(function() {
        if (window.history.replaceState) {
          var urlLimpia = window.location.protocol + "//" + window.location.host + window.location.pathname + (window.location.search.indexOf('mesa=') !== -1 ? '?ruta=menu&mesa=<?= urlencode($mesa) ?>' : '?ruta=menu');
          window.history.replaceState({path: urlLimpia}, '', urlLimpia);
        }
      });
      <?php endif; ?>
    });
  </script>
</body>
</html>
