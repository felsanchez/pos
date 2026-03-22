<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/gastos.controlador.php";

require_once "../modelos/gastos.modelo.php";
require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

class AjaxActualizarNotaGasto{

	/*=============================================
	ACTUALIZAR NOTA DE GASTO
	=============================================*/

	public $idGasto;
	public $nota;

	public function ajaxActualizarNotaGasto(){

		$tabla = "gastos";

		$datos = array(
			"id" => $this->idGasto,
			"notas" => $this->nota
		);

		$respuesta = ModeloGastos::mdlActualizarNotaGasto($tabla, $datos);

		echo json_encode($respuesta);

	}

}

/*=============================================
ACTUALIZAR NOTA
=============================================*/
if(isset($_POST["idGasto"])){

	$actualizarNota = new AjaxActualizarNotaGasto();
	$actualizarNota -> idGasto = $_POST["idGasto"];
	$actualizarNota -> nota = $_POST["nota"];
	$actualizarNota -> ajaxActualizarNotaGasto();

}
