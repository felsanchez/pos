<?php
// Iniciar buffer de salida para evitar problemas con headers
ob_start();

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

// 1. CARGAR DEPENDENCIAS NECESARIAS
require_once "../controladores/factus.controlador.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";

// 2. VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        if (ob_get_level()) ob_clean();
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

if (isset($_POST["draw"])) {
    try {
        if (ob_get_level()) ob_clean();
        
        $respuesta = ControladorFactus::ctrMostrarNotasCreditoServerSide($_POST);
        
        if (!is_array($respuesta)) {
            throw new Exception("La respuesta del controlador no es un array válido");
        }

        header('Content-Type: application/json');
        echo json_encode($respuesta);
    } catch (Throwable $e) {
        if (ob_get_level()) ob_clean();
        echo json_encode([
            "draw" => intval($_POST['draw'] ?? 0),
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
            "error" => $e->getMessage()
        ]);
    }
    exit;
}


class AjaxNotasCredito
{
    /*=============================================
    GENERAR NOTA CRÉDITO
    =============================================*/
    public function ajaxGenerarNotaCredito()
    {
        // Limpiar cualquier salida previa de forma segura
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            if (!isset($_POST["idVenta"]) || !isset($_POST["motivo"])) {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "Datos incompletos"
                ]);
                return;
            }

            $idVenta = $_POST["idVenta"];
            $motivo = $_POST["motivo"];
            $listaProductos = isset($_POST["listaProductos"]) ? json_decode($_POST["listaProductos"], true) : null;
            $idCliente = $_POST["idCliente"] ?? null;
            $motivoDescripcion = $_POST["motivoDescripcion"] ?? null;
            $metodoPago = $_POST["metodoPago"] ?? "Efectivo";
            $observacion = $_POST["observacion"] ?? "";

            // Por defecto crear como borrador
            $firmar = false;

            $respuesta = ControladorFactus::ctrGenerarNotaCredito($idVenta, $motivo, $listaProductos, $idCliente, $motivoDescripcion, $metodoPago, $observacion, $firmar);


            echo json_encode($respuesta);

        } catch (Exception $e) {
            echo json_encode([
                "error" => true,
                "mensaje" => "Error interno del servidor: " . $e->getMessage()
            ]);
        }
    }
    /*=============================================
    FIRMAR NOTA CRÉDITO BORRADOR
    =============================================*/
    public function ajaxFirmarNotaCredito()
    {
        // Limpiar cualquier salida previa de forma segura
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            if (!isset($_POST["idNota"])) {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "ID de nota requerido"
                ]);
                return;
            }

            $idNota = $_POST["idNota"];

            $respuesta = ControladorFactus::ctrFirmarNotaCredito($idNota);

            echo json_encode($respuesta);

        } catch (Throwable $e) {
            echo json_encode([
                "error" => true,
                "mensaje" => "Error del servidor: " . $e->getMessage()
            ]);
        }
    }
}

/*=============================================
GENERAR NOTA CRÉDITO
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "generarNotaCredito") {
    $generarNC = new AjaxNotasCredito();
    $generarNC->ajaxGenerarNotaCredito();
}

/*=============================================
FIRMAR NOTA CRÉDITO
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "firmarNotaCredito") {
    $firmarNC = new AjaxNotasCredito();
    $firmarNC->ajaxFirmarNotaCredito();
}
?>