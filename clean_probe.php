<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

$ids = range(1, 10); // Probe 1-10
$baseNit = 900000000;
$token = ModeloFactus::mdlObtenerAccessToken();

echo "Probing IDs 1-10...\n";

foreach ($ids as $id) {
    $currentNit = $baseNit + $id;
    $dv = ModeloFactus::mdlCalcularDV($currentNit);

    $payload = array(
        "numbering_range_id" => 1190,
        "reference_code" => "PRB" . time() . $id,
        "payment_form" => "1",
        "payment_due_date" => date('Y-m-d', strtotime('+1 day')),
        "payment_method_code" => "10",
        "operation_type" => 10,
        "establishment" => array(
            "name" => "FELIPE DE JESUS",
            "address" => "Calle 14A # 20-04",
            "phone_number" => "3013142899",
            "email" => "felipesanchez.info@gmail.com",
            "municipality_id" => "27",
            "merchant_registration" => "15rm",
            "economic_activity_code" => "6819",
            "fiscal_responsibilities" => array(array("code" => "R-99-PN")),
            "legal_organization_id" => "2"
        ),
        "customer" => array(
            "identification" => (string) $currentNit,
            "dv" => (string) $dv,
            "company" => "Empresa Prueba ID $id",
            "trade_name" => "Prueba ID $id",
            "names" => "Empresa Prueba $id",
            "address" => "Calle Falsa 123",
            "email" => "prueba$id@example.com",
            "phone" => "3001234567",
            "legal_organization_id" => "1",
            "tribute_id" => "18",
            "fiscal_responsibilities" => array(array("code" => "O-23")),
            "identification_document_id" => $id,
            "municipality_id" => "169"
        ),
        "items" => array(
            array(
                "code_reference" => "TEST-1",
                "name" => "Producto Prueba",
                "quantity" => 1,
                "discount_rate" => 0,
                "price" => 1000,
                "tax_rate" => "19.00",
                "unit_measure_id" => 70,
                "standard_code_id" => 1,
                "is_excluded" => 0,
                "tribute_id" => 1,
                "withholding_taxes" => array()
            )
        )
    );

    $jsonPayload = json_encode($payload);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api-sandbox.factus.com.co/v1/bills/validate",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $data = json_decode($response, true);
    curl_close($curl);

    if (isset($data['data']['customer']['legal_organization'])) {
        $org = $data['data']['customer']['legal_organization'];
        echo "ID $id: " . $org['name'] . " (Code: " . $org['code'] . ")\n";
    } else {
        $msg = $data['message'] ?? 'Unknown Error';
        if (strpos($msg, 'inválido') !== false) {
            echo "ID $id: INVALID\n";
        } else {
            echo "ID $id: ERROR - $msg\n";
        }
    }
}
?>