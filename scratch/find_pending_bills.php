<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$token = $config['access_token'];
$apiUrl = $config['api_url'];

$endpoint = '/v1/bills';
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $apiUrl . $endpoint,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_TIMEOUT => 10,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
  ),
));

$response = curl_exec($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpcode == 200) {
    $data = json_decode($response, true);
    if (isset($data['data']['data'])) {
        // Pagination data has data nested inside data!
        foreach ($data['data']['data'] as $bill) {
            echo "Bill ID: " . $bill['id'] . " | Reference: " . $bill['reference_code'] . " | Number: " . ($bill['number'] ?? 'N/A') . " | Status: " . $bill['status'] . " | Date: " . $bill['created_at'] . "\n";
            if (!empty($bill['errors'])) {
                echo "  Errors: " . json_encode($bill['errors']) . "\n";
            }
        }
    } else {
        echo "No data.data field in response, dumping response:\n";
        print_r($data);
    }
} else {
    echo "Error HTTP $httpcode: $response\n";
}
