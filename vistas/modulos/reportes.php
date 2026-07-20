<style>
  .excelbtn {
    z-index: 9999;
  }

  .filtro-excel-container {
    padding: 15px;
    border-radius: 10px;
    background-color: #f9f9f9;
    margin-bottom: 15px;
  }

  .filtro-excel-container label {
    font-weight: 600;
    margin-top: 10px;
  }

  .filtro-excel-container select,
  .filtro-excel-container input[type="date"] {
    border-radius: 8px;
    margin-bottom: 10px;
  }

  /* Toast notification */
  .toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #00a65a;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideInRight 0.3s ease-out;
    font-size: 15px;
  }

  .toast-notification.toast-hide {
    animation: slideOutRight 0.3s ease-out;
  }

  @keyframes slideInRight {
    from {
      transform: translateX(400px);
      opacity: 0;
    }

    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }

    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }

  /* Colapsar secciones en móvil por defecto */
  @media (max-width: 767px) {
    .box.collapsed-box-mobile .box-body {
      display: none;
    }

    .box.collapsed-box-mobile .fa-minus:before {
      content: "\f067";
      /* Icono de plus */
    }

    .box:not(.collapsed-box-mobile) .fa-minus:before {
      content: "\f068";
      /* Icono de minus */
    }
  }

  @media (max-width: 767px) {
    .form-filtros-fe {
      flex-direction: column !important;
      align-items: stretch !important;
      width: 100% !important;
      gap: 12px !important;
    }
    .form-filtros-fe > div {
      width: 100% !important;
      margin-bottom: 0 !important;
    }
    .form-filtros-fe > div > div {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      width: 100% !important;
      gap: 10px !important;
    }
    .form-filtros-fe > div > div > span {
      min-width: 80px !important;
      text-align: left !important;
    }
    .form-filtros-fe > div > div > .input-group {
      flex: 1 !important;
      width: auto !important;
    }
    .form-filtros-fe > div .select2-container {
      width: 100% !important;
    }
    .form-filtros-fe > div > div > .input-group > #daterange-btn-reportes {
      flex: 1 !important;
      width: auto !important;
      text-align: left !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
    }
    .form-filtros-fe > div > .btn-group {
      width: 100% !important;
    }
  }
</style>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Reportes de ventas
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Reportes de ventas</li>
    </ol>

  </section>

  <section class="content">

    <!-- FILTRO MAESTRO DE SUCURSAL -->
    <?php 
    $configuracionGlobal = ControladorConfiguracion::ctrObtenerConfiguracion();
    $sucursalesActivas = !isset($configuracionGlobal["activar_sucursales"]) || $configuracionGlobal["activar_sucursales"] == 1;
    if ($sucursalesActivas && (stripos($_SESSION["perfil"], "Admin") !== false || $_SESSION["perfil"] == "_SystemMaster_")): 
    ?>
      <div class="box box-default">
        <div class="box-body" style="padding: 15px 25px;">
          <div class="row" style="display: flex; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="col-md-4 col-sm-6 col-xs-12">
              <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 14px; color: #555;"><i class="fa fa-building text-primary"></i> Filtrar por
                  Sucursal (Vista Global):</label>
                <select class="form-control select2" id="sucursalReporteMaestro" style="width: 100%;" autocomplete="off">
                  <option value="todos" <?php echo empty($_SESSION["id_bodega"]) ? "selected" : ""; ?>>Filtrar por Sucursal (Vista Global):</option>
                  <?php
                  $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                  foreach ($bodegas as $key => $value) {
                    $selected = (!empty($_SESSION["id_bodega"]) && $_SESSION["id_bodega"] == $value["id"]) ? "selected" : "";
                    echo '<option value="' . $value["id"] . '" ' . $selected . '>' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-md-8 col-sm-6 hidden-xs">
              <p class="text-muted" style="margin-top: 22px; font-style: italic;">
                <i class="fa fa-info-circle"></i> Seleccione una sucursal para actualizar automáticamente todos los
                análisis y gráficos de esta página.
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <input type="hidden" id="sucursalReporteMaestro" value="<?php echo !empty($_SESSION['id_bodega']) ? $_SESSION['id_bodega'] : 1; ?>">
    <?php endif; ?>


    <!-- SECCIÓN 1: ANÁLISIS DE VENTAS -->
    <div class="box box-info" id="seccion-analisis-ventas">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Análisis de Ventas</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <!-- Análisis de ventas -->
        <div id="contenedor-barras-formas-pago">
          <div class="col-12 col-md-12">
            <?php include "reportes/analisis-ventas1.php"; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- SECCIÓN 2: GRÁFICOS DE RENDIMIENTO -->
    <div class="box box-success" id="seccion-graficos-rendimiento">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Gráficos de Rendimiento</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <div id="contenedor-graficos-rendimiento">
          <?php
          // Definir idBodega para la carga inicial de los gráficos
          $idBodega = !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : "todos";
          ?>
          <div class="row">
            <div class="col-md-6 col-xs-12">
              <?php include "reportes/metodos-pago-mas-usados.php"; ?>
            </div>

            <div class="col-md-6 col-xs-12">
              <?php include "reportes/productos-mas-vendidos.php"; ?>
            </div>
            <div class="col-md-6 col-xs-12">
              <?php include "reportes/vendedores.php"; ?>
            </div>
            <div class="col-md-6 col-xs-12">
              <?php include "reportes/compradores.php"; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECCIÓN 3: REPORTE FINANCIERO -->
    <div class="box box-primary" id="seccion-estado-resultados">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-balance-scale"></i> Estado de Resultados</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <?php include "reportes/estado-resultados.php"; ?>
      </div>
    </div>

    <?php
    $configuracionReportes = ControladorConfiguracion::ctrObtenerConfiguracion();
    if (!isset($configuracionReportes["grafica_analisis_ordenes_activa"]) || $configuracionReportes["grafica_analisis_ordenes_activa"] == 1):
    ?>
    <!-- SECCIÓN 4: REPORTE DE ÓRDENES -->
    <div class="box box-warning" id="seccion-analisis-ordenes">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Análisis de Órdenes con IA</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <?php include "reportes/ordenes-reporte.php"; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php
    $configuracionReportesManuales = ControladorConfiguracion::ctrObtenerConfiguracion();
    if (!isset($configuracionReportesManuales["grafica_ordenes_manuales_activa"]) || $configuracionReportesManuales["grafica_ordenes_manuales_activa"] == 1):
    ?>
    <!-- SECCIÓN 4.1: REPORTE DE ÓRDENES DE VENTA -->
    <div class="box box-primary collapsed-box" id="seccion-analisis-ordenes-venta">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shopping-bag"></i> Análisis de Órdenes de Venta</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>

      <div class="box-body" style="display: none;">
        <?php include "reportes/ordenes-reporte-ventas.php"; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php
    $configuracionFE = ControladorConfiguracion::ctrObtenerConfiguracion();
    if (!isset($configuracionFE["facturacion_electronica_activa"]) || $configuracionFE["facturacion_electronica_activa"] == 1):
    ?>
    <!-- SECCIÓN 5: REPORTE DE FACTURACIÓN ELECTRÓNICA -->
    <div class="box box-danger collapsed-box" id="seccion-reportes-facturacion">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Reportes Facturación Electrónica</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>
      <div class="box-body" style="display: none;">
        <div class="row form-filtros-fe" style="display: flex; align-items: center; flex-wrap: wrap;">
          <!-- 1. Categoría -->
          <div class="col-md-2" style="margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Categoría:</b></span>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f4f4f4;"><i class="fa fa-filter"></i></span>
                <select class="form-control" id="seleccionarCategoriaReporte">
                  <option value="todos">Mostrar Todas</option>
                  <option value="facturas">Facturas Electrónicas</option>
                  <option value="nc">Notas Crédito</option>
                  <?php if (!isset($configuracionFE["documento_soporte_activo"]) || $configuracionFE["documento_soporte_activo"] == 1): ?>
                    <option value="ds">Documentos Soporte</option>
                    <option value="na">Notas de Ajuste DS</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- 2. Tercero (Cliente/Proveedor) -->
          <div class="col-md-3" style="margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Cliente:</b></span>
              <div class="input-group" style="width: 100%;">
                <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i
                    class="fa fa-users"></i></span>

                <div id="divClienteReporte" style="display: block; width: 100%;">
                  <select class="form-control select2" id="seleccionarClienteReporte" style="width: 100%;">
                    <option value="todos">Mostrar Todos</option>
                    <?php
                    $clientes = ControladorClientes::ctrMostrarClientes(null, null);
                    foreach ($clientes as $key => $value) {
                      echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                    }
                    ?>
                  </select>
                </div>

                <div id="divProveedorReporte" style="display: none; width: 100%;">
                  <select class="form-control select2" id="seleccionarProveedorReporte" style="width: 100%;">
                    <option value="todos">Mostrar Todos</option>
                    <?php
                    $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                    foreach ($proveedores as $key => $value) {
                      echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Usuario -->
          <div class="col-md-2" style="margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Usuario:</b></span>
              <div class="input-group" style="width: 100%;">
                <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i
                    class="fa fa-user"></i></span>
                <select class="form-control select2" id="seleccionarUsuarioReporte" style="width: 100%;">
                  <option value="todos">Mostrar Todos</option>
                  <?php
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                  foreach ($usuarios as $key => $value) {
                    if ($value['perfil'] === '_SystemMaster_' || $value['perfil'] === 'Visitante') continue;
                    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <!-- 4. Rango de fecha -->
          <div class="col-md-3" style="margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Fecha:</b></span>
              <div class="input-group" style="width: 100%;">
                <button type="button" class="btn btn-default" id="daterange-btn-reportes" style="width: 100%;">
                  <span><i class="fa fa-calendar"></i> Rango de fecha</span>
                  <i class="fa fa-caret-down"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- 5. Botones -->
          <div class="col-md-2" style="margin-bottom: 10px;">
            <div class="btn-group" style="width: 100%; display: flex;">
              <button type="button" class="btn btn-primary" id="btnFiltrarReportes" style="flex: 1; margin-right: 2px;"
                title="Buscar">
                <i class="fa fa-search"></i>
              </button>
              <button type="button" class="btn btn-default" id="btnLimpiarFiltrosReportes" title="Limpiar Filtros"
                style="flex: 1;">
                <i class="fa fa-refresh"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- WIDGETS DE RESUMEN (KPIs) -->
        <div class="row" id="kpi-reports" style="margin-top: 20px;">
          <div class="col-lg-3 col-xs-6">
            <div class="inner">
              <h3 id="widget-total-ventas">$0</h3>
              <p>Venta Neta (Facturado - NC)</p>
            </div>
            <div class="icon"><i class="ion ion-social-usd"></i></div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="inner">
              <h3 id="widget-total-iva">$0</h3>
              <p>Total IVA Recaudado</p>
            </div>
            <div class="icon"><i class="ion ion-pie-graph"></i></div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="inner">
              <h3 id="widget-total-ds">$0</h3>
              <p>Documentos Soporte</p>
            </div>
            <div class="icon"><i class="ion ion-ios-paper"></i></div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="inner">
              <h3 id="widget-total-docs">0</h3>
              <p>Docs. Electrónicos Totales</p>
            </div>
            <div class="icon"><i class="ion ion-ios-albums"></i></div>
          </div>
        </div>

        <!-- Fila para Tabla Detallada -->
        <div class="row" style="margin-top: 20px;">
          <div class="col-md-12">
            <div class="box box-default">
              <div class="box-header with-border">
                <h3 class="box-title">Listado Consolidado de Documentos</h3>
                <button type="button" class="btn btn-success pull-right" data-toggle="modal"
                  data-target="#modalDescargarExcelFacturacion">
                  <i class="fa fa-file-excel-o"></i> Descargar Reporte
                </button>
              </div>
              <div class="box-body" style="padding-left: 0; padding-right: 0;">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped dt-responsive tablaReporteFacturacion" width="100%">
                    <thead>
                      <tr>
                        <th style="width:10px">#</th>
                        <th>Tipo</th>
                        <th>Número</th>
                        <th>Cliente/Proveedor</th>
                        <th>Vendedor</th>
                        <th>Fecha</th>
                        <th>Monto Total</th>
                        <th>Estado</th>
                        <th>Ver</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </section>

</div>

<!-- Fix: permite que el daterangepicker dentro del modal no quede recortado -->
<style>
  #modalDescargarExcel .modal-dialog,
  #modalDescargarExcel .modal-content,
  #modalDescargarExcelFacturacion .modal-dialog,
  #modalDescargarExcelFacturacion .modal-content {
    overflow: visible;
  }
  #modalDescargarExcel .modal-body,
  #modalDescargarExcelFacturacion .modal-body {
    overflow: visible;
  }
  /* Asegurar z-index del picker por encima del backdrop del modal */
  .daterangepicker {
    z-index: 1060 !important;
  }
</style>

<!-- Modal para descargar Excel con filtro de fechas -->
<div class="modal fade" id="modalDescargarExcel" tabindex="-1" role="dialog" aria-labelledby="modalDescargarExcelLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalDescargarExcelLabel"><i class="fa fa-file-excel-o"></i> Descargar Reporte en
          Excel</h4>
      </div>
      <div class="modal-body">
        <div class="filtro-excel-container">
          <div class="form-group">
            <label for="filtro-usuario-excel">Usuario:</label>
            <select id="filtro-usuario-excel" class="form-control">
              <option value="">Mostrar Todos</option>
              <?php
              $item = null;
              $valor = null;
              $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
              foreach ($usuarios as $key => $value) {
                if ($value['perfil'] === '_SystemMaster_' || $value['perfil'] === 'Visitante') continue;
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="filtro-cliente-excel">Cliente:</label>
            <select id="filtro-cliente-excel" class="form-control select2-modal" style="width: 100%;">
              <option value="todos">Mostrar Todos</option>
              <?php
              $clientesA = ControladorClientes::ctrMostrarClientes(null, null);
              foreach ($clientesA as $key => $value) {
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Fecha:</label>
            <button type="button" class="btn btn-default" id="daterange-btn-excel" style="width:100%; display:flex; align-items:center; justify-content:space-between; border:1px solid #d2d6de; border-radius:4px; padding:6px 12px;">
              <span><i class="fa fa-calendar"></i> Mostrar Todas</span>
              <i class="fa fa-caret-down"></i>
            </button>
            <input type="hidden" id="excel-fecha-inicio" value="">
            <input type="hidden" id="excel-fecha-fin" value="">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <?php if (puedeAccion('reporte_ventas', 'imprimir')): ?>
          <a id="btn-descargar-excel" href="vistas/modulos/descargar-reporte.php?reporte=reporte" class="btn btn-success">
            <i class="fa fa-download"></i> Descargar
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>



<!-- Modal para descargar Excel Facturación Electrónica con filtro de fechas -->
<div class="modal fade" id="modalDescargarExcelFacturacion" tabindex="-1" role="dialog"
  aria-labelledby="modalDescargarExcelFacturacionLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalDescargarExcelFacturacionLabel"><i class="fa fa-file-excel-o"></i> Descargar
          Reporte en Excel (Facturación)</h4>
      </div>
      <div class="modal-body">
        <div class="filtro-excel-container">
          <div class="form-group">
            <label for="filtro-usuario-excel-fact">Usuario:</label>
            <select id="filtro-usuario-excel-fact" class="form-control">
              <option value="todos">Mostrar Todos</option>
              <?php
              foreach ($usuarios as $key => $value) {
                if ($value['perfil'] === '_SystemMaster_') continue;
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="filtro-categoria-excel-fact">Tipo de Documento</label>
            <select id="filtro-categoria-excel-fact" class="form-control">
              <option value="todos">Mostrar Todas</option>
              <option value="facturas">Facturas Electrónicas</option>
              <option value="nc">Notas Crédito</option>
              <option value="ds">Documentos Soporte</option>
              <option value="na">Notas de Ajuste DS</option>
            </select>
          </div>

          <div class="form-group" id="divFiltroClienteModal">
            <label for="filtro-cliente-excel-fact">Cliente:</label>
            <select id="filtro-cliente-excel-fact" class="form-control select2-modal" style="width: 100%;">
              <option value="todos">Mostrar Todos</option>
              <?php
              foreach ($clientes as $key => $value) {
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group" id="divFiltroProveedorModal" style="display: none;">
            <label for="filtro-proveedor-excel-fact">Proveedor:</label>
            <select id="filtro-proveedor-excel-fact" class="form-control select2-modal" style="width: 100%;">
              <option value="todos">Mostrar Todos</option>
              <?php
              foreach ($proveedores as $key => $value) {
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Fecha:</label>
            <button type="button" class="btn btn-default" id="daterange-btn-excel-fact" style="width:100%; display:flex; align-items:center; justify-content:space-between; border:1px solid #d2d6de; border-radius:4px; padding:6px 12px;">
              <span><i class="fa fa-calendar"></i> Mostrar Todas</span>
              <i class="fa fa-caret-down"></i>
            </button>
            <input type="hidden" id="excel-fact-fecha-inicio" value="">
            <input type="hidden" id="excel-fact-fecha-fin" value="">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <?php if (puedeAccion('reporte_ventas', 'imprimir')): ?>
          <a id="btn-descargar-excel-fact" href="#" class="btn btn-success">
            <i class="fa fa-download"></i> Descargar
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  // ---- BOTON EXCEL DIRECTO CON FILTROS ----
  $(document).on('click', '#btn-descargar-excel-directo', function (e) {
    e.preventDefault();

    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte.php?reporte=reporte`;

    // Filtros de la seccion Análisis de Ventas
    const tipo = document.getElementById('av-tipo') ? document.getElementById('av-tipo').value : 'todo';
    const fechaInicio = document.getElementById('av-fecha-inicio') ? document.getElementById('av-fecha-inicio').value : '';
    const fechaFin = document.getElementById('av-fecha-fin') ? document.getElementById('av-fecha-fin').value : '';
    const idVendedor = document.getElementById('filtro-vendedor') ? document.getElementById('filtro-vendedor').value : '';
    const idCliente = document.getElementById('filtro-cliente') ? document.getElementById('filtro-cliente').value : '';
    const idProducto = document.getElementById('filtro-producto') ? document.getElementById('filtro-producto').value : '';
    const metodoPago = document.getElementById('filtro-metodo-pago') ? document.getElementById('filtro-metodo-pago').value : '';

    // Obtener sucursal/bodega si existe el filtro maestro
    const sucursalMaestra = document.getElementById('sucursalReporteMaestro');
    const idBodega = sucursalMaestra ? sucursalMaestra.value : '';

    if (tipo) {
      url += `&tipo=${tipo}`;
    }
    if (fechaInicio) {
      url += `&fechaInicial=${fechaInicio}`;
    }
    if (fechaFin) {
      url += `&fechaFinal=${fechaFin}`;
    }
    if (idVendedor) {
      url += `&vendedor=${idVendedor}`;
    }
    if (idCliente) {
      url += `&cliente=${idCliente}`;
    }
    if (idProducto) {
      url += `&producto=${idProducto}`;
    }
    if (metodoPago) {
      url += `&metodoPago=${metodoPago}`;
    }
    if (idBodega && idBodega !== 'todos') {
      url += `&idBodega=${idBodega}`;
    }

    // Mostrar toast
    mostrarToast('¡Descarga iniciada! El archivo Excel se está descargando...');

    // Iniciar descarga
    window.location.href = url;
  });

  // ---- BOTON PDF DIRECTO CON FILTROS ----
  $(document).on('click', '#btn-descargar-pdf-directo', function (e) {
    e.preventDefault();

    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte-pdf.php?reporte=reporte`;

    // Filtros de la seccion Análisis de Ventas
    const tipo = document.getElementById('av-tipo') ? document.getElementById('av-tipo').value : 'todo';
    const fechaInicio = document.getElementById('av-fecha-inicio') ? document.getElementById('av-fecha-inicio').value : '';
    const fechaFin = document.getElementById('av-fecha-fin') ? document.getElementById('av-fecha-fin').value : '';
    const idVendedor = document.getElementById('filtro-vendedor') ? document.getElementById('filtro-vendedor').value : '';
    const idCliente = document.getElementById('filtro-cliente') ? document.getElementById('filtro-cliente').value : '';
    const idProducto = document.getElementById('filtro-producto') ? document.getElementById('filtro-producto').value : '';
    const metodoPago = document.getElementById('filtro-metodo-pago') ? document.getElementById('filtro-metodo-pago').value : '';

    // Obtener sucursal/bodega si existe el filtro maestro
    const sucursalMaestra = document.getElementById('sucursalReporteMaestro');
    const idBodega = sucursalMaestra ? sucursalMaestra.value : '';

    if (tipo) {
      url += `&tipo=${tipo}`;
    }
    if (fechaInicio) {
      url += `&fechaInicial=${fechaInicio}`;
    }
    if (fechaFin) {
      url += `&fechaFinal=${fechaFin}`;
    }
    if (idVendedor) {
      url += `&vendedor=${idVendedor}`;
    }
    if (idCliente) {
      url += `&cliente=${idCliente}`;
    }
    if (idProducto) {
      url += `&producto=${idProducto}`;
    }
    if (metodoPago) {
      url += `&metodoPago=${metodoPago}`;
    }
    if (idBodega && idBodega !== 'todos') {
      url += `&idBodega=${idBodega}`;
    }

    // Mostrar toast
    mostrarToast('¡Generación iniciada! El archivo PDF se está generando...');

    // Iniciar descarga en pestaña nueva
    window.open(url, '_blank');
  });

  // ---- MODAL EXCEL: listeners se registran en shown.bs.modal (despues del re-init de select2) ----
  // La funcion actualizarEnlaceExcel se define aqui; los listeners se conectan en shown.bs.modal


  function actualizarEnlaceExcel() {
    const btnDescargar = document.getElementById('btn-descargar-excel');
    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte.php?reporte=reporte`;

    // Fechas desde los hidden inputs (gestionados por daterangepicker)
    const fechaInicio = document.getElementById('excel-fecha-inicio').value;
    const fechaFin    = document.getElementById('excel-fecha-fin').value;

    if (fechaInicio && fechaFin) {
      url += `&fechaInicial=${fechaInicio}&fechaFinal=${fechaFin}`;
    }

    const usuario = document.getElementById('filtro-usuario-excel').value;
    if (usuario) {
      url += `&usuario=${usuario}`;
    }

    const cliente = document.getElementById('filtro-cliente-excel').value;
    if (cliente && cliente !== "todos") {
      url += `&cliente=${cliente}`;
    }

    btnDescargar.href = url;
  }



  // --- LOGICA MODAL FACTURACION ELECTRONICA ---

  // También queremos que lea 'categoria' y 'tercero' actuales
  $('#seleccionarCategoriaReporte, #seleccionarClienteReporte, #seleccionarProveedorReporte').on('change', function () {
    actualizarEnlaceExcelFacturacion();
  });

  // Al abrir la ventana modal actualizar el enlace
  $('#modalDescargarExcelFacturacion').on('show.bs.modal', function () {
    actualizarEnlaceExcelFacturacion();
  });

  function actualizarEnlaceExcelFacturacion() {
    const btnDescargar = document.getElementById('btn-descargar-excel-fact');

    // Obtener los otros filtros (categoría desde el modal)
    var cat = document.getElementById('filtro-categoria-excel-fact').value;
    var tercero = "todos";

    // Obtener el tercero desde el modal
    if (cat == "ds" || cat == "na") {
      tercero = document.getElementById('filtro-proveedor-excel-fact').value;
    } else {
      tercero = document.getElementById('filtro-cliente-excel-fact').value;
    }

    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte-facturacion.php?reporte=reporte_facturacion&categoria=${cat}&tercero=${tercero}`;

    // Fechas desde los hidden inputs (gestionados por daterangepicker)
    const fechaInicio = document.getElementById('excel-fact-fecha-inicio').value;
    const fechaFin    = document.getElementById('excel-fact-fecha-fin').value;

    if (fechaInicio && fechaFin) {
      url += `&fechaInicial=${fechaInicio}&fechaFinal=${fechaFin}`;
    }

    const usuario = document.getElementById('filtro-usuario-excel-fact').value;
    if (usuario) {
      url += `&idUsuario=${usuario}`;
    }

    // Incluir sucursal (idBodega)
    const sucursalMaestra = document.getElementById('sucursalReporteMaestro');
    const idBodega = sucursalMaestra ? sucursalMaestra.value : 'todos';
    url += `&idBodega=${idBodega}`;

    if (btnDescargar) {
      btnDescargar.href = url;
    }
  }

  // --- FIN LOGICA MODAL FACTURACION ELECTRONICA ---

  // Función para mostrar toast notification
  function mostrarToast(mensaje) {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<i class="fa fa-check-circle" style="font-size: 20px;"></i> <span>' + mensaje + '</span>';

    // Agregar al body
    document.body.appendChild(toast);

    // Remover después de 3 segundos
    setTimeout(function () {
      toast.classList.add('toast-hide');
      setTimeout(function () {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }

  // Mostrar toast ANTES de que se cierre el modal
  $('#btn-descargar-excel').on('click', function (e) {
    // Mostrar toast inmediatamente
    mostrarToast('¡Descarga iniciada! El archivo Excel se está descargando...');
  });

  $('#btn-descargar-excel-fact').on('click', function (e) {
    mostrarToast('¡Descarga iniciada! El archivo Excel de Facturación se está descargando...');
    setTimeout(function () {
      $('#modalDescargarExcelFacturacion').modal('hide');
    }, 1000);
  });

  // Limpiar completamente cuando el modal se cierra
  $('#modalDescargarExcel, #modalDescargarExcelFacturacion').on('hidden.bs.modal', function () {
    setTimeout(function () {
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open');
      $('body').css('padding-right', '');
      $('body').css('overflow', '');
    }, 50);
  });

  // Colapsar secciones al cargar la página
  $(document).ready(function () {
    if ($.fn.select2) {
      $(".select2").select2({
        width: '100%'
      });
      $("#seleccionarUsuarioReporte").select2({
        width: '100%'
      });

      // Forzar reset de sucursal al cargar para que siempre inicie en la sucursal por defecto
      if ($("#sucursalReporteMaestro").is("select")) {
        var defaultSucursal = "<?php echo !empty($_SESSION['id_bodega']) ? $_SESSION['id_bodega'] : 'todos'; ?>";
        $("#sucursalReporteMaestro").val(defaultSucursal).trigger("change.select2");
        // Limpiar cualquier rastro previo en localStorage
        localStorage.removeItem("sucursalReporteMaestro");
      }
    }

    // ============================================================
    // FIX: Inicializar Select2 DENTRO del modal al abrirse
    // Select2 no puede calcular el ancho/posición cuando el modal
    // está oculto (display:none), por eso hay que inicializarlo
    // en el evento 'shown.bs.modal' con dropdownParent al modal.
    // ============================================================
    // Al abrir el modal de reporte de ventas: inicializar Select2 con dropdownParent
    // (destroy primero para evitar doble init en aperturas repetidas)
    $('#modalDescargarExcel').on('shown.bs.modal', function () {
      // 1. Re-inicializar select2 del cliente (con dropdownParent para que se muestre dentro del modal)
      if ($.fn.select2) {
        var $sel = $('#filtro-cliente-excel');
        if ($sel.hasClass('select2-hidden-accessible')) {
          $sel.select2('destroy');
        }
        $sel.select2({
          width: '100%',
          dropdownParent: $('#modalDescargarExcel'),
          placeholder: 'Mostrar Todos'
        });
        // Re-conectar listener de select2 (se pierde al hacer destroy+reinit)
        $sel.off('select2:select select2:unselect').on('select2:select select2:unselect', actualizarEnlaceExcel);
      }

      // 2. Re-conectar listeners de usuario (jQuery .on para que sobreviva a re-renders)
      $('#filtro-usuario-excel').off('change.excel').on('change.excel', actualizarEnlaceExcel);

      // 3. Inicializar daterangepicker del modal si aun no fue inicializado
      if (typeof $.fn.daterangepicker !== 'undefined' && !$('#daterange-btn-excel').data('daterangepicker')) {
        $('#daterange-btn-excel').daterangepicker({
          parentEl: '#modalDescargarExcel',
          opens: 'left',
          drops: 'down',
          ranges: {
            'Todas las fechas': [moment('2000-01-01'), moment()],
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
          },
          startDate: moment().subtract(29, 'days'),
          endDate: moment(),
          autoUpdateInput: false,
          locale: { cancelLabel: 'Limpiar' }
        }, function (start, end) {
          $('#daterange-btn-excel span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
          $('#excel-fecha-inicio').val(start.format('YYYY-MM-DD'));
          $('#excel-fecha-fin').val(end.format('YYYY-MM-DD'));
          actualizarEnlaceExcel();
        });

        $('#daterange-btn-excel').on('cancel.daterangepicker', function () {
          $(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar Todas');
          $('#excel-fecha-inicio').val('');
          $('#excel-fecha-fin').val('');
          actualizarEnlaceExcel();
        });
      }

      // 4. Actualizar el enlace con los valores actuales
      actualizarEnlaceExcel();
    });

    // Al abrir el modal de facturación electrónica: inicializar Select2 con dropdownParent
    $('#modalDescargarExcelFacturacion').on('shown.bs.modal', function () {
      if ($.fn.select2) {
        var $selCli = $('#filtro-cliente-excel-fact');
        if ($selCli.hasClass('select2-hidden-accessible')) {
          $selCli.select2('destroy');
        }
        $selCli.select2({
          width: '100%',
          dropdownParent: $('#modalDescargarExcelFacturacion'),
          placeholder: 'Mostrar Todos'
        });
        // Conectar listener de select2 cliente
        $selCli.off('select2:select select2:unselect').on('select2:select select2:unselect', actualizarEnlaceExcelFacturacion);

        var $selProv = $('#filtro-proveedor-excel-fact');
        if ($selProv.hasClass('select2-hidden-accessible')) {
          $selProv.select2('destroy');
        }
        $selProv.select2({
          width: '100%',
          dropdownParent: $('#modalDescargarExcelFacturacion'),
          placeholder: 'Mostrar Todos'
        });
        // Conectar listener de select2 proveedor
        $selProv.off('select2:select select2:unselect').on('select2:select select2:unselect', actualizarEnlaceExcelFacturacion);
      }

      // Re-conectar listeners del usuario y categoría
      $('#filtro-usuario-excel-fact').off('change.excelfact').on('change.excelfact', actualizarEnlaceExcelFacturacion);
      $('#filtro-categoria-excel-fact').off('change.excelfact').on('change.excelfact', function () {
        const cat = this.value;
        if (cat == "ds" || cat == "na") {
          document.getElementById('divFiltroClienteModal').style.display = 'none';
          document.getElementById('divFiltroProveedorModal').style.display = 'block';
        } else {
          document.getElementById('divFiltroProveedorModal').style.display = 'none';
          document.getElementById('divFiltroClienteModal').style.display = 'block';
        }
        actualizarEnlaceExcelFacturacion();
      });

      // Inicializar daterangepicker para el modal de facturación electrónica
      if (typeof $.fn.daterangepicker !== 'undefined' && !$('#daterange-btn-excel-fact').data('daterangepicker')) {
        $('#daterange-btn-excel-fact').daterangepicker({
          parentEl: '#modalDescargarExcelFacturacion',
          opens: 'left',
          drops: 'down',
          ranges: {
            'Todas las fechas': [moment('2000-01-01'), moment()],
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
          },
          startDate: moment().subtract(29, 'days'),
          endDate: moment(),
          autoUpdateInput: false,
          locale: { cancelLabel: 'Limpiar' }
        }, function (start, end) {
          $('#daterange-btn-excel-fact span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
          $('#excel-fact-fecha-inicio').val(start.format('YYYY-MM-DD'));
          $('#excel-fact-fecha-fin').val(end.format('YYYY-MM-DD'));
          actualizarEnlaceExcelFacturacion();
        });

        $('#daterange-btn-excel-fact').on('cancel.daterangepicker', function () {
          $(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar Todas');
          $('#excel-fact-fecha-inicio').val('');
          $('#excel-fact-fecha-fin').val('');
          actualizarEnlaceExcelFacturacion();
        });
      }

      actualizarEnlaceExcelFacturacion();
    });



    // Al cambiar la sucursal maestra, disparar todos los formularios de filtros
    $('#sucursalReporteMaestro').on('change', function () {
      var idBodega = $(this).val();

      // Disparar los formularios de forma que sea capturado por sus respectivos listeners
      // Usamos dispatchEvent para asegurar que los listeners nativos (addEventListener) funcionen

      // 1. Análisis de Ventas (Sección 1)
      var formVentas = document.getElementById('filtro-fechas');
      if (formVentas) formVentas.dispatchEvent(new Event('submit', { cancelable: true }));

      // 2. Estado de Resultados (Sección 3)
      var formFinan = document.getElementById('filtro-financiero');
      if (formFinan) formFinan.dispatchEvent(new Event('submit', { cancelable: true }));

      // 3. Análisis de Órdenes (Sección 4)
      var formOrden = document.getElementById('filtroOrdenesForm');
      if (formOrden) formOrden.dispatchEvent(new Event('submit', { cancelable: true }));

      // 4. Reporte Facturación Electrónica (Sección 5)
      if ($('#btnFiltrarReportes').length) $('#btnFiltrarReportes').click();

      // 6. Gráficos de Rendimiento (Sección 2)
      cargarGraficosRendimiento(idBodega);
    });

    function cargarGraficosRendimiento(idBodega) {
      // Obtener fechas actuales si existen en la URL o scope
      const urlParams = new URLSearchParams(window.location.search);
      const fechaInicial = urlParams.get('fechaInicial') || "";
      const fechaFinal = urlParams.get('fechaFinal') || "";

      $("#contenedor-graficos-rendimiento").html('<div class="text-center" style="padding:50px;"><i class="fa fa-refresh fa-spin fa-3x"></i><br>Actualizando gráficos...</div>');

      $.ajax({
        url: "vistas/modulos/reportes/obtener-graficos-rendimiento.php",
        method: "POST",
        data: {
          idBodega: idBodega,
          fechaInicial: fechaInicial,
          fechaFinal: fechaFinal
        },
        success: function (respuesta) {
          $("#contenedor-graficos-rendimiento").html(respuesta);
        }
      });
    }

    // En móvil: colapsar todas las secciones
    if ($(window).width() < 768) {
      $('.box').addClass('collapsed-box-mobile');
    } else {
      // En desktop: colapsar solo las secciones 2, 3, 4 y 5 (dejar la 1ra expandida)
      $('#seccion-graficos-rendimiento').addClass('collapsed-box');
      $('#seccion-estado-resultados').addClass('collapsed-box');
      $('#seccion-analisis-ordenes').addClass('collapsed-box');
      $('#seccion-reportes-facturacion').addClass('collapsed-box');
    }

    // Manejar el clic en el botón de colapso
    $('[data-widget="collapse"]').on('click', function () {
      var box = $(this).closest('.box');

      if ($(window).width() < 768) {
        // En móvil usar la clase mobile
        box.toggleClass('collapsed-box-mobile');
      }
      // En desktop AdminLTE maneja el colapso automáticamente con collapsed-box
    });

    // Re-evaluar al cambiar tamaño de ventana
    $(window).resize(function () {
      if ($(window).width() >= 768) {
        // En desktop, remover clase móvil y aplicar collapsed-box a secciones 2, 3, 4
        $('.box').removeClass('collapsed-box-mobile');

        // Colapsar secciones 2, 3, 4 solo si no están ya expandidas manualmente
        if (!$('#seccion-graficos-rendimiento').hasClass('manually-expanded')) {
          $('#seccion-graficos-rendimiento').addClass('collapsed-box');
        }
        if (!$('#seccion-estado-resultados').hasClass('manually-expanded')) {
          $('#seccion-estado-resultados').addClass('collapsed-box');
        }
        if (!$('#seccion-analisis-ordenes').hasClass('manually-expanded')) {
          $('#seccion-analisis-ordenes').addClass('collapsed-box');
        }
        if (!$('#seccion-reportes-facturacion').hasClass('manually-expanded')) {
          $('#seccion-reportes-facturacion').addClass('collapsed-box');
        }
      } else {
        // En móvil, remover collapsed-box y aplicar collapsed-box-mobile
        $('.box').removeClass('collapsed-box');
        $('.box').addClass('collapsed-box-mobile');
      }
    });
  });
</script>
<script src="vistas/js/reportes-facturacion.js?v=<?php echo time(); ?>"></script>