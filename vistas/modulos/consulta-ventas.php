<style>
.formulario-fechas-container {
  max-width: 300px;
  padding: 15px;
  border-radius: 10px;
  background-color: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}
.formulario-fechas label {
  font-weight: 600;
  margin-top: 10px;
}
.formulario-fechas select,
.formulario-fechas input[type="date"] {
  border-radius: 8px;
  margin-bottom: 10px;
}
.d-none {
  display: none !important;
}
</style>


<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  
  
  <?php

    $xml = ControladorVentas::ctrDescargarXML();

    if($xml){

      rename($_GET["xml"].".xml", "xml/".$_GET["xml"].".xml");
      echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/'.$_GET["xml"].'.xml" href="ventas">Se ha creado correctamente el archivo XML<span class="fa fa-times pull-right"></span></a>';
    }
  ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Administrar Consulta de ventas
      </h1>
      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Administrar Consulta de ventas</li>
      </ol>
    </section>

    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <div class="pull-right contenedor-filtros">
            <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
              <input type="hidden" name="ruta" value="consulta-ventas">
              <input type="hidden" name="fechaInicial" id="fechaInicial" value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
              <input type="hidden" name="fechaFinal" id="fechaFinal" value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

              <!-- Botón Rango de Fecha -->
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="hidden-xs"><b>Fecha:</b></span>
                <button type="button" class="btn btn-default" id="daterange-btn">
                  <span>
                    <i class="fa fa-calendar"></i> Rango de fecha
                  </span>
                  <i class="fa fa-caret-down"></i>
                </button>
              </div>

              <!-- Botón Limpiar -->
              <a href="index.php?ruta=consulta-ventas" class="btn btn-default" title="Limpiar">
                <i class="fa fa-refresh"></i>
              </a>
            </form>
          </div>
        </div>

        <div class="box-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped tablaConsultaVentas display nowrap" width="100%">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Cliente</th>
                  <th>Forma de pago</th>
                  <th>Total</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <!-- Cargado por DataTables Server-Side -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!--==========================================================================
  MODAL VER CLIENTE (REUTILIZADO)
  ===========================================================================-->
  <div id="modalEditarCliente" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form role="form" method="post">
          <?php CSRF::insertToken(); ?>
          <div class="modal-header" style="background:#3c8dbc; color: white">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Detalles del Cliente</h4>
          </div>
          <div class="modal-body">
            <div class="box-body">
              <div class="row">
                <div class="col-xs-12 col-md-6">
                  <div class="form-group">
                    <label>Nombre:</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-user"></i></span>
                      <input type="text" class="form-control input-lg" id="editarCliente" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12 col-md-6">
                  <div class="form-group">
                    <label>Documento:</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-key"></i></span>
                      <input type="text" class="form-control input-lg" id="editarDocumentoId" readonly>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-xs-12 col-md-6">
                  <div class="form-group">
                    <label>Teléfono:</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                      <input type="text" class="form-control input-lg" id="editarTelefono" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-xs-12 col-md-6">
                  <div class="form-group">
                    <label>Email:</label>
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                      <input type="text" class="form-control input-lg" id="editarEmail" readonly>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Dirección:</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                  <input type="text" class="form-control input-lg" id="editarDireccion" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php
    $eliminarVenta = new ControladorVentas(); 
    $eliminarVenta -> ctrEliminarVenta();
  ?>

  <!-- Scripts específicos del módulo -->
  <script src="vistas/bower_components/moment/min/moment.min.js"></script>
  <script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
  <script src="vistas/js/consulta-ventas.js"></script>