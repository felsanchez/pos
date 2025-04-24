<?php

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

// Clase que contiene los métodos para manejar el AJAX
class AjaxClientes {

  public $idCliente;
  public $validarCliente;

  /*=============================================
  EDITAR CLIENTE
  =============================================*/
  public function ajaxEditarCliente() {
    $item = "id";
    $valor = $this->idCliente;

    $respuesta = ControladorClientes::ctrMostrarClientes($item, $valor);
    echo json_encode($respuesta);
  }

  /*=============================================
  VALIDAR NO REPETIR CLIENTE
  =============================================*/
  public function ajaxValidarCliente() {
    $item = "nombre";
    $valor = $this->validarCliente;

    $respuesta = ControladorClientes::ctrMostrarClientes($item, $valor);
    echo json_encode($respuesta);
  }

  /*=============================================
  ACTUALIZAR ESTATUS DEL CLIENTE
  =============================================*/
  public function ajaxActualizarEstatus($nuevoEstatus) {
    $tabla = "clientes";
    $datos = array(
      "id" => $this->idCliente,
      "estatus" => $nuevoEstatus
    );

    $respuesta = ModeloClientes::mdlActualizarEstatusCliente($tabla, $datos);
    echo $respuesta;
  }
}

/*=============================================
SOLICITUD PARA EDITAR CLIENTE (usa "idClienteEditar")
=============================================*/
if (isset($_POST["idClienteEditar"])) {
  $editar = new AjaxClientes();
  $editar->idCliente = $_POST["idClienteEditar"];
  $editar->ajaxEditarCliente();
  return;
}

/*=============================================
SOLICITUD PARA VALIDAR CLIENTE REPETIDO
=============================================*/
if (isset($_POST["validarCliente"])) {
  $valCliente = new AjaxClientes();
  $valCliente->validarCliente = $_POST["validarCliente"];
  $valCliente->ajaxValidarCliente();
  return;
}

/*=============================================
SOLICITUD PARA ACTUALIZAR ESTATUS (usa "idCliente" y "nuevoEstatus")
=============================================*/
if (isset($_POST["idCliente"]) && isset($_POST["nuevoEstatus"])) {
	$estatus = new AjaxClientes();
	$estatus->idCliente = $_POST["idCliente"];
	$estatus->ajaxActualizarEstatus($_POST["nuevoEstatus"]);
	return;
  }

/*
if (isset($_POST["idCliente"]) && isset($_POST["nuevoEstatus"])) {
  $estatus = new AjaxClientes();
  $estatus->idCliente = $_POST["idCliente"];
  $estatus->ajaxActualizarEstatus($_POST["nuevoEstatus"]);
  return;
}
*/