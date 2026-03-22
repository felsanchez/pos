<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/estados-clientes.controlador.php";

require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
require_once "../modelos/estados-clientes.modelo.php"; 

class AjaxEstadosClientes{ 

	/*=============================================
	EDITAR ESTADO
	=============================================*/ 

	public $idEstado; 

	public function ajaxEditarEstado(){ 

		$item = "id";
		$valor = $this->idEstado; 

		$respuesta = ControladorEstadosClientes::ctrMostrarEstadosClientes($item, $valor); 

		echo json_encode($respuesta);
	}
} 

/*=============================================
EDITAR ESTADO
=============================================*/

if(isset($_POST["idEstado"])){ 

	$estado = new AjaxEstadosClientes();
	$estado -> idEstado = $_POST["idEstado"];
	$estado -> ajaxEditarEstado();
}

/*=============================================
ELIMINAR ESTADO
=============================================*/
if (isset($_POST["idEstadoEliminar"])) {
    $eliminar = new ControladorEstadosClientes();
    $respuesta = $eliminar->ctrEliminarEstado();
    echo $respuesta;
    exit;
}