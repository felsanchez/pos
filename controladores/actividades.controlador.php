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
							   "observacion" => $_POST["nuevaObservacion"]);

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
    $respuesta = ModeloActividades::mdlMostrarActividadesConCliente($tabla, $item, $valor);
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