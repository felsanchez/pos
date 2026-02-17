<?php
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT access_token, refresh_token, token_expiracion FROM factus_config WHERE id = 1");
$stmt->execute();
$config = $stmt->fetch();

echo "=== ESTADO DE TOKENS ===\n";
echo "Access Token: " . (empty($config['access_token']) ? "VACÍO" : substr($config['access_token'], 0, 50) . "...") . "\n";
echo "Refresh Token: " . (empty($config['refresh_token']) ? "VACÍO" : substr($config['refresh_token'], 0, 50) . "...") . "\n";
echo "Expiración: " . ($config['token_expiracion'] ?? "NO DEFINIDA") . "\n";
echo "Hora actual: " . date('Y-m-d H:i:s') . "\n";

if (!empty($config['token_expiracion'])) {
    $expira = strtotime($config['token_expiracion']);
    $ahora = time();
    if ($expira > $ahora) {
        $diff = $expira - $ahora;
        echo "Estado: VÁLIDO (expira en " . round($diff / 60) . " minutos)\n";
    } else {
        echo "Estado: EXPIRADO (hace " . round(($ahora - $expira) / 60) . " minutos)\n";
    }
}
