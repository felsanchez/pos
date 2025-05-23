<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

  <div class="content-wrapper">
    <section class="content-header">

      <h1>
        Administrar productos
      </h1>

      <ol class="breadcrumb">
        <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Administrar productos</li>
      </ol>

    </section>

    <section class="content">

      <div class="box">

        <div class="box-header with-border">

          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProducto">
            
             Agregar producto

          </button>

        </div>


       <div class="box-body table-responsive">

          <table class="table table-bordered table-striped tablaProductos" style="width: 95%">
              
            <thead>
              <tr>
                <th style="width: 5px">#</th>
                <th>Imagen</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio de Compra</th>
                <th>Precio de Venta</th>
                <th>Agregado</th>
                <th>Acciones</th>
              </tr>             
            </thead>
          
          </table>

           <input type="hidden" value="<?php echo $_SESSION['perfil']; ?>" class="perfilUsuario" id="perfilOculto">

        </div>

      </div>

    </section>

  </div>



<!--=====================================
MODAL AGREGAR PRODUCTO
======================================-->
  
<!-- Modal -->
<div id="modalAgregarProducto" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar producto</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">
        
        <div class="box-body">

           <!-- entrada para seleccionar categoria -->

              <div class="form-group">
          
                <div class="input-group">
            
                   <span class="input-group-addon"><i class="fa fa-th"></i></span>

                     <select class="form-control input-lg" id="nuevaCategoria" name="nuevaCategoria" required>
              
                      <option value="">Seleccionar categoría</option>

                      <?php

                        $item = null;
                        $valor = null;
                        $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

                        foreach ($categorias as $key => $value) {
                      
                          echo'<option value="'.$value["id"].'">'.$value["categoria"].'</option>';   
                        }

                      ?>

                      </select>

                 </div>

              </div>

          <!-- entrada para el codigo -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-code"></i></span>

              <input type="text" class="form-control input-lg" id="nuevoCodigo" name="nuevoCodigo" placeholder="Código" readonly required>

             </div>

           </div>

           <!-- entrada para la descripcion -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaDescripcion" id="nuevaDescripcion" placeholder="Ingresar descripción" required>

             </div>

           </div>


               <!-- entrada para el stock -->
            
              <div class="form-group">
          
                <div class="input-group">
              
                  <span class="input-group-addon"><i class="fa fa-check"></i></span>

                  <input type="number" class="form-control input-lg" name="nuevoStock" min="0" placeholder="Stock" required>

                </div>

              </div>


      <!-- entrada para el precio de compra -->
            
  <div class="form-group row">

        <div class="col-xs-6">
          
            <div class="input-group">
              
                  <span class="input-group-addon"><i class="fa fa-arrow-up"></i></span>

                  <input type="number" class="form-control input-lg" id="nuevoPrecioCompra" name="nuevoPrecioCompra" min="0" placeholder="Precio de Compra" required>

             </div>

        </div>

      <!-- entrada para el precio de venta -->

        <div class="col-xs-6">
          
           <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span>

               <input type="number" class="form-control input-lg" id="nuevoPrecioVenta" name="nuevoPrecioVenta" min="0" placeholder="Precio de Venta" required>

            </div>

            <br>

            <!-- checkbox para porcentaje -->

            <div class="col-xs-6">
              
              <div class="form-group">
                
                <label>
                  
                  <input type="checkbox" class="minimal porcentaje" checked>
                  Utilizar porcentaje

                </label>

              </div>

            </div>

            <!-- entrada para porcentaje -->

            <div class="col-xs-6" style="padding:0">
              
              <div class="input-group">
                
                <input type="number" class="form-control input-lg nuevoPorcentaje" min="0" value="40" required>

                <span class="input-group-addon"><i class="fa fa-percent"></i></span>

              </div>

            </div>

        </div>

   </div>

                
           <!-- entrada para imagen -->

           <div class="form-group">
                    
              <div class="panel">SUBIR IMAGEN</div>

                 <input type="file" class="nuevaImagen" name="nuevaImagen">

                 <p class="help-block">Peso máximo de la imagen 2MB</p>

                 <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">

              </div>

           </div>  

       </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar producto</button>

        </div>

     </form>


     <?php

      $crearProducto = new ControladorProductos();
      $crearProducto -> ctrCrearProducto();

     ?>

    </div>


  </div>

</div>



<!--==========================================================================
MODAL EDITAR PRODUCTO
============================================================================-->
  
<!-- Modal -->
<div id="modalEditarProducto" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Editar producto</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">
        
        <div class="box-body">

          <!-- entrada para el codigo -->
              
          <div class="form-group">
            
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-code"></i></span>

              <input type="text" class="form-control input-lg" id="editarCodigo" name="editarCodigo" readonly required>

            </div>

          </div>

           <!-- entrada para seleccionar categoria -->
            <!--
              <div class="form-group">
                <div class="input-group">
                   <span class="input-group-addon"><i class="fa fa-th"></i></span>
                     <select class="form-control input-lg" name="editarCategoria" readonly required>
                      <option id="editarCategoria"></option>
                      </select>
                 </div>
              </div>
              -->
                      

              <!-- entrada para seleccionar categoria -->
              <div class="form-group">
            
                <div class="input-group">
            
                    <span class="input-group-addon"><i class="fa fa-th"></i></span>

                    <select class="form-control input-lg" id="editarCategoria" name="editarCategoria">
                
                        <option value="">Editar Categoria</option>

                        <?php
                          $item = null;
                          $valor = null;
                          $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

                          foreach ($categorias as $key => $value) {

                              $selected = ($producto["id_categoria"] == $value["id"]) ? "selected" : "";

                              echo'<option value="'.$value["id"].'" '.$selected.'>'.$value["categoria"].'</option>';   
                          }
                          ?>

                    </select>

                </div>
              </div>


           <!-- entrada para la descripcion -->
            
          <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

              <input type="text" class="form-control input-lg" id="editarDescripcion" name="editarDescripcion" required>

             </div>

           </div>


               <!-- entrada para el stock -->
            
              <div class="form-group">
          
                <div class="input-group">
              
                  <span class="input-group-addon"><i class="fa fa-check"></i></span>

                  <input type="number" class="form-control input-lg" id="editarStock" name="editarStock" min="0" required>

                </div>

              </div>


      <!-- entrada para el precio de compra -->
            
  <div class="form-group row">

        <div class="col-xs-6">
          
            <div class="input-group">
              
                  <span class="input-group-addon"><i class="fa fa-arrow-up"></i></span>

                  <input type="number" class="form-control input-lg" id="editarPrecioCompra" name="editarPrecioCompra" min="0" required>

             </div>

        </div>

      <!-- entrada para el precio de venta -->

        <div class="col-xs-6">
          
           <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span>

               <input type="number" class="form-control input-lg" id="editarPrecioVenta" name="editarPrecioVenta" min="0" readonly required>

            </div>

            <br>

            <!-- checkbox para porcentaje -->

            <div class="col-xs-6">
              
              <div class="form-group">
                
                <label>
                  
                  <input type="checkbox" class="minimal porcentaje" checked>
                  Utilizar porcentaje

                </label>

              </div>

            </div>

            <!-- entrada para porcentaje -->

            <div class="col-xs-6" style="padding:0">
              
              <div class="input-group">
                
                <input type="number" class="form-control input-lg nuevoPorcentaje" min="0" value="40" required>

                <span class="input-group-addon"><i class="fa fa-percent"></i></span>

              </div>

            </div>

        </div>

   </div>

                
           <!-- entrada para imagen -->

           <div class="form-group">
                    
              <div class="panel">SUBIR IMAGEN</div>

                 <input type="file" class="nuevaImagen" name="editarImagen">

                 <p class="help-block">Peso máximo de la imagen 2MB</p>

                 <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">

                 <input type="hidden" name="imagenActual" id="imagenActual">

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


     <?php

      $editarProducto = new ControladorProductos();
      $editarProducto -> ctrEditarProducto();

     ?>


    </div>


  </div>

</div>


  <?php

    $eliminarProducto = new ControladorProductos();
    $eliminarProducto -> ctrEliminarProducto();

  ?>