<?php

class ControladorCategoriasGastos{

	/*=============================================
	MOSTRAR CATEGORÍAS DE GASTOS
	=============================================*/

	static public function ctrMostrarCategoriasGastos($item, $valor){

		$tabla = "categorias_gastos";

		$respuesta = ModeloCategoriasGastos::mdlMostrarCategoriasGastos($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	CREAR CATEGORÍA DE GASTO
	=============================================*/

	static public function ctrCrearCategoriaGasto(){

		if(isset($_POST["nombreCategoriaGasto"])){

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

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nombreCategoriaGasto"])){

				$tabla = "categorias_gastos";

				$datos = array("nombre" => $_POST["nombreCategoriaGasto"],
							   "color" => $_POST["colorCategoriaGasto"],
							   "descripcion" => $_POST["descripcionCategoriaGasto"]);

				$respuesta = ModeloCategoriasGastos::mdlIngresarCategoriaGasto($tabla, $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "La categoría ha sido guardada correctamente",
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
						  title: "¡La categoría no puede ir vacía o llevar caracteres especiales!",
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
	EDITAR CATEGORÍA DE GASTO
	=============================================*/

	static public function ctrEditarCategoriaGasto(){

		if(isset($_POST["editarNombreCategoriaGasto"])){

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

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombreCategoriaGasto"])){

				$tabla = "categorias_gastos";

				$datos = array("id" => $_POST["idCategoriaGasto"],
							   "nombre" => $_POST["editarNombreCategoriaGasto"],
							   "color" => $_POST["editarColorCategoriaGasto"],
							   "descripcion" => $_POST["editarDescripcionCategoriaGasto"]);

				$respuesta = ModeloCategoriasGastos::mdlEditarCategoriaGasto($tabla, $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "La categoría ha sido editada correctamente",
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
						  title: "¡La categoría no puede ir vacía o llevar caracteres especiales!",
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
	ELIMINAR CATEGORÍA DE GASTO
	=============================================*/

	static public function ctrEliminarCategoriaGasto(){

		if (isset($_GET["idCategoriaGasto"]) || isset($_POST["idCategoriaGastoEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idCategoriaGastoEliminar"])) {
					return "error_csrf";
				}
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

			$tabla ="categorias_gastos";
			$idCategoriaGasto = isset($_GET["idCategoriaGasto"]) ? $_GET["idCategoriaGasto"] : $_POST["idCategoriaGastoEliminar"];

			// Verificar si hay gastos con esta categoría
			$totalGastos = ModeloCategoriasGastos::mdlContarGastosPorCategoria($idCategoriaGasto);

			if($totalGastos > 0){

				if (isset($_POST["idCategoriaGastoEliminar"])) {
					return "error_gastos_asociados";
				}

				echo'<script>

				swal({
					  type: "error",
					  title: "¡No se puede eliminar la categoría porque tiene '.$totalGastos.' gasto(s) asociado(s)!",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(() => {
								window.location = "gastos";
							})

				</script>';

			}else{

				$respuesta = ModeloCategoriasGastos::mdlEliminarCategoriaGasto($tabla, $idCategoriaGasto);

				if($respuesta == "ok"){

					if (isset($_POST["idCategoriaGastoEliminar"])) {
						return "ok";
					}

					echo'<script>

					swal({
						  type: "success",
						  title: "La categoría ha sido eliminada correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
								window.location = "gastos";
								})

					</script>';

				}

			}

		}

	}

}