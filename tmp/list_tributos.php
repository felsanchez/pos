<?php
require_once 'modelos/conexion.php';
$stmt = Conexion::conectar()->prepare("SELECT id, nombre, porcentaje_defecto FROM factus_tributos");
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
?>