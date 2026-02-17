<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "Iniciando migración de tipos de documento...\n";

// 1. BACKUP
echo "1. Realizando backup de tabla clientes...\n";
$stmt = $db->query("SELECT * FROM clientes");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$backupFile = "backups/clientes_backup_" . date('Ymd_His') . ".json";
if (!is_dir('backups'))
    mkdir('backups');
file_put_contents($backupFile, json_encode($clientes));
echo "Backup guardado en $backupFile (" . count($clientes) . " registros)\n";

// 2. MIGRACIÓN
$map = [
    1 => 3, // CC (Old 1) -> CC (New 3)
    2 => 5, // CE (Old 2) -> CE (New 5)
    3 => 6, // NIT (Old 3) -> NIT (New 6)
    4 => 7, // Pasaporte (Old 4) -> Pasaporte (New 7)
    5 => 2, // TI (Old 5) -> TI (New 2)
    6 => 1  // RC (Old 6) -> RC (New 1)
];

echo "2. Ejecutando migración en BD...\n";
$db->beginTransaction();

try {
    // Paso A: Mover a rango temporal para evitar colisiones
    echo "  - Moviendo IDs actuales a rango temporal (+100)...\n";
    $db->exec("UPDATE clientes SET tipo_documento_id = tipo_documento_id + 100 WHERE tipo_documento_id BETWEEN 1 AND 6");

    // Paso B: Mapear desde rango temporal a nuevos IDs
    echo "  - Reasignando a nuevos IDs de Factus...\n";
    foreach ($map as $old => $new) {
        $temp = $old + 100;
        $rows = $db->exec("UPDATE clientes SET tipo_documento_id = $new WHERE tipo_documento_id = $temp");
        echo "    - Old $old (Temp $temp) -> New $new: $rows registros actualizados.\n";
    }

    // Paso C: Verificar huérfanos
    $stmtCheck = $db->query("SELECT COUNT(*) as count FROM clientes WHERE tipo_documento_id > 100");
    $orphan = $stmtCheck->fetch()['count'];

    if ($orphan > 0) {
        throw new Exception("Error: Quedaron $orphan registros en rango temporal (>100). Abortando.");
    }

    $db->commit();
    echo "3. Migración completada con éxito.\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Se ha revertido la transacción.\n";
}
