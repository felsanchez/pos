<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    // 1. Deactivate INC Bolsas (ID 4 / Código 22)
    $stmt1 = $db->prepare("UPDATE factus_tributos SET activo = 0 WHERE codigo = '22'");
    $stmt1->execute();
    echo "Deactivated " . $stmt1->rowCount() . " INC Bolsas tributes.\n";
    
    // 2. Rename IVA Excluido (ID 5 / Código ZA) to (IVA) 0%
    $stmt2 = $db->prepare("UPDATE factus_tributos SET nombre = '(IVA) 0%' WHERE codigo = 'ZA'");
    $stmt2->execute();
    echo "Renamed " . $stmt2->rowCount() . " IVA Excluido tributes to '(IVA) 0%'.\n";
    
    // 3. Print current active tributes
    $stmt3 = $db->prepare("SELECT id, codigo, nombre, porcentaje_defecto, activo FROM factus_tributos");
    $stmt3->execute();
    $rows = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== Current Tributes state ===\n";
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | Código: {$row['codigo']} | Nombre: {$row['nombre']} | Porcentaje: {$row['porcentaje_defecto']} | Activo: {$row['activo']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
