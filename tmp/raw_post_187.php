<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/ventas.modelo.php';
require_once 'modelos/conexion.php';
require_once 'controladores/ventas.controlador.php';

if (!isset($_SESSION)) {
    session_start();
}

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error de autenticación: " . $auth['mensaje']);
}

$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$apiUrl = $config['api_url'];

$idVenta = 765;
$venta = ControladorVentas::ctrMostrarVentas("id", $idVenta);

if (!$venta)
    die("No venta 765");

// Construir un payload basico usando json quemado (mock) pero con la numeracion real (187) 
// para forzar a Factus a validar el documento
$payload = array(
    "numbering_range_id" => $venta["resolucion_id"],
    "reference_code" => $venta["codigo"],
    "observation" => "Factura generada para validación",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "customer" => array(
        "identification" => "123456789", // Reemplazar con datos reales si falla la validacion, solo intentamos desbloquear
        "dv" => "3",
        "company" => "Consumidor Final",
        "trade_name" => "Consumidor Final",
        "names" => "Consumidor",
        "email" => "cliente@example.com",
        "phone" => "3000000000",
        "legal_organization_id" => "2",
        "tribute_id" => "21",
        "identification_document_id" => "3",
        "municipality_id" => "1000",
    ),
    "items" => array(
        array(
            "code_reference" => "GEN-01",
            "name" => "Item Prueba",
            "quantity" => 1,
            "discount_rate" => 0,
            "price" => 100,
            "tax_rate" => "19.00",
            "unit_measure_id" => 94,
            "standard_code_id" => 1,
            "is_excluded" => 0,
            "tribute_id" => 1,
            "withholding_taxes" => array()
        )
    )
);

// We try POST to /v1/bills to see if it overwrites the unvalidated 187
$url = $apiUrl . "/v1/bills";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
));

$respuesta = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: " . $httpCode . "\n";
print_r(json_decode($respuesta, true));
?>