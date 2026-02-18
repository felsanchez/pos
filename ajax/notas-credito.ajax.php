<?php

// Activar reporte de errores pero no mostrar en salida directa (para no romper JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'debug_php_errors.log');

session_start();

require_once "../controladores/factus.controlador.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/conexion.php";

class AjaxNotasCredito
{
    /*=============================================
    GENERAR NOTA CRÉDITO
    =============================================*/
    public function ajaxGenerarNotaCredito()
    {
        // Limpiar cualquier salida previa
        ob_clean();

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
            $tipo = $_POST["tipo"] ?? 'anulacion_total';
            $listaProductos = isset($_POST["listaProductos"]) ? json_decode($_POST["listaProductos"], true) : null;
            $idCliente = $_POST["idCliente"] ?? null;
            $motivoDescripcion = $_POST["motivoDescripcion"] ?? null;
            $metodoPago = $_POST["metodoPago"] ?? "Efectivo";
            $observacion = $_POST["observacion"] ?? "";

            // Capturar salida de controlador si la hay (echo, print_r) para evitar romper JSON
            ob_start();
            $respuesta = ControladorFactus::ctrGenerarNotaCredito($idVenta, $motivo, $tipo, $listaProductos, $idCliente, $motivoDescripcion, $metodoPago, $observacion);
            ob_get_clean(); // Descartar salida no deseada

            echo json_encode($respuesta);

        } catch (Exception $e) {
            echo json_encode([
                "error" => true,
                "mensaje" => "Error interno del servidor: " . $e->getMessage()
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
?>