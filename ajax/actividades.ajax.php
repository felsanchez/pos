<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../controladores/actividades.controlador.php";
require_once "../modelos/actividades.modelo.php";
require_once "../controladores/estados-actividades.controlador.php";
require_once "../modelos/estados-actividades.modelo.php";
require_once "../modelos/csrf.php";

// VALIDAR CSRF para todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}


class AjaxActividades{

	/*=============================================
	EDITAR ACTIVIDAD
	=============================================*/

	public $idActividad;

	public function ajaxEditarActividad(){
    $item = "id";
    $valor = $this->idActividad;
    
    // Usar el método con cliente Y usuario
    $respuesta = ControladorActividades::ctrMostrarActividadesConCliente($item, $valor);
    
    echo json_encode($respuesta);
}



	/*==============CUADRO ACTIVIDADES===============================*/
	public function ajaxListarActividades() {
    // Obtener actividades
    $actividades = ControladorActividades::ctrMostrarActividadesConCliente(null, null);
    
    // Obtener estados para el mapeo de colores
    $estados = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
    $mapaEstados = [];
    
    if (is_array($estados)) {
        foreach ($estados as $e) {
            $mapaEstados[strtolower($e["nombre"])] = $e["color"] ?? "#3c8dbc";
        }
    }

    $eventos = [];
    
    foreach ($actividades as $actividad) {
        // Normalizar estado para buscar el color
        $estadoNombre = $actividad["estado"] ?? "";
        $estadoLower = strtolower($estadoNombre);
        
        // Color por defecto (azul AdminLTE) si no hay match
        $color = isset($mapaEstados[$estadoLower]) ? $mapaEstados[$estadoLower] : "#3c8dbc";

        // Título con prefijo de estado
        $tituloConEstado = !empty($estadoNombre) ? "[" . strtoupper($estadoNombre) . "] " . $actividad["descripcion"] : $actividad["descripcion"];

        $eventos[] = [
            "id"             => $actividad["id"],
            "title"          => $tituloConEstado,
            "descripcion_original" => $actividad["descripcion"],
            "fecha_full"     => $actividad["fecha"],
            "start"          => $actividad["fecha"],
            "end"            => $actividad["fecha"],
            "backgroundColor"=> $color,
            "borderColor"    => $color,
            "textColor"      => "#fff", // Asegurar legibilidad
            "tipo"           => $actividad["tipo"],
            "estado"         => $actividad["estado"],
            "id_user"        => $actividad["id_user"],
            "nombre_usuario" => $actividad["nombre_usuario"] ?? 'Sin usuario',
            "id_cliente"     => $actividad["id_cliente"],
            "nombre_cliente" => $actividad["nombre_cliente"] ?? 'Sin cliente',
            "observacion"    => $actividad["observacion"]
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($eventos);
}


// AGREGAR este nuevo método para obtener clientes
public function ajaxListarClientes(){
    $respuesta = ControladorActividades::ctrMostrarClientes();
    echo json_encode($respuesta);
}

public function ajaxListarUsuarios(){
    $respuesta = ControladorActividades::ctrMostrarUsuarios();
    echo json_encode($respuesta);
}

    


}



/*=============================================
CUADRO ACTIVIDADES
=============================================*/

// Listar actividades (GET)
if (isset($_GET["action"]) && $_GET["action"] == "listar") {
    $actividades = new AjaxActividades();
    $actividades->ajaxListarActividades();
    exit;
}

// Editar actividad (POST)
/*if (isset($_POST["idActividad"])) {
    $actividad = new AjaxActividades();
    $actividad->idActividad = $_POST["idActividad"];
    $actividad->ajaxEditarActividad();
    exit;
}*/

/*=============================================
  BUSCAR ACTIVIDADES POR FECHA
=============================================*/
if (isset($_POST["fecha"]) && !isset($_POST["idActividad"])) {
    $item = "fecha";
    $valor = $_POST["fecha"];
    $respuesta = ControladorActividades::ctrMostrarActividadesConCliente($item, $valor);
    
    echo json_encode($respuesta);
    exit;
}

/*=============================================
LISTAR CLIENTES
=============================================*/
if (isset($_GET["action"]) && $_GET["action"] == "clientes") {
    $clientes = new AjaxActividades();
    $clientes->ajaxListarClientes();
    exit;
}

if (isset($_GET["action"]) && $_GET["action"] == "usuarios") {
    $usuarios = new AjaxActividades();
    $usuarios->ajaxListarUsuarios();
    exit;
}



        /*=============================================
        EDITAR Actividad
        =============================================*/
        if(isset($_POST["idActividad"])){

            $Actividad = new AjaxActividades();
            $Actividad -> idActividad = $_POST["idActividad"];
            $Actividad -> ajaxEditarActividad();
			//return;
        }


		/*=============================================
        Guardar Tipo de Actividad
        =============================================*/
		if (isset($_POST["idActividad"]) && isset($_POST["nuevoTipo"])) {
			$datos = array(
				"id" => $_POST["idActividad"],
				"tipo" => $_POST["nuevoTipo"]
			);
		
			$respuesta = ControladorActividades::ctrActualizarTipoActividad($datos);
		
			header('Content-Type: application/json');
			echo json_encode($respuesta);
			exit;
		}
		
		
		/*=============================================
        Guardar Estado de Actividad
        =============================================*/
		/*if (isset($_POST["idActividad"]) && isset($_POST["nuevoEstado"])) {
			$datos = array(
				"id" => $_POST["idActividad"],
				"estado" => $_POST["nuevoEstado"]
			);
			$respuesta = ControladorActividades::ctrActualizarEstadoActividad($datos);
			header('Content-Type: application/json');
			echo json_encode($respuesta);
			exit;
		}
			*/		

		if (isset($_POST["idActividad"]) && isset($_POST["nuevoEstado"])) {
			$datos = array(
				"id" => $_POST["idActividad"],
				"estado" => $_POST["nuevoEstado"]
			);
		
			$respuesta = ControladorActividades::ctrActualizarEstadoActividad($datos);
		
			// Siempre devolvemos un objeto JSON estructurado
			if ($respuesta === "ok") {
				echo json_encode([
					"status" => "ok",
					"idActividad" => $datos["id"],
					"nuevoEstado" => $datos["estado"]
				]);
			} else {
				echo json_encode([
					"status" => "error",
					"message" => "Error al actualizar estado"
				]);
			}
		
			exit;
		}
		


		/*=============================================
		PERMITE EDITAR Observacion
		=============================================*/
		
		if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarObservacion") {
	$tabla = "actividades";
	$datos = array(
	"id" => $_POST["id"],
	"observacion" => $_POST["observacion"]
	);
	$respuesta = ModeloActividades::mdlActualizarObservacion("actividades", $_POST["id"], $_POST["observacion"]);
	echo json_encode($respuesta);
}

/*=============================================
ELIMINAR ACTIVIDAD
=============================================*/
if (isset($_POST["idActividadEliminar"])) {
    $eliminar = new ControladorActividades();
    $respuesta = $eliminar->ctrEliminarActividad();
    echo $respuesta;
    exit;
}
			
  