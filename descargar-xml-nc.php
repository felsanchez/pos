<?php
require_once "modelos/session-manager.php";
SessionManager::startSecure();

require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

if (isset($_GET["xml"])) {

    $numeroNC = $_GET["xml"];

    // 1. Buscar la NC localmente para validar que existe
    $nota = ControladorFactus::ctrMostrarNotasCredito("numero_nota_credito", $numeroNC);

    if (!$nota) {
        die("No se encontró registro de la nota de crédito: " . htmlspecialchars($numeroNC));
    }

    // Restricción por Perfil: Si no es Admin, solo ve su sucursal
    $esAdmin = (isset($_SESSION["perfil"]) && stripos($_SESSION["perfil"], "Admin") !== false);
    $idBodegaSession = !empty($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;

    if (!$esAdmin) {
        require_once "modelos/ventas.modelo.php";
        $venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $nota["id_venta_original"]);
        if ($venta && $venta["id_bodega"] != $idBodegaSession) {
            die("No autorizado para descargar este documento.");
        }
    }

    // 2. Autenticar con Factus
    $auth = ControladorFactus::ctrAutenticar();
    if ($auth['error']) {
        echo "<h3>Error de Autenticación con Factus</h3>";
        echo "<p>La conexión con Factus ha expirado. Por favor, vaya a <b>Configuración > Factus</b> y haga clic en <b>'Autenticar'</b>.</p>";
        exit;
    }

    // 3. Llamar al endpoint correcto de descarga de XML
    $config = ModeloFactus::mdlObtenerConfiguracion();
    $url = $config['api_url'] . '/v1/credit-notes/download-xml/' . $numeroNC;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $auth['token'],
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        die("Error al obtener el XML de la Nota de Crédito desde Factus (HTTP $httpCode).");
    }

    $data = json_decode($respuesta, true);
    $xmlBase64 = $data['data']['xml_base_64_encoded'] ?? '';
    $fileName   = $data['data']['file_name'] ?? $numeroNC;
    if (substr($fileName, -4) !== '.xml') {
        $fileName .= '.xml';
    }

    if (empty($xmlBase64)) {
        die("Factus no devolvió el contenido del XML para la nota de crédito.");
    }

    // 4. Decodificar y servir el archivo
    $xmlContent = base64_decode($xmlBase64);
    header('Content-Description: File Transfer');
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . strlen($xmlContent));
    echo $xmlContent;
    exit;

} else {
    die("Parámetro de nota no definido.");
}
