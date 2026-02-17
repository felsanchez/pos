<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

// Aumentar tiempo de ejecución
set_time_limit(300);

$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_config WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config || empty($config['client_id']) || empty($config['client_secret'])) {
    die("No hay credenciales configuradas en la base de datos id=1\n");
}

$baseUrl = $config['api_url']; // https://api.factus.com.co (Producción)
if (empty($baseUrl))
    $baseUrl = 'https://api.factus.com.co';

echo "Autenticando en: $baseUrl\n";

// 1. Obtener Token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/oauth/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret']
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    die("Error de autenticación: HTTP $httpCode - Response: " . substr($response, 0, 200) . "\n");
}

$auth = json_decode($response, true);
$token = $auth['access_token'];
echo "Token obtenido exitosamente.\n\n";

// 2. Probar endpoints de tipos de documentos
$endpoints = [
    '/v1/identification-types',
    '/v1/identifications',
    '/v1/document-types',
    '/v1/references/identification-types',
    '/v1/references/identifications'
];

foreach ($endpoints as $endpoint) {
    echo "Probando: $endpoint ... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP $httpCode\n";

    if ($httpCode == 200) {
        echo "¡ÉXITO! Respuesta encontrada:\n";
        $json = json_decode($response, true);

        // Imprimir datos de forma legible
        if (isset($json['data'])) {
            $data = $json['data'];
            // Si es paginado
            if (isset($data['data'])) {
                $data = $data['data'];
            }

            foreach ($data as $item) {
                echo "ID: " . ($item['id'] ?? '?') .
                    " | Code: " . ($item['code'] ?? '?') .
                    " | Name: " . ($item['name'] ?? '?') . "\n";
            }
        } else {
            print_r($json);
        }
        break; // Detener al encontrar
    }
}
