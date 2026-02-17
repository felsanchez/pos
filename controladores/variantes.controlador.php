<?php

class ControladorVariantes{

	/*=============================================
	MOSTRAR TIPOS DE VARIANTES
	=============================================*/

	static public function ctrMostrarTiposVariantes($item, $valor){

		$tabla = "tipos_variantes";

		$respuesta = ModeloVariantes::mdlMostrarTiposVariantes($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	CREAR TIPO DE VARIANTE
	=============================================*/

	static public function ctrCrearTipoVariante(){

		if(isset($_POST["nuevoTipoVariante"])){

			if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoTipoVariante"])){

				$tabla = "tipos_variantes";

				$datos = array("nombre" => $_POST["nuevoTipoVariante"],
				               "orden" => $_POST["nuevoOrdenTipo"]);

				$respuesta = ModeloVariantes::mdlIngresarTipoVariante($tabla, $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "El tipo de variante ha sido guardado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {

									window.location = "variantes";

									}
								})

					</script>';

				}


			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡El tipo de variante no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "variantes";

							}
						})

			  	</script>';

			}

		}

	}

	/*=============================================
	MOSTRAR OPCIONES DE VARIANTES
	=============================================*/

	static public function ctrMostrarOpcionesVariantes($item, $valor){

		$tabla = "opciones_variantes";

		$respuesta = ModeloVariantes::mdlMostrarOpcionesVariantes($tabla, $item, $valor);

		return $respuesta;

	}

	
    /*=============================================
    CREAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function ctrCrearOpcionVariante(){

        if(isset($_POST["nuevaOpcion"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaOpcion"])){

                $tabla = "opciones_variantes";

                $datos = array("id_tipo_variante" => $_POST["idTipoVarianteOpcion"],
                            "nombre" => $_POST["nuevaOpcion"],
                            "orden" => $_POST["nuevoOrdenOpcion"]);

                $respuesta = ModeloVariantes::mdlIngresarOpcionVariante($tabla, $datos);

                if($respuesta == "ok"){

                    echo'<script>

                    swal({
                        type: "success",
                        title: "La opción ha sido guardada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                                    if (result.value) {

                                    window.location = "variantes";

                                    }
                                })

                    </script>';

                }


            }else{

                echo'<script>

                    swal({
                        type: "error",
                        title: "¡La opción no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {

                            window.location = "variantes";

                            }
                        })

                </script>';

            }

        }

    }



    /*=============================================
    EDITAR TIPO DE VARIANTE
    =============================================*/

    static public function ctrEditarTipoVariante(){

        if(isset($_POST["editarTipoVariante"])){

            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarTipoVariante"])){

                $tabla = "tipos_variantes";

                $datos = array("nombre" => $_POST["editarTipoVariante"],
                            "orden" => $_POST["editarOrdenTipo"],
                            "id" => $_POST["idTipo"]);

                $respuesta = ModeloVariantes::mdlEditarTipoVariante($tabla, $datos);

                if($respuesta == "ok"){

                    echo'<script>

                    swal({
                        type: "success",
                        title: "El tipo de variante ha sido actualizado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                                    if (result.value) {

                                    window.location = "variantes";

                                    }
                                })

                    </script>';

                }

            }else{

                echo'<script>

                    swal({
                        type: "error",
                        title: "¡El tipo de variante no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {

                            window.location = "variantes";

                            }
                        })

                </script>';

            }

        }

    }


    
    /*=============================================
    EDITAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function ctrEditarOpcionVariante(){

        if(isset($_POST["editarOpcion"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarOpcion"])){

                $tabla = "opciones_variantes";

                $datos = array("nombre" => $_POST["editarOpcion"],
                            "orden" => $_POST["editarOrdenOpcion"],
                            "id" => $_POST["idOpcion"]);

                $respuesta = ModeloVariantes::mdlEditarOpcionVariante($tabla, $datos);

                if($respuesta == "ok"){

                    echo'<script>

                    swal({
                        type: "success",
                        title: "La opción ha sido actualizada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                                    if (result.value) {

                                    window.location = "variantes";

                                    }
                                })

                    </script>';

                }

            }else{

                echo'<script>

                    swal({
                        type: "error",
                        title: "¡La opción no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {

                            window.location = "variantes";

                            }
                        })

                </script>';

            }

        }

    }


    /*=============================================
    ELIMINAR TIPO DE VARIANTE
    =============================================*/ 

    static public function ctrEliminarTipoVariante($idTipo){ 

        // Verificar si el tipo tiene opciones asociadas
        $tabla = "opciones_variantes";
        $item = "id_tipo_variante";
        $valor = $idTipo;

        $opciones = ModeloVariantes::mdlMostrarOpcionesVariantes($tabla, $item, $valor); 

        if(count($opciones) > 0){
            return "error_opciones";

        } 

        // Verificar si el tipo está siendo usado en productos
        $tabla2 = "productos_variantes_opciones";
        $checkUso = ModeloVariantes::mdlVerificarUsoTipoVariante($idTipo);

         if($checkUso > 0){
            return "error_uso";

        }

         // Si no tiene opciones ni está en uso, eliminar
        $tabla3 = "tipos_variantes";
        $respuesta = ModeloVariantes::mdlEliminarTipoVariante($tabla3, $idTipo);

        return $respuesta; 
    }

     /*=============================================
    ELIMINAR OPCIÓN DE VARIANTE
    =============================================*/

     static public function ctrEliminarOpcionVariante($idOpcion){ 

        // Verificar si la opción está siendo usada en productos
        $tabla = "productos_variantes_opciones";
        $item = "id_opcion_variante";
        $valor = $idOpcion; 

        $checkUso = ModeloVariantes::mdlVerificarUsoOpcionVariante($tabla, $item, $valor); 

        if($checkUso > 0){
            return "error";
        }

        // Si no está en uso, eliminar
        $tabla2 = "opciones_variantes";
        $respuesta = ModeloVariantes::mdlEliminarOpcionVariante($tabla2, $idOpcion);

         return $respuesta;
    }

}