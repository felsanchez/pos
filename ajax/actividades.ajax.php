<?php

//console log
//console.log("¿Se cargó el script desde ajax1?");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../controladores/actividades.controlador.php";
require_once "../modelos/actividades.modelo.php";


class AjaxActividades{

	/*=============================================
	EDITAR ACTIVIDAD
	=============================================*/

	public $idActividad;

	public function ajaxEditarActividad(){

		//console log
		//console.log("¿Se cargó el script desde ajax2?");

		$item = "id";
		$valor = $this->idActividad;

		$respuesta = ControladorActividades::ctrMostrarActividades($item, $valor);

		echo json_encode($respuesta);

	}
		
	/*=============================================
	HPM VALIDAR NO REPETIR actividad
	=============================================

	public $validarActividad;
	public function ajaxValidarActividad(){

		$item = "descripcion";
		$valor = $this->validarActividad;

		$respuesta = ControladorActividades::ctrMostrarActividades($item, $valor);

		echo json_encode($respuesta);
	}
		*/
	
}

        /*=============================================
        EDITAR Actividad
        =============================================*/
        if(isset($_POST["idActividad"])){

            $Actividad = new AjaxActividades();
            $Actividad -> idActividad = $_POST["idActividad"];
            $Actividad -> ajaxEditarActividad();
			//return;
        }


		/*=============================================
        Guardar Tipo de Actividad
        =============================================*/
		if (isset($_POST["idActividad"]) && isset($_POST["nuevoTipo"])) {
			$datos = array(
				"id" => $_POST["idActividad"],
				"tipo" => $_POST["nuevoTipo"]
			);
		
			$respuesta = ControladorActividades::ctrActualizarTipoActividad($datos);
		
			header('Content-Type: application/json');
			echo json_encode($respuesta);
			exit;
		}
		
		
		/*=============================================
        Guardar Estado de Actividad
        =============================================*/
		/*if (isset($_POST["idActividad"]) && isset($_POST["nuevoEstado"])) {
			$datos = array(
				"id" => $_POST["idActividad"],
				"estado" => $_POST["nuevoEstado"]
			);
			$respuesta = ControladorActividades::ctrActualizarEstadoActividad($datos);
			header('Content-Type: application/json');
			echo json_encode($respuesta);
			exit;
		}
			*/		

		if (isset($_POST["idActividad"]) && isset($_POST["nuevoEstado"])) {
			$datos = array(
				"id" => $_POST["idActividad"],
				"estado" => $_POST["nuevoEstado"]
			);
		
			$respuesta = ControladorActividades::ctrActualizarEstadoActividad($datos);
		
			// Siempre devolvemos un objeto JSON estructurado
			if ($respuesta === "ok") {
				echo json_encode([
					"status" => "ok",
					"idActividad" => $datos["id"],
					"nuevoEstado" => $datos["estado"]
				]);
			} else {
				echo json_encode([
					"status" => "error",
					"message" => "Error al actualizar estado"
				]);
			}
		
			exit;
		}
		


		/*=============================================
		PERMITE EDITAR Observacion
		=============================================*/
		
		if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarObservacion") {
			$tabla = "actividades";
			$datos = array(
			"id" => $_POST["id"],
			"observacion" => $_POST["observacion"]
			);
			$respuesta = ModeloActividades::mdlActualizarObservacion("actividades", $_POST["id"], $_POST["observacion"]);
			echo json_encode($respuesta);
		}
			
  
  