<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/proveedores.controlador.php";
require_once "../modelos/proveedores.modelo.php";
require_once "../modelos/productos.modelo.php";
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
TABLA PROVEEDORES SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
    require_once "../modelos/sanitizer.php";
    require_once "../modelos/helpers.php";
    $respuesta = ControladorProveedores::ctrMostrarProveedoresServerSide($_POST);
    echo json_encode($respuesta);
    exit;
}

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
VERIFICAR RELACIONES DEL PROVEEDOR ANTES DE ELIMINAR
=============================================*/
if (isset($_POST["idProveedorVerificarRelaciones"])) {
	$idProveedor = $_POST["idProveedorVerificarRelaciones"];
	
	$db = Conexion::conectar();
	$relaciones = [];

	// 1. Verificar productos activos (no eliminados)
	$stmt = $db->prepare("SELECT COUNT(*) FROM productos WHERE id_proveedor = :id AND eliminado = 0");
	$stmt->bindParam(":id", $idProveedor, PDO::PARAM_INT);
	$stmt->execute();
	$totalProductosActivos = $stmt->fetchColumn();
	$tieneProductosActivos = $totalProductosActivos > 0;
	if ($tieneProductosActivos) {
		$relaciones[] = "productos";
	}

	// 2. Verificar gastos
	$stmt = $db->prepare("SELECT COUNT(*) FROM gastos WHERE id_proveedor = :id");
	$stmt->bindParam(":id", $idProveedor, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "gastos";
	}

	// 3. Verificar documentos_soporte
	$stmt = $db->prepare("SELECT COUNT(*) FROM documentos_soporte WHERE id_proveedor = :id");
	$stmt->bindParam(":id", $idProveedor, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "documentos soporte";
	}

	// 4. Verificar notas_ajuste_ds
	$stmt = $db->prepare("SELECT COUNT(*) FROM notas_ajuste_ds WHERE id_proveedor = :id");
	$stmt->bindParam(":id", $idProveedor, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "notas de ajuste";
	}

	echo json_encode(["status" => "success", "relaciones" => $relaciones, "tieneProductosActivos" => $tieneProductosActivos]);
	exit;
}

/*=============================================
ELIMINAR PROVEEDOR
=============================================*/
if (isset($_POST["idProveedorEliminar"])) {
    $eliminar = new ControladorProveedores();
    $respuesta = $eliminar->ctrBorrarProveedor();
    echo $respuesta;
    exit;
}

/*=============================================
OBTENER PROVEEDOR PARA MODAL EDITAR
=============================================*/
if (isset($_POST["idProveedor"]) && !isset($_POST["guardarEditarProveedor"])) {
	$proveedor = new AjaxProveedores();
	$proveedor->idProveedor = $_POST["idProveedor"];
	$proveedor->ajaxEditarProveedor();
	exit;
}

/*=============================================
GUARDAR CREAR PROVEEDOR
=============================================*/
if (isset($_POST["guardarCrearProveedor"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoProveedor"])) {
		$tabla = "proveedores";
		$datos = array(
			"nombre" => $_POST["nuevoProveedor"],
			"documento" => $_POST["nuevoDocumento"],
			"tipo_documento_id" => $_POST["nuevoTipoDocumento"],
			"marca" => $_POST["nuevaMarca"],
			"celular" => $_POST["nuevoCelular"],
			"correo" => $_POST["nuevoCorreo"],
			"direccion" => $_POST["nuevaDireccion"],
			"municipio_id" => $_POST["nuevoMunicipio"],
			"organizacion_id" => $_POST["nuevaOrganizacion"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloProveedores::mdlIngresarProveedor($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al guardar el proveedor.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El proveedor ha sido guardado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del proveedor es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR PROVEEDOR
=============================================*/
if (isset($_POST["guardarEditarProveedor"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarProveedor"])) {
		$tabla = "proveedores";
		$datos = array(
			"id" => $_POST["idProveedor"],
			"nombre" => $_POST["editarProveedor"],
			"documento" => $_POST["editarDocumento"],
			"tipo_documento_id" => $_POST["editarTipoDocumento"],
			"marca" => $_POST["editarMarca"],
			"celular" => $_POST["editarCelular"],
			"correo" => $_POST["editarCorreo"],
			"direccion" => $_POST["editarDireccion"],
			"municipio_id" => $_POST["editarMunicipio"],
			"organizacion_id" => $_POST["editarOrganizacion"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloProveedores::mdlEditarProveedor($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el proveedor.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El proveedor ha sido editado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del proveedor es obligatorio."]);
	}
	exit;
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
	exit;
}