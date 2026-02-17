<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT id_factus, prefijo, numero_actual FROM factus_rangos WHERE prefijo = 'SETP'");
$stmt->execute();
$rango = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Rango SETP en BD local:\n";
echo "  ID Factus: {$rango['id_factus']}\n";
echo "  Número actual: {$rango['numero_actual']}\n\n";

// Consultar desde API
$config = ModeloFactus::mdlObtenerConfiguracion();
$token = $config['access_token'];
$rangosAPI = ModeloFactus::mdlConsultarRangosAPI($token);

foreach ($rangosAPI as $r) {
    if ($r['prefix'] == 'SETP') {
        echo "Rango SETP en API de Factus:\n";
        echo "  ID: {$r['id']}\n";
        echo "  Current: {$r['current']}\n";
        echo "  From: {$r['from']}\n";
        echo "  To: {$r['to']}\n";
        break;
    }
}
