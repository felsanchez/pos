<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT id, codigo, estado_dian, mensaje_dian, cufe, xml_dian, pdf_dian FROM ventas ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | Codigo: {$row['codigo']} | Estado DIAN: {$row['estado_dian']}\n";
        echo "  Mensaje DIAN: {$row['mensaje_dian']}\n";
        echo "  CUFE: {$row['cufe']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
