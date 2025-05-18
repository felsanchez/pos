<?php

class ControladorActividades{

	/*=============================================
	CREAR ACTIVIDADES
	=============================================*/

	static public function ctrCrearActividad(){

		if(isset($_POST["nuevaActividad"])){

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

			    	echo '<script>
					swal({
						type: "success",
						title: "!La actividad ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{
							if(result.value){

							   window.location = "actividades";
							}
						})
			     	</script>';
		         }
			}

			else{
				echo '<script>
					swal({
						type: "error",
						title: "!La actividad no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{

							if(result.value){

								window.location = "actividades";
							}
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
    
                        echo '<script>
                        swal({
                            type: "success",
                            title: "!La actividad ha sido editada correctamente!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                            }).then((result)=>{
                                if(result.value){
    
                                   window.location = "actividades";
                                }
                            })
                         </script>';
                     }
                }
    
                else{
                    echo '<script>
                        swal({
                            type: "error",
                            title: "!La actividad no puede ir vacío o llevar caracteres especiales!",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar",
                            closeOnConfirm: false
                            }).then((result)=>{
    
                                if(result.value){
    
                                    window.location = "actividades";
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

		if(isset($_GET["idActividad"])){

			$tabla = "actividades";
			$datos = $_GET["idActividad"];

			$respuesta = ModeloActividades::mdlEliminarActividad($tabla, $datos);
			//var_dump($idActividad);

			if($respuesta == "ok"){

				echo '<script>
					swal({
						type: "success",
						title: "!La actividad ha sido borrada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "actividades";
							}
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
	


}
