<?php
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

// Obtener token actual
$db = Conexion::conectar();
$stmt = $db->query("SELECT access_token FROM factus_config LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
$token = $config['access_token'];

echo "Consultando medios de pago a Factus API...\n";

// Intentar endpoint común de referencias
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.factus.com.co/v1/payment-methods', // Endpoint hipotético
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode == 404) {
    // Probar otro endpoint si falla
    echo "\nIntentando endpoint alternativo /v1/references/payment_methods...\n";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.factus.com.co/v1/references/payment_methods',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $token, 'Accept: application/json'),
    ));
    $response = curl_exec($curl);
    echo "Response: $response\n";
}
?>