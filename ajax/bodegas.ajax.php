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

require_once "../modelos/csrf.php";

/*=============================================
OBTENER BODEGA PARA MODAL EDITAR
=============================================*/
if (isset($_POST["idBodega"]) && !isset($_POST["guardarEditarBodega"])) {
	$bodega = new AjaxBodegas();
	$bodega->idBodega = $_POST["idBodega"];
	$bodega->ajaxEditarBodega();
	exit;
}

/*=============================================
INGRESAR A SUCURSAL
=============================================*/
if (isset($_POST["ingresarId"])) {
	$ingresar = new AjaxBodegas();
	$ingresar->ingresarId = $_POST["ingresarId"];
	$ingresar->ajaxIngresarSucursal();
	exit;
}

/*=============================================
ACTIVAR BODEGA
=============================================*/
if (isset($_POST["activarId"])) {
	$activar = new AjaxBodegas();
	$activar->activarId = $_POST["activarId"];
	$activar->activarBodega = $_POST["activarBodega"];
	$activar->ajaxActivarBodega();
	exit;
}

/*=============================================
GUARDAR CREAR BODEGA
=============================================*/
if (isset($_POST["guardarCrearBodega"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevaBodega"])) {
		$tabla = "bodegas";
		$datos = array(
			"nombre" => $_POST["nuevaBodega"],
			"direccion" => $_POST["nuevaDireccionBodega"],
			"telefono" => $_POST["nuevoTelefonoBodega"],
			"estado" => 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloBodegas::mdlIngresarBodega($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al guardar la sucursal.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La sucursal ha sido guardada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la sucursal es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR BODEGA
=============================================*/
if (isset($_POST["guardarEditarBodega"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarBodega"])) {
		$tabla = "bodegas";
		$datos = array(
			"nombre" => $_POST["editarBodega"],
			"direccion" => $_POST["editarDireccionBodega"],
			"telefono" => $_POST["editarTelefonoBodega"],
			"id" => $_POST["idBodega"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloBodegas::mdlEditarBodega($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al editar la sucursal.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La sucursal ha sido editada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la sucursal es obligatorio."]);
	}
	exit;
}
