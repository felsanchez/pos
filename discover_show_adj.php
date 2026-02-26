<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];

$idFactus = 84;
$endpoints = [
    "/v1/bills/" . $idFactus,
    "/v1/support-documents/" . $idFactus,
    "/v1/adjustment-notes/" . $idFactus,
    "/v1/bills/show/" . $idFactus,
    "/v1/support-documents/show/" . $idFactus
];

foreach ($endpoints as $e) {
    echo "Probando $e ... ";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-sandbox.factus.com.co" . $e);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "HTTP $http\n";
    if ($http == 200) {
        echo "EXITO: " . substr($res, 0, 100) . "...\n";
    }
}
