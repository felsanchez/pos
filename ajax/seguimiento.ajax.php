<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/seguimiento.controlador.php";

require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once "../modelos/seguimiento.modelo.php";

class AjaxSeguimiento
{

    /*=============================================
    ELIMINAR SEGUIMIENTOS MASIVO
    =============================================*/

    public $idsEliminar;

    public function ajaxEliminarSeguimientos()
    {

        $tabla = "seguimiento_leads";
        $ids = $this->idsEliminar;

        $respuesta = ModeloSeguimiento::mdlEliminarSeguimientosMasivo($tabla, $ids);

        echo $respuesta;

    }

}

/*=============================================
ELIMINAR SEGUIMIENTOS MASIVO
=============================================*/
if (isset($_POST["idsEliminar"])) {

    $eliminar = new AjaxSeguimiento();
    $eliminar->idsEliminar = $_POST["idsEliminar"];
    $eliminar->ajaxEliminarSeguimientos();

}
