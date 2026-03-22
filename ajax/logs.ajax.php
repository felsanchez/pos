<?php

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../config.php";
require_once "../controladores/logs.controlador.php";

class AjaxLogs
{

    /**
     * Obtener logs
     */
    public function ajaxObtenerLogs()
    {
        $fecha = isset($_POST["fecha"]) ? $_POST["fecha"] : null;
        $nivel = isset($_POST["nivel"]) ? $_POST["nivel"] : null;
        $limite = isset($_POST["limite"]) ? intval($_POST["limite"]) : 100;

        $logs = ControladorLogs::ctrObtenerLogs($fecha, $nivel, $limite);

        echo json_encode($logs);
    }

    /**
     * Obtener estadísticas
     */
    public function ajaxObtenerEstadisticas()
    {
        $fecha = isset($_POST["fecha"]) ? $_POST["fecha"] : null;

        $stats = ControladorLogs::ctrObtenerEstadisticas($fecha);

        echo json_encode($stats);
    }

    /**
     * Obtener lista de archivos de log
     */
    public function ajaxObtenerArchivos()
    {
        $archivos = ControladorLogs::ctrMostrarLogs();

        echo json_encode($archivos);
    }

    /**
     * Limpiar logs antiguos
     */
    public function ajaxLimpiarLogs()
    {
        $dias = isset($_POST["dias"]) ? intval($_POST["dias"]) : 30;

        $deleted = ControladorLogs::ctrLimpiarLogsAntiguos($dias);

        echo json_encode([
            'success' => true,
            'deleted' => $deleted
        ]);
    }


    /**
     * Eliminar logs específicos
     */

    public function ajaxEliminarLogs()
    {
        $logsJson = isset($_POST["logs"]) ? $_POST["logs"] : '[]';
        $logs = json_decode($logsJson, true);

        if (!is_array($logs) || empty($logs)) {

            echo json_encode([
                'success' => false,
                'message' => 'No se especificaron logs para eliminar'
            ]);

            return;
        }

        $resultado = ControladorLogs::ctrEliminarLogs($logs);
        echo json_encode($resultado);
    }


}

// Procesar peticiones
if (isset($_POST["accion"])) {
    $ajax = new AjaxLogs();

    switch ($_POST["accion"]) {
        case 'obtener_logs':
            $ajax->ajaxObtenerLogs();
            break;

        case 'obtener_estadisticas':
            $ajax->ajaxObtenerEstadisticas();
            break;

        case 'obtener_archivos':
            $ajax->ajaxObtenerArchivos();
            break;

        case 'limpiar_logs':
            $ajax->ajaxLimpiarLogs();
            break;

        case 'eliminar_logs':
            $ajax->ajaxEliminarLogs();
            break;
    }
}