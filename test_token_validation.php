<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

echo "=== TEST DE VALIDACIÓN DE TOKEN ===\n\n";

// Test 1: ¿Está expirado?
$expirado = ModeloFactus::mdlTokenExpirado();
echo "1. mdlTokenExpirado(): " . ($expirado ? "SÍ (EXPIRADO)" : "NO (VÁLIDO)") . "\n";

// Test 2: ¿Puede obtener token?
$token = ModeloFactus::mdlObtenerAccessToken();
echo "2. mdlObtenerAccessToken(): " . (empty($token) ? "VACÍO" : substr($token, 0, 30) . "...") . "\n";

// Test 3: ¿Puede garantizar token válido?
$tokenValido = ModeloFactus::mdlGarantizarTokenValido();
echo "3. mdlGarantizarTokenValido(): " . (empty($tokenValido) ? "VACÍO/FALSE" : substr($tokenValido, 0, 30) . "...") . "\n";

// Test 4: Verificar expiración manualmente
$stmt = Conexion::conectar()->prepare("SELECT token_expiracion FROM factus_config WHERE id = 1");
$stmt->execute();
$config = $stmt->fetch();
echo "\n4. Verificación manual:\n";
echo "   Token expira: " . $config['token_expiracion'] . "\n";
echo "   Hora actual:  " . date('Y-m-d H:i:s') . "\n";
echo "   ¿Expirado?: " . (strtotime($config['token_expiracion']) < time() ? "SÍ" : "NO") . "\n";
