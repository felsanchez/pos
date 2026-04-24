<?php

require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

if (isset($_GET["xml"])) {

    $codigo = $_GET["xml"];

    // 1. Buscar la venta localmente
    $venta = ControladorVentas::ctrMostrarVentas("codigo", $codigo);
    if (!$venta) {
        $venta = ControladorVentas::ctrMostrarVentas("numero_factura", $codigo);
    }

    if (!$venta) {
        die("No se encontró registro de la venta: " . htmlspecialchars($codigo));
    }

    $numeroFactura = $venta["numero_factura"];
    if (empty($numeroFactura)) {
        die("Esta factura no tiene un número oficial de la DIAN aún. Sólo se puede descargar el XML de facturas ya enviadas.");
    }

    // 2. Autenticar con Factus
    $auth = ControladorFactus::ctrAutenticar();
    if ($auth['error']) {
        echo "<h3>Error de Autenticación con Factus</h3>";
        echo "<p>La conexión con Factus ha expirado. Por favor, vaya a <b>Configuración > Factus</b> y haga clic en <b>'Autenticar'</b>.</p>";
        exit;
    }

    // 3. Llamar al endpoint de descarga de XML
    $config = ModeloFactus::mdlObtenerConfiguracion();
    $url = $config['api_url'] . '/v1/bills/download-xml/' . $numeroFactura;

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
        die("Error al obtener el XML desde Factus (HTTP $httpCode). Verifique que la factura haya sido enviada correctamente a la DIAN.");
    }

    $data = json_decode($respuesta, true);
    $xmlBase64 = $data['data']['xml_base_64_encoded'] ?? '';
    $fileName   = $data['data']['file_name'] ?? $numeroFactura;
    // Asegurar que siempre tenga la extensión .xml
    if (substr($fileName, -4) !== '.xml') {
        $fileName .= '.xml';
    }

    if (empty($xmlBase64)) {
        die("Factus no devolvió el contenido del XML.");
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
    die("Parámetro de factura no definido.");
}
