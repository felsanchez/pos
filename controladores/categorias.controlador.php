<?php

class ControladorCategorias{

	/*=============================================
	CREAR CATEGORIAS
	=============================================*/

	static public function ctrCrearCategoria(){

		if(isset($_POST["nuevaCategoria"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaCategoria"])){

				$tabla = "categorias";

				$datos = $_POST["nuevaCategoria"];

				$respuesta = ModeloCategorias::mdlIngresarCategoria($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
					swal({
						type: "success",
						title: "!La categoría ha sido guardada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "categorias";
							}
						});
				</script>';
				}

			}
			else{

				echo '<script>
					swal({
						type: "error",
						title: "!La categoría no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "categorias";
							}
						});
				</script>';
			}
		}

	}

	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/

	static public function ctrMostrarCategorias($item, $valor){

		$tabla = "categorias";

		$respuesta = ModeloCategorias::mdlMostrarCategorias($tabla, $item, $valor);

		return $respuesta;
	}


	/*=============================================
	EDITAR CATEGORIAS
	=============================================*/

	static public function ctrEditarCategoria(){

		if(isset($_POST["editarCategoria"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCategoria"])){

				$tabla = "categorias";

				$datos = array("categoria"=>$_POST["editarCategoria"],"id"=>$_POST["idCategoria"]);

				$respuesta = ModeloCategorias::mdlEditarCategoria($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
					swal({
						type: "success",
						title: "!La categoría ha sido editada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "categorias";
							}
						});
				</script>';
				}

			}
			else{

				echo '<script>
					swal({
						type: "error",
						title: "!La categoría no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "categorias";
							}
						});
				</script>';
			}
		}

	}


	/*=============================================
	BORRAR CATEGORIAS
	=============================================*/

	static public function ctrBorrarCategoria() {

		if(isset($_GET["idCategoria"])) {
	
			$tabla = "categorias";
			$idCategoria = $_GET["idCategoria"];
	
			// Verificar si hay productos asociados a esta categoría
			$productosAsociados = ModeloProductos::mdlMostrarProductos("productos", "id_categoria", $idCategoria, "id");
	
			if (!empty($productosAsociados)) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "La categoría tiene productos asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "categorias";
						}
					});
				</script>';
				return;
			}
	
			$respuesta = ModeloCategorias::mdlBorrarCategoria($tabla, $idCategoria);
	
			if($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "¡La categoría ha sido borrada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "categorias";
						}
					});
				</script>';
			}
		}
	}


			/*
			if(isset($_GET["idCategoria"])){

				$tabla = "Categorias";
				$datos = $_GET["idCategoria"];

				$respuesta = ModeloCategorias::mdlBorrarCategoria($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
						swal({
							type: "success",
							title: "!La categoría ha sido borrada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar",
							closeOnConfirm: false

							}).then((result)=>{

								if(result.value){

									window.location = "categorias";
								}
							});
					</script>';
				}
			}
				*/

	


}
