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

/*=============================================
TABLA ACTIVIDADES SERVER-SIDE
=============================================*/
if (isset($_POST["draw"])) {
    require_once "../modelos/sanitizer.php";
    require_once "../modelos/helpers.php";
    $respuesta = ControladorActividades::ctrMostrarActividadesServerSide($_POST);
    echo json_encode($respuesta);
    exit;
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
    // Obtener filtros del GET
    $filtroTipo = isset($_GET["filtroTipo"]) ? $_GET["filtroTipo"] : null;
    $filtroEstado = isset($_GET["filtroEstado"]) ? $_GET["filtroEstado"] : null;

    // Obtener actividades
    $actividades = ControladorActividades::ctrMostrarActividadesConCliente(null, null);

    // Filtrar actividades si aplica
    if (!empty($filtroTipo) || !empty($filtroEstado)) {
        $actividadesFiltradas = [];
        foreach ($actividades as $actividad) {
            if (!empty($filtroTipo) && strtolower($actividad["tipo"]) !== strtolower($filtroTipo)) {
                continue;
            }
            if (!empty($filtroEstado) && strtolower($actividad["estado"]) !== strtolower($filtroEstado)) {
                continue;
            }
            $actividadesFiltradas[] = $actividad;
        }
        $actividades = $actividadesFiltradas;
    }
    
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

        $isAllDay = !empty($actividad["todo_dia"]) || (substr($actividad["fecha"], 11, 8) === "00:00:00" && (empty($actividad["fecha_fin"]) || substr($actividad["fecha_fin"], 11, 8) === "23:59:59" || substr($actividad["fecha_fin"], 11, 8) === "00:00:00"));

        $startDate = $isAllDay ? substr($actividad["fecha"], 0, 10) : str_replace(" ", "T", $actividad["fecha"]);

        $evtObj = [
            "id"             => $actividad["id"],
            "title"          => $actividad["descripcion"],
            "descripcion_original" => $actividad["descripcion"],
            "fecha_full"     => $actividad["fecha"],
            "allDay"         => $isAllDay,
            "start"          => $startDate,
            "backgroundColor"=> $color,
            "borderColor"    => $color,
            "textColor"      => "#fff",
            "tipo"           => $actividad["tipo"],
            "estado"         => $actividad["estado"],
            "id_user"        => $actividad["id_user"],
            "nombre_usuario" => $actividad["nombre_usuario"] ?? 'Sin usuario',
            "id_cliente"     => $actividad["id_cliente"],
            "nombre_cliente" => $actividad["nombre_cliente"] ?? 'Sin cliente',
            "observacion"    => $actividad["observacion"],
            "categoria_google" => $actividad["categoria_google"] ?? "evento",
            "google_event_id"  => $actividad["google_event_id"] ?? null,
            "google_meet_link" => $actividad["google_meet_link"] ?? null,
            "ubicacion"        => $actividad["ubicacion"] ?? null,
            "invitados_email"  => $actividad["invitados_email"] ?? null,
            "archivo"          => $actividad["archivo"] ?? null
        ];

        if (!$isAllDay) {
            $evtObj["end"] = !empty($actividad["fecha_fin"]) ? str_replace(" ", "T", $actividad["fecha_fin"]) : str_replace(" ", "T", date("Y-m-d H:i:s", strtotime($actividad["fecha"] . " +1 hour")));
        } else if (!empty($actividad["fecha_fin"])) {
            $startD = substr($actividad["fecha"], 0, 10);
            $endD = substr($actividad["fecha_fin"], 0, 10);
            if ($endD > $startD) {
                // En FullCalendar, para allDay multidía la fecha de fin es exclusiva (+1 día)
                $evtObj["end"] = date("Y-m-d", strtotime($endD . " +1 day"));
            }
        }

        $eventos[] = $evtObj;
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

/*=============================================
  BUSCAR ACTIVIDADES POR FECHA
=============================================*/
if (isset($_POST["fecha"]) && !isset($_POST["idActividad"]) && (!isset($_POST["accion"]) || $_POST["accion"] !== "actualizarFecha")) {
    $item = "fecha";
    $valor = $_POST["fecha"];
    $respuesta = ControladorActividades::ctrMostrarActividadesConCliente($item, $valor);

    // Filtrar actividades por tipo/estado si se especificaron
    $filtroTipo = isset($_POST["filtroTipo"]) ? $_POST["filtroTipo"] : null;
    $filtroEstado = isset($_POST["filtroEstado"]) ? $_POST["filtroEstado"] : null;

    if (!empty($filtroTipo) || !empty($filtroEstado)) {
        $filtrado = [];
        foreach ($respuesta as $actividad) {
            if (!empty($filtroTipo) && strtolower($actividad["tipo"]) !== strtolower($filtroTipo)) {
                continue;
            }
            if (!empty($filtroEstado) && strtolower($actividad["estado"]) !== strtolower($filtroEstado)) {
                continue;
            }
            $filtrado[] = $filtrado;
        }
        $respuesta = $filtrado;
    }
    
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
if(isset($_POST["idActividad"]) && !isset($_POST["nuevoTipo"]) && !isset($_POST["nuevoEstado"])){

    $Actividad = new AjaxActividades();
    $Actividad -> idActividad = $_POST["idActividad"];
    $Actividad -> ajaxEditarActividad();
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
if (isset($_POST["idActividad"]) && isset($_POST["nuevoEstado"])) {
    $datos = array(
        "id" => $_POST["idActividad"],
        "estado" => $_POST["nuevoEstado"]
    );

    $respuesta = ControladorActividades::ctrActualizarEstadoActividad($datos);

    if ($respuesta === "ok") {
        if (class_exists("ControladorGoogle")) {
            require_once "../controladores/google.controlador.php";
            $actividadPrev = ModeloActividades::mdlMostrarActividades("actividades", "id", $_POST["idActividad"]);
            if (!empty($actividadPrev) && !empty($actividadPrev["google_event_id"])) {
                ControladorGoogle::ctrSincronizarActividad([
                    "id" => $_POST["idActividad"],
                    "id_user" => $_SESSION["id"] ?? $actividadPrev["id_user"],
                    "categoria_google" => $actividadPrev["categoria_google"] ?? "evento",
                    "google_event_id" => $actividadPrev["google_event_id"],
                    "descripcion" => $actividadPrev["descripcion"],
                    "observacion" => $actividadPrev["observacion"],
                    "fecha" => $actividadPrev["fecha"],
                    "fecha_fin" => $actividadPrev["fecha_fin"],
                    "todo_dia" => $actividadPrev["todo_dia"] ?? 0,
                    "estado" => $_POST["nuevoEstado"]
                ], "editar");
            }
        }

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
    exit;
}

/*=============================================
DESCONECTAR GOOGLE CALENDAR
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "desconectarGoogle") {
    require_once "../controladores/google.controlador.php";
    $idUser = $_SESSION["id"] ?? null;
    if ($idUser) {
        ControladorGoogle::ctrDesconectarGoogle($idUser);
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    exit;
}

/*=============================================
SINCRONIZAR DESDE GOOGLE (CALENDAR & TASKS)
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "sincronizarDesdeGoogle") {
    require_once "../controladores/google.controlador.php";
    $idUser = $_SESSION["id"] ?? null;
    if ($idUser) {
        try {
            $res = ControladorGoogle::ctrImportarNovedadesGoogle($idUser);
            echo json_encode($res);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Excepción en el servidor: " . $e->getMessage()]);
        } catch (Throwable $e) {
            echo json_encode(["status" => "error", "message" => "Error de ejecución: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Sesión no válida"]);
    }
    exit;
}

/*=============================================
PERMITE EDITAR Fecha (Drag & Drop con Sincronización Google)
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarFecha") {
    require_once "../controladores/google.controlador.php";
    $tabla = "actividades";
    $idActividad = $_POST["id"];
    $nuevaFecha = $_POST["fecha"];

    $actividadPrev = ModeloActividades::mdlMostrarActividades($tabla, "id", $idActividad);

    $isAllDay = isset($_POST["todo_dia"]) ? intval($_POST["todo_dia"]) : (empty($nuevaFecha) || strlen($nuevaFecha) <= 10 || substr($nuevaFecha, 11, 8) === "00:00:00" ? 1 : 0);

    // Calcular la nueva fecha fin manteniendo la duración original
    $duracion = 3600; // 1 hora por defecto
    if (!empty($actividadPrev["fecha"]) && !empty($actividadPrev["fecha_fin"])) {
        $diff = strtotime($actividadPrev["fecha_fin"]) - strtotime($actividadPrev["fecha"]);
        if ($diff > 0) {
            $duracion = $diff;
        }
    }
    $nuevaFechaFin = date("Y-m-d H:i:s", strtotime($nuevaFecha) + $duracion);

    $respuesta = ModeloActividades::mdlActualizarActividad($tabla, "fecha", $nuevaFecha, $idActividad);
    ModeloActividades::mdlActualizarActividad($tabla, "fecha_fin", $nuevaFechaFin, $idActividad);
    ModeloActividades::mdlActualizarActividad($tabla, "todo_dia", $isAllDay, $idActividad);

    if ($respuesta === "ok" && class_exists("ControladorGoogle")) {
        if (!empty($actividadPrev)) {
            $accionSync = !empty($actividadPrev["google_event_id"]) ? "editar" : "crear";
            $categoriaGoogle = !empty($actividadPrev["categoria_google"]) ? $actividadPrev["categoria_google"] : ((strtolower(trim($actividadPrev["tipo"] ?? "")) === "tarea") ? "tarea" : "evento");
            $resSync = ControladorGoogle::ctrSincronizarActividad([
                "id" => $idActividad,
                "id_user" => $_SESSION["id"] ?? $actividadPrev["id_user"],
                "categoria_google" => $categoriaGoogle,
                "google_event_id" => $actividadPrev["google_event_id"] ?? null,
                "descripcion" => $actividadPrev["descripcion"],
                "observacion" => $actividadPrev["observacion"],
                "fecha" => $nuevaFecha,
                "fecha_fin" => $nuevaFechaFin,
                "todo_dia" => $isAllDay,
                "ubicacion" => $actividadPrev["ubicacion"] ?? null,
                "invitados_email" => $actividadPrev["invitados_email"] ?? null
            ], $accionSync);

            if (!empty($resSync["google_event_id"])) {
                ModeloActividades::mdlActualizarGoogleInfo($idActividad, $resSync["google_event_id"], $resSync["google_meet_link"] ?? null);
            }
        }
    }

    echo json_encode($respuesta);
    exit;
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
			
  