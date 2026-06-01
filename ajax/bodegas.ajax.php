<?php

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/bodegas.controlador.php";
require_once "../modelos/bodegas.modelo.php";

class AjaxBodegas
{

	/*=============================================
	EDITAR BODEGA
	=============================================*/
	public $idBodega;

	public function ajaxEditarBodega()
	{
		$item = "id";
		$valor = $this->idBodega;
		$respuesta = ControladorBodegas::ctrMostrarBodegas($item, $valor);
		echo json_encode($respuesta);
	}

	/*=============================================
	INGRESAR A SUCURSAL
	=============================================*/
	public $ingresarId;

	public function ajaxIngresarSucursal()
	{
		if($this->ingresarId == "todas") {
			$_SESSION["id_bodega"] = "";
			$_SESSION["nombre_bodega"] = "Todas las sucursales";
			echo "ok";
			return;
		}

		$item = "id";
		$valor = $this->ingresarId;
		$sucursal = ControladorBodegas::ctrMostrarBodegas($item, $valor);

		if($sucursal){
			$_SESSION["id_bodega"] = $sucursal["id"];
			$_SESSION["nombre_bodega"] = $sucursal["nombre"];
			echo "ok";
		}else{
			echo "error";
		}
	}

	/*=============================================
	ACTIVAR BODEGA
	=============================================*/
	public $activarId;
	public $activarBodega;

	public function ajaxActivarBodega()
	{
		$tabla = "bodegas";
		$item1 = "estado";
		$valor1 = $this->activarBodega;
		$item2 = "id";
		$valor2 = $this->activarId;

		// Proteger la Bodega Principal (ID 1)
		if ($valor2 == 1 && $valor1 == 0) {
			echo "error_bodega_principal";
			return;
		}

		$respuesta = ModeloBodegas::mdlActualizarBodega($tabla, $item1, $valor1, $item2, $valor2);
		echo $respuesta;
	}
}

/*=============================================
EDITAR BODEGA
=============================================*/
if (isset($_POST["idBodega"])) {
	$bodega = new AjaxBodegas();
	$bodega->idBodega = $_POST["idBodega"];
	$bodega->ajaxEditarBodega();
}

/*=============================================
INGRESAR A SUCURSAL
=============================================*/
if (isset($_POST["ingresarId"])) {
	$ingresar = new AjaxBodegas();
	$ingresar->ingresarId = $_POST["ingresarId"];
	$ingresar->ajaxIngresarSucursal();
}

/*=============================================
ACTIVAR BODEGA
=============================================*/
if (isset($_POST["activarId"])) {
	$activar = new AjaxBodegas();
	$activar->activarId = $_POST["activarId"];
	$activar->activarBodega = $_POST["activarBodega"];
	$activar->ajaxActivarBodega();
}
