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
						  title: "¡La categoría no puede ir vacía o llevar caracteres especiales!",
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
	EDITAR CATEGORÍA DE GASTO
	=============================================*/

	static public function ctrEditarCategoriaGasto(){

		if(isset($_POST["editarNombreCategoriaGasto"])){

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
						  title: "¡La categoría no puede ir vacía o llevar caracteres especiales!",
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
	ELIMINAR CATEGORÍA DE GASTO
	=============================================*/

	static public function ctrEliminarCategoriaGasto(){

		if(isset($_GET["idCategoriaGasto"])){

			$tabla ="categorias_gastos";
			$datos = $_GET["idCategoriaGasto"];

			// Verificar si hay gastos con esta categoría
			$totalGastos = ModeloCategoriasGastos::mdlContarGastosPorCategoria($datos);

			if($totalGastos > 0){

				echo'<script>

				swal({
					  type: "error",
					  title: "¡No se puede eliminar la categoría porque tiene '.$totalGastos.' gasto(s) asociado(s)!",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "gastos";

								}
							})

				</script>';

			}else{

				$respuesta = ModeloCategoriasGastos::mdlEliminarCategoriaGasto($tabla, $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "La categoría ha sido eliminada correctamente",
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

	}

}