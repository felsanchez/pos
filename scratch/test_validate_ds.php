<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION["nombre"] = "admin test";
$_SESSION["perfil"] = "Administrador";

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM documentos_soporte ORDER BY id DESC LIMIT 1");
$stmt->execute();
$ds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ds) {
    die("No Documento Soporte found in DB!\n");
}

echo "Found DS ID: " . $ds['id'] . " | Proveedor ID: " . $ds['id_proveedor'] . "\n";

// Authenticate Factus API
$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Auth error: " . $auth['mensaje'] . "\n");
}

$postSimulado = [
    "seleccionarProveedor" => $ds["id_proveedor"],
    "listaProductosDS" => $ds["productos"],
    "tipoDescuentoDS" => $ds["tipo_descuento"],
    "valorDescuentoDS" => $ds["valor_descuento"],
    "montoDescuentoDS" => $ds["monto_descuento"],
    "datosRetencionesDS" => $ds["retenciones"]
];

$payload = ControladorFactus::prepararDatosDocumentoSoporte($postSimulado);

if (isset($payload['error'])) {
    die("Payload prep error: " . $payload['mensaje'] . "\n");
}

echo "Payload Prepared:\n" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Sending validate request to Factus Sandbox API...\n";
$res = ModeloFactus::mdlCrearDocumentoSoporte($auth['token'], $payload);

echo "HTTP Code: " . $res['http_code'] . "\n";
echo "Response: " . $res['respuesta'] . "\n";
