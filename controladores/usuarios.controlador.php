<?php

class ControladorUsuarios{

	/*=============================================
	Ingreso de Usuarios
	=============================================*/


	static public function ctrIngresoUsuario(){

		if(isset($_POST["ingUsuario"])){

			if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"]) &&
			   preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingPassword"])) {


			   	//$encriptar = crypt($_POST["ingPassword"], '$2a$07$usesomesillystringforsalt$');

			   	$encriptar = password_hash($_POST["ingPassword"], PASSWORD_BCRYPT);

			   	$tabla = "usuarios";

			    $item = "usuario";
			    $valor = $_POST["ingUsuario"];

			    $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

			    if($respuesta["usuario"] == $_POST["ingUsuario"] && password_verify($respuesta["password"], $encriptar)){

			    	if($respuesta["estado"] == 1){

					    $_SESSION["iniciarSesion"] = "ok";
					    $_SESSION["id"] = $respuesta["id"];
				    	$_SESSION["nombre"] = $respuesta["nombre"];
				    	$_SESSION["usuario"] = $respuesta["usuario"];
				    	$_SESSION["foto"] = $respuesta["foto"];
				    	$_SESSION["perfil"] = $respuesta["perfil"];

				    	/*=============================================
						REGISTRAR FECHA PARA SABER EL ULTIMO LOGIN
						=============================================*/

						date_default_timezone_set('America/Bogota');

						$fecha = date('Y-m-d');
						$hora = date('H:i:s');

						$fechaActual = $fecha.' '.$hora;

						$item1 = "ultimo_login";
						$valor1 = $fechaActual;

						$item2 = "id";
						$valor2 = $respuesta["id"];

						$ultimo_login = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

						if($ultimo_login == "ok"){

							echo '<script>

								window.location = "inicio";
									
					    	</script>';
						}

					    	
				    }
				    else{

			    		echo '<br><div class="alert alert-danger">El usuario aún no esta activado</div>';
			         }

			    }

			    else{

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

			$tabla = "usuarios";
			$datos = $_GET["idUsuario"];

			if($_GET["fotoUsuario"] != ""){

				unlink($_GET["fotoUsuario"]);
				rmdir('vistas/img/usuarios/'.$_GET["usuario"]);
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
