<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$column = "tipo_persona";
$type = "VARCHAR(20) DEFAULT '2'"; // 1: Juridica, 2: Natural (Default)

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
