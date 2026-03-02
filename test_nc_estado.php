<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT id, numero_nota_credito, estado_dian FROM notas_credito ORDER BY id DESC LIMIT 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>