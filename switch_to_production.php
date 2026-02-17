<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "=== CAMBIO A AMBIENTE DE PRODUCCIÓN ===\n\n";

// Actualizar ambiente a producción
$stmt = $db->prepare("UPDATE factus_config SET ambiente = 'produccion', api_url = 'https://api.factus.com.co' WHERE id = 1");
$stmt->execute();

echo "✅ Configuración actualizada:\n";
echo "   - Ambiente: PRODUCCIÓN\n";
echo "   - API URL: https://api.factus.com.co\n\n";

echo "⚠️ IMPORTANTE - PRÓXIMOS PASOS:\n";
echo "1. Debes autenticarte nuevamente con las credenciales de PRODUCCIÓN\n";
echo "2. Ve a: Configuración > Factus\n";
echo "3. Ingresa tus credenciales de producción (client_id y client_secret)\n";
echo "4. Haz clic en 'Autenticar'\n";
echo "5. Sincroniza los rangos de numeración de producción\n\n";

echo "⚠️ RECORDATORIO:\n";
echo "- Estás en PRODUCCIÓN - Las facturas son REALES y van a la DIAN\n";
echo "- Asegúrate de tener la habilitación DIAN activa\n";
echo "- Verifica que los rangos de numeración sean correctos\n";
echo "- Las facturas de producción NO se pueden eliminar\n";
