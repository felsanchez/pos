<?php

class ControladorProveedores{

	/*=============================================
	CREAR PROVEEDOR
	=============================================*/

	static public function ctrCrearProveedor(){

		if(isset($_POST["nuevoProveedor"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoProveedor"]) &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaMarca"]) &&
                preg_match('/^[0-9]+$/', $_POST["nuevoCelular"])                
                ){

				$tabla = "proveedores";

				$datos = $_POST["nuevoProveedor"];
                
                $datos = array("nombre" => $_POST["nuevoProveedor"],
                               "marca" => $_POST["nuevaMarca"],
                               "celular" => $_POST["nuevoCelular"],
                               "correo" => $_POST["nuevoCorreo"],
                               "direccion" => $_POST["nuevaDireccion"]);
							   
				$respuesta = ModeloProveedores::mdlIngresarProveedor($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
					swal({
						type: "success",
						title: "!El proveedor ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "proveedores";
							}
						});
				</script>';
				}

			}
			else{

				echo '<script>
					swal({
						type: "error",
						title: "!El proveedor no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "proveedores";
							}
						});
				</script>';
			}
		}

	}

	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/

	static public function ctrMostrarProveedores($item, $valor){

		$tabla = "proveedores";

		$respuesta = ModeloProveedores::mdlMostrarProveedores($tabla, $item, $valor);

		return $respuesta;
	}


	/*=============================================
	EDITAR PROVEEDORES
	=============================================*/

	static public function ctrEditarProveedor(){

		if(isset($_POST["editarProveedor"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarProveedor"]) &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarMarca"]) &&
                preg_match('/^[0-9]+$/', $_POST["editarCelular"])                
                ){

				$tabla = "proveedores";

				$datos = array("id" => $_POST["idProveedor"],
                              "nombre" => $_POST["editarProveedor"],
                               "marca" => $_POST["editarMarca"],
                               "celular" => $_POST["editarCelular"],
                               "correo" => $_POST["editarCorreo"],
                               "direccion" => $_POST["editarDireccion"]);

				$respuesta = ModeloProveedores::mdlEditarProveedor($tabla, $datos);

				if($respuesta == "ok"){

					echo '<script>
					swal({
						type: "success",
						title: "!El Proveedor ha sido editado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "proveedores";
							}
						});
				</script>';
				}

			}
			else{

				echo '<script>
					swal({
						type: "error",
						title: "!El Proveedor no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "proveedores";
							}
						});
				</script>';
			}
		}

	}


	/*=============================================
	BORRAR PROVEEDORES
	=============================================*/

	static public function ctrBorrarProveedor() {

		if(isset($_GET["idProveedor"])) {
	
			$tabla = "proveedores";
			$idProveedor = $_GET["idProveedor"];
	
			// Verificar si hay productos asociados a esta proveedores
			$productosAsociados = ModeloProductos::mdlMostrarProductos("productos", "id_proveedor", $idProveedor, "id");
	
			if (!empty($productosAsociados)) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El proveedor tiene productos asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "proveedores";
						}
					});
				</script>';
				return;
			}
	
			$respuesta = ModeloProveedores::mdlBorrarProveedor($tabla, $idProveedor);
	
			if($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "¡El Proveedor ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "proveedores";
						}
					});
				</script>';
			}
		}
	}

	


}
