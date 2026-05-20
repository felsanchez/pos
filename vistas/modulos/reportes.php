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
    <?php if (stripos($_SESSION["perfil"], "Admin") !== false): ?>
      <div class="box box-default">
        <div class="box-body" style="padding: 15px 25px;">
          <div class="row" style="display: flex; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="col-md-4 col-sm-6 col-xs-12">
              <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 14px; color: #555;"><i class="fa fa-building text-primary"></i> Filtrar por
                  Sucursal (Vista Global):</label>
                <select class="form-control select2" id="sucursalReporteMaestro" style="width: 100%;" autocomplete="off">
                  <option value="todos">Filtrar por Sucursal (Vista Global):</option>
                  <?php
                  $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                  foreach ($bodegas as $key => $value) {
                    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
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
      <input type="hidden" id="sucursalReporteMaestro" value="<?php echo $_SESSION['id_bodega']; ?>">
    <?php endif; ?>


    <!-- SECCIÓN 1: ANÁLISIS DE VENTAS -->
    <div class="box box-info" id="seccion-analisis-ventas">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Análisis de Ventas</h3>
        <div class="box-tools pull-right">
          <?php if (puedeAccion('reporte_ventas', 'imprimir')): ?>
            <button class="btn btn-success btn-sm" style="margin-right: 5px;" data-toggle="modal"
              data-target="#modalDescargarExcel">
              <i class="fa fa-file-excel-o"></i> Descargar Excel
            </button>
            <button class="btn btn-danger btn-sm" style="margin-right: 5px;" data-toggle="modal"
              data-target="#modalDescargarPdf">
              <i class="fa fa-file-pdf-o"></i> Descargar PDF
            </button>
          <?php endif; ?>
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
          $idBodega = (stripos($_SESSION["perfil"], "Admin") !== false) ? "todos" : $_SESSION["id_bodega"];
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

    <!-- SECCIÓN 4: REPORTE DE ÓRDENES -->
    <div class="box box-warning" id="seccion-analisis-ordenes">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Análisis de Órdenes</h3>
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

    <!-- SECCIÓN 5: ESTADO DE RESULTADOS (FACTURACIÓN ELECTRÓNICA) -->
    <div class="box box-info collapsed-box" id="seccion-estado-resultados-facturacion">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-balance-scale"></i> Estado de Resultados (Facturación Electrónica)</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-plus"></i>
          </button>
        </div>
      </div>

      <div class="box-body" style="display: none;">
        <?php include "reportes/estado-resultados-facturacion.php"; ?>
      </div>
    </div>

    <!-- SECCIÓN 6: REPORTE DE FACTURACIÓN ELECTRÓNICA -->
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
        <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
          <!-- 1. Categoría -->
          <div class="col-md-2" style="margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Categoría:</b></span>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f4f4f4;"><i class="fa fa-filter"></i></span>
                <select class="form-control" id="seleccionarCategoriaReporte">
                  <option value="todos">Mostrar Todas</option>
                  <option value="facturas">Facturas Electrónicas</option>
                  <option value="nc">Notas Crédito</option>
                  <option value="ds">Documentos Soporte</option>
                  <option value="na">Notas de Ajuste DS</option>
                </select>
              </div>
            </div>
          </div>

          <!-- 2. Tercero (Cliente/Proveedor) -->
          <div class="col-md-3" style="margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Cliente/Proveedor:</b></span>
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
              <span class="hidden-xs"><b>Usuario:</b></span>
              <div class="input-group" style="width: 100%;">
                <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i
                    class="fa fa-user"></i></span>
                <select class="form-control select2" id="seleccionarUsuarioReporte" style="width: 100%;">
                  <option value="todos">Mostrar Todos</option>
                  <?php
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                  foreach ($usuarios as $key => $value) {
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
              <span class="hidden-xs"><b>Fecha:</b></span>
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

  </section>

</div>

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
            <label for="tipo-fecha-excel">Fecha:</label>
            <select id="tipo-fecha-excel" class="form-control">
              <option value="todo">Mostrar Todas</option>
              <option value="hoy">Hoy</option>
              <option value="ayer">Ayer</option>
              <option value="mes">Mes actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>

          <div id="campo-desde-excel" class="form-group" style="display:none;">
            <label for="fecha-desde-excel">Desde</label>
            <input type="date" id="fecha-desde-excel" class="form-control">
          </div>

          <div id="campo-hasta-excel" class="form-group" style="display:none;">
            <label for="fecha-hasta-excel">Hasta</label>
            <input type="date" id="fecha-hasta-excel" class="form-control">
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

<!-- Modal para descargar PDF con filtro de fechas -->
<div class="modal fade" id="modalDescargarPdf" tabindex="-1" role="dialog" aria-labelledby="modalDescargarPdfLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: #c0392b; color: #fff; border-radius: 4px 4px 0 0;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1;"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalDescargarPdfLabel"><i class="fa fa-file-pdf-o"></i> Descargar Reporte en PDF
        </h4>
      </div>
      <div class="modal-body">
        <div class="filtro-excel-container">
          <div class="form-group">
            <label for="filtro-usuario-pdf">Usuario:</label>
            <select id="filtro-usuario-pdf" class="form-control">
              <option value="">Mostrar Todos</option>
              <?php
              foreach ($usuarios as $key => $value) {
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="filtro-cliente-pdf">Cliente:</label>
            <select id="filtro-cliente-pdf" class="form-control select2-modal" style="width: 100%;">
              <option value="todos">Mostrar Todos</option>
              <?php
              foreach ($clientesA as $key => $value) {
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="tipo-fecha-pdf">Fecha:</label>
            <select id="tipo-fecha-pdf" class="form-control">
              <option value="todo">Mostrar Todas</option>
              <option value="hoy">Hoy</option>
              <option value="ayer">Ayer</option>
              <option value="mes">Mes actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>

          <div id="campo-desde-pdf" class="form-group" style="display:none;">
            <label for="fecha-desde-pdf">Desde</label>
            <input type="date" id="fecha-desde-pdf" class="form-control">
          </div>

          <div id="campo-hasta-pdf" class="form-group" style="display:none;">
            <label for="fecha-hasta-pdf">Hasta</label>
            <input type="date" id="fecha-hasta-pdf" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <?php if (puedeAccion('reporte_ventas', 'imprimir')): ?>
          <a id="btn-descargar-pdf" href="vistas/modulos/descargar-reporte-pdf.php?reporte=reporte" class="btn btn-danger"
            target="_blank">
            <i class="fa fa-download"></i> Descargar PDF
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
            <label for="tipo-fecha-excel-fact">Fecha:</label>
            <select id="tipo-fecha-excel-fact" class="form-control">
              <option value="todo">Mostrar Todos</option>
              <option value="hoy">Hoy</option>
              <option value="ayer">Ayer</option>
              <option value="mes">Mes actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>

          <div id="campo-desde-excel-fact" class="form-group" style="display:none;">
            <label for="fecha-desde-excel-fact">Desde</label>
            <input type="date" id="fecha-desde-excel-fact" class="form-control">
          </div>

          <div id="campo-hasta-excel-fact" class="form-group" style="display:none;">
            <label for="fecha-hasta-excel-fact">Hasta</label>
            <input type="date" id="fecha-hasta-excel-fact" class="form-control">
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
  // Mostrar/ocultar campos de fecha personalizada
  document.getElementById('tipo-fecha-excel').addEventListener('change', function () {
    const tipo = this.value;
    const campoDesde = document.getElementById('campo-desde-excel');
    const campoHasta = document.getElementById('campo-hasta-excel');

    if (tipo === 'personalizado') {
      campoDesde.style.display = 'block';
      campoHasta.style.display = 'block';
    } else {
      campoDesde.style.display = 'none';
      campoHasta.style.display = 'none';
    }

    actualizarEnlaceExcel();
  });

  // Actualizar enlace cuando cambian las fechas
  document.getElementById('fecha-desde-excel').addEventListener('change', actualizarEnlaceExcel);
  document.getElementById('fecha-hasta-excel').addEventListener('change', actualizarEnlaceExcel);
  document.getElementById('filtro-usuario-excel').addEventListener('change', actualizarEnlaceExcel);
  document.getElementById('filtro-cliente-excel').addEventListener('change', actualizarEnlaceExcel);

  function actualizarEnlaceExcel() {
    const tipo = document.getElementById('tipo-fecha-excel').value;
    const btnDescargar = document.getElementById('btn-descargar-excel');
    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte.php?reporte=reporte`;

    let fechaInicial, fechaFinal;
    const hoy = new Date();

    switch (tipo) {
      case 'hoy':
        fechaInicial = fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'ayer':
        const ayer = new Date(hoy);
        ayer.setDate(ayer.getDate() - 1);
        fechaInicial = fechaFinal = ayer.toISOString().split('T')[0];
        break;
      case 'mes':
        fechaInicial = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
        fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'personalizado':
        fechaInicial = document.getElementById('fecha-desde-excel').value;
        fechaFinal = document.getElementById('fecha-hasta-excel').value;
        break;
      default:
        // "todo" - sin filtro de fechas
        break;
    }

    if (fechaInicial && fechaFinal) {
      url += `&fechaInicial=${fechaInicial}&fechaFinal=${fechaFinal}`;
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

  // --- LOGICA MODAL PDF ---
  document.getElementById('tipo-fecha-pdf').addEventListener('change', function () {
    const tipo = this.value;
    const campoDesde = document.getElementById('campo-desde-pdf');
    const campoHasta = document.getElementById('campo-hasta-pdf');

    if (tipo === 'personalizado') {
      campoDesde.style.display = 'block';
      campoHasta.style.display = 'block';
    } else {
      campoDesde.style.display = 'none';
      campoHasta.style.display = 'none';
    }
    actualizarEnlacePdf();
  });

  document.getElementById('fecha-desde-pdf').addEventListener('change', actualizarEnlacePdf);
  document.getElementById('fecha-hasta-pdf').addEventListener('change', actualizarEnlacePdf);
  document.getElementById('filtro-usuario-pdf').addEventListener('change', actualizarEnlacePdf);
  document.getElementById('filtro-cliente-pdf').addEventListener('change', actualizarEnlacePdf);

  function actualizarEnlacePdf() {
    const tipo = document.getElementById('tipo-fecha-pdf').value;
    const btnDescargar = document.getElementById('btn-descargar-pdf');
    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte-pdf.php?reporte=reporte`;

    let fechaInicial, fechaFinal;
    const hoy = new Date();

    switch (tipo) {
      case 'hoy':
        fechaInicial = fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'ayer':
        const ayer = new Date(hoy);
        ayer.setDate(ayer.getDate() - 1);
        fechaInicial = fechaFinal = ayer.toISOString().split('T')[0];
        break;
      case 'mes':
        fechaInicial = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
        fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'personalizado':
        fechaInicial = document.getElementById('fecha-desde-pdf').value;
        fechaFinal = document.getElementById('fecha-hasta-pdf').value;
        break;
      default:
        break;
    }

    if (fechaInicial && fechaFinal) {
      url += `&fechaInicial=${fechaInicial}&fechaFinal=${fechaFinal}`;
    }

    const usuario = document.getElementById('filtro-usuario-pdf').value;
    if (usuario) {
      url += `&usuario=${usuario}`;
    }

    const cliente = document.getElementById('filtro-cliente-pdf').value;
    if (cliente && cliente !== "todos") {
      url += `&cliente=${cliente}`;
    }

    btnDescargar.href = url;
  }

  $('#btn-descargar-pdf').on('click', function () {
    mostrarToast('¡Descarga iniciada! El archivo PDF se está generando...');
    setTimeout(function () { $('#modalDescargarPdf').modal('hide'); }, 1000);
  });
  // --- FIN LOGICA MODAL PDF ---

  // --- LOGICA MODAL FACTURACION ELECTRONICA ---
  document.getElementById('tipo-fecha-excel-fact').addEventListener('change', function () {
    const tipo = this.value;
    const campoDesde = document.getElementById('campo-desde-excel-fact');
    const campoHasta = document.getElementById('campo-hasta-excel-fact');

    if (tipo === 'personalizado') {
      campoDesde.style.display = 'block';
      campoHasta.style.display = 'block';
    } else {
      campoDesde.style.display = 'none';
      campoHasta.style.display = 'none';
    }
    actualizarEnlaceExcelFacturacion();
  });

  document.getElementById('fecha-desde-excel-fact').addEventListener('change', actualizarEnlaceExcelFacturacion);
  document.getElementById('fecha-hasta-excel-fact').addEventListener('change', actualizarEnlaceExcelFacturacion);
  document.getElementById('filtro-usuario-excel-fact').addEventListener('change', actualizarEnlaceExcelFacturacion);
  document.getElementById('filtro-cliente-excel-fact').addEventListener('change', actualizarEnlaceExcelFacturacion);
  document.getElementById('filtro-proveedor-excel-fact').addEventListener('change', actualizarEnlaceExcelFacturacion);

  // Al cambiar categoría en el modal
  document.getElementById('filtro-categoria-excel-fact').addEventListener('change', function () {
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

  // También queremos que lea 'categoria' y 'tercero' actuales
  $('#seleccionarCategoriaReporte, #seleccionarClienteReporte, #seleccionarProveedorReporte').on('change', function () {
    actualizarEnlaceExcelFacturacion();
  });

  // Al abrir la ventana modal actualizar el enlace
  $('#modalDescargarExcelFacturacion').on('show.bs.modal', function () {
    actualizarEnlaceExcelFacturacion();
  });

  function actualizarEnlaceExcelFacturacion() {
    const tipo = document.getElementById('tipo-fecha-excel-fact').value;
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

    let fechaInicial, fechaFinal;
    const hoy = new Date();

    switch (tipo) {
      case 'hoy':
        fechaInicial = fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'ayer':
        const ayer = new Date(hoy);
        ayer.setDate(ayer.getDate() - 1);
        fechaInicial = fechaFinal = ayer.toISOString().split('T')[0];
        break;
      case 'mes':
        fechaInicial = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
        fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'personalizado':
        fechaInicial = document.getElementById('fecha-desde-excel-fact').value;
        fechaFinal = document.getElementById('fecha-hasta-excel-fact').value;
        break;
      default:
        // "todo" - sin filtro de fechas
        break;
    }

    if (fechaInicial && fechaFinal) {
      url += `&fechaInicial=${fechaInicial}&fechaFinal=${fechaFinal}`;
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

      // Forzar reset de sucursal al cargar para que siempre inicie en "Vista Global" (Solo para administradores)
      if ($("#sucursalReporteMaestro").is("select")) {
        $("#sucursalReporteMaestro").val("todos").trigger("change.select2");
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
      }
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

        var $selProv = $('#filtro-proveedor-excel-fact');
        if ($selProv.hasClass('select2-hidden-accessible')) {
          $selProv.select2('destroy');
        }
        $selProv.select2({
          width: '100%',
          dropdownParent: $('#modalDescargarExcelFacturacion'),
          placeholder: 'Mostrar Todos'
        });
      }
      actualizarEnlaceExcelFacturacion();
    });

    // Al abrir el modal de PDF: inicializar Select2 con dropdownParent
    $('#modalDescargarPdf').on('shown.bs.modal', function () {
      if ($.fn.select2) {
        var $selPdf = $('#filtro-cliente-pdf');
        if ($selPdf.hasClass('select2-hidden-accessible')) {
          $selPdf.select2('destroy');
        }
        $selPdf.select2({
          width: '100%',
          dropdownParent: $('#modalDescargarPdf'),
          placeholder: 'Mostrar Todos'
        });
      }
      actualizarEnlacePdf();
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

      // 4. Estado de Resultados FE (Sección 5)
      var formFinanFact = document.getElementById('filtro-financiero-fact');
      if (formFinanFact) formFinanFact.dispatchEvent(new Event('submit', { cancelable: true }));

      // 5. Reporte Facturación Electrónica (Sección 6)
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
      // En desktop: colapsar solo las secciones 2, 3, 4, 5 y 6 (dejar la 1ra expandida)
      $('#seccion-graficos-rendimiento').addClass('collapsed-box');
      $('#seccion-estado-resultados').addClass('collapsed-box');
      $('#seccion-analisis-ordenes').addClass('collapsed-box');
      $('#seccion-estado-resultados-facturacion').addClass('collapsed-box');
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