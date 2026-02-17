<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT access_token FROM factus_config WHERE id = 1");
$stmt->execute();
$res = $stmt->fetch();
$token = $res['access_token'];

echo "=== JWT TOKEN DECODE ===\n";
$parts = explode('.', $token);
if (count($parts) != 3) {
    echo "No es un JWT válido.\n";
    exit;
}

$header = json_decode(base64_decode($parts[0]), true);
$payload = json_decode(base64_decode($parts[1]), true);

echo "HEADER: " . print_r($header, true) . "\n";
echo "PAYLOAD: " . print_r($payload, true) . "\n";
