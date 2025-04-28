<?php 

require_once "../controladores/actividades.controlador.php";
require_once "../modelos/actividades.modelo.php";

class AjaxActividades{

	/*=============================================
	EDITAR ACTIVIDAD
	=============================================*/

	//public $idActividad;

	public function ajaxEditarActividad(){

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
        HPM VALIDAR NO REPETIR Actividad
        =============================================

        if(isset($_POST["validarActividad"])){

            $valActividad = new AjaxActividades();
            $valActividad -> validarActividad = $_POST["validarActividad"];
            $valActividad -> ajaxValidarActividad();
        }
			*/