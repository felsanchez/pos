<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/proveedores.modelo.php";

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];

// Simulate payload for a NEW Documento Soporte (Provider 8)
// This will NOT create a real document, just test the validation (and see the echoed data)
$idProveedor = 8;
$proveedor = ModeloProveedores::mdlMostrarProveedores("proveedores", "id", $idProveedor);

echo "DEBUG PROVIDER DATA:\n";
print_r($proveedor);
echo "\n";

$tipoOrganizacion = $proveedor['organizacion_id'] ?? "2";
$tipoDocumentoId = $proveedor['tipo_documento_id'] ?? 3;
$dv = strval(ModeloFactus::mdlCalcularDV($proveedor['documento']));

$payload = [
    "numbering_range_id" => 1410, // Standard DS range in sandbox
    "reference_code" => "TEST-VAL-NEW-" . time(),
    "observation" => "Prueba de validación municipio",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "operation_type" => 10,
    "provider" => [
        "identification" => $proveedor['documento'],
        "dv" => $dv,
        "company" => ($tipoOrganizacion == "1") ? $proveedor['nombre'] : '',
        "trade_name" => $proveedor["marca"] ?? $proveedor["nombre"],
        "names" => $proveedor['nombre'],
        "address" => $proveedor['direccion'],
        "email" => $proveedor['correo'],
        "phone" => $proveedor['celular'],
        "legal_organization_id" => strval($tipoOrganizacion),
        "tribute_id" => "21",
        "identification_document_id" => strval($tipoDocumentoId),
        "municipality_id" => strval($proveedor['municipio_id']),
        "country_code" => "CO"
    ],
    "items" => [
        [
            "code_reference" => "114",
            "name" => "Producto prueba",
            "quantity" => 1,
            "discount_rate" => 0,
            "price" => 100,
            "tax_rate" => "0.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 1,
            "tribute_id" => 7,
            "withholding_taxes" => []
        ]
    ]
];

echo "Sending Payload to Factus (Validation):\n";
echo "Identification: " . $payload["provider"]["identification"] . "\n";
echo "Municipality ID Sent: " . $payload["provider"]["municipality_id"] . "\n";

$apiUrl = ModeloFactus::mdlObtenerConfiguracion()['api_url'];
$url = $apiUrl . "/v1/support-documents/validate";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: $httpCode\n";
$resData = json_decode($response, true);
if (isset($resData["data"]["provider"]["municipality"])) {
    echo "Factus Response Municipality: " . $resData["data"]["provider"]["municipality"]["name"] . " (ID: " . $resData["data"]["provider"]["municipality"]["id"] . ")\n";
} else if (isset($resData["message"])) {
    echo "Message: " . $resData["message"] . "\n";
    if (isset($resData["errors"])) {
        print_r($resData["errors"]);
    }
} else {
    echo "Response: " . $response . "\n";
}
