<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_tipos_documento");
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
