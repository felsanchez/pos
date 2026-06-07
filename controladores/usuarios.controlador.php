<?php

class ControladorUsuarios
{

	/*=============================================
	Ingreso de Usuarios
	=============================================*/

	public static function ctrIngresoUsuario()
	{

		if (isset($_POST["ingUsuario"])) {

			// Obtener IP del usuario
			$ip = $_SERVER['REMOTE_ADDR'];

			// VERIFICAR RATE LIMITING
			require_once "modelos/rate-limiter.php";

			if (RateLimiter::isBlocked($ip)) {
				$remaining = RateLimiter::getRemainingTime($ip);

				echo '<br><div class="alert alert-warning">
						<i class="fa fa-exclamation-triangle"></i>
						<strong>Demasiados intentos fallidos.</strong><br>
						Por seguridad, tu IP ha sido bloqueada temporalmente.<br>
						Intenta nuevamente en <strong>' . $remaining . ' minutos</strong>.
					  </div>';
				return;
			}

			// Validar CSRF
			if (!CSRF::validateToken()) {
				echo '<br><div class="alert alert-danger">Token de seguridad inválido</div>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"])) {

				$tabla = "usuarios";
				$item = "usuario";
				$valor = $_POST["ingUsuario"];

				$respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

				if (
					$respuesta &&
					isset($respuesta["usuario"], $respuesta["password"]) &&
					$respuesta["usuario"] == $_POST["ingUsuario"]
				) {

					$loginExitoso = false;
					$passwordMigrada = false;

					// Intentar primero con password_verify (método nuevo y seguro)
					if (password_verify($_POST["ingPassword"], $respuesta["password"])) {
						$loginExitoso = true;
					}
					// Si falla, intentar con crypt (método antiguo - compatibilidad temporal)
					else if ($respuesta["password"] === crypt($_POST["ingPassword"], '$2a$07$usesomesillystringforsalt$')) {
						$loginExitoso = true;
						$passwordMigrada = true;

						// MIGRAR AUTOMÁTICAMENTE a método seguro
						$nuevaPassword = password_hash($_POST["ingPassword"], PASSWORD_BCRYPT, ['cost' => 12]);
						ModeloUsuarios::mdlActualizarUsuario($tabla, "password", $nuevaPassword, "id", $respuesta["id"]);
					}

					if ($loginExitoso) {

						if ($respuesta["estado"] == 1) {

							// 🔹 Verificar si la sucursal del usuario está activa
							require_once "modelos/bodegas.modelo.php";
							$idBodegaLogueado = !empty($respuesta["id_bodega"]) ? $respuesta["id_bodega"] : 1;
							$bodegaUsuario = ModeloBodegas::mdlMostrarBodegas("bodegas", "id", $idBodegaLogueado);
							if ($bodegaUsuario && $bodegaUsuario["estado"] == 0) {
								echo '<br><div class="alert alert-danger">
										<i class="fa fa-exclamation-triangle"></i>
										<strong>Acceso Denegado.</strong><br>
										La sucursal asociada a tu usuario está temporalmente desactivada. Contacta al administrador.
									  </div>';
								return;
							}

							// LOGIN EXITOSO - Limpiar intentos fallidos
							RateLimiter::clearAttempts($ip);

							$_SESSION["iniciarSesion"] = "ok";
							$_SESSION["id"] = $respuesta["id"];
							$_SESSION["nombre"] = $respuesta["nombre"];
							$_SESSION["usuario"] = $respuesta["usuario"];
							$_SESSION["foto"] = $respuesta["foto"];
							$_SESSION["perfil"] = $respuesta["perfil"];
							$_SESSION["email"] = $respuesta["email"];
							$_SESSION["id_bodega"] = $idBodegaLogueado;

							// Cargar permisos del perfil en sesión (una sola consulta al login)
							$_SESSION["permisos"] = ModeloPerfiles::mdlCargarPermisosEnSesion($respuesta["perfil"]);

							// Regenerar ID de sesión después del login (previene fijación de sesión)
							SessionManager::regenerate();

							date_default_timezone_set('America/Bogota');
							$fechaActual = date('Y-m-d H:i:s');

							ModeloUsuarios::mdlActualizarUsuario($tabla, "ultimo_login", $fechaActual, "id", $respuesta["id"]);

							echo '<script>window.location = "inicio";</script>';
						} else {
							echo '<br><div class="alert alert-danger">El usuario aún no está activado</div>';
						}

					} else {
						// LOGIN FALLIDO - Registrar intento
						RateLimiter::recordAttempt($ip, $_POST["ingUsuario"]);

						$attempts = RateLimiter::getAttempts($ip);
						$remaining = RateLimiter::MAX_ATTEMPTS - $attempts;

						if ($remaining > 0) {
							echo '<br><div class="alert alert-danger">
									<i class="fa fa-times-circle"></i>
									<strong>Error al ingresar.</strong><br>
									Usuario y/o contraseña incorrectos.<br>
									<small>Intentos restantes: <strong>' . $remaining . '</strong></small>
								  </div>';
						} else {
							echo '<br><div class="alert alert-warning">
									<i class="fa fa-exclamation-triangle"></i>
									<strong>Demasiados intentos fallidos.</strong><br>
									Tu IP ha sido bloqueada temporalmente por 15 minutos.
								  </div>';
						}
					}

				} else {
					// Usuario no existe - Registrar intento
					RateLimiter::recordAttempt($ip, $_POST["ingUsuario"]);

					$attempts = RateLimiter::getAttempts($ip);
					$remaining = RateLimiter::MAX_ATTEMPTS - $attempts;

					if ($remaining > 0) {
						echo '<br><div class="alert alert-danger">
								<i class="fa fa-times-circle"></i>
								<strong>Error al ingresar.</strong><br>
								Usuario y/o contraseña incorrectos.<br>
								<small>Intentos restantes: <strong>' . $remaining . '</strong></small>
							  </div>';
					}
				}
			}
		}
	}


	/*=============================================
	REGISTRO DE USUARIOS
	=============================================*/

	static public function ctrCrearUsuario()
	{

		if (isset($_POST["nuevoUsuario"])) {

			// VALIDAR TOKEN CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token inválido. Recarga la página e intenta nuevamente.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "usuarios";
					});
				</script>';
				return;
			}

			// 1. Validar nombre completo
			if (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Nombre inválido!",
						text: "El nombre completo no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}

			// 2. Validar nombre de usuario (Login)
			if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Usuario inválido!",
						text: "El nombre de usuario no puede llevar espacios ni caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}

			// 3. Validar correo electrónico
			if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["nuevoEmail"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Correo inválido!",
						text: "El formato de correo electrónico ingresado no es válido.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}

			// 4. Validar contraseña
			if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Contraseña inválida!",
						text: "La contraseña no puede contener espacios ni caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "usuarios";
						}
					});
				</script>';
				return;
			}

			/*=============================================
			VALIDAR IMAGEN
			=============================================*/

			$ruta = "";

			if (isset($_FILES["nuevaFoto"]["tmp_name"]) && !empty($_FILES["nuevaFoto"]["tmp_name"])) {

				list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);

				$nuevoAncho = 500;
				$nuevoAlto = 500;

				//CREAMOS DIRECTORIO DE LAS FOTOS DEL USUARIO

				$directorio = "vistas/img/usuarios/" . $_POST["nuevoUsuario"];

				mkdir($directorio, 0755);

				//DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES PHP, 1ro EN JPEG

				if ($_FILES["nuevaFoto"]["type"] == "image/jpeg") {

					//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

					$aleatorio = mt_rand(100, 999);

					$ruta = "vistas/img/usuarios/" . $_POST["nuevoUsuario"] . "/" . $aleatorio . ".jpeg";

					$origen = imagecreatefromjpeg($_FILES["nuevaFoto"]["tmp_name"]);

					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

					imagejpeg($destino, $ruta);

				}

				//FUNCIONES PARA PNG

				if ($_FILES["nuevaFoto"]["type"] == "image/png") {

					//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

					$aleatorio = mt_rand(100, 999);

					$ruta = "vistas/img/usuarios/" . $_POST["nuevoUsuario"] . "/" . $aleatorio . ".png";

					$origen = imagecreatefrompng($_FILES["nuevaFoto"]["tmp_name"]);

					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

					imagepng($destino, $ruta);

				}

			}


			$tabla = "usuarios";

			// Usar password_hash con bcrypt y factor de costo 12 (seguro)
			$encriptar = password_hash($_POST["nuevoPassword"], PASSWORD_BCRYPT, ['cost' => 12]);


			$datos = array(
				"nombre" => $_POST["nuevoNombre"],
				"usuario" => $_POST["nuevoUsuario"],
				"password" => $encriptar,
				"perfil" => $_POST["nuevoPerfil"],
				"foto" => $ruta,
				"email" => $_POST["nuevoEmail"],
				"id_bodega" => (!empty($_POST["nuevoIdBodega"]) && is_numeric($_POST["nuevoIdBodega"])) ? intval($_POST["nuevoIdBodega"]) : 1,
				"estado" => 1
			);

			$respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);

			if ($respuesta == "ok") {

				echo '<script>
					swal({
						type: "success",
						title: "¡El usuario ha sido guardado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "usuarios";
						});
				</script>';
			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡Error al guardar el usuario!",
						text: "Por favor intenta nuevamente.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "usuarios";
						});
				</script>';
			}

		}

	}

	/*=============================================
	Mostrar Usuarios
	=============================================*/

	static public function ctrMostrarUsuarios($item, $valor)
	{

		$tabla = "usuarios";
		$respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

		if ($item === "id" && $respuesta === false) {
			return array(
				"id" => null,
				"nombre" => "Usuario Eliminado",
				"usuario" => "usuario_eliminado",
				"perfil" => "Ninguno",
				"foto" => "vistas/img/usuarios/default/anonymous.png",
				"estado" => 0
			);
		}

		return $respuesta;
	}

	/*=============================================
	MOSTRAR USUARIOS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarUsuariosServerSide($params)
	{
		$tabla = "usuarios";

		// Columnas para ordenar
		$columns = array(
			0 => 'u.usuario',
			1 => 'u.nombre',
			2 => 'u.email',
			3 => 'u.foto',
			4 => 'u.perfil',
			5 => 'b.nombre',
			6 => 'u.estado',
			7 => 'u.ultimo_login',
			9 => 'u.id'
		);

		$where = " WHERE 1=1 ";
		
		// Omitir el usuario logueado actualmente
		if(isset($_SESSION["usuario"])){
			$where .= " AND u.usuario != '".$_SESSION["usuario"]."' ";
		}

		// Filtro de búsqueda (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (u.usuario LIKE '%$searchValue%' OR u.nombre LIKE '%$searchValue%' OR u.email LIKE '%$searchValue%' OR u.perfil LIKE '%$searchValue%' OR b.nombre LIKE '%$searchValue%') ";
		}

		// Filtro por Perfil (Personalizado)
		if (!empty($params['perfilFiltro'])) {
			$perfilFiltro = $params['perfilFiltro'];
			$where .= " AND u.perfil = '$perfilFiltro' ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$order = " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$usuarios = ModeloUsuarios::mdlMostrarUsuariosServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloUsuarios::mdlGetTotalUsuarios($tabla, " WHERE 1=1 " . (isset($_SESSION["usuario"]) ? " AND u.usuario != '".$_SESSION["usuario"]."' " : ""));
		$totalFiltered = ModeloUsuarios::mdlGetTotalUsuarios($tabla, $where);

		$data = array();

		foreach ($usuarios as $key => $value) {
			
			$nestedData = array();

			$nestedData[] = e($value["usuario"]);
			$nestedData[] = e($value["nombre"]);
			$nestedData[] = e($value["email"]);

			// Foto
			if ($value["foto"] != "") {
				$fotoHtml = '<img src="' . $value["foto"] . '" class="img-thumbnail img-usuario-clickeable" width="40px" style="cursor: pointer;" data-foto="' . $value["foto"] . '" data-idusuario="' . $value["id"] . '" data-usuario="' . $value["usuario"] . '">';
			} else {
				$fotoHtml = '<img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail img-usuario-clickeable" width="40px" style="cursor: pointer;" data-foto="vistas/img/usuarios/default/anonymous.png" data-idusuario="' . $value["id"] . '" data-usuario="' . $value["usuario"] . '">';
			}
			$nestedData[] = $fotoHtml;

			$nestedData[] = e($value["perfil"]);

			// Sucursal
			$nestedData[] = $value["sucursal"] != "" ? e($value["sucursal"]) : '<span class="label label-default">Sin asignar</span>';

			// Estado
			$estadoHtml = "";
			if (puedeAccion('usuarios', 'editar')) {
				if ($value["estado"] != 0) {
					$estadoHtml = '<button class="btn btn-success btn-xs btnActivar" idUsuario="' . $value["id"] . '" estadoUsuario="0">Activado</button>';
				} else {
					$estadoHtml = '<button class="btn btn-danger btn-xs btnActivar" idUsuario="' . $value["id"] . '" estadoUsuario="1">Desactivado</button>';
				}
			} else {
				if ($value["estado"] != 0) {
					$estadoHtml = '<button class="btn btn-success btn-xs">Activado</button>';
				} else {
					$estadoHtml = '<button class="btn btn-danger btn-xs">Desactivado</button>';
				}
			}
			$nestedData[] = $estadoHtml;

			$nestedData[] = $value["ultimo_login"];

			// Acciones
			$accionesHtml = '<div class="btn-group">';
			if (puedeAccion('usuarios', 'editar')) {
				$accionesHtml .= '<button class="btn btn-warning btnEditarUsuario" idUsuario="' . $value["id"] . '" title="Editar usuario"><i class="fa fa-pencil"></i></button>';
			} else {
				$accionesHtml .= '<button class="btn btn-warning" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para editar"><i class="fa fa-pencil"></i></button>';
			}
			if (puedeAccion('usuarios', 'eliminar')) {
				$accionesHtml .= '<button class="btn btn-danger btnEliminarUsuario" idUsuario="' . $value["id"] . '" fotoUsuario="' . $value["foto"] . '" usuario="' . $value["usuario"] . '" title="Eliminar usuario"><i class="fa fa-times"></i></button>';
			} else {
				$accionesHtml .= '<button class="btn btn-danger" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para eliminar"><i class="fa fa-times"></i></button>';
			}
			$accionesHtml .= '</div>';

			$nestedData[] = $accionesHtml;
			$nestedData[] = $value["id"];
			
			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		return $json_data;
	}


	/*=============================================
	EDITAR USUARIOS
	=============================================*/

	static public function ctrEditarUsuario()
	{

		if (isset($_POST["editarUsuario"])) {

			// VALIDAR TOKEN CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token inválido. Recarga la página e intenta nuevamente.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "usuarios";
					});
				</script>';
				return;
			}

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"]) &&
				preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarUsuario"]) &&
				preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["editarEmail"])
			) {

				$tabla = "usuarios";
				$usuarioActual = ModeloUsuarios::MdlMostrarUsuarios($tabla, "id", $_POST["idUsuario"]);
				$ruta = $_POST["fotoActual"];

				// Si el login cambió, validar duplicado y renombrar carpeta de fotos
				if ($_POST["editarUsuario"] !== $usuarioActual["usuario"]) {
					$checkExiste = ModeloUsuarios::MdlMostrarUsuarios($tabla, "usuario", $_POST["editarUsuario"]);
					if ($checkExiste) {
						echo '<script>
							swal({
								type: "error",
								title: "Error",
								text: "El nombre de usuario (Login) ya está en uso.",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(() => {
								window.location = "usuarios";
							});
						</script>';
						return;
					}

					$dirAntiguo = "vistas/img/usuarios/" . $usuarioActual["usuario"];
					$dirNuevo = "vistas/img/usuarios/" . $_POST["editarUsuario"];
					if (is_dir($dirAntiguo)) {
						rename($dirAntiguo, $dirNuevo);
						if (!empty($ruta) && strpos($ruta, $dirAntiguo) === 0) {
							$ruta = str_replace($dirAntiguo, $dirNuevo, $ruta);
						}
					}
				}

				/*=============================================
				VALIDAR IMAGEN
				=============================================*/

				if (isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])) {

					list($ancho, $alto) = getimagesize($_FILES["editarFoto"]["tmp_name"]);

					$nuevoAncho = 500;
					$nuevoAlto = 500;

					//CREAMOS DIRECTORIO DE LAS FOTOS DEL USUARIO

					$directorio = "vistas/img/usuarios/" . $_POST["editarUsuario"];

					if (!file_exists($directorio)) {
						mkdir($directorio, 0755, true);
					}

					//DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES PHP, 1ro EN JPEG

					if ($_FILES["editarFoto"]["type"] == "image/jpeg") {

						//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/usuarios/" . $_POST["editarUsuario"] . "/" . $aleatorio . ".jpeg";

						$origen = imagecreatefromjpeg($_FILES["editarFoto"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagejpeg($destino, $ruta);

					}

					//FUNCIONES PARA PNG

					if ($_FILES["editarFoto"]["type"] == "image/png") {

						//GUARDAMOS LA IMAGEN EN EL DIRECTORIO

						$aleatorio = mt_rand(100, 999);

						$ruta = "vistas/img/usuarios/" . $_POST["editarUsuario"] . "/" . $aleatorio . ".png";

						$origen = imagecreatefrompng($_FILES["editarFoto"]["tmp_name"]);

						$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

						imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

						imagepng($destino, $ruta);

					}
				}

				$fotoActualAEliminar = (!empty($_POST["fotoActual"]) && $ruta !== $_POST["fotoActual"]) ? $_POST["fotoActual"] : null;

				if (isset($_POST["editarPassword"]) && $_POST["editarPassword"] != "") {

					if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])) {

						// Usar password_hash con bcrypt y factor de costo 12 (seguro)
						$encriptar = password_hash($_POST["editarPassword"], PASSWORD_BCRYPT, ['cost' => 12]);
					} else {

						echo '<script>
					swal({
						type: "error",
						title: "¡La contraseña no puede llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "usuarios";
						});
				</script>';
						return;

					}

				} else {

					// Si no se cambia la contraseña, mantener la actual
					$encriptar = $usuarioActual["password"];
				}

				$datos = array(
					"id" => $_POST["idUsuario"],
					"nombre" => $_POST["editarNombre"],
					"usuario" => $_POST["editarUsuario"],
					"password" => $encriptar,
					"perfil" => $_POST["editarPerfil"],
					"foto" => $ruta,
					"email" => $_POST["editarEmail"],
					"id_bodega" => (!empty($_POST["editarIdBodega"]) && is_numeric($_POST["editarIdBodega"])) ? intval($_POST["editarIdBodega"]) : 1
				);

				/*=============================================
				TRANSACCIÓN PDO: EDITAR USUARIO
				=============================================*/
				$db = Conexion::conectar();
				try {
					$db->beginTransaction();

					$respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);
					if ($respuesta != "ok") {
						throw new Exception("Error al actualizar el usuario en la base de datos.");
					}

					$db->commit();

					// Eliminar foto anterior DESPUÉS del commit para evitar pérdida si falla la BD
					if ($fotoActualAEliminar && file_exists($fotoActualAEliminar)) {
						unlink($fotoActualAEliminar);
					}

					echo '<script>
					swal({
						type: "success",
						title: "¡El usuario ha sido editado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "usuarios";
						});
				</script>';

				} catch (Exception $e) {
					$db->rollBack();
					$mensajeError = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
					echo '<script>
						swal({
							type: "error",
							title: "Error al editar el usuario",
							text: "' . $mensajeError . '",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "usuarios";
						})
					</script>';
				}

			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡El nombre o usuario no puede ir vacío o llevar caracteres especiales!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "usuarios";
						});
				</script>';

			}

		}


	}

	/*=============================================
	BORRAR USUARIOS
	=============================================*/

	static public function ctrBorrarUsuario()
	{

		if (isset($_GET["idUsuario"]) || isset($_POST["idUsuarioEliminar"])) {

			/*=============================================
			VALIDAR CSRF (Solo si es POST)
			=============================================*/
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && !CSRF::validateToken()) {
				if (isset($_POST["idUsuarioEliminar"])) {
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
						window.location = "usuarios";
					})
				</script>';
				return;
			}

			$idUsuario = isset($_GET["idUsuario"]) ? $_GET["idUsuario"] : $_POST["idUsuarioEliminar"];

			// ❗ Validar que no elimine su propio usuario
			if ($idUsuario == $_SESSION["id"]) {
				if (isset($_POST["idUsuarioEliminar"])) {
					return "error_auto_eliminacion";
				}
				echo '<script>
					swal({
						type: "error",
						title: "¡No puedes eliminar tu propio usuario!",
						text: "Cierra la sesión e inicia con otro usuario para poder eliminar este.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "usuarios";
					});
				</script>';
				return;
			}

			$tabla = "usuarios";

			// Obtener información del usuario desde la DB para validar rutas y dependencias
			$usuario = ModeloUsuarios::mdlMostrarUsuarios($tabla, "id", $idUsuario);

			if (!$usuario) {
				if (isset($_POST["idUsuarioEliminar"])) {
					return "error_no_existe";
				}
				return;
			}

			$foto = $usuario["foto"];
			$usrName = $usuario["usuario"];








			/*=============================================
			TRANSACCIÓN PDO: BORRAR USUARIO
			=============================================*/
			$db = Conexion::conectar();
			try {
				$db->beginTransaction();

				$respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $idUsuario);
				if ($respuesta != "ok") {
					throw new Exception("Error al eliminar el usuario de la base de datos.");
				}

				$db->commit();

				// Borrar foto y directorio DESPUÉS del commit para evitar pérdida si falla la BD
				if ($foto != "" && $foto != "vistas/img/usuarios/default/anonymous.png") {
					if (file_exists($foto)) {
						unlink($foto);
					}
					$dir = 'vistas/img/usuarios/' . $usrName;
					if (is_dir($dir)) {
						rmdir($dir);
					}
				}

				if (isset($_POST["idUsuarioEliminar"])) {
					return "ok";
				}
				echo '<script>
					swal({
						type: "success",
						title: "¡El usuario ha sido borrado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"

						}).then(() => {
							window.location = "usuarios";
						});
				</script>';

			} catch (Exception $e) {
				$db->rollBack();
				$mensajeError = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
				if (isset($_POST["idUsuarioEliminar"])) {
					return "error: " . $mensajeError;
				}
				echo '<script>
					swal({
						type: "error",
						title: "Error al borrar el usuario",
						text: "' . $mensajeError . '",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "usuarios";
					})
				</script>';
			}
		}

	}


	/*=============================================
	REGISTRO DE USUARIO DESDE LOGIN
	=============================================*/

	static public function ctrRegistroUsuario()
	{

		if (isset($_POST["registroNombre"])) {

			// VALIDAR TOKEN CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token inválido. Recarga la página e intenta nuevamente.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "login";
					});
				</script>';
				return;
			}

			// 1. Validar nombre completo
			if (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["registroNombre"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Nombre inválido!",
						text: "El nombre completo no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "login";
						}
					});
				</script>';
				return;
			}

			// 2. Validar nombre de usuario (Login)
			if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST["registroUsuario"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Usuario inválido!",
						text: "El nombre de usuario no puede llevar espacios ni caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "login";
						}
					});
				</script>';
				return;
			}

			// 3. Validar correo electrónico
			if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["registroEmail"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Correo inválido!",
						text: "El formato de correo electrónico ingresado no es válido.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "login";
						}
					});
				</script>';
				return;
			}

			// 4. Validar contraseña
			if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST["registroPassword"])) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Contraseña inválida!",
						text: "La contraseña no puede contener espacios ni caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "login";
						}
					});
				</script>';
				return;
			}

			// Verificar si el usuario ya existe
			$tabla = "usuarios";
			$item = "usuario";
			$valor = $_POST["registroUsuario"];
			$usuarioExiste = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

			if ($usuarioExiste) {
				echo '<script>
					swal({
						type: "error",
						title: "¡El usuario ya existe!",
						text: "Por favor elige otro nombre de usuario.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "login";
					});
				</script>';
				return;
			}

			// Usar password_hash con bcrypt y factor de costo 12 (seguro)
			$encriptar = password_hash($_POST["registroPassword"], PASSWORD_BCRYPT, ['cost' => 12]);

			// Datos del nuevo usuario
			$datos = array(
				"nombre" => $_POST["registroNombre"],
				"usuario" => $_POST["registroUsuario"],
				"password" => $encriptar,
				"perfil" => "Administrador",
				"foto" => "",
				"email" => $_POST["registroEmail"],
				"id_bodega" => 1,
				"estado" => 0
			);

			$respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);

			if ($respuesta == "ok") {

				echo '<script>
					swal({
						type: "success",
						title: "¡Registro exitoso!",
						text: "Ya puedes ingresar al sistema con tu usuario y contraseña.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "login";
					});
				</script>';

			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error al registrar!",
						text: "Por favor intenta nuevamente.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "login";
					});
				</script>';
			}

		}

	}


	/*=============================================
	ACTUALIZAR PERFIL DE USUARIO ACTUAL
	=============================================*/

	static public function ctrActualizarPerfil()
	{

		if (isset($_POST["actualizarPerfil"])) {

			// VALIDAR TOKEN CSRF
			if (!CSRF::validateToken()) {
				echo json_encode(array(
					"status" => "error",
					"message" => "Token de seguridad inválido"
				));
				return;
			}

			// Validar que el usuario esté autenticado
			if (!isset($_SESSION["id"])) {
				echo json_encode(array(
					"status" => "error",
					"message" => "No hay sesión activa"
				));
				return;
			}

			// Validar nombre
			if (!preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["perfilNombre"])) {
				echo json_encode(array(
					"status" => "error",
					"message" => "El nombre no puede llevar caracteres especiales"
				));
				return;
			}

			// Validar email
			if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["perfilEmail"])) {
				echo json_encode(array(
					"status" => "error",
					"message" => "El formato del correo electrónico no es correcto"
				));
				return;
			}

			$tabla = "usuarios";
			$passwordEncriptada = "";

			// Si se proporciona nueva contraseña, validarla y encriptarla
			if (!empty($_POST["perfilPassword"])) {

				// Validar longitud mínima
				if (strlen($_POST["perfilPassword"]) < 6) {
					echo json_encode(array(
						"status" => "error",
						"message" => "La contraseña debe tener al menos 6 caracteres"
					));
					return;
				}

				// Validar que no tenga caracteres especiales
				if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST["perfilPassword"])) {
					echo json_encode(array(
						"status" => "error",
						"message" => "La contraseña no puede llevar caracteres especiales"
					));
					return;
				}

				// Validar que las contraseñas coincidan
				if ($_POST["perfilPassword"] !== $_POST["perfilPasswordConfirm"]) {
					echo json_encode(array(
						"status" => "error",
						"message" => "Las contraseñas no coinciden"
					));
					return;
				}

				// Encriptar contraseña
				$passwordEncriptada = password_hash($_POST["perfilPassword"], PASSWORD_BCRYPT, ['cost' => 12]);
			}

			// Preparar datos
			$datos = array(
				"id" => $_SESSION["id"],
				"nombre" => $_POST["perfilNombre"],
				"email" => $_POST["perfilEmail"],
				"password" => $passwordEncriptada
			);

			// Actualizar en base de datos
			$respuesta = ModeloUsuarios::mdlActualizarPerfil($tabla, $datos);

			if ($respuesta == "ok") {

				// Actualizar variable de sesión del nombre y email
				$_SESSION["nombre"] = $_POST["perfilNombre"];
				$_SESSION["email"] = $_POST["perfilEmail"];

				echo json_encode(array(
					"status" => "success",
					"message" => "Perfil actualizado correctamente",
					"nuevoNombre" => $_POST["perfilNombre"]
				));

			} else {

				echo json_encode(array(
					"status" => "error",
					"message" => "Error al actualizar el perfil"
				));

			}

		}

	}

	/*=============================================
	RESTABLECER CONTRASEÑA
	=============================================*/
	static public function ctrRestablecerPassword()
	{
		if (isset($_POST["resetEmail"])) {

			if (preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST["resetEmail"])) {

				$tabla = "usuarios";
				$email = $_POST["resetEmail"];

				$usuario = ModeloUsuarios::mdlMostrarUsuarioPorEmail($tabla, $email);

				if ($usuario) {

					// Generar nueva contraseña aleatoria
					$password = substr(md5(microtime()), rand(0, 26), 8);
					$passwordEncriptada = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

					$respuesta = ModeloUsuarios::mdlActualizarUsuario($tabla, "password", $passwordEncriptada, "id", $usuario["id"]);

					if ($respuesta == "ok") {

						// Enviar correo
						$asunto = "Restablecer Password - Sistema POS";
						$mensaje = '
						<div style="background-color: #f4f4f4; padding: 20px; font-family: Helvetica, Arial, sans-serif;">
							<div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
								<h1 style="color: #333; text-align: center;">Recuperación de Contraseña</h1>
								<p style="color: #666; font-size: 16px;">Hola <strong>' . $usuario["nombre"] . '</strong>,</p>
								<p style="color: #666; font-size: 16px;">Has solicitado restablecer tu contraseña. Tu nueva contraseña temporal es:</p>
								<div style="background-color: #f8f9fa; padding: 15px; text-align: center; border-radius: 4px; margin: 20px 0;">
									<h2 style="color: #007bff; margin: 0; letter-spacing: 2px;">' . $password . '</h2>
								</div>
								<p style="color: #666; font-size: 16px;">Por favor ingresa con esta contraseña y cámbiala inmediatamente por una segura.</p>
								<div style="text-align: center; margin-top: 30px; font-size: 12px; color: #999;">
									<p>Si no solicitaste este cambio, por favor contacta al administrador.</p>
								</div>
							</div>
						</div>';

						require_once "controladores/correo.controlador.php";
						$envio = ControladorCorreo::ctrEnviarCorreo($usuario["email"], $asunto, $mensaje);

						if ($envio == "ok") {
							echo '<script>
								swal({
									type: "success",
									title: "¡Correo enviado!",
									text: "Revisa tu bandeja de entrada para ver tu nueva contraseña. Si no lo ves, revisa Spam.",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								}).then((result)=>{
									if(result.value){
										window.location = "login";
									}
								});
							</script>';
						} else {
							echo '<script>
								swal({
									type: "error",
									title: "Error al enviar correo",
									text: "Hubo un problema enviando el correo (' . $envio . '). Intenta de nuevo más tarde. Error: ' . $envio . '",
									showConfirmButton: true,
									confirmButtonText: "Cerrar"
								});
							</script>';
						}
					}
				} else {
					echo '<script>
						swal({
							type: "error",
							title: "¡Correo no encontrado!",
							text: "El correo ingresado no está registrado en el sistema.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Formato inválido!",
						text: "El formato del correo electrónico no es correcto.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}



}
