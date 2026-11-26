<?php

require_once "../controladores/menu-digital.controlador.php";
require_once "../modelos/menu-digital.modelo.php";
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

header('Content-Type: application/json; charset=utf-8');

class AjaxMenuDigital
{
    /*=============================================
    CAMBIAR ESTADO PRODUCTO EN MENÚ DIGITAL
    =============================================*/
    public $idProductoEstado;
    public $nuevoEstadoMenu;

    public function ajaxCambiarEstado()
    {
        $respuesta = ControladorMenuDigital::ctrActualizarEstadoMenu($this->idProductoEstado, $this->nuevoEstadoMenu);
        echo json_encode(["status" => $respuesta]);
    }

    /*=============================================
    GUARDAR CARACTERÍSTICAS DEL PRODUCTO
    =============================================*/
    public $idProductoCaract;
    public $caracteristicasTexto;

    public function ajaxGuardarCaracteristicas()
    {
        $respuesta = ControladorMenuDigital::ctrActualizarCaracteristicas($this->idProductoCaract, $this->caracteristicasTexto);
        echo json_encode(["status" => $respuesta]);
    }

    /*=============================================
    PROCESAR PEDIDO DEL CLIENTE
    =============================================*/
    public $datosPedido;

    public function ajaxProcesarPedido()
    {
        $respuesta = ControladorMenuDigital::ctrProcesarPedido($this->datosPedido);
        echo json_encode($respuesta);
    }
}

// 1. CAMBIAR ESTADO
if (isset($_POST["accion"]) && $_POST["accion"] == "cambiar_estado") {
    $cambiar = new AjaxMenuDigital();
    $cambiar->idProductoEstado = intval($_POST["id_producto"]);
    $cambiar->nuevoEstadoMenu = intval($_POST["estado"]);
    $cambiar->ajaxCambiarEstado();
}

// 2. GUARDAR CARACTERÍSTICAS
else if (isset($_POST["accion"]) && $_POST["accion"] == "guardar_caracteristicas") {
    require_once "../modelos/helpers.php";
    if (!puedeAccion('menu_digital', 'crear')) {
        echo json_encode(["status" => "error", "mensaje" => "No tiene permisos para modificar características"]);
        exit;
    }
    $guardar = new AjaxMenuDigital();
    $guardar->idProductoCaract = intval($_POST["id_producto"]);
    $guardar->caracteristicasTexto = trim($_POST["caracteristicas"]);
    $guardar->ajaxGuardarCaracteristicas();
}

// 3. PROCESAR PEDIDO DESDE EL CARRITO WEB
else if (isset($_POST["accion"]) && $_POST["accion"] == "procesar_pedido") {
    $productosArr = [];
    if (isset($_POST["productos"])) {
        $productosArr = is_array($_POST["productos"]) ? $_POST["productos"] : json_decode($_POST["productos"], true);
    }

    $datos = [
        "nombre" => $_POST["nombre"] ?? "",
        "celular" => $_POST["celular"] ?? "",
        "direccion" => $_POST["direccion"] ?? "",
        "notas" => $_POST["notas"] ?? "",
        "mesa" => $_POST["mesa"] ?? "",
        "metodo_pago" => $_POST["metodo_pago"] ?? "efectivo",
        "productos" => $productosArr
    ];

    $pedido = new AjaxMenuDigital();
    $pedido->datosPedido = $datos;
    $pedido->ajaxProcesarPedido();
}

// 4. GUARDAR CONFIGURACIÓN BOLD
else if (isset($_POST["accion"]) && $_POST["accion"] == "guardar_configuracion_bold") {
    require_once "../modelos/helpers.php";
    if (!puedeAccion('menu_digital', 'crear')) {
        echo json_encode(["status" => "error", "mensaje" => "No tiene permisos para modificar la configuración de pagos."]);
        exit;
    }

    $datos = [
        "bold_activo" => isset($_POST["bold_activo"]) ? intval($_POST["bold_activo"]) : 0,
        "bold_api_key" => isset($_POST["bold_api_key"]) ? trim($_POST["bold_api_key"]) : "",
        "bold_secret_key" => isset($_POST["bold_secret_key"]) ? trim($_POST["bold_secret_key"]) : ""
    ];

    $respuesta = ControladorMenuDigital::ctrActualizarConfiguracionBold($datos);
    echo json_encode(["status" => $respuesta]);
}
