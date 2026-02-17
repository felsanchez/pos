<?php 

require_once "../controladores/tipos-actividades.controlador.php";
require_once "../modelos/tipos-actividades.modelo.php"; 

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