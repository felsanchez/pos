<?php
require_once "modelos/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    // 1. Crear tabla productos_variantes_bodegas
    $pdo->exec("CREATE TABLE IF NOT EXISTS productos_variantes_bodegas (
        id_variante INT(11) NOT NULL,
        id_bodega INT(11) NOT NULL,
        stock INT(11) NOT NULL DEFAULT 0,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id_variante, id_bodega),
        FOREIGN KEY (id_variante) REFERENCES productos_variantes(id) ON DELETE CASCADE,
        FOREIGN KEY (id_bodega) REFERENCES bodegas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    echo "Tabla productos_variantes_bodegas creada/verificada.\n";
    
    // 2. Migrar stock de productos_variantes a la bodega principal (id_bodega = 1)
    // Solo si no existen registros ya.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos_variantes_bodegas");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO productos_variantes_bodegas (id_variante, id_bodega, stock)
                    SELECT id, 1, stock FROM productos_variantes");
        echo "Stock de variantes migrado a la bodega principal.\n";
    } else {
        echo "La migración de variantes ya fue realizada previamente o la tabla no está vacía.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
