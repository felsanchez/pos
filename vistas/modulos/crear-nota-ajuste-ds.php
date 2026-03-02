<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

// 1. Obtener datos del DS original si viene en la URL
$idDS = null;
$documentoSoporte = null;
$proveedor = null;
$productos = [];

if (isset($_GET["idDS"])) {
    $idDS = $_GET["idDS"];
    $documentoSoporte = ControladorFactus::ctrMostrarDocumentosSoporte("id", $idDS);

    if ($documentoSoporte) {
        $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $documentoSoporte["id_proveedor"]);
        // Decodificar productos
        $productos = json_decode($documentoSoporte["productos"], true);
    }
}

// Verificar rango de Notas de Ajuste
$rangoAjuste = ModeloFactus::mdlObtenerRangoAjusteDS();

if (!$rangoAjuste) {
    echo '<script>
        swal({
            type: "error",
            title: "No hay rango de Nota de Ajuste activo",
            text: "Por favor sincronice los rangos de numeración en Configuración Factus.",
            showConfirmButton: true
        }).then(function(result){
            window.location = "documentos-soporte";
        });
    </script>';
    return;
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Crear Nota de Ajuste DS
            <?php if ($idDS && $documentoSoporte): ?>
                <small>Documento Soporte #<?php echo $documentoSoporte["numero_ds"]; ?></small>
            <?php endif; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="notas-ajuste-ds">Notas de Ajuste</a></li>
            <li class="active">Crear Nota de Ajuste</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-warning">
            <div class="box-header with-border">
                <div class="row">
                    <div class="col-md-12">
                        <div class="callout callout-info" style="margin-bottom:0; padding: 8px 15px;">
                            <strong><i class="fa fa-file-text-o"></i> Número de Nota de Ajuste:</strong>
                            <span class="label label-primary"
                                style="font-size: 1.1em; padding: 4px 10px; margin-left: 8px;">
                                <?php
                                $prefijo = $rangoAjuste["prefijo"] ?? "NA";
                                $proximoNumero = ModeloFactus::mdlObtenerSiguienteConsecutivoNotaAjusteDS();
                                echo htmlspecialchars($prefijo . $proximoNumero);
                                ?>
                            </span>
                            <?php if ($idDS && $documentoSoporte): ?>
                                <small style="color: white; margin-left: 10px;">
                                    (Referencia: <?php echo $documentoSoporte["numero_ds"]; ?>)
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <form role="form" method="post" class="formularioNotaAjusteDS" id="formNotaAjusteDS">
                <div class="box-body">

                    <?php if (!$idDS || !$documentoSoporte): ?>

                        <!-- PANTALLA DE SELECCIÓN DE DOCUMENTO SOPORTE -->
                        <div class="row">
                            <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
                                <h3>Seleccione un Documento Soporte</h3>
                                <p class="text-muted">Elija el documento soporte al que desea aplicarle una nota de ajuste.
                                </p>

                                <div class="form-group" style="margin-top:20px; text-align: left;">
                                    <label>Documento Soporte Referencia *</label>
                                    <select class="form-control select2" id="seleccionarDSReferencia" style="width: 100%;">
                                        <option value="">Seleccione un Documento Soporte...</option>
                                        <?php
                                        // Cargar todos los DS que están en estado "enviada" (exitosos)
                                        $documentos = ControladorFactus::ctrMostrarDocumentosSoporte(null, null);
                                        foreach ($documentos as $key => $value) {
                                            if ($value["estado_dian"] == "enviada") {
                                                $provDS = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);
                                                $nombreProv = $provDS ? $provDS["nombre"] : "Proveedor Desconocido";

                                                echo '<option value="' . $value["id"] . '">' . $value["numero_ds"] . ' - ' . $nombreProv . ' - $' . number_format($value["monto_total"], 2) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- ENCABEZADO DE LA NOTA DE AJUSTE -->
                        <div class="row">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>Documento Soporte Referencia</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                                        <input type="text" class="form-control"
                                            value="<?php echo $documentoSoporte["numero_ds"]; ?>" readonly>
                                        <input type="hidden" name="idDS" id="idDS"
                                            value="<?php echo $documentoSoporte["id"]; ?>">
                                    </div>
                                    <!-- Botón para cambiar de DS -->
                                    <a href="crear-nota-ajuste-ds" class="btn btn-default btn-xs"
                                        style="margin-top: 5px;"><i class="fa fa-exchange"></i> Cambiar Documento</a>
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-users"></i></span>
                                        <input type="text" class="form-control" value="<?php echo $proveedor["nombre"]; ?>"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <label>Método de Pago</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                        <input type="text" class="form-control"
                                            value="<?php echo $documentoSoporte["metodo_pago"]; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- CONFIGURACIÓN DE LA NOTA -->
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label>Motivo de Ajuste *</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-comment"></i></span>
                                        <select class="form-control" name="motivoNotaDS" id="motivoNotaDS" required>
                                            <option value="1">Devolución parcial de los bienes y/o no aceptación parcial del
                                                servicio</option>
                                            <option value="2">Anulación de documento soporte</option>
                                            <option value="3">Rebaja o descuento parcial o total</option>
                                            <option value="4">Ajuste de precio</option>
                                            <option value="5">Otros</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>Descripción del Motivo (Opcional)</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-commenting"></i></span>
                                        <textarea class="form-control" name="motivoDescDS" id="motivoDescDS" rows="2"
                                            placeholder="Escriba el motivo detallado del ajuste..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>Método de Pago *</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                        <select class="form-control" name="metodoPagoDS" id="metodoPagoDS" required>
                                            <?php
                                            $metodosPago = [
                                                "Efectivo" => "Efectivo",
                                                "Cheque" => "Cheque",
                                                "Consignacion" => "Consignacion",
                                                "Transferencia" => "Transferencia",
                                                "Tarjeta Crédito" => "Tarjeta Crédito",
                                                "Tarjeta Débito" => "Tarjeta Débito",
                                                "Bonos" => "Bonos",
                                                "Vales" => "Vales",
                                                "Otros" => "Otros"
                                            ];
                                            foreach ($metodosPago as $valor => $etiqueta) {
                                                $selected = ($documentoSoporte["metodo_pago"] == $valor) ? 'selected' : '';
                                                echo "<option value=\"$valor\" $selected>$etiqueta</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- TABLA DE PRODUCTOS -->
                        <h4>Productos del Documento Original:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaProductosAdjDS">
                                <thead>
                                    <tr>
                                        <th style="width: 50px; text-align: center;"><input type="checkbox" id="checkTodoDS"
                                                checked></th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th style="width: 100px;">Cant. Orig.</th>
                                        <th style="width: 120px;">Cant. Ajuste</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal Ajuste</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($productos as $key => $prod) {
                                        $totalFila = $prod["precio"] * $prod["cantidad"];
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="checkProductoDS" name="productosSeleccionadosDS[]"
                                                    value="<?php echo $key; ?>" checked>
                                            </td>
                                            <td>
                                                <?php echo $prod["codigo"] ?? "N/A"; ?>
                                            </td>
                                            <td>
                                                <?php echo $prod["descripcion"]; ?>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control input-sm"
                                                    value="<?php echo $prod["cantidad"]; ?>" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control input-sm cantidadAjusteDS"
                                                    name="cantidad_<?php echo $key; ?>" min="1"
                                                    max="<?php echo $prod["cantidad"]; ?>"
                                                    value="<?php echo $prod["cantidad"]; ?>"
                                                    data-precio="<?php echo $prod["precio"]; ?>" data-key="<?php echo $key; ?>">
                                            </td>
                                            <td>$
                                                <?php echo number_format($prod["precio"], 2); ?>
                                            </td>
                                            <td class="subtotalFilaAdjDS">$
                                                <?php echo number_format($totalFila, 2); ?>
                                            </td>

                                            <!-- Inputs ocultos -->
                                            <input type="hidden" name="idProducto_<?php echo $key; ?>"
                                                value="<?php echo $prod["id"]; ?>">
                                            <input type="hidden" name="descripcion_<?php echo $key; ?>"
                                                value="<?php echo $prod["descripcion"]; ?>">
                                            <input type="hidden" name="precio_<?php echo $key; ?>"
                                                value="<?php echo $prod["precio"]; ?>">
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-md-4 pull-right">
                                <table class="table table-condensed table-bordered" style="background:#f9f9f9;">
                                    <tr>
                                        <td style="font-weight: bold; width: 50%;">Total Ajuste</td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon">$</span>
                                                <input type="text" class="form-control input-lg" id="nuevoTotalAdjDS"
                                                    name="nuevoTotalAdjDS" readonly
                                                    style="font-weight: bold; font-size: 1.2em;">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="listaProductosAdjDS" id="listaProductosAdjDS">
                        <input type="hidden" name="idUsuario" value="<?php echo $_SESSION["id"]; ?>">

                    <?php endif; ?> <!-- Fin validación IDDS -->

                </div>

                <div class="box-footer">
                    <button type="button" class="btn btn-default pull-left"
                        onclick="window.location='notas-ajuste-ds'">Cancelar</button>
                    <?php if ($idDS && $documentoSoporte): ?>
                        <button type="submit" class="btn btn-warning pull-right">Guardar Borrador de Nota</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>
</div>

<script src="vistas/js/notas-ajuste-ds.js?v=<?php echo time(); ?>"></script>