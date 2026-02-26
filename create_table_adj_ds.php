<?php
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();

    $sql = "CREATE TABLE IF NOT EXISTS `notas_ajuste_ds` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_ds_original` int(11) NOT NULL,
        `numero_ds_original` varchar(50) NOT NULL,
        `tipo_nota` varchar(10) NOT NULL,
        `motivo` text,
        `productos` text NOT NULL,
        `monto_total` decimal(15,2) NOT NULL,
        `estado_dian` varchar(20) DEFAULT 'borrador',
        `numero_nota_ajuste` varchar(50) DEFAULT NULL,
        `cuds_ajuste` varchar(150) DEFAULT NULL,
        `qr_data` text,
        `xml_dian` text,
        `pdf_dian` text,
        `mensaje_dian` text,
        `fecha_envio_dian` datetime DEFAULT NULL,
        `id_usuario` int(11) NOT NULL,
        `id_proveedor` int(11) NOT NULL,
        `observacion` text,
        `metodo_pago` varchar(100) DEFAULT NULL,
        `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `id_ds_original` (`id_ds_original`),
        KEY `id_proveedor` (`id_proveedor`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $db->exec($sql);
    echo "Tabla 'notas_ajuste_ds' creada o ya existía correctamente.\n";

} catch (PDOException $e) {
    echo "Error al crear la tabla: " . $e->getMessage() . "\n";
}
?>