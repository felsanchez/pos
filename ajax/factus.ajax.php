<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once __DIR__ . "/../modelos/session-manager.php";
SessionManager::startSecure();

require_once __DIR__ . "/../modelos/sanitizer.php";
require_once __DIR__ . "/../controladores/factus.controlador.php";
require_once __DIR__ . "/../modelos/factus.modelo.php";
require_once __DIR__ . "/../controladores/clientes.controlador.php";
require_once __DIR__ . "/../modelos/clientes.modelo.php";
require_once __DIR__ . "/../controladores/productos.controlador.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";
require_once __DIR__ . "/../controladores/configuracion.controlador.php";
require_once __DIR__ . "/../modelos/configuracion.modelo.php";
require_once __DIR__ . "/../controladores/notificaciones.controlador.php";
require_once __DIR__ . "/../modelos/notificaciones.modelo.php";
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/csrf.php";
require_once __DIR__ . "/../controladores/ventas.controlador.php";
require_once __DIR__ . "/../modelos/ventas.modelo.php";
require_once __DIR__ . "/../controladores/movimientos.controlador.php";
require_once __DIR__ . "/../modelos/movimientos.modelo.php";
require_once __DIR__ . "/../modelos/helpers.php";
require_once __DIR__ . "/../controladores/correo.controlador.php";
require_once __DIR__ . "/../controladores/usuarios.controlador.php";
require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../controladores/proveedores.controlador.php";
require_once __DIR__ . "/../modelos/proveedores.modelo.php";

// Incluir AjaxFacturacion como librería (sin ejecutar su entry-point)
// El flag FACTURACION_AJAX_INCLUDED evita que el código raíz de facturacion.ajax.php se ejecute.
if (!defined('FACTURACION_AJAX_INCLUDED')) {
    define('FACTURACION_AJAX_INCLUDED', true);
}
require_once __DIR__ . "/facturacion.ajax.php";

/*

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Token CSRF inválido', 'success' => false]));
    }
}
*/

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

		// Envío automático de correo al cliente si la firma fue exitosa
		if (!$resultado['error']) {
			try {
				$envioAuto = new AjaxFacturacion();
				$venta   = ControladorVentas::ctrMostrarVentas('id', $idVenta);
				$emailCliente = '';
				if (!empty($venta['id_cliente'])) {
					$cliente = ControladorClientes::ctrMostrarClientes('id', $venta['id_cliente']);
					$emailCliente = $cliente['email'] ?? '';
				}
				if (!empty($emailCliente)) {
					$envioAuto->idVenta      = $idVenta;
					$envioAuto->emailDestino = $emailCliente;
					ob_start();
					$envioAuto->ajaxEnviarPDFCorreo();
					ob_end_clean(); // Descartar la salida JSON del método de correo
				}
			} catch (Exception $eCorreo) {
				// El correo falló pero la firma fue exitosa: no interrumpir la respuesta
				error_log('Error al enviar correo automático tras firma: ' . $eCorreo->getMessage());
			}
		}

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

		// Envío automático de correo al proveedor si la firma fue exitosa
		if (isset($resultado['error']) && !$resultado['error']) {
			try {
				$documentoSoporte = ControladorFactus::ctrMostrarDocumentosSoporte("id", $idDS);
				if ($documentoSoporte && !empty($documentoSoporte["id_proveedor"])) {
					$proveedor = ControladorProveedores::ctrMostrarProveedores("id", $documentoSoporte["id_proveedor"]);
					$emailProveedor = $proveedor['correo'] ?? '';
					if (!empty($emailProveedor)) {
						$envioAuto = new AjaxFacturacion();
						$envioAuto->idDS = $idDS;
						$envioAuto->emailDestino = $emailProveedor;
						ob_start();
						$envioAuto->ajaxEnviarPDFDSCorreo();
						ob_end_clean(); // Descartar la salida JSON del método de correo
					}
				}
			} catch (Exception $eCorreo) {
				// El correo falló pero la firma fue exitosa: no interrumpir la respuesta
				error_log('Error al enviar correo automático tras firma de DS: ' . $eCorreo->getMessage());
			}
		}

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
	FIRMAR NOTA DE AJUSTE DS
	=============================================*/
	public function ajaxFirmarNotaAjusteDS()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "firmarNotaAjusteDS") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		if (!isset($_POST["idNota"]) || empty($_POST["idNota"])) {
			echo json_encode(array("error" => true, "mensaje" => "ID de nota no proporcionado"));
			return;
		}

		$idNota = $_POST["idNota"];
		$resultado = ControladorFactus::ctrFirmarNotaAjusteDS($idNota);

		// Envío automático de correo al proveedor si la firma fue exitosa
		if (isset($resultado['error']) && !$resultado['error']) {
			try {
				$nota = ControladorFactus::ctrMostrarNotasAjusteDS("id", $idNota);
				if ($nota && !empty($nota["id_proveedor"])) {
					$proveedor = ControladorProveedores::ctrMostrarProveedores("id", $nota["id_proveedor"]);
					$emailProveedor = $proveedor['correo'] ?? '';
					if (!empty($emailProveedor)) {
						$envioAuto = new AjaxFacturacion();
						$envioAuto->idNA = $idNota;
						$envioAuto->emailDestino = $emailProveedor;
						ob_start();
						$envioAuto->ajaxEnviarPDFNACorreo();
						ob_end_clean(); // Descartar la salida JSON del método de correo
					}
				}
			} catch (Exception $eCorreo) {
				// El correo falló pero la firma fue exitosa: no interrumpir la respuesta
				error_log('Error al enviar correo automático tras firma de NA DS: ' . $eCorreo->getMessage());
			}
		}

		echo json_encode($resultado);
	}

	/*=============================================
	OBTENER TODAS LAS NOTAS CRÉDITO POR VENTA
	=============================================*/
	public function ajaxObtenerNotasCreditoVenta()
	{
		if (!isset($_POST["idVenta"])) {
			echo json_encode(["error" => "No se recibió el ID de la venta"]);
			return;
		}

		$idVenta = $_POST["idVenta"];
		$notas = ControladorFactus::ctrObtenerNotasCreditoPorVenta($idVenta);

		echo json_encode($notas);
	}

	/*=============================================
	OBTENER TODAS LAS NOTAS DE AJUSTE POR DOCUMENTO SOPORTE
	=============================================*/
	public function ajaxObtenerNotasAjusteDS()
	{
		if (!isset($_POST["idDS"])) {
			echo json_encode(["error" => "No se recibió el ID del Documento Soporte"]);
			return;
		}

		$idDS = $_POST["idDS"];
		$notas = ControladorFactus::ctrObtenerNotasAjusteDSPorDS($idDS);

		echo json_encode($notas);
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

		// Si faltan parámetros en $_POST, cargar la configuración desde la BD
		$config = ModeloFactus::mdlObtenerConfiguracion();

		$apiUrl = !empty($_POST["apiUrl"]) ? $_POST["apiUrl"] : ($config["api_url"] ?? "");
		$clientId = !empty($_POST["clientId"]) ? $_POST["clientId"] : ($config["client_id"] ?? "");
		$clientSecret = !empty($_POST["clientSecret"]) ? $_POST["clientSecret"] : ($config["client_secret"] ?? "");
		$username = !empty($_POST["username"]) ? $_POST["username"] : ($config["username"] ?? "");
		$password = !empty($_POST["password"]) ? $_POST["password"] : ($config["password"] ?? "");

		if (empty($apiUrl) || empty($clientId) || empty($clientSecret)) {
			echo json_encode(array(
				"error" => true,
				"mensaje" => "Faltan datos de configuración de Factus para autenticar."
			));
			return;
		}

		// Preparar URL de autenticación OAuth2
		$url = rtrim($apiUrl, '/') . '/oauth/token';

		// Verificamos si vienen username y password para usar grant_type="password"
		if (!empty($username) && !empty($password)) {
			$datos = array(
				"grant_type" => "password",
				"client_id" => $clientId,
				"client_secret" => $clientSecret,
				"username" => $username,
				"password" => $password
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

				// Guardar tokens Y configuración en la base de datos (preservando valores existentes si no vienen en POST)
				$ambiente = !empty($_POST["ambiente"]) ? $_POST["ambiente"] : ($config["ambiente"] ?? ((strpos($apiUrl, 'sandbox') !== false) ? 'sandbox' : 'produccion'));
				$username = !empty($_POST["username"]) ? $_POST["username"] : ($config["username"] ?? '');
				$password = !empty($_POST["password"]) ? $_POST["password"] : ($config["password"] ?? '');
				$rangoNumeracionId = !empty($_POST["rangoNumeracionId"]) ? $_POST["rangoNumeracionId"] : ($config["rango_numeracion_id"] ?? '');

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
	/*=============================================
	ELIMINAR NOTA DE AJUSTE DS (BORRADOR)
	=============================================*/
	public function ajaxEliminarNotaAjusteDS()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "eliminarNotaAjusteDS") {
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			return;
		}

		if (!isset($_POST["idNota"]) || empty($_POST["idNota"])) {
			echo json_encode(array("error" => true, "mensaje" => "ID de nota no proporcionado"));
			return;
		}

		$idNota = $_POST["idNota"];
		$resultado = ControladorFactus::ctrEliminarNotaAjusteDS($idNota);

		if ($resultado == "ok") {
			echo json_encode(array("error" => false, "mensaje" => "Borrador de Nota de Ajuste eliminado correctamente"));
		} else if ($resultado == "error_estado") {
			echo json_encode(array("error" => true, "mensaje" => "No se puede eliminar la nota porque ya fue enviada a la DIAN"));
		} else {
			echo json_encode(array("error" => true, "mensaje" => "Error al eliminar la nota de ajuste de la base de datos"));
		}
	}

	public function ajaxMostrarNotasAjusteDSServerSide()
	{
		$respuesta = ControladorFactus::ctrMostrarNotasAjusteDSServerSide($_POST);
		echo json_encode($respuesta);
	}

	public function ajaxCrearDocumentoSoporte()
	{
		$resultado = ControladorFactus::ctrCrearDocumentoSoporte();
		echo json_encode($resultado);
	}

	/*=============================================
	CREAR FACTURA ELECTRÓNICA (Desde formulario de creación)
	=============================================*/
	public function ajaxCrearFacturaElectronica()
	{
		if (!isset($_POST["accion"]) || $_POST["accion"] != "crearFacturaElectronica") {
			echo json_encode(["status" => "error", "titulo" => "Acción inválida", "mensaje" => "Acción no reconocida"]);
			return;
		}

		// Delegar al controlador de ventas (que ya tiene la lógica completa)
		// Forzamos el flag de ajax para que retorne JSON
		$_POST["guardarVentaFactus"] = "1";
		$_POST["ajax"] = "true";

		ControladorVentas::ctrCrearVentaFactus();
	}
	public function ajaxEjecutarWebhookConocimiento()
	{
		if (ob_get_length()) ob_clean();
		header('Content-Type: application/json');

		$url = "https://master-n8n.la6x8e.easypanel.host/webhook/base-de-conocimiento";

		// Petición GET requerida por el webhook n8n
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		// Fallback POST si n8n responde 404 en GET
		if ($httpCode == 404) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
				"evento" => "sincronizar_base_conocimiento",
				"timestamp" => date("Y-m-d H:i:s")
			)));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curlError = curl_error($ch);
			curl_close($ch);
		}

		if ($curlError) {
			echo json_encode(array(
				"status" => "error",
				"mensaje" => "Error de conexión al webhook: " . $curlError
			));
		} else if ($httpCode >= 200 && $httpCode < 300) {
			echo json_encode(array(
				"status" => "ok",
				"mensaje" => "Webhook ejecutado correctamente (HTTP " . $httpCode . ")",
				"respuesta" => $response
			));
		} else {
			echo json_encode(array(
				"status" => "error",
				"mensaje" => "El servidor webhook respondió con código HTTP " . $httpCode,
				"respuesta" => $response
			));
		}
		exit;
	}
}

/*=============================================
EJECUTAR ACCIÓN
=============================================*/
if (isset($_POST["accion"])) {
	$factus = new AjaxFactus();

	switch ($_POST["accion"]) {
		case "ejecutarWebhookConocimiento":
			$factus->ajaxEjecutarWebhookConocimiento();
			break;
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
		case "crearDS":
			$factus->ajaxCrearDocumentoSoporte();
			break;
		case "crearNotaAjusteDS":
			$factus->ajaxCrearNotaAjusteDS();
			break;
		case "firmarNotaAjusteDS":
			$factus->ajaxFirmarNotaAjusteDS();
			break;
		case "eliminarNotaAjusteDS":
			$factus->ajaxEliminarNotaAjusteDS();
			break;
		case "obtenerNotasCreditoVenta":
			$factus->ajaxObtenerNotasCreditoVenta();
			break;
		case "obtenerNotasAjusteDS":
			$factus->ajaxObtenerNotasAjusteDS();
			break;
		case "mostrarNotasAjusteDSServerSide":
			$factus->ajaxMostrarNotasAjusteDSServerSide();
			break;
		case "crearFacturaElectronica":
			$factus->ajaxCrearFacturaElectronica();
			break;
		case "eliminarNotaCredito":
			$factusEliminar = new ControladorFactus();
			$respuesta = $factusEliminar->ctrEliminarNotaCredito();
			echo $respuesta;
			exit;
			break;
		default:
			echo json_encode(array("error" => true, "mensaje" => "Acción no válida"));
			break;
	}
}
