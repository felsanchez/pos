<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();
$stmt = $db->query("SELECT id, numero_ds, factus_id FROM documentos_soporte WHERE factus_id IS NOT NULL ORDER BY id DESC LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
