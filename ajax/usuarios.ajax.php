<?php

// Iniciar sesión con SessionManager (el sistema usa sesiones personalizadas)
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";
require_once "../modelos/actividades.modelo.php";
require_once "../modelos/ventas.modelo.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/perfiles.modelo.php";
require_once "../modelos/helpers.php";
require_once "../modelos/sanitizer.php";
require_once "../modelos/csrf.php";

// NOTA TEMPORAL: Validación CSRF deshabilitada para peticiones AJAX con FormData
// TODO: Implementar solución para agregar token CSRF a FormData en JavaScript
// Las peticiones de formularios HTML siguen protegidas por CSRF en los controladores

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Validar token CSRF
	if (!CSRF::validateToken()) {
		// Log para debugging
		error_log("CSRF validation failed in usuarios.ajax.php. Token in session: " . ($_SESSION['csrf_token'] ?? 'none'));
		error_log("Token received: " . ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'none'));

		http_response_code(403);
		die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
	}
}

class AjaxUsuarios
{

	/*=============================================
	EDITAR USUARIO
	=============================================*/

	public $idUsuario;

	public function ajaxEditarUsuario()
	{

		$item = "id";
		$valor = $this->idUsuario;

		$respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

		echo json_encode($respuesta);
	}

	/*=============================================
	ACTIVAR USUARIO
	=============================================*/

	public $activarUsuario;
	public $activarId;

	public function ajaxActivarUsuario()
	{

		$tabla = "usuarios";

		$item1 = "estado";
		$valor1 = $this->activarUsuario;

		$item2 = "id";
		$valor2 = $this->activarId;

		$respuesta = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

	}

	/*=============================================
	VALIDAR NO REPETIR USUARIO
	=============================================*/

	public $validarUsuario;
	public function ajaxValidarUsuario()
	{

		$item = "usuario";
		$valor = $this->validarUsuario;

		$respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

		echo json_encode($respuesta);
	}

	/*=============================================
	MOSTRAR USUARIOS SERVER-SIDE
	=============================================*/
	public function ajaxMostrarUsuariosServerSide()
	{
		$respuesta = ControladorUsuarios::ctrMostrarUsuariosServerSide($_POST);
		echo json_encode($respuesta);
	}


}

/*=============================================
VERIFICAR RELACIONES DEL USUARIO ANTES DE ELIMINAR
=============================================*/
if (isset($_POST["idUsuarioVerificarRelaciones"])) {
	$idUsuario = $_POST["idUsuarioVerificarRelaciones"];
	
	$db = Conexion::conectar();
	$relaciones = [];

	// 1. Verificar actividades
	$stmt = $db->prepare("SELECT COUNT(*) FROM actividades WHERE id_user = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "actividades";
	}

	// 2. Verificar ventas
	$stmt = $db->prepare("SELECT COUNT(*) FROM ventas WHERE id_vendedor = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "ventas";
	}

	// 3. Verificar gastos
	$stmt = $db->prepare("SELECT COUNT(*) FROM gastos WHERE id_usuario = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "gastos";
	}

	// 4. Verificar cajas_turnos
	$stmt = $db->prepare("SELECT COUNT(*) FROM cajas_turnos WHERE id_usuario = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "turnos de caja";
	}

	// 5. Verificar notas_credito
	$stmt = $db->prepare("SELECT COUNT(*) FROM notas_credito WHERE id_usuario = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "notas crédito";
	}

	// 6. Verificar traslados
	$stmt = $db->prepare("SELECT COUNT(*) FROM traslados WHERE id_usuario = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "traslados";
	}

	// 7. Verificar documentos_soporte
	$stmt = $db->prepare("SELECT COUNT(*) FROM documentos_soporte WHERE id_usuario = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "documentos soporte";
	}

	// 8. Verificar notas_ajuste_ds
	$stmt = $db->prepare("SELECT COUNT(*) FROM notas_ajuste_ds WHERE id_usuario = :id");
	$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->fetchColumn() > 0) {
		$relaciones[] = "notas de ajuste";
	}

	echo json_encode(["status" => "success", "relaciones" => $relaciones]);
	exit;
}

/*=============================================
ELIMINAR USUARIO
=============================================*/
if (isset($_POST["idUsuarioEliminar"])) {
	$eliminar = new ControladorUsuarios();
	$respuesta = $eliminar->ctrBorrarUsuario();
	echo $respuesta;
	exit;
}

/*=============================================
EDITAR USUARIO
=============================================*/
if (isset($_POST["idUsuario"])) {

	$editar = new AjaxUsuarios();
	$editar->idUsuario = $_POST["idUsuario"];
	$editar->ajaxEditarUsuario();

}

/*=============================================
ACTIVAR USUARIO
=============================================*/

if (isset($_POST["activarUsuario"])) {

	$activarUsuario = new AjaxUsuarios();
	$activarUsuario->activarUsuario = $_POST["activarUsuario"];
	$activarUsuario->activarId = $_POST["activarId"];
	$activarUsuario->ajaxActivarUsuario();
}

/*=============================================
VALIDAR NO REPETIR USUARIO
=============================================*/

if (isset($_POST["validarUsuario"])) {

	$valUsuario = new AjaxUsuarios();
	$valUsuario->validarUsuario = $_POST["validarUsuario"];
	$valUsuario->ajaxValidarUsuario();
}


/*=============================================
ACTUALIZAR IMAGEN DE USUARIO DESDE LA TABLA
=============================================*/

if (isset($_FILES["nuevaImagenUsuario"])) {

	require_once "../modelos/usuarios.modelo.php";

	$idUsuario = $_POST["idUsuarioImagen"];
	$usuario = $_POST["usuarioNombre"];

	list($ancho, $alto) = getimagesize($_FILES["nuevaImagenUsuario"]["tmp_name"]);

	$nuevoAncho = 500;
	$nuevoAlto = 500;

	// Crear directorio si no existe
	$directorio = "../vistas/img/usuarios/" . $usuario;

	if (!file_exists($directorio)) {
		mkdir($directorio, 0755, true);
	}

	// Procesar según el tipo de imagen
	$ruta = "";

	if ($_FILES["nuevaImagenUsuario"]["type"] == "image/jpeg") {

		$aleatorio = mt_rand(100, 999);
		$ruta = "vistas/img/usuarios/" . $usuario . "/" . $aleatorio . ".jpeg";

		$origen = imagecreatefromjpeg($_FILES["nuevaImagenUsuario"]["tmp_name"]);
		$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

		imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
		imagejpeg($destino, "../" . $ruta);

	}

	if ($_FILES["nuevaImagenUsuario"]["type"] == "image/png") {

		$aleatorio = mt_rand(100, 999);
		$ruta = "vistas/img/usuarios/" . $usuario . "/" . $aleatorio . ".png";

		$origen = imagecreatefrompng($_FILES["nuevaImagenUsuario"]["tmp_name"]);
		$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

		imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
		imagepng($destino, "../" . $ruta);

	}

	// Actualizar en la base de datos
	if (!empty($ruta)) {
		$tabla = "usuarios";
		$datos = array("foto" => $ruta);

		$respuesta = ModeloUsuarios::mdlActualizarImagenUsuario($tabla, $datos, $idUsuario);

		if ($respuesta == "ok") {
			// Actualizar la variable de sesión para que el cambio se refleje inmediatamente
			$_SESSION["foto"] = $ruta;
		}

		echo json_encode($respuesta);
	} else {
		echo json_encode("error");
	}

	exit;
}


/*=============================================
ACTUALIZAR PERFIL DE USUARIO ACTUAL
=============================================*/

if (isset($_POST["actualizarPerfil"])) {

	// Llamar al controlador para procesar la actualización
	ControladorUsuarios::ctrActualizarPerfil();

	exit;
}

/*=============================================
MOSTRAR USUARIOS SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
	$mostrar = new AjaxUsuarios();
	$mostrar->ajaxMostrarUsuariosServerSide();
}