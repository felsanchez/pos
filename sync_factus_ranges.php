<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

// Forzar sincronización con la API
echo "Sincronizando rangos de numeración con Factus...\n\n";

$config = ModeloFactus::mdlObtenerConfiguracion();
$token = $config['access_token'];

// Consultar rangos desde la API
$rangosAPI = ModeloFactus::mdlConsultarRangosAPI($token);

if ($rangosAPI === null) {
    die("Error: No se pudieron obtener los rangos desde la API de Factus\n");
}

echo "Rangos obtenidos desde la API:\n";
foreach ($rangosAPI as $rango) {
    echo "  - ID: {$rango['id']}, Prefijo: {$rango['prefix']}, Current: {$rango['current']}, From: {$rango['from']}, To: {$rango['to']}\n";
}

// Guardar rangos en la BD
$resultado = ModeloFactus::mdlGuardarRangos($rangosAPI);
echo "\nRangos sincronizados: {$resultado['actualizados']} actualizados\n";

// Mostrar estado actual en BD
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT id_factus, prefijo, numero_actual, numero_desde, numero_hasta FROM factus_rangos ORDER BY id");
$stmt->execute();
$rangosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nEstado actual en la base de datos local:\n";
foreach ($rangosLocal as $rango) {
    echo "  - ID: {$rango['id_factus']}, Prefijo: {$rango['prefijo']}, Actual: {$rango['numero_actual']}, Desde: {$rango['numero_desde']}, Hasta: {$rango['numero_hasta']}\n";
}

echo "\n✅ Sincronización completada\n";
