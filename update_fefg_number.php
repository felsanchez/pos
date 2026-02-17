<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Actualizar el número actual del rango FEFG a 28
$stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = 28 WHERE id_factus = 1040");
$stmt->execute();

echo "✅ Número actualizado correctamente\n";
echo "   Rango: FEFG (ID: 1040)\n";
echo "   Número actual: 28\n";
echo "   Próxima factura: 29\n";
