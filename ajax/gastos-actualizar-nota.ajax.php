<?php

require_once "../controladores/gastos.controlador.php";
require_once "../modelos/gastos.modelo.php";

class AjaxActualizarNotaGasto{

	/*=============================================
	ACTUALIZAR NOTA DE GASTO
	=============================================*/

	public $idGasto;
	public $nota;

	public function ajaxActualizarNotaGasto(){

		$tabla = "gastos";

		$datos = array(
			"id" => $this->idGasto,
			"notas" => $this->nota
		);

		$respuesta = ModeloGastos::mdlActualizarNotaGasto($tabla, $datos);

		echo json_encode($respuesta);

	}

}

/*=============================================
ACTUALIZAR NOTA
=============================================*/
if(isset($_POST["idGasto"])){

	$actualizarNota = new AjaxActualizarNotaGasto();
	$actualizarNota -> idGasto = $_POST["idGasto"];
	$actualizarNota -> nota = $_POST["nota"];
	$actualizarNota -> ajaxActualizarNotaGasto();

}
