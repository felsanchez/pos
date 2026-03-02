<?php

// Activar reporte de errores pero no mostrar en salida directa (para no romper JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

require_once "../controladores/factus.controlador.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/conexion.php";

// Registro de depuración inicial
file_put_contents("debug_nc_init.txt", "Petición recibida: " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);
file_put_contents("debug_nc_init.txt", "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

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

            // Capturar salida de controlador si la hay (echo, print_r) para evitar romper JSON
            ob_start();
            $respuesta = ControladorFactus::ctrGenerarNotaCredito($idVenta, $motivo, $listaProductos, $idCliente, $motivoDescripcion, $metodoPago, $observacion, $firmar);
            $debugOutput = ob_get_clean(); // Descartar salida no deseada

            if (!empty($debugOutput)) {
                file_put_contents("debug_nc_output_error.txt", $debugOutput);
            }

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

            file_put_contents("debug_nc_firmar_init.txt", "Firmando nota ID: $idNota - " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);

            ob_start();
            $respuesta = ControladorFactus::ctrFirmarNotaCredito($idNota);
            $debugOutput = ob_get_clean();

            if (!empty($debugOutput)) {
                file_put_contents("debug_nc_firmar_error.txt", date("Y-m-d H:i:s") . "\n" . $debugOutput . "\n\n", FILE_APPEND);
            }

            file_put_contents("debug_nc_firmar_init.txt", "Respuesta: " . json_encode($respuesta) . "\n", FILE_APPEND);

            echo json_encode($respuesta);

        } catch (Throwable $e) {
            $msg = get_class($e) . ": " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine();
            file_put_contents("debug_nc_firmar_error.txt", date("Y-m-d H:i:s") . " THROWABLE: " . $msg . "\n", FILE_APPEND);
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