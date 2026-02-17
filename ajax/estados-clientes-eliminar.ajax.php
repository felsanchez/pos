<?php

require_once "../controladores/estados-clientes.controlador.php";
require_once "../modelos/estados-clientes.modelo.php";

class AjaxEliminarEstado {

	public $idEstado;
	public $nombreEstado;

	public function ajaxEliminarEstado() {

		$tabla = "estados_clientes";

		// Verificar si el estado está en uso
		$enUso = ModeloEstadosClientes::mdlVerificarEstadoEnUso($this->nombreEstado);

		if ($enUso > 0) {
			echo json_encode(array(
				"status" => "error",
				"type" => "en_uso",
				"message" => "Este estado está siendo usado por " . $enUso . " cliente(s)."
			));
			return;
		}

		// Eliminar el estado
		$respuesta = ModeloEstadosClientes::mdlEliminarEstado($tabla, $this->idEstado);

		if ($respuesta == "ok") {
			echo json_encode(array(
				"status" => "success",
				"message" => "El estado ha sido eliminado correctamente"
			));
		} else {
			echo json_encode(array(
				"status" => "error",
				"type" => "general",
				"message" => "Error al eliminar el estado"
			));
		}
	}
}

if (isset($_POST["idEstado"])) {
	$eliminar = new AjaxEliminarEstado();
	$eliminar->idEstado = $_POST["idEstado"];
	$eliminar->nombreEstado = $_POST["nombreEstado"];
	$eliminar->ajaxEliminarEstado();
}
