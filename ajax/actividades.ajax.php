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
