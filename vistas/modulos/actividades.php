<?php
$editarActividad = new ControladorActividades();
$editarActividad -> ctrEditarActividad();
?>

<div class="content-wrapper">
    <section class="content-header">

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


            <div class="box-body table-responsive">

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
                            <td><?php echo $value["estado"]; ?></td>

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

                            <td><?php echo $value["observacion"]; ?></td>

                            <td>
                            <div class="btn-group">
                                <button class="btn btn-warning btnEditarActividad" data-toggle="modal" data-target="#modalEditarActividad" idActividad="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>
                                
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
                            
                            <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                            <input type="text" class="form-control input-lg" name="nuevaActividad" id="nuevaActividad" placeholder="Ingresar descripción" required>

                        </div>

                    </div>

                <!-- entrada para tipo -->
                    
                    <div class="form-group">
                    
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                            <input type="text" class="form-control input-lg" name="nuevoTipo" id="nuevoTipo" placeholder="Ingresar Tipo" required>

                        </div>

                    </div>

                 <!-- entrada para usuario -->

                    <div class="form-group">
            
                        <div class="input-group">
                    
                            <span class="input-group-addon"><i class="fa fa-th"></i></span>

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
                                
                                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                                <input type="date" class="form-control input-lg" name="nuevaFecha" id="nuevaFecha" placeholder="Ingresar Fecha" required>

                            </div>

                        </div>

                        <!-- entrada para estado -->
                    
                        <div class="form-group">
                    
                            <div class="input-group">
                                
                                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                                <input type="text" class="form-control input-lg" name="nuevoEstado" id="nuevoEstado" placeholder="Ingresar Estado" required>

                            </div>

                        </div>


                        <!-- entrada para seleccionar cliente -->

                            <div class="form-group">
                        
                                <div class="input-group">
                            
                                <span class="input-group-addon"><i class="fa fa-th"></i></span>

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
                                        
                                        <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

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
                    
                    <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                    <input type="text" class="form-control input-lg" name="editarActividad" id="editarActividad" placeholder="Ingresar descripción" required>
                    <input type="hidden" id="idActividad" name="idActividad">

                </div>

            </div>

            <!-- entrada para tipo -->
                
                <div class="form-group">
                
                    <div class="input-group">
                        
                        <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                        <input type="text" class="form-control input-lg" name="editarTipo" id="editarTipo" required>

                    </div>

                </div>

                             
            <!-- entrada para seleccionar usuario -->

            <div class="form-group">
            
            <div class="input-group">
        
                <span class="input-group-addon"><i class="fa fa-th"></i></span>

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


                <!-- entrada para fecha -->
                
                    <div class="form-group">
                        
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                            <input type="date" class="form-control input-lg" name="editarFecha" id="editarFecha" placeholder="Ingresar Fecha" required>


                        </div>

                    </div>

                    <!-- entrada para estado -->
                
                    <div class="form-group">
                
                        <div class="input-group">
                            
                            <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                            <input type="text" class="form-control input-lg" name="editarEstado" id="editarEstado" placeholder="Ingresar Estado" required>

                        </div>

                    </div>


                    <!-- entrada para seleccionar cliente -->
                            
                    <div class="form-group">
                        
                        <div class="input-group">
                    
                        <span class="input-group-addon"><i class="fa fa-th"></i></span>

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
                                    
                                    <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

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



  <!--Ruta actividades.js-->
  <script src="vistas/js/actividades.js"></script>


  <?php
    $eliminarActividad = new ControladorActividades();
    $eliminarActividad -> ctrEliminarActividad();
  ?>






