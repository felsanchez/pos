<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";
require_once "../modelos/actividades.modelo.php";
require_once "../modelos/ventas.modelo.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

/*=============================================
TABLA CLIENTES SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
    require_once "../modelos/sanitizer.php";
    require_once "../modelos/helpers.php";
    $respuesta = ControladorClientes::ctrMostrarClientesServerSide($_POST);
    echo json_encode($respuesta);
    exit;
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
PERMITE Mostrar el modal de clientes desde Ventas
=============================================*/
if (isset($_POST["idCliente"]) && !isset($_POST["nuevoEstatus"]) && !isset($_POST["guardarEditarCliente"])) {
  $cliente = new AjaxClientes();
  $cliente->idCliente = $_POST["idCliente"];
  $cliente->ajaxEditarCliente();
  exit; // IMPORTANTE: salir después de enviar el JSON
}

/*=============================================
GUARDAR CREAR CLIENTE
=============================================*/
if (isset($_POST["guardarCrearCliente"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoCliente"]) && !empty($_POST["nuevoDocumentoId"])) {
		$tabla = "clientes";
		$datos = array(
			"nombre" => $_POST["nuevoCliente"],
			"documento" => $_POST["nuevoDocumentoId"],
			"email" => $_POST["nuevoEmail"],
			"telefono" => $_POST["nuevoTelefono"],
			"departamento" => $_POST["nuevoDepartamento"],
			"ciudad" => $_POST["nuevoCiudad"],
			"direccion" => $_POST["nuevaDireccion"],
			"fecha_nacimiento" => isset($_POST["nuevaFechaNacimiento"]) ? $_POST["nuevaFechaNacimiento"] : "",
			"estatus" => isset($_POST["nuevoEstatus"]) ? $_POST["nuevoEstatus"] : "nuevo",
			"tipo_documento" => isset($_POST["nuevoTipoDocumento"]) ? $_POST["nuevoTipoDocumento"] : "3",
			"municipio_id" => isset($_POST["nuevoMunicipio"]) ? $_POST["nuevoMunicipio"] : "11001",
			"notas" => isset($_POST["nuevaNota"]) ? $_POST["nuevaNota"] : ""
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al guardar el cliente.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El cliente ha sido guardado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre y documento son obligatorios."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR CLIENTE
=============================================*/
if (isset($_POST["guardarEditarCliente"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarCliente"]) && !empty($_POST["editarDocumentoId"])) {
		$tabla = "clientes";
		$datos = array(
			"id" => $_POST["idCliente"],
			"nombre" => $_POST["editarCliente"],
			"documento" => $_POST["editarDocumentoId"],
			"email" => $_POST["editarEmail"],
			"telefono" => $_POST["editarTelefono"],
			"departamento" => $_POST["editarDepartamento"],
			"ciudad" => $_POST["editarCiudad"],
			"direccion" => $_POST["editarDireccion"],
			"fecha_nacimiento" => isset($_POST["editarFechaNacimiento"]) ? $_POST["editarFechaNacimiento"] : "",
			"tipo_documento" => isset($_POST["editarTipoDocumento"]) ? $_POST["editarTipoDocumento"] : "3",
			"municipio_id" => isset($_POST["editarMunicipio"]) ? $_POST["editarMunicipio"] : "11001"
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el cliente.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El cliente ha sido editado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre y documento son obligatorios."]);
	}
	exit;
}

/*=============================================
VERIFICAR RELACIONES DEL CLIENTE ANTES DE ELIMINAR
=============================================*/
if (isset($_POST["idClienteVerificarRelaciones"])) {
  $idCliente = $_POST["idClienteVerificarRelaciones"];
  
  $db = Conexion::conectar();
  $relaciones = [];

  // 1. Verificar actividades
  $stmt = $db->prepare("SELECT COUNT(*) FROM actividades WHERE id_cliente = :id");
  $stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);
  $stmt->execute();
  if ($stmt->fetchColumn() > 0) {
    $relaciones[] = "actividades";
  }

  // 2. Verificar ventas
  $stmt = $db->prepare("SELECT COUNT(*) FROM ventas WHERE id_cliente = :id");
  $stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);
  $stmt->execute();
  if ($stmt->fetchColumn() > 0) {
    $relaciones[] = "ventas";
  }

  // 3. Verificar notas_credito
  $stmt = $db->prepare("SELECT COUNT(*) FROM notas_credito WHERE id_cliente = :id");
  $stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);
  $stmt->execute();
  if ($stmt->fetchColumn() > 0) {
    $relaciones[] = "notas crédito";
  }

  echo json_encode(["status" => "success", "relaciones" => $relaciones]);
  exit;
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
