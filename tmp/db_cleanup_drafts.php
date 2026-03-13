<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "=== INICIANDO LIMPIEZA DE BORRADORES ANOMALOS ===\n";

// 1. Registros en 'aceptada' sin número (Inconsistentes)
$stmt1 = $db->prepare("DELETE FROM ventas WHERE (numero_factura IS NULL OR numero_factura = '') AND estado_dian = 'aceptada'");
$stmt1->execute();
echo " - Registros 'aceptada' sin numero eliminados: " . $stmt1->rowCount() . "\n";

// 2. Registros con estado vacio y sin numero (Basura antigua)
$stmt2 = $db->prepare("DELETE FROM ventas WHERE (numero_factura IS NULL OR numero_factura = '') AND (estado_dian IS NULL OR estado_dian = '') AND (resolucion_id IS NOT NULL AND resolucion_id != '')");
$stmt2->execute();
echo " - Registros sin estado y sin numero eliminados: " . $stmt2->rowCount() . "\n";

// 3. Registros 'rechazada' antiguos (Basura de pruebas previas)
$stmt3 = $db->prepare("DELETE FROM ventas WHERE (numero_factura IS NULL OR numero_factura = '') AND estado_dian = 'rechazada'");
$stmt3->execute();
echo " - Registros 'rechazada' sin numero eliminados: " . $stmt3->rowCount() . "\n";

echo "=== LIMPIEZA COMPLETADA ===\n";
?>