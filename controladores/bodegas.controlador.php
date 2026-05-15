<?php

class ControladorBodegas
{

	/*=============================================
	CREAR BODEGA
	=============================================*/
	static public function ctrCrearBodega()
	{
		if (isset($_POST["nuevaBodega"])) {
			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaBodega"])) {

				$tabla = "bodegas";

				$datos = array(
					"nombre" => $_POST["nuevaBodega"],
					"direccion" => $_POST["nuevaDireccionBodega"],
					"telefono" => $_POST["nuevoTelefonoBodega"],
					"estado" => 1
				);

				$respuesta = ModeloBodegas::mdlIngresarBodega($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						type: "success",
						title: "La sucursal ha sido guardada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El nombre de la sucursal no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
			  	</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR BODEGAS
	=============================================*/
	static public function ctrMostrarBodegas($item, $valor)
	{
		$tabla = "bodegas";
		$respuesta = ModeloBodegas::mdlMostrarBodegas($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR BODEGA
	=============================================*/
	static public function ctrEditarBodega()
	{
		if (isset($_POST["editarBodega"])) {
			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarBodega"])) {

				$tabla = "bodegas";

				$datos = array(
					"id" => $_POST["idBodega"],
					"nombre" => $_POST["editarBodega"],
					"direccion" => $_POST["editarDireccionBodega"],
					"telefono" => $_POST["editarTelefonoBodega"]
				);

				$respuesta = ModeloBodegas::mdlEditarBodega($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
					swal({
						type: "success",
						title: "La sucursal ha sido cambiada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El nombre de la sucursal no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
			  	</script>';
			}
		}
	}

	/*=============================================
	BORRAR BODEGA
	=============================================*/
	static public function ctrBorrarBodega()
	{
		if (isset($_GET["idBodega"])) {

			// Proteger la Bodega Principal (ID 1)
			if($_GET["idBodega"] == 1){
				echo '<script>
					swal({
						type: "error",
						title: "¡La Bodega Principal no puede ser eliminada!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
				</script>';
				return;
			}

			$tabla = "bodegas";
			$datos = $_GET["idBodega"];

			// Aquí deberíamos validar si la bodega tiene productos_bodegas asociados, etc.
			// Por ahora se borrará si no hay constraints de Foreign Key restrictivas.

			$respuesta = ModeloBodegas::mdlBorrarBodega($tabla, $datos);

			if ($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "La sucursal ha sido borrada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
					</script>';
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede borrar la sucursal porque tiene productos o ventas asociadas!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "bodegas";
							}
						})
				</script>';
			}
		}
	}
}
