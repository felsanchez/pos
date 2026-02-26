<?php
require_once "modelos/conexion.php";
$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_rangos");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
?>