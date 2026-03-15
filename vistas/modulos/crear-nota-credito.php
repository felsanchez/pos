<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

// 1. Obtener datos de la venta original si viene en la URL
$idVenta = null;
$venta = null;
$cliente = null;
$vendedor = null;
$productos = [];

if (isset($_GET["idVenta"])) {
    $idVenta = $_GET["idVenta"];
    $venta = ControladorVentas::ctrMostrarVentas("id", $idVenta);

    if ($venta) {
        $cliente = ControladorClientes::ctrMostrarClientes("id", $venta["id_cliente"]);
        $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $venta["id_vendedor"]);
        // Decodificar productos
        $productos = json_decode($venta["productos"], true);
    }
}

// ---------------------------------------------------------
// VALIDACIÓN DE CONSECUTIVO (DRAFT BLOCKING)
// ---------------------------------------------------------
$ultimaNota = ControladorFactus::ctrMostrarUltimaNotaCredito();

if ($ultimaNota) {
    $estadosValidos = ['enviada', 'aceptada'];
    if (!in_array($ultimaNota["estado_dian"], $estadosValidos) || empty($ultimaNota["numero_nota_credito"])) {
        echo '
        <script>
          swal({
            type: "warning",
            title: "Bloqueo de Consecutivo",
            text: "No se puede crear una nueva Nota Crédito porque la anterior aún no ha sido FIRMADA y ENVIADA a la DIAN o es un borrador. Debe firmar los documentos en orden secuencial.",
            showConfirmButton: true,
            confirmButtonText: "Ir a Notas Crédito"
          }).then(function (result) {
            if (result.value) {
              window.location = "notas-credito";
            }
          });
        </script>';
        return;
    }
}

$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();

// Verificar configuración de Factus para rangos
$rangoNC = ModeloFactus::mdlObtenerRangoNC();
if (!$rangoNC) {
    echo '<script>
        swal({
            type: "error",
            title: "No hay rango de Nota Crédito activo",
            text: "Por favor configure un rango de numeración para Notas Crédito en Configuración Factus.",
            showConfirmButton: true
        }).then(function(result){
            window.location = "facturas-electronicas";
        });
    </script>';
    return;
}

// Importar modelos
require_once "modelos/productos.modelo.php";

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Crear Nota Crédito
            <?php if ($idVenta && $venta): ?>
                <small>Factura #<?php echo $venta["numero_factura"]; ?></small>
            <?php endif; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="notas-credito">Notas Crédito</a></li>
            <li class="active">Crear Nota Crédito</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">

            <!--=====================================
      EL FORMULARIO
      ======================================-->
            <div class="col-lg-12 col-xs-12">

                <div class="box box-danger">

                    <div class="box-header with-border">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="callout callout-info" style="margin-bottom:0; padding: 8px 15px;">
                                    <strong><i class="fa fa-file-text-o"></i> Número de Nota Crédito:</strong>
                                    <span class="label label-warning"
                                        style="font-size: 1.1em; padding: 4px 10px; margin-left: 8px;">
                                        <?php
                                        $prefijo = $rangoNC["prefijo"] ?? "NC";
                                        $proximoNumero = ModeloFactus::mdlObtenerSiguienteConsecutivoNC();
                                        echo htmlspecialchars($prefijo . $proximoNumero);
                                        ?>
                                    </span>
                                    <small style="color: white; margin-left: 10px;">
                                        (Rango: <?php echo htmlspecialchars($rangoNC["numero_desde"]); ?> -
                                        <?php echo htmlspecialchars($rangoNC["numero_hasta"]); ?>)
                                    </small>
                                    <?php if ($idVenta && $venta): ?>
                                        <small style="color: white; margin-left: 10px;">
                                            (Factura Referencia: <?php echo $venta["numero_factura"]; ?>)
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form role="form" method="post" class="formularioNotaCredito" id="formNotaCredito">
                        <input type="hidden" name="idUsuarioSesion" id="idUsuarioSesion" value="<?php echo $_SESSION['id'] ?? ''; ?>">

                        <div class="box-body">

                            <?php if (!$idVenta || !$venta): ?>

                                <!-- PANTALLA DE SELECCIÓN DE FACTURA ELECTRÓNICA -->
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
                                        <h3>Seleccione una Factura Electrónica</h3>
                                        <p class="text-muted">Elija la factura a la que desea aplicarle una nota crédito.
                                        </p>

                                        <div class="form-group" style="margin-top:20px; text-align: left;">
                                            <label>Factura Referencia *</label>
                                            <select class="form-control select2" id="seleccionarFacturaReferencia"
                                                style="width: 100%;">
                                                <option value="">Seleccione una Factura...</option>
                                                <?php
                                                // Cargar todas las facturas en estado "enviada" o "aceptada"
                                                $ventas = ControladorVentas::ctrMostrarVentas(null, null);
                                                foreach ($ventas as $key => $value) {
                                                    if ($value["estado_dian"] == "enviada" || $value["estado_dian"] == "aceptada") {
                                                        $clienteVenta = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
                                                        $nombreCliente = $clienteVenta ? $clienteVenta["nombre"] : "Cliente Desconocido";

                                                        echo '<option value="' . $value["id"] . '">' . $value["numero_factura"] . ' - ' . $nombreCliente . ' - $' . number_format($value["total"], 2) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            <?php else: ?>

                                <div class="box">

                                    <!--=====================================
                ENCABEZADO
                ======================================-->
                                    <div class="row">
                                        <!-- Factura Referencia -->
                                        <div class="col-xs-12 col-md-4">
                                            <div class="form-group">
                                                <label>Factura Referencia</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $venta["numero_factura"]; ?>" readonly>
                                                    <input type="hidden" name="idVenta" value="<?php echo $venta["id"]; ?>">
                                                    <input type="hidden" name="numeroFactura"
                                                        value="<?php echo $venta["numero_factura"]; ?>">
                                                </div>
                                                <!-- Botón para cambiar de Factura -->
                                                <a href="crear-nota-credito" class="btn btn-default btn-xs"
                                                    style="margin-top: 5px;"><i class="fa fa-exchange"></i> Cambiar
                                                    Factura</a>
                                            </div>
                                        </div>

                                        <!-- Cliente original de factura, readonly mejor opción para no perder integridad Factus-NC -->
                                        <div class="col-xs-12 col-md-4">
                                            <div class="form-group">
                                                <label>Cliente</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $cliente["nombre"]; ?>" readonly>
                                                    <input type="hidden" id="seleccionarCliente" name="seleccionarCliente"
                                                        value="<?php echo $cliente["id"]; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Vendedor -->
                                        <div class="col-xs-12 col-md-4">
                                            <div class="form-group">
                                                <label>Vendedor Original</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $vendedor["nombre"]; ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--=====================================


                            <hr>

                            <!--=====================================
                CONFIGURACIÓN DE LA NOTA
                ======================================-->
                                <div class="row">

                                    <!-- Método de Pago -->
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            <label>Método de Pago *</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                                <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago"
                                                    required>
                                                    <option value="">Seleccione método de pago</option>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="TC">Tarjeta Crédito</option>
                                                    <option value="TD">Tarjeta Débito</option>
                                                    <option value="Transf">Transferencia</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Consignacion">Consignación</option>
                                                    <option value="Bonos">Bonos</option>
                                                    <option value="Vales">Vales</option>
                                                    <option value="Otros">Otros</option>
                                                    <option value="No Definido">No Definido</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>

                            <br>

                            <!--=====================================
                                MOTIVO
                                ======================================-->
                            <div class="row">
                                <div class="col-xs-12 col-md-12">
                                    <div class="form-group">
                                        <label>Motivo *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                            <select class="form-control" name="motivoNota" id="motivoNota" required>
                                                <option value="1">Devolución parcial de los bienes y/o no aceptación parcial
                                                    del servicio</option>
                                                <option value="2">Anulación de factura electrónica</option>
                                                <option value="3">Rebaja o descuento parcial o total</option>
                                                <option value="4">Ajuste de precio</option>
                                                <option value="5">Descuento comercial por pronto pago</option>
                                                <option value="6">Descuento comercial por volumen de ventas</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!--=====================================
                                ENTRADA OBSERVACIONES
                                ======================================-->
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label>Observaciones (Opcional)</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                                            <textarea class="form-control" name="observacion" id="observacion" rows="3"
                                                placeholder="Escriba observaciones adicionales aquí..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!--=====================================
                TABLA DE PRODUCTOS
                ======================================-->
                            <h4>Seleccione los productos a devolver o ajustar:</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tablaProductosNC">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px; text-align: center;"><input type="checkbox"
                                                    id="checkTodo" checked></th>
                                            <th>Código</th>
                                            <th>Descripción</th>
                                            <th style="width: 100px;">Cant. Orig.</th>
                                            <th style="width: 120px;">Cant. Devolver</th>
                                            <th>Precio Unit.</th>
                                            <th>Subtotal Devolución</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($productos as $key => $prod) {
                                            // Buscamos info extra del producto si es necesario (ej. impuestos)
                                            // Por ahora usamos los datos guardados en la venta para integridad
                                            $totalFila = $prod["precio"] * $prod["cantidad"]; // Precio incluye impuesto según lógica pos
                                    
                                            // Recuperar código si falta
                                            $codigoProducto = $prod["codigo"] ?? "";
                                            if (empty($codigoProducto)) {
                                                $infoP = ModeloProductos::mdlMostrarProductos("productos", "id", $prod["id"], "id");
                                                $codigoProducto = $infoP["codigo"] ?? "";
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="checkProducto" name="productosSeleccionados[]"
                                                        value="<?php echo $key; ?>" checked>
                                                </td>
                                                <td><?php echo $codigoProducto; ?></td>
                                                <td><?php echo $prod["descripcion"]; ?></td>
                                                <td>
                                                    <input type="text" class="form-control input-sm"
                                                        value="<?php echo $prod["cantidad"]; ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-sm cantidadDevolver"
                                                        name="cantidad_<?php echo $key; ?>" min="1"
                                                        max="<?php echo $prod["cantidad"]; ?>"
                                                        value="<?php echo $prod["cantidad"]; ?>"
                                                        data-precio="<?php echo $prod["precio"]; ?>"
                                                        data-impuesto="<?php echo $prod["impuesto"]; ?>"
                                                        data-key="<?php echo $key; ?>">
                                                </td>
                                                <td>$<?php echo number_format($prod["precio"], 2); ?></td>
                                                <td class="subtotalFila">
                                                    $<?php echo number_format($prod["total"], 2); ?></td>

                                                <!-- Inputs ocultos para enviar datos -->
                                                <input type="hidden" name="idProducto_<?php echo $key; ?>"
                                                    value="<?php echo $prod["id"]; ?>">
                                                <input type="hidden" name="codigo_<?php echo $key; ?>"
                                                    value="<?php echo $codigoProducto; ?>">
                                                <input type="hidden" name="descripcion_<?php echo $key; ?>"
                                                    value="<?php echo $prod["descripcion"]; ?>">
                                                <input type="hidden" name="precio_<?php echo $key; ?>"
                                                    value="<?php echo $prod["precio"]; ?>">
                                                <input type="hidden" name="totalOriginal_<?php echo $key; ?>"
                                                    value="<?php echo $prod["total"]; ?>">
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-md-4 pull-right">
                                    <table class="table table-condensed table-bordered" style="background:#f9f9f9;">
                                        <tr>
                                            <td style="font-weight: bold;">Valor Bruto</td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                            class="ion ion-social-usd"></i></span>
                                                    <input type="text" class="form-control input-lg" id="nuevoTotalBase"
                                                        name="nuevoTotalBase" readonly
                                                        style="font-weight: bold; font-size: 1.2em;">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold;">Subtotal</td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                            class="ion ion-social-usd"></i></span>
                                                    <input type="text" class="form-control input-lg" id="nuevoTotalSubtotal"
                                                        name="nuevoTotalSubtotal" readonly
                                                        style="font-weight: bold; font-size: 1.2em;">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold;">Impuestos</td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                            class="ion ion-social-usd"></i></span>
                                                    <input type="text" class="form-control input-lg" id="nuevoTotalImpuesto"
                                                        name="nuevoTotalImpuesto" readonly
                                                        style="font-weight: bold; font-size: 1.2em;">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: bold;">Total Devolución</td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                            class="ion ion-social-usd"></i></span>
                                                    <input type="text" class="form-control input-lg" id="nuevoTotalNC"
                                                        name="nuevoTotalNC" readonly
                                                        style="font-weight: bold; font-size: 1.2em;">
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <input type="hidden" name="listaProductosNC" id="listaProductosNC">

                        <?php endif; ?> <!-- Fin validación idVenta -->

                </div> <!-- box-body -->

                <div class="box-footer">
                    <button type="button" class="btn btn-default pull-left"
                        onclick="window.location='notas-credito'">Cancelar</button>
                    <?php if ($idVenta && $venta): ?>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                    <?php endif; ?>
                </div>

                </form>

            </div>

        </div>

</div>
</section>
</div>

<!-- Importar Script específico para NC -->
<script src="vistas/js/notas-credito.js?v=<?php echo time(); ?>"></script>