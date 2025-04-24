<?php

class ControladorClientes{

	/*=============================================
	CREAR CLIENTES
	=============================================*/

	static public function ctrCrearCliente(){

		if(isset($_POST["nuevoCliente"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoCliente"]) &&
				preg_match('/^[0-9]+$/', $_POST["nuevoDocumentoId"]) &&
				//preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"]) &&
				(empty($_POST["nuevoEmail"]) || preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"])) &&
				preg_match('/^[()\-0-9 ]+$/', $_POST["nuevoTelefono"]) &&
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoDepartamento"]) &&
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoCiudad"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoEstatus"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaNota"]) &&
			    preg_match('/^[#\.\-a-zA-Z0-9 ]+$/', $_POST["nuevaDireccion"])){

				$tabla = "clientes";

				$datos = array("nombre" => $_POST["nuevoCliente"],
							   "documento" => $_POST["nuevoDocumentoId"],
							   "email" => $_POST["nuevoEmail"],
					           "telefono" => $_POST["nuevoTelefono"],
							   "departamento" => $_POST["nuevoDepartamento"],
							   "ciudad" => $_POST["nuevoCiudad"],
					           "direccion" => $_POST["nuevaDireccion"],
							   "estatus" => $_POST["nuevoEstatus"],
							   "notas" => $_POST["nuevaNota"],
				               "fecha_nacimiento" => $_POST["nuevaFechaNacimiento"]);

				$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);


				if ($respuesta == "ok") {

			    	echo '<script>
					swal({
						type: "success",
						title: "!El cliente ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{
							if(result.value){

							   window.location = "clientes";
							}
						})
			     	</script>';
		         }
			}

			else{
				echo '<script>
					swal({
						type: "error",
						title: "!El cliente no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{

							if(result.value){

								window.location = "clientes";
							}
						})
				</script>';
			}


		}


	}

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/

	static public function ctrMostrarClientes($item, $valor){

		$tabla = "clientes";

		$respuesta = ModeloClientes::mdlMostrarClientes($tabla, $item, $valor);

		return $respuesta;
	}



	/*=============================================
	EDITAR CLIENTES
	=============================================*/

	static public function ctrEditarCliente(){

		if(isset($_POST["editarCliente"])){

			/*echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
	*/

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCliente"]) &&
				preg_match('/^[0-9]+$/', $_POST["editarDocumentoId"]) &&
				//preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"]) &&
				(empty($_POST["nuevoEmail"]) || preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"])) &&
				preg_match('/^[()\-0-9 ]+$/', $_POST["editarTelefono"]) &&
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDepartamento"]) &&
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCiudad"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEstatus"]) &&
				//preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNota"]) &&
			    preg_match('/^[#\.\-a-zA-Z0-9 ]+$/', $_POST["editarDireccion"])){

				$tabla = "clientes";

				$datos = array("id" => $_POST["idCliente"],
							   "nombre" => $_POST["editarCliente"],
							   "documento" => $_POST["editarDocumentoId"],
							   "email" => $_POST["editarEmail"],
					           "telefono" => $_POST["editarTelefono"],
							   "departamento" => $_POST["editarDepartamento"],
							   "ciudad" => $_POST["editarCiudad"],
					           "direccion" => $_POST["editarDireccion"],
							   "estatus" => $_POST["editarEstatus"],
							   "notas" => $_POST["editarNota"],
				               "fecha_nacimiento" => $_POST["editarFechaNacimiento"]);


				$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

				if ($respuesta == "ok") {

			    	echo '<script>
					swal({
						type: "success",
						title: "!El cliente ha sido cambiado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{
							if(result.value){

							   window.location = "clientes";
							}
						})
			     	</script>';
		         }
			}

			else{
				echo '<script>
					swal({
						type: "error",
						title: "!El cliente no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false
						}).then((result)=>{

							if(result.value){

								window.location = "clientes";
							}
						})
				</script>';
			}

		}


	}


	/*=============================================
	ELIMINAR CLIENTES
	=============================================*/

	static public function ctrEliminarCliente(){

		if(isset($_GET["idCliente"])){

			$tabla = "clientes";
			$datos = $_GET["idCliente"];

			$respuesta = ModeloClientes::mdlEliminarCliente($tabla, $datos);

			if($respuesta == "ok"){

				echo '<script>
					swal({
						type: "success",
						title: "!El cliente ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "clientes";
							}
						});
				</script>';
			}

		}
	}



}
