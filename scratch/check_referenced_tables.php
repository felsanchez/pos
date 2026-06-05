<?php
require_once __DIR__ . "/../modelos/conexion.php";

$db = Conexion::conectar();

$tables = ['cajas_turnos', 'gastos', 'notas_credito', 'traslados'];

foreach ($tables as $table) {
    try {
        $stmt = $db->prepare("SHOW CREATE TABLE $table");
        $stmt->execute();
        $res = $stmt->fetch();
        echo "=== Schema for '$table' ===\n";
        echo $res[1] . "\n\n";
    } catch (Exception $e) {
        echo "Could not get schema for '$table': " . $e->getMessage() . "\n\n";
    }
}
