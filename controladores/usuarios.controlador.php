<?php

class ControladorUsuarios{

	/*=============================================
	Ingreso de Usuarios
	=============================================*/

	static public function ctrIngresoUsuario()
{
	if (isset($_POST["ingUsuario"])) {

		if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"]) &&
			preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingPassword"])) {

			$tabla = "usuarios";
			$item = "usuario";
			$valor = $_POST["ingUsuario"];

			$respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

			if ($respuesta && 
				isset($respuesta["usuario"], $respuesta["password"]) && 
				$respuesta["usuario"] == $_POST["ingUsuario"] &&
				password_verify($_POST["ingPassword"], $respuesta["password"])) {

				if ($respuesta["estado"] == 1) {

					$_SESSION["iniciarSesion"] = "ok";
					$_SESSION["id"] = $respuesta["id"];
					$_SESSION["nombre"] = $respuesta["nombre"];
					$_SESSION["usuario"] = $respuesta["usuario"];
					$_SESSION["foto"] = $respuesta["foto"];
					$_SESSION["perfil"] = $respuesta["perfil"];

					date_default_timezone_set('America/Bogota');
					$fechaActual = date('Y-m-d H:i:s');

					ModeloUsuarios::mdlActualizarUsuario($tabla, "ultimo_login", $fechaActual, "id", $respuesta["id"]);

					echo '<script>window.location = "inicio";</script>';
				} else {
					echo '<br><div class="alert alert-danger">El usuario aún no está activado</div>';
				}

			} else {
				echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
			}
		}
	}
}


	/*=============================================
	REGISTRO DE USUARIOS
	=============================================*/

	static public function ctrCrearUsuario(){

		if(isset($_POST["nuevoUsuario"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
			  preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
			  preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])) {



				/*=============================================
				VALIDAR IMAGEN
				=============================================*/

				$ruta = "";

				//if(isset($_FILES["nuevaFoto"]["tmp_name"])){
				if(isset($_FILES["nuevaFoto"]["tmp_name"]) && !empty($_FILES["nuevaFoto"]["tmp_name"])){

					list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;

					//CREAMOS DIRECTORIO DE LAS FOTOS DEL USUARIO

					$directorio = "vistas/img/usuarios/".$_POST["nuevoUsuario"];

					mkdir($directorio, 0755);


						//DE A CUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES PHP, 1ro EN JPEG

						if($_FILES["nuevaFoto"]["type"] == "image/jpeg"){

						//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/usuarios/".$_POST["nuevoUsuario"]."/".$aleatorio.".jpeg";

						$origen = imagecreatefromjpeg($_FILES["nuevaFoto"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagejpeg($destino, $ruta);

						}

						//FUNCIONES PARA PNG

						if($_FILES["nuevaFoto"]["type"] == "image/png"){

						//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/usuarios/".$_POST["nuevoUsuario"]."/".$aleatorio.".png";

						$origen = imagecreatefrompng($_FILES["nuevaFoto"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagepng($destino, $ruta);

						}

				 }


			  	$tabla = "usuarios";  

			  	$encriptar = crypt($_POST["nuevoPassword"], '$2a$07$usesomesillystringforsalt$');


				$datos = array("nombre" => $_POST["nuevoNombre"],
							   "usuario" => $_POST["nuevoUsuario"],
							   "password" => $encriptar,
							   "perfil" => $_POST["nuevoPerfil"],
							   "foto" => $ruta);

				$respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);
			 }

			  if ($respuesta == "ok") {

			  	echo '<script>
					swal({
						type: "success",
						title: "!El usuario ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "usuarios";
							}
						});
				</script>';
		     }


			else{

				echo '<script>
					swal({
						type: "error",
						title: "!El usuario no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "usuarios";
							}
						});
				</script>';
			}


		}

	}

	/*=============================================
	Mostrar Usuarios
	=============================================*/

	static public function ctrMostrarUsuarios($item, $valor){

		$tabla = "usuarios";
		$respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

		return $respuesta;
	}


	/*=============================================
	EDITAR USUARIOS
	=============================================*/

	static public function ctrEditarUsuario(){

		if(isset($_POST["editarUsuario"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"])) {

				/*=============================================
				VALIDAR IMAGEN
				=============================================*/

				$ruta = $_POST["fotoActual"];

				if(isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])){

					list($ancho, $alto) = getimagesize($_FILES["editarFoto"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;

					//CREAMOS DIRECTORIO DE LAS FOTOS DEL USUARIO

					$directorio = "vistas/img/usuarios/".$_POST["editarUsuario"];

					//PRIMERO PREGUNTAMOS SI EXISTE UNA IMAGEN EN LA BD

					if(!empty($_POST["fotoActual"])){

						unlink($_POST["fotoActual"]);
					} else{

						mkdir($directorio, 0755);
					}


						//DE A CUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES PHP, 1ro EN JPEG

						if($_FILES["editarFoto"]["type"] == "image/jpeg"){

							//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

							$aleatorio = mt_rand(100, 999);

							$ruta = "vistas/img/usuarios/".$_POST["editarUsuario"]."/".$aleatorio.".jpeg";

							$origen = imagecreatefromjpeg($_FILES["editarFoto"]["tmp_name"]);

							$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

							imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

							imagejpeg($destino, $ruta);

						}

						//FUNCIONES PARA PNG

						if($_FILES["editarFoto"]["type"] == "image/png"){

							//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

							$aleatorio = mt_rand(100, 999);

							$ruta = "vistas/img/usuarios/".$_POST["editarUsuario"]."/".$aleatorio.".png";

							$origen = imagecreatefrompng($_FILES["editarFoto"]["tmp_name"]);

							$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

							imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

							imagepng($destino, $ruta);

						}
				 }

				 $tabla = "usuarios";

				 if ($_POST["editarPassword"] != "") {

				 	if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])) {

				 		$encriptar = crypt($_POST["editarPassword"], '$2a$07$usesomesillystringforsalt$');
				 	} 
				 	else{

				 		echo '<script>
					swal({
						type: "error",
						title: "!El usuario no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "usuarios";
							}
						});
				</script>';

				 	}


				 }
				 else{

				 	$encriptar = $passwordActual;
				 }

				 $datos = array("nombre" => $_POST["editarNombre"],
							   "usuario" => $_POST["editarUsuario"],
							   "password" => $encriptar,
							   "perfil" => $_POST["editarPerfil"],
							   "foto" => $ruta);

				 $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

				 if($respuesta == "ok"){

				 	echo '<script>
					swal({
						type: "success",
						title: "!El usuario ha sido editado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "usuarios";
							}
						});
				</script>';

				 }


			 }


			 else{
				 	echo '<script>
					swal({
						type: "error",
						title: "!El nombre no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "usuarios";
							}
						});
				</script>';

				 	}

		}
		

	}

	/*=============================================
	BORRAR USUARIOS
	=============================================*/

	static public function ctrBorrarUsuario(){

		if(isset($_GET["idUsuario"])){


			// ❗ Validar que no elimine su propio usuario
			if ($_GET["idUsuario"] == $_SESSION["id"]) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No puedes eliminar tu propio usuario!",
						text: "Cierra la sesión e inicia con otro usuario para poder eliminar este.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}

			$tabla = "usuarios";
			$datos = $_GET["idUsuario"];

			if($_GET["fotoUsuario"] != ""){

				unlink($_GET["fotoUsuario"]);
				rmdir('vistas/img/usuarios/'.$_GET["usuario"]);
			}


			// Verificar si hay actividades asociados
			$actividadesAsociados = ModeloActividades::mdlMostrarActividades("actividades", "id_user", $datos, "id");
	
			if (!empty($actividadesAsociados)) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El usuario tiene actividades asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}


			// Verificar si hay ventas asociados
			$ventasAsociados = ModeloVentas::mdlMostrarVentas("ventas", "id_vendedor", $datos, "id");
	
			if (!empty($ventasAsociados)) {
				echo '<script>
					swal({
						type: "error",
						title: "¡No se puede eliminar!",
						text: "El usuario tiene ventas asociadas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result) => {
						if (result.value) {
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}


			$respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

			if($respuesta == "ok"){

				 	echo '<script>
					swal({
						type: "success",
						title: "!El usuario ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar",
						closeOnConfirm: false

						}).then((result)=>{

							if(result.value){

								window.location = "usuarios";
							}
						})
				</script>';

				 }
		}

	}

}
