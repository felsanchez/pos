<?php
require_once "modelos/conexion.php";

$sql = "CREATE TABLE IF NOT EXISTS `documentos_soporte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_ds` varchar(20) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `fecha_emision` datetime NOT NULL,
  `metodo_pago` varchar(10) NOT NULL,
  `productos` text NOT NULL,
  `monto_total` decimal(15,2) NOT NULL,
  `estado_dian` varchar(20) DEFAULT 'borrador',
  `cuds` varchar(100) DEFAULT NULL,
  `qr_data` text DEFAULT NULL,
  `pdf_dian` text DEFAULT NULL,
  `xml_dian` text DEFAULT NULL,
  `mensaje_dian` text DEFAULT NULL,
  `factus_id` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

try {
    $stmt = Conexion::conectar()->prepare($sql);
    if ($stmt->execute()) {
        echo "Tabla documentos_soporte creada exitosamente.\n";
    } else {
        echo "Error al crear la tabla.\n";
    }
} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
}
