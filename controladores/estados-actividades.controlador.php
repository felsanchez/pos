<?php

class ControladorEstadosActividades{

	/*=============================================
	MOSTRAR ESTADOS
	=============================================*/

	static public function ctrMostrarEstadosActividades($item, $valor){

		$tabla = "estados_actividades";
		$respuesta = ModeloEstadosActividades::mdlMostrarEstadosActividades($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	CREAR ESTADO
	=============================================*/

	static public function ctrCrearEstado(){

		if(isset($_POST["nuevoEstadoNombre"])){

			// Detectar desde dónde se llamó el modal
			$redireccion = isset($_POST["origenModal"]) && $_POST["origenModal"] == "actividades" ? "actividades" : "estados-actividades";

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoEstadoNombre"])){

				// Obtener el orden máximo actual
				$estados = ModeloEstadosActividades::mdlMostrarEstadosActividades("estados_actividades", null, null);
				$maxOrden = 0;

				if($estados){
					foreach($estados as $estado){

						if($estado["orden"] > $maxOrden){
							$maxOrden = $estado["orden"];
						}
					}
				}

				$tabla = "estados_actividades";

				$datos = array(
					"nombre" => strtolower($_POST["nuevoEstadoNombre"]),
					"color" => $_POST["nuevoEstadoColor"],
					"orden" => $maxOrden + 1
				);

				$respuesta = ModeloEstadosActividades::mdlCrearEstado($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
						swal({
							type: "success",
							title: "¡El estado ha sido creado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});
					</script>';

				} else if($respuesta == "duplicado"){

					echo '<script>
						swal({
							type: "error",
							title: "¡El nombre del estado ya existe!",
							text: "Por favor, elija un nombre diferente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});
					</script>';

				} else {
					echo '<script>
						swal({
							type: "error",
							title: "¡Error al crear el estado!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});

					</script>';
				}

 			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if(result.value){
							window.location = "'.$redireccion.'";
						}

					});

				</script>';
			}
		}
	}

	/*=============================================
	EDITAR ESTADO
	=============================================*/

	static public function ctrEditarEstado(){

		if(isset($_POST["editarEstadoNombre"])){

			// Detectar desde dónde se llamó el modal
			$redireccion = isset($_POST["origenModal"]) && $_POST["origenModal"] == "actividades" ? "actividades" : "estados-actividades";

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEstadoNombre"])){

				$tabla = "estados_actividades";

				$datos = array(
					"id" => $_POST["idEstado"],
					"nombre" => strtolower($_POST["editarEstadoNombre"]),
					"color" => $_POST["editarEstadoColor"],
					"orden" => $_POST["editarEstadoOrden"]
				);

				$respuesta = ModeloEstadosActividades::mdlEditarEstado($tabla, $datos);

				if($respuesta == "ok"){
					echo '<script>
						swal({
							type: "success",
							title: "¡El estado ha sido editado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});
					</script>';
				} else if($respuesta == "duplicado"){
					echo '<script>
						swal({
							type: "error",
							title: "¡El nombre del estado ya existe!",
							text: "Por favor, elija un nombre diferente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});
					</script>';
				} else {
					echo '<script>
						swal({
							type: "error",
							title: "¡Error al editar el estado!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if(result.value){
							window.location = "'.$redireccion.'";
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR ESTADO
	=============================================*/

	static public function ctrEliminarEstado(){

		if(isset($_GET["idEstado"])){

			$tabla ="estados_actividades";
			$id = $_GET["idEstado"];
			$nombreEstado = $_GET["nombreEstado"];

			// Detectar desde dónde se llamó la eliminación
			$redireccion = isset($_GET["origen"]) && $_GET["origen"] == "actividades" ? "actividades" : "estados-actividades";

			// Verificar si el estado está en uso
			$enUso = ModeloEstadosActividades::mdlVerificarEstadoEnUso($nombreEstado);

			if($enUso > 0){
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "Este estado está siendo usado por ' . $enUso . ' actividad(es).",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if(result.value){
							window.location = "'.$redireccion.'";
						}
					});
				</script>';

			} else {
				$respuesta = ModeloEstadosActividades::mdlEliminarEstado($tabla, $id);
				if($respuesta == "ok"){
					echo '<script>
						swal({
							type: "success",
							title: "¡El estado ha sido eliminado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "'.$redireccion.'";
							}
						});
					</script>';
				}
			}
		}
	}

}