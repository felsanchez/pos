<?php
require_once "modelos/conexion.php";

$sql = file_get_contents("sql/crear_tabla_notas_credito.sql");

try {
    $conn = Conexion::conectar();
    $conn->exec($sql);
    echo "✅ Tabla 'notas_credito' creada exitosamente\n";
} catch (PDOException $e) {
    echo "❌ Error al crear tabla: " . $e->getMessage() . "\n";
}
?>