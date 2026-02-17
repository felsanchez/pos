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
						  }).then(function(result){
								if (result.value) {

								window.location = "gastos";

								}
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
						  }).then(function(result){
								if (result.value) {

								window.location = "gastos";

								}
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

		if(isset($_GET["idGasto"])){

			$tabla ="gastos";
			$datos = $_GET["idGasto"];

			// Obtener información del gasto para eliminar imagen si existe
			$gasto = ModeloGastos::mdlMostrarGastos($tabla, "id", $datos);

			if(!empty($gasto["imagen_comprobante"]) && file_exists($gasto["imagen_comprobante"])){
				unlink($gasto["imagen_comprobante"]);
			}

			$respuesta = ModeloGastos::mdlEliminarGasto($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "El gasto ha sido eliminado correctamente",
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
	SUMA TOTAL DE GASTOS
	=============================================*/

	static public function ctrSumarTotalGastos(){

		$respuesta = ModeloGastos::mdlSumarTotalGastos();

		return $respuesta;

	}

	/*=============================================
	SUMA TOTAL DE GASTOS POR RANGO DE FECHAS
	=============================================*/

	static public function ctrSumarGastosPorFecha($fechaInicio, $fechaFin){

		$respuesta = ModeloGastos::mdlSumarGastosPorFecha($fechaInicio, $fechaFin);

		return $respuesta;

	}

	/*=============================================
	GASTOS POR CATEGORÍA
	=============================================*/

	static public function ctrGastosPorCategoria(){

		$respuesta = ModeloGastos::mdlGastosPorCategoria();

		return $respuesta;

	}

}