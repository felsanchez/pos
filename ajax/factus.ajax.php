<?php

session_start();

require_once "../controladores/factus.controlador.php";
require_once "../modelos/factus.modelo.php";
require_once "../modelos/conexion.php";

class AjaxFactus
{

	/*=============================================
	PROBAR CONEXIÓN CON FACTUS
	=============================================*/
	public function ajaxProbarConexion()
	{

		if (!isset($_POST["accion"]) || $_POST["accion"] != "probarConexion") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		// Validar que vengan todos los datos
		if (empty($_POST["apiUrl"]) || empty($_POST["clientId"]) || empty($_POST["clientSecret"])) {
			echo json_encode(array(
				"error" => true,
				"mensaje" => "Faltan datos para probar la conexión"
			));
			return;
		}

		$apiUrl = $_POST["apiUrl"];
		$clientId = $_POST["clientId"];
		$clientSecret = $_POST["clientSecret"];

		// Preparar URL de autenticación OAuth2
		$url = rtrim($apiUrl, '/') . '/oauth/token';

		$datos = array(
			"grant_type" => "client_credentials",
			"client_id" => $clientId,
			"client_secret" => $clientSecret
		);

		// Realizar petición a Factus
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$respuesta = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		// Verificar errores de CURL
		if ($error) {
			echo json_encode(array(
				"error" => true,
				"mensaje" => "Error de conexión",
				"detalles" => $error
			));
			return;
		}

		// Verificar respuesta HTTP
		if ($httpCode == 200) {
			$resultado = json_decode($respuesta, true);

			if (isset($resultado['access_token'])) {
				echo json_encode(array(
					"error" => false,
					"mensaje" => "Conexión exitosa con Factus API"
				));
			} else {
				echo json_encode(array(
					"error" => true,
					"mensaje" => "Respuesta inesperada de la API",
					"detalles" => $respuesta
				));
			}
		} else {
			$resultado = json_decode($respuesta, true);
			$mensajeError = isset($resultado['mensaje']) ? $resultado['mensaje'] : "Error HTTP $httpCode";

			echo json_encode(array(
				"error" => true,
				"mensaje" => $mensajeError,
				"detalles" => $respuesta
			));
		}
	}

	/*=============================================
	SINCRONIZAR MUNICIPIOS
	=============================================*/
	public function ajaxSincronizarMunicipios()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "sincronizarMunicipios") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		$resultado = ControladorFactus::ctrSincronizarMunicipios();
		echo json_encode($resultado);
	}

	/*=============================================
	SINCRONIZAR TRIBUTOS
	=============================================*/
	public function ajaxSincronizarTributos()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "sincronizarTributos") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		$resultado = ControladorFactus::ctrSincronizarTributos();
		echo json_encode($resultado);
	}

	/*=============================================
	SINCRONIZAR UNIDADES DE MEDIDA
	=============================================*/
	public function ajaxSincronizarUnidades()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "sincronizarUnidades") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		$resultado = ControladorFactus::ctrSincronizarUnidades();
		echo json_encode($resultado);
	}

	/*=============================================
	SINCRONIZAR RANGOS DE NUMERACIÓN
	=============================================*/
	public function ajaxSincronizarRangos()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "sincronizarRangos") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		$resultado = ControladorFactus::ctrSincronizarRangos();
		echo json_encode($resultado);
	}

	/*=============================================
	GENERAR FACTURA ELECTRÓNICA
	=============================================*/
	public function ajaxGenerarFacturaElectronica()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "generarFactura") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		if (!isset($_POST["idVenta"]) || empty($_POST["idVenta"])) {
			echo json_encode(array("error" => true, "mensaje" => "ID de venta no proporcionado"));
			return;
		}

		$idVenta = $_POST["idVenta"];
		$resultado = ControladorFactus::ctrGenerarFacturaElectronica($idVenta);
		echo json_encode($resultado);
	}

	/*=============================================
	FIRMAR DOCUMENTO SOPORTE
	=============================================*/
	public function ajaxFirmarDocumentoSoporte()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "firmarDS") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		if (!isset($_POST["idDS"]) || empty($_POST["idDS"])) {
			echo json_encode(array("error" => true, "mensaje" => "ID de documento soporte no proporcionado"));
			return;
		}

		$idDS = $_POST["idDS"];
		$resultado = ControladorFactus::ctrFirmarDocumentoSoporte($idDS);
		echo json_encode($resultado);
	}

	/*=============================================
	ELIMINAR DOCUMENTO SOPORTE
	=============================================*/
	public function ajaxEliminarDocumentoSoporte()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "eliminarDS") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		if (!isset($_POST["idDS"]) || empty($_POST["idDS"])) {
			echo json_encode(array("error" => true, "mensaje" => "ID de documento soporte no proporcionado"));
			return;
		}

		$idDS = $_POST["idDS"];
		$resultado = ControladorFactus::ctrEliminarDocumentoSoporte($idDS);
		echo json_encode($resultado);
	}

	/*=============================================
	AUTENTICAR Y OBTENER TOKENS
	=============================================*/
	public function ajaxAutenticar()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "autenticar") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		// Validar que vengan todos los datos
		if (empty($_POST["apiUrl"]) || empty($_POST["clientId"]) || empty($_POST["clientSecret"])) {
			echo json_encode(array(
				"error" => true,
				"mensaje" => "Faltan datos para autenticar"
			));
			return;
		}

		$apiUrl = $_POST["apiUrl"];
		$clientId = $_POST["clientId"];
		$clientSecret = $_POST["clientSecret"];

		// Preparar URL de autenticación OAuth2
		$url = rtrim($apiUrl, '/') . '/oauth/token';

		// Verificamos si vienen username y password para usar grant_type="password"
		if (isset($_POST["username"]) && !empty($_POST["username"]) && isset($_POST["password"]) && !empty($_POST["password"])) {
			$datos = array(
				"grant_type" => "password",
				"client_id" => $clientId,
				"client_secret" => $clientSecret,
				"username" => $_POST["username"],
				"password" => $_POST["password"]
			);
		} else {
			// Si no, usamos client_credentials (por defecto)
			$datos = array(
				"grant_type" => "client_credentials",
				"client_id" => $clientId,
				"client_secret" => $clientSecret
			);
		}

		// Realizar petición a Factus
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$respuesta = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		// Verificar errores de CURL
		if ($error) {
			echo json_encode(array(
				"error" => true,
				"mensaje" => "Error de conexión",
				"detalles" => $error
			));
			return;
		}

		// Verificar respuesta HTTP
		if ($httpCode == 200) {
			$resultado = json_decode($respuesta, true);

			if (isset($resultado['access_token'])) {
				// Calcular fecha de expiración
				$segundos = isset($resultado['expires_in']) ? $resultado['expires_in'] : 3600;
				$fechaExpiracion = date('Y-m-d H:i:s', time() + $segundos);

				// Guardar tokens Y configuración en la base de datos
				$ambiente = $_POST["ambiente"] ?? ((strpos($apiUrl, 'sandbox') !== false) ? 'sandbox' : 'produccion');
				$username = $_POST["username"] ?? '';
				$password = $_POST["password"] ?? '';
				$rangoNumeracionId = $_POST["rangoNumeracionId"] ?? '';

				$stmt = Conexion::conectar()->prepare(
					"UPDATE factus_config 
					SET access_token = :access_token,
						refresh_token = :refresh_token,
						token_expiracion = :token_expiracion,
						api_url = :api_url,
						client_id = :client_id,
						client_secret = :client_secret,
						ambiente = :ambiente,
						username = :username,
						password = :password,
						rango_numeracion_id = :rango_numeracion_id
					WHERE id = 1"
				);

				$stmt->bindParam(":access_token", $resultado['access_token'], PDO::PARAM_STR);
				$stmt->bindParam(":refresh_token", $resultado['refresh_token'], PDO::PARAM_STR);
				$stmt->bindParam(":token_expiracion", $fechaExpiracion, PDO::PARAM_STR);
				$stmt->bindParam(":api_url", $apiUrl, PDO::PARAM_STR);
				$stmt->bindParam(":client_id", $clientId, PDO::PARAM_STR);
				$stmt->bindParam(":client_secret", $clientSecret, PDO::PARAM_STR);
				$stmt->bindParam(":ambiente", $ambiente, PDO::PARAM_STR);
				$stmt->bindParam(":username", $username, PDO::PARAM_STR);
				$stmt->bindParam(":password", $password, PDO::PARAM_STR);
				$stmt->bindParam(":rango_numeracion_id", $rangoNumeracionId, PDO::PARAM_STR);

				if ($stmt->execute()) {
					echo json_encode(array(
						"error" => false,
						"mensaje" => "Autenticación exitosa. Tokens guardados correctamente.",
						"expiracion" => $fechaExpiracion
					));
				} else {
					echo json_encode(array(
						"error" => true,
						"mensaje" => "Error al guardar los tokens en la base de datos"
					));
				}
			} else {
				echo json_encode(array(
					"error" => true,
					"mensaje" => "Respuesta inesperada de la API",
					"detalles" => $respuesta
				));
			}
		} else {
			$resultado = json_decode($respuesta, true);
			$mensajeError = isset($resultado['message']) ? $resultado['message'] : "Error HTTP $httpCode";

			echo json_encode(array(
				"error" => true,
				"mensaje" => $mensajeError,
				"detalles" => $respuesta
			));
		}
	}
	public function ajaxCrearNotaAjusteDS()
	{
		$resultado = ControladorFactus::ctrCrearNotaAjusteDS();
		echo json_encode($resultado);
	}
}

/*=============================================
EJECUTAR ACCIÓN
=============================================*/
if (isset($_POST["accion"])) {
	$factus = new AjaxFactus();

	switch ($_POST["accion"]) {
		case "probarConexion":
			$factus->ajaxProbarConexion();
			break;
		case "sincronizarMunicipios":
			$factus->ajaxSincronizarMunicipios();
			break;
		case "sincronizarTributos":
			$factus->ajaxSincronizarTributos();
			break;
		case "sincronizarUnidades":
			$factus->ajaxSincronizarUnidades();
			break;
		case "sincronizarRangos":
			$factus->ajaxSincronizarRangos();
			break;
		case "autenticar":
			$factus->ajaxAutenticar();
			break;
		case "generarFactura":
			$factus->ajaxGenerarFacturaElectronica();
			break;
		case "firmarDS":
			$factus->ajaxFirmarDocumentoSoporte();
			break;
		case "eliminarDS":
			$factus->ajaxEliminarDocumentoSoporte();
			break;
		case "crearNotaAjusteDS":
			$factus->ajaxCrearNotaAjusteDS();
			break;
		default:
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			break;
	}
}
