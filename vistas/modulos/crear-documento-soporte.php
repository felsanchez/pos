<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$impuestoDefecto = !empty($configuracion["impuesto_defecto"]) ? $configuracion["impuesto_defecto"] : 0;

$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Transferencia", "Cheque");

require_once "modelos/factus.modelo.php";

$rangoDS = ModeloFactus::mdlObtenerRangoDS();

// ---------------------------------------------------------
// VALIDACIÓN DE CONSECUTIVO (DRAFT BLOCKING)
// ---------------------------------------------------------
$ultimoDS = ControladorFactus::ctrMostrarUltimoDocumentoSoporte();

if ($ultimoDS) {
    $estadosValidos = ['enviada', 'aceptada'];
    if (!in_array($ultimoDS["estado_dian"], $estadosValidos) || empty($ultimoDS["numero_ds"])) {
        echo '
        <script>
          swal({
            type: "warning",
            title: "Bloqueo de Consecutivo",
            text: "No se puede crear un nuevo Documento Soporte porque el anterior aún no ha sido FIRMADO y ENVIADO a la DIAN. Debe firmar los documentos en orden secuencial.",
            showConfirmButton: true,
            confirmButtonText: "Ir a Documentos Soporte"
          }).then(function (result) {
            if (result.value) {
              window.location = "documentos-soporte";
            }
          });
        </script>';
        return;
    }
}
?>

<?php
$crearDS = new ControladorFactus();
$respuesta = $crearDS->ctrCrearDocumentoSoporte();

if (isset($respuesta) && $respuesta["error"] == false) {
    echo '<script>
      swal({
        type: "success",
        title: "¡El Documento Soporte ha sido generado correctamente!",
        text: "Número: ' . $respuesta["numero"] . '",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      }).then(function(result){
        window.location = "documentos-soporte";
      });
    </script>';
} else if (isset($respuesta) && $respuesta["error"] == true) {
    echo '<script>
      swal({
        type: "error",
        title: "¡Error al generar Documento Soporte!",
        text: "' . addslashes($respuesta["mensaje"]) . '",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      });
    </script>';
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Emitir Documento Soporte
        </h1>

        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="documentos-soporte">Documentos Soporte</a></li>
            <li class="active">Emitir Documento Soporte</li>
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

                    <form role="form" method="post" class="formularioDocumentoSoporte">

                        <div class="box-body">

                            <div class="box">

                                <div class="row">
                                    <!-- Usuario (Quien emite) -->
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            <label>Usuario</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                                <input type="text" class="form-control" id="nuevoUsuarioDS"
                                                    name="nuevoUsuarioDS" value="<?php echo $_SESSION["nombre"]; ?>"
                                                    readonly>
                                                <input type="hidden" name="idUsuario"
                                                    value="<?php echo $_SESSION["id"]; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Número sugerido DS (Temporal) -->
                                    <div class="col-xs-12 col-md-6">
                                        <div class="form-group">
                                            <label>Referencia DS</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                <?php
                                                $prefijo = $rangoDS["prefijo"] ?? "DS";
                                                $proximo = ($rangoDS["numero_actual"] ?? 0) + 1;
                                                $numeroSugerido = $prefijo . $proximo;
                                                ?>
                                                <input type="text" class="form-control" id="nuevoCodigoDS"
                                                    name="nuevoCodigoDS" value="<?php echo $numeroSugerido; ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Proveedor -->
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label>Proveedor</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                                <select class="form-control" id="seleccionarProveedor"
                                                    name="seleccionarProveedor" required>
                                                    <option value="">Seleccionar proveedor</option>
                                                    <?php
                                                    $item = null;
                                                    $valor = null;
                                                    $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
                                                    
                                                    // Obtener tipos de documento para mapear el nombre
                                                    $tiposDocumento = ControladorFactus::ctrMostrarTiposDocumento();
                                                    $mapaTipos = [];
                                                    foreach($tiposDocumento as $td){
                                                        $mapaTipos[$td["id"]] = strtoupper($td["nombre"]);
                                                    }

                                                    foreach ($proveedores as $key => $value) {
                                                        $nombreTipo = isset($mapaTipos[$value["tipo_documento_id"]]) ? $mapaTipos[$value["tipo_documento_id"]] : "DESCONOCIDO";
                                                        echo '<option value="' . $value["id"] . '" tipoDocumentoId="' . $value["tipo_documento_id"] . '" nombreTipo="' . $nombreTipo . '">' . $value["nombre"] . ' (' . ($value["marca"] != "" ? $value["marca"] : "Sin Marca") . ')</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Entrada para agregar productos -->
                                <div class="form-group row nuevoProducto">
                                    <!-- Aquí se cargarán los productos seleccionados -->
                                </div>

                                <input type="hidden" id="listaProductosDS" name="listaProductosDS">

                                <hr>

                                <!-- Totales -->
                                <div class="row">
                                    <div class="col-xs-12 col-md-6 pull-right">
                                        <table class="table table-condensed table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td style="font-weight: bold;">Subtotal (Sin Desc)</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="fa fa-usd"></i></span>
                                                            <input type="text" class="form-control"
                                                                id="nuevoSubtotalSinDescDS"
                                                                name="nuevoSubtotalSinDescDS" value="0" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: bold;">Subtotal (Con Desc)</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="fa fa-usd"></i></span>
                                                            <input type="text" class="form-control" id="nuevoSubtotalDS"
                                                                name="nuevoSubtotalDS" value="0" readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: bold; font-size: 1.1em;">Total</td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><i
                                                                    class="fa fa-usd"></i></span>
                                                            <input type="text" class="form-control input-lg"
                                                                id="nuevoTotalDS" name="nuevoTotalDS" value="0" readonly
                                                                style="font-weight: bold;">
                                                            <input type="hidden" name="totalDS" id="totalDS">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- SECCIÓN DE RETENCIONES -->
                                    <div class="col-xs-12 pull-left" id="seccionRetencionesDS"
                                        style="display: none; margin-top: 10px;">
                                        <div class="alert alert-info">
                                            <h4><i class="icon fa fa-info-circle"></i> Retenciones Aplicadas</h4>
                                            <div id="listaRetencionesDS"></div>
                                            <input type="hidden" id="datosRetencionesDS" name="datosRetencionesDS"
                                                value="">
                                        </div>
                                    </div>

                                </div>

                                <hr>

                                <!-- Método de Pago -->
                                <div class="form-group">
                                    <label>Método de Pago</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                        <select class="form-control" id="nuevoMetodoPagoDS" name="nuevoMetodoPagoDS"
                                            required>
                                            <option value="">Seleccione método de pago</option>
                                            <?php
                                            foreach ($mediosPago as $medio) {
                                                echo '<option value="' . $medio . '">' . $medio . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <!-- SECCIÓN DE DESCUENTOS -->
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label style="font-weight: normal; cursor: pointer;">
                                                <input type="checkbox" id="checkDescuentoPorcentajeDS"
                                                    name="checkDescuentoPorcentajeDS"
                                                    style="margin-right: 5px; transform: scale(1.2);">
                                                Agregar descuento por %
                                            </label>
                                            &nbsp;&nbsp;&nbsp;
                                            <label style="font-weight: normal; cursor: pointer;">
                                                <input type="checkbox" id="checkDescuentoFijoDS"
                                                    name="checkDescuentoFijoDS"
                                                    style="margin-right: 5px; transform: scale(1.2);">
                                                Agregar descuento por valor fijo
                                            </label>
                                        </div>

                                        <div class="form-group" id="campoDescuentoDS" style="display: none;">
                                            <div class="input-group">
                                                <span class="input-group-addon" id="iconoDescuentoDS"><i
                                                        class="fa fa-percent"></i></span>
                                                <input type="number" class="form-control input-lg" min="0"
                                                    id="valorDescuentoDS" name="valorDescuentoDS" placeholder="0"
                                                    value="0">
                                                <span class="input-group-addon" id="labelDescuentoDS">Descuento</span>
                                            </div>
                                        </div>
                                        <input type="hidden" id="tipoDescuentoDS" name="tipoDescuentoDS" value="">
                                        <input type="hidden" id="montoDescuentoDS" name="montoDescuentoDS" value="0">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12">
                                        <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target="#modalAgregarRetencionDS">Retenciones</button>
                                    </div>
                                </div>


                            </div>

                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">Emitir Documento</button>
                        </div>

                    </form>

                </div>

            </div>

            <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->

            <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

                <div class="box box-warning">

                    <div class="box-header with-border"></div>

                    <div class="box-body">

                        <table class="table table-bordered table-striped tablaProductosDS">
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
MODAL AGREGAR RETENCION DS
======================================-->
<div id="modalAgregarRetencionDS" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post" id="formularioRetencionDS">
                <div class="modal-header" style="background:#3c8dbc; color: white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Agregar Retención (Doc. Soporte)</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Tipo Retención</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                                <select class="form-control input-lg" id="nuevoTipoRetencionDS"
                                    name="nuevoTipoRetencionDS">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="ReteRenta">ReteRenta</option>

                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Porcentaje</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                <select class="form-control input-lg" id="nuevoPorcentajeRetencionDS"
                                    name="nuevoPorcentajeRetencionDS">
                                    <option value="">Seleccionar porcentaje</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-primary" id="guardarRetencionDS"
                        data-dismiss="modal">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#nuevoTipoRetencionDS').change(function () {
            var tipo = $(this).val();
            var selectPorcentaje = $('#nuevoPorcentajeRetencionDS');
            selectPorcentaje.html('<option value="">Seleccionar porcentaje</option>');
            if (tipo === 'ReteRenta') {
                var porcentajes = ['0.10', '0.50', '1.00', '1.50', '2.00', '2.50', '3.00', '3.50', '4.00', '6.00', '7.00', '10.00', '11.00', '20.00'];
                porcentajes.forEach(function (p) {
                    selectPorcentaje.append('<option value="' + p + '">' + p + '%</option>');
                });
            }

        });

        $('#checkDescuentoPorcentajeDS').on('change', function () {
            if ($(this).is(':checked')) {
                $('#checkDescuentoFijoDS').prop('checked', false);
                $('#campoDescuentoDS').slideDown();
                $('#iconoDescuentoDS').html('<i class="fa fa-percent"></i>');
                $('#labelDescuentoDS').text('% Descuento');
                $('#tipoDescuentoDS').val('porcentaje');
            } else {
                $('#campoDescuentoDS').slideUp();
                $('#tipoDescuentoDS').val('');
                $('#valorDescuentoDS').val(0);
                aplicarDescuentoDS();
            }
        });

        $('#checkDescuentoFijoDS').on('change', function () {
            if ($(this).is(':checked')) {
                $('#checkDescuentoPorcentajeDS').prop('checked', false);
                $('#campoDescuentoDS').slideDown();
                $('#iconoDescuentoDS').html('<i class="fa fa-money"></i>');
                $('#labelDescuentoDS').text('Valor Descuento');
                $('#tipoDescuentoDS').val('fijo');
            } else {
                $('#campoDescuentoDS').slideUp();
                $('#tipoDescuentoDS').val('');
                $('#valorDescuentoDS').val(0);
                aplicarDescuentoDS();
            }
        });

        $('#valorDescuentoDS').on('change keyup', function () {
            aplicarDescuentoDS();
        });
    });
</script>