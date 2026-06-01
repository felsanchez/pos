<?php
require_once "modelos/conexion.php";
try {
    $stmt = Conexion::conectar()->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES:\n" . implode("\n", $tables) . "\n\n";

    // Let's also check if there are tables starting with factus_
    foreach ($tables as $table) {
        if (strpos($table, 'factus') !== false || strpos($table, 'tribut') !== false || strpos($table, 'documento') !== false) {
            echo "--- Table: $table ---\n";
            $stmtCol = Conexion::conectar()->prepare("DESCRIBE `$table`");
            $stmtCol->execute();
            print_r($stmtCol->fetchAll(PDO::FETCH_ASSOC));
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
