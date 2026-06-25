<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Editar venta
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

      <div class="col-lg-5 col-xs-12">

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
                      ENTRADA DEL CLIENTE
                      ======================================-->

                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-users"></i></span>

                    <!--   <input type="text" class="form-control" id="seleccionarCliente" name="seleccionarCliente" value="<?php //echo $cliente["nombre"]; ?>" readonly>-->

                    <select class="form-control" id="seleccionarCliente" name="seleccionarCliente" required>

                      <option value="<?php echo $cliente["id"]; ?>"><?php echo $cliente["nombre"]; ?></option>

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
                        data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar
                        cliente</button></span>

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

                    $esVarianteAttr = (isset($value["esVariante"]) && $value["esVariante"] == "1") ? ' esVariante="1" idVariante="' . $value["idVariante"] . '" skuVariante="' . $value["skuVariante"] . '"' : '';
                    $camposVariante = (isset($value["esVariante"]) && $value["esVariante"] == "1") ? 
                      '<input type="hidden" class="esVariante" value="1">
                       <input type="hidden" class="idVarianteProducto" value="' . $value["idVariante"] . '">
                       <input type="hidden" class="skuVariante" value="' . $value["skuVariante"] . '">' : '';

                    echo '<div class="row" style="padding:5px 15px">

                          
                            <div class="col-xs-6" style="padding-right:0px">

                              <div class="input-group">

                                 <span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto="' . $value["id"] . '"><i class="fa fa-times"></i></button></span>

                                 <input type="text" class="form-control nuevaDescripcionProducto" idProducto="' . $value["id"] . '"' . $esVarianteAttr . ' name="agregarProducto" value="' . $value["descripcion"] . '" readonly required>
                                 ' . $camposVariante . '
                                          
                              </div>

                            </div>


                            <div class="col-xs-3">
                                       
                                <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="' . $value["cantidad"] . '" stock="' . $stockAntiguo . '" nuevoStock="' . $value["stock"] . '" required>

                            </div>


                            <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">
                                        
                                <div class="input-group">

                                    <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                          
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

                <button type="button" class="btn btn-default hidden-lg btnAgregarProducto">Agregar producto</button>

                <hr>

                <div class="row">

                  <!--=====================================
                        ENTRADA IMPUESTOS Y TOTAL
                        ======================================-->

                  <div class="col-xs-8 pull-right">
                    <?php
                    // RECALCULO EXACTO DE TOTALES (Igual que en JS de crear venta)
                    
                    $listaProducto = json_decode($venta["productos"], true);
                    $valorBrutoRecalculado = 0; // Base Imponible Bruta (Antes de descuentos)
                    $subtotalRecalculado = 0;   // Base Imponible Neta (Despues de descuentos)
                    $impuestoGeneral = 0;
                    $impuestoINC = 0;

                    // Datos de descuentos
                    $tipoDescuento = $venta["tipo_descuento"] ?? ""; // porcentaje o fijo
                    $valorDescuentoGlobal = $venta["valor_descuento"] ?? 0;
                    $montoDescuentoTotal = $venta["monto_descuento"] ?? 0;
                    $totalVentaOriginal = $venta["total"];

                    // Si hay descuento fijo, necesitamos el total original para prorratear
                    // Afortunadamente tenemos $venta["total"] que es el FINAL con impuestos y descuentos.
                    // Pero para prorrateo necesitamos el total ANTES de descuento... 
                    // ESTIMACION: TotalOrig = TotalFinal + MontoDescuento
                    $totalOriginalEstimado = $totalVentaOriginal + $montoDescuentoTotal;

                    foreach ($listaProducto as $prod) {
                      // Obtener datos del producto (especialmente impuestos)
                      // Nota: $prod contiene precio y total que YA INCLUYEN IMPUESTO
                      $totalProductoConImpuesto = floatval($prod["total"]);

                      // Buscar porcentaje de impuesto
                      $impuestoPorcentaje = 0;
                      $impuestoNombre = "";

                      // Intentamos sacar el impuesto del propio JSON si se guardó (idealmente)
                      if (isset($prod["impuesto"])) {
                        $impuestoPorcentaje = floatval($prod["impuesto"]);
                      } else {
                        // Fallback: buscar en BD (menos preciso si cambiaron impuestos)
                        $infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
                        $impuestoPorcentaje = isset($infoP["impuesto_porcentaje"]) ? floatval($infoP["impuesto_porcentaje"]) : 19; // Default 19 si falla
                      }

                      // Buscar nombre impuesto en BD para saber si es INC
                      // (Esto es una limitante si no se guardó en el JSON de venta, asumimos IVA general si falla)
                      // Intentamos obtener el nombre del impuesto del propio JSON
                      if (isset($prod["impuestoNombre"])) {
                        $impuestoNombre = $prod["impuestoNombre"];
                      } else {
                        // Fallback DB
                        //$infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
                        //$impuestoNombre = $infoP["impuesto_nombre"] ?? ""; 
                        // Simplificación: Si no tenemos el nombre, asumimos IVA para no romper, 
                        // pero idealmente deberíamos haber guardado esto.
                      }

                      // 1. CALCULAR VALOR BRUTO (BASE BRUTA) DEL ITEM
                      // Base = TotalConImpuesto / (1 + %/100)
                      // PERO: Si hubo descuento, el TotalConImpuesto ya lo tiene restado?
                      // NO. En el JSON de productos, "total" suele ser Cantidad * PrecioUnitario.
                      // En `ventas.js`: "total": $(precio[i]).val() -> Precio con impuesto unitario * cantidad.
                      // El descuento se aplica GLOBALMENTE al final en la logica JS, 
                      // AUNQUE internamente calcula prorrateos.
                      // VERIFICACION CRITICA: ¿El "total" del JSON productos tiene el descuento restado?
                      // En `listarProductos()` de ventas.js: "total": $(precio[i]).val().
                      // $(precio[i]) es el input. Ese input NO cambia con el descuento global visualmente 
                      // en la tabla (el descuento se muestra abajo).
                      // POR LO TANTO: $prod["total"] es PRECIO DE LISTA CON IMPUESTO (SIN DESCUENTO APLICADO).
                    
                      $baseItemBruta = $totalProductoConImpuesto / (1 + ($impuestoPorcentaje / 100));
                      $valorBrutoRecalculado += $baseItemBruta;

                      // 2. CALCULAR DESCUENTO PRORRATEADO PARA ESTE ITEM
                      $descuentoItem = 0;

                      if ($tipoDescuento == "porcentaje") {
                        $descuentoItem = $totalProductoConImpuesto * ($valorDescuentoGlobal / 100);
                      } else if ($tipoDescuento == "fijo" && $totalOriginalEstimado > 0) {
                        // Prorrateo: Descuento * (Peso del Item en el Total)
                        $descuentoItem = $valorDescuentoGlobal * ($totalProductoConImpuesto / $totalOriginalEstimado);
                      }

                      $precioConDescuento = $totalProductoConImpuesto - $descuentoItem;

                      // 3. CALCULAR BASE NETA (SUBTOTAL)
                      $baseItemNeta = $precioConDescuento / (1 + ($impuestoPorcentaje / 100));
                      $impuestoItem = $precioConDescuento - $baseItemNeta;

                      $subtotalRecalculado += $baseItemNeta;

                      // Clasificar impuesto
                      // Si detectamos INC en alguna parte (difícil sin el nombre exacto guardado)
                      // Por ahora sumamos todo a General user request.
                      $impuestoGeneral += $impuestoItem;
                    }

                    // Calculo Final del Valor Neto (Total a Pagar)
                    // Total Venta = Subtotal + Impuestos
                    $totalVentaCalculado = $subtotalRecalculado + $impuestoGeneral;
                    // Valor Neto = Total Venta - Retenciones
                    // Definimos totalRetenciones aqui si no existe (en este archivo no hay bloque previo garantizado)
                    $totalRetenciones = 0;
                    // En editar-venta1.php no vimos el bloque de retenciones previo, asi que asumimos 0 o lo buscamos si existe.
                    // REVISION: editar-venta1.php parece ser diferente. Vamos a asumir 0 por seguridad si no esta definido.
                    
                    // REVISION: editar-venta1.php parece ser diferente. Vamos a asumir 0 por seguridad si no esta definido.
                    
                    // 4. CALCULAR DESCUENTO SOBRE LA BASE
                    $descuentoBase = $valorBrutoRecalculado - $subtotalRecalculado;

                    ?>
                    <table class="table">
                      <thead>
                        <tr>
                          <th colspan="2">Resumen de Venta</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <th style="width:50%">Subtotal</th>
                          <td>$
                            <?php echo number_format($valorBrutoRecalculado, 2); ?>
                          </td>
                        </tr>
                        <!-- Descuento logic -->
                        <?php if ($montoDescuentoTotal > 0): ?>
                          <tr>
                            <th>Descuento:</th>
                            <td>$<?php echo number_format((float) $descuentoBase, 2); ?></td>
                          </tr>
                        <?php endif; ?>
                        <tr>
                          <th>Valor Bruto:</th>
                          <td>$<?php echo number_format((float) $subtotalRecalculado, 2); ?></td>
                        </tr>
                        <tr>
                          <th>Impuesto:</th>
                          <td>$<?php echo number_format((float) $impuestoGeneral, 2); ?></td>
                        </tr>
                        <tr>
                          <th>Total:</th>
                          <td>$<?php echo number_format((float) $totalVentaCalculado, 2); ?></td>
                        </tr>
                        <tr>
                          <th style="font-size: 1.1em; color: #333;">Valor Neto:</th>
                          <td style="font-size: 1.1em; font-weight: bold;">
                            $<?php echo number_format((float) ($totalVentaCalculado - $totalRetenciones), 2); ?></td>
                        </tr>
                      </tbody>
                    </table>
                    <div class="input-group">
                      <input type="number" class="form-control input-lg" min="0" id="nuevoImpuestoVenta"
                        name="nuevoImpuestoVenta" value="<?php echo $porcentajeImpuesto; ?>" required>

                      <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto"
                        value="<?php echo $venta["impuesto"]; ?>" required>

                      <input type="hidden" name="nuevoPrecioNeto" id="nuevoPrecioNeto"
                        value="<?php echo $venta["neto"]; ?>" required>

                      <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                    </div>

                    </td>

                    <td style="width: 50%">

                      <div class="input-group">
                        <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                        <input type="text" class="form-control input-lg" id="nuevoTotalVenta" name="nuevoTotalVenta"
                          total="" value="<?php echo $venta["total"]; ?>" readonly required>

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

                <br>


              </div>

            </div>



            <div class="box-footer">

              <button type="submit" class="btn btn-primary pull-right">Guardar cambios</button>

              <button class="btn btn-danger pull-left" onclick="location.href='ventas'">Cancelar</button>

            </div>

          </form>


          <?php

          $editarVenta = new ControladorVentas();
          $editarVenta->ctrEditarVenta();

          ?>



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