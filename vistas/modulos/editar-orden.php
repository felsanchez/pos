<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$impuestoDefecto = !empty($configuracion["impuesto_defecto"]) ? $configuracion["impuesto_defecto"] : 0;
$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");
$mensajeConfirmado = !empty($configuracion["mensaje_confirmado"]) ? $configuracion["mensaje_confirmado"] : "Su pedido ha sido confirmado";
?>

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
      Convertir orden a venta
    </h1>

    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Convertir orden a venta</li>
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

          <!--<form role="form" method="post" class="formularioVenta">-->
          <form role="form" method="post" class="formularioVenta" id="formularioVenta">

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
                
                $porcentajeImpuesto = $impuestoDefecto; // Usar impuesto de configuración por defecto
                if ($venta["neto"] != 0) {
                  $porcentajeImpuesto = $venta["impuesto"] * 100 / $venta["neto"];
                }

                ?>



                <!--=====================================
                      ENTRADA DEL VENDEDOR
                      ======================================-->

                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-user"></i></span>

                    <input type="text" class="form-control" id="nuevoVendedor"
                      value="<?php echo $vendedor["nombre"]; ?>" readonly>

                    <input type="hidden" name="idVendedor" value="<?php echo $vendedor["id"]; ?>">

                  </div>

                </div>

                <!--=====================================
                      ENTRADA DEL CODIGO
                      ======================================-->

                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-key"></i></span>

                    <input type="text" class="form-control" id="nuevaVenta" name="editarVenta"
                      value="<?php echo $venta["codigo"]; ?>" readonly>

                  </div>

                </div>


                <!--=====================================
                      ENTRADA Imagen
                      ======================================-->

                <?php if (!empty($venta["imagen"])): ?>
                  <div class="form-group text-center">
                    <label>Imagen de la orden</label>
                    <br>
                    <img src="<?php echo $venta["imagen"]; ?>" class="img-thumbnail" style="max-width:150px;">
                  </div>
                <?php endif; ?>

                <!-- Campo oculto para enviar la imagen al guardar la venta -->
                <input type="hidden" name="nuevaimagen" value="<?php echo $venta["imagen"]; ?>">


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

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-users"></i></span>

                    <!--   <input type="text" class="form-control" id="seleccionarCliente" name="seleccionarCliente" value="<?php //echo $cliente["nombre"]; ?>" readonly>-->

                    <select class="form-control" id="seleccionarCliente" name="seleccionarCliente" required>

                      <option value="<?php echo $cliente["id"]; ?>" data-telefono="<?php echo $cliente["telefono"]; ?>">
                        <?php echo $cliente["nombre"]; ?>
                      </option>

                      <?php

                      $item = null;
                      $valor = null;

                      $categorias = ControladorClientes::ctrMostrarClientes($item, $valor);

                      foreach ($categorias as $key => $value) {

                        echo '<option value="' . $value["id"] . '" data-telefono="' . $value["telefono"] . '">' . $value["nombre"] . '</option>';

                      }

                      ?>

                    </select>


                    <span class="input-group-addon"><button type="button" class="btn btn-default btn-xs"
                        data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar
                        cliente</button></span>

                  </div>

                </div>


                <!--=====================================
                      ENTRADA PARA QUIEN RECIBE
                      ======================================-->

                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-user-circle"></i></span>

                    <input type="text" class="form-control" id="recibe" name="recibe"
                      placeholder="Nombre de quien recibe (opcional)"
                      value="<?php echo isset($venta["recibe"]) ? $venta["recibe"] : ''; ?>">

                  </div>

                </div>


                <!--=====================================
                      ENTRADA PARA AGREGAR PRODUCTO
                      ======================================-->

                <div class="form-group row nuevoProducto">

                  <?php

                  $listaProducto = json_decode($venta["productos"], true);
                  $totalProductos = 0; // Inicializar subtotal
                  
                  foreach ($listaProducto as $key => $value) {

                    $totalProductos += $value["total"]; // Sumar al subtotal
                  
                    $item = "id";
                    $valor = $value["id"];
                    $orden = "id";

                    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

                    $stockAntiguo = $respuesta["stock"] + $value["cantidad"];

                    // Verificar si es una variante para agregar campos hidden
                  
                    $camposVariante = '';

                    if (isset($value["esVariante"]) && $value["esVariante"] == "1") {

                      $camposVariante = '<input type="hidden" class="esVariante" value="1">

                                                   <input type="hidden" class="idVarianteProducto" value="' . $value["idVariante"] . '">

                                                   <input type="hidden" class="skuVariante" value="' . $value["skuVariante"] . '">';
                    }

                    echo '<div class="row" style="padding:5px 15px"> 

                            <div class="col-xs-6" style="padding-right:0px"> 

                              <div class="input-group"> 

                                 <span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="' . $value["id"] . '"><i class="fa fa-times"></i></button></span>

                                  <input type="text" class="form-control nuevaDescripcionProducto" idProducto="' . $value["id"] . '" name="agregarProducto" value="' . $value["descripcion"] . '" readonly required>

                                 ' . $camposVariante . '
                                          
                              </div>

                            </div>

                            <div class="col-xs-3">
                                       
                                <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="' . $value["cantidad"] . '" stock="' . $stockAntiguo . '" nuevoStock="' . $value["stock"] . '" required>

                            </div>


                            <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">
                                        
                                <div class="input-group">
                                    
                                          
                                    <input type="text" class="form-control nuevoPrecioProducto" precioReal="' . $respuesta["precio_venta"] . '" name="nuevoPrecioProducto" value="' . $value["total"] . '" readonly required>

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
                <button type="button" class="btn btn-default btnAgregarProducto">Agregar producto</button>

                <hr>

                <div class="row">

                  <!--=====================================
                        ENTRADA VALOR BRUTO, SUBTOTAL, IMPUESTOS Y TOTAL
                        ======================================-->

                  <div class="col-xs-12 col-md-6 pull-right">
                    <table class="table table-condensed table-bordered" style="background:#f9f9f9;">
                      <tbody>
                        <!-- Subtotal (precio sin impuesto) -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold; width: 40%">Subtotal</td>
                          <td style="width: 60%">
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoValorBruto" name="nuevoValorBruto"
                                placeholder="0" value="<?php echo $totalProductos; ?>" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Valor Bruto -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold;">Valor Bruto</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoSubtotalVenta" name="nuevoSubtotalVenta"
                                placeholder="0" value="<?php echo $totalProductos; ?>" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Impuestos IVA -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold;">Impuestos IVA</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoImpuestoVenta" name="nuevoImpuestoVenta"
                                placeholder="0" value="<?php echo $venta['impuesto']; ?>" readonly>
                              <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto"
                                value="<?php echo $venta['impuesto']; ?>" required>
                              <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto"
                                value="<?php echo $venta['neto']; ?>" required>
                            </div>
                          </td>
                        </tr>
                        <!-- Impuestos INC -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold;">Impuestos INC</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control" id="nuevoImpuestoINCVenta"
                                name="nuevoImpuestoINCVenta" placeholder="0" readonly>
                            </div>
                          </td>
                        </tr>
                        <!-- Total -->
                        <tr>
                          <td style="vertical-align: middle; font-weight: bold; font-size: 1.2em;">Total</td>
                          <td>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-lg" id="nuevoTotalVenta"
                                name="nuevoTotalVenta" total="" value="<?php echo $venta['total']; ?>" readonly required
                                style="font-weight: bold; font-size: 1.2em;">
                              <input type="hidden" name="totalVenta" value="<?php echo $venta['total']; ?>"
                                id="totalVenta">
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- SECCIÓN DE RETENCIONES -->
                  <div class="col-xs-12 pull-left" id="seccionRetenciones" style="display: none; margin-top: 10px;">
                    <div class="alert alert-info">
                      <h4><i class="icon fa fa-info-circle"></i> Retenciones Aplicadas</h4>
                      <div id="listaRetenciones"></div>
                      <input type="hidden" id="datosRetenciones" name="datosRetenciones" value="">
                    </div>
                  </div>

                </div>

                <hr>

                <!--=====================================
                         SECCIÓN DE DESCUENTOS
                         ======================================-->

                <div class="row">
                  <div class="col-xs-12">

                    <!-- Checkboxes para tipo de descuento -->
                    <div class="form-group">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="checkDescuentoPorcentaje" name="checkDescuentoPorcentaje"
                          style="margin-right: 5px; transform: scale(1.2);"
                          <?php echo ($venta["tipo_descuento"] == "porcentaje") ? "checked" : ""; ?>>
                        Agregar descuento por %
                      </label>
                      &nbsp;&nbsp;&nbsp;

                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="checkDescuentoFijo" name="checkDescuentoFijo"
                          style="margin-right: 5px; transform: scale(1.2);"
                          <?php echo ($venta["tipo_descuento"] == "fijo") ? "checked" : ""; ?>>
                        Agregar descuento por valor fijo
                      </label>
                    </div>

                    <!-- Campo de entrada para descuento -->
                    <div class="form-group" id="campoDescuento" style="<?php echo empty($venta["tipo_descuento"]) ? "display: none;" : ""; ?>">
                      <div class="input-group">
                        <span class="input-group-addon" id="iconoDescuento">
                            <?php if ($venta["tipo_descuento"] == "fijo"): ?>
                                <i class="fa fa-money"></i>
                            <?php else: ?>
                                <i class="fa fa-percent"></i>
                            <?php endif; ?>
                        </span>
                        <input type="number" class="form-control input-lg" min="0" id="valorDescuento"
                          name="valorDescuento" placeholder="0" value="<?php echo empty($venta["valor_descuento"]) ? 0 : $venta["valor_descuento"]; ?>">
                        <span class="input-group-addon" id="labelDescuento">
                            <?php echo ($venta["tipo_descuento"] == "fijo") ? "Valor Descuento" : "Descuento"; ?>
                        </span>
                      </div>
                      <small class="text-muted" id="textoAyudaDescuento">
                          <?php echo ($venta["tipo_descuento"] == "fijo") ? "Ingrese el valor fijo del descuento" : "Ingrese el porcentaje de descuento"; ?>
                      </small>
                    </div>

                    <!-- Campos ocultos para guardar información del descuento -->
                    <input type="hidden" id="tipoDescuento" name="tipoDescuento" value="<?php echo $venta["tipo_descuento"]; ?>">
                    <input type="hidden" id="montoDescuento" name="montoDescuento" value="<?php echo empty($venta["monto_descuento"]) ? 0 : $venta["monto_descuento"]; ?>">
                  </div>

                </div>

                <div class="row">
                  <div class="col-xs-12">
                    <button type="button" class="btn btn-default" data-toggle="modal"
                      data-target="#modalAgregarRetencionNuevo">Retenciones</button>
                  </div>
                </div>

                <hr>

                <!--=====================================
                         ENTRADA METODO DE PAGO
                         ======================================-->

                <div class="form-group row">
                  <div class="col-xs-6" style="padding-right:0px">
                    <div class="input-group">
                      <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago" required>
                        <option value="">Seleccione método de pago</option>
                        <?php
                        foreach ($mediosPago as $medio) {
                          $medio = trim($medio); // Eliminar espacios en blanco
                          $sel = ($medio == $venta["metodo_pago"]) ? 'selected' : '';
                          echo '<option value="' . $medio . '" ' . $sel . '>' . $medio . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="cajasMetodoPago"></div>
                  <input type="hidden" id="listaMetodoPago" name="listaMetodoPago">
                </div>


                <!-- ENTRADA ESTADO-->
                <input type="hidden" name="estado" value="venta">

                <br>

              </div>

            </div>


            <div class="box-footer">

              <button type="submit" class="btn btn-primary pull-right">Guardar cambios</button>

            </div>




            <!-- Agregar campo oculto para el origen -->
            <input type="hidden" name="origen" value="ventas">

          </form>


          <?php

          $editarVenta = new ControladorVentas();
          $editarVenta->ctrEditarVenta();

          ?>

          <button class="btn btn-danger pull-left" onclick="location.href='ordenes'">Cancelar</button>


        </div>

      </div>

      <!--=====================================
            LA TABLA DE PRODUCTOS
            ======================================-->

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

                <input type="text" class="form-control input-lg" name="nuevoCliente" placeholder="Ingresar nombre"
                  required>

              </div>

            </div>


            <!-- entrada para documento ID -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-key"></i></span>

                <input type="number" min="0" class="form-control input-lg" name="nuevoDocumentoId"
                  placeholder="Ingresar documento" required>

              </div>

            </div>


            <!-- entrada para Email -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email"
                  required>

              </div>

            </div>


            <!-- entrada para telefono -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono"
                  data-inputmask="'mask':'(999) 999-9999'" data-mask required>

              </div>

            </div>


            <!-- entrada para la direccion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección"
                  required>

              </div>

            </div>


            <!-- entrada para la fecha naciminiento -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento"
                  placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>

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
      $crearCliente->ctrCrearCliente();
      ?>

    </div>

  </div>

</div>

<!--=====================================
MODAL AGREGAR RETENCION
======================================-->
<div id="modalAgregarRetencionNuevo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formularioRetencionNuevo">

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Retención</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">
          <div class="box-body">

            <!-- Tipo de retencion -->
            <div class="form-group">
              <label>Tipo Retención</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                <select class="form-control input-lg" id="nuevoTipoRetencionNuevo" name="nuevoTipoRetencion">
                  <option value="">Seleccionar tipo</option>
                  <option value="ReteIVA">ReteIVA</option>
                  <option value="ReteRenta">ReteRenta</option>
                </select>
              </div>
            </div>

            <!-- Porcentaje -->
            <div class="form-group">
              <label>Porcentaje</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                <select class="form-control input-lg" id="nuevoPorcentajeRetencionNuevo"
                  name="nuevoPorcentajeRetencion">
                  <option value="">Seleccionar porcentaje</option>
                </select>
              </div>
            </div>

          </div>
        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="button" class="btn btn-primary" id="guardarRetencionNuevo" data-dismiss="modal">Guardar</button>
        </div>

      </form>
    </div>
  </div>
</div>


<!-- Script para inicializar el descuento al cargar la página -->
<script>
  $(document).ready(function () {
    // Si hay un descuento aplicado, recalcular el total al cargar
    if ($("#tipoDescuento").val() !== "") {
      // Asegurarse de que el atributo 'total' esté configurado
      var subtotal = $("#nuevoSubtotalVenta").val();
      $("#nuevoTotalVenta").attr("total", subtotal);

      // Aplicar el descuento
      aplicarDescuento();
    }
  });

  $(document).ready(function () {
    // Función centralizada para manejar la selección de porcentaje
    function activarPorcentaje() {
        // Desmarcar el otro checkbox
        $('#checkDescuentoFijo').prop('checked', false);
        try { $('#checkDescuentoFijo').iCheck('uncheck'); } catch(e){}

        // Mostrar campo de descuento
        $('#campoDescuento').slideDown();

        // Cambiar icono y texto a porcentaje
        $('#iconoDescuento').html('<i class="fa fa-percent"></i>');
        $('#labelDescuento').text('% Descuento');
        $('#textoAyudaDescuento').text('Ingrese el porcentaje de descuento (0-100)');
        $('#valorDescuento').attr('max', '100');
        $('#valorDescuento').attr('placeholder', '0');
        // Si no había valor previo, setear a 0
        if($('#tipoDescuento').val() !== 'porcentaje') $('#valorDescuento').val('0');

        // Guardar tipo de descuento
        $('#tipoDescuento').val('porcentaje');
        
        aplicarDescuento();
    }

    function desactivarDescuento() {
        $('#campoDescuento').slideUp();
        $('#valorDescuento').val('0');
        $('#tipoDescuento').val('');
        $('#montoDescuento').val('0');

        // Recalcular total sin descuento
        sumarTotalPrecios();
        agregarImpuesto();
    }

    // Función centralizada para manejar la selección de fijo
    function activarFijo() {
        // Desmarcar el otro checkbox
        $('#checkDescuentoPorcentaje').prop('checked', false);
        try { $('#checkDescuentoPorcentaje').iCheck('uncheck'); } catch(e){}

        // Mostrar campo de descuento
        $('#campoDescuento').slideDown();

        // Cambiar icono y texto a valor fijo
        $('#iconoDescuento').html('<i class="fa fa-money"></i>');
        $('#labelDescuento').text('Valor Descuento');
        $('#textoAyudaDescuento').text('Ingrese el valor fijo del descuento');
        $('#valorDescuento').removeAttr('max');
        $('#valorDescuento').attr('placeholder', '0');
        if($('#tipoDescuento').val() !== 'fijo') $('#valorDescuento').val('0');

        // Guardar tipo de descuento
        $('#tipoDescuento').val('fijo');
        
        aplicarDescuento();
    }

    // Eventos normales y eventos iCheck
    $('#checkDescuentoPorcentaje').on('change', function () {
      if ($(this).is(':checked')) activarPorcentaje(); else desactivarDescuento();
    });
    $('#checkDescuentoPorcentaje').on('ifChecked', activarPorcentaje);
    $('#checkDescuentoPorcentaje').on('ifUnchecked', desactivarDescuento);

    $('#checkDescuentoFijo').on('change', function () {
      if ($(this).is(':checked')) activarFijo(); else desactivarDescuento();
    });
    $('#checkDescuentoFijo').on('ifChecked', activarFijo);
    $('#checkDescuentoFijo').on('ifUnchecked', desactivarDescuento);

    // Cuando cambia el valor del descuento, recalcular
    $('#valorDescuento').on('change keyup', function () {
      aplicarDescuento();
    });

  });
</script>

<!-- Webhook para Guardar Cambios en Editar Orden -->
<script>
  $(document).ready(function () {
    $("#formularioVenta").on("submit", function (e) {

      var formulario = this;
      var btnGuardar = $(this).find("button[type='submit']");

      // Si el formulario ya tiene el flag de 'enviando', dejar pasar el submit real
      if (btnGuardar.data("enviando")) {
        return true;
      }

      e.preventDefault(); // Detener el primer envío

      // Evitar múltiples clics
      if (btnGuardar.attr("disabled")) return;
      btnGuardar.attr("disabled", true);
      btnGuardar.data("enviando", true); // Marcar para permitir el siguiente submit

      // Obtener datos del cliente seleccionado
      var opcionCliente = $("#seleccionarCliente option:selected");
      var nombreCliente = opcionCliente.text().trim();
      var telefonoCliente = opcionCliente.attr("data-telefono");
      var codigoVenta = $("#nuevaVenta").val();

      // URL del Webhook
      var urlWebhook = "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/b6aad80c-aedf-4339-a701-89d040f44f47";

      console.log("Enviando webhook:", nombreCliente, telefonoCliente);

      // Preparar datos
      const datosWebhook = new URLSearchParams();
      datosWebhook.append('nombre', nombreCliente);
      datosWebhook.append('celular', telefonoCliente);
      datosWebhook.append('codigo', codigoVenta);
      datosWebhook.append('mensaje', '<?php echo addslashes($mensajeConfirmado); ?>');

      // Enviar fetch sin esperar respuesta (no bloquear UI mucho tiempo)
      fetch(urlWebhook, {
        method: 'POST',
        mode: 'no-cors',
        cache: 'no-cache',
        credentials: 'omit',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: datosWebhook
      }).then(() => {
        console.log("Webhook enviado");
      }).catch(err => {
        console.error("Error webhook", err);
      }).finally(() => {
        // Liberar botón y reenviar formulario
        // btnGuardar.removeAttr("disabled"); // No liberar para evitar doble clic del usuario
        formulario.submit();
      });
    });
  });
</script>