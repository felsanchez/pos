<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT api_url, ambiente, client_id FROM factus_config WHERE id = 1");
$stmt->execute();
$config = $stmt->fetch();

echo "=== CONFIGURACIÓN FACTUS ===\n";
echo "API URL: " . $config['api_url'] . "\n";
echo "Ambiente: " . $config['ambiente'] . "\n";
echo "Client ID: " . substr($config['client_id'], 0, 20) . "...\n";
