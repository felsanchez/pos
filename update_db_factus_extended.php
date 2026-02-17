<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

$columns = [
    "tributo_emisor" => "VARCHAR(50) DEFAULT 'no_responsable'",
    "actividad_economica" => "VARCHAR(20) NULL",
    "registro_mercantil" => "VARCHAR(50) NULL",
    "dv" => "VARCHAR(2) NULL",
    "responsabilidades_fiscales" => "TEXT NULL" // To store JSON array
];

foreach ($columns as $column => $type) {
    try {
        $stmt = $db->prepare("DESCRIBE factus_config $column");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $sql = "ALTER TABLE factus_config ADD COLUMN $column $type";
            $db->exec($sql);
            echo "Columna $column agregada correctamente.<br>";
        } else {
            echo "Columna $column ya existe.<br>";
        }
    } catch (Exception $e) {
        echo "Error con columna $column: " . $e->getMessage() . "<br>";
    }
}

echo "Actualización de base de datos completada.";
