<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();
$stmt = $db->query("DESCRIBE factus_tributos");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
?>