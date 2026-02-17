<?php

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

        $respuesta = ControladorFactus::ctrGenerarNotaCredito($idVenta, $motivo, $tipo);

        echo json_encode($respuesta);
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