<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

$token = $config['access_token'];
$apiUrl = $config['api_url'];

$endpoint = '/v1/bills?per_page=50';
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

$data = json_decode($response, true);
if (isset($data['data']['data'])) {
    echo "=== PENDING/FAILED DRAFTS (status != 1) ===\n";
    $count = 0;
    foreach ($data['data']['data'] as $bill) {
        if ($bill['status'] != 1) {
            $count++;
            echo "ID: {$bill['id']} | Reference: {$bill['reference_code']} | Number: {$bill['number']} | Status: {$bill['status']} | Created: {$bill['created_at']}\n";
            if (!empty($bill['errors'])) {
                echo "  Errors: " . json_encode($bill['errors']) . "\n";
            }
        }
    }
    if ($count == 0) {
        echo "No pending drafts found in the first 50 bills.\n";
    }
} else {
    echo "Could not fetch bills data.\n";
}
