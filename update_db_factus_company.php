<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

$columns = [
    "nombre_empresa" => "VARCHAR(255) NULL",
    "nit_empresa" => "VARCHAR(50) NULL",
    "direccion_empresa" => "TEXT NULL",
    "telefono_empresa" => "VARCHAR(50) NULL",
    "email_empresa" => "VARCHAR(100) NULL",
    "municipio_id" => "VARCHAR(20) DEFAULT '169'"
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
