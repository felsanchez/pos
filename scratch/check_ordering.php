<?php
require_once "modelos/conexion.php";
try {
    $where = " WHERE 1=1 ";
    $order = " ORDER BY g.fecha DESC, g.id DESC";
    $limit = " LIMIT 0, 10";
    $stmt = Conexion::conectar()->prepare("SELECT g.id, g.fecha, g.concepto, g.numero_comprobante
                                            FROM gastos g 
                                            LEFT JOIN categorias_gastos c ON g.id_categoria_gasto = c.id 
                                            LEFT JOIN usuarios u ON g.id_usuario = u.id 
                                            LEFT JOIN proveedores p ON g.id_proveedor = p.id 
                                            LEFT JOIN bodegas b ON g.id_bodega = b.id
                                            $where $order $limit");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
