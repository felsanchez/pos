<?php
ob_start();
require_once "../modelos/session-manager.php";
SessionManager::startSecure();
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once "../controladores/seguimiento.controlador.php";
    require_once "../modelos/seguimiento.modelo.php";
    require_once "../modelos/csrf.php";
    require_once "../modelos/sanitizer.php";

    /*=============================================
    TABLA SEGUIMIENTO SERVER-SIDE (solo lectura, no requiere CSRF)
    =============================================*/
    if (isset($_POST["drawSeguimientos"])) {
        if (ob_get_length()) ob_clean();
        $respuesta = ControladorSeguimiento::ctrMostrarSeguimientosServerSide($_POST);
        echo json_encode($respuesta);
        exit;
    }

    // Validar CSRF para operaciones de escritura
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!CSRF::validateToken()) {
            http_response_code(403);
            die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
        }
    }

    /*=============================================
    ELIMINAR SEGUIMIENTOS MASIVO
    =============================================*/
    if (isset($_POST["idsEliminar"])) {
        if (ob_get_length()) ob_clean();
        $tabla     = "seguimiento_leads";
        $respuesta = ModeloSeguimiento::mdlEliminarSeguimientosMasivo($tabla, $_POST["idsEliminar"]);
        echo json_encode($respuesta);
        exit;
    }

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        "status"   => "error",
        "titulo"   => "Error de Sistema (AJAX)",
        "mensaje"  => $e->getMessage(),
        "detalles" => $e->getFile() . " L" . $e->getLine()
    ]);
    exit;
}
