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

// Recuperar datos de sesión en caso de error previo
$datosSession = null;
if (isset($_SESSION["datos_cliente_error"])) {
    $datosSession = $_SESSION["datos_cliente_error"];
    unset($_SESSION["datos_cliente_error"]);
}

// Obtener estados disponibles
$estadosDisponibles = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);

// Detectar si viene de contactos
$esContacto = isset($_GET['origen']) && $_GET['origen'] === 'contactos';
$tipoEntidad = $esContacto ? 'Contacto' : 'Cliente';
$rutaVolver = $esContacto ? 'contactos' : 'clientes';

// Prefijo para los campos según el modo
$prefix = $modoEdicion ? 'editar' : 'nuevo';

// Helper function para obtener valores del cliente de forma segura (Prioridad: Sesión > DB > Default)
function obtenerValorForm($campoForm, $campoDB, $datosSession, $cliente, $default = '') {
    if ($datosSession && isset($datosSession[$campoForm])) {
        return $datosSession[$campoForm];
    }
    if ($cliente && isset($cliente[$campoDB])) {
        return $cliente[$campoDB];
    }
    return $default;
}

/**
 * @deprecated Use obtenerValorForm instead for form fields
 */
function obtenerValor($cliente, $campo, $default = '') {
    if ($cliente && isset($cliente[$campo])) {
        return $cliente[$campo];
    }
    return $default;
}
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

        <?php CSRF::insertToken(); ?>

            <?php if ($modoEdicion): ?>
                <input type="hidden" name="idCliente" value="<?php echo $cliente['id']; ?>">
            <?php endif; ?>

            <!-- Campo oculto para saber a qué vista regresar -->
            <input type="hidden" name="vistaOrigen" value="<?php echo isset($_GET['origen']) ? $_GET['origen'] : 'clientes'; ?>">

            <!-- Campo oculto para permanecer en la vista actual en caso de error -->
            <?php
            $urlActual = "cliente-detalle";
            $params = [];
            if (isset($_GET['id'])) {
                $params[] = "id=" . $_GET['id'];
            }
            if (isset($_GET['origen'])) {
                $params[] = "origen=" . $_GET['origen'];
            }
            if (!empty($params)) {
                $urlActual .= "?" . implode("&", $params);
            }
            ?>
            <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

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
                                <?php
                                $tipoPersonaActual = obtenerValorForm($prefix . 'TipoPersona', 'tipo_persona', $datosSession, $cliente, 'natural');
                                ?>
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="<?php echo $modoEdicion ? 'editarTipoPersona' : 'nuevoTipoPersona'; ?>" 
                                               value="natural" id="tipoPersonaNatural"
                                               <?php echo ($tipoPersonaActual == 'natural') ? 'checked' : ''; ?>>
                                        Persona Natural
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="<?php echo $modoEdicion ? 'editarTipoPersona' : 'nuevoTipoPersona'; ?>" 
                                               value="juridica" id="tipoPersonaJuridica"
                                               <?php echo ($tipoPersonaActual == 'juridica') ? 'checked' : ''; ?>>
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
                                        $tipoDocActual = obtenerValorForm($prefix . 'TipoDocumento', 'tipo_documento_id', $datosSession, $cliente, 3);
                                        
                                        foreach ($tiposDocumento as $tipo) {
                                            $selected = ($tipoDocActual == $tipo['id']) ? 'selected' : '';
                                            
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
                                           value="<?php echo obtenerValorForm($prefix . 'DocumentoId', 'documento', $datosSession, $cliente); ?>"
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
                                           value="<?php echo obtenerValorForm($prefix . 'DigitoVerificacion', 'digito_verificacion', $datosSession, $cliente); ?>">
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
                                           value="<?php echo obtenerValorForm($prefix . ($modoEdicion ? 'Cliente' : 'Cliente'), 'nombre', $datosSession, $cliente); ?>"
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
                                           value="<?php echo obtenerValorForm(($modoEdicion ? 'editar' : 'nueva') . 'RazonSocial', 'razon_social', $datosSession, $cliente); ?>">
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
                                           value="<?php echo obtenerValorForm($prefix . 'NombreComercial', 'nombre_comercial', $datosSession, $cliente); ?>">
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
                                        $campoFormEstatus = $modoEdicion ? 'editarEstado' : 'nuevoEstatus';
                                        $estadoActual = obtenerValorForm($campoFormEstatus, 'estatus', $datosSession, $cliente, 'nuevo');
                                        
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

                    <?php if ($modoEdicion): ?>
                    <div class="row">
                        <!-- Última compra -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Última Compra</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-shopping-cart"></i></span>
                                    <input type="text" class="form-control" 
                                           value="<?php echo obtenerValorForm('', 'ultima_compra', $datosSession, $cliente); ?>" 
                                           readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Ingreso al sistema -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ingreso al Sistema</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar-check-o"></i></span>
                                    <input type="text" class="form-control" 
                                           value="<?php echo obtenerValorForm('', 'fecha', $datosSession, $cliente); ?>" 
                                           readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
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
                                            value="<?php echo obtenerValorForm($prefix . 'Email', 'email', $datosSession, $cliente); ?>">
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
                                            value="<?php echo obtenerValorForm($prefix . 'Telefono', 'telefono', $datosSession, $cliente); ?>"
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
                        <!-- Municipio -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Municipio *</label>
                                <select class="form-control" id="selectMunicipio"
                                        name="<?php echo $modoEdicion ? 'editarMunicipio' : 'nuevoMunicipio'; ?>"
                                        required>
                                    <option value="">-- Seleccionar Municipio --</option>
                                    <?php
                                    require_once "modelos/factus.modelo.php";
                                    $municipios = ModeloFactus::mdlObtenerMunicipios();
                                    $municipioActual = obtenerValorForm($prefix . 'Municipio', 'municipio_id', $datosSession, $cliente);
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
                                           value="<?php echo obtenerValorForm(($modoEdicion ? 'editar' : 'nueva') . 'Direccion', 'direccion', $datosSession, $cliente); ?>"
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
                                    <?php
                                    $valFecha = obtenerValorForm(($modoEdicion ? 'editar' : 'nueva') . 'FechaNacimiento', 'fecha_nacimiento', $datosSession, $cliente);
                                    $valFechaFormatted = ($valFecha && $valFecha != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($valFecha)) : '';
                                    ?>
                                    <input type="date" class="form-control"
                                           name="<?php echo $modoEdicion ? 'editarFechaNacimiento' : 'nuevaFechaNacimiento'; ?>"
                                           value="<?php echo $valFechaFormatted; ?>">
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
                                              placeholder="Información adicional sobre el cliente"><?php echo obtenerValorForm(($modoEdicion ? 'editar' : 'nueva') . 'Nota', 'notas', $datosSession, $cliente); ?></textarea>
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
                                        $campoFormResp = $modoEdicion ? 'editarResponsabilidades' : 'nuevasResponsabilidades';
                                        $respActual = obtenerValorForm($campoFormResp, 'responsabilidades_fiscales', $datosSession, $cliente, 'R-99-PN');
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
                                           value="<?php echo obtenerValorForm($prefix . 'CodigoPostal', 'codigo_postal', $datosSession, $cliente); ?>">
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
