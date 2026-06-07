<?php

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

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaActividad"]) &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoTipo"]) &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoEstado"])){

				$tabla = "actividades";

				// Convertimos el valor "0" en NULL para el cliente
				$idCliente = ($_POST["nuevoCliente"] == "0") ? null : $_POST["nuevoCliente"];

				$datos = array("descripcion" => $_POST["nuevaActividad"],
							   "tipo" => $_POST["nuevoTipo"],
							   "id_user" => $_POST["nuevoUsuario"],
					           "fecha" => $_POST["nuevaFecha"],
							   "estado" => $_POST["nuevoEstado"],
							   "id_cliente" => $idCliente,
							   "observacion" => $_POST["nuevaObservacion"],
							   "id_bodega" => isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1);

				$respuesta = ModeloActividades::mdlIngresarActividad($tabla, $datos);


				if ($respuesta == "ok") {

					// Verificar si la actividad creada requiere notificación
					ControladorNotificaciones::ctrVerificarActividadesProximas();

					// Determinar a de dónde redirigir según la URL actual o el origen
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

				// Determinar a de dónde redirigir según la URL actual o el origen
				$paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

				echo '<script>
					swal({
						type: "error",
						title: "¡La actividad no puede ir vacía o llevar caracteres especiales!",
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

		// Columnas para ordenar (coinciden con la vista HTML: 0=desc, 1=tipo, 2=resp, 3=fecha, 4=estado, 5=cliente, 6=notas)
		$columnsMap = array(
			0 => 'a.descripcion',
			1 => 'a.tipo',
			2 => 'u.nombre',
			3 => 'a.fecha',
			4 => 'a.estado',
			5 => 'c.nombre',
			6 => 'a.observacion',
			7 => 'a.id' // Acciones
		);

		$where = " WHERE 1=1 ";

		// Filtro por bodega activa
		$idBodegaActiva = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;
		$where .= " AND a.id_bodega = $idBodegaActiva ";

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (a.descripcion LIKE '%$searchValue%' OR a.tipo LIKE '%$searchValue%' OR a.estado LIKE '%$searchValue%' OR a.observacion LIKE '%$searchValue%' OR c.nombre LIKE '%$searchValue%' OR u.nombre LIKE '%$searchValue%') ";
		}

		// Filtros personalizados (Tipo y Estado)
		if (!empty($params['filtroTipo'])) {
			$filtroTipo = $params['filtroTipo'];
			$where .= " AND a.tipo = '$filtroTipo' ";
		}

		if (!empty($params['filtroEstado'])) {
			$filtroEstado = $params['filtroEstado'];
			$where .= " AND a.estado = '$filtroEstado' ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'a.id';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY a.id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$actividades = ModeloActividades::mdlMostrarActividadesServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloActividades::mdlGetTotalActividades($tabla, " WHERE 1=1 AND a.id_bodega = $idBodegaActiva ");
		$totalFiltered = ModeloActividades::mdlGetTotalActividades($tabla, $where);

		$data = array();

		// Pre-fetch estados para los badges y tooltips
		require_once "../controladores/estados-actividades.controlador.php";
		require_once "../modelos/estados-actividades.modelo.php";
		$estadosDisponibles = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);

		foreach ($actividades as $key => $value) {
			
			$nestedData = array();
			
			$fechaHoy = date('Y-m-d');
			$fechaActividad = !empty($value["fecha"]) ? substr($value["fecha"], 0, 10) : '';
			$esHoy = ($fechaActividad == $fechaHoy);

			// Buscar color del estado
			$estadoActual = $value["estado"] ?? "S/E";
			$colorEstado = "#999";
			foreach ($estadosDisponibles as $estadoFor) {
				if (strcasecmp($estadoFor["nombre"], $estadoActual) == 0) {
					$colorEstado = $estadoFor["color"];
					break;
				}
			}

			// Nombres para el tooltip
			$nomUser = !empty($value["nombre_usuario"]) ? $value["nombre_usuario"] : "Sin asignar";
			$nomCli = !empty($value["nombre_cliente"]) ? $value["nombre_cliente"] : "Sin cliente";

			// Tooltip HTML
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

			// Atributos del <tr>
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

			// 0: Descripción
			$nestedData[] = e($value["descripcion"]);

			// 1: Tipo
			$nestedData[] = e($value["tipo"]);

			// 2: Responsable
			$nestedData[] = e($nomUser);

			// 3: Fecha
			$nestedData[] = e($value["fecha"]);

			// 4: Estado (Badge)
			if (!empty($estadoActual) && $estadoActual !== "S/E") {
				$nestedData[] = '<span class="badge" style="background-color: ' . $colorEstado . '">' . ucfirst($estadoActual) . '</span>';
			} else {
				$nestedData[] = '<span class="text-muted">Sin estado</span>';
			}

			// 5: Cliente
			$nestedData[] = e($nomCli);

			// 6: Notas (Editable)
			$observacion = trim($value["observacion"] ?? "");
			$nestedData[] = '<div contenteditable="true" class="celda-observacion" tabindex="0" data-id="' . $value['id'] . '" data-placeholder="Escribe una nota..">' . e($observacion) . '</div>';

			// 7: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('actividades', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarActividad" data-id="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarActividad" idActividad="' . $value["id"] . '" title="Editar actividad"><i class="fa fa-pencil"></i></button>';
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

			// En el controlador
			//var_dump($actividad);

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarActividad"]) &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarTipo"]) &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEstado"])){

                    $tabla = "actividades";

					$idCliente = ($_POST["editarCliente"] == "0") ? null : $_POST["editarCliente"];

                    $datos = array(
								"id" => $_POST["idActividad"],
								"descripcion" => $_POST["editarActividad"],
								"tipo" => $_POST["editarTipo"],
								"id_user" => $_POST["editarUsuario"],
								 // "fecha" => $fecha,
								"estado" => $_POST["editarEstado"],
								//"id_cliente" => $_POST["editarCliente"],
								"id_cliente" => $idCliente,
								"observacion" => $_POST["editarObservacion"]);

								
                    $respuesta = ModeloActividades::mdlEditarActividad($tabla, $datos);    
    

                    if ($respuesta == "ok") {

						// Verificar si la actividad editada requiere notificación
						ControladorNotificaciones::ctrVerificarActividadesProximas();

                        // Determinar a de dónde redirigir según la URL actual o el origen
                        $paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

                        echo '<script>
                        swal({
                            type: "success",
                            title: "!La actividad ha sido editada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                            }).then((result)=>{
                                if(result.value){
    
                                   window.location = "'.$paginaDestino.'";
                                }
                            })
                         </script>';
                     }
                }
    
                else{
                    // Determinar a de dónde redirigir según la URL actual o el origen
                    $paginaDestino = isset($_POST["urlActual"]) ? $_POST["urlActual"] : "actividades";

                    echo '<script>
                        swal({
                            type: "error",
                            title: "!La actividad no puede ir vacío o llevar caracteres especiales!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
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

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
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