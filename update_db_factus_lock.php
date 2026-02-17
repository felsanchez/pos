<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$column = "bloqueo_datos_emisor";
$type = "TINYINT(1) DEFAULT 1"; // 1: Bloqueado (Default), 0: Desbloqueado

try {
    $stmt = $db->prepare("DESCRIBE factus_config $column");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $sql = "ALTER TABLE factus_config ADD COLUMN $column $type";
        $db->exec($sql);
        echo "Columna $column agregada correctamente.";
    } else {
        echo "Columna $column ya existe.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
