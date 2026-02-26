<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];
$config = ModeloFactus::mdlObtenerConfiguracion();
$baseUrl = $config['api_url'];

$cases = [
    ['range' => 1193, 'desc' => 'Adjustment DS range'],
    ['range' => 1195, 'desc' => 'Adjustment Payroll range'],
    ['range' => 1191, 'desc' => 'Credit Note range'],
    ['range' => 1193, 'type' => 12, 'desc' => 'Adjustment DS with type 12'],
];

foreach ($cases as $c) {
    $url = $baseUrl . "/v1/adjustment-notes/validate";
    echo "Testing range {$c['range']} ({$c['desc']}) ... ";

    $payload = ['numbering_range_id' => $c['range']];
    if (isset($c['type']))
        $payload['type_document_id'] = $c['type'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $errors = json_decode($response, true)['data']['errors'] ?? [];
    if (isset($errors['numbering_range_id'])) {
        echo "INVALID RANGE\n";
    } else {
        echo "RANGE ACCEPTED (HTTP $httpCode)\n";
        echo "   Response: $response\n";
    }
}
?>