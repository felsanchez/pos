<?php

class ControladorTiposActividades{

	/*=============================================
	MOSTRAR TIPOS
	=============================================*/

	static public function ctrMostrarTiposActividades($item, $valor){

		$tabla = "tipos_actividades";
		$respuesta = ModeloTiposActividades::mdlMostrarTiposActividades($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	CREAR TIPO
	=============================================*/

	static public function ctrCrearTipo(){

		if(isset($_POST["nuevoTipoNombre"])){

			// Detectar desde dónde se llamó el modal
			$redireccion = isset($_POST["origenModal"]) && $_POST["origenModal"] == "actividades" ? "actividades" : "tipos-actividades";

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoTipoNombre"])){

				// Obtener el orden máximo actual
				$tipos = ModeloTiposActividades::mdlMostrarTiposActividades("tipos_actividades", null, null);
				$maxOrden = 0;

				if($tipos){
					foreach($tipos as $tipo){

						if($tipo["orden"] > $maxOrden){
							$maxOrden = $tipo["orden"];
						}
					}
				}

				$tabla = "tipos_actividades";

				$datos = array(
					"nombre" => strtolower($_POST["nuevoTipoNombre"]),
					"orden" => $maxOrden + 1
				);

				$respuesta = ModeloTiposActividades::mdlCrearTipo($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
						swal({
							type: "success",
							title: "¡El tipo ha sido creado correctamente!",
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
							title: "¡El nombre del tipo ya existe!",
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
							title: "¡Error al crear el tipo!",
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
	EDITAR TIPO
	=============================================*/

	static public function ctrEditarTipo(){

		if(isset($_POST["editarTipoNombre"])){

			// Detectar desde dónde se llamó el modal
			$redireccion = isset($_POST["origenModal"]) && $_POST["origenModal"] == "actividades" ? "actividades" : "tipos-actividades";

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarTipoNombre"])){

				$tabla = "tipos_actividades";

				$datos = array(
					"id" => $_POST["idTipo"],
					"nombre" => strtolower($_POST["editarTipoNombre"]),
					"orden" => $_POST["editarTipoOrden"]
				);

				$respuesta = ModeloTiposActividades::mdlEditarTipo($tabla, $datos);

				if($respuesta == "ok"){
					echo '<script>
						swal({
							type: "success",
							title: "¡El tipo ha sido editado correctamente!",
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
							title: "¡El nombre del tipo ya existe!",
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
							title: "¡Error al editar el tipo!",
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
	ELIMINAR TIPO
	=============================================*/

	static public function ctrEliminarTipo(){

		if (isset($_GET["idTipo"]) || isset($_POST["idTipoEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idTipoEliminar"])) {
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

			$tabla ="tipos_actividades";
			$id = isset($_GET["idTipo"]) ? $_GET["idTipo"] : $_POST["idTipoEliminar"];
			$nombreTipo = isset($_GET["nombreTipo"]) ? $_GET["nombreTipo"] : $_POST["nombreTipo"];

			// Detectar desde dónde se llamó la eliminación
			$redireccion = isset($_GET["origen"]) && $_GET["origen"] == "actividades" ? "actividades" : "tipos-actividades";

			// Verificar si el tipo está en uso
			$enUso = ModeloTiposActividades::mdlVerificarTipoEnUso($nombreTipo);

			if($enUso > 0){
				if (isset($_POST["idTipoEliminar"])) {
					return "error_en_uso";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "Este tipo está siendo usado por ' . $enUso . ' actividad(es).",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if(result.value){
							window.location = "'.$redireccion.'";
						}
					});
				</script>';

			} else {
				$respuesta = ModeloTiposActividades::mdlEliminarTipo($tabla, $id);

				if($respuesta == "ok"){
					if (isset($_POST["idTipoEliminar"])) {
						return "ok";
					}
					echo '<script>
						swal({
							type: "success",
							title: "¡El tipo ha sido eliminado correctamente!",
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