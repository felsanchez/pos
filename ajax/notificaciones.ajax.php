<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

require_once "../controladores/notificaciones.controlador.php";
require_once "../modelos/notificaciones.modelo.php";

class AjaxNotificaciones
{

    /*=============================================
    MARCAR COMO LEÍDA
    =============================================*/

    public $idNotificacion;

    public function ajaxMarcarComoLeida()
    {

        $idUsuario = $_SESSION["id"];
        file_put_contents("../pos_debug_ajax.txt", "AJAX MarcarComoLeida: UserID: " . $idUsuario . " - NotifID: " . $this->idNotificacion . "\n", FILE_APPEND);
        $respuesta = ModeloNotificaciones::mdlMarcarComoLeida($this->idNotificacion, $idUsuario);
        file_put_contents("../pos_debug_ajax.txt", "Response: " . $respuesta . "\n", FILE_APPEND);

        echo $respuesta;

    }

    /*=============================================
    ELIMINAR NOTIFICACIÓN
    =============================================*/

    public $idEliminarNotificacion;

    public function ajaxEliminarNotificacion()
    {

        $respuesta = ModeloNotificaciones::mdlEliminarNotificacion($this->idEliminarNotificacion);

        echo $respuesta;

    }

    /*=============================================
    ELIMINAR MÚLTIPLES NOTIFICACIONES
    =============================================*/

    public $idsEliminarNotificaciones;

    public function ajaxEliminarNotificaciones()
    {

        $respuesta = ModeloNotificaciones::mdlEliminarNotificaciones($this->idsEliminarNotificaciones);

        echo $respuesta;

    }

}

/*=============================================
MARCAR COMO LEÍDA
=============================================*/

if (isset($_POST["idNotificacion"])) {

    $marcarLeida = new AjaxNotificaciones();
    $marcarLeida->idNotificacion = $_POST["idNotificacion"];
    $marcarLeida->ajaxMarcarComoLeida();

}

/*=============================================
MARCAR TODAS COMO LEÍDAS
=============================================*/

if (isset($_POST["marcarTodasLeidas"])) {

    $idUsuario = $_SESSION["id"];
    $respuesta = ModeloNotificaciones::mdlMarcarTodasComoLeidas($idUsuario);

    echo $respuesta;

}

/*=============================================
ELIMINAR NOTIFICACIÓN
=============================================*/

if (isset($_POST["idEliminarNotificacion"])) {

    $eliminar = new AjaxNotificaciones();
    $eliminar->idEliminarNotificacion = $_POST["idEliminarNotificacion"];
    $eliminar->ajaxEliminarNotificacion();

}

/*=============================================
ELIMINAR MÚLTIPLES NOTIFICACIONES
=============================================*/

if (isset($_POST["idsEliminarNotificaciones"])) {

    $eliminar = new AjaxNotificaciones();
    $eliminar->idsEliminarNotificaciones = $_POST["idsEliminarNotificaciones"];
    $eliminar->ajaxEliminarNotificaciones();

}