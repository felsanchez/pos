<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

echo "=== OPCIONES PARA SOLUCIONAR EL PROBLEMA ===\n\n";

// Obtener configuración actual
$config = ModeloFactus::mdlObtenerConfiguracion();
$token = $config['access_token'];

// Consultar rangos disponibles
echo "1. Rangos de numeración disponibles en Factus:\n";
$rangos = ModeloFactus::mdlConsultarRangosAPI($token);

if ($rangos) {
    foreach ($rangos as $rango) {
        $activo = ($rango['current'] < $rango['to']) ? "✅ ACTIVO" : "❌ AGOTADO";
        echo "   - Prefijo: {$rango['prefix']}, Current: {$rango['current']}, To: {$rango['to']} {$activo}\n";
    }
} else {
    echo "   ❌ No se pudieron obtener los rangos\n";
}

echo "\n2. OPCIONES:\n";
echo "   A) Usar un rango diferente si existe (ej: FE, SETT, etc.)\n";
echo "   B) Esperar a que Factus solucione el problema con SETP\n";
echo "   C) Cambiar a ambiente de producción (si ya tienes habilitación DIAN)\n";

echo "\n3. IMPORTANTE:\n";
echo "   - Las facturas 138-139 quedaron 'perdidas' en Factus\n";
echo "   - NO se pueden borrar facturas electrónicas (ley DIAN)\n";
echo "   - El rango SETP está bloqueado en el número 135\n";
echo "   - Necesitas que Factus lo corrija manualmente\n";
