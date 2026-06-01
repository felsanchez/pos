<?php
require_once "config.php";
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $rows = $db->exec("DELETE FROM notas_credito WHERE numero_nota_credito = 'NC71-TEST-DT' OR observacion = 'Test desde simulacion AJAX' OR observacion = 'Observacion de prueba'");
    echo "Cleaned up $rows temporary test rows.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
