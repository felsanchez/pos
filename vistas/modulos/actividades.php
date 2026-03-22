<!-- Ruta actividades.css -->
<link rel="stylesheet" href="assets/css/actividades.css">

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css' rel='stylesheet' />


<!-- Centrar filtro -->
<style>
  @media (max-width: 767px) {

    .filtro-estado-wrapper,
    .filtro-tipo-wrapper {
      float: none !important;
      /* anula el pull-right */
      justify-content: center !important;
      /* centra con flex */
      text-align: center;
      /* por si acaso */
      width: 100%;
      /* ocupa todo el ancho */
    }

    .filtro-estado-wrapper label,
    .filtro-tipo-wrapper label {
      margin-bottom: 5px;
      /* pequeño espacio si se apila */
    }

    /* Botones pequeños en móvil (Columna Acciones es la última) */
    .tabla-actividades .table tr td:last-child .btn,
    .tabla-actividades .table tr th:last-child .btn {
      padding: 1px 5px !important;
      font-size: 12px !important;
      line-height: 1.5 !important;
    }
  }
</style>

<!-- DataTables Responsive manejará la visualización móvil -->



<style>
  .card-actividad.actividad-hoy {
    border-left: 5px solid #28a745 !important;
    background-color: #f0f9f4;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
  }

  /* Ocultar columna de control (botón responsive) en escritorio */
  @media (min-width: 768px) {

    .tabla-actividades .table tr th:first-child,
    .tabla-actividades .table tr td:first-child {
      display: none !important;
    }
  }
</style>




<div class="content-wrapper">
  <section class="content-header">

    <?php
    $editarActividad = new ControladorActividades();
    $editarActividad->ctrEditarActividad();
    ?>

    <h1>
      Administrar Actividades
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Actividades</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarActividad">
          Agregar Actividad
        </button>

        <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarEstados">
          <i class="fa fa-flag"></i> Gestionar estados
        </button>

        <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarTipos">
          <i class="fa fa-tags"></i> Gestionar tipos
        </button>

      </div>


      <!--Filtro Tipos-->
      <?php
      $filtroTipo = isset($_GET['filtroTipo']) ? $_GET['filtroTipo'] : '';  // Captura el valor del filtro tipo si existe
      // Aplica el filtro para obtener las actividades correctas
      $item = "tipo";
      $valor = $filtroTipo;
      $actividades = ControladorActividades::ctrMostrarActividades($item, $valor);
      ?>


      <div class="box-body table-responsive">


        <!-- Filtro tipo -->
        <div class="clearfix mb-2">
          <div class="pull-right filtro-tipo-wrapper d-flex align-items-center" style="gap: 8px;">
            <label for="filtroTipo" class="control-label mb-0">Filtra por TIPO:</label>
            <select id="filtroTipo" class="form-control filtro-tipo">
              <option value="">Todos</option>

              <?php
              $tiposFiltro = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
              foreach ($tiposFiltro as $tipoFiltro) {
                $selected = ($filtroTipo == $tipoFiltro["nombre"]) ? "selected" : "";
                echo '<option value="' . $tipoFiltro["nombre"] . '" ' . $selected . '>' . ucfirst($tipoFiltro["nombre"]) . '</option>';
              }
              ?>

            </select>
          </div>
        </div>
        <br>


        <!-- Filtro estado -->
        <div class="clearfix mb-2">
          <div class="pull-right filtro-estado-wrapper d-flex align-items-center" style="gap: 8px;">
            <label for="filtroEstado" class="control-label mb-0">Filtra por ESTADO:</label>
            <select id="filtroEstado" class="form-control filtro-estado">
              <option value="">Todos</option>

              <?php
              $filtroEstado = isset($_GET['filtroEstado']) ? $_GET['filtroEstado'] : '';
              $estadosFiltro = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
              foreach ($estadosFiltro as $estadoFiltroItem) {
                $selected = ($filtroEstado == $estadoFiltroItem["nombre"]) ? "selected" : "";
                echo '<option value="' . $estadoFiltroItem["nombre"] . '" ' . $selected . '>' . ucfirst($estadoFiltroItem["nombre"]) . '</option>';
              }
              ?>

            </select>
          </div>
        </div>

        <br><br>

        <!-- TABLA PARA ESCRITORIO -->
        <div class="tabla-actividades">

          <table class="table table-bordered table-striped tablas" style="width: 100%">

            <thead>
              <tr>
                <th></th>
                <th style="width: 5px" class="none">#</th>
                <th class="all">Descripción</th>
                <th class="all">Tipo</th>
                <th class="desktop">Responsable</th>
                <th class="desktop">Fecha</th>
                <th class="desktop">Estado</th>
                <th class="desktop">Cliente</th>
                <th class="desktop"><i class="fa fa-pencil-square"></i> Observación</th>
                <th class="all">Acciones</th>

              </tr>
            </thead>

            <tbody>

              <?php
              $item = null;
              $valor = null;
              $actividades = ControladorActividades::ctrMostrarActividades($item, $valor);

              // Obtener estados una sola vez para toda la tabla
              $estadosActividades = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
              ?>

              <?php
              foreach ($actividades as $key => $value):
                // Verificar si es hoy
                $fechaHoy = date('Y-m-d');
                $fechaActividad = !empty($value["fecha"]) ? substr($value["fecha"], 0, 10) : '';
                $esHoy = ($fechaActividad == $fechaHoy);
                $rowStyle = $esHoy ? 'style="border-left: 6px solid #28a745 !important; background-color: #f0f9f4; box-shadow: inset 6px 0 0 #28a745;"' : '';
                ?>

                <tr <?php echo $rowStyle; ?> data-tipo="<?php echo strtolower($value["tipo"]); ?>"
                  data-estado="<?php echo strtolower($value["estado"]); ?>">
                  <td class="control"></td>
                  <td data-order="<?php echo $value["id"]; ?>"><?php echo $key + 1; ?></td>
                  <td><?php echo $value["descripcion"]; ?></td>

                  <td><?php echo $value["tipo"]; ?></td>

                  <?php
                  $itemUsuario = "id";
                  $valorUsuario = $value["id_user"];
                  $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
                  if ($respuestaUsuario) {
                    echo '<td>' . $respuestaUsuario["nombre"] . '</td>';
                  } else {
                    echo '<td>Sin asignar</td>'; // o lo que prefieras mostrar si no hay usuario
                  }
                  ?>

                  <td><?php echo $value["fecha"]; ?></td>

                  <td>
                    <?php
                    // Obtener el estado actual
                    $estadoActual = $value["estado"] ?? "";

                    // Buscar el color del estado (comparación case-insensitive)
                    $colorEstado = "#999"; // Color por defecto (gris)
                    foreach ($estadosActividades as $estado) {
                      if (strcasecmp($estado["nombre"], $estadoActual) == 0) {
                        $colorEstado = $estado["color"];
                        break;
                      }
                    }

                    // Mostrar badge con color
                    if (!empty($estadoActual)) {
                      echo '<span class="badge" style="background-color: ' . $colorEstado . '">' . ucfirst($estadoActual) . '</span>';
                    } else {
                      echo '<span class="text-muted">Sin estado</span>';
                    }
                    ?>

                  </td>


                  <?php
                  $itemCliente = "id";
                  $valorCliente = $value["id_cliente"];
                  $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
                  if ($respuestaCliente) {
                    echo '<td>' . $respuestaCliente["nombre"] . '</td>';
                  } else {
                    echo '<td>Sin cliente</td>'; // o como quieras mostrarlo
                  }
                  ?>

                  <td contenteditable="true" class="celda-observacion" data-id="<?= $value['id']; ?>">
                    <?= $value['observacion']; ?>
                  </td>

                  <td>
                    <div class="btn-group">

                      <!--<button class="btn btn-warning btnEditarActividad" 
                                    idActividad="<?php echo $value["id"]; ?>">
                                    <i class="fa fa-pencil"></i>
                                </button>-->

                      <button class="btn btn-warning btnEditarActividad" data-id="<?php echo $actividad['id']; ?>"
                        data-toggle="modal" data-target="#modalEditarActividad"
                        idActividad="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>

                      <button class="btn btn-danger btnEliminarActividad" idActividad="<?php echo $value["id"]; ?>"><i
                          class="fa fa-times"></i></button>
                    </div>
                  </td>
                </tr>

                <?php
              endforeach;
              ?>


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

              <!-- Observación -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Observación</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                    <input type="text" class="form-control" name="nuevaObservacion" id="nuevaObservacion"
                      placeholder="Observaciones adicionales">
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

              <!-- Observación -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Observación</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                    <input type="text" class="form-control" name="editarObservacion" id="editarObservacion"
                      placeholder="Observaciones adicionales">
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

<!-- Estilos para que el modal de edición quede encima del modal de gestión -->
<style>
  /* Modal de gestión - nivel base */
  #modalGestionarEstados.modal {
    z-index: 1050 !important;
  }

  #modalGestionarEstados+.modal-backdrop {
    z-index: 1049 !important;
  }
</style>

<!--=====================================
MODAL EDITAR ESTADO
======================================-->

<div id="modalEditarEstadoActividad" class="modal fade" role="dialog" data-backdrop="true" data-keyboard="true">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

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

<!-- DataTables Initialization for Actividades -->
<script>
  $(document).ready(function () {
    // Verificar si existe la tabla antes de inicializar
    if ($(".tablas").length > 0) {
      // Destruir instancia previa si existe
      if ($.fn.DataTable.isDataTable('.tablas')) {
        $('.tablas').DataTable().destroy();
      }

      $(".tablas").DataTable({
        "order": [[1, "desc"]], // Ordenar por ID (columna 1)
        "responsive": {
          "details": {
            "type": "column",
            "target": 0,
            "renderer": function (api, rowIdx, columns) {

              // Mapear datos por título de columna
              var dataMap = {};
              columns.forEach(function (col) {
                var headerText = col.title ? col.title.replace(/<[^>]*>?/gm, '').trim() : '';
                dataMap[headerText] = col.data;
              });

              // Obtener datos
              var descripcion = dataMap['Descripción'] || '';
              var tipo = dataMap['Tipo'] || '';
              var responsable = dataMap['Responsable'] || '';
              var fecha = dataMap['Fecha'] || '';
              var estado = dataMap['Estado'] || '';
              var cliente = dataMap['Cliente'] || '';
              var observacion = dataMap['Observación'] || '';
              var acciones = dataMap['Acciones'] || '';

              var finalHtml = '';

              // SECCIÓN 1: Información General
              finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Información General</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold" style="color:#555;">Descripción: </span><span class="pull-right" style="color:#333;">' + descripcion + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold" style="color:#555;">Fecha: </span><span class="pull-right" style="color:#333;">' + fecha + '</span></div>';

              // SECCIÓN 2: Categoría
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #f39c12;">';
              finalHtml += '<h5 style="font-weight:bold; color:#f39c12; margin:0;">Categoría</h5></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold" style="color:#555;">Estado: </span><span class="pull-right" style="color:#333;">' + estado + '</span></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold" style="color:#555;">Tipo: </span><span class="pull-right" style="color:#333;">' + tipo + '</span></div>';

              // SECCIÓN 3: Clientes
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #00a65a;">';
              finalHtml += '<h5 style="font-weight:bold; color:#00a65a; margin:0;">Clientes</h5></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold" style="color:#555;">Responsable: </span><span class="pull-right" style="color:#333;">' + responsable + '</span></div>';

              finalHtml += '<div class="col-xs-12 col-sm-6" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold" style="color:#555;">Cliente: </span><span class="pull-right" style="color:#333;">' + cliente + '</span></div>';

              // SECCIÓN 4: Información Adicional (solo si hay observación)
              if (observacion && observacion.trim() !== '') {
                finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #605ca8;">';
                finalHtml += '<h5 style="font-weight:bold; color:#605ca8; margin:0;">Información Adicional</h5></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                finalHtml += '<span class="text-bold" style="color:#555;">Observación: </span><span class="pull-right" style="color:#333;">' + observacion + '</span></div>';
              }

              // SECCIÓN 4: Información Adicional (solo si hay observación)
              if (observacion && observacion.trim() !== '') {
                finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #605ca8;">';
                finalHtml += '<h5 style="font-weight:bold; color:#605ca8; margin:0;">Información Adicional</h5></div>';

                finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                finalHtml += '<span class="text-bold" style="color:#555;">Observación: </span><span class="pull-right" style="color:#333;">' + observacion + '</span></div>';
              }

              // SECCIÓN 5: Acciones (ELIMINADO en detalle móvil porque ya se muestra en la tabla principal)
              /*
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #dd4b39;">';
              finalHtml += '<h5 style="font-weight:bold; color:#dd4b39; margin:0;">Acciones</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 10px 0;">';
              finalHtml += acciones;
              finalHtml += '</div>';
              */

              return finalHtml;
            }
          }
        },
        "language": {
          "sProcessing": "Procesando...",
          "sLengthMenu": "Mostrar _MENU_ registros",
          "sZeroRecords": "No se encontraron resultados",
          "sEmptyTable": "Ningún dato disponible en esta tabla",
          "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
          "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
          "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
          "sInfoPostFix": "",
          "sSearch": "Buscar:",
          "sUrl": "",
          "sInfoThousands": ",",
          "sLoadingRecords": "Cargando...",
          "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
          },
          "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
          }
        }
      });

      // Marcar tabla como lista para mostrarla
      $(".tablas").addClass("datatable-ready");
    }
  });
</script>

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