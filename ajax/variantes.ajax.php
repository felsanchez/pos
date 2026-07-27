<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/variantes.controlador.php";
require_once "../modelos/variantes.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

/*=============================================
TABLA TIPOS VARIANTES SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
    require_once "../modelos/sanitizer.php";
    require_once "../modelos/helpers.php";
    $respuesta = ControladorVariantes::ctrMostrarTiposVariantesServerSide($_POST);
    echo json_encode($respuesta);
    exit;
}

class AjaxVariantes{

	/*=============================================
	CARGAR OPCIONES DE UN TIPO DE VARIANTE
	=============================================*/

	public $idTipoVariante;

	public function ajaxCargarOpciones(){

		$item = "id_tipo_variante";
		$valor = $this->idTipoVariante;

		$respuesta = ControladorVariantes::ctrMostrarOpcionesVariantes($item, $valor);

		echo json_encode($respuesta);

	}

	/*=============================================
	EDITAR TIPO DE VARIANTE
	=============================================*/

	public $idTipo;

	public function ajaxEditarTipo(){

		$item = "id";
		$valor = $this->idTipo;

		$respuesta = ControladorVariantes::ctrMostrarTiposVariantes($item, $valor);

		echo json_encode($respuesta);

	}


    /*=============================================
    EDITAR OPCIÓN DE VARIANTE
    =============================================*/

    public $idOpcionEditar;

    public function ajaxEditarOpcion(){

        $tabla = "opciones_variantes";
        $item = "id";
        $valor = $this->idOpcionEditar;

        $respuesta = ControladorVariantes::ctrMostrarOpcionesVariantes($item, $valor);

        echo json_encode($respuesta[0]);

    }

}

/*=============================================
CARGAR OPCIONES
=============================================*/

if(isset($_POST["idTipoVariante"])){

	$opciones = new AjaxVariantes();
	$opciones -> idTipoVariante = $_POST["idTipoVariante"];
	$opciones -> ajaxCargarOpciones();

}

/*=============================================
EDITAR TIPO
=============================================*/

if(isset($_POST["idTipo"]) && !isset($_POST["guardarEditarTipoVariante"])){

	$editarTipo = new AjaxVariantes();
	$editarTipo -> idTipo = $_POST["idTipo"];
	$editarTipo -> ajaxEditarTipo();
	exit;
}

/*=============================================
ACTIVAR/DESACTIVAR TIPO
=============================================*/

if(isset($_POST["activarTipo"])){

	$tabla = "tipos_variantes";

	$item1 = "estado";
	$valor1 = $_POST["estadoTipo"];

	$item2 = "id";
	$valor2 = $_POST["activarTipo"];

	$respuesta = ModeloVariantes::mdlActualizarTipoVariante($tabla, $item1, $valor1, $item2, $valor2);

	echo $respuesta;

}

/*=============================================
ACTIVAR/DESACTIVAR OPCIÓN
=============================================*/

if(isset($_POST["activarOpcion"])){

	$tabla = "opciones_variantes";

	$item1 = "estado";
	$valor1 = $_POST["estadoOpcion"];

	$item2 = "id";
	$valor2 = $_POST["activarOpcion"];

	$respuesta = ModeloVariantes::mdlActualizarOpcionVariante($tabla, $item1, $valor1, $item2, $valor2);

	echo $respuesta;

}


/*=============================================
EDITAR OPCIÓN
=============================================*/

if(isset($_POST["idOpcionEditar"])){

	$editarOpcion = new AjaxVariantes();
	$editarOpcion -> idOpcionEditar = $_POST["idOpcionEditar"];
	$editarOpcion -> ajaxEditarOpcion();

}


/*=============================================
OBTENER SIGUIENTE ORDEN DISPONIBLE PARA TIPO
=============================================*/

if(isset($_POST["obtenerSiguienteOrdenTipo"])){

	$stmt = Conexion::conectar()->prepare("SELECT MAX(orden) as max_orden FROM tipos_variantes");
	$stmt -> execute();
	$resultado = $stmt -> fetch();
	
	$siguienteOrden = ($resultado["max_orden"] != null) ? $resultado["max_orden"] + 1 : 1;
	
	echo json_encode($siguienteOrden);

}

/*=============================================
OBTENER SIGUIENTE ORDEN DISPONIBLE PARA OPCIÓN
=============================================*/

if(isset($_POST["obtenerSiguienteOrdenOpcion"])){

	$idTipo = $_POST["obtenerSiguienteOrdenOpcion"];
	
	$stmt = Conexion::conectar()->prepare("SELECT MAX(orden) as max_orden FROM opciones_variantes WHERE id_tipo_variante = :id_tipo");
	$stmt -> bindParam(":id_tipo", $idTipo, PDO::PARAM_INT);
	$stmt -> execute();
	$resultado = $stmt -> fetch();
	
	$siguienteOrden = ($resultado["max_orden"] != null) ? $resultado["max_orden"] + 1 : 1;
	
	echo json_encode($siguienteOrden);

}

/*=============================================
VALIDAR SI ORDEN YA EXISTE EN TIPOS
=============================================*/

if(isset($_POST["validarOrdenTipo"])){

	$orden = $_POST["validarOrdenTipo"];
	$idActual = $_POST["idTipoActual"];
	
	$stmt = Conexion::conectar()->prepare("SELECT id, nombre FROM tipos_variantes WHERE orden = :orden AND id != :id");
	$stmt -> bindParam(":orden", $orden, PDO::PARAM_INT);
	$stmt -> bindParam(":id", $idActual, PDO::PARAM_INT);
	$stmt -> execute();
	
	$resultado = $stmt -> fetch();
	
	if($resultado){
		echo json_encode(array("existe" => true, "nombre" => $resultado["nombre"], "id" => $resultado["id"]));
	} else {
		echo json_encode(array("existe" => false));
	}

}

/*=============================================
VALIDAR SI ORDEN YA EXISTE EN OPCIONES
=============================================*/

if(isset($_POST["validarOrdenOpcion"])){

	$orden = $_POST["validarOrdenOpcion"];
	$idActual = $_POST["idOpcionActual"];
	$idTipo = $_POST["idTipoVariante"];
	
	$stmt = Conexion::conectar()->prepare("SELECT id, nombre FROM opciones_variantes WHERE orden = :orden AND id != :id AND id_tipo_variante = :id_tipo");
	$stmt -> bindParam(":orden", $orden, PDO::PARAM_INT);
	$stmt -> bindParam(":id", $idActual, PDO::PARAM_INT);
	$stmt -> bindParam(":id_tipo", $idTipo, PDO::PARAM_INT);
	$stmt -> execute();

	$resultado = $stmt -> fetch(); 

	if($resultado){
		echo json_encode(array("existe" => true, "nombre" => $resultado["nombre"], "id" => $resultado["id"]));

	} else {
		echo json_encode(array("existe" => false));
	}

 }

/*=============================================
VERIFICAR USO DE TIPO DE VARIANTE ANTES DE ELIMINAR
=============================================*/
if (isset($_POST["idTipoVerificarUso"])) {
	require_once "../modelos/variantes.modelo.php";
	$idTipo = $_POST["idTipoVerificarUso"];
	$checkUso = ModeloVariantes::mdlVerificarUsoTipoVariante($idTipo);
	echo json_encode(["status" => "success", "tieneUso" => ($checkUso > 0)]);
	exit;
}

/*=============================================
VERIFICAR USO DE OPCION DE VARIANTE ANTES DE ELIMINAR
=============================================*/
if (isset($_POST["idOpcionVerificarUso"])) {
	require_once "../modelos/variantes.modelo.php";
	$idOpcion = $_POST["idOpcionVerificarUso"];
	$usoGlobal = ModeloVariantes::mdlContarUsoGlobalOpcion($idOpcion);
	
	if ($usoGlobal > 0) {
		$usoLocal = ModeloVariantes::mdlContarUsoLocalOpcion($idOpcion);
		if ($usoLocal == 0) {
			echo json_encode(["status" => "success", "tieneUso" => true, "tipo" => "otra_sucursal"]);
		} else {
			echo json_encode(["status" => "success", "tieneUso" => true, "tipo" => "local"]);
		}
	} else {
		echo json_encode(["status" => "success", "tieneUso" => false]);
	}
	exit;
}

/*=============================================
ELIMINAR TIPO DE VARIANTE
=============================================*/

if(isset($_POST["idEliminarTipo"])){ 

	require_once "../controladores/variantes.controlador.php";
	require_once "../modelos/variantes.modelo.php";

	$idTipo = $_POST["idEliminarTipo"];

	$respuesta = ControladorVariantes::ctrEliminarTipoVariante($idTipo); 

	echo json_encode($respuesta); 

}
 

/*=============================================
ELIMINAR OPCIÓN DE VARIANTE
=============================================*/

if(isset($_POST["idEliminarOpcion"])){ 

	require_once "../controladores/variantes.controlador.php";
	require_once "../modelos/variantes.modelo.php";

 	$idOpcion = $_POST["idEliminarOpcion"]; 

	$respuesta = ControladorVariantes::ctrEliminarOpcionVariante($idOpcion);

	echo json_encode($respuesta);
	exit;

}

/*=============================================
VALIDAR NO REPETIR TIPO DE VARIANTE
=============================================*/
if (isset($_POST["validarTipoVariante"])) {
	$tabla = "tipos_variantes";
	$item = "nombre";
	$valor = trim($_POST["validarTipoVariante"]);

	$respuesta = ModeloVariantes::mdlMostrarTiposVariantes($tabla, $item, $valor);

	echo json_encode($respuesta);
	exit;
}

/*=============================================
GUARDAR CREAR TIPO DE VARIANTE
=============================================*/
if (isset($_POST["guardarCrearTipoVariante"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevoTipoVariante"])) {
		$tabla = "tipos_variantes";
		$nombre = trim($_POST["nuevoTipoVariante"]);

		$tipoExistente = ModeloVariantes::mdlMostrarTiposVariantes($tabla, "nombre", $nombre);
		if ($tipoExistente) {
			echo json_encode(["status" => "error", "mensaje" => "¡El tipo de variante ya existe en la base de datos!"]);
			exit;
		}

		$datos = array(
			"nombre" => $nombre,
			"orden" => isset($_POST["nuevoOrdenTipo"]) ? $_POST["nuevoOrdenTipo"] : 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloVariantes::mdlIngresarTipoVariante($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al guardar el tipo de variante.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El tipo de variante ha sido guardado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del tipo es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR TIPO DE VARIANTE
=============================================*/
if (isset($_POST["guardarEditarTipoVariante"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarTipoVariante"])) {
		$tabla = "tipos_variantes";
		$datos = array(
			"id" => $_POST["idTipo"],
			"nombre" => $_POST["editarTipoVariante"],
			"orden" => $_POST["editarOrdenTipo"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloVariantes::mdlEditarTipoVariante($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar el tipo de variante.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡El tipo de variante ha sido editado correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre del tipo es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR CREAR OPCIÓN DE VARIANTE
=============================================*/
if (isset($_POST["guardarCrearOpcion"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["nuevaOpcion"])) {
		$tabla = "opciones_variantes";
		$datos = array(
			"id_tipo_variante" => $_POST["idTipoVarianteOpcion"],
			"nombre" => $_POST["nuevaOpcion"],
			"orden" => isset($_POST["nuevoOrdenOpcion"]) ? $_POST["nuevoOrdenOpcion"] : 1
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloVariantes::mdlIngresarOpcionVariante($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al guardar la opción de variante.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La opción de variante ha sido guardada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la opción es obligatorio."]);
	}
	exit;
}

/*=============================================
GUARDAR EDITAR OPCIÓN DE VARIANTE
=============================================*/
if (isset($_POST["guardarEditarOpcion"])) {
	if (!CSRF::validateToken()) {
		echo json_encode(["status" => "error", "mensaje" => "Token CSRF inválido. Recarga la página."]);
		exit;
	}

	if (!empty($_POST["editarOpcion"])) {
		$tabla = "opciones_variantes";
		$datos = array(
			"id" => $_POST["idOpcion"],
			"nombre" => $_POST["editarOpcion"],
			"orden" => $_POST["editarOrdenOpcion"]
		);

		$db = Conexion::conectar();
		try {
			$db->beginTransaction();
			$respuesta = ModeloVariantes::mdlEditarOpcionVariante($tabla, $datos);
			if ($respuesta != "ok") {
				throw new Exception("Error al actualizar la opción de variante.");
			}
			$db->commit();
			echo json_encode(["status" => "ok", "mensaje" => "¡La opción de variante ha sido editada correctamente!"]);
		} catch (Exception $e) {
			$db->rollBack();
			echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
		}
	} else {
		echo json_encode(["status" => "error", "mensaje" => "El nombre de la opción es obligatorio."]);
	}
	exit;
}