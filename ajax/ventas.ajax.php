<?php
ob_start();
require_once __DIR__ . "/../modelos/session-manager.php";
SessionManager::startSecure();
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // 1. Cargar dependencias de forma segura
    require_once __DIR__ . "/../controladores/ventas.controlador.php";
    require_once __DIR__ . "/../modelos/ventas.modelo.php";
    require_once __DIR__ . "/../controladores/productos.controlador.php";
    require_once __DIR__ . "/../modelos/productos.modelo.php";
    require_once __DIR__ . "/../controladores/clientes.controlador.php";
    require_once __DIR__ . "/../modelos/clientes.modelo.php";
    require_once __DIR__ . "/../controladores/usuarios.controlador.php";
    require_once __DIR__ . "/../modelos/usuarios.modelo.php";
    require_once __DIR__ . "/../controladores/notificaciones.controlador.php";
    require_once __DIR__ . "/../modelos/notificaciones.modelo.php";
    require_once __DIR__ . "/../controladores/configuracion.controlador.php";
    require_once __DIR__ . "/../modelos/configuracion.modelo.php";
    require_once __DIR__ . "/../controladores/factus.controlador.php";
    require_once __DIR__ . "/../modelos/factus.modelo.php";
    require_once __DIR__ . "/../controladores/movimientos.controlador.php";
    require_once __DIR__ . "/../modelos/movimientos.modelo.php";
    require_once __DIR__ . "/../modelos/csrf.php";

    // 2. VALIDAR CSRF para todas las peticiones POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!CSRF::validateToken()) {
            http_response_code(403);
            die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
        }
    }

    class AjaxVentas
    {
        public $idVentaImagen;
        public $idVentaSeguimiento;
        public $columna;
        public $valor;
        public $idProducto;
        public $activarVenta;
        public $activarId;
        public $validarVenta;

        /*=============================================
        EDITAR IMAGEN DE VENTA
        =============================================*/
        public function ajaxEditarImagenVenta()
        {
            if (isset($_FILES["nuevaImagenVenta"]["tmp_name"]) && !empty($_FILES["nuevaImagenVenta"]["tmp_name"])) {
                list($ancho, $alto) = getimagesize($_FILES["nuevaImagenVenta"]["tmp_name"]);
                $nuevoAncho = 500; $nuevoAlto = 500;
                $directorio = "../vistas/img/ventas/" . $this->idVentaImagen;
                if (!file_exists($directorio)) mkdir($directorio, 0755, true);
                
                $aleatorio = mt_rand(100, 999);
                $extension = ($_FILES["nuevaImagenVenta"]["type"] == "image/png") ? ".png" : ".jpg";
                $ruta = "vistas/img/ventas/" . $this->idVentaImagen . "/" . $aleatorio . $extension;
                
                if ($_FILES["nuevaImagenVenta"]["type"] == "image/jpeg") {
                    $origen = imagecreatefromjpeg($_FILES["nuevaImagenVenta"]["tmp_name"]);
                    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                    imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                    imagejpeg($destino, "../" . $ruta);
                } else if ($_FILES["nuevaImagenVenta"]["type"] == "image/png") {
                    $origen = imagecreatefrompng($_FILES["nuevaImagenVenta"]["tmp_name"]);
                    $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                    imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                    imagepng($destino, "../" . $ruta);
                }
            } else { echo json_encode("error_imagen"); return; }

            $datos = array("id" => $this->idVentaImagen, "imagen" => $ruta);
            $respuesta = ControladorVentas::ctrEditarImagenVenta($datos);
            echo json_encode($respuesta);
        }

        /*=============================================
        ACTUALIZAR SEGUIMIENTO
        =============================================*/
        public function ajaxActualizarSeguimiento()
        {
            $datos = array("id" => $this->idVentaSeguimiento, "columna" => $this->columna, "valor" => $this->valor);
            $respuesta = ControladorVentas::ctrActualizarSeguimiento($datos);
            echo json_encode($respuesta);
        }

        /*=============================================
        TRAER VARIANTES DE PRODUCTO
        =============================================*/
        public function ajaxTraerVariantes()
        {
            $item = "id_producto";
            $valor = $this->idProducto;
            $respuesta = ControladorProductos::ctrMostrarVariantes($item, $valor);
            echo json_encode($respuesta);
        }

        /*=============================================
        ACTIVAR VENTA
        =============================================*/
        public function ajaxActivarVenta()
        {
            $tabla = "ventas"; $item1 = "estado"; $valor1 = $this->activarVenta;
            $item2 = "id"; $valor2 = $this->activarId;
            $respuesta = ModeloVentas::mdlActualizarVenta($tabla, $item1, $valor1, $valor2);
            echo json_encode($respuesta);
        }

        /*=============================================
        VALIDAR NO REPETIR VENTA
        =============================================*/
        public function ajaxValidarVenta()
        {
            $item = "codigo"; $valor = $this->validarVenta;
            $respuesta = ControladorVentas::ctrMostrarVentas($item, $valor);
            echo json_encode($respuesta);
        }
    }

    // --- ACCIONES AJAX ---

    /*=============================================
    TABLA ORDENES SERVER-SIDE
    =============================================*/
    if (isset($_POST["drawOrdenes"])) {
        if (ob_get_length()) ob_clean();
        require_once "../modelos/sanitizer.php";
        require_once "../modelos/helpers.php";
        $respuesta = ControladorVentas::ctrMostrarOrdenesServerSide($_POST);
        echo json_encode($respuesta);
        exit;
    }

    /*=============================================
    TABLA FACTURAS ELECTRÓNICAS SERVER-SIDE
    =============================================*/
    if (isset($_POST["drawFacturasElectronicas"])) {
        if (ob_get_length()) ob_clean();
        require_once "../modelos/sanitizer.php";
        require_once "../modelos/helpers.php";
        $respuesta = ControladorVentas::ctrMostrarFacturasElectronicasServerSide($_POST);
        echo json_encode($respuesta);
        exit;
    }

    if (isset($_POST["idProducto"])) {
        $traerVariantes = new AjaxVentas();
        $traerVariantes->idProducto = $_POST["idProducto"];
        $traerVariantes->ajaxTraerVariantes();
    }

    if (isset($_POST["nuevoVendedor"]) && !isset($_POST["editarVenta"])) {
        if (ob_get_length()) ob_clean();
        $crearVenta = new ControladorVentas();
        $crearVenta->ctrCrearVenta();
        exit;
    }

    // ✅ PRIORIDAD: editarVentaFactus debe procesarse ANTES que editarVenta
    // porque el formulario de edición de factura electrónica envía ambos campos.
    // ✅ PRIORIDAD: guardarVentaFactus debe procesarse ANTES que editarVenta
    // porque al convertir una orden a factura electrónica, se envían ambos.
    if (isset($_POST["guardarVentaFactus"])) {
        if (ob_get_length()) ob_clean();
        $crearFactura = new ControladorVentas();
        $crearFactura->ctrCrearVentaFactus();
        exit;
    }

    if (isset($_POST["editarVentaFactus"]) && isset($_POST["idVenta"])) {
        if (ob_get_length()) ob_clean();
        $crearVenta = new ControladorVentas();
        $crearVenta->ctrCrearVenta();
        exit;
    }

    if (isset($_POST["editarVenta"])) {
        if (ob_get_length()) ob_clean();
        $editarVenta = new ControladorVentas();
        $editarVenta->ctrEditarVenta();
        exit;
    }

    if (isset($_POST["idVentaImagen"])) {
        $editarImagen = new AjaxVentas();
        $editarImagen->idVentaImagen = $_POST["idVentaImagen"];
        $editarImagen->ajaxEditarImagenVenta();
    }

    if (isset($_POST["idVentaSeguimiento"])) {
        $seguimiento = new AjaxVentas();
        $seguimiento->idVentaSeguimiento = $_POST["idVentaSeguimiento"];
        $seguimiento->columna = $_POST["columna"];
        $seguimiento->valor = $_POST["valor"];
        $seguimiento->ajaxActualizarSeguimiento();
    }

    if (isset($_POST["activarVenta"])) {
        $activarVenta = new AjaxVentas();
        $activarVenta->activarVenta = $_POST["activarVenta"];
        $activarVenta->activarId = $_POST["activarId"];
        $activarVenta->ajaxActivarVenta();
    }

    if (isset($_POST["idVentaEliminar"])) {
        if (ob_get_length()) ob_clean();
        $eliminarVenta = new ControladorVentas();
        $eliminarVenta->ctrEliminarVenta();
        exit;
    }

    if (isset($_POST["validarVenta"])) {
        $validarVenta = new AjaxVentas();
        $validarVenta->validarVenta = $_POST["validarVenta"];
        $validarVenta->ajaxValidarVenta();
    }

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status" => "error",
        "titulo" => "Error de Sistema (AJAX)",
        "mensaje" => $e->getMessage(),
        "detalles" => $e->getFile() . " L" . $e->getLine()
    ]);
    exit;
}