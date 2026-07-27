<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
    echo '<script>window.location = "login";</script>';
    return;
}

$idProducto = (int)($_GET["id"] ?? 0);
$modoEdicion = $idProducto > 0;

if ($modoEdicion) {
    if (!puedeAccion('productos', 'editar') && !puedeVer('productos')) {
        echo '<script>window.location = "inicio";</script>';
        return;
    }
} else {
    if (!puedeAccion('productos', 'crear')) {
        echo '<script>window.location = "inicio";</script>';
        return;
    }
}


// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$tipoCodigoProducto = !empty($configuracion["tipo_codigo_producto"]) ? $configuracion["tipo_codigo_producto"] : "automatico";

// Función helper para obtener valor del producto (soporta snake_case, camelCase y $_POST)
function obtenerValorProducto($producto, $campo) {
    // 1. Intentar obtener de $_POST primero (para persistencia en caso de error)
    // Mapeo manual de campos comunes de POST a nombres de BD si es necesario
    $postMap = [
        'descripcion' => 'editarDescripcion',
        'codigo' => 'editarCodigo',
        'id_categoria' => 'editarCategoria',
        'precio_compra' => 'editarPrecioCompra',
        'precio_venta' => 'editarPrecioVenta',
        'id_proveedor' => 'editarProveedor',
        'stock' => 'editarStock'
    ];
    
    $postName = isset($postMap[$campo]) ? $postMap[$campo] : $campo;
    
    // Probar también con prefijos "nuevo"
    $postNameNuevo = str_replace('editar', 'nuevo', $postName);
    
    if (isset($_POST[$postName])) {
        return $_POST[$postName];
    }
    if (isset($_POST[$postNameNuevo])) {
        return $_POST[$postNameNuevo];
    }

    if (!$producto) return '';

    // Intentar snake_case
    if (isset($producto[$campo])) {
        return $producto[$campo];
    }

    // Convertir a camelCase y buscar
    $camelCase = str_replace('_', '', lcfirst(str_replace('_', ' ', $campo)));
    $camelCase = str_replace(' ', '', ucwords($camelCase));
    if (isset($producto[$camelCase])) {
        return $producto[$camelCase];
    }

    return '';
}

// Determinar si es modo edición
$modoEdicion = isset($_GET['id']) && !empty($_GET['id']);
$producto = null;

if ($modoEdicion) {
    $item = "id";
    $valor = $_GET['id'];
    $idBodega = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
    $producto = ControladorProductos::ctrMostrarProductos($item, $valor, null, $idBodega);

    // DEBUG: Ver qué campos tiene el producto
    // echo '<pre>'; print_r($producto); echo '</pre>'; die();

    if (!$producto) {
        echo '<script>window.location = "productos";</script>';
        return;
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <?php echo $modoEdicion ? 'Editar Producto' : 'Nuevo Producto'; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="productos">Productos</a></li>
            <li class="active"><?php echo $modoEdicion ? 'Editar' : 'Nuevo'; ?></li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <form role="form" method="post" enctype="multipart/form-data" id="formProducto">

        <?php CSRF::insertToken(); ?>
                    <input type="hidden" name="form_detalle_producto" value="1">
                    <input type="hidden" name="idProducto" value="<?php echo $modoEdicion ? $producto['id'] : ''; ?>">

                    <!-- ============================================= -->
                    <!-- SECCIÓN 1: INFORMACIÓN BÁSICA -->
                    <!-- ============================================= -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-info-circle"></i> Información Básica</h3>
                            <div class="box-tools pull-right">
                                <a href="productos" class="btn btn-box-tool" data-toggle="tooltip" title="Volver">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <!-- Código -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Código del Producto *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                                            <input type="text" class="form-control" id="codigo" name="<?php echo $modoEdicion ? 'editarCodigo' : 'nuevoCodigo'; ?>"
                                                placeholder="<?php echo $tipoCodigoProducto == 'manual' ? 'Ingrese el código' : 'Se generará automáticamente'; ?>"
                                                <?php echo $tipoCodigoProducto == 'automatico' || $modoEdicion ? 'readonly' : ''; ?>
                                                value="<?php echo $modoEdicion ? $producto['codigo'] : ''; ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Categoría -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Categoría *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-th"></i></span>
                                            <select class="form-control" id="categoria" name="<?php echo $modoEdicion ? 'editarCategoria' : 'nuevaCategoria'; ?>"
                                                required>
                                                <option value="">Seleccionar categoría</option>
                                                <?php
                                                $categorias = ControladorCategorias::ctrMostrarCategorias(null, null);
                                                foreach ($categorias as $cat) {
                                                    $selected = ($modoEdicion && $producto['id_categoria'] == $cat['id']) ? 'selected' : '';
                                                    echo '<option value="' . $cat["id"] . '" ' . $selected . '>' . $cat["categoria"] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Proveedor -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Proveedor</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-truck"></i></span>
                                            <select class="form-control" id="proveedor" name="<?php echo $modoEdicion ? 'editarProveedor' : 'nuevoProveedor'; ?>">
                                                <option value="0">Sin proveedor</option>
                                                <?php
                                                $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                                                if ($proveedores) {
                                                    foreach ($proveedores as $prov) {
                                                        $selected = ($modoEdicion && $producto['id_proveedor'] == $prov['id']) ? 'selected' : '';
                                                        echo '<option value="' . $prov["id"] . '" ' . $selected . '>' . $prov["nombre"] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Descripción -->
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Descripción del Producto *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                                            <input type="text" class="form-control" id="descripcion"
                                                name="<?php echo $modoEdicion ? 'editarDescripcion' : 'nuevaDescripcion'; ?>"
                                                placeholder="Nombre o descripción del producto"
                                                value="<?php echo $modoEdicion ? $producto['descripcion'] : ''; ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Imagen -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Imagen del Producto</label>
                                        <input type="file" class="form-control" id="imagen" name="<?php echo $modoEdicion ? 'editarImagen' : 'nuevaImagen'; ?>"
                                            accept="image/*">
                                        <p class="help-block">Peso máximo: 2MB</p>
                                        <img src="<?php echo ($modoEdicion && !empty($producto['imagen'])) ? $producto['imagen'] : 'vistas/img/productos/default/anonymous.png'; ?>" class="img-thumbnail"
                                            style="max-width: 100px; margin-top: 10px;">
                                        <input type="hidden" name="imagenActual" value="<?php echo ($modoEdicion && !empty($producto['imagen'])) ? $producto['imagen'] : 'vistas/img/productos/default/anonymous.png'; ?>">
                                    </div>
                                </div>
                            </div>

                            <?php if ($modoEdicion): ?>
                            <div class="row">
                                <!-- Fecha de Agregado -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha de Agregado</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control" value="<?php echo $producto['fecha']; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ============================================= -->
                    <!-- SECCIÓN 2: INVENTARIO Y PRECIOS -->
                    <!-- ============================================= -->
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-dollar"></i> Inventario y Precios</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <!-- Stock -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Stock Inicial *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-cubes"></i></span>
                                            <input type="number" class="form-control" id="stock" name="<?php echo $modoEdicion ? 'editarStock' : 'nuevoStock'; ?>"
                                                min="0" placeholder="0"
                                                value="<?php echo $modoEdicion ? $producto['stock'] : '0'; ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Precio de Compra -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Precio de Compra *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                    class="fa fa-arrow-down"></i></span>
                                            <input type="number" class="form-control" id="precioCompra"
                                                name="<?php echo $modoEdicion ? 'editarPrecioCompra' : 'nuevoPrecioCompra'; ?>" min="0" step="1" placeholder="0"
                                                value="<?php echo $modoEdicion ? $producto['precio_compra'] : ''; ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Precio de Venta -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Precio de Venta *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                    class="fa fa-arrow-up"></i></span>
                                            <input type="number" class="form-control" id="precioVenta"
                                                name="<?php echo $modoEdicion ? 'editarPrecioVenta' : 'nuevoPrecioVenta'; ?>" min="0" step="1" placeholder="0"
                                                value="<?php echo $modoEdicion ? $producto['precio_venta'] : ''; ?>"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Porcentaje de Ganancia -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" id="usarPorcentaje" checked> Usar %
                                        </label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="porcentaje" min="0"
                                                value="50" placeholder="50">
                                            <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================= -->
                    <!-- SECCIÓN 3: GESTIÓN DE VARIANTES -->
                    <!-- ============================================= -->
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-sitemap"></i> Gestión de Variantes</h3>
                        </div>
                        
                        <div class="box-body">
                            <!-- ============================================= -->
                            <!-- CHECKBOX: HABILITAR VARIANTES -->
                            <!-- ============================================= -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="checkbox-inline" style="font-size: 16px;">
                                    <input type="checkbox" id="checkTieneVariantes" name="tieneVariantes"
                                        <?php echo ($modoEdicion && $producto['tiene_variantes'] == 1) ? 'checked' : ''; ?>>
                                    <strong>Este producto tiene variantes</strong> (Ej: Colores, Tallas, etc.)
                                </label>
                                <?php if ($modoEdicion && $producto['tiene_variantes'] == 1): ?>
                                    <p class="help-block text-primary" style="margin-top: 10px;"><i class="fa fa-info-circle"></i> Este producto ya utiliza variantes. Puedes agregar más combinaciones si lo deseas.</p>
                                <?php elseif ($modoEdicion): ?>
                                    <p class="help-block text-warning" style="margin-top: 10px;"><i class="fa fa-warning"></i> Al activar variantes en un producto existente, el stock global se sustituirá por el stock de las nuevas combinaciones que generes.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="seccionVariantes" style="display: none;">
                            <div class="box-body" style="border-top: 1px solid #f4f4f4; padding-top: 20px;">
                                <p class="text-muted">Selecciona los tipos de variantes y sus opciones para este
                                    producto.</p>

                            <div id="contenedorVariantes">
                                <!-- 1. Selección de Tipos de Variante -->
                                <div class="form-group">
                                    <label>Tipos de Variante:</label>
                                    <div id="tiposVariantesContainer" style="margin-bottom: 15px;">
                                        <!-- Aquí se cargarán los checkboxes de tipos (Color, Talla, etc.) -->
                                        <p class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando
                                            tipos de variantes...</p>
                                    </div>
                                    <button type="button" class="btn btn-default btn-xs"
                                        onclick="window.open('variantes', '_blank')">
                                        <i class="fa fa-plus"></i> Gestionar Tipos/Opciones
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs"
                                        id="btnRecargarVariantes">
                                        <i class="fa fa-refresh"></i> Recargar
                                    </button>
                                </div>

                                <!-- 2. Selección de Opciones (se muestra al seleccionar un tipo) -->
                                <div id="opcionesVariantesContainer"
                                    style="display: none; margin-bottom: 20px;">
                                    <!-- Aquí se cargarán las opciones de cada tipo seleccionado -->
                                </div>

                                <!-- 3. Tabla de Combinaciones Generadas -->
                                <div id="combinacionesContainer" style="display: none;">
                                    <h4>Combinaciones Generadas</h4>
                                    <div class="table-responsive" id="listaCombinaciones">
                                        <!-- Aquí se generará la tabla de combinaciones -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- ============================================= -->
                    <!-- SECCIÓN 4: FACTURACIÓN ELECTRÓNICA -->
                    <!-- ============================================= -->
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file-text"></i> Facturación Electrónica</h3>
                        </div>
                        <div class="box-body">
                            <p class="help-block"><i class="fa fa-info-circle"></i> Estos datos son requeridos para generar facturas electrónicas válidas ante la DIAN.</p>
                            <div class="row">
                                <!-- Unidad de Medida -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unidad de Medida *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-balance-scale"></i></span>
                                            <select class="form-control" id="unidadMedida"
                                                    name="<?php echo $modoEdicion ? 'editarUnidadMedida' : 'nuevaUnidadMedida'; ?>"
                                                    required>
                                                <option value="">-- Seleccionar --</option>
                                                <?php
                                                require_once "modelos/factus.modelo.php";
                                                $unidades = ModeloFactus::mdlObtenerUnidadesMedida();
                                                $unidadActual = $modoEdicion && !empty($producto['unidad_medida_id']) ? $producto['unidad_medida_id'] : '94';
                                                foreach ($unidades as $unidad) {
                                                    $selected = ($unidadActual == $unidad['codigo']) ? 'selected' : '';
                                                    $displayCode = ($unidad['codigo_dian'] == '94') ? 'UND' : $unidad['codigo_dian'];
                                                    echo "<option value='{$unidad['codigo']}' $selected>{$unidad['nombre']} ({$displayCode})</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <p class="help-block">Unidad de medida según estándar DIAN (ej: Unidad, Kilo, Metro)</p>
                                    </div>
                                </div>

                                <!-- Tipo de Tributo -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tributo/Impuesto *</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                            <select class="form-control" id="tributo"
                                                    name="<?php echo $modoEdicion ? 'editarTributo' : 'nuevoTributo'; ?>"
                                                    required>
                                                <option value="">-- Seleccionar --</option>
                                                <?php
                                                $tributos = ModeloFactus::mdlObtenerTributos();
                                                $tributoActual = $modoEdicion && isset($producto['tributo_id']) ? $producto['tributo_id'] : '';
                                                foreach ($tributos as $tributo) {
                                                    $selected = ($tributoActual == $tributo['id']) ? 'selected' : '';
                                                    $pct = floatval($tributo['porcentaje_defecto']);
                                                    // Omitir porcentaje solo si el código es 'ZA' (IVA Excluido)
                                                    if ($tributo['codigo'] == 'ZA') {
                                                        $nombreMostrar = $tributo['nombre'];
                                                    } else {
                                                        $nombreMostrar = $tributo['nombre'] . " " . number_format($pct, 0) . "%";
                                                    }
                                                    echo "<option value='{$tributo['id']}' $selected>{$nombreMostrar}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <p class="help-block">Impuesto aplicable según DIAN (ej: IVA 19%, Excluido)</p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Footer con botones -->
                    <div class="box">
                        <div class="box-footer">
                            <a href="productos" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary pull-right">
                                <i class="fa fa-save"></i>
                                <?php echo $modoEdicion ? 'Actualizar Producto' : 'Guardar Producto'; ?>
                            </button>
                        </div>
                    </div>

                    <?php if ($modoEdicion): ?>
                        <input type="hidden" name="idProducto" value="<?php echo $producto['id']; ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    // Variable global con la configuración del tipo de código de producto
    var tipoCodigoProducto = "<?php echo $tipoCodigoProducto; ?>";
    console.log("Tipo de código configurado:", tipoCodigoProducto);

    // Si estamos en modo edición y tiene datos de facturación, mostrar la sección
    <?php if ($tieneFacturacion): ?>
    $(document).ready(function() {
        $('#seccionFacturacion').slideDown();
        $('#seccionFacturacion').removeClass('collapsed-box');
    });
    <?php endif; ?>
</script>
<script src="vistas/js/producto-detalle.js?v=<?php echo time(); ?>"></script>

<?php
// Procesar formulario
if (!$modoEdicion) {
    $crearProducto = new ControladorProductos();
    $crearProducto->ctrCrearProducto();
} else {
    $editarProducto = new ControladorProductos();
    $editarProducto->ctrEditarProducto();
}
?>