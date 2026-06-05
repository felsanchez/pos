<?php
require_once __DIR__ . "/../modelos/conexion.php";

$db = Conexion::conectar();

$alters = [
    // 1. cajas_turnos
    "ALTER TABLE cajas_turnos DROP FOREIGN KEY cajas_turnos_ibfk_1",
    "ALTER TABLE cajas_turnos ADD CONSTRAINT cajas_turnos_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE CASCADE",

    // 2. gastos
    "ALTER TABLE gastos DROP FOREIGN KEY gastos_ibfk_2",
    "ALTER TABLE gastos ADD CONSTRAINT gastos_ibfk_2 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE CASCADE",

    // 3. notas_credito
    "ALTER TABLE notas_credito DROP FOREIGN KEY notas_credito_ibfk_2",
    "ALTER TABLE notas_credito ADD CONSTRAINT notas_credito_ibfk_2 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE CASCADE",

    // 4. traslados
    "ALTER TABLE traslados DROP FOREIGN KEY traslados_ibfk_3",
    "ALTER TABLE traslados ADD CONSTRAINT traslados_ibfk_3 FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE CASCADE"
];

foreach ($alters as $sql) {
    try {
        $db->exec($sql);
        echo "SUCCESS: $sql\n";
    } catch (Exception $e) {
        echo "FAILED: $sql - " . $e->getMessage() . "\n";
    }
}
