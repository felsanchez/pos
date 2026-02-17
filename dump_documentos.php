<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_tipos_documento");
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($docs);