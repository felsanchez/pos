<?php

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/estados-actividades.controlador.php";

require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

require_once "../controladores/estados-actividades.controlador.php";
require_once "../modelos/estados-actividades.modelo.php";

class AjaxEstadosActividades{

	/*=============================================
	EDITAR ESTADO DE ACTIVIDAD
	=============================================*/

	public $idEstado;

	public function ajaxEditarEstadoActividad(){

		try {
			$item = "id";
			$valor = $this->idEstado;

			$respuesta = ControladorEstadosActividades::ctrMostrarEstadosActividades($item, $valor);

			if($respuesta){
				echo json_encode($respuesta);
			} else {
				echo json_encode(array("error" => "No se encontró el estado"));
			}
		} catch (Exception $e) {
			echo json_encode(array("error" => $e->getMessage()));
		}
	}

}

/*=============================================
EDITAR ESTADO DE ACTIVIDAD
=============================================*/

if(isset($_POST["idEstado"])){

	$estado = new AjaxEstadosActividades();
	$estado -> idEstado = $_POST["idEstado"];
	$estado -> ajaxEditarEstadoActividad();
} else {
	echo json_encode(array("error" => "No se recibió el ID del estado"));
}
