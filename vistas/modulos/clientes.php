<!-- Librería de estilos de Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<!-- Ruta contactos.css -->
<link rel="stylesheet" href="assets/css/clientes.css">


<?php
      $editarCliente = new ControladorClientes();
      $editarCliente -> ctrEditarCliente();
?>

  
  <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Administrar Clientes
      </h1>

      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Administrar Contactos</li>
      </ol>

    </section>

    <section class="content">

      <div class="box">

        <div class="box-header with-border">

          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCliente">
            
             Agregar Nuevo

          </button>

        </div>
    
  
        <?php
          $filtroEstatus1 = isset($_GET['filtroEstatus1']) ? $_GET['filtroEstatus1'] : '';  // Captura el valor del filtro de estatus si existe.

          // Aquí aplica el filtro de estatus desde el GET para obtener los clientes correctos
          $item = "estatus";
          $valor = $filtroEstatus1;
          $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
        ?>

        <h3 style="text-align: center; font-weight: bold; margin: 20px 0; color: #4A4A4A; padding-bottom: 10px; border-bottom: 2px solid #4A4A4A;">
          Lista de Clientes
        </h3>


        <div class="box-body table-responsive">

            <!-- filtro estatus-->
            <div class="clearfix mb-2">
              <div class="pull-right filtro-estatus-wrapper d-flex align-items-center" style="gap: 8px;">
                <label for="filtroEstatus1" class="control-label mb-0">Estados:</label>
                <select id="filtroEstatus1" onchange="filterTable1()" class="form-control filtro-estatus">
                  <option value="">Todos</option>
                  <option value="contactado" <?php if ($filtroEstatus1 == 'contactado') echo 'selected'; ?>>Contactado</option>
                  <option value="interesado" <?php if ($filtroEstatus1 == 'interesado') echo 'selected'; ?>>Interesado</option>
                  <option value="no interesado" <?php if ($filtroEstatus1 == 'no interesado') echo 'selected'; ?>>No Interesado</option>
                  <option value="en espera" <?php if ($filtroEstatus1 == 'en espera') echo 'selected'; ?>>En Espera</option>
                </select>
              </div>
            </div>

          <table class="table table-bordered table-striped tablas1">          
              
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
                <th>Documento</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Departamento</th>
                <th>Ciudad</th>
                <th>Dirección</th>
                <!--<th>Fecha de nacimiento</th>-->
                <th>Estado</th>
                <th><i class="fa fa-pencil"></i> <i class="fa fa-hand-o-down"></i> Notas</th>
                <th>Total compras</th>
                <th>Ultima compra</th>
                <th>Ingreso al sistema</th>
                <th>Acciones</th>
              </tr>             
            </thead>

              <tbody>

              <?php
                $item = null;
                $valor = null;
                $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
              ?>

                  <?php 
                  $key = 0;
                  foreach ($clientes as $value): 
                    if ($value["compras"] > 0): 
                      $estatusClass = "estatus-" . str_replace(" ", "-", strtolower($value["estatus"]));
                  ?>

              
                <tr>
                  <td><?php echo $key + 1; ?></td>
                  <td><?php echo $value["nombre"]; ?></td>
                  <td><?php echo $value["documento"]; ?></td>
                  <td><?php echo $value["email"]; ?></td>
                  <td><?php echo $value["telefono"]; ?></td>
                  <td><?php echo $value["departamento"]; ?></td>
                  <td><?php echo $value["ciudad"]; ?></td>
                  <td><?php echo $value["direccion"]; ?></td>
                  
                  <td>
                    <select class="form-control cambiarEstatus <?php echo $estatusClass; ?>" data-id="<?php echo $value["id"]; ?>">
                      <option value="nuevo" <?php if($value["estatus"] == "nuevo") echo "selected"; ?>>Nuevo</option>
                      <option value="contactado" <?php if($value["estatus"] == "contactado") echo "selected"; ?>>Contactado</option>
                      <option value="en espera" <?php if($value["estatus"] == "en espera") echo "selected"; ?>>En espera</option>
                      <option value="interesado" <?php if($value["estatus"] == "interesado") echo "selected"; ?>>Interesado</option>
                      <option value="no interesado" <?php if($value["estatus"] == "no interesado") echo "selected"; ?>>No interesado</option>
                    </select>
                  </td>
     
                   <td contenteditable="true" class="celda-notas" data-id="<?= $value['id']; ?>">
                    <?= $value['notas']; ?>
                  </td>

                  <td><?php echo $value["compras"]; ?></td>
                  <td><?php echo $value["ultima_compra"]; ?></td>
                  <td><?php echo $value["fecha"]; ?></td>
                  <td>
                    <div class="btn-group">
                      <button class="btn btn-warning btnEditarCliente" data-toggle="modal" data-target="#modalEditarCliente" idCliente="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-danger btnEliminarCliente" idCliente="<?php echo $value["id"]; ?>"><i class="fa fa-times"></i></button>
                    </div>
                  </td>
                </tr>
              <?php 
               endif;
            endforeach; 
            ?>

                
              </tbody>

          </table>

        </div>


          <!--=====================================
          2DA TABLA CLIENTES SIN VENTAS
          ======================================-->
          <br><br>

            <?php
              $filtroEstatus2 = isset($_GET['filtroEstatus2']) ? $_GET['filtroEstatus2'] : '';  // Captura el valor del filtro de estatus si existe.

              // Aquí aplica el filtro de estatus desde el GET para obtener los clientes correctos
              $item = "estatus";
              $valor = $filtroEstatus2;
              $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
            ?>

        <h3 style="text-align: center; font-weight: bold; margin: 20px 0; color: #4A4A4A; padding-bottom: 10px; border-bottom: 2px solid #4A4A4A;">
          Contactos sin Ventas
        </h3>    

            <div class="box-body table-responsive">

            <!-- filtro estatus -->
              <div class="clearfix mb-2">
              <div class="pull-right filtro-estatus-wrapper d-flex align-items-center" style="gap: 8px;">
                <label for="filtroEstatus2" class="control-label mb-0">Estados:</label>
                <select id="filtroEstatus2" onchange="filterTable2()" class="form-control filtro-estatus">
                  <option value="">Todos</option>
                  <option value="contactado" <?php if ($filtroEstatus2 == 'contactado') echo 'selected'; ?>>Contactado</option>
                  <option value="interesado" <?php if ($filtroEstatus2 == 'interesado') echo 'selected'; ?>>Interesado</option>
                  <option value="no interesado" <?php if ($filtroEstatus2 == 'no interesado') echo 'selected'; ?>>No Interesado</option>
                  <option value="en espera" <?php if ($filtroEstatus2 == 'en espera') echo 'selected'; ?>>En Espera</option>
                </select>
              </div>
            </div>


              <table class="table table-bordered table-striped tablas2">          
                  
                <thead>
                  <tr>
                    <th style="width: 10px">#</th>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Departamento</th>
                    <th>Ciudad</th>
                    <th>Dirección</th>
                    <!--<th>Fecha de nacimiento</th>-->
                    <th>Estado</th>
                    <th><i class="fa fa-pencil"></i> <i class="fa fa-hand-o-down"></i> Notas</th>
                    <!--<th>Total compras</th>-->
                    <!--<th>Ultima compra</th>-->
                    <th>Ingreso al sistema</th>
                    <th>Acciones</th>
                  </tr>             
                </thead>

                  <tbody>

                  <?php
                    $item = null;
                    $valor = null;
                    $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
                  ?>

                    <?php 
                    $key = 0;
                    foreach ($clientes as $value): 
                      if ($value["compras"] == 0): 
                        $estatusClass = "estatus-" . str_replace(" ", "-", strtolower($value["estatus"]));
                    ?>

                    <tr>
                      <td><?php echo $key + 1; ?></td>
                      <td><?php echo $value["nombre"]; ?></td>
                      <td><?php echo $value["documento"]; ?></td>
                      <td><?php echo $value["email"]; ?></td>
                      <td><?php echo $value["telefono"]; ?></td>
                      <td><?php echo $value["departamento"]; ?></td>
                      <td><?php echo $value["ciudad"]; ?></td>
                      <td><?php echo $value["direccion"]; ?></td>
                      
                      <td>
                        <select class="form-control cambiarEstatus <?php echo $estatusClass; ?>" data-id="<?php echo $value["id"]; ?>">
                          <option value="nuevo" <?php if($value["estatus"] == "nuevo") echo "selected"; ?>>Nuevo</option>
                          <option value="contactado" <?php if($value["estatus"] == "contactado") echo "selected"; ?>>Contactado</option>
                          <option value="en espera" <?php if($value["estatus"] == "en espera") echo "selected"; ?>>En espera</option>
                          <option value="interesado" <?php if($value["estatus"] == "interesado") echo "selected"; ?>>Interesado</option>
                          <option value="no interesado" <?php if($value["estatus"] == "no interesado") echo "selected"; ?>>No interesado</option>
                        </select>
                      </td>
        
                      <td contenteditable="true" class="celda-notas" data-id="<?= $value['id']; ?>">
                        <?= $value['notas']; ?>
                      </td>

                      
                      <td><?php echo $value["fecha"]; ?></td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-warning btnEditarCliente" data-toggle="modal" data-target="#modalEditarCliente" idCliente="<?php echo $value["id"]; ?>"><i class="fa fa-pencil"></i></button>
                          <button class="btn btn-danger btnEliminarCliente" idCliente="<?php echo $value["id"]; ?>"><i class="fa fa-times"></i></button>
                        </div>
                      </td>
                    </tr>
                  <?php 
                 endif;
                 endforeach; 
                 ?>

                    
                  </tbody>

              </table>

            </div>
            <!--fin tabla-->


      </div>

    </section>

  </div>


<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->
  
<!-- Modal -->
<div id="modalAgregarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar cliente</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">
        
        <div class="box-body">

          <!-- entrada para nombre -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-user"></i></span>

              <input type="text" class="form-control input-lg" name="nuevoCliente" id="nuevoCliente" placeholder="Ingresar nombre" required>

             </div>

           </div>


            <!-- entrada para documento ID -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-key"></i></span>

              <input type="number" min="0" max="9999999999" class="form-control input-lg" name="nuevoDocumentoId" placeholder="Ingresar documento" required>

             </div>

           </div>


           <!-- entrada para telefono -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-phone"></i></span>

              <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>

             </div>

           </div>


            <!-- entrada para Email -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

              <!--<input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email" required>-->
              <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">

             </div>

           </div>


           <!-- entrada para departamento -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-building"></i></span>

            <input type="text" class="form-control input-lg" name="nuevoDepartamento" placeholder="Ingresar departamento" required>

           </div>

         </div>


         <!-- entrada para ciudad -->
            
         <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

            <input type="text" class="form-control input-lg" name="nuevoCiudad" placeholder="Ingresar Ciudad" required>

           </div>

         </div>


           <!-- entrada para la direccion -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-home"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" required>

             </div>

           </div>


           <!-- entrada para estatus -->
           <input type="hidden" name="nuevoEstatus" value="nuevo">


           <!-- Estatus 
            <div class="form-group"> 
              <label for="editarEstatus">Estatus</label>
              <select class="form-control" name="editarEstatus" id="editarEstatus">
                <option value="contactado">Contactado</option>
                <option value="en espera">En espera</option>
                <option value="interesado">Interesado</option>
                <option value="no interesado">No interesado</option>
              </select>
            </div>
            -->


           <!-- entrada para la fecha naciminiento -->
            
           <!--
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>

             </div>

           </div>
           -->


            <!-- entrada para notas -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaNota" placeholder="Ingresar Nota">

             </div>

           </div>

          

         </div>  

       </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cliente</button>

        </div>

     </form>


     <?php

      $crearCliente = new ControladorClientes();
      $crearCliente -> ctrCrearCliente();

     ?>

    </div>

  </div>

</div>



<!--==========================================================================
MODAL EDITAR CLIENTE
===========================================================================-->
  
<!-- Modal -->
<div id="modalEditarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Editar cliente</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">
        
        <div class="box-body">

          <!-- entrada para nombre -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-user"></i></span>

              <input type="text" class="form-control input-lg" name="editarCliente" id="editarCliente" required>
              <input type="hidden" id="idCliente" name="idCliente">

             </div>

           </div>


            <!-- entrada para documento ID -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-key"></i></span>

              <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId" id="editarDocumentoId" required>

             </div>

           </div>


           <!-- entrada para telefono -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-phone"></i></span>

            <input type="text" class="form-control input-lg" name="editarTelefono"  id="editarTelefono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>

           </div>

         </div>


           <!-- entrada para Email -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

              <!--<input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" required>-->
              <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail">

             </div>

           </div>


           <!-- entrada para la departamento -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-building"></i></span>

            <input type="text" class="form-control input-lg" name="editarDepartamento" id="editarDepartamento" required>

           </div>

         </div>


         <!-- entrada para la ciudad -->
            
         <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

            <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad" required>

           </div>

         </div>


           <!-- entrada para la direccion -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-home"></i></span>

              <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion" required>

             </div>

           </div>


           <!-- entrada para la fecha naciminiento -->
            
            <!-- 
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

              <input type="text" class="form-control input-lg" name="editarFechaNacimiento" id="editarFechaNacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>

             </div>

           </div>
           -->


           <!-- entrada para nota -->
            
           <div class="form-group">
          
          <div class="input-group">
            
            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

            <input type="text" class="form-control input-lg" name="editarNota" id="editarNota">

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

 <?php

 $eliminarCliente = new ControladorClientes();
 $eliminarCliente -> ctrEliminarCliente();

 ?>



<!-- jQuery 
<script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
 Datatable
<script src="vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
-->

<!-- Choices.js para Campo estatus-->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<!--Ruta Clientes.js-->
<script src="assets/js/clientes.js"></script>



<!--sirve para dar estilos al select-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

