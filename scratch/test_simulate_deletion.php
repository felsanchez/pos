<?php
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/factus.modelo.php";

echo "1. Current consecutive: ";
$current = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus(true);
echo "$current\n";

echo "2. Simulating deletion of ID 86 (990000314)...";
$db = Conexion::conectar();
$stmt = $db->prepare("UPDATE ventas SET estado = 'anulada' WHERE id = 86");
$stmt->execute();
echo " Done.\n";

echo "3. Consecutive after deleting 990000314: ";
$after = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus(true);
echo "$after\n";

echo "4. Restoring ID 86 back to 'venta'...";
$stmt = $db->prepare("UPDATE ventas SET estado = 'venta' WHERE id = 86");
$stmt->execute();
echo " Done.\n";

echo "5. Consecutive after restoring: ";
$restored = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus(true);
echo "$restored\n";
