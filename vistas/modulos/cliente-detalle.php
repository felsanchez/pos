<?php
// Obtener estados de clientes
require_once "controladores/estados-clientes.controlador.php";
require_once "modelos/estados-clientes.modelo.php";

// Determinar si es modo edición
$modoEdicion = isset($_GET['id']) && !empty($_GET['id']);
$cliente = null;

if ($modoEdicion) {
    $item = "id";
    $valor = $_GET['id'];
    $cliente = ControladorClientes::ctrMostrarClientes($item, $valor);

    // DEBUG: Descomentar para ver los datos del cliente
    // echo "<pre style='background: white; padding: 20px; margin: 20px;'>";
    // echo "MODO EDICION - Cliente ID: " . $_GET['id'] . "\n\n";
    // echo "Estructura del cliente:\n";
    // echo json_encode($cliente, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // echo "\n\nArray print_r:\n";
    // print_r($cliente);
    // echo "</pre>";
    // die();

    if (!$cliente || !is_array($cliente)) {
        echo '<script>window.location = "clientes";</script>';
        return;
    }
}

// Helper function para obtener valores del cliente de forma segura
function obtenerValor($cliente, $campo, $default = '') {
    if ($cliente && isset($cliente[$campo])) {
        return $cliente[$campo];
    }
    return $default;
}

// Obtener estados disponibles
$estadosDisponibles = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);

// Detectar si viene de contactos
$esContacto = isset($_GET['origen']) && $_GET['origen'] === 'contactos';
$tipoEntidad = $esContacto ? 'Contacto' : 'Cliente';
$rutaVolver = $esContacto ? 'contactos' : 'clientes';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <?php echo $modoEdicion ? 'Editar ' . $tipoEntidad : 'Nuevo ' . $tipoEntidad; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="<?php echo $rutaVolver; ?>"><?php echo $esContacto ? 'Contactos' : 'Clientes'; ?></a></li>
            <li class="active"><?php echo $modoEdicion ? 'Editar' : 'Nuevo'; ?></li>
        </ol>
    </section>

    <section class="content">
        <form role="form" method="post" id="formCliente">

            <?php if ($modoEdicion): ?>
                <input type="hidden" name="idCliente" value="<?php echo $cliente['id']; ?>">
            <?php endif; ?>

            <!-- Campo oculto para saber a qué vista regresar -->
            <input type="hidden" name="vistaOrigen" value="<?php echo isset($_GET['origen']) ? $_GET['origen'] : 'clientes'; ?>">

            <!-- ============================================= -->
            <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
            <!-- ============================================= -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user"></i> Información Básica</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <!-- Tipo de Persona -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Persona *</label>
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="<?php echo $modoEdicion ? 'editarTipoPersona' : 'nuevoTipoPersona'; ?>" 
                                               value="natural" id="tipoPersonaNatural"
                                               <?php echo (!$modoEdicion || (isset($cliente['tipo_persona']) && $cliente['tipo_persona'] == 'natural')) ? 'checked' : ''; ?>>
                                        Persona Natural
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="<?php echo $modoEdicion ? 'editarTipoPersona' : 'nuevoTipoPersona'; ?>" 
                                               value="juridica" id="tipoPersonaJuridica"
                                               <?php echo ($modoEdicion && isset($cliente['tipo_persona']) && $cliente['tipo_persona'] == 'juridica') ? 'checked' : ''; ?>>
                                        Persona Jurídica
                                    </label>
                                </div>
                            </div>
                        </div>                        
                    </div>


                    <div class="row">
                        <!-- Tipo de Documento (DIAN) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Documento *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                    <select class="form-control" id="tipoDocumento"
                                            name="<?php echo $modoEdicion ? 'editarTipoDocumento' : 'nuevoTipoDocumento'; ?>"
                                            required>
                                        <option value="">Seleccione tipo de documento</option>
                                        <?php
                                        // Obtener tipos de documento dinámicamente
                                        $tiposDocumento = ControladorFactus::ctrMostrarTiposDocumento();
                                        foreach ($tiposDocumento as $tipo) {
                                            $selected = '';
                                            if ($modoEdicion && isset($cliente['tipo_documento_id']) && $cliente['tipo_documento_id'] == $tipo['id']) {
                                                $selected = 'selected';
                                            } elseif (!$modoEdicion && $tipo['id'] == 3) { 
                                                // Preseleccionar Cédula (ID 3) por defecto en nuevo cliente
                                                $selected = 'selected';
                                            }
                                            
                                            // Mostrar solo nombre para mayor limpieza visual
                                            $label = $tipo['nombre'];
                                            
                                            echo '<option value="' . $tipo['id'] . '" ' . $selected . '>' . $label . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <p class="help-block">Tipo de documento para facturación electrónica</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Número de Documento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Número de Documento *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                    <input type="number" class="form-control" id="documento"
                                           name="<?php echo $modoEdicion ? 'editarDocumentoId' : 'nuevoDocumentoId'; ?>"
                                           placeholder="Ingrese el número de documento"
                                           value="<?php echo $modoEdicion ? $cliente['documento'] : ''; ?>"
                                           min="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Dígito de Verificación (solo para NIT) -->
                        <div class="col-md-4" id="contenedorDV" style="display: none;">
                            <div class="form-group">
                                <label>Dígito de Verificación</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                    <input type="text" class="form-control" id="digitoVerificacion" maxlength="1"
                                           name="<?php echo $modoEdicion ? 'editarDigitoVerificacion' : 'nuevoDigitoVerificacion'; ?>"
                                           placeholder="DV"
                                           value="<?php echo $modoEdicion && isset($cliente['digito_verificacion']) ? $cliente['digito_verificacion'] : ''; ?>">
                                </div>
                                <p class="help-block">Obligatorio para NIT</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Nombre / Razón Social -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label id="labelNombre">Nombre Completo *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control" id="nombre"
                                           name="<?php echo $modoEdicion ? 'editarCliente' : 'nuevoCliente'; ?>"
                                           placeholder="Ingrese el nombre"
                                           value="<?php echo $modoEdicion ? obtenerValor($cliente, 'nombre') : ''; ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Razón Social (solo para Persona Jurídica) -->
                        <div class="col-md-6" id="contenedorRazonSocial" style="display: none;">
                            <div class="form-group">
                                <label>Razón Social *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control" id="razonSocial"
                                           name="<?php echo $modoEdicion ? 'editarRazonSocial' : 'nuevaRazonSocial'; ?>"
                                           placeholder="Nombre legal de la empresa"
                                           value="<?php echo $modoEdicion && isset($cliente['razon_social']) ? $cliente['razon_social'] : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Nombre Comercial (solo para Persona Jurídica) -->
                        <div class="col-md-6" id="contenedorNombreComercial" style="display: none;">
                            <div class="form-group">
                                <label>Nombre Comercial</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                    <input type="text" class="form-control" id="nombreComercial"
                                           name="<?php echo $modoEdicion ? 'editarNombreComercial' : 'nuevoNombreComercial'; ?>"
                                           placeholder="Nombre comercial"
                                           value="<?php echo $modoEdicion && isset($cliente['nombre_comercial']) ? $cliente['nombre_comercial'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Estado del Cliente -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado del Cliente</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                                    <select class="form-control"
                                            name="<?php echo $modoEdicion ? 'editarEstado' : 'nuevoEstatus'; ?>">
                                        <?php
                                        $estadoActual = $modoEdicion && isset($cliente['estatus']) ? $cliente['estatus'] : 'nuevo';
                                        if (is_array($estadosDisponibles) && count($estadosDisponibles) > 0):
                                            foreach ($estadosDisponibles as $estado):
                                                $selected = (strcasecmp($estadoActual, $estado['nombre']) == 0) ? 'selected' : '';
                                        ?>
                                                <option value="<?php echo $estado['nombre']; ?>" <?php echo $selected; ?>>
                                                    <?php echo ucfirst($estado['nombre']); ?>
                                                </option>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                            <option value="nuevo" selected>Nuevo</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================= -->
            <!-- SECCIÓN 2: DATOS DE CONTACTO -->
            <!-- ============================================= -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-phone"></i> Datos de Contacto</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                    <input type="email" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarEmail' : 'nuevoEmail'; ?>"
                                           placeholder="correo@ejemplo.com"
                                           value="<?php echo $modoEdicion ? $cliente['email'] : ''; ?>">
                                </div>
                                <p class="help-block">Se usará para envío de facturas electrónicas</p>
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                    <input type="text" class="form-control" id="telefono"
                                           name="<?php echo $modoEdicion ? 'editarTelefono' : 'nuevoTelefono'; ?>"
                                           placeholder="(300) 123-4567"
                                           value="<?php echo $modoEdicion ? $cliente['telefono'] : ''; ?>"
                                           data-inputmask="'mask':'(999) 999-9999'" data-mask required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--
                    COMENTADO: Ahora se usa Municipio de Factus en lugar de ciudad/departamento
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Departamento</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarDepartamento' : 'nuevoDepartamento'; ?>"
                                           placeholder="Ej: Antioquia, Cundinamarca"
                                           value="<?php echo $modoEdicion ? $cliente['departamento'] : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                    <input type="text" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarCiudad' : 'nuevoCiudad'; ?>"
                                           placeholder="Ej: Medellín, Bogotá"
                                           value="<?php echo $modoEdicion ? $cliente['ciudad'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    -->


                    <div class="row">
                        <!-- Municipio (Factus) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Municipio (Factus) *</label>
                                <select class="form-control" id="selectMunicipio"
                                        name="<?php echo $modoEdicion ? 'editarMunicipio' : 'nuevoMunicipio'; ?>"
                                        required>
                                    <option value="">-- Seleccionar Municipio --</option>
                                    <?php
                                    require_once "modelos/factus.modelo.php";
                                    $municipios = ModeloFactus::mdlObtenerMunicipios();
                                    $municipioActual = $modoEdicion && isset($cliente['municipio_id']) ? $cliente['municipio_id'] : '';
                                    foreach ($municipios as $municipio) {
                                        $selected = ($municipioActual == $municipio['id_factus']) ? 'selected' : '';
                                        $textoMunicipio = $municipio['nombre'] . ' - ' . $municipio['departamento'];
                                        echo "<option value='{$municipio['id_factus']}' $selected>{$textoMunicipio}</option>";
                                    }
                                    ?>
                                </select>
                                <p class="help-block">Municipio para facturación electrónica</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Dirección -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Dirección *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-home"></i></span>
                                    <input type="text" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarDireccion' : 'nuevaDireccion'; ?>"
                                           placeholder="Calle, carrera, número, etc."
                                           value="<?php echo $modoEdicion ? $cliente['direccion'] : ''; ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fecha de Nacimiento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Nacimiento</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="date" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarFechaNacimiento' : 'nuevaFechaNacimiento'; ?>"
                                           value="<?php echo $modoEdicion && isset($cliente['fecha_nacimiento']) && $cliente['fecha_nacimiento'] != '0000-00-00 00:00:00' ? date('Y-m-d', strtotime($cliente['fecha_nacimiento'])) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notas Adicionales</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                                    <textarea class="form-control" rows="3"
                                              name="<?php echo $modoEdicion ? 'editarNota' : 'nuevaNota'; ?>"
                                              placeholder="Información adicional sobre el cliente"><?php echo $modoEdicion && isset($cliente['notas']) ? $cliente['notas'] : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================= -->
            <!-- SECCIÓN 3: INFORMACIÓN DE FACTURACIÓN ELECTRÓNICA -->
            <!-- ============================================= -->
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text"></i> Información de Facturación Electrónica (Factus)</h3>
                </div>
                <div class="box-body">
                    <p class="help-block"><i class="fa fa-info-circle"></i> Estos datos son requeridos para generar facturas electrónicas válidas ante la DIAN.</p>
                    <div class="row">
                        <!-- Responsabilidades Fiscales (DIAN) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Responsabilidades Fiscales *</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-list"></i></span>
                                    <select class="form-control"
                                            name="<?php echo $modoEdicion ? 'editarResponsabilidades' : 'nuevasResponsabilidades'; ?>"
                                            required>
                                        <option value="">-- Seleccionar --</option>
                                        <?php
                                        $respActual = $modoEdicion && isset($cliente['responsabilidades_fiscales']) ? $cliente['responsabilidades_fiscales'] : 'R-99-PN';
                                        $listaResponsabilidades = [
                                            "R-99-PN" => "R-99-PN: No responsable (Persona Natural)",
                                            "O-13" => "O-13: Gran Contribuyente",
                                            "O-15" => "O-15: Autorretenedor",
                                            "O-23" => "O-23: Agente de Retención IVA",
                                            "O-47" => "O-47: Régimen Simple de Tributación",
                                            "ZY" => "ZY: No responsable de IVA (Persona Jurídica)"
                                        ];

                                        foreach ($listaResponsabilidades as $codigo => $descripcion) {
                                            $selected = ($respActual == $codigo) ? 'selected' : '';
                                            echo "<option value='{$codigo}' {$selected}>{$descripcion}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <p class="help-block">Código de responsabilidad fiscal ante la DIAN</p>
                            </div>
                        </div>

                        <!-- Código Postal -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código Postal</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
                                    <input type="text" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarCodigoPostal' : 'nuevoCodigoPostal'; ?>"
                                           placeholder="Código postal"
                                           value="<?php echo $modoEdicion && isset($cliente['codigo_postal']) ? $cliente['codigo_postal'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="box">
                <div class="box-footer">
                    <a href="<?php echo $rutaVolver; ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> <?php echo $modoEdicion ? 'Actualizar ' . $tipoEntidad : 'Guardar ' . $tipoEntidad; ?>
                    </button>
                </div>
            </div>

        </form>
    </section>
</div>

<script src="vistas/js/cliente-detalle.js?v=<?php echo time(); ?>"></script>

<?php
// Procesar formulario
if ($modoEdicion) {
    $editarCliente = new ControladorClientes();
    $editarCliente->ctrEditarCliente();
} else {
    $crearCliente = new ControladorClientes();
    $crearCliente->ctrCrearCliente();
}
?>
