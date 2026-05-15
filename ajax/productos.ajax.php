<?php

// Iniciar sesión segura
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../modelos/conexion.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

require_once "../controladores/variantes.controlador.php";
require_once "../modelos/variantes.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

class AjaxProductos
{


    /*=============================================
    GENERAR CODIGO A PARTIR DE ID CATEGORIA
    =============================================*/
    public $idCategoria;

    public function ajaxCrearCodigoProducto()
    {

        // Buscar el último código NUMÉRICO de esta categoría

        $stmt = Conexion::conectar()->prepare("SELECT codigo FROM productos

												WHERE id_categoria = :id_categoria

												AND codigo REGEXP '^[0-9]+$'

												ORDER BY CAST(codigo AS UNSIGNED) DESC

												LIMIT 1");



        $stmt->bindParam(":id_categoria", $this->idCategoria, PDO::PARAM_INT);

        $stmt->execute();

        $respuesta = $stmt->fetch();

        $stmt = null;

        echo json_encode($respuesta);

    }


    /*=============================================
    EDITAR PRODUCTO
    =============================================*/

    public $idProducto;
    public $traerProductos;
    public $nombreProducto;

    public function ajaxEditarProducto()
    {

        if ($this->traerProductos == "ok") {

            $item = null;
            $valor = null;
            $orden = "id";

            $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

            // LOGGING DEBUG
            $logFile = fopen("debug_ajax_productos.txt", "a");
            fwrite($logFile, "Response ID: " . print_r($respuesta, true) . "\n");
            fclose($logFile);

            echo json_encode($respuesta);
        } else if ($this->nombreProducto != "") {

            $item = "descripcion";
            $valor = $this->nombreProducto;
            $orden = "id";

            $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

            // Obtener información del impuesto
            if ($respuesta && isset($respuesta["tributo_id"])) {
                if ($respuesta["tributo_id"] != "" && $respuesta["tributo_id"] != 0) {
                    require_once "../modelos/factus.modelo.php";
                    $tributo = ModeloFactus::mdlMostrarTributo($respuesta["tributo_id"]);
                    if ($tributo) {
                        // Usar porcentaje_defecto que es el nombre correcto de la columna
                        $respuesta["impuesto_porcentaje"] = isset($tributo["porcentaje_defecto"]) ? $tributo["porcentaje_defecto"] : (isset($tributo["porcentaje"]) ? $tributo["porcentaje"] : 0);
                        $respuesta["impuesto_nombre"] = $tributo["nombre"];
                    } else {
                        $respuesta["impuesto_porcentaje"] = 0;
                        $respuesta["impuesto_nombre"] = "Sin Impuesto";
                    }
                } else {
                    $respuesta["impuesto_porcentaje"] = 0;
                    $respuesta["impuesto_nombre"] = "Exento";
                }
            }

            echo json_encode($respuesta);
        } else {
            $item = "id";
            $valor = $this->idProducto;
            $orden = "id";

            // LOGGING DEBUG
            $logFile = fopen("debug_ajax_productos.txt", "a");
            fwrite($logFile, "Querying - Item: " . $item . ", Valor: " . $valor . "\n");

            $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

            // PATCH: Handle list response vs single row
            if (is_array($respuesta) && isset($respuesta[0]) && is_array($respuesta[0])) {
                fwrite($logFile, "Response was a LIST. Extracting first element.\n");
                $respuesta = $respuesta[0];
            }

            fwrite($logFile, "Response Type: " . gettype($respuesta) . "\n");
            if (is_array($respuesta) && isset($respuesta[0])) {
                fwrite($logFile, "Response is LIST (Error?). Count: " . count($respuesta) . "\n");
            } else {
                fwrite($logFile, "Response is SINGLE ROW.\n");
            }
            fclose($logFile);

            // Obtener información del impuesto
            if ($respuesta && isset($respuesta["tributo_id"])) {
                if ($respuesta["tributo_id"] != "" && $respuesta["tributo_id"] != 0) {
                    $logFile = fopen("debug_ajax_productos.txt", "a");
                    fwrite($logFile, "Fetching tax for ID: " . $respuesta["tributo_id"] . "\n");

                    try {
                        require_once __DIR__ . "/../modelos/factus.modelo.php";

                        if (class_exists("ModeloFactus")) {
                            $tributo = ModeloFactus::mdlMostrarTributo($respuesta["tributo_id"]);
                            fwrite($logFile, "Fetched Tributo: " . print_r($tributo, true) . "\n");
                        } else {
                            fwrite($logFile, "Class ModeloFactus NOT FOUND.\n");
                            $tributo = false;
                        }

                    } catch (Exception $e) {
                        fwrite($logFile, "Exception: " . $e->getMessage() . "\n");
                        $tributo = false;
                    }
                    fclose($logFile);
                    if ($tributo) {
                        $respuesta["impuesto_porcentaje"] = $tributo["porcentaje_defecto"];
                        $respuesta["impuesto_nombre"] = $tributo["nombre"];
                    } else {
                        $respuesta["impuesto_porcentaje"] = 0;
                        $respuesta["impuesto_nombre"] = "Sin Impuesto";
                    }
                } else {
                    $respuesta["impuesto_porcentaje"] = 0;
                    $respuesta["impuesto_nombre"] = "Exento";
                }
            }

            echo json_encode($respuesta);
        }
    }


    /*=============================================
    HPM VALIDAR NO REPETIR PRODUCTO
    =============================================*/

    public $validarDescripcion;
    public function ajaxValidarDescripcion()
    {

        $item = "descripcion";
        $valor = $this->validarDescripcion;
        $orden = "id";

        $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

        echo json_encode($respuesta);
    }

    /*=============================================
    VALIDAR NO REPETIR CODIGO DE PRODUCTO
    =============================================*/

    public $validarCodigo;
    public $idProductoActual;

    public function ajaxValidarCodigo()
    {

        $item = "codigo";
        $valor = $this->validarCodigo;
        $orden = "id";

        $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

        // Si existe el producto y no es el mismo que estamos editando
        if ($respuesta && (!$this->idProductoActual || $respuesta["id"] != $this->idProductoActual)) {
            echo json_encode($respuesta);

        } else {
            echo json_encode(false);
        }
    }




}

/*=============================================
ELIMINAR PRODUCTO
=============================================*/
if (isset($_POST["idProductoEliminar"])) {
    $eliminar = new ControladorProductos();
    $respuesta = $eliminar->ctrEliminarProducto();
    echo $respuesta;
    exit;
}

/*=============================================
GENERAR CODIGO A PARTIR DE ID CATEGORIA
=============================================*/

if (isset($_POST["idCategoria"])) {

    $codigoProducto = new AjaxProductos();
    $codigoProducto->idCategoria = $_POST["idCategoria"];
    $codigoProducto->ajaxCrearCodigoProducto();
}


/*=============================================
EDITAR PRODUCTO
=============================================*/

if (isset($_POST["idProducto"])) {

    $editarProducto = new AjaxProductos();
    $editarProducto->idProducto = $_POST["idProducto"];
    $editarProducto->ajaxEditarProducto();
    exit;
}


/*=============================================
VALIDAR CODIGO DE PRODUCTO
=============================================*/

if (isset($_POST["validarCodigo"])) {

    $validarCodigo = new AjaxProductos();
    $validarCodigo->validarCodigo = $_POST["validarCodigo"];
    $validarCodigo->idProductoActual = isset($_POST["idProductoActual"]) ? $_POST["idProductoActual"] : null;
    $validarCodigo->ajaxValidarCodigo();
}


/*=============================================
TRAER PRODUCTOS (dispositivos)
=============================================*/

if (isset($_POST["traerProductos"])) {

    $traerProductos = new AjaxProductos();
    $traerProductos->traerProductos = $_POST["traerProductos"];
    $traerProductos->ajaxEditarProducto();
}


/*=============================================
TRAER PRODUCTOS nombre(dispositivos)
=============================================*/

if (isset($_POST["nombreProducto"])) {

    $traerProductos = new AjaxProductos();
    $traerProductos->nombreProducto = $_POST["nombreProducto"];
    $traerProductos->ajaxEditarProducto();
}



/*=============================================
HPM VALIDAR NO REPETIR PRODUCTO
=============================================*/

if (isset($_POST["validarDescripcion"])) {

    $valProducto = new AjaxProductos();
    $valProducto->validarDescripcion = $_POST["validarDescripcion"];
    $valProducto->ajaxValidarDescripcion();
}



/*=============================================
ACTUALIZAR IMAGEN DE PRODUCTO
=============================================*/

if (isset($_FILES["nuevaImagenProducto"])) {
    // La lógica ahora está centralizada en el controlador
    ControladorProductos::ctrAjaxEditarImagen();
    exit;
}

/*=============================================
OBTENER TIPOS DE VARIANTES PARA PRODUCTOS
============================================*/

if (isset($_POST["obtenerTiposVariantes"])) {

    $item = null;
    $valor = null;
    $respuesta = ControladorVariantes::ctrMostrarTiposVariantes($item, $valor);

    echo json_encode($respuesta);

    exit;
}

/*============================================
OBTENER VARIANTES DE UN PRODUCTO
=============================================*/

if (isset($_POST["obtenerVariantesProducto"])) {

    $idProducto = $_POST["obtenerVariantesProducto"];

    // Obtener variantes del producto
    $variantes = ModeloProductos::mdlObtenerVariantesProducto($idProducto);

    // Obtener producto base para calcular precio final
    $productoBase = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id");

    // Obtener información del impuesto del producto base para heredar a las variantes
    $impuestoPorcentaje = 0;
    $impuestoNombre = "Exento";

    if ($productoBase && isset($productoBase["tributo_id"]) && $productoBase["tributo_id"] != 0) {
        require_once "../modelos/factus.modelo.php";
        $tributo = ModeloFactus::mdlMostrarTributo($productoBase["tributo_id"]);
        if ($tributo) {
            $impuestoPorcentaje = isset($tributo["porcentaje_defecto"]) ? $tributo["porcentaje_defecto"] : (isset($tributo["porcentaje"]) ? $tributo["porcentaje"] : 0);
            $impuestoNombre = $tributo["nombre"];
        }
    }

    $resultado = array();

    foreach ($variantes as $variante) {

        // Obtener opciones de esta variante
        $opciones = ModeloProductos::mdlObtenerOpcionesVariante($variante["id"]);

        // Construir nombre de la variante
        $nombreVariante = array();
        foreach ($opciones as $opcion) {
            $nombreVariante[] = $opcion["nombre"];
        }

        $nombreVarianteStr = implode(" - ", $nombreVariante);

        // Calcular precio final
        $precioFinal = $productoBase["precio_venta"] + $variante["precio_adicional"];

        $resultado[] = array(
            "id" => $variante["id"],
            "id_producto" => $idProducto,  // ID del producto base
            "sku" => $variante["sku"],
            "nombre" => $productoBase["descripcion"] . " - " . $nombreVarianteStr,
            "precio_adicional" => $variante["precio_adicional"],
            "precio_final" => $precioFinal,
            "stock" => $variante["stock"],
            "estado" => $variante["estado"],
            "imagen" => $variante["imagen"],
            "impuesto_porcentaje" => $impuestoPorcentaje,
            "impuesto_nombre" => $impuestoNombre
        );
    }

    echo json_encode($resultado);
    exit;
}

/*=============================================
ACTIVAR/DESACTIVAR VARIANTE
=============================================*/

if (isset($_POST["activarVariante"])) {

    $tabla = "productos_variantes";

    $datos = array(
        "id" => $_POST["activarVariante"],
        "estado" => $_POST["nuevoEstado"]
    );

    $respuesta = ModeloProductos::mdlActualizarEstadoVariante($tabla, $datos);

    echo json_encode($respuesta);

    exit;
}


/*=============================================
OBTENER OPCIONES DE UN TIPO DE VARIANTE
=============================================*/

if (isset($_POST["obtenerOpcionesVariante"])) {

    $idTipoVariante = $_POST["obtenerOpcionesVariante"];

    $item = "id_tipo_variante";

    $valor = $idTipoVariante;

    $respuesta = ControladorVariantes::ctrMostrarOpcionesVariantes($item, $valor);

    echo json_encode($respuesta);

    exit;
}


/*=============================================
EDITAR VARIANTE
=============================================*/

if (isset($_POST["editarVariante"])) {

    $tabla = "productos_variantes";

    // 🔹 OBTENER STOCK ANTERIOR antes de editar
    require_once "../controladores/movimientos.controlador.php";
    require_once "../modelos/movimientos.modelo.php";

    $stmt = Conexion::conectar()->prepare("SELECT pv.*, p.descripcion as producto_descripcion, p.id as id_producto
                                           FROM productos_variantes pv
                                           INNER JOIN productos p ON pv.id_producto = p.id
                                           WHERE pv.id = :id");

    $stmt->bindParam(":id", $_POST["editarVariante"], PDO::PARAM_INT);
    $stmt->execute();
    $varianteAnterior = $stmt->fetch();
    $stmt = null;

    $stockAnterior = $varianteAnterior["stock"];
    $nuevoStock = $_POST["editarStockVariante"];

    $datos = array(
        "id" => $_POST["editarVariante"],
        "precio_adicional" => $_POST["editarPrecioAdicionalVariante"],
        "stock" => $nuevoStock
    );

    $respuesta = ModeloProductos::mdlEditarVariante($tabla, $datos);

    if ($respuesta == "ok") {
        // 📦 ACTUALIZAR STOCK EN BODEGA ACTIVA
        $idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
        
        // Obtener stock actual de la variante en ESTA bodega
        $stmtBodega = Conexion::conectar()->prepare("SELECT stock FROM productos_variantes_bodegas WHERE id_variante = :id_variante AND id_bodega = :id_bodega");
        $stmtBodega->bindParam(":id_variante", $_POST["editarVariante"], PDO::PARAM_INT);
        $stmtBodega->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
        $stmtBodega->execute();
        $resBodega = $stmtBodega->fetch();
        $stmtBodega = null;

        $stockBodegaAnterior = $resBodega ? $resBodega["stock"] : 0;
        $diferenciaStock = $nuevoStock - $stockAnterior; // Diferencia global que queremos aplicar a esta bodega
        $nuevoStockBodega = $stockBodegaAnterior + $diferenciaStock;
        if($nuevoStockBodega < 0) $nuevoStockBodega = 0;

        ModeloProductos::mdlActualizarStockVarianteBodega($_POST["editarVariante"], $idBodegaActiva, $nuevoStockBodega);

        // 🔹 RECALCULAR STOCK TOTAL DE LA VARIANTE (Suma de todas las bodegas)
        $stmtTotalVar = Conexion::conectar()->prepare("SELECT SUM(stock) as total FROM productos_variantes_bodegas WHERE id_variante = :id");
        $stmtTotalVar->bindParam(":id", $_POST["editarVariante"], PDO::PARAM_INT);
        $stmtTotalVar->execute();
        $resTotalVar = $stmtTotalVar->fetch();
        $stockTotalVariante = $resTotalVar["total"] ? $resTotalVar["total"] : 0;
        $stmtTotalVar = null;

        ModeloProductos::mdlActualizarProducto("productos_variantes", "stock", $stockTotalVariante, $_POST["editarVariante"]);

        // 🔹 RECALCULAR STOCK TOTAL DEL PRODUCTO BASE
        $idProductoBase = $varianteAnterior["id_producto"];
        $stmtTotalProd = Conexion::conectar()->prepare("SELECT SUM(stock) as total FROM productos_variantes WHERE id_producto = :id AND estado = 1");
        $stmtTotalProd->bindParam(":id", $idProductoBase, PDO::PARAM_INT);
        $stmtTotalProd->execute();
        $resTotalProd = $stmtTotalProd->fetch();
        $stockTotalProducto = $resTotalProd["total"] ? $resTotalProd["total"] : 0;
        $stmtTotalProd = null;

        ModeloProductos::mdlActualizarProducto("productos", "stock", $stockTotalProducto, $idProductoBase);
        
        // Sincronizar también el stock base en la bodega activa
        $stmtBodegaProd = Conexion::conectar()->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb 
                                                       INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id
                                                       WHERE pv.id_producto = :id AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
        $stmtBodegaProd->bindParam(":id", $idProductoBase, PDO::PARAM_INT);
        $stmtBodegaProd->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
        $stmtBodegaProd->execute();
        $resBodegaProd = $stmtBodegaProd->fetch();
        $stockBodegaProducto = $resBodegaProd["total"] ? $resBodegaProd["total"] : 0;
        $stmtBodegaProd = null;

        ModeloProductos::mdlActualizarStockBodega($idProductoBase, $idBodegaActiva, $stockBodegaProducto);
    }

    // 🟢 REGISTRAR MOVIMIENTO DE STOCK - EDICIÓN DE VARIANTE
    if ($respuesta == "ok" && $stockAnterior != $nuevoStock) {


        // Obtener el nombre de la variante con sus opciones
        $stmtNombre = Conexion::conectar()->prepare("SELECT GROUP_CONCAT(ov.nombre SEPARATOR ' - ') as nombre_variante
                                                     FROM productos_variantes_opciones pvo
                                                     INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
                                                     WHERE pvo.id_producto_variante = :id_variante
                                                     ORDER BY ov.id ASC");

        $stmtNombre->bindParam(":id_variante", $_POST["editarVariante"], PDO::PARAM_INT);
        $stmtNombre->execute();
        $nombreVariante = $stmtNombre->fetch();
        $stmtNombre = null;

        $nombreCompleto = $varianteAnterior["producto_descripcion"] . " - " . $nombreVariante["nombre_variante"];

        $diferencia = $nuevoStock - $stockAnterior;

        ControladorMovimientos::ctrRegistrarMovimiento(
            "variante",
            $varianteAnterior["id_producto"],
            $_POST["editarVariante"],
            $nombreCompleto,
            "edicion_stock",
            $diferencia,
            $stockAnterior,
            $nuevoStock,

            "Stock de variante editado manualmente",
            ""
        );

    }

    echo json_encode($respuesta);

    exit;
}


/*=============================================
OBTENER VARIANTES EXISTENTES PARA EDITAR PRODUCTO
=============================================*/

if (isset($_POST["obtenerVariantesParaEditar"])) {

    $idProducto = $_POST["obtenerVariantesParaEditar"];

    // Obtener bodega activa
    $idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;

    // Obtener variantes del producto con stock de la bodega activa
    $stmt = Conexion::conectar()->prepare("SELECT pv.id, pv.precio_adicional, COALESCE(pvb.stock, 0) as stock, pv.sku 
                                         FROM productos_variantes pv 
                                         LEFT JOIN productos_variantes_bodegas pvb ON pv.id = pvb.id_variante AND pvb.id_bodega = :id_bodega
                                         WHERE pv.id_producto = :id_producto AND pv.estado = 1");
    $stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
    $stmt->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
    $stmt->execute();

    $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;

    $resultado = array();

    foreach ($variantes as $variante) {
        // Obtener las opciones de esta variante con su ID de TIPO
        $stmtOpciones = Conexion::conectar()->prepare("SELECT ov.id, ov.id_tipo_variante 
                                                     FROM productos_variantes_opciones pvo
                                                     INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
                                                     WHERE pvo.id_producto_variante = :id_variante 
                                                     ORDER BY ov.id ASC");
        $stmtOpciones->bindParam(":id_variante", $variante["id"], PDO::PARAM_INT);
        $stmtOpciones->execute();

        $opcionesData = $stmtOpciones->fetchAll(PDO::FETCH_ASSOC);
        $stmtOpciones = null;

        $idsOpciones = array();
        $tiposInvolucrados = array();

        foreach($opcionesData as $opt) {
            $idsOpciones[] = $opt["id"];
            if(!in_array($opt["id_tipo_variante"], $tiposInvolucrados)) {
                $tiposInvolucrados[] = $opt["id_tipo_variante"];
            }
        }

        // Asegurar orden numérico para que coincida con el JS
        sort($idsOpciones, SORT_NUMERIC);

        // Crear string de opciones separadas por _
        $opcionesStr = implode("_", $idsOpciones);

        $resultado[] = array(
            "id" => $variante["id"],
            "opciones" => $opcionesStr,
            "tipos" => $tiposInvolucrados, // Tipos que deben marcarse
            "precio_adicional" => $variante["precio_adicional"],
            "stock" => $variante["stock"],
            "sku" => $variante["sku"]
        );
    }

    echo json_encode($resultado);
    exit;
}