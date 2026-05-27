<?php

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once "../controladores/cajas.controlador.php";
require_once "../modelos/cajas.modelo.php";
require_once "../modelos/configuracion.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";

// VALIDAR SESIÓN
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    http_response_code(401);
    die(json_encode(["error" => "No autorizado. Sesion inactiva."]));
}

// CARGA DE DATATABLES HISTORIAL (Server-Side)
if (isset($_POST["draw"])) {
    $respuesta = ControladorCajas::ctrMostrarCierresCajaServerSide($_POST);
    echo json_encode($respuesta);
    exit;
}

// VALIDAR CSRF para escrituras
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["accion"]) && in_array($_POST["accion"], ["abrirCaja", "registrarMovimiento", "cerrarCaja"])) {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF invalido', 'success' => false]));
    }
}

class AjaxCajas
{
    /*=============================================
    VERIFICAR CAJA ABIERTA
    =============================================*/
    public function ajaxVerificarCaja()
    {
        $caja = ControladorCajas::ctrVerificarCajaAbierta();
        $controlCaja = 0;

        $configuracion = ModeloConfiguracion::mdlMostrarConfiguracion("configuracion", null, null);
        if ($configuracion && isset($configuracion["control_caja"])) {
            $controlCaja = intval($configuracion["control_caja"]);
        }

        if ($caja) {
            echo json_encode([
                "cajaAbierta" => true,
                "controlCaja" => $controlCaja,
                "datos" => $caja
            ]);
        } else {
            echo json_encode([
                "cajaAbierta" => false,
                "controlCaja" => $controlCaja
            ]);
        }
    }

    /*=============================================
    ABRIR CAJA
    =============================================*/
    public $montoApertura;
    public $observacionesApertura;
    public function ajaxAbrirCaja()
    {
        $respuesta = ControladorCajas::ctrAbrirCaja($this->montoApertura, $this->observacionesApertura);
        echo json_encode($respuesta);
    }

    /*=============================================
    REGISTRAR MOVIMIENTO MANUAL
    =============================================*/
    public $tipoMovimiento;
    public $montoMovimiento;
    public $motivoMovimiento;
    public function ajaxRegistrarMovimiento()
    {
        $respuesta = ControladorCajas::ctrRegistrarMovimiento($this->tipoMovimiento, $this->montoMovimiento, $this->motivoMovimiento);
        echo json_encode($respuesta);
    }

    /*=============================================
    CERRAR CAJA
    =============================================*/
    public $montoCierreReal;
    public $observacionesCierre;
    public function ajaxCerrarCaja()
    {
        $respuesta = ControladorCajas::ctrCerrarCaja($this->montoCierreReal, $this->observacionesCierre);
        echo json_encode($respuesta);
    }

    /*=============================================
    OBTENER DETALLES DE UN TURNO
    =============================================*/
    public $idCierre;
    public function ajaxObtenerDetalleTurno()
    {
        $respuesta = ControladorCajas::ctrObtenerDetalleTurno($this->idCierre);
        echo json_encode($respuesta);
    }
}

/*=============================================
EJECUCIÓN DE ACCIONES
=============================================*/

if (isset($_POST["accion"])) {
    
    $ajax = new AjaxCajas();

    if ($_POST["accion"] == "verificarCaja") {
        $ajax->ajaxVerificarCaja();
    }

    if ($_POST["accion"] == "abrirCaja") {
        $ajax->montoApertura = $_POST["montoApertura"];
        $ajax->observacionesApertura = isset($_POST["observacionesApertura"]) ? $_POST["observacionesApertura"] : "";
        $ajax->ajaxAbrirCaja();
    }

    if ($_POST["accion"] == "registrarMovimiento") {
        $ajax->tipoMovimiento = $_POST["tipoMovimiento"];
        $ajax->montoMovimiento = $_POST["montoMovimiento"];
        $ajax->motivoMovimiento = $_POST["motivoMovimiento"];
        $ajax->ajaxRegistrarMovimiento();
    }

    if ($_POST["accion"] == "cerrarCaja") {
        $ajax->montoCierreReal = $_POST["montoCierreReal"];
        $ajax->observacionesCierre = $_POST["observaciones"];
        $ajax->ajaxCerrarCaja();
    }

    if ($_POST["accion"] == "obtenerDetalle") {
        $ajax->idCierre = $_POST["idCierre"];
        $ajax->ajaxObtenerDetalleTurno();
    }
}
