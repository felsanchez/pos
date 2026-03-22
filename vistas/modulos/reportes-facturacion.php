<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Reportes Facturación Electrónica
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Reportes Facturación</li>
    </ol>

  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Filtros de Reporte</h3>
          </div>
          <div class="box-body">
            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
              <div class="col-md-3" style="margin-bottom: 10px;">
                <div class="input-group">
                  <button type="button" class="btn btn-default" id="daterange-btn-reportes" style="width: 100%;">
                    <span>
                      <i class="fa fa-calendar"></i> Rango de fecha
                    </span>
                    <i class="fa fa-caret-down"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-2" style="margin-bottom: 10px;">
                <div class="input-group">
                  <span class="input-group-addon" style="background-color: #f4f4f4;"><i class="fa fa-filter"></i>
                    Categoría</span>
                  <select class="form-control" id="seleccionarCategoriaReporte">
                    <option value="todos">Todos los documentos</option>
                    <option value="facturas">Facturas Electrónicas</option>
                    <option value="nc">Notas Crédito</option>
                    <option value="ds">Documentos Soporte</option>
                    <option value="na">Notas de Ajuste DS</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3" style="margin-bottom: 10px;">
                <div class="input-group" style="width: 100%;">
                  <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i
                      class="fa fa-users"></i></span>
                  <select class="form-control" id="seleccionarClienteReporte" style="display:block; width: 100%;">
                    <option value="todos">Todos los clientes</option>
                    <?php
$clientes = ControladorClientes::ctrMostrarClientes(null, null);
foreach ($clientes as $key => $value) {
  echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
}
?>
                  </select>
                  <select class="form-control" id="seleccionarProveedorReporte" style="display:none; width: 100%;">
                    <option value="todos">Todos los proveedores</option>
                    <?php
$proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
foreach ($proveedores as $key => $value) {
  echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
}
?>
                  </select>
                </div>
              </div>
              <div class="col-md-3" style="margin-bottom: 10px;">
                <div class="input-group" style="width: 100%;">
                  <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i
                      class="fa fa-user"></i></span>
                  <select class="form-control" id="seleccionarUsuarioReporte" style="width: 100%;">
                    <option value="todos">Todos los usuarios</option>
                    <?php
$usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
foreach ($usuarios as $key => $value) {
  echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
}
?>
                  </select>
                </div>
              </div>
              <div class="col-md-1" style="margin-bottom: 10px; display: flex; gap: 5px;">
                <button type="button" class="btn btn-primary" id="btnFiltrarReportes" style="flex: 1;">
                  <i class="fa fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-default" id="btnLimpiarFiltrosReportes" style="flex: 1;" title="Limpiar filtros">
                  <i class="fa fa-refresh"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- WIDGETS DE RESUMEN (KPIs) -->
    <div class="row" id="kpi-reports">
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3 id="widget-total-ventas">$0</h3>
            <p>Venta Neta (Facturado - NC)</p>
          </div>
          <div class="icon">
            <i class="ion ion-social-usd"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <h3 id="widget-total-iva">$0</h3>
            <p>Total IVA Recaudado</p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3 id="widget-total-ds">$0</h3>
            <p>Documentos Soporte</p>
          </div>
          <div class="icon">
            <i class="ion ion-ios-paper"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <h3 id="widget-total-docs">0</h3>
            <p>Docs. Electrónicos Totales</p>
          </div>
          <div class="icon">
            <i class="ion ion-ios-albums"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Fila para Tabla Detallada -->
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Listado Consolidado de Documentos</h3>
            <button type="button" class="btn btn-success pull-right" id="btnExportarExcelFacturacion">
              <i class="fa fa-file-excel-o"></i> Exportar a Excel
            </button>
          </div>
          <div class="box-body">
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

  </section>

</div>

<script src="vistas/js/reportes-facturacion.js?v=<?php echo time(); ?>"></script>