<?php
require_once "modelos/conexion.php";

try {
    $pdo = Conexion::conectar();

    // Check if columns exist
    $stmt = $pdo->prepare("SHOW COLUMNS FROM ventas LIKE 'seguimiento_recibido'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ventas ADD COLUMN seguimiento_recibido TINYINT(1) DEFAULT 0");
        echo "Columna 'seguimiento_recibido' agregada correctamente.<br>";
    } else {
        echo "Columna 'seguimiento_recibido' ya existe.<br>";
    }

    $stmt = $pdo->prepare("SHOW COLUMNS FROM ventas LIKE 'seguimiento_procesado'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ventas ADD COLUMN seguimiento_procesado TINYINT(1) DEFAULT 0");
        echo "Columna 'seguimiento_procesado' agregada correctamente.<br>";
    } else {
        echo "Columna 'seguimiento_procesado' ya existe.<br>";
    }

    // NEW COLUMN: seguimiento_alistado
    $stmt = $pdo->prepare("SHOW COLUMNS FROM ventas LIKE 'seguimiento_alistado'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE ventas ADD COLUMN seguimiento_alistado TINYINT(1) DEFAULT 0");
        echo "Columna 'seguimiento_alistado' agregada correctamente.<br>";
    } else {
        echo "Columna 'seguimiento_alistado' ya existe.<br>";
    }

    echo "Actualización de base de datos completada.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>