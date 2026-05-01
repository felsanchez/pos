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

/*=============================================
TABLA DOCUMENTOS SOPORTE SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
    try {
        // Limpiar buffer para asegurar que solo salga JSON
        if (ob_get_level()) ob_clean();
        
        $respuesta = ControladorFactus::ctrMostrarDocumentosSoporteServerSide($_POST);
        
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
