<?php 

require_once "../controladores/proveedores.controlador.php";
require_once "../modelos/proveedores.modelo.php";

class AjaxProveedores{

	/*=============================================
	EDITAR PROVEEDORES
	=============================================*/

	public $idProveedor;

	public function ajaxEditarProveedor(){

		$item = "id";
		$valor = $this->idProveedor;

		$respuesta = ControladorProveedores::ctrMostrarProveedores($item, $valor);

		echo json_encode($respuesta);
	}
	
}

/*=============================================
EDITAR PROVEEDORES
=============================================*/

if(isset($_POST["idProveedor"])){

	$proveedor = new AjaxProveedores();
	$proveedor -> idProveedor = $_POST["idProveedor"];
	$proveedor -> ajaxEditarProveedor();
}


/*=============================================
ACTUALIZAR NOTAS DEL PROVEEDOR
=============================================*/ 

if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarNotas") {
	$tabla = "proveedores";
	$datos = array(
		"id" => $_POST["id"],
		"notas" => $_POST["notas"]
	);

	$respuesta = ModeloProveedores::mdlActualizarNotas("proveedores", $_POST["id"], $_POST["notas"]);
	echo json_encode($respuesta);
}