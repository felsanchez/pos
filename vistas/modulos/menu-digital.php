<?php
if (!puedeVer('menu_digital')) {
    echo '<script>window.location = "inicio";</script>';
    return;
}

$puedeCrearMenu = puedeAccion('menu_digital', 'crear');

$productos = ControladorMenuDigital::ctrMostrarProductosMenu(false);
$totalProductos = is_array($productos) ? count($productos) : 0;
$totalActivos = 0;
$totalInactivos = 0;

$configGeneral = ControladorConfiguracion::ctrObtenerConfiguracion();
$configFactus = class_exists("ControladorFactus") ? ControladorFactus::ctrObtenerConfiguracion() : null;

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

if (!empty($productos)) {
    foreach ($productos as $p) {
        if (!empty($p["en_menu_digital"]) && intval($p["en_menu_digital"]) === 1) {
            $totalActivos++;
        } else {
            $totalInactivos++;
        }
    }
}

// Detectar URL base para el menú público
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$basePath = strtok($uri, '?');
$baseDir = dirname($basePath);
if ($baseDir == '/' || $baseDir == '\\') {
    $baseDir = '';
}
$urlMenuPublico = $protocolo . "://" . $host . $baseDir . "/index.php?ruta=menu";

// Generar QR Code localmente en base64 para carga instantánea y 100% offline
$qrSrcInicial = "ajax/generar-qr.php?data=" . urlencode($urlMenuPublico);
if (file_exists(__DIR__ . "/../../extensiones/tcpdf/pdf/phpqrcode/qrlib.php")) {
    require_once __DIR__ . "/../../extensiones/tcpdf/pdf/phpqrcode/qrlib.php";
    ob_start();
    QRcode::png($urlMenuPublico, false, QR_ECLEVEL_M, 6, 2);
    $qrPngBytes = ob_get_clean();
    if (!empty($qrPngBytes)) {
        $qrSrcInicial = "data:image/png;base64," . base64_encode($qrPngBytes);
    }
}
?>

<div class="content-wrapper">
  
  <section class="content-header">
    <h1>
      <i class="fa fa-cutlery text-primary" style="margin-right: 8px;"></i> Menú Digital & Códigos QR
      <small>Configura los productos visibles y comparte tu menú con carrito de compras</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Menú Digital</li>
    </ol>
  </section>

  <section class="content">

    <div class="nav-tabs-custom" style="box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-radius: 4px; overflow: hidden;">
      
      <ul class="nav nav-tabs" style="font-size: 14px; background: #fafafa; border-bottom: 2px solid #3c8dbc;">
        <li class="active">
          <a href="#tab_catalogo_menu" data-toggle="tab" style="font-weight: 600; padding: 12px 20px;">
            <i class="fa fa-list text-primary" style="margin-right: 6px;"></i> Catálogo del Menú Digital
          </a>
        </li>
        <?php if ($puedeCrearMenu): ?>
        <li>
          <a href="#tab_qr_menu" data-toggle="tab" style="font-weight: 600; padding: 12px 20px;">
            <i class="fa fa-qrcode text-warning" style="margin-right: 6px;"></i> Enlace Público & Códigos QR
          </a>
        </li>
        <?php else: ?>
        <li class="disabled" style="cursor: not-allowed;" title="Permiso de creación requerido para acceder a esta pestaña">
          <a href="javascript:void(0);" style="font-weight: 600; padding: 12px 20px; color: #94a3b8; cursor: not-allowed; background: #f1f5f9;" onclick="return false;">
            <i class="fa fa-lock text-muted" style="margin-right: 6px;"></i> Enlace Público & Códigos QR
          </a>
        </li>
        <?php endif; ?>
      </ul>

      <div class="tab-content" style="padding: 20px 15px;">

        <!--=====================================
        PESTAÑA 1: CATÁLOGO DEL MENÚ DIGITAL
        ======================================-->
        <div class="tab-pane active" id="tab_catalogo_menu">

          <!-- Tarjetas KPI -->
          <div class="row" style="margin-bottom: 20px;">
            
            <div class="col-md-4 col-sm-4 col-xs-12">
              <div class="info-box" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 6px;">
                <span class="info-box-icon bg-aqua" style="border-radius: 6px 0 0 6px;"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total Productos</span>
                  <span class="info-box-number" style="font-size: 22px;"><?= $totalProductos ?></span>
                </div>
              </div>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-12">
              <div class="info-box" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 6px;">
                <span class="info-box-icon bg-green" style="border-radius: 6px 0 0 6px;"><i class="fa fa-eye"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Visibles en Menú Digital</span>
                  <span class="info-box-number" style="font-size: 22px; color: #00a65a;"><?= $totalActivos ?></span>
                </div>
              </div>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-12">
              <div class="info-box" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 6px;">
                <span class="info-box-icon bg-gray" style="border-radius: 6px 0 0 6px;"><i class="fa fa-eye-slash"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Ocultos / No en Menú</span>
                  <span class="info-box-number" style="font-size: 22px; color: #6c757d;"><?= $totalInactivos ?></span>
                </div>
              </div>
            </div>

          </div>

          <!-- Tabla de Configuración de Productos -->
          <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
            <div class="box-header with-border" style="background: #f8fafc; padding: 12px 15px;">
              <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #1e293b;">
                <i class="fa fa-cog text-muted"></i> Selecciona los productos que aparecerán en el Menú Digital
              </h3>
              <div class="pull-right">
                <?php if ($puedeCrearMenu): ?>
                <a href="<?= $urlMenuPublico ?>" target="_blank" class="btn btn-sm btn-primary" style="font-weight: 600;">
                  <i class="fa fa-external-link"></i> Abrir Menú Digital
                </a>
                <?php else: ?>
                <button type="button" class="btn btn-sm btn-primary" disabled style="font-weight: 600; opacity: 0.5; cursor: not-allowed;" title="Permiso de creación requerido para abrir el menú digital">
                  <i class="fa fa-external-link"></i> Abrir Menú Digital
                </button>
                <?php endif; ?>
              </div>
            </div>

            <div class="box-body" style="padding: 15px;">
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover tablaMenuDigital display nowrap" style="width: 100%;">
                  <thead style="background: #f1f5f9; color: #334155;">
                    <tr>
                      <th style="width: 50px;" class="text-center">Imagen</th>
                      <th style="width: 90px;">Código</th>
                      <th>Producto</th>
                      <th>Categoría</th>
                      <th class="text-right" style="width: 100px;">Precio</th>
                      <th style="min-width: 220px;">Características / Ingredientes</th>
                      <th class="text-center" style="width: 140px;">Mostrar en Menú</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($productos)): ?>
                      <?php foreach ($productos as $item): 
                        $estaEnMenu = (!empty($item["en_menu_digital"]) && intval($item["en_menu_digital"]) === 1);
                        $caracteristicas = trim($item["caracteristicas_menu"] ?? "");
                        $foto = (!empty($item["imagen"]) && file_exists($item["imagen"])) ? $item["imagen"] : "vistas/img/productos/default/anonymous.png";
                      ?>
                        <tr data-id="<?= $item['id'] ?>">
                          
                          <!-- Imagen -->
                          <td class="text-center">
                            <img src="<?= $foto ?>" class="img-thumbnail" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                          </td>

                          <!-- Código -->
                          <td style="font-family: monospace; font-weight: 600; color: #64748b; vertical-align: middle;">
                            <?= htmlspecialchars($item["codigo"]) ?>
                          </td>

                          <!-- Nombre -->
                          <td style="vertical-align: middle;">
                            <b style="color: #1e293b; font-size: 13px;"><?= htmlspecialchars($item["descripcion"]) ?></b>
                          </td>

                          <!-- Categoría -->
                          <td style="vertical-align: middle;">
                            <span class="label label-info" style="font-size: 11px;"><?= htmlspecialchars($item["categoria"] ?? "General") ?></span>
                          </td>

                          <!-- Precio -->
                          <td class="text-right" style="font-weight: 700; color: #00a65a; font-size: 13px; vertical-align: middle;">
                            $ <?= number_format($item["precio_venta"], 2, ',', '.') ?>
                          </td>

                          <!-- Características / Ingredientes (Editable si tiene permiso 'crear') -->
                          <td style="vertical-align: middle;">
                            <?php if ($puedeCrearMenu): ?>
                            <div contenteditable="true" 
                                 class="celda-caracteristicas" 
                                 tabindex="0" 
                                 data-id="<?= $item['id'] ?>"
                                 <?= empty($caracteristicas) ? 'data-placeholder="Escribe características o ingredientes..."' : '' ?>
                                 style="min-height: 32px; padding: 6px 10px; border-radius: 4px; border: 1px dashed #cbd5e1; background: #fffdf5; font-size: 12px; color: #334155; line-height: 1.4; outline: none; cursor: text; transition: all 0.2s ease;">
                              <?= htmlspecialchars($caracteristicas) ?>
                            </div>
                            <?php else: ?>
                            <div contenteditable="false" 
                                 class="celda-caracteristicas-disabled" 
                                 tabindex="-1" 
                                 data-id="<?= $item['id'] ?>"
                                 title="Permiso de creación requerido para editar características"
                                 style="min-height: 32px; padding: 6px 10px; border-radius: 4px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 12px; color: #64748b; line-height: 1.4; cursor: not-allowed; opacity: 0.85;">
                              <?= !empty($caracteristicas) ? htmlspecialchars($caracteristicas) : '<span class="text-muted" style="font-style: italic;">Sin características</span>' ?>
                            </div>
                            <?php endif; ?>
                          </td>

                          <!-- Switch Mostrar en Menú -->
                          <td class="text-center" style="vertical-align: middle;">
                            <label class="switch-menu" style="position: relative; display: inline-block; width: 48px; height: 24px; margin: 0;">
                              <input type="checkbox" class="toggleEstadoMenu" data-id="<?= $item['id'] ?>" <?= $estaEnMenu ? "checked" : "" ?> style="opacity: 0; width: 0; height: 0;">
                              <span class="slider-menu round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px;"></span>
                            </label>
                            <span class="label-estado-menu-<?= $item['id'] ?>" style="display: block; font-size: 10px; font-weight: 600; margin-top: 2px; color: <?= $estaEnMenu ? '#00a65a' : '#94a3b8' ?>;">
                              <?= $estaEnMenu ? "Visible" : "Oculto" ?>
                            </span>
                          </td>

                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>

        <!--=====================================
        PESTAÑA 2: ENLACE PÚBLICO & CÓDIGOS QR
        ======================================-->
        <?php if ($puedeCrearMenu): ?>
        <div class="tab-pane" id="tab_qr_menu">

          <div class="row">
            
            <!-- Columna Izquierda: Enlace Público y Configuración de Mesa -->
            <div class="col-md-7 col-sm-12">
              
              <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); margin-bottom: 20px;">
                <div class="box-header with-border" style="background: #f8fafc;">
                  <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-link text-primary"></i> Enlace Web de tu Menú Digital
                  </h3>
                </div>
                <div class="box-body" style="padding: 20px;">
                  <p style="color: #64748b; font-size: 13px;">
                    Comparte este enlace directamente en tu biografía de Instagram, mensajes de WhatsApp, Facebook o estados para que tus clientes puedan explorar tu catálogo y hacer pedidos desde cualquier lugar.
                  </p>

                  <div class="input-group" style="margin-bottom: 15px;">
                    <input type="text" id="inputUrlMenuPublico" class="form-control input-lg" value="<?= $urlMenuPublico ?>" readonly style="background: #f8fafc; font-weight: 600; font-size: 13px; color: #3c8dbc;">
                    <span class="input-group-btn">
                      <button class="btn btn-primary btn-lg" type="button" id="btnCopiarEnlaceMenu">
                        <i class="fa fa-copy"></i> Copiar Enlace
                      </button>
                    </span>
                  </div>

                  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?= $urlMenuPublico ?>" target="_blank" class="btn btn-success" style="font-weight: 600;">
                      <i class="fa fa-external-link"></i> Abrir Menú en Nueva Pestaña
                    </a>
                  </div>
                </div>
              </div>

              <!-- Generador de QR Específico para Mesas / Ubicaciones -->
              <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
                <div class="box-header with-border" style="background: #f8fafc;">
                  <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-table text-warning"></i> Personalizar Código QR por Mesa / Ubicación
                  </h3>
                </div>
                <div class="box-body" style="padding: 20px;">
                  <p style="color: #64748b; font-size: 13px;">
                    Si tienes un restaurante, cafetería o establecimiento con mesas o puestos de atención, puedes generar códigos QR independientes para cada mesa (ej: <b>Mesa 1</b>, <b>Mesa 2</b>, etc.). Al escanearlo, el menú identificará la mesa automáticamente en el pedido.
                  </p>

                  <div class="row">
                    <div class="col-sm-6">
                      <label style="font-size: 13px; font-weight: 600; color: #334155;">Número o Nombre de la Mesa:</label>
                      <input type="text" id="inputNumeroMesa" class="form-control" placeholder="Ej: Mesa 1, Terraza 3..." value="">
                    </div>
                    <div class="col-sm-6" style="display: flex; align-items: flex-end; padding-top: 5px;">
                      <button type="button" class="btn btn-warning btn-block" id="btnGenerarQRMesa" style="font-weight: 600; height: 34px;">
                        <i class="fa fa-qrcode"></i> Actualizar QR con esta Mesa
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- Columna Derecha: Tarjeta Visual del Código QR e Impresión -->
            <div class="col-md-5 col-sm-12">
              
              <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); text-align: center;">
                <div class="box-header with-border" style="background: #f8fafc;">
                  <h3 class="box-title" style="font-size: 15px; font-weight: 600; color: #1e293b;">
                    <i class="fa fa-qrcode text-primary"></i> Código QR para Escaneo
                  </h3>
                </div>
                
                <div class="box-body" style="padding: 25px 20px;" id="seccionImprimibleQR">
                  
                  <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; background: #fff; max-width: 320px; margin: 0 auto;">
                    <h4 style="font-weight: 800; color: #1e293b; margin-top: 0; margin-bottom: 3px;" id="tituloQRNegocio">
                      <?= htmlspecialchars($nombreNegocio) ?>
                    </h4>
                    <p style="font-size: 11px; font-weight: 700; color: #3c8dbc; margin-bottom: 5px;">
                      ¡ESCANEA Y HAZ TU PEDIDO!
                    </p>
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 15px;" id="subtituloQRMesa">
                      Explora nuestro menú digital y pide desde tu celular
                    </p>

                    <div id="contenedorCodigoQR" style="margin: 0 auto; display: flex; justify-content: center; align-items: center; min-height: 200px;">
                      <!-- Código QR renderizado localmente en base64 -->
                      <img id="imagenCodigoQR" src="<?= $qrSrcInicial ?>" alt="Código QR Menú Digital" style="width: 200px; height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); object-fit: contain; background: #fff;">
                    </div>

                    <p style="font-size: 11px; color: #94a3b8; margin-top: 15px; margin-bottom: 0;">
                      <i class="fa fa-camera"></i> Apunta la cámara de tu celular
                    </p>
                  </div>

                  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-default" id="btnDescargarQR" style="font-weight: 600;">
                      <i class="fa fa-download text-primary"></i> Descargar Imagen
                    </button>
                    <button type="button" class="btn btn-primary" id="btnImprimirQR" style="font-weight: 600;">
                      <i class="fa fa-print"></i> Imprimir Tarjeta
                    </button>
                  </div>

                </div>
              </div>

            </div>

          </div>

        </div>
        <?php endif; ?>

      </div>

    </div>

  </section>

</div>

<style>
/* Estilos para celdas editables inline */
.celda-caracteristicas:focus {
  border-color: #3c8dbc !important;
  background-color: #ffffff !important;
  box-shadow: 0 0 0 2px rgba(60, 141, 188, 0.2) !important;
}

.celda-caracteristicas:empty:before,
.celda-caracteristicas:empty:not(:focus):before {
  content: attr(data-placeholder);
  color: #94a3b8;
  font-style: italic;
}

/* Switch Toggle Styles */
.switch-menu input:checked + .slider-menu {
  background-color: #00a65a !important;
}
.switch-menu input:focus + .slider-menu {
  box-shadow: 0 0 1px #00a65a;
}
.switch-menu input:checked + .slider-menu:before {
  transform: translateX(24px);
}
.slider-menu:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}
</style>
