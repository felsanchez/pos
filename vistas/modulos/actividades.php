<!-- Ruta actividades.css -->
<link rel="stylesheet" href="assets/css/actividades.css">

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css' rel='stylesheet' />


<!-- Centrar filtro -->
  <style>
    @media (max-width: 767px) {
    .filtro-estado-wrapper,
    .filtro-tipo-wrapper {
      float: none !important;        /* anula el pull-right */
      justify-content: center !important; /* centra con flex */
      text-align: center;            /* por si acaso */
      width: 100%;                   /* ocupa todo el ancho */
    }

    .filtro-estado-wrapper label,
    .filtro-tipo-wrapper label {
      margin-bottom: 5px;            /* pequeño espacio si se apila */
    }
  }
  </style>

<!-- Solo muestra 2 campos en movil en la Tabla 1-->
<style>
@media (max-width: 767px) {
  /* Ocultar TODAS las columnas primero */
  .tablas td,
  .tablas th {
      display: none;
  }
  
  /* Mostrar SOLO la columna 2 y columna 6 */
  .tablas td:nth-child(2),
  .tablas td:nth-child(6),
  .tablas th:nth-child(2),
  .tablas th:nth-child(6) {
      display: table-cell;
  }
}
</style>

<style>
/* Solo muestra el botón en móvil */
.solo-movil {
  display: none;
}
@media (max-width: 767px) {
  .solo-movil {
    display: inline-block !important;
  }
}
</style>

<!--Agregar espacio entre los btones en móvil-->
<style>
@media (max-width: 767px) {
  .solo-movil {
    margin-left: 3px !important;
  }
}
</style>




<div class="content-wrapper">
<section class="content-header">

    <?php
        $editarActividad = new ControladorActividades();
        $editarActividad -> ctrEditarActividad();
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
                <select id="filtroTipo" onchange="filterTableTipo()" class="form-control filtro-tipo">
                <option value="">Todos</option>

                <?php
                $tiposFiltro = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
                foreach($tiposFiltro as $tipoFiltro){
                    $selected = ($filtroTipo == $tipoFiltro["nombre"]) ? "selected" : "";
                    echo '<option value="'.$tipoFiltro["nombre"].'" '.$selected.'>'.ucfirst($tipoFiltro["nombre"]).'</option>';
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
                        foreach($estadosFiltro as $estadoFiltroItem){
                            $selected = ($filtroEstado == $estadoFiltroItem["nombre"]) ? "selected" : "";
                            echo '<option value="'.$estadoFiltroItem["nombre"].'" '.$selected.'>'.ucfirst($estadoFiltroItem["nombre"]).'</option>';
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
                        <th style="width: 5px">#</th>
                        <th>Descripción</th>
                       <th>Tipo</th>
                        <th>Responsable</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Cliente</th>
                        <th>Observación</th>
                        <th>Acciones</th> 

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
                            ?>

                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo $value["descripcion"]; ?></td>
                            
                            <td><?php echo ucfirst($value["tipo"]); ?></td>

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
                            foreach($estadosActividades as $estado){
                                if(strcasecmp($estado["nombre"], $estadoActual) == 0){
                                    $colorEstado = $estado["color"];
                                    break;
                                }
                            }

                            // Mostrar badge con color
                            if(!empty($estadoActual)){
                                echo '<span class="badge" style="background-color: '.$colorEstado.'">'.ucfirst($estadoActual).'</span>';
                            } else {
                                echo '<span class="text-muted">Sin estado</span>';
                            }
                            ?>

                                <!--BTN EDITAR Y ELIMINAR EN MOVIL-->
                                <button class="btn btn-danger btnEliminarActividad btn-xs solo-movil" style="float: right;" idActividad="<?php echo $value["id"]; ?>"><i class="fa fa-times"></i></button>

                                <button class="btn btn-warning btnEditarActividad btn-xs solo-movil" style="float: right;" data-id="<?php echo $actividad['id']; ?>" data-toggle="modal" data-target="#modalEditarActividad" idActividad="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>

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

                            <td contenteditable="true" class="celda-observacion" data-id="<?= $value['id']; ?>"><?= $value['observacion']; ?></td>

                            <td>
                            <div class="btn-group"> 

                                <!--<button class="btn btn-warning btnEditarActividad" 
                                    idActividad="<?php echo $value["id"]; ?>">
                                    <i class="fa fa-pencil"></i>
                                </button>-->
 
                                <button class="btn btn-warning btnEditarActividad" data-id="<?php echo $actividad['id']; ?>" data-toggle="modal" data-target="#modalEditarActividad" idActividad="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>
                                
                                <button class="btn btn-danger btnEliminarActividad" idActividad="<?php echo $value["id"]; ?>"><i class="fa fa-times"></i></button>
                            </div>
                            </td>
                        </tr>

                        <?php                      
                        endforeach; 
                        ?>

  
                    </tbody>

                </table>

            </div>
            <!-- FIN TABLA PARA ESCRITORIO -->

            <!-- CARDS PARA MÓVIL -->
            <div class="cards-actividades">

                <?php
                $item = null;
                $valor = null;
                $actividades = ControladorActividades::ctrMostrarActividades($item, $valor);
                $estadosActividades = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);

                foreach ($actividades as $key => $value):

                    // Obtener nombre del responsable
                    $itemUsuario = "id";
                    $valorUsuario = $value["id_user"];
                    $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
                    $nombreResponsable = $respuestaUsuario ? $respuestaUsuario["nombre"] : "Sin asignar";

                    // Obtener nombre del cliente
                    $itemCliente = "id";
                    $valorCliente = $value["id_cliente"];
                    $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
                    $nombreCliente = $respuestaCliente ? $respuestaCliente["nombre"] : "Sin cliente";

                    // Obtener color del estado
                    $estadoActual = $value["estado"] ?? "";
                    $colorEstado = "#999";
                    foreach($estadosActividades as $estado){
                        if(strcasecmp($estado["nombre"], $estadoActual) == 0){
                            $colorEstado = $estado["color"];
                            break;
                        }
                    }

                    $estadoBadge = !empty($estadoActual)
                        ? '<span class="badge" style="background-color: '.$colorEstado.'">'.ucfirst($estadoActual).'</span>'
                        : '<span class="text-muted">Sin estado</span>';

                    echo '<div class="card-actividad" data-tipo="'.strtolower($value["tipo"]).'" data-estado="'.strtolower($estadoActual).'">

                            <div class="card-actividad-header">
                                <div class="card-actividad-descripcion">
                                    '.$value["descripcion"].'
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-warning btn-xs btnEditarActividad" data-id="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarActividad" idActividad="'.$value["id"].'">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-xs btnEliminarActividad" idActividad="'.$value["id"].'">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="card-actividad-detalles">
                                <div class="card-actividad-fila">
                                    <div class="card-actividad-tipo">
                                        <i class="fa fa-info-circle"></i> '.ucfirst($value["tipo"]).'
                                    </div>
                                    <div class="card-actividad-estado">
                                        '.$estadoBadge.'
                                    </div>
                                </div>
                                <div class="card-actividad-fila">
                                    <div class="card-actividad-responsable">
                                        <i class="fa fa-user-plus"></i> '.$nombreResponsable.'
                                    </div>
                                    <div class="card-actividad-fecha">
                                        <i class="fa fa-calendar"></i> '.$value["fecha"].'
                                    </div>
                                </div>
                                <div class="card-actividad-fila">
                                    <div class="card-actividad-cliente">
                                        <i class="fa fa-user"></i> '.$nombreCliente.'
                                    </div>
                                </div>
                            </div>';

                    // Observación
                    if(!empty($value["observacion"])){
                        echo '<div class="card-actividad-observacion">
                                <i class="fa fa-pencil-square-o"></i> '.$value["observacion"].'
                              </div>';
                    }

                    echo '</div>';

                endforeach;
                ?>

            </div>
            <!-- FIN CARDS PARA MÓVIL -->

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
                            <input type="text" class="form-control" name="nuevaActividad" id="nuevaActividad" placeholder="Descripción de la actividad" required>
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
                                foreach($tiposModalAgregar as $tipoModal){
                                    echo '<option value="'.$tipoModal["nombre"].'">'.ucfirst($tipoModal["nombre"]).'</option>';
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
                                    echo'<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
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
                                foreach($estadosModalAgregar as $estadoModal){
                                    echo '<option value="'.$estadoModal["nombre"].'">'.ucfirst($estadoModal["nombre"]).'</option>';
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
                                    echo'<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
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
                            <input type="text" class="form-control" name="nuevaObservacion" id="nuevaObservacion" placeholder="Observaciones adicionales">
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
      $crearActividad -> ctrCrearActividad();

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
                                    <input type="text" class="form-control" name="editarActividad" id="editarActividad" placeholder="Descripción de la actividad" required>
                                    <input type="hidden" name="idActividad" value="<?php echo !empty($actividad['id']) ? $actividad['id'] : ''; ?>">
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
                                        foreach($tiposModalEditar as $tipoModal){
                                            echo '<option value="'.$tipoModal["nombre"].'">'.ucfirst($tipoModal["nombre"]).'</option>';
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
                                            echo'<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
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
                                        foreach($estadosModalEditar as $estadoModal){
                                            echo '<option value="'.$estadoModal["nombre"].'">'.ucfirst($estadoModal["nombre"]).'</option>';
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
                                            echo'<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
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
                                    <input type="text" class="form-control" name="editarObservacion" id="editarObservacion" placeholder="Observaciones adicionales">
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
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoEstadoNombre" placeholder="Nombre del estado *" required>
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
            <table class="table table-bordered table-striped">
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
                      <td>'.($key+1).'</td>
                      <td><span class="badge" style="background-color: '.$value["color"].'">'.ucfirst($value["nombre"]).'</span></td>
                      <td><input type="color" value="'.$value["color"].'" disabled style="width: 50px;"></td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarEstadoActividad" idEstado="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarEstadoActividad"><i class="fa fa-pencil"></i></button>
                        <button class="btn btn-danger btn-xs btnEliminarEstadoActividad" idEstado="'.$value["id"].'" nombreEstado="'.$value["nombre"].'"><i class="fa fa-times"></i></button>
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

#modalGestionarEstados + .modal-backdrop {
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
              <div class="row">
                <div class="col-md-9">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoTipoNombre" placeholder="Nombre del tipo *" required>
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
            <table class="table table-bordered table-striped">
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
                      <td>'.($key+1).'</td>
                      <td>'.ucfirst($value["nombre"]).'</td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarTipoActividad" idTipo="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarTipoActividad"><i class="fa fa-pencil"></i></button>
                        <button class="btn btn-danger btn-xs btnEliminarTipoActividad" idTipo="'.$value["id"].'" nombreTipo="'.$value["nombre"].'"><i class="fa fa-times"></i></button>
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



<!--Ruta actividades.js-->
<script src="vistas/js/actividades.js"></script>
<!-- Archivo actualizado para funcionar con badges en lugar de selects -->
<script src="assets/js/actividades.js"></script>


  <?php
    $eliminarActividad = new ControladorActividades();
    $eliminarActividad -> ctrEliminarActividad();

    $eliminarEstado = new ControladorEstadosActividades();
    $eliminarEstado -> ctrEliminarEstado();

    $crearEstado = new ControladorEstadosActividades();
    $crearEstado -> ctrCrearEstado();

    $editarEstado = new ControladorEstadosActividades();
    $editarEstado -> ctrEditarEstado();

    $eliminarTipo = new ControladorTiposActividades();
    $eliminarTipo -> ctrEliminarTipo();

    $crearTipo = new ControladorTiposActividades();
    $crearTipo -> ctrCrearTipo();

    $editarTipo = new ControladorTiposActividades();
    $editarTipo -> ctrEditarTipo();
  ?>

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