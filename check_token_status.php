<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_config WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

print_r($config);

if (empty($config['access_token'])) {
    echo "NO TOKEN FOUND\n";
} else {
    echo "Has Token. Expires: " . $config['expires_at'] . "\n";
    echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
}
