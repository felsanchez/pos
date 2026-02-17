<?php 

require_once "../controladores/estados-clientes.controlador.php";
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