<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Sistema de Logs
      <small>Registro de errores y actividades</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Logs</li>
    </ol>
  </section>

  <section class="content">

    <!-- Estadísticas -->
    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3 id="stat-total">0</h3>
            <p>Total de Logs</p>
          </div>
          <div class="icon">
            <i class="fa fa-file-text-o"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <h3 id="stat-errors">0</h3>
            <p>Errores</p>
          </div>
          <div class="icon">
            <i class="fa fa-times-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-orange">
          <div class="inner">
            <h3 id="stat-warnings">0</h3>
            <p>Advertencias</p>
          </div>
          <div class="icon">
            <i class="fa fa-exclamation-triangle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-blue">
          <div class="inner">
            <h3 id="stat-info">0</h3>
            <p>Información</p>
          </div>
          <div class="icon">
            <i class="fa fa-info-circle"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros y controles -->
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Filtros</h3>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Fecha:</label>
                  <input type="date" class="form-control" id="filtro-fecha" value="<?php echo date('Y-m-d'); ?>">
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Nivel:</label>
                  <select class="form-control" id="filtro-nivel">
                    <option value="">Todos</option>
                    <option value="ERROR">ERROR</option>
                    <option value="WARNING">WARNING</option>
                    <option value="INFO">INFO</option>
                    <option value="DEBUG">DEBUG</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Límite:</label>
                  <select class="form-control" id="filtro-limite">
                    <option value="50">50 registros</option>
                    <option value="100" selected>100 registros</option>
                    <option value="200">200 registros</option>
                    <option value="500">500 registros</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <div>
                    <button type="button" class="btn btn-primary btn-block" id="btn-filtrar">
                      <i class="fa fa-filter"></i> Filtrar
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <button type="button" class="btn btn-success" id="btn-refrescar">
                  <i class="fa fa-refresh"></i> Refrescar
                </button>

                 <button type="button" class="btn btn-danger" id="btn-eliminar-seleccionados" style="display: none;">
                  <i class="fa fa-trash"></i> Eliminar Seleccionados
                </button>

                <button type="button" class="btn btn-warning" id="btn-limpiar" title="Eliminar logs con más de 30 días">
                  <i class="fa fa-trash"></i> Limpiar Logs Antiguos
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de logs -->
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Registros de Log</h3>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover" id="tabla-logs">
                <thead>
                  <tr>
                    <th style="width: 30px;">
                      <input type="checkbox" id="check-all">
                    </th>
                    <th style="width: 140px;">Fecha/Hora</th>
                    <th style="width: 80px;">Nivel</th>
                    <th>Mensaje</th>
                    <th style="width: 100px;">Usuario</th>
                    <th style="width: 100px;">IP</th>
                    <th style="width: 60px;">Detalles</th>
                  </tr>
                </thead>
                <tbody id="tbody-logs">
                  <tr>
                    <td colspan="7" class="text-center">
                      <i class="fa fa-spinner fa-spin"></i> Cargando logs...
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>

</div>

<!-- Modal para ver detalles del log -->
<div class="modal fade" id="modal-detalle-log">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Detalles del Log</h4>
      </div>
      <div class="modal-body" id="modal-body-log">
        <!-- Contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>