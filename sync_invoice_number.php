<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Verificar el estado actual
$stmt = $db->query("SELECT id_factus, prefijo, numero_actual FROM factus_rangos WHERE activo = 1");
$rango = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Rango activo actual:\n";
echo "  Prefijo: {$rango['prefijo']}\n";
echo "  Número actual en BD: {$rango['numero_actual']}\n";
echo "  Factus espera: 29\n\n";

// Ajustar al número correcto
$stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = 28 WHERE id_factus = :id");
$stmt->bindParam(':id', $rango['id_factus']);
$stmt->execute();

echo "✅ Ajuste completado:\n";
echo "  Número actual actualizado a: 28\n";
echo "  La próxima factura será: 29\n";
