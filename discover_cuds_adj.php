<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';

session_start();
$_SESSION['id'] = 14;
$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];

$refs = ["NA-SEDS984000003-1772082334", "NA-SEDS984000000-1772082389"];

foreach ($refs as $ref) {
    echo "--- Buscando Ref: $ref ---\n";

    $endpoints = [
        "https://api-sandbox.factus.com.co/v1/bills?filter[reference_code]=" . $ref,
        "https://api-sandbox.factus.com.co/v1/adjustment-notes?filter[reference_code]=" . $ref,
        "https://api-sandbox.factus.com.co/v1/support-documents/adjustments?filter[reference_code]=" . $ref,
        "https://api-sandbox.factus.com.co/v1/bills/" . $ref,
        "https://api-sandbox.factus.com.co/v1/adjustment-notes/" . $ref
    ];

    foreach ($endpoints as $url) {
        echo "URL: $url ... ";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
        $res = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo "HTTP $http\n";

        if ($http == 200) {
            $json = json_decode($res, true);
            // Si es un listado, buscar en data.data o data
            $items = $json['data']['data'] ?? $json['data'] ?? [];
            if (isset($items[0]))
                $items = [$items[0]]; // solo el primero si es lista

            foreach ((array) $items as $item) {
                if (isset($item['cuds']) || isset($item['qr']) || isset($item['public_url'])) {
                    echo "ENCONTRADO: CUDS=" . ($item['cuds'] ?? 'N/A') . " QR=" . ($item['qr'] ?? 'N/A') . "\n";
                    print_r($item);
                }
            }
        }
    }
}
