<?php
ob_start();
require_once "../modelos/session-manager.php";
SessionManager::startSecure();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once "../controladores/gastos.controlador.php";
require_once "../modelos/gastos.modelo.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";

/*=============================================
MOSTRAR GASTOS SERVER-SIDE (solo lectura, no requiere CSRF)
=============================================*/
if (isset($_POST["draw"])) {
    if (ob_get_length()) ob_clean();
    require_once "../controladores/configuracion.controlador.php";
    require_once "../modelos/configuracion.modelo.php";
    require_once "../modelos/sanitizer.php";
    $respuesta = ControladorGastos::ctrMostrarGastosServerSide($_POST);
    echo json_encode($respuesta);
    exit;
}

// VALIDAR CSRF para todas las peticiones de escritura
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}

class AjaxGastos{

	/*=============================================
	EDITAR GASTO
	=============================================*/

	public $idGasto;

	public function ajaxEditarGasto(){

		$item = "id";
		$valor = $this->idGasto;

		$respuesta = ControladorGastos::ctrMostrarGastos($item, $valor);

		echo json_encode($respuesta);

	}

	/*=============================================
	OBTENER GASTOS FILTRADOS
	=============================================*/

	public $fechaInicio;
	public $fechaFin;
	public $categoria;
	public $proveedor;

	public function ajaxObtenerGastosFiltrados(){

		$respuesta = ControladorGastos::ctrMostrarGastosFiltrados(
			$this->fechaInicio,
			$this->fechaFin,
			$this->categoria,
			$this->proveedor
		);

		echo json_encode($respuesta);

	}

	/*=============================================
	OBTENER TOTAL DE GASTOS
	=============================================*/

	public function ajaxObtenerTotalGastos(){

		$respuesta = ControladorGastos::ctrSumarTotalGastos();

		echo json_encode($respuesta);

	}

	/*=============================================
	OBTENER GASTOS POR CATEGORÍA
	=============================================*/

	public function ajaxObtenerGastosPorCategoria(){

		$respuesta = ControladorGastos::ctrGastosPorCategoria();

		echo json_encode($respuesta);

	}

}

/*=============================================
ELIMINAR GASTO
=============================================*/
if (isset($_POST["idGastoEliminar"])) {
    $eliminar = new ControladorGastos();
    $respuesta = $eliminar->ctrEliminarGasto();
    echo $respuesta;
    exit;
}

/*=============================================
EDITAR GASTO
=============================================*/
if(isset($_POST["idGasto"])){

	$gasto = new AjaxGastos();
	$gasto -> idGasto = $_POST["idGasto"];
	$gasto -> ajaxEditarGasto();
}

/*=============================================
OBTENER GASTOS FILTRADOS
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "filtrarGastos"){

	$gastos = new AjaxGastos();
	$gastos -> fechaInicio = $_POST["fechaInicio"];
	$gastos -> fechaFin = $_POST["fechaFin"];
	$gastos -> categoria = $_POST["categoria"];
	$gastos -> proveedor = $_POST["proveedor"];
	$gastos -> ajaxObtenerGastosFiltrados();
}

/*=============================================
OBTENER TOTAL DE GASTOS
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerTotal"){

	$gastos = new AjaxGastos();
	$gastos -> ajaxObtenerTotalGastos();
}

/*=============================================
OBTENER GASTOS POR CATEGORÍA
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerPorCategoria"){

	$gastos = new AjaxGastos();
	$gastos -> ajaxObtenerGastosPorCategoria();
}

/*=============================================
ACTUALIZAR IMAGEN DE COMPROBANTE DESDE LA TABLA
=============================================*/

if(isset($_FILES["nuevaImagenComprobante"])){ 

	require_once "../modelos/gastos.modelo.php"; 

	$idGasto = $_POST["idGastoImagen"];
	$concepto = $_POST["conceptoGasto"];

	list($ancho, $alto) = getimagesize($_FILES["nuevaImagenComprobante"]["tmp_name"]); 

	$nuevoAncho = 800;
	$nuevoAlto = 600; 

	// Crear directorio si no existe
	$directorio = "vistas/img/gastos/comprobantes";
	if(!file_exists("../".$directorio)){
		mkdir("../".$directorio, 0755, true);
	}

 	// Procesar según el tipo de imagen
	$ruta = "";

	if($_FILES["nuevaImagenComprobante"]["type"] == "image/jpeg"){ 

		$aleatorio = mt_rand(100, 999);
		$ruta = $directorio."/gasto_".$idGasto."_".$aleatorio.".jpeg"; 

		$origen = imagecreatefromjpeg($_FILES["nuevaImagenComprobante"]["tmp_name"]);
		$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

		imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
		imagejpeg($destino, "../".$ruta); 
	} 

	if($_FILES["nuevaImagenComprobante"]["type"] == "image/png"){ 

		$aleatorio = mt_rand(100, 999);
		$ruta = $directorio."/gasto_".$idGasto."_".$aleatorio.".png"; 

		$origen = imagecreatefrompng($_FILES["nuevaImagenComprobante"]["tmp_name"]);
		$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

 		imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
		imagepng($destino, "../".$ruta);
	}

	// Obtener la imagen actual para eliminarla
	$item = "id";
	$valor = $idGasto;
	$gastoActual = ControladorGastos::ctrMostrarGastos($item, $valor);

	// Eliminar imagen anterior si existe
	if(!empty($gastoActual["imagen_comprobante"]) && file_exists("../".$gastoActual["imagen_comprobante"])){
		unlink("../".$gastoActual["imagen_comprobante"]);
	}

 	// Actualizar en la base de datos
	$tabla = "gastos";
	$datos = array(
		"id" => $idGasto,
		"imagen_comprobante" => $ruta
	);

 	$respuesta = ModeloGastos::mdlActualizarImagenGasto($tabla, $datos); 

	echo json_encode($respuesta);
}
