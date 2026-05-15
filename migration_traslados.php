<?php
require_once "modelos/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    // 1. Tabla de Traslados (Cabecera)
    $pdo->exec("CREATE TABLE IF NOT EXISTS traslados (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(20) NOT NULL,
        id_bodega_origen INT(11) NOT NULL,
        id_bodega_destino INT(11) NOT NULL,
        id_usuario INT(11) NOT NULL,
        total_items INT(11) NOT NULL DEFAULT 0,
        estado ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
        notas TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_completado DATETIME NULL,
        FOREIGN KEY (id_bodega_origen) REFERENCES bodegas(id),
        FOREIGN KEY (id_bodega_destino) REFERENCES bodegas(id),
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    echo "Tabla traslados creada.\n";
    
    // 2. Tabla de Traslados Items (Detalle)
    $pdo->exec("CREATE TABLE IF NOT EXISTS traslados_items (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        id_traslado INT(11) NOT NULL,
        tipo_producto ENUM('producto', 'variante') NOT NULL,
        id_producto INT(11) NOT NULL,
        id_variante INT(11) DEFAULT NULL,
        cantidad INT(11) NOT NULL,
        FOREIGN KEY (id_traslado) REFERENCES traslados(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    echo "Tabla traslados_items creada.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
