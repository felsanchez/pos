
<div class="content-wrapper">
    <section class="content-header">

      <h1>
        Venta
      </h1>

      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Editar venta</li>
      </ol>

    </section>

    
    <section class="content">

      <div class="row">

            <!--=====================================
            EL FORMULARIO
            ======================================-->
            
            <div class="col-lg-9 col-xs-12" style="float:none; margin:0 auto;">

              <div class="box box-success">

                <div class="box-header with-border"></div>

                <form role="form" method="post" class="formularioVenta">

                <div class="box-body">

                    <div class="box">

                       <?php

                          $item = "id";
                          $valor = $_GET["idVenta"];

                          $venta = ControladorVentas::ctrMostrarVentas($item, $valor);


                          $itemUsuario = "id";
                          $valorUsuario = $venta["id_vendedor"];

                          $vendedor = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);

                          
                          $itemCliente = "id";
                          $valorCliente = $venta["id_cliente"];

                          $cliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

                          //$porcentajeImpuesto = $venta["impuesto"] * 100 / $venta["neto"];

                          $porcentajeImpuesto = 0; // Inicializamos el porcentaje de impuesto en 0 por defecto
                            if ($venta["neto"] != 0) {
                              $porcentajeImpuesto = $venta["impuesto"] * 100 / $venta["neto"];
                            }

                       ?>

                      <!--=====================================********************************************************************************************
                      ENTRADA DEL VENDEDOR
                      ======================================**********************************************************************************************-->

                      <div class="form-group">

                        <div class="input-group col-xs-9">

                          <span class="input-group-addon">Usuario</span>                          
                          <input type="text" class="form-control" id="nuevoVendedor" value="<?php echo $vendedor["nombre"]; ?>" readonly>
                          <span class="input-group-addon"><i class="fa fa-user"></i></span>

                          <input type="hidden" name="idVendedor" value="<?php echo $vendedor["id"]; ?>">
                          
                        </div>
                        
                      </div>

                      <!--=====================================
                      ENTRADA DEL CODIGO
                      ======================================-->

                      <div class="form-group">

                        <div class="input-group col-xs-9">

                          <span class="input-group-addon">Código</span>
                          <input type="text" class="form-control" id="nuevaVenta" name="editarVenta" value="<?php echo $venta["codigo"]; ?>" readonly>
                          <span class="input-group-addon"><i class="fa fa-key"></i></span>
                          
                        </div>
                        
                      </div>

                      <!--=====================================
                      ENTRADA DEL CLIENTE
                      ======================================-->

                      
                      <?php
                        $cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
                        ?>
                      
                    <div class="form-group">
                    <div class="input-group col-xs-9">

                        <span class="input-group-addon">Cliente</span>
                        <input type="text" class="form-control" value="<?php echo $cliente["nombre"]; ?>" readonly>
                        <span class="input-group-addon"><i class="fa fa-users"></i></span>
                        
                        <!-- Campo oculto con el ID del cliente -->
                        <input type="hidden" name="seleccionarCliente" value="<?php echo $cliente["id"]; ?>">
                        <br>

                    </div>
                    </div>


                      <!--=====================================
                      ENTRADA PARA AGREGAR PRODUTO
                      ======================================-->

                      <div class="form-group row nuevoProducto">

                        <?php

                          $listaProducto = json_decode($venta["productos"], true);


                          foreach ($listaProducto as $key => $value) {

                            $item = "id";
                            $valor = $value["id"];
                            $orden = "id";

                            $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

                            $stockAntiguo = $respuesta["stock"] + $value["cantidad"];
                            
                            echo '<div class="row" style="padding:5px 15px">

                          
                            <div class="col-xs-6" style="padding-right:0px">
                              <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-shopping-cart"></i></span>
                                 <input type="text" class="form-control nuevaDescripcionProducto" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" readonly required>                                          
                              </div>
                            </div>


                            <div class="col-xs-3">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                                    <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="'.$value["cantidad"].'" stock="'.$stockAntiguo.'" nuevoStock="'.$value["stock"].'" readonly>
                                </div> 
                            </div>


                            <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">                                       
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>                                        
                                    <input type="text" class="form-control nuevoPrecioProducto" precioReal="'.$respuesta["precio_venta"].'" name="nuevoPrecioProducto" value="'.number_format($value["total"], 0, '', '.').'" readonly required>
                                </div>
                            </div>

                    </div>';

                          }


                        ?>

                      </div>

                       <input type="hidden" id="listaProductos" name="listaProductos">

                       <!--=====================================
                       BOTON PARA AGREGAR PRODUCTO
                       ======================================-->

                       <button type="button" class="btn btn-default hidden-lg btnAgregarProducto">Agregar producto</button>

                       <hr>

                       <div class="row">

                        <!--=====================================
                        ENTRADA IMPUESTOS Y TOTAL
                        ======================================-->
                         
                          <div class="col-xs-8">
                           
                             <table class="table">                     

                               <thead>
                                 
                                 <tr>                             
                                   <th>Impuesto</th>
                                   <th>Total</th>
                                 </tr>

                               </thead>

                               <tbody>
                                 
                                 <tr>
                                   <td style="width: 20%">
                                     
                                     <div class="input-group"> 
                                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>                                    
                                        <input type="number" class="form-control input-lg" min="0" id="nuevoImpuestoVenta" name="nuevoImpuestoVenta" value="<?php echo $porcentajeImpuesto; ?>" readonly>
                                        <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" value="<?php echo $venta["impuesto"]; ?>" required>
                                        <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto" value="<?php echo $venta["neto"]; ?>" required>
                                     </div>

                                   </td>

                                   <td style="width: 40%">
                                     
                                     <div class="input-group">  
                                        <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>                              
                                        <input type="text" class="form-control input-lg" id="nuevoTotalVenta" name="nuevoTotalVenta" total="" value="<?php echo $venta["total"]; ?>" readonly required>

                                        <input type="hidden" name="totalVenta" value="<?php echo $venta["total"]; ?>" id="totalVenta">

                                     </div>

                                   </td>

                                 </tr>

                               </tbody>

                             </table>


                           </div>
                       
                        </div>

                        <hr>

                        <!--=====================================
                        ENTRADA METODO DE PAGO
                        ======================================-->

                        <div class="form-group row">                          
                          <div class="col-xs-4" style="padding-right:0px">
                            <div class="input-group">

                                <span class="input-group-addon">Método de Pago</span>
                                <input type="text" class="form-control" id="metodo_pago" name="metodo_pago" value="<?php echo $venta["metodo_pago"]; ?>" readonly>
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 

                            </div>
                          </div>
                        </div>

                        <br>                       
                        
                   </div>
                  
                </div>

                 <!--=====================================**************************************************************************************************
                        FIN FORM
                        ======================================-->


                <div class="box-footer">

                  <!--<button type="submit" class="btn btn-primary pull-right">Guardar cambios</button>

                  <button  class="btn btn-danger pull-left" onclick="location.href='ventas'">Cancelar</button>
                -->                 
                </div>
                
               </form>

               <button  class="btn btn-primary btn-lg pull-right" onclick="location.href='ventas'">Regresar</button>

               


             <?php

              $editarVenta = new ControladorVentas();
              $editarVenta -> ctrEditarVenta();

             ?>

             
           </div>
        
              
        </div>   


            <!--=====================================
            LA TABLA DE PRODUCTOS
            ======================================-->
            <!--

            <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

              <div class="box box-warning">

                <div class="box-header with-border"></div>

                <div class="box-body">
                  
                  <table class="table table-bordered table-striped dt-responsive tablaVentas">

                    <thead>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                      </tr>             
                    </thead>

                   
                  </table>

                </div>
                
              </div>
              
            </div>
        -->



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

              <input type="text" class="form-control input-lg" name="nuevoCliente" placeholder="Ingresar nombre" required>

             </div>

           </div>


            <!-- entrada para documento ID -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-key"></i></span>

              <input type="number" min="0" class="form-control input-lg" name="nuevoDocumentoId" placeholder="Ingresar documento" required>

             </div>

           </div>


           <!-- entrada para Email -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

              <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email" required>

             </div>

           </div>


           <!-- entrada para telefono -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-phone"></i></span>

              <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>

             </div>

           </div>


           <!-- entrada para la direccion -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" required>

             </div>

           </div>


           <!-- entrada para la fecha naciminiento -->
            
            <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>

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

