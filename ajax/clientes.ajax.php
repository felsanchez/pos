<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

// Clase que contiene los métodos para manejar el AJAX
class AjaxClientes
{

  public $idCliente;
  public $validarCliente;
  public $validarDocumento;
  public $validarTelefono;

  /*=============================================
  EDITAR CLIENTE
  =============================================*/
  public function ajaxEditarCliente()
  {
    $item = "id";
    $valor = $this->idCliente;

    $respuesta = ControladorClientes::ctrMostrarClientes($item, $valor);

    echo json_encode($respuesta);
  }

  /*=============================================
  VALIDAR NO REPETIR CLIENTE
  =============================================*/
  public function ajaxValidarCliente()
  {
    $item = "nombre";
    $valor = $this->validarCliente;

    $respuesta = ControladorClientes::ctrMostrarClientes($item, $valor);
    echo json_encode($respuesta);
  }

  /*=============================================
  ACTUALIZAR ESTATUS DEL CLIENTE
  =============================================*/
  public function ajaxActualizarEstatus($nuevoEstatus)
  {
    $tabla = "clientes";
    $datos = array(
      "id" => $this->idCliente,
      "estatus" => $nuevoEstatus
    );

    $respuesta = ModeloClientes::mdlActualizarEstatusCliente($tabla, $datos);
    echo $respuesta;
  }

  /*=============================================
  VALIDAR DUPLICADOS (DOCUMENTO Y TELEFONO)
  =============================================*/
  public function ajaxValidarDuplicados()
  {
    $respuesta = false;
    $mensaje = "";

    // Validar Documento
    if ($this->validarDocumento != null) {
      $item = "documento";
      $valor = $this->validarDocumento;
      $cliente = ControladorClientes::ctrMostrarClientes($item, $valor);

      if ($cliente) {
        // Si estamos editando, verificar que no sea el mismo cliente
        if ($this->idCliente != null && $cliente["id"] == $this->idCliente) {
          // Es el mismo, no cuenta como duplicado
        } else {
          $respuesta = true;
          $mensaje = "El documento ingresado ya está registrado para el cliente: " . $cliente["nombre"];
        }
      }
    }

    // Validar Teléfono (solo si no se ha encontrado duplicado por documento)
    if (!$respuesta && $this->validarTelefono != null) {
      $item = "telefono";
      $valor = $this->validarTelefono;
      $cliente = ControladorClientes::ctrMostrarClientes($item, $valor);

      if ($cliente) {
        // Si estamos editando, verificar que no sea el mismo cliente
        if ($this->idCliente != null && $cliente["id"] == $this->idCliente) {
          // Es el mismo, no cuenta como duplicado
        } else {
          $respuesta = true;
          $mensaje = "El teléfono ingresado ya está registrado para el cliente: " . $cliente["nombre"];
        }
      }
    }

    echo json_encode(array("existe" => $respuesta, "mensaje" => $mensaje));
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

/*=============================================
PERMITE EDITAR NOTAS
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarNota") {
  $tabla = "clientes";
  $datos = array(
    "id" => $_POST["id"],
    "notas" => $_POST["notas"]
  );
  $respuesta = ModeloClientes::mdlActualizarNota("clientes", $_POST["id"], $_POST["notas"]);
  echo json_encode($respuesta);
}


/*=============================================
PERMITE Mostrar el modal de clients desde Ventas
=============================================*/
if (isset($_POST["idCliente"])) {
  $cliente = new AjaxClientes();
  $cliente->idCliente = $_POST["idCliente"];
  $cliente->ajaxEditarCliente();
  exit; // IMPORTANTE: salir después de enviar el JSON
}

/*=============================================
SOLICITUD PARA ELIMINAR CLIENTE
=============================================*/
if (isset($_POST["idClienteEliminar"])) {
  $eliminar = new ControladorClientes();
  $respuesta = $eliminar->ctrEliminarCliente();
  echo $respuesta;
  return;
}

/*=============================================
SOLICITUD PARA VALIDAR DUPLICADOS
=============================================*/
if (isset($_POST["validarDocumento"]) || isset($_POST["validarTelefono"])) {
  $valDuplicado = new AjaxClientes();
  $valDuplicado->validarDocumento = isset($_POST["validarDocumento"]) ? $_POST["validarDocumento"] : null;
  $valDuplicado->validarTelefono = isset($_POST["validarTelefono"]) ? $_POST["validarTelefono"] : null;
  $valDuplicado->idCliente = isset($_POST["idClienteValidacion"]) ? $_POST["idClienteValidacion"] : null;
  $valDuplicado->ajaxValidarDuplicados();
  return;
}
