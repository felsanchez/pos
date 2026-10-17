<?php
date_default_timezone_set('America/Bogota');

require_once __DIR__ . "/google.controlador.php";

class ControladorActividades{

	/*=============================================
	CREAR ACTIVIDADES
	=============================================*/

	static public function ctrCrearActividad(){

		if(isset($_POST["nuevaActividad"])){

			/*=============================================
			VALIDAR CSRF
			=============================================*/
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "actividades";
						}
					})
				</script>';
				return;
			}

			if (!empty($_POST["nuevaActividad"]) && !empty($_POST["nuevoTipo"]) && !empty($_POST["nuevoEstado"])) {

				$tabla = "actividades";

				$idCliente = (isset($_POST["nuevoCliente"]) && $_POST["nuevoCliente"] !== "0") ? $_POST["nuevoCliente"] : null;
				$fechaRaw = trim($_POST["nuevaFecha"]);
				$isAllDay = !empty($_POST["chkTodoElDia"]) || (strlen($fechaRaw) <= 10);

				if ($isAllDay) {
					$dateOnly = substr($fechaRaw, 0, 10);
					$fechaInicio = $dateOnly . " 00:00:00";
					$fechaFin = !empty($_POST["nuevaFechaFin"]) ? $_POST["nuevaFechaFin"] : ($dateOnly . " 23:59:59");
				} else {
					$fechaInicio = str_replace("T", " ", $fechaRaw);
					if (strlen($fechaInicio) == 16) {
						$fechaInicio .= ":00";
					}
					$fechaFin = !empty($_POST["nuevaFechaFin"]) ? str_replace("T", " ", $_POST["nuevaFechaFin"]) : date("Y-m-d H:i:s", strtotime($fechaInicio . " +1 hour"));
				}

				$rutaArchivo = null;
				if (isset($_FILES["nuevoArchivoActividad"]) && !empty($_FILES["nuevoArchivoActividad"]["name"]) && $_FILES["nuevoArchivoActividad"]["error"] == 0) {
					$directorio = "vistas/img/actividades/";
					if (!file_exists($directorio)) {
						mkdir($directorio, 0755, true);
					}
					$extension = strtolower(pathinfo($_FILES["nuevoArchivoActividad"]["name"], PATHINFO_EXTENSION));
					$nombreArchivo = "actividad_" . date("Ymd_His") . "_" . uniqid() . "." . $extension;
					$rutaArchivo = $directorio . $nombreArchivo;
					move_uploaded_file($_FILES["nuevoArchivoActividad"]["tmp_name"], $rutaArchivo);
				}

				$datos = array(
					"descripcion" => trim($_POST["nuevaActividad"]),
					"tipo" => trim($_POST["nuevoTipo"]),
					"id_user" => $_POST["nuevoUsuario"],
					"fecha" => $fechaInicio,
					"fecha_fin" => $fechaFin,
					"todo_dia" => $isAllDay ? 1 : 0,
					"estado" => trim($_POST["nuevoEstado"]),
					"id_cliente" => $idCliente,
					"observacion" => trim($_POST["nuevaObservacion"] ?? ""),
					"id_bodega" => isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1,
					"categoria_google" => !empty($_POST["categoriaGoogle"]) ? $_POST["categoriaGoogle"] : ((strtolower(trim($_POST["nuevoTipo"])) === "tarea") ? "tarea" : "evento"),
					"generar_meet" => !empty($_POST["generarMeet"]) ? 1 : 0,
					"ubicacion" => !empty($_POST["nuevaUbicacion"]) ? trim($_POST["nuevaUbicacion"]) : null,
					"invitados_email" => !empty($_POST["nuevosInvitados"]) ? trim($_POST["nuevosInvitados"]) : null,
					"archivo" => $rutaArchivo
				);

				$respuestaId = ModeloActividades::mdlIngresarActividad($tabla, $datos);

				if ($respuestaId && $respuestaId != "error") {

					$idActividad = is_numeric($respuestaId) ? intval($respuestaId) : null;

					// Sincronizar con Google Calendar / Tasks si está conectado
					if (class_exists("ControladorGoogle")) {
						$resSync = ControladorGoogle::ctrSincronizarActividad($datos, "crear");
						if ($idActividad && !empty($resSync["google_event_id"])) {
							ModeloActividades::mdlActualizarGoogleInfo($idActividad, $resSync["google_event_id"], $resSync["google_meet_link"] ?? null);
						}
					}

					// Verificar si la actividad creada requiere notificación
					ControladorNotificaciones::ctrVerificarActividadesProximas();

					// Determinar a dónde redirigir según la URL actual o el origen
					$paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

					echo '<script>
					swal({
						type: "success",
						title: "¡La actividad ha sido guardada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {
							   window.location = "'.$paginaDestino.'";
						})
			     	</script>';
				}
			} else {

				$paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

				echo '<script>
					swal({
						type: "error",
						title: "¡La actividad no puede ir vacía!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {
							   window.location = "'.$paginaDestino.'";
						})
				</script>';
			}


		}

	}

	/*=============================================
	MOSTRAR Actividades
	=============================================*/

	static public function ctrMostrarActividades($item, $valor){

		$tabla = "actividades";

		$respuesta = ModeloActividades::mdlMostrarActividades($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	MOSTRAR ACTIVIDADES SERVER-SIDE
	=============================================*/
	static public function ctrMostrarActividadesServerSide($params)
	{
		$tabla = "actividades";

		$columnsMap = array(
			0 => 'a.descripcion',
			1 => 'a.tipo',
			2 => 'u.nombre',
			3 => 'a.fecha',
			4 => 'a.estado',
			5 => 'c.nombre',
			6 => 'a.observacion',
			7 => 'a.id'
		);

		$where = " WHERE 1=1 ";

		$idBodegaActiva = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;
		$where .= " AND a.id_bodega = $idBodegaActiva ";

		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (a.descripcion LIKE '%$searchValue%' OR a.tipo LIKE '%$searchValue%' OR a.estado LIKE '%$searchValue%' OR a.observacion LIKE '%$searchValue%' OR c.nombre LIKE '%$searchValue%' OR u.nombre LIKE '%$searchValue%') ";
		}

		if (!empty($params['filtroTipo'])) {
			$filtroTipo = $params['filtroTipo'];
			$where .= " AND a.tipo = '$filtroTipo' ";
		}

		if (!empty($params['filtroEstado'])) {
			$filtroEstado = $params['filtroEstado'];
			$where .= " AND a.estado = '$filtroEstado' ";
		}

		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'a.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY a.id DESC";
		}

		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		$actividades = ModeloActividades::mdlMostrarActividadesServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloActividades::mdlGetTotalActividades($tabla, " WHERE 1=1 AND a.id_bodega = $idBodegaActiva ");
		$totalFiltered = ModeloActividades::mdlGetTotalActividades($tabla, $where);

		$data = array();

		require_once "../controladores/estados-actividades.controlador.php";
		require_once "../modelos/estados-actividades.modelo.php";
		$estadosDisponibles = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);

		foreach ($actividades as $key => $value) {
			
			$nestedData = array();
			
			$fechaHoy = date('Y-m-d');
			$fechaActividad = !empty($value["fecha"]) ? substr($value["fecha"], 0, 10) : '';
			$esHoy = ($fechaActividad == $fechaHoy);

			$estadoActual = $value["estado"] ?? "S/E";
			$colorEstado = "#999";
			foreach ($estadosDisponibles as $estadoFor) {
				if (strcasecmp($estadoFor["nombre"], $estadoActual) == 0) {
					$colorEstado = $estadoFor["color"];
					break;
				}
			}

			$nomUser = !empty($value["nombre_usuario"]) ? $value["nombre_usuario"] : "Sin asignar";
			$nomCli = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Sin cliente";

			$tooltipHTMLBody = '
				<div class="tooltip-card" style="border-left: 5px solid ' . $colorEstado . '">
				<div class="tooltip-header">
					<span><i class="fa fa-info-circle"></i> VISTA RÁPIDA</span>
					<span class="badge" style="background-color: ' . $colorEstado . '">' . ucfirst($estadoActual) . '</span>
				</div>
				<div class="tooltip-body">
					<div class="tooltip-item">
					<i class="fa fa-tasks"></i>
					<div><span class="tooltip-label">Descripción</span><span class="tooltip-value">' . e($value["descripcion"]) . '</span></div>
					</div>
					<div class="tooltip-item">
					<i class="fa fa-calendar-check-o"></i>
					<div><span class="tooltip-label">Fecha y Hora</span><span class="tooltip-value">' . e($value["fecha"]) . '</span></div>
					</div>
					<div class="tooltip-item">
					<i class="fa fa-tags"></i>
					<div><span class="tooltip-label">Tipo</span><span class="tooltip-value">' . e($value["tipo"]) . '</span></div>
					</div>
					<div class="tooltip-item">
					<i class="fa fa-user"></i>
					<div><span class="tooltip-label">Cliente</span><span class="tooltip-value">' . e($nomCli) . '</span></div>
					</div>
					<div class="tooltip-item">
					<i class="fa fa-user-circle"></i>
					<div><span class="tooltip-label">Responsable</span><span class="tooltip-value">' . e($nomUser) . '</span></div>
					</div>
				</div>
				<div class="tooltip-footer">ID Actividad: #' . $value["id"] . '</div>
				</div>';

			$dtRowAttr = array(
				'data-tipo' => strtolower($value["tipo"] ?? ""),
				'data-estado' => strtolower($value["estado"] ?? ""),
				'data-actividad-id' => $value["id"],
				'data-tippy-content' => htmlspecialchars($tooltipHTMLBody)
			);
			if ($esHoy) {
				$dtRowAttr['style'] = "border-left: 6px solid #28a745 !important; background-color: #f0f9f4; box-shadow: inset 6px 0 0 #28a745;";
			}

			$nestedData['DT_RowAttr'] = $dtRowAttr;
			$nestedData['DT_RowClass'] = 'has-tooltip';

			$nestedData[] = e($value["descripcion"]);
			$nestedData[] = e($value["tipo"]);
			$nestedData[] = e($nomUser);
			$nestedData[] = e($value["fecha"]);

			if (!empty($estadoActual) && $estadoActual !== "S/E") {
				$nestedData[] = '<span class="badge" style="background-color: ' . $colorEstado . '">' . ucfirst($estadoActual) . '</span>';
			} else {
				$nestedData[] = '<span class="text-muted">Sin estado</span>';
			}

			$nestedData[] = e($nomCli);

			$observacion = trim($value["observacion"] ?? "");
			$nestedData[] = '<div contenteditable="true" class="celda-observacion" tabindex="0" data-id="' . $value['id'] . '" data-placeholder="Escribe una nota..">' . e($observacion) . '</div>';

			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('actividades', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarActividad" data-id="' . $value["id"] . '" idActividad="' . $value["id"] . '" title="Editar actividad"><i class="fa fa-pencil"></i></button>';
			} else {
				$botonesAcciones .= '<button class="btn btn-warning" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para editar"><i class="fa fa-pencil"></i></button>';
			}
			if (puedeAccion('actividades', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarActividad" idActividad="' . $value["id"] . '" title="Eliminar actividad"><i class="fa fa-times"></i></button>';
			} else {
				$botonesAcciones .= '<button class="btn btn-danger" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para eliminar"><i class="fa fa-times"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		return $json_data;
	}

	/*=============================================
	EDITAR Actividad
	=============================================*/

	static public function ctrEditarActividad(){

		if(isset($_POST["editarActividad"])){

			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "actividades";
						}
					})
				</script>';
				return;
			}

			if(!empty($_POST["editarActividad"]) && !empty($_POST["editarTipo"]) && !empty($_POST["editarEstado"])){

				$tabla = "actividades";

				$idCliente = ($_POST["editarCliente"] == "0") ? null : $_POST["editarCliente"];

				$rutaArchivo = $_POST["archivoActual"] ?? null;
				if (isset($_FILES["editarArchivoActividad"]) && !empty($_FILES["editarArchivoActividad"]["name"]) && $_FILES["editarArchivoActividad"]["error"] == 0) {
					$directorio = "vistas/img/actividades/";
					if (!file_exists($directorio)) {
						mkdir($directorio, 0755, true);
					}
					$extension = strtolower(pathinfo($_FILES["editarArchivoActividad"]["name"], PATHINFO_EXTENSION));
					$nombreArchivo = "actividad_" . date("Ymd_His") . "_" . uniqid() . "." . $extension;
					$rutaArchivo = $directorio . $nombreArchivo;
					if (move_uploaded_file($_FILES["editarArchivoActividad"]["tmp_name"], $rutaArchivo)) {
						if (!empty($_POST["archivoActual"]) && file_exists($_POST["archivoActual"])) {
							unlink($_POST["archivoActual"]);
						}
					}
				}

				$actividadPrev = ModeloActividades::mdlMostrarActividades($tabla, "id", $_POST["idActividad"]);

				$fechaInicio = $actividadPrev["fecha"] ?? date("Y-m-d H:i:s");
				$fechaFin = $actividadPrev["fecha_fin"] ?? null;
				$isAllDay = $actividadPrev["todo_dia"] ?? 0;

				if (!empty($_POST["editarFecha"])) {
					$fechaRaw = trim($_POST["editarFecha"]);
					$isAllDay = !empty($_POST["editarChkTodoElDia"]) || (strlen($fechaRaw) <= 10);

					if ($isAllDay) {
						$dateOnly = substr($fechaRaw, 0, 10);
						$fechaInicio = $dateOnly . " 00:00:00";
						$fechaFin = !empty($_POST["editarFechaFin"]) ? $_POST["editarFechaFin"] : ($dateOnly . " 23:59:59");
					} else {
						$fechaInicio = str_replace("T", " ", $fechaRaw);
						if (strlen($fechaInicio) == 16) {
							$fechaInicio .= ":00";
						}
						$fechaFin = !empty($_POST["editarFechaFin"]) ? str_replace("T", " ", $_POST["editarFechaFin"]) : date("Y-m-d H:i:s", strtotime($fechaInicio . " +1 hour"));
					}
				}

				$generarMeet = !empty($_POST["editarGenerarMeet"]) ? 1 : 0;
				$gMeetInitial = ($generarMeet == 1) ? ($actividadPrev["google_meet_link"] ?? null) : null;

				$datos = array(
					"id" => $_POST["idActividad"],
					"descripcion" => trim($_POST["editarActividad"]),
					"tipo" => trim($_POST["editarTipo"]),
					"id_user" => $_POST["editarUsuario"],
					"fecha" => $fechaInicio,
					"fecha_fin" => $fechaFin,
					"todo_dia" => $isAllDay ? 1 : 0,
					"estado" => trim($_POST["editarEstado"]),
					"id_cliente" => $idCliente,
					"observacion" => trim($_POST["editarObservacion"] ?? ""),
					"categoria_google" => !empty($_POST["editarCategoriaGoogle"]) ? $_POST["editarCategoriaGoogle"] : ((strtolower(trim($_POST["editarTipo"])) === "tarea") ? "tarea" : "evento"),
					"generar_meet" => $generarMeet,
					"google_meet_link" => $gMeetInitial,
					"ubicacion" => !empty($_POST["editarUbicacion"]) ? trim($_POST["editarUbicacion"]) : null,
					"invitados_email" => !empty($_POST["editarInvitados"]) ? trim($_POST["editarInvitados"]) : null,
					"archivo" => $rutaArchivo,
					"google_event_id" => $actividadPrev["google_event_id"] ?? null
				);

				$respuesta = ModeloActividades::mdlEditarActividad($tabla, $datos);    

				if ($respuesta == "ok") {

					if (class_exists("ControladorGoogle")) {
						$resSync = ControladorGoogle::ctrSincronizarActividad($datos, "editar");
						$meetLinkToSave = !empty($resSync["google_meet_link"]) ? $resSync["google_meet_link"] : null;
						$gEvIdToSave = !empty($resSync["google_event_id"]) ? $resSync["google_event_id"] : ($datos["google_event_id"] ?? null);
						ModeloActividades::mdlActualizarGoogleInfo($_POST["idActividad"], $gEvIdToSave, $meetLinkToSave);
					}

					ControladorNotificaciones::ctrVerificarActividadesProximas();
					$paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

					echo '<script>
					swal({
						type: "success",
						title: "¡La actividad ha sido editada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "'.$paginaDestino.'";
							}
						})
					 </script>';
				}
			} else {
				$paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

				echo '<script>
					swal({
						type: "error",
						title: "¡La actividad no puede ir vacía!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "'.$paginaDestino.'";
							}
						})
				</script>';
			}

		}

	}

	/*=============================================
	BORRAR actividades
	=============================================*/

	static public function ctrEliminarActividad(){

		if (isset($_GET["idActividad"]) || isset($_POST["idActividadEliminar"])) {

			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idActividadEliminar"])) {
					return "error_csrf";
				}
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "actividades";
						}
					})
				</script>';
				return;
			}

			$tabla = "actividades";
			$idActividad = isset($_GET["idActividad"]) ? $_GET["idActividad"] : $_POST["idActividadEliminar"];

			$actividadPrev = ModeloActividades::mdlMostrarActividades($tabla, "id", $idActividad);
			if (!empty($actividadPrev) && !empty($actividadPrev["google_event_id"]) && class_exists("ControladorGoogle")) {
				ControladorGoogle::ctrSincronizarActividad([
					"id_user" => $actividadPrev["id_user"],
					"categoria_google" => $actividadPrev["categoria_google"] ?? "evento",
					"google_event_id" => $actividadPrev["google_event_id"]
				], "eliminar");
			}

			$respuesta = ModeloActividades::mdlEliminarActividad($tabla, $idActividad);

			if($respuesta == "ok"){

				if (isset($_POST["idActividadEliminar"])) {
					return "ok";
				}

				echo '<script>
					swal({
						type: "success",
						title: "¡La actividad ha sido borrada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(() => {
								window.location = "actividades";
						});
				</script>';
			}

		}
	}


	/*=============================================
	Guardar Tipo de Actividad
	=============================================*/
	public static function ctrActualizarTipoActividad($datos) {
		$tabla = "actividades";
		return ModeloActividades::mdlActualizarTipoActividad($tabla, $datos);
	}

	/*=============================================
	Guardar Estado
	=============================================*/
	public static function ctrActualizarEstadoActividad($datos) {
		$tabla = "actividades";
		return ModeloActividades::mdlActualizarEstadoActividad($tabla, $datos);
	}


//CUADRO ACTIVIDADES CON CLIENTE********************************************************
	// Agregar este método después de ctrMostrarActividades
static public function ctrMostrarActividadesConCliente($item, $valor){
    $tabla = "actividades";
    $idBodega = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : null;
    $respuesta = ModeloActividades::mdlMostrarActividadesConCliente($tabla, $item, $valor, $idBodega);
    return $respuesta;
}

// Método para obtener clientes
static public function ctrMostrarClientes(){
    $respuesta = ModeloActividades::mdlMostrarClientes();
    return $respuesta;
}

static public function ctrMostrarUsuarios(){
    $respuesta = ModeloActividades::mdlMostrarUsuarios();
    return $respuesta;
}



}