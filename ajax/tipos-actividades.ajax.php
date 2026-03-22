<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/tipos-actividades.controlador.php";
require_once "../modelos/tipos-actividades.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
 

class AjaxTiposActividades{ 

	/*=============================================
	EDITAR TIPO
	=============================================*/ 

	public $idTipo; 

	public function ajaxEditarTipo(){ 

		$item = "id";
		$valor = $this->idTipo;
		$respuesta = ControladorTiposActividades::ctrMostrarTiposActividades($item, $valor);
		echo json_encode($respuesta);
	}
} 

/*=============================================
EDITAR TIPO
=============================================*/

if(isset($_POST["idTipo"])){ 

	$editar = new AjaxTiposActividades();
	$editar -> idTipo = $_POST["idTipo"];
	$editar -> ajaxEditarTipo();

}

/*=============================================
ELIMINAR TIPO
=============================================*/
if (isset($_POST["idTipoEliminar"])) {
    $eliminar = new ControladorTiposActividades();
    $respuesta = $eliminar->ctrEliminarTipo();
    echo $respuesta;
    exit;
}