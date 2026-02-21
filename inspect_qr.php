<?php
require_once "modelos/conexion.php";
echo "=== Last NC qr_data_nc field ===\n";
$stmt2 = Conexion::conectar()->query("SELECT id, qr_data_nc, xml_dian_nc FROM notas_credito ORDER BY id DESC LIMIT 1");
$row = $stmt2->fetch(PDO::FETCH_ASSOC);
print_r($row);
?>