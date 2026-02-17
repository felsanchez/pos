<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Actualizar el número actual del rango SETP a 990000140 para saltar los números problemáticos
$stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = 990000140 WHERE id_factus = 1190");
$stmt->execute();

echo "✅ Número actual actualizado a 990000140\n";
echo "La próxima factura usará el número 990000141\n";
