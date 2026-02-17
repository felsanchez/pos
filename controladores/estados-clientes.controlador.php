<?php

class ControladorEstadosClientes
{

	/*=============================================
	MOSTRAR ESTADOS
	=============================================*/

	static public function ctrMostrarEstadosClientes($item, $valor)
	{

		$tabla = "estados_clientes";
		$respuesta = ModeloEstadosClientes::mdlMostrarEstadosClientes($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	CREAR ESTADO
	=============================================*/

	static public function ctrCrearEstado()
	{
		if (isset($_POST["nuevoEstadoNombre"])) {

			// Detectar desde dónde se llamó el modal
			$origen = isset($_POST["origenModal"]) ? $_POST["origenModal"] : "estados-clientes";

			if ($origen == "clientes") {
				$redireccion = "clientes";
			} else if ($origen == "contactos") {
				$redireccion = "contactos";
			} else {
				$redireccion = "estados-clientes";
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoEstadoNombre"])) {

				// Obtener el orden máximo actual
				$estados = ModeloEstadosClientes::mdlMostrarEstadosClientes("estados_clientes", null, null);
				$maxOrden = 0;

				if ($estados) {
					foreach ($estados as $estado) {

						if ($estado["orden"] > $maxOrden) {
							$maxOrden = $estado["orden"];
						}
					}
				}

				$tabla = "estados_clientes";

				$datos = array(
					"nombre" => strtolower($_POST["nuevoEstadoNombre"]),
					"color" => $_POST["nuevoEstadoColor"],
					"orden" => $maxOrden + 1
				);

				$respuesta = ModeloEstadosClientes::mdlCrearEstado($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "¡El estado ha sido creado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
									window.location = "' . $redireccion . '";
							}
						});
					</script>';

				} else if ($respuesta == "duplicado") {

					echo '<script>
						swal({
							type: "error",
							title: "¡El nombre del estado ya existe!",
							text: "Por favor, elija un nombre diferente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
									window.location = "' . $redireccion . '";
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
								// Cerrar todos los modales antes de redireccionar
								$(".modal").modal("hide");
								$("body").removeClass("modal-open");
								$(".modal-backdrop").remove();

								setTimeout(function() {
									window.location = "' . $redireccion . '";
								}, 300);
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
							// Cerrar todos los modales antes de redireccionar
							$(".modal").modal("hide");
							$("body").removeClass("modal-open");
							$(".modal-backdrop").remove();

							setTimeout(function() {
								window.location = "' . $redireccion . '";
							}, 300);
						}
					});
				</script>';
			}
		}
	}

	/*=============================================
	EDITAR ESTADO
	=============================================*/

	static public function ctrEditarEstado()
	{

		if (isset($_POST["editarEstadoNombre"])) {

			// Detectar desde dónde se llamó el modal de edición
			$origen = isset($_POST["origenEdicion"]) ? $_POST["origenEdicion"] : "estados-clientes";
			$redireccion = ($origen == "clientes" || $origen == "contactos") ? $origen : "estados-clientes";

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEstadoNombre"])) {

				$tabla = "estados_clientes";

				// Obtener el nombre anterior para actualizar clientes si cambia
				$item = "id";
				$valor = $_POST["idEstado"];
				$estadoAnterior = ModeloEstadosClientes::mdlMostrarEstadosClientes($tabla, $item, $valor);
				$nombreAnterior = $estadoAnterior["nombre"];

				$datos = array(
					"id" => $_POST["idEstado"],
					"nombre" => strtolower($_POST["editarEstadoNombre"]),
					"color" => $_POST["editarEstadoColor"],
					"orden" => isset($_POST["editarEstadoOrden"]) ? $_POST["editarEstadoOrden"] : $estadoAnterior["orden"]
				);

				$respuesta = ModeloEstadosClientes::mdlEditarEstado($tabla, $datos);

				if ($respuesta == "ok") {

					// Si el nombre cambió, actualizar en la tabla de clientes
					if ($nombreAnterior != $datos["nombre"]) {
						ModeloEstadosClientes::mdlActualizarNombreEstadoClientes($nombreAnterior, $datos["nombre"]);
					}

					echo '<script>
						swal({
							type: "success",
							title: "¡El estado ha sido editado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								// Cerrar todos los modales antes de redireccionar
								$(".modal").modal("hide");
								$("body").removeClass("modal-open");
								$(".modal-backdrop").remove();

								setTimeout(function() {
									window.location = "' . $redireccion . '";
								}, 300);
							}
						});
					</script>';

				} else if ($respuesta == "duplicado") {
					echo '<script>
						swal({
							type: "error",
							title: "¡El nombre del estado ya existe!",
							text: "Por favor, elija un nombre diferente.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								window.location = "' . $redireccion . '";
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
									window.location = "' . $redireccion . '";
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
							// Cerrar todos los modales antes de redireccionar
							$(".modal").modal("hide");
							$("body").removeClass("modal-open");
							$(".modal-backdrop").remove();

							setTimeout(function() {
								window.location = "' . $redireccion . '";
							}, 300);
						}
					});
				</script>';
			}
		}
	}


	/*=============================================
	ELIMINAR ESTADO
	=============================================*/

	static public function ctrEliminarEstado()
	{

		if (isset($_GET["idEstado"])) {

			// Detectar desde dónde se llamó
			$origen = isset($_GET["origen"]) ? $_GET["origen"] : "estados-clientes";
			$redireccion = ($origen == "clientes" || $origen == "contactos") ? $origen : "estados-clientes";

			$tabla = "estados_clientes";

			$id = $_GET["idEstado"];

			$nombreEstado = $_GET["nombreEstado"];

			// Verificar si el estado está en uso
			$enUso = ModeloEstadosClientes::mdlVerificarEstadoEnUso($nombreEstado);

			if ($enUso > 0) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "Este estado está siendo usado por ' . $enUso . ' cliente(s).",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if(result.value){
							// Cerrar todos los modales antes de redireccionar
							$(".modal").modal("hide");
							$("body").removeClass("modal-open");
							$(".modal-backdrop").remove();

							setTimeout(function() {
								window.location = "' . $redireccion . '";
							}, 300);
						}
					});
				</script>';

			} else {

				$respuesta = ModeloEstadosClientes::mdlEliminarEstado($tabla, $id);

				if ($respuesta == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "¡El estado ha sido eliminado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result) => {
							if(result.value){
								// Cerrar todos los modales antes de redireccionar
								$(".modal").modal("hide");
								$("body").removeClass("modal-open");
								$(".modal-backdrop").remove();

								setTimeout(function() {
									window.location = "' . $redireccion . '";
								}, 300);
							}
						});
					</script>';
				}
			}
		}
	}



}