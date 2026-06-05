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

	// 1. Verificar productos
	$stmt = $db->prepare("SELECT COUNT(*) FROM productos WHERE id_proveedor = :id");
	$stmt->bindParam(":id", $idProveedor, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
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

	echo json_encode(["status" => "success", "relaciones" => $relaciones]);
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