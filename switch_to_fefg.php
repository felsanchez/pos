<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Marcar SETP como inactivo
$stmt = $db->prepare("UPDATE factus_rangos SET activo = 0 WHERE id_factus = 1190");
$stmt->execute();

// Marcar FEFG como activo
$stmt = $db->prepare("UPDATE factus_rangos SET activo = 1 WHERE id_factus = 1040");
$stmt->execute();

echo "✅ Cambio de rango completado:\n";
echo "   - SETP (ID 1190): DESACTIVADO (corrupto en Factus)\n";
echo "   - FEFG (ID 1040): ACTIVADO (122 facturas disponibles)\n\n";
echo "IMPORTANTE:\n";
echo "- Las próximas facturas usarán el prefijo FEFG\n";
echo "- Tienes 122 facturas disponibles con este rango\n";
echo "- Cuando Factus solucione SETP, puedes volver a activarlo\n";
echo "- Contacta a Factus para que corrijan el rango SETP\n";
