<style>
/* Responsive para móvil */
@media (max-width: 767px) {
  /* Campos de ancho completo */
  .formularioVenta .form-group .input-group.col-xs-12,
  .formularioVenta .form-group .col-xs-6 {
    width: 100% !important;
    max-width: 100%;
    padding: 0 !important;
  }

  /* Productos - diseño vertical en móvil */
  .nuevoProducto .row.col-xs-10 {
    width: 100% !important;
    margin: 0;
    padding: 5px 15px;
  }

  .nuevoProducto .row .col-xs-7,
  .nuevoProducto .row .col-xs-2,
  .nuevoProducto .row .col-xs-3 {
    width: 100% !important;
    padding: 0;
    margin-bottom: 10px;
  }

  .nuevoProducto .row .col-xs-7 {
    margin-bottom: 5px;
  }

  /* Tabla de impuesto y total - diseño vertical */
  .table-responsive {
    overflow-x: auto;
  }

  table thead th,
  table tbody td {
    font-size: 12px;
    padding: 5px !important;
  }

  table tbody td .input-group {
    min-width: 120px;
  }

  /* Descuento - ancho completo */
  .col-xs-10 {
    width: 100% !important;
  }

  /* Input-group en móvil */
  .input-group-addon {
    padding: 6px 8px;
    font-size: 12px;
  }
}

@media (min-width: 769px) {
  .solo-movil {
    display: none !important;
  }
}
</style>


<div class="content-wrapper">
    <section class="content-header">

      <h1>
        Venta - Grupo FEJ Technologies
      </h1>

      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Venta - Grupo FEJ Technologies</li>
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

        <?php CSRF::insertToken(); ?>

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
                    

                      <!--=====================================
                      ENTRADA DEL VENDEDOR
                      ======================================-->

                      <div class="form-group">

                        <div class="input-group col-xs-12">

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

                        <div class="input-group col-xs-12">

                          <span class="input-group-addon">Código</span>
                          <input type="text" class="form-control" id="nuevaVenta" name="editarVenta" value="<?php echo $venta["codigo"]; ?>" readonly>
                          <span class="input-group-addon"><i class="fa fa-key"></i></span>

                        </div>

                      </div>


                       <!--=====================================
                      ENTRADA notas
                      ======================================-->

                      <!--
                      <div class="form-group">
                        <div class="input-group">
                          <span class="input-group-addon"><i class="fa fa-sticky-note"></i></span>
                          <textarea class="form-control input-lg" name="notas" placeholder="Sin notas" readonly><?php echo $venta["notas"]; ?></textarea>

                        </div>
                      </div>
                        -->


                      <!--=====================================
                      ENTRADA DEL CLIENTE
                      ======================================-->
                      <div class="form-group">
                        <div class="input-group col-xs-12">
                          <span class="input-group-addon">Cliente</span>
                          <input type="text" class="form-control" value="<?php echo $cliente["nombre"]; ?>" readonly>
                          <span class="input-group-addon"><i class="fa fa-users"></i></span>
                          <input type="hidden" name="seleccionarCliente" value="<?php echo $cliente["id"]; ?>">
                        </div>
                      </div>


                      <!--=====================================
                      ENTRADA PARA AGREGAR PRODUCTO
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
                            
                            echo '<div class="row col-xs-10" style="padding:5px 15px">

                          
                            <div class="col-xs-7" style="padding-right:0px">
                              <div class="input-group">
                              <span class="input-group-addon"><i class="fa fa-tags"></i></span>
                                 <input type="text" class="form-control nuevaDescripcionProducto" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" readonly required>
                              </div>
                            </div>

                            <div class="col-xs-2">
                              <div class="input-group">
                                  <span class="input-group-addon"><i class="fa fa-list-ol"></i></span>                                       
                                  <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="'.$value["cantidad"].'" stock="'.$stockAntiguo.'" nuevoStock="'.$value["stock"].'" readonly>
                              </div>
                            </div>

                            <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">                                        
                                <div class="input-group">                                    
                                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 
                                    <input type="text" class="form-control nuevoPrecioProducto" precioReal="'.$respuesta["precio_venta"].'" name="nuevoPrecioProducto" value="'.$value["total"].'" readonly required>
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

                       <!--<button type="button" class="btn btn-default btnAgregarProducto solo-movil">Agregar producto</button>-->

                       <hr>

                       <div class="row">

                        <!--=====================================
                        ENTRADA IMPUESTOS Y TOTAL
                        ======================================-->

                          <div class="col-xs-12 col-sm-6">

                             <div class="table-responsive">
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

                                   <td style="width: 60%">

                                     <div class="input-group">
                                        <input type="text" class="form-control input-lg" id="nuevoTotalVenta" name="nuevoTotalVenta" total="" value="<?php echo $venta["total"]; ?>" readonly required>

                                        <input type="hidden" name="totalVenta" value="<?php echo $venta["total"]; ?>" id="totalVenta">

                                     </div>

                                   </td>

                                 </tr>

                               </tbody>

                             </table>
                             </div>

                           </div>

                        </div>

                        <hr>


                        <!--=====================================
                        SECCIÓN DE DESCUENTO (solo si existe)
                        ======================================-->

                        <?php if(!empty($venta["tipo_descuento"])): ?>

                        <div class="row">

                          <div class="col-xs-10">

                            <div class="form-group">

                              <label>Descuento Aplicado</label>

                              <div class="input-group">

                                <span class="input-group-addon">

                                  <?php if($venta["tipo_descuento"] == "porcentaje"): ?>

                                    <i class="fa fa-percent"></i>

                                  <?php else: ?>
                                    <i class="fa fa-money"></i>

                                  <?php endif; ?>

                                </span>

                                <input type="text" class="form-control input-lg"

                                       value="<?php
                                         if($venta["tipo_descuento"] == "porcentaje") {
                                           echo number_format($venta["valor_descuento"], 0) . "% - Monto: $" . number_format($venta["monto_descuento"], 0, '', '.');

                                         } else {
                                           echo "$" . number_format($venta["valor_descuento"], 0, '', '.');

                                         }
                                       ?>"

                                       readonly>

                                <span class="input-group-addon">

                                  <?php echo ($venta["tipo_descuento"] == "porcentaje") ? "Descuento %" : "Descuento Fijo"; ?>

                                </span>

                              </div>

                            </div>

                          </div>

                        </div>

                        <?php endif; ?>

                        <hr>

                        <!--=====================================
                        ENTRADA MÉTODO DE PAGO
                        ======================================-->

                        <div class="form-group row">
                          <div class="col-xs-6" style="padding-right:0px">
                            <div class="input-group">

                                <span class="input-group-addon">Método de Pago</span>
                                <input type="text" class="form-control" id="metodo_pago" name="metodo_pago" value="<?php echo $venta["metodo_pago"]; ?>" readonly>
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>

                            </div>
                          </div>
                        </div>

                        <hr>

                        </div>

                        <br>

                        
                   </div>
                  
                </div>


               </form>


             <?php
              $editarVenta = new ControladorVentas();
              $editarVenta -> ctrEditarVenta();
             ?>

              <button  class="btn btn-danger pull-left" onclick="location.href='ordenes-visita'">Cancelar</button>
             
                
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

        <?php CSRF::insertToken(); ?>

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

