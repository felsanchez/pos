<?php
require_once __DIR__ . '/../modelos/conexion.php';

try {
    $db = Conexion::conectar();
    
    // Count all municipalities
    $stmtCount = $db->prepare("SELECT COUNT(*) FROM factus_municipios");
    $stmtCount->execute();
    $total = $stmtCount->fetchColumn();
    
    // Count active municipalities
    $stmtActive = $db->prepare("SELECT COUNT(*) FROM factus_municipios WHERE activo = 1");
    $stmtActive->execute();
    $active = $stmtActive->fetchColumn();
    
    echo "Total municipalities in table: {$total}\n";
    echo "Active municipalities (activo = 1): {$active}\n";
    
    if ($active == 0 && $total > 0) {
        echo "No active municipalities found. Let's see some entries:\n";
        $stmtEntries = $db->prepare("SELECT id, id_factus, nombre, activo FROM factus_municipios LIMIT 5");
        $stmtEntries->execute();
        $entries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
        foreach ($entries as $entry) {
            echo "ID: {$entry['id']} | FactusID: {$entry['id_factus']} | Name: {$entry['nombre']} | Active: {$entry['activo']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
