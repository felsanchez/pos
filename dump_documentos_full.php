<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->query("SELECT * FROM factus_tipos_documento ORDER BY id ASC");
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents("documentos_dump_full.json", json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Dump guardado en documentos_dump_full.json";
