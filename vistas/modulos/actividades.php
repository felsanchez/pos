<!-- Librería de estilos de Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<!-- Ruta actividades.css -->
<link rel="stylesheet" href="assets/css/actividades.css">

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css' rel='stylesheet' />


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
                <option value="actividad" <?php if ($filtroTipo == 'actividad') echo 'selected'; ?>>Actividad</option>
                <option value="Llamada Telefónica" <?php if ($filtroTipo == 'Llamada Telefónica') echo 'selected'; ?>>Llamada Telefónica</option>
                <option value="reunión" <?php if ($filtroTipo == 'reunión') echo 'selected'; ?>>Reunión</option>
                <option value="enviar mensaje" <?php if ($filtroTipo == 'enviar mensaje') echo 'selected'; ?>>Enviar Mensaje</option>
                <option value="evento" <?php if ($filtroTipo == 'evento') echo 'selected'; ?>>Evento</option>
                <option value="seguimiento" <?php if ($filtroTipo == 'seguimiento') echo 'selected'; ?>>Seguimiento</option>
                <option value="promociones" <?php if ($filtroTipo == 'promociones') echo 'selected'; ?>>promociones</option>
                <option value="soporte" <?php if ($filtroTipo == 'soporte') echo 'selected'; ?>>Soporte</option>
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
                        <option value="programada">Programada</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en revisión">En Revisión</option>
                    </select>
                </div>
            </div>



                <table class="table table-bordered table-striped tablas" style="width: 95%">
                    
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
                        ?>

                            <?php 
                            foreach ($actividades as $key => $value):                       
                            ?>

                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo $value["descripcion"]; ?></td>
                            
                            <td>
                                <select class="form-control cambiarTipo" data-id="<?php echo $value["id"]; ?>">
                                    <option value="actividad" <?php if($value["tipo"] == "actividad") echo "selected"; ?>>Actividad</option>
                                    <option value="Llamada Telefónica" <?php if($value["tipo"] == "Llamada Telefónica") echo "selected"; ?>>Llamada Telefónica</option>
                                    <option value="reunión" <?php if($value["tipo"] == "reunión") echo "selected"; ?>>Reunión</option>
                                    <option value="enviar mensaje" <?php if($value["tipo"] == "enviar mensaje") echo "selected"; ?>>Enviar Mensaje</option>
                                    <option value="evento" <?php if($value["tipo"] == "evento") echo "selected"; ?>>Evento</option>
                                    <option value="seguimiento" <?php if($value["tipo"] == "seguimiento") echo "selected"; ?>>Seguimiento</option>
                                    <option value="promociones" <?php if($value["tipo"] == "promociones") echo "selected"; ?>>Promociones</option>
                                    <option value="soporte" <?php if($value["tipo"] == "soporte") echo "selected"; ?>>Soporte</option>
                                </select>
                            </td>

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
                            <select class="form-control cambiarEstado" data-id="<?php echo $value["id"]; ?>">
                                <option value="programada" <?php if($value["estado"] == "programada") echo "selected"; ?>>Programada</option>
                                <option value="completada" <?php if($value["estado"] == "completada") echo "selected"; ?>>Completada</option>
                                <option value="cancelada" <?php if($value["estado"] == "cancelada") echo "selected"; ?>>Cancelada</option>
                                <option value="pendiente" <?php if($value["estado"] == "pendiente") echo "selected"; ?>>Pendiente</option>
                                <option value="en-revision" <?php if($value["estado"] == "en-revision") echo "selected"; ?>>En Revisión</option>
                            </select>
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

        </div>

        <!--Calendario-->
        <div class="calendar-container">
        <div id="calendar" style="width: 60%;"></div>
        </div>


    </section>

  </div>



<!--=====================================
MODAL AGREGAR actividad
======================================-->
  
<!-- Modal -->
<div id="modalAgregarActividad" class="modal fade" role="dialog">

  <div class="modal-dialog">

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


                <!-- entrada para la descripcion -->
                    
                    <div class="form-group">
                    
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-tasks"></i></span>

                            <input type="text" class="form-control input-lg" name="nuevaActividad" id="nuevaActividad" placeholder="Ingresar descripción" required>

                        </div>

                    </div>

                <!-- entrada para tipo -->
                    
                   <!-- <div class="form-group">
                    
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-filter"></i></span>

                            <input type="text" class="form-control input-lg" name="nuevoTipo" id="nuevoTipo" placeholder="Ingresar Tipo" required>

                        </div>

                    </div>
                    -->

                    <!-- entrada para tipo -->
                    <input type="hidden" name="nuevoTipo" value="actividad">


                 <!-- entrada para usuario -->

                    <div class="form-group">
            
                        <div class="input-group">
                    
                            <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>

                            <select class="form-control input-lg" id="nuevoUsuario" name="nuevoUsuario" required>
                        
                                <option value="">Seleccionar Responsable</option>

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

                    <!-- entrada para fecha -->
                    
                        <div class="form-group">
                            
                            <div class="input-group">
                                
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                                <input type="datetime-local" class="form-control input-lg" name="nuevaFecha" id="nuevaFecha" placeholder="Ingresar Fecha" required>

                            </div>

                        </div>


                        <!-- entrada para estado -->
                    <!--
                        <div class="form-group">
                    
                            <div class="input-group">
                                
                                <span class="input-group-addon"><i class="fa fa-check-square-o"></i></span>

                                <input type="text" class="form-control input-lg" name="nuevoEstado" id="nuevoEstado" placeholder="Ingresar Estado" required>

                            </div>

                        </div>
                            -->

                        <!-- entrada para tipo -->
                        <input type="hidden" name="nuevoEstado" value="actividad">


                        <!-- entrada para seleccionar cliente -->

                            <div class="form-group">
                        
                                <div class="input-group">
                            
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>

                                    <select class="form-control input-lg" id="nuevoCliente" name="nuevoCliente" required>
                            
                                    <option value="">Seleccionar Cliente</option>

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


                            <!-- entrada para observacion -->
                    
                                <div class="form-group">
                                    
                                    <div class="input-group">
                                        
                                        <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                                        <input type="text" class="form-control input-lg" name="nuevaObservacion" id="nuevaObservacion" placeholder="Ingresar Observación">

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

    <div class="modal-dialog">

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

           <!-- entrada para la descripcion -->
                    
           <div class="form-group">
                    
                <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-tasks"></i></span>

                    <input type="text" class="form-control input-lg" name="editarActividad" id="editarActividad" placeholder="Ingresar descripción" required>
                    <input type="hidden" name="idActividad" value="<?php echo !empty($actividad['id']) ? $actividad['id'] : ''; ?>">

                </div>

            </div>

            <!-- entrada para tipo -->
               <!--
                <div class="form-group">
                
                    <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-filter"></i></span>

                        <input type="text" class="form-control input-lg" name="editarTipo" id="editarTipo" required>

                    </div>

                </div>
                                    -->
                <input type="hidden" name="editarTipo" id="editarTipo">

                             
            <!-- entrada para seleccionar usuario -->

            <div class="form-group">
            
                <div class="input-group">
            
                    <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>

                    <select class="form-control input-lg" id="editarUsuario" name="editarUsuario" required>
                
                        <option value="">Seleccionar Responsable</option>

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


                <!-- entrada para fecha -->
                <!--
                    <div class="form-group">
                        
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                            <input type="datetime-local" class="form-control input-lg" name="editarFecha" id="editarFecha">

                        </div>

                    </div>
                    -->



                    

                    


                    <!-- entrada para estado -->
                        <!--
                    <div class="form-group">
                
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-check-square-o"></i></span>

                            <input type="text" class="form-control input-lg" name="editarEstado" id="editarEstado" placeholder="Ingresar Estado" required>

                        </div>

                    </div>
                    -->
                    <input type="hidden" name="editarEstado" id="editarEstado">
                   

                    <!-- entrada para seleccionar cliente -->
                            
                    <div class="form-group">
                        
                        <div class="input-group">
                    
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>

                                <select class="form-control input-lg" id="editarCliente" name="editarCliente" required>
                        
                                    <option value="">Seleccionar Cliente</option>

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


                        <!-- entrada para observacion -->
                
                            <div class="form-group">
                                
                                <div class="input-group">
                                    
                                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                                    <input type="text" class="form-control input-lg" name="editarObservacion" id="editarObservacion" placeholder="Ingresar Observación">

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

<button onclick="$('#modalEditarActividad').modal('show')" class="btn btn-danger">Abrir modal manual</button>


<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
<!-- Idioma Esp FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/es.js"></script>

  <!-- Choices.js para Campo estatus-->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<!-- ✅ jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Popper.js (necesario para Bootstrap 4) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>

<!-- ✅ Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


  <!--Ruta actividades.js-->
  <script src="vistas/js/actividades.js"></script>
  <script src="assets/js/actividades.js"></script>

  <!--sirve para dar estilos al select-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>-->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>


  <?php
    $eliminarActividad = new ControladorActividades();
    $eliminarActividad -> ctrEliminarActividad();
  ?>


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




