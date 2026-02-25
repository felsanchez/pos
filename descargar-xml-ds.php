<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

// Validar que se reciba el número de documento soporte
if (!isset($_GET["xml"])) {
    die("Error: No se especificó el número de documento soporte.");
}

$numeroDS = $_GET["xml"]; // Ej: DS1

// 1. Autenticar
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error de autenticación con Factus: " . $auth['mensaje']);
}
$token = $auth['token'];

// 2. Obtener URL base
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

// 3. Consultar endpoint de descarga XML para Documento Soporte
// Endpoint: /v1/support-documents/download-xml/{number}
$endpoint = "$baseUrl/v1/support-documents/download-xml/$numeroDS";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Bearer $token"
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("Error al descargar XML de Documento Soporte. Código HTTP: $httpCode. Respuesta: " . substr($response, 0, 200));
}

$json = json_decode($response, true);

if (!isset($json['data']['xml_base_64_encoded'])) {
    die("Error: La respuesta de Factus no contiene el XML codificado.");
}

// 4. Decodificar y descargar
$xmlContent = base64_decode($json['data']['xml_base_64_encoded']);
$fileName = isset($json['data']['file_name']) ? $json['data']['file_name'] . ".xml" : "ds_$numeroDS.xml";

if (!$xmlContent) {
    die("Error al decodificar el XML.");
}

// Limpiar buffer de salida para evitar corrupción de archivo
if (ob_get_level())
    ob_end_clean();

header('Content-Description: File Transfer');
header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($xmlContent));

echo $xmlContent;
exit;
?>