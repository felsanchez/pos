<?php
require_once "modelos/conexion.php";
try {
    $stmt = Conexion::conectar()->prepare("SELECT id, codigo, numero_factura, estado, estado_dian, orden_compra FROM ventas ORDER BY id DESC LIMIT 2");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
