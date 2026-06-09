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

require_once "../controladores/movimientos.controlador.php";
require_once "../modelos/movimientos.modelo.php";

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
        // 1. Obtener el prefijo de la categoría
        $prefijo = null;
        $stmtCat = Conexion::conectar()->prepare("SELECT prefijo FROM categorias WHERE id = :id");
        $stmtCat->bindParam(":id", $this->idCategoria, PDO::PARAM_INT);
        $stmtCat->execute();
        $cat = $stmtCat->fetch();
        $stmtCat = null;

        if ($cat && !empty($cat["prefijo"])) {
            $prefijo = $cat["prefijo"];
        } else {
            $prefijo = $this->idCategoria;
        }

        // 2. Buscar el último código NUMÉRICO de esta categoría que empiece por este prefijo
        $prefixLike = $prefijo . '%';
        $stmt = Conexion::conectar()->prepare("SELECT codigo FROM productos
												WHERE id_categoria = :id_categoria
												AND codigo LIKE :prefix_like
												AND codigo REGEXP '^[0-9]+$'
												ORDER BY CAST(codigo AS UNSIGNED) DESC
												LIMIT 1");

        $stmt->bindParam(":id_categoria", $this->idCategoria, PDO::PARAM_INT);
        $stmt->bindParam(":prefix_like", $prefixLike, PDO::PARAM_STR);
        $stmt->execute();
        $respuesta = $stmt->fetch();
        $stmt = null;

        if (!$respuesta) {
            // Devolvemos el prefijo + "00" en formato array para que JS le sume 1 y quede como prefijo + "01"
            $codigoBase = $prefijo . "00";
            echo json_encode(array("codigo" => $codigoBase));
        } else {
            echo json_encode($respuesta);
        }
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

            $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

            // Obtener tributos para mapear impuestos de forma eficiente
            require_once "../modelos/factus.modelo.php";
            $tributos = ModeloFactus::mdlObtenerTributos();
            $tributosMap = array();
            if (is_array($tributos)) {
                foreach ($tributos as $t) {
                    $tributosMap[$t['id']] = $t;
                }
            }

            $idBodega = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
            $resultado = array();

            if (is_array($productos)) {
                foreach ($productos as $prod) {
                    // Obtener impuesto del producto
                    $impuestoPorcentaje = 0;
                    $impuestoNombre = "Exento";
                    if (isset($prod["tributo_id"]) && $prod["tributo_id"] != 0 && isset($tributosMap[$prod["tributo_id"]])) {
                        $t = $tributosMap[$prod["tributo_id"]];
                        $impuestoPorcentaje = isset($t["porcentaje_defecto"]) ? $t["porcentaje_defecto"] : (isset($t["porcentaje"]) ? $t["porcentaje"] : 0);
                        $impuestoNombre = $t["nombre"];
                    }

                    if (isset($prod["tiene_variantes"]) && $prod["tiene_variantes"] == 1) {
                        // Obtener variantes
                        $variantes = ModeloProductos::mdlObtenerVariantesProducto($prod["id"], $idBodega);
                        if (is_array($variantes) && count($variantes) > 0) {
                            // Agregar el producto base como opción deshabilitada
                            $resultado[] = array(
                                "id" => $prod["id"],
                                "descripcion" => $prod["descripcion"],
                                "tiene_variantes" => 1,
                                "es_variante" => 0,
                                "id_variante" => null,
                                "sku" => $prod["codigo"],
                                "stock" => 0,
                                "precio_venta" => 0,
                                "impuesto_porcentaje" => $impuestoPorcentaje,
                                "impuesto_nombre" => $impuestoNombre,
                                "deshabilitar" => 1
                            );

                            foreach ($variantes as $var) {
                                if ($var["estado"] != 1) continue;

                                // Obtener opciones
                                $opciones = ModeloProductos::mdlObtenerOpcionesVariante($var["id"]);
                                $nombreVariante = array();
                                if (is_array($opciones)) {
                                    foreach ($opciones as $opcion) {
                                        $nombreVariante[] = $opcion["nombre"];
                                    }
                                }
                                $nombreVarianteStr = implode(" - ", $nombreVariante);
                                $descripcionCompleta = $prod["descripcion"] . " - " . $nombreVarianteStr;

                                $precioFinal = $prod["precio_venta"] + $var["precio_adicional"];

                                $resultado[] = array(
                                    "id" => $prod["id"],
                                    "descripcion" => $descripcionCompleta,
                                    "tiene_variantes" => 1,
                                    "es_variante" => 1,
                                    "id_variante" => $var["id"],
                                    "sku" => $var["sku"],
                                    "stock" => $var["stock"],
                                    "precio_venta" => $precioFinal,
                                    "impuesto_porcentaje" => $impuestoPorcentaje,
                                    "impuesto_nombre" => $impuestoNombre
                                );
                            }
                        }
                    } else {
                        // Producto simple
                        $resultado[] = array(
                            "id" => $prod["id"],
                            "descripcion" => $prod["descripcion"],
                            "tiene_variantes" => 0,
                            "es_variante" => 0,
                            "id_variante" => null,
                            "sku" => $prod["codigo"],
                            "stock" => $prod["stock"],
                            "precio_venta" => $prod["precio_venta"],
                            "impuesto_porcentaje" => $impuestoPorcentaje,
                            "impuesto_nombre" => $impuestoNombre
                        );
                    }
                }
            }

            echo json_encode($resultado);
            exit;
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

            $idBodega = isset($_POST["idBodega"]) && !empty($_POST["idBodega"]) ? intval($_POST["idBodega"]) : null;

            // LOGGING DEBUG
            $logFile = fopen("debug_ajax_productos.txt", "a");
            fwrite($logFile, "Querying - Item: " . $item . ", Valor: " . $valor . ", idBodega: " . ($idBodega ?? 'null') . "\n");

            $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden, $idBodega);

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
    $idBodega = isset($_POST["idBodega"]) && !empty($_POST["idBodega"]) ? intval($_POST["idBodega"]) : (isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1);

    // Obtener variantes del producto con el stock de la bodega activa
    $variantes = ModeloProductos::mdlObtenerVariantesProducto($idProducto, $idBodega);

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

    $db = Conexion::conectar();
    try {
        $db->beginTransaction();

        $tabla = "productos_variantes";

        // 🔹 OBTENER DATOS DE LA VARIANTE Y PRODUCTO antes de editar
        require_once "../controladores/movimientos.controlador.php";
        require_once "../modelos/movimientos.modelo.php";

        $stmt = $db->prepare("SELECT pv.*, p.descripcion as producto_descripcion, p.id as id_producto
                                               FROM productos_variantes pv
                                               INNER JOIN productos p ON pv.id_producto = p.id
                                               WHERE pv.id = :id");

        $stmt->bindParam(":id", $_POST["editarVariante"], PDO::PARAM_INT);
        $stmt->execute();
        $varianteAnteriorGlobal = $stmt->fetch();
        $stmt = null;

        if (!$varianteAnteriorGlobal) {
            throw new Exception("No se encontró la variante solicitada.");
        }

        $idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;

        // Obtener stock actual de la variante en ESTA bodega
        $stmtBodega = $db->prepare("SELECT stock FROM productos_variantes_bodegas WHERE id_variante = :id_variante AND id_bodega = :id_bodega");
        $stmtBodega->bindParam(":id_variante", $_POST["editarVariante"], PDO::PARAM_INT);
        $stmtBodega->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
        $stmtBodega->execute();
        $resBodega = $stmtBodega->fetch();
        $stmtBodega = null;

        $stockBodegaAnterior = $resBodega ? $resBodega["stock"] : 0;
        $nuevoStockBodega = $_POST["editarStockVariante"]; // El stock ingresado es el stock de esta bodega

        // Primero actualizamos el precio adicional global (el stock global se actualizará a continuación sumando bodegas)
        $datos = array(
            "id" => $_POST["editarVariante"],
            "precio_adicional" => $_POST["editarPrecioAdicionalVariante"],
            "stock" => $varianteAnteriorGlobal["stock"]
        );

        $respuesta = ModeloProductos::mdlEditarVariante($tabla, $datos);
        if ($respuesta != "ok") {
            throw new Exception("Error al actualizar precio y stock base de la variante.");
        }

        // 📦 ACTUALIZAR STOCK EN LA BODEGA ACTIVA DIRECTAMENTE
        $resStockB = ModeloProductos::mdlActualizarStockVarianteBodega($_POST["editarVariante"], $idBodegaActiva, $nuevoStockBodega);
        if ($resStockB != "ok") {
            throw new Exception("Error al actualizar stock de la variante en la bodega activa.");
        }

        // 🔹 RECALCULAR STOCK TOTAL DE LA VARIANTE (Suma de todas las bodegas)
        $stmtTotalVar = $db->prepare("SELECT SUM(stock) as total FROM productos_variantes_bodegas WHERE id_variante = :id");
        $stmtTotalVar->bindParam(":id", $_POST["editarVariante"], PDO::PARAM_INT);
        $stmtTotalVar->execute();
        $resTotalVar = $stmtTotalVar->fetch();
        $stockTotalVariante = $resTotalVar["total"] ? $resTotalVar["total"] : 0;
        $stmtTotalVar = null;

        $resActPV = ModeloProductos::mdlActualizarProducto("productos_variantes", "stock", $stockTotalVariante, $_POST["editarVariante"]);
        if ($resActPV != "ok") {
            throw new Exception("Error al actualizar el stock global de la variante.");
        }

        // 🔹 RECALCULAR STOCK TOTAL DEL PRODUCTO BASE (Suma de variantes activas)
        $idProductoBase = $varianteAnteriorGlobal["id_producto"];
        $stmtTotalProd = $db->prepare("SELECT SUM(stock) as total FROM productos_variantes WHERE id_producto = :id AND estado = 1");
        $stmtTotalProd->bindParam(":id", $idProductoBase, PDO::PARAM_INT);
        $stmtTotalProd->execute();
        $resTotalProd = $stmtTotalProd->fetch();
        $stockTotalProducto = $resTotalProd["total"] ? $resTotalProd["total"] : 0;
        $stmtTotalProd = null;

        $resActP = ModeloProductos::mdlActualizarProducto("productos", "stock", $stockTotalProducto, $idProductoBase);
        if ($resActP != "ok") {
            throw new Exception("Error al actualizar el stock global del producto.");
        }
        
        // Sincronizar también el stock del producto base en la bodega activa
        $stmtBodegaProd = $db->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb 
                                                       INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id
                                                       WHERE pv.id_producto = :id AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
        $stmtBodegaProd->bindParam(":id", $idProductoBase, PDO::PARAM_INT);
        $stmtBodegaProd->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
        $stmtBodegaProd->execute();
        $resBodegaProd = $stmtBodegaProd->fetch();
        $stockBodegaProducto = $resBodegaProd["total"] ? $resBodegaProd["total"] : 0;
        $stmtBodegaProd = null;

        $resStockBProd = ModeloProductos::mdlActualizarStockBodega($idProductoBase, $idBodegaActiva, $stockBodegaProducto);
        if ($resStockBProd != "ok") {
            throw new Exception("Error al actualizar el stock del producto en la bodega activa.");
        }

        // 🟢 REGISTRAR MOVIMIENTO DE STOCK - EDICIÓN DE VARIANTE EN BODEGA
        if ($stockBodegaAnterior != $nuevoStockBodega) {

            // Obtener el nombre de la variante con sus opciones
            $stmtNombre = $db->prepare("SELECT GROUP_CONCAT(ov.nombre SEPARATOR ' - ') as nombre_variante
                                                         FROM productos_variantes_opciones pvo
                                                         INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
                                                         WHERE pvo.id_producto_variante = :id_variante
                                                         ORDER BY ov.id ASC");

            $stmtNombre->bindParam(":id_variante", $_POST["editarVariante"], PDO::PARAM_INT);
            $stmtNombre->execute();
            $nombreVariante = $stmtNombre->fetch();
            $stmtNombre = null;

            $nombreCompleto = $varianteAnteriorGlobal["producto_descripcion"] . " - " . (isset($nombreVariante["nombre_variante"]) ? $nombreVariante["nombre_variante"] : "");

            $diferencia = $nuevoStockBodega - $stockBodegaAnterior;

            $resMov = ControladorMovimientos::ctrRegistrarMovimiento(
                "variante",
                $varianteAnteriorGlobal["id_producto"],
                $_POST["editarVariante"],
                $nombreCompleto,
                "edicion_stock",
                $diferencia,
                $stockBodegaAnterior,
                $nuevoStockBodega,
                "Stock de variante editado manualmente",
                "",
                $idBodegaActiva
            );
            if ($resMov != "ok") {
                throw new Exception("Error al registrar el movimiento de stock.");
            }
        }

        $db->commit();
        echo json_encode("ok");

    } catch (Exception $e) {
        $db->rollBack();
        Logger::error("Error al editar variante ID " . $_POST["editarVariante"] . ": " . $e->getMessage());
        echo json_encode($e->getMessage());
    }

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

/*=============================================
ELIMINAR VARIANTE
=============================================*/
if (isset($_POST["idVarianteEliminar"])) {
    
    $idVariante = $_POST["idVarianteEliminar"];
    $idProducto = $_POST["idProductoVarianteEliminar"];
    $idBodegaActiva = isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;

    $db = Conexion::conectar();
    try {
        $db->beginTransaction();

        // 1. Obtener datos de la variante y producto
        $stmt = $db->prepare("SELECT pv.*, p.descripcion as producto_descripcion 
                               FROM productos_variantes pv 
                               INNER JOIN productos p ON pv.id_producto = p.id 
                               WHERE pv.id = :id");
        $stmt->bindParam(":id", $idVariante, PDO::PARAM_INT);
        $stmt->execute();
        $variante = $stmt->fetch();
        $stmt = null;

        if (!$variante) {
            throw new Exception("Variante no encontrada");
        }

        // Obtener stock actual de la variante en ESTA bodega
        $stmtBodega = $db->prepare("SELECT stock FROM productos_variantes_bodegas WHERE id_variante = :id_variante AND id_bodega = :id_bodega");
        $stmtBodega->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
        $stmtBodega->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
        $stmtBodega->execute();
        $resBodega = $stmtBodega->fetch();
        $stmtBodega = null;

        $stockBodegaAnterior = $resBodega ? $resBodega["stock"] : 0;

        // 2. Registrar movimiento de stock si tenía stock > 0
        if ($stockBodegaAnterior > 0) {
            // Obtener el nombre de la variante con sus opciones
            $stmtNombre = $db->prepare("SELECT GROUP_CONCAT(ov.nombre SEPARATOR ' - ') as nombre_variante
                                         FROM productos_variantes_opciones pvo
                                         INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
                                         WHERE pvo.id_producto_variante = :id_variante
                                         ORDER BY ov.id ASC");
            $stmtNombre->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
            $stmtNombre->execute();
            $nombreVariante = $stmtNombre->fetch();
            $stmtNombre = null;

            $nombreCompleto = $variante["producto_descripcion"] . " - " . (isset($nombreVariante["nombre_variante"]) ? $nombreVariante["nombre_variante"] : "");

            $resMov = ControladorMovimientos::ctrRegistrarMovimiento(
                "variante",
                $idProducto,
                $idVariante,
                $nombreCompleto,
                "eliminacion_variante", 
                -abs($stockBodegaAnterior),
                $stockBodegaAnterior,
                0,
                "Eliminación de variante",
                "",
                $idBodegaActiva
            );
            if ($resMov != "ok") {
                throw new Exception("Error al registrar el movimiento de stock.");
            }
        }

        // 3. Eliminar de la base de datos (se usa CASCADE o borrado explícito)
        $stmtDelOpciones = $db->prepare("DELETE FROM productos_variantes_opciones WHERE id_producto_variante = :id_variante");
        $stmtDelOpciones->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
        $stmtDelOpciones->execute();

        $stmtDelBodegas = $db->prepare("DELETE FROM productos_variantes_bodegas WHERE id_variante = :id_variante");
        $stmtDelBodegas->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
        $stmtDelBodegas->execute();

        $stmtDelVariante = $db->prepare("DELETE FROM productos_variantes WHERE id = :id_variante");
        $stmtDelVariante->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
        $stmtDelVariante->execute();

        // 3.5. Si ya no quedan variantes para este producto, marcar tiene_variantes = 0
        $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM productos_variantes WHERE id_producto = :id_producto");
        $stmtCount->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
        $stmtCount->execute();
        $resCount = $stmtCount->fetch();
        $variantesRestantes = $resCount ? intval($resCount["total"]) : 0;
        $stmtCount = null;

        if ($variantesRestantes == 0) {
            $stmtUpdateTieneVar = $db->prepare("UPDATE productos SET tiene_variantes = 0 WHERE id = :id_producto");
            $stmtUpdateTieneVar->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
            $stmtUpdateTieneVar->execute();
            $stmtUpdateTieneVar = null;
        }

        // 4. Recalcular stock global del producto base
        $stmtTotalProd = $db->prepare("SELECT SUM(stock) as total FROM productos_variantes WHERE id_producto = :id AND estado = 1");
        $stmtTotalProd->bindParam(":id", $idProducto, PDO::PARAM_INT);
        $stmtTotalProd->execute();
        $resTotalProd = $stmtTotalProd->fetch();
        $stockTotalProducto = $resTotalProd["total"] ? $resTotalProd["total"] : 0;
        $stmtTotalProd = null;

        $resActP = ModeloProductos::mdlActualizarProducto("productos", "stock", $stockTotalProducto, $idProducto);

        // 5. Recalcular stock del producto en la bodega activa
        $stmtBodegaProd = $db->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb 
                                                       INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id
                                                       WHERE pv.id_producto = :id AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
        $stmtBodegaProd->bindParam(":id", $idProducto, PDO::PARAM_INT);
        $stmtBodegaProd->bindParam(":id_bodega", $idBodegaActiva, PDO::PARAM_INT);
        $stmtBodegaProd->execute();
        $resBodegaProd = $stmtBodegaProd->fetch();
        $stockBodegaProducto = $resBodegaProd["total"] ? $resBodegaProd["total"] : 0;
        $stmtBodegaProd = null;

        $resStockBProd = ModeloProductos::mdlActualizarStockBodega($idProducto, $idBodegaActiva, $stockBodegaProducto);

        $db->commit();
        echo json_encode(array("status" => "ok", "variantesRestantes" => $variantesRestantes));

    } catch (Exception $e) {
        $db->rollBack();
        Logger::error("Error al eliminar variante ID " . $idVariante . ": " . $e->getMessage());
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
    
    exit;
}