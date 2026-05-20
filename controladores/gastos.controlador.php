<?php

class ControladorGastos{

	/*=============================================
	MOSTRAR GASTOS
	=============================================*/

	static public function ctrMostrarGastos($item, $valor){

		$tabla = "gastos";

		$respuesta = ModeloGastos::mdlMostrarGastos($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR GASTOS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarGastosServerSide($params)
	{
		$tabla = "gastos";

		// Mapeo de columnas para ordenamiento dinámico
		if ($_SESSION["perfil"] == "Administrador") {
			$columnsMap = array(
				0 => 'g.concepto',
				1 => 'g.monto',
				2 => 'c.nombre',
				3 => 'g.estado',
				4 => 'p.nombre',
				5 => 'b.nombre', // Sucursal
				6 => 'g.id',     // Imagen
				7 => 'g.fecha',
				8 => 'g.notas'
			);
		} else {
			$columnsMap = array(
				0 => 'g.concepto',
				1 => 'g.monto',
				2 => 'c.nombre',
				3 => 'g.estado',
				4 => 'p.nombre',
				5 => 'g.id',     // Imagen
				6 => 'g.fecha',
				7 => 'g.notas'
			);
		}

		$where = " WHERE 1=1 ";

		// Filtro por Fechas
		if (!empty($params['fechaInicio']) && !empty($params['fechaFin'])) {
			$where .= " AND g.fecha BETWEEN '" . $params['fechaInicio'] . "' AND '" . $params['fechaFin'] . "' ";
		}

		// Filtro por Categoría
		if (!empty($params['categoriaId'])) {
			$where .= " AND g.id_categoria_gasto = " . $params['categoriaId'];
		}

		// Filtro por Proveedor
		if (!empty($params['proveedorId'])) {
			$where .= " AND g.id_proveedor = " . $params['proveedorId'];
		}

		// Filtro por Sucursal (Seguridad Multi-sucursal y Filtro Admin)
		if ($_SESSION["perfil"] == "Administrador") {
			if (!empty($params['bodegaId'])) {
				$where .= " AND g.id_bodega = " . $params['bodegaId'];
			}
		} else {
			$idBodega = !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
			$where .= " AND g.id_bodega = " . $idBodega;
		}

		// Búsqueda global
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (g.concepto LIKE '%$searchValue%' OR g.codigo LIKE '%$searchValue%' OR p.nombre LIKE '%$searchValue%' OR g.notas LIKE '%$searchValue%') ";
		}

		// Orden
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'g.id';
			if ($colName === 'g.fecha') {
				$order = " ORDER BY g.fecha " . $params['order'][0]['dir'] . ", g.id DESC";
			} else {
				$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
			}
		} else {
			$order = " ORDER BY g.id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$gastos = ModeloGastos::mdlMostrarGastosServerSide($tabla, $where, $order, $limit);
		
		$whereTotal = " WHERE 1=1 ";
		if ($_SESSION["perfil"] != "Administrador") {
			$idBodega = !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
			$whereTotal .= " AND g.id_bodega = " . $idBodega;
		}
		
		$totalData = ModeloGastos::mdlGetTotalGastos($tabla, $whereTotal);
		$totalFiltered = ModeloGastos::mdlGetTotalGastos($tabla, $where);

		$data = array();
		$fechaHoy = date('Y-m-d');

		foreach ($gastos as $key => $value) {
			
			$nestedData = array();

			// Columna 1: Concepto
			$nestedData[] = e($value["concepto"]);

			// Columna 2: Monto
			$nestedData[] = '<strong>$' . number_format($value["monto"], 2, ',', '.') . '</strong>';

			// Columna 3: Categoría
			$categoriaBadge = '-';
			if (!empty($value["categoria_nombre"])) {
				$categoriaBadge = '<span class="badge" style="background-color: ' . $value["categoria_color"] . '">' . e($value["categoria_nombre"]) . '</span>';
			}
			$nestedData[] = $categoriaBadge;

			// Columna 4: Estado
			$estadoBadge = '';
			if ($value["estado"] == "aprobado") {
				$estadoBadge = '<button class="btn btn-success btn-xs">Aprobado</button>';
			} else if ($value["estado"] == "pendiente") {
				$estadoBadge = '<button class="btn btn-warning btn-xs">Pendiente</button>';
			} else {
				$estadoBadge = '<button class="btn btn-danger btn-xs">Rechazado</button>';
			}
			$nestedData[] = $estadoBadge;

			// Columna 5: Proveedor
			$nestedData[] = e(!empty($value["proveedor_nombre"]) ? $value["proveedor_nombre"] : '-');

			// Columna Sucursal (Solo para Administrador)
			if ($_SESSION["perfil"] == "Administrador") {
				$nestedData[] = e($value["bodega_nombre"]);
			}

			// Columna 6: Imagen
			$imgSrc = !empty($value["imagen_comprobante"]) ? $value["imagen_comprobante"] : "vistas/img/gastos/default/sin-imagen.png";
			$nestedData[] = '<img src="' . $imgSrc . '" class="img-thumbnail img-comprobante-clickeable" width="40px" style="cursor: pointer;" data-imagen="' . $imgSrc . '" data-idgasto="' . $value["id"] . '" data-concepto="' . e($value["concepto"]) . '">';

			// Columna 7: Fecha
			$nestedData[] = !empty($value["fecha"]) ? date("d/m/Y", strtotime($value["fecha"])) : '-';

			// Columna 8: Notas (Editable)
			$nestedData[] = '<div contenteditable="true" class="celda-notas-gasto" data-id="' . $value["id"] . '">' . e($value["notas"]) . '</div>';

			// Columna 9: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('gastos', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarGasto" idGasto="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarGasto" title="Editar gasto"><i class="fa fa-pencil"></i></button>';
			}
			if (puedeAccion('gastos', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarGasto" idGasto="' . $value["id"] . '" codigoGasto="' . $value["codigo"] . '" conceptoGasto="' . e($value["concepto"]) . '" title="Eliminar gasto"><i class="fa fa-times"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			// Row attributes for styling
			if ($value["fecha"] == $fechaHoy) {
				$nestedData['DT_RowAttr'] = array(
					'style' => 'border-left: 6px solid #28a745 !important; background-color: #f0f9f4; box-shadow: inset 6px 0 0 #28a745;'
				);
			}

			$data[] = $nestedData;
		}

		return array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);
	}

	/*=============================================
	MOSTRAR GASTOS CON FILTROS
	=============================================*/

	static public function ctrMostrarGastosFiltrados($fechaInicio, $fechaFin, $categoria, $proveedor){

		$respuesta = ModeloGastos::mdlMostrarGastosFiltrados($fechaInicio, $fechaFin, $categoria, $proveedor);

		return $respuesta;

	}

	/*=============================================
	CREAR GASTO
	=============================================*/

	static public function ctrCrearGasto(){

		if(isset($_POST["nuevoConceptoGasto"])){
			@file_put_contents("log_post.txt", date("[Y-m-d H:i:s] ") . "ctrCrearGasto: " . print_r($_POST, true) . "\n", FILE_APPEND);

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
					}).then(() => {
						window.location = "gastos";
					})
				</script>';
				return;
			}

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoConceptoGasto"])){

				/*=============================================
				VALIDAR IMAGEN COMPROBANTE
				=============================================*/

				$rutaImagen = "";

				if(isset($_FILES["nuevaImagenComprobante"]["tmp_name"]) && !empty($_FILES["nuevaImagenComprobante"]["tmp_name"])){

					list($ancho, $alto) = getimagesize($_FILES["nuevaImagenComprobante"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;

					/*=============================================
					CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA IMAGEN DEL COMPROBANTE
					=============================================*/

					$directorio = "vistas/img/comprobantes/";

					if(!file_exists($directorio)){
						mkdir($directorio, 0755, true);
					}

					/*=============================================
					DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
					=============================================*/

					if($_FILES["nuevaImagenComprobante"]["type"] == "image/jpeg"){

						/*=============================================
						GUARDAMOS LA IMAGEN EN EL DIRECTORIO
						=============================================*/

						$aleatorio = mt_rand(100,999);

						$rutaImagen = $directorio.$aleatorio.".jpg";

						$origen = imagecreatefromjpeg($_FILES["nuevaImagenComprobante"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagejpeg($destino, $rutaImagen);

					}

					if($_FILES["nuevaImagenComprobante"]["type"] == "image/png"){

						/*=============================================
						GUARDAMOS LA IMAGEN EN EL DIRECTORIO
						=============================================*/

						$aleatorio = mt_rand(100,999);

						$rutaImagen = $directorio.$aleatorio.".png";

						$origen = imagecreatefrompng($_FILES["nuevaImagenComprobante"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagepng($destino, $rutaImagen);

					}

				}

				/*=============================================
				GENERAR CÓDIGO AUTOMÁTICO
				=============================================*/

				$tabla = "gastos";
				$ultimoCodigo = ModeloGastos::mdlObtenerUltimoCodigo($tabla);
				$nuevoCodigo = "GAS-" . str_pad($ultimoCodigo + 1, 3, "0", STR_PAD_LEFT);

				$tabla = "gastos";

				$datos = array("codigo" => $nuevoCodigo,
							   "concepto" => $_POST["nuevoConceptoGasto"],
							   "monto" => $_POST["nuevoMontoGasto"],
							   "fecha" => $_POST["nuevaFechaGasto"],
							   "id_categoria_gasto" => $_POST["nuevaCategoriaGasto"],
							   "id_usuario" => $_SESSION["id"],
							   "id_bodega" => !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1,
							   "id_proveedor" => !empty($_POST["nuevoProveedorGasto"]) ? $_POST["nuevoProveedorGasto"] : null,
							   "metodo_pago" => $_POST["nuevoMetodoPagoGasto"],
							   "numero_comprobante" => $_POST["nuevoNumeroComprobante"],
							   "imagen_comprobante" => $rutaImagen,
							   "estado" => $_POST["nuevoEstadoGasto"],
							   "notas" => $_POST["nuevasNotasGasto"]);

				$respuesta = ModeloGastos::mdlIngresarGasto($tabla, $datos);

				if($respuesta == "ok"){

				// Verificar si el gasto creado requiere notificación
				ControladorNotificaciones::ctrVerificarGastosProximos();

					echo'<script>

					swal({
						  type: "success",
						  title: "El gasto ha sido guardado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
								window.location = "gastos";
							})

					</script>';

				}


			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡El concepto no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "gastos";

							}
						})

			  	</script>';

			}

		}

	}

	/*=============================================
	EDITAR GASTO
	=============================================*/

	static public function ctrEditarGasto(){

		if(isset($_POST["editarConceptoGasto"])){
			@file_put_contents("log_post.txt", date("[Y-m-d H:i:s] ") . "ctrEditarGasto: " . print_r($_POST, true) . "\n", FILE_APPEND);

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
							window.location = "gastos";
						}
					})
				</script>';
				return;
			}

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarConceptoGasto"])){

				/*=============================================
				VALIDAR IMAGEN COMPROBANTE
				=============================================*/

				$rutaImagen = $_POST["imagenActual"];

				if(isset($_FILES["editarImagenComprobante"]["tmp_name"]) && !empty($_FILES["editarImagenComprobante"]["tmp_name"])){

					list($ancho, $alto) = getimagesize($_FILES["editarImagenComprobante"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;

					/*=============================================
					CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA IMAGEN DEL COMPROBANTE
					=============================================*/

					$directorio = "vistas/img/comprobantes/";

					if(!file_exists($directorio)){
						mkdir($directorio, 0755, true);
					}

					/*=============================================
					ELIMINAR IMAGEN ANTERIOR SI EXISTE
					=============================================*/

					if(!empty($_POST["imagenActual"]) && file_exists($_POST["imagenActual"])){
						unlink($_POST["imagenActual"]);
					}

					/*=============================================
					DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
					=============================================*/

					if($_FILES["editarImagenComprobante"]["type"] == "image/jpeg"){

						/*=============================================
						GUARDAMOS LA IMAGEN EN EL DIRECTORIO
						=============================================*/

						$aleatorio = mt_rand(100,999);

						$rutaImagen = $directorio.$aleatorio.".jpg";

						$origen = imagecreatefromjpeg($_FILES["editarImagenComprobante"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagejpeg($destino, $rutaImagen);

					}

					if($_FILES["editarImagenComprobante"]["type"] == "image/png"){

						/*=============================================
						GUARDAMOS LA IMAGEN EN EL DIRECTORIO
						=============================================*/

						$aleatorio = mt_rand(100,999);

						$rutaImagen = $directorio.$aleatorio.".png";

						$origen = imagecreatefrompng($_FILES["editarImagenComprobante"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagepng($destino, $rutaImagen);

					}

				}

				$tabla = "gastos";

				$datos = array("id" => $_POST["idGasto"],
							   "concepto" => $_POST["editarConceptoGasto"],
							   "monto" => $_POST["editarMontoGasto"],
							   "fecha" => $_POST["editarFechaGasto"],
							   "id_categoria_gasto" => $_POST["editarCategoriaGasto"],
							   "id_proveedor" => !empty($_POST["editarProveedorGasto"]) ? $_POST["editarProveedorGasto"] : null,
							   "metodo_pago" => $_POST["editarMetodoPagoGasto"],
							   "numero_comprobante" => $_POST["editarNumeroComprobante"],
							   "imagen_comprobante" => $rutaImagen,
							   "estado" => $_POST["editarEstadoGasto"],
							   "notas" => $_POST["editarNotasGasto"]);

				$respuesta = ModeloGastos::mdlEditarGasto($tabla, $datos);

				if($respuesta == "ok"){

				// Verificar si el gasto editado requiere notificación
				ControladorNotificaciones::ctrVerificarGastosProximos();

					echo'<script>

					swal({
						  type: "success",
						  title: "El gasto ha sido editado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
								window.location = "gastos";
							})

					</script>';

				}


			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡El concepto no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "gastos";

							}
						})

			  	</script>';

			}

		}

	}

	/*=============================================
	ELIMINAR GASTO
	=============================================*/

	static public function ctrEliminarGasto(){

		if (isset($_GET["idGasto"]) || isset($_POST["idGastoEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idGastoEliminar"])) {
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
							window.location = "gastos";
						}
					})
				</script>';
				return;
			}

			$tabla ="gastos";
			$idGasto = isset($_GET["idGasto"]) ? $_GET["idGasto"] : $_POST["idGastoEliminar"];

			// Obtener información del gasto para eliminar imagen si existe
			$gasto = ModeloGastos::mdlMostrarGastos($tabla, "id", $idGasto);

			if(!empty($gasto["imagen_comprobante"]) && file_exists($gasto["imagen_comprobante"])){
				unlink($gasto["imagen_comprobante"]);
			}

			$respuesta = ModeloGastos::mdlEliminarGasto($tabla, $idGasto);

			if($respuesta == "ok"){

				if (isset($_POST["idGastoEliminar"])) {
					return "ok";
				}

				echo'<script>

				swal({
					  type: "success",
					  title: "El gasto ha sido eliminado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(() => {
								window.location = "gastos";
						})

				</script>';

			}

		}

	}

	/*=============================================
	SUMA TOTAL DE GASTOS
	=============================================*/

	static public function ctrSumarTotalGastos(){

		$idBodega = ($_SESSION["perfil"] != "Administrador") ? $_SESSION["id_bodega"] : null;
		$respuesta = ModeloGastos::mdlSumarTotalGastos($idBodega);

		return $respuesta;

	}

	/*=============================================
	SUMA TOTAL DE GASTOS POR RANGO DE FECHAS
	=============================================*/

	static public function ctrSumarGastosPorFecha($fechaInicio, $fechaFin){

		$idBodega = ($_SESSION["perfil"] != "Administrador") ? $_SESSION["id_bodega"] : null;
		$respuesta = ModeloGastos::mdlSumarGastosPorFecha($fechaInicio, $fechaFin, $idBodega);

		return $respuesta;

	}

	/*=============================================
	GASTOS POR CATEGORÍA
	=============================================*/

	static public function ctrGastosPorCategoria(){

		$idBodega = ($_SESSION["perfil"] != "Administrador") ? $_SESSION["id_bodega"] : null;
		$respuesta = ModeloGastos::mdlGastosPorCategoria($idBodega);

		return $respuesta;

	}

}