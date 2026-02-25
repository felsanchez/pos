<?php
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();

    // Add columns if they don't exist
    $columns = [
        "tipo_descuento VARCHAR(20) DEFAULT NULL",
        "valor_descuento DECIMAL(10,2) DEFAULT 0.00",
        "monto_descuento DECIMAL(10,2) DEFAULT 0.00",
        "retenciones TEXT DEFAULT NULL"
    ];

    foreach ($columns as $column) {
        $colName = explode(" ", $column)[0];
        // Check if column exists
        $check = $db->query("SHOW COLUMNS FROM documentos_soporte LIKE '$colName'");
        if ($check->rowCount() == 0) {
            $db->exec("ALTER TABLE documentos_soporte ADD COLUMN $column");
            echo "Column $colName added successfully.\n";
        } else {
            echo "Column $colName already exists.\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
