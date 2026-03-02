<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();

    // Check current ENUM values
    $stmt = $db->query("SHOW COLUMNS FROM notas_credito LIKE 'estado_dian'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    $type = $col['Type'];
    echo "Current Type: " . $type . "\n";

    // If it doesn't contain borrador, add it
    if (strpos($type, 'borrador') === false) {
        $newType = str_replace(")", ",'borrador')", $type);
        $db->exec("ALTER TABLE notas_credito MODIFY COLUMN estado_dian " . $newType . " DEFAULT 'pendiente'");
        echo "Successfully altered table to: " . $newType . "\n";

        // Fix the empty states that were meant to be borrador
        $db->exec("UPDATE notas_credito SET estado_dian = 'borrador' WHERE estado_dian = ''");
        echo "Updated empty states to borrador\n";
    } else {
        echo "Already contains borrador.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>