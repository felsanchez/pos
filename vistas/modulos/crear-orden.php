  <style>
      @media (min-width: 769px) {
        .solo-movil {
          display: none !important;
        }
      }
  </style>


<div class="content-wrapper">
    <section class="content-header">


      <h1>
        Crear orden de venta
      </h1>

      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Crear orden</li>
      </ol>

    </section>

    
    <section class="content">

      <div class="row">

            <!--=====================================
            EL FORMULARIO
            ======================================-->
            
            <div class="col-lg-5 col-xs-12">

              <div class="box box-success">

                <div class="box-header with-border"></div>

                <form role="form" method="post" class="formularioVenta">

                <div class="box-body">

                    <div class="box">

                      <!--=====================================
                      ENTRADA DEL VENDEDOR
                      ======================================-->

                      <div class="form-group">

                        <div class="input-group">

                          <span class="input-group-addon"><i class="fa fa-user"></i></span>

                          <input type="text" class="form-control" id="nuevoVendedor" name="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                          <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">
                          
                        </div>
                        
                      </div>



                      <!--=====================================
                      ENTRADA DE LA VENTA
                      ======================================-->

                      <div class="form-group">

                        <div class="input-group">

                          <span class="input-group-addon"><i class="fa fa-key"></i></span>

                          

                          <?php

                            $item = null;
                            $valor = null;

                            $ventas = ControladorVentas::ctrMostrarVentas($item, $valor);

                            if(!$ventas){

                              echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="10001" readonly>';
                            }

                            else {

                               foreach ($ventas as $key => $value) {
                                                                              
                              } 

                              $codigo = $value["codigo"] +1;

                              /*echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="'.$codigo.'" readonly>'; */


                              echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="'.$key.'" readonly>';                              

                              /* $fecha = $value["fecha"] +1;
                              echo '<input type="date" class="form-control" id="nuevaVenta" name="nuevaVenta" value="'.$fecha.'">'; */

                            }


                          ?>
                          
                        </div>
                        
                      </div>

                      <!--=====================================
                      ENTRADA DEL CLIENTE
                      ======================================-->

                      <div class="form-group">

                        <div class="input-group">

                          <span class="input-group-addon"><i class="fa fa-users"></i></span>

                          <select class="form-control" id="seleccionarCliente" name="seleccionarCliente" required>

                            <option value="">Seleccionar cliente</option>

                              <?php

                                $item = null;
                                $valor = null;

                                $categorias = ControladorClientes::ctrMostrarClientes($item, $valor);

                                foreach ($categorias as $key => $value) {
                                  
                                  echo '<option value="'.$value["id"].'">'.$value["nombre"].'</option>';
                                }

                              ?>

                          </select>

                          <span class="input-group-addon"><button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar cliente</button></span>
                          
                        </div>
                        
                      </div>


                      <!--=====================================
                      ENTRADA PARA AGREGAR PRODUTO
                      ======================================-->

                      <div class="form-group row nuevoProducto">

                       </div>

                       <input type="hidden" id="listaProductos" name="listaProductos">

                       <!--=====================================
                       BOTON PARA AGREGAR PRODUCTO
                       ======================================-->

                       <!--BTN SE MUESTRA SOLO DESDE MOVIL-->
                        <button type="button" class="btn btn-default btnAgregarProducto solo-movil">Agregar producto</button>
                        
                       <hr>

                       <div class="row">

                        <!--=====================================
                        ENTRADA IMPUESTOS Y TOTAL
                        ======================================-->
                         
                          <div class="col-xs-8 pull-right">
                           
                             <table class="table">                     

                               <thead>
                                 
                                 <tr>                             
                                   <th>Impuesto</th>
                                   <th>Total</th>
                                 </tr>

                               </thead>

                               <tbody>
                                 
                                 <tr>
                                   <td style="width: 50%">
                                     
                                     <div class="input-group">                                      
                                        <input type="number" class="form-control input-lg" min="0" id="nuevoImpuestoVenta" name="nuevoImpuestoVenta" value="0">

                                        <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" required>

                                        <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto" required>

                                        <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                     </div>

                                   </td>

                                   <td style="width: 50%">
                                     
                                     <div class="input-group">  
                                        <!--<span class="input-group-addon"><i class="ion ion-social-usd"></i></span> -->                          
                                        <input type="text" class="form-control input-lg" id="nuevoTotalVenta" name="nuevoTotalVenta" total="" placeholder="00000" readonly required>

                                        <input type="hidden" name="totalVenta" id="totalVenta">

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

                        <!--<div class="form-group row">                          
                          <div class="col-xs-6" style="padding-right:0px">
                              <div class="input-group">
                                <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago" required>
                                  <option value="">Seleccione método de pago</option>
                                  <option value="Efectivo">Efectivo</option>
                                  <option value="TC">Tarjeta Crédito</option>
                                  <option value="DEBE">DEBE</option>                                  
                                </select>   
                              </div>                            
                          </div>
                          <div class="cajasMetodoPago"></div>
                            <input type="hidden" id="listaMetodoPago" name="listaMetodoPago">
                        </div>
                        -->

                        <input type="hidden" id="nuevoMetodoPago" name="nuevoMetodoPago" value="DEBE">
                        <input type="hidden" id="listaMetodoPago" name="listaMetodoPago" value='[{"metodo":"DEBE","total":0}]'>



                        <!--=====================================
                        ENTRADA ESTADO
                        ======================================-->
                        <input type="hidden" name="estado" value="orden">

                        
                        <br>

                       
                   </div>
                  
                </div>



                <div class="box-footer">

                  <button type="submit" class="btn btn-primary pull-right">Guardar orden</button>
                  
                </div>

               </form>



               <?php
                
                $guardarVenta = new ControladorVentas();
                $guardarVenta -> ctrCrearVenta();
                
               ?>
            
            <button  class="btn btn-danger pull-left" onclick="location.href='ordenes'">Cancelar</button>

                
              </div>
              
            </div>   

            <!--=====================================
            LA TABLA DE PRODUCTOS
            ======================================-->

            <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

              <div class="box box-warning">

                <div class="box-header with-border"></div>

                <div class="box-body">
                  
                 <!--<table class="table table-bordered table-striped dt-responsive tablaVentas">-->
                  	<table class="table table-bordered table-striped tablaVentas">

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
              
              <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" required>

             </div>

           </div>

           <!-- entrada para estatus -->
           <input type="hidden" name="nuevoEstatus" value="nuevo">


           <!-- entrada para notas -->
            
           <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaNota" placeholder="Ingresar Nota">

            </div>

           </div>

           <!-- Al guardar me reenvie a esta pagina de crear venta y no a la de clientes  -->
           <input type="hidden" name="origen" value="crear-venta">

           <!--=====================================
          Estado Orden
          ======================================-->
          <input type="hidden" name="vistaOrigen" value="crear-orden">



           <!-- entrada para la fecha naciminiento -->
            
            <!--
           <div class="form-group">
          
            <div class="input-group">
              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>

             </div>

           </div>
          -->

          

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

      <!--Verificar que tenga productos , antes de guardar la venta-->                          
        <script>
          document.querySelector('form').addEventListener('submit', function (e) {
            const listaProductos = document.getElementById('listaProductos').value;
            if (!listaProductos || listaProductos === '[]') {
              e.preventDefault(); // Detiene el envío del formulario
              Swal.fire({
                icon: 'warning',
                title: 'Sin productos',
                text: 'Debe agregar al menos un producto para guardar la venta',
                confirmButtonText: 'OK'
              });
              return false;
            }
          });
        </script>

  </div>

</div>

