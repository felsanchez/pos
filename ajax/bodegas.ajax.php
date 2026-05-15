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
