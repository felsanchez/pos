<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT na.id, na.id_ds_original, ds.id_bodega FROM notas_ajuste_ds na LEFT JOIN documentos_soporte ds ON na.id_ds_original = ds.id LIMIT 5");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data, JSON_PRETTY_PRINT);
