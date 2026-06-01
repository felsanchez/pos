<!-- Ruta actividades.css -->
<link rel="stylesheet" href="assets/css/actividades.css">

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css' rel='stylesheet' />

<!-- Librerías para Tooltips (Tippy.js + Popper.js) -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css" />

<!-- Estilos para filtros estandarizados -->

.card-actividad.actividad-hoy {
border-left: 5px solid #28a745 !important;
background-color: #f0f9f4;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
}
</style>

<?php
$urlActual = "actividades";
$params = [];
if (isset($_GET['filtroTipo']) && !empty($_GET['filtroTipo'])) {
  $params[] = "filtroTipo=" . $_GET['filtroTipo'];
}
if (isset($_GET['filtroEstado']) && !empty($_GET['filtroEstado'])) {
  $params[] = "filtroEstado=" . $_GET['filtroEstado'];
}
if (!empty($params)) {
  $urlActual .= "?" . implode("&", $params);
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <?php
    $editarActividad = new ControladorActividades();
    $editarActividad->ctrEditarActividad();
    ?>
    <h1>
      Agenda de actividades
      <small>Seguimiento comercial</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Actividades</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <?php if (puedeAccion('actividades', 'crear')): ?>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarActividad">
            <i class="fa fa-plus"></i> Agregar Actividad
          </button>
        <?php endif; ?>
        <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarEstados">
          <i class="fa fa-flag"></i> Gestionar estados
        </button>
        <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarTipos">
          <i class="fa fa-tags"></i> Gestionar tipos
        </button>

        <div class="pull-right" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="hidden-xs"><b>Tipo:</b></span>
            <div class="input-group" style="width: 200px;">
              <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;"><i
                  class="fa fa-search text-primary"></i></span>
              <select class="form-control select2" id="filtroTipo" style="width: 100%;">
                <option value="">Mostrar Todos</option>
                <?php
                $tiposFiltro = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
                $filtroTipoHeader = isset($_GET['filtroTipo']) ? $_GET['filtroTipo'] : '';
                foreach ($tiposFiltro as $tipoFiltroItem) {
                  $selected = ($filtroTipoHeader == $tipoFiltroItem["nombre"]) ? "selected" : "";
                  echo '<option value="' . $tipoFiltroItem["nombre"] . '" ' . $selected . '>' . ucfirst($tipoFiltroItem["nombre"]) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="hidden-xs"><b>Estado:</b></span>
            <div class="input-group" style="width: 200px;">
              <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;"><i
                  class="fa fa-search text-primary"></i></span>
              <select class="form-control select2" id="filtroEstado" style="width: 100%;">
                <option value="">Mostrar Todos</option>
                <?php
                $filtroEstadoHeader = isset($_GET['filtroEstado']) ? $_GET['filtroEstado'] : '';
                $estadosFiltro = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
                foreach ($estadosFiltro as $estadoFiltroItem) {
                  $selected = ($filtroEstadoHeader == $estadoFiltroItem["nombre"]) ? "selected" : "";
                  echo '<option value="' . $estadoFiltroItem["nombre"] . '" ' . $selected . '>' . ucfirst($estadoFiltroItem["nombre"]) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="box-body">
        <div class="tabla-actividades table-responsive">
          <table class="table table-bordered table-striped tablaActividades display nowrap" style="width: 100%">
            <thead>
              <tr>
                <th>Descripción</th>
                <th>Tipo</th>
                <th>Responsable</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Cliente</th>
                <th>Notas</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Los datos se cargarán dinámicamente por AJAX (Server-Side) -->
            </tbody>

          </table>

        </div>
        <!-- FIN TABLA -->

      </div>

    </div>

    <!--<button class="btn btn-primary pull-left" onclick="location.href='actividades-cuadro'">CUADRO</button>-->

    <!--Calendario
        <div class="calendar-container">
        <div id="calendar" style="width: 100%;"></div>
        </div>
        -->

  </section>

</div>



<!--=====================================
MODAL AGREGAR actividad
======================================-->

<!-- Modal -->
<div id="modalAgregarActividad" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar actividad</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

              <!-- Descripción -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Descripción *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tasks"></i></span>
                    <input type="text" class="form-control" name="nuevaActividad" id="nuevaActividad"
                      placeholder="Descripción de la actividad" required>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Tipo -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tipo *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-info-circle"></i></span>
                    <select class="form-control" name="nuevoTipo" id="nuevoTipo" required>
                      <option value="">Seleccionar tipo</option>
                      <?php
                      $tiposModalAgregar = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
                      foreach ($tiposModalAgregar as $tipoModal) {
                        echo '<option value="' . $tipoModal["nombre"] . '">' . ucfirst($tipoModal["nombre"]) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Responsable -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Responsable *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>
                    <select class="form-control" id="nuevoUsuario" name="nuevoUsuario" required>
                      <option value="">Seleccionar responsable</option>
                      <?php
                      $item = null;
                      $valor = null;
                      $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                      foreach ($usuarios as $key => $value) {
                        if ($value["perfil"] == "_SystemMaster_") continue;
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Estado *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    <select class="form-control" name="nuevoEstado" id="nuevoEstado" required>
                      <option value="">Seleccionar estado</option>
                      <?php
                      $estadosModalAgregar = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
                      foreach ($estadosModalAgregar as $estadoModal) {
                        echo '<option value="' . $estadoModal["nombre"] . '">' . ucfirst($estadoModal["nombre"]) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Fecha -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Fecha *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="datetime-local" class="form-control" name="nuevaFecha" id="nuevaFecha" required>
                  </div>
                </div>
              </div>

              <!-- Cliente -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Cliente</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <select class="form-control" id="nuevoCliente" name="nuevoCliente" required>
                      <option value="0">Sin cliente</option>
                      <?php
                      $item = null;
                      $valor = null;
                      $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
                      foreach ($clientes as $key => $value) {
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Notas -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Notas</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                    <input type="text" class="form-control" name="nuevaObservacion" id="nuevaObservacion"
                      placeholder="Escribe una nota...">
                  </div>
                </div>
              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar actividad</button>

        </div>

      </form>


      <?php

      $crearActividad = new ControladorActividades();
      $crearActividad->ctrCrearActividad();

      ?>

    </div>

  </div>

</div>


<!--==========================================================================
MODAL EDITAR Actividad
============================================================================-->

<!-- Modal -->
<div id="modalEditarActividad" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

        <!--=====================================
            CABEZA DEL MODAL
            ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Actividad</h4>

        </div>

        <!--=====================================
            CUERPO DEL MODAL
            ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

              <!-- Descripción -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Descripción *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tasks"></i></span>
                    <input type="text" class="form-control" name="editarActividad" id="editarActividad"
                      placeholder="Descripción de la actividad" required>
                    <input type="hidden" name="idActividad"
                      value="<?php echo !empty($actividad['id']) ? $actividad['id'] : ''; ?>">
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Tipo -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tipo *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-info-circle"></i></span>
                    <select class="form-control" name="editarTipo" id="editarTipo" required>
                      <option value="">Seleccionar tipo</option>
                      <?php
                      $tiposModalEditar = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
                      foreach ($tiposModalEditar as $tipoModal) {
                        echo '<option value="' . $tipoModal["nombre"] . '">' . ucfirst($tipoModal["nombre"]) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Responsable -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Responsable *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>
                    <select class="form-control" id="editarUsuario" name="editarUsuario">
                      <option value="">Seleccionar responsable</option>
                      <?php
                      $item = null;
                      $valor = null;
                      $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                      foreach ($usuarios as $key => $value) {
                        if ($value["perfil"] == "_SystemMaster_") continue;
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Estado *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    <select class="form-control" name="editarEstado" id="editarEstado" required>
                      <option value="">Seleccionar estado</option>
                      <?php
                      $estadosModalEditar = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
                      foreach ($estadosModalEditar as $estadoModal) {
                        echo '<option value="' . $estadoModal["nombre"] . '">' . ucfirst($estadoModal["nombre"]) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Cliente -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Cliente</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <select class="form-control" id="editarCliente" name="editarCliente">
                      <option value="0">Sin cliente</option>
                      <?php
                      $item = null;
                      $valor = null;
                      $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
                      foreach ($clientes as $key => $value) {
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Notas -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Notas</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                    <input type="text" class="form-control" name="editarObservacion" id="editarObservacion"
                      placeholder="Escribe una nota...">
                  </div>
                </div>
              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      </form>

    </div>

  </div>

</div>


<!--=====================================
MODAL GESTIONAR ESTADOS
======================================-->

<div id="modalGestionarEstados" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Gestionar Estados de Actividades</h4>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <!-- Formulario agregar estado -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nuevo Estado</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarEstado">

              <?php CSRF::insertToken(); ?>
              <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoEstadoNombre" placeholder="Nombre del estado *"
                      required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <input type="color" class="form-control" name="nuevoEstadoColor" value="#3c8dbc">
                  </div>
                </div>
                <div class="col-md-3">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>

              <!-- CAMPO OCULTO PARA ORIGEN -->
              <input type="hidden" name="origenModal" value="actividades">

            </form>
          </div>
        </div>

        <!-- Lista de estados -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Estados Existentes</h3>
          </div>
          <div class="panel-body">
            <table class="table table-bordered table-striped tablaEstadosActividades">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Color</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $estadosGestion = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
                foreach ($estadosGestion as $key => $value) {
                  echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td><span class="badge" style="background-color: ' . $value["color"] . '">' . ucfirst($value["nombre"]) . '</span></td>
                      <td><input type="color" value="' . $value["color"] . '" disabled style="width: 50px;"></td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarEstadoActividad"
                          data-id="' . $value["id"] . '"
                          data-nombre="' . $value["nombre"] . '"
                          data-color="' . $value["color"] . '"
                          data-orden="' . $value["orden"] . '"
                          data-toggle="modal"
                          data-target="#modalEditarEstadoActividad">
                          <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btnEliminarEstadoActividad" idEstado="' . $value["id"] . '" nombreEstado="' . $value["nombre"] . '"><i class="fa fa-times"></i></button>
                      </td>
                    </tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>

  </div>

</div>



<!--=====================================
MODAL EDITAR ESTADO
======================================-->

<div id="modalEditarEstadoActividad" class="modal fade" role="dialog" data-backdrop="true" data-keyboard="true">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Estado</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="form-group">
              <label>Nombre *</label>
              <input type="text" class="form-control" name="editarEstadoNombre" id="editarEstadoNombre" required>
              <input type="hidden" name="idEstado" id="idEstado">
              <input type="hidden" name="editarEstadoOrden" id="editarEstadoOrden">
              <input type="hidden" name="origenModal" value="actividades">
            </div>

            <div class="form-group">
              <label>Color</label>
              <input type="color" class="form-control" name="editarEstadoColor" id="editarEstadoColor">
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

      </form>

    </div>

  </div>

</div>


<!--=====================================
MODAL GESTIONAR TIPOS
======================================-->

<div id="modalGestionarTipos" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Gestionar Tipos de Actividades</h4>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <!-- Formulario agregar tipo -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nuevo Tipo</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarTipo">

              <?php CSRF::insertToken(); ?>
              <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">
              <div class="row">
                <div class="col-md-9">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoTipoNombre" placeholder="Nombre del tipo *"
                      required>
                  </div>
                </div>
                <div class="col-md-3">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>

              <!-- CAMPO OCULTO PARA ORIGEN -->
              <input type="hidden" name="origenModal" value="actividades">

            </form>
          </div>
        </div>

        <!-- Lista de tipos -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Tipos Existentes</h3>
          </div>
          <div class="panel-body">
            <table class="table table-bordered table-striped tablaTiposActividades">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $tiposGestion = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
                foreach ($tiposGestion as $key => $value) {
                  echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td>' . ucfirst($value["nombre"]) . '</td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarTipoActividad" idTipo="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarTipoActividad"><i class="fa fa-pencil"></i></button>
                        <button class="btn btn-danger btn-xs btnEliminarTipoActividad" idTipo="' . $value["id"] . '" nombreTipo="' . $value["nombre"] . '"><i class="fa fa-times"></i></button>
                      </td>
                    </tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR TIPO
======================================-->

<div id="modalEditarTipoActividad" class="modal fade" role="dialog" data-backdrop="true" data-keyboard="true">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Tipo</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="form-group">
              <label>Nombre *</label>
              <input type="text" class="form-control" name="editarTipoNombre" id="editarTipoNombre" required>
              <input type="hidden" name="idTipo" id="idTipo">
              <input type="hidden" name="editarTipoOrden" id="editarTipoOrden">
              <input type="hidden" name="origenModal" value="actividades">
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

      </form>

    </div>

  </div>

</div>


<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
<!-- Idioma Esp FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/es.js"></script>



<!-- Scripts ya cargados en plantilla.php, no duplicar aquí -->
<!-- actividades.js ya se carga en plantilla.php línea 230 -->
<script src="vistas/js/estados-actividades.js?v=<?php echo time(); ?>"></script>
<script src="vistas/js/tipos-actividades.js"></script>


<?php
$eliminarActividad = new ControladorActividades();
$eliminarActividad->ctrEliminarActividad();

$eliminarEstado = new ControladorEstadosActividades();
$eliminarEstado->ctrEliminarEstado();

$crearEstado = new ControladorEstadosActividades();
$crearEstado->ctrCrearEstado();

$editarEstado = new ControladorEstadosActividades();
$editarEstado->ctrEditarEstado();

$eliminarTipo = new ControladorTiposActividades();
$eliminarTipo->ctrEliminarTipo();

$crearTipo = new ControladorTiposActividades();
$crearTipo->ctrCrearTipo();

$editarTipo = new ControladorTiposActividades();
$editarTipo->ctrEditarTipo();
?>

<!-- DataTables y Scripts se manejan desde actividades.js -->

<!--=============CALENDARIO========================
<script>
success: function (respuesta) {
    console.log("Respuesta AJAX:", respuesta);

    $("#editarActividad").val(respuesta.descripcion);
    $("#editarTipo").val(respuesta.tipo);
    $("#editarUsuario").val(respuesta.id_user);
    $("#editarCliente").val(respuesta.id_cliente);
    $("#editarEstado").val(respuesta.estado);
    $("#editarObservacion").val(respuesta.observacion);
    $("input[name='idActividad']").val(respuesta.id);

    if (respuesta.fecha && !isNaN(new Date(respuesta.fecha))) {
        const fechaOriginal = new Date(respuesta.fecha);
        const fechaFormateada = fechaOriginal.toISOString().slice(0, 16);
        $("#editarFecha").val(fechaFormateada);
    } else {
        console.warn("Fecha inválida o vacía:", respuesta.fecha);
        $("#editarFecha").val("");
    }

    // ✅ Solución clave: cerrar primero y abrir luego
    $("#modalEditarActividad").modal("hide");

    setTimeout(() => {
        $("#modalEditarActividad").modal("show");
    }, 300); // da tiempo para cerrar correctamente antes de abrir
}
</script>
-->