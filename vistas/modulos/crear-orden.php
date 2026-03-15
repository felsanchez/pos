<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$impuestoDefecto = !empty($configuracion["impuesto_defecto"]) ? $configuracion["impuesto_defecto"] : 0;
$formatoCodigoVenta = !empty($configuracion["formato_codigo_venta"]) ? $configuracion["formato_codigo_venta"] : "";
$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");
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

                    <input type="text" class="form-control" id="nuevoVendedor" name="nuevoVendedor"
                      value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                    <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["id"]; ?>">

                  </div>

                </div>


                <!--=====================================
                      ENTRADA DEL FORMATO DE CÓDIGO
                      ======================================-->

                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-barcode"></i></span>

                    <input type="text" class="form-control" id="formatoCodigoVenta" name="formatoCodigoVenta"
                      value="<?php echo $formatoCodigoVenta; ?>" readonly placeholder="Formato de código">

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

                    /*
                    if(!$ventas){
                      echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="10001" readonly>';
                    }
                    else {
                       foreach ($ventas as $key => $value) {
                      } 
                      $codigo = $value["codigo"] +1;
                       echo '<input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta" value="'.$key.'" readonly>';
                       $fecha = $value["fecha"] +1;
                      echo '<input type="date" class="form-control" id="nuevaVenta" name="nuevaVenta" value="'.$fecha.'">';
                    }
                    */

                    // Obtener el siguiente consecutivo
                    
                    $siguienteNumero = ModeloVentas::mdlObtenerSiguienteConsecutivo("ventas");


                    ?>

                    <!-- Mostrar el codigo en el campo de texto -->
                    <input type="text" class="form-control" id="nuevaVenta" name="nuevaVenta"
                      value="<?php echo $siguienteNumero; ?>" readonly>

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

                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }

                      ?>

                    </select>

                    <span class="input-group-addon"><button type="button" class="btn btn-default btn-xs"
                        onclick="window.location.href='index.php?ruta=cliente-detalle'">Agregar cliente</button></span>

                  </div>

                </div>


                <!--=====================================
                      ENTRADA PARA QUIEN RECIBE
                      ======================================-->

                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-user-circle"></i></span>

                    <input type="text" class="form-control" id="recibe" name="recibe"
                      placeholder="Nombre de quien recibe (opcional)">

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
                                placeholder="0" readonly>
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
                                placeholder="0" readonly>
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
                                placeholder="0" readonly>
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
                                name="nuevoTotalVenta" total="" placeholder="0" readonly required
                                style="font-weight: bold; font-size: 1.2em;">
                              <input type="hidden" name="totalVenta" id="totalVenta">
                              <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" value="0"
                                required>
                              <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto" required>
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
                  <div class="col-xs-6" style="padding-right:0px">
                    <div class="input-group">
                      <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago" required>
                        <option value="">Seleccione método de pago</option>
                        <?php
                        foreach ($mediosPago as $medio) {
                          $medio = trim($medio); // Eliminar espacios en blanco
                          echo '<option value="' . $medio . '">' . $medio . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="cajasMetodoPago"></div>
                  <input type="hidden" id="listaMetodoPago" name="listaMetodoPago">
                </div>


                <!--
                        <input type="hidden" id="nuevoMetodoPago" name="nuevoMetodoPago" value="DEBE">
                        <input type="hidden" id="listaMetodoPago" name="listaMetodoPago" value='[{"metodo":"DEBE","total":0}]'>
                        -->


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
          $guardarVenta->ctrCrearVenta();

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





<!--Verificar que tenga productos , antes de guardar la venta-->
<script>
  document.querySelector('form').addEventListener('submit', function (e) {
    // Llamar a listarProductos() primero para generar el JSON con todos los campos
    listarProductos();
    const listaProductos = document.getElementById('listaProductos').value;


    // DEBUG: Ver qué JSON se generó
    console.log('=== DEBUG CREAR ORDEN ===');
    console.log('JSON listaProductos:', listaProductos);
    console.log('Parsed:', JSON.parse(listaProductos || '[]'));


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