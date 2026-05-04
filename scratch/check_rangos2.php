<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE is_active = 1 OR estado = 1 OR id_factus IS NOT NULL LIMIT 1");
$stmt->execute();
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
?>
