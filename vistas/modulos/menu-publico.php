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

$mesa = isset($_GET["mesa"]) ? trim($_GET["mesa"]) : "";

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

    /* HEADER */
    .menu-header {
      background: #ffffff;
      border-bottom: 1px solid var(--border-color);
      padding: 14px 18px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: var(--shadow-sm);
    }

    .header-inner {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .brand-box {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }

    .brand-logo {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      object-fit: contain;
      background: #ffffff;
      border: 1px solid var(--border-color);
      padding: 2px;
      flex-shrink: 0;
    }

    .brand-title {
      font-size: 16px;
      font-weight: 800;
      color: var(--text-main);
      margin: 0;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .brand-subtitle {
      font-size: 11px;
      font-weight: 600;
      color: var(--accent);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .mesa-pill {
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
      border-radius: 20px;
      padding: 4px 12px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }

    /* HERO SEARCH BAR */
    .search-container {
      max-width: 1100px;
      margin: 16px auto 12px;
      padding: 0 16px;
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
      top: 73px;
      background: var(--bg-body);
      z-index: 90;
      padding: 6px 0 10px;
      border-bottom: 1px solid var(--border-color);
    }

    .categories-scroll {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 16px;
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
    .menu-container {
      max-width: 1100px;
      margin: 16px auto;
      padding: 0 16px;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 16px;
    }

    @media (max-width: 600px) {
      .products-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }
    }

    /* CARD DE PRODUCTO */
    .product-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 14px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-sm);
      transition: all 0.25s ease;
      position: relative;
    }

    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
      border-color: #cbd5e1;
    }

    .product-img-box {
      width: 100%;
      height: 180px;
      background: #f1f5f9;
      position: relative;
      overflow: hidden;
    }

    .product-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-img {
      transform: scale(1.03);
    }

    .product-cat-tag {
      position: absolute;
      top: 10px;
      left: 10px;
      background: rgba(15, 23, 42, 0.75);
      backdrop-filter: blur(4px);
      color: #ffffff;
      font-size: 10px;
      font-weight: 700;
      padding: 3px 8px;
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

  <!-- HEADER DEL MENÚ -->
  <header class="menu-header">
    <div class="header-inner">
      <div class="brand-box">
        <img src="<?= $logoNegocio ?>" alt="<?= htmlspecialchars($nombreNegocio) ?>" class="brand-logo" onerror="this.src='<?= $logoDefault ?>'">
        <div>
          <h1 class="brand-title"><?= htmlspecialchars($nombreNegocio) ?></h1>
          <p class="brand-subtitle">
            <i class="fa fa-circle" style="font-size: 8px;"></i> Abierto para Pedidos
          </p>
        </div>
      </div>

      <?php if (!empty($mesa)): ?>
        <div class="mesa-pill">
          <i class="fa fa-map-marker"></i> <?= htmlspecialchars($mesa) ?>
        </div>
      <?php endif; ?>
    </div>
  </header>

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
  <main class="menu-container">
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
                  <?= $moneda ?> <?= number_format($prod["precio_venta"], 2, ',', '.') ?>
                </div>
                <button type="button" class="btn-add-cart btnAgregarCarrito" 
                        data-id="<?= $prod['id'] ?>" 
                        data-nombre="<?= htmlspecialchars($prod['descripcion']) ?>" 
                        data-precio="<?= floatval($prod['precio_venta']) ?>">
                  <i class="fa fa-plus"></i> Agregar
                </button>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

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
  <script src="vistas/plugins/sweetalert2/sweetalert2.all.js"></script>

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

      if (cantTotal > 0) {
        $('#barraFlotanteCarrito').addClass('visible');
      } else {
        $('#barraFlotanteCarrito').removeClass('visible');
      }
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

    // AGREGAR AL CARRITO
    $(document).on('click', '.btnAgregarCarrito', function() {
      var id = parseInt($(this).attr('data-id'));
      var nombre = $(this).attr('data-nombre');
      var precio = parseFloat($(this).attr('data-precio'));

      var existe = carrito.find(function(i) { return i.id === id; });
      if (existe) {
        existe.cantidad++;
      } else {
        carrito.push({
          id: id,
          nombre: nombre,
          precio: precio,
          cantidad: 1
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

    // ENVIAR PEDIDO FINAL
    $('#formCheckoutPedido').on('submit', function(e) {
      e.preventDefault();

      if (carrito.length === 0) {
        swal({
          type: 'warning',
          title: 'Carrito vacío',
          text: 'Debes agregar al menos un producto al carrito.'
        });
        return;
      }

      var nombre = $('#checkoutNombre').val().trim();
      var celular = $('#checkoutCelular').val().trim();
      var direccion = $('#checkoutDireccion').val().trim();
      var notas = $('#checkoutNotas').val().trim();

      if (!nombre || !celular || !direccion) {
        swal({
          type: 'warning',
          title: 'Campos incompletos',
          text: 'Por favor ingresa Nombre, Celular y Dirección/Mesa.'
        });
        return;
      }

      var boton = $('#btnConfirmarPedidoFinal');
      boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando tu pedido al restaurante...');

      var datos = new FormData();
      datos.append('accion', 'procesar_pedido');
      datos.append('nombre', nombre);
      datos.append('celular', celular);
      datos.append('direccion', direccion);
      datos.append('notas', notas);
      datos.append('mesa', MESA_URL);
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
          boton.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Confirmar y Enviar Pedido');

          if (res.status === 'ok') {
            // Vaciar carrito
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
            swal({
              type: 'error',
              title: 'Error al enviar pedido',
              text: res.mensaje || 'No se pudo registrar la orden en este momento. Intenta de nuevo.'
            });
          }
        },
        error: function() {
          boton.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Confirmar y Enviar Pedido');
          swal({
            type: 'error',
            title: 'Error de conexión',
            text: 'Ocurrió un problema de comunicación con el servidor. Revisa tu conexión.'
          });
        }
      });
    });

    $('#btnCerrarExito').on('click', function() {
      $('#modalExitoPedido').modal('hide');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Inicializar estado del carrito al cargar
    $(document).ready(function() {
      actualizarBarraCarrito();
    });
  </script>
</body>
</html>
