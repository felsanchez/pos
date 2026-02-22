<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Cleaning up ghost records (No CUFE)...\n";

    // 1. Identify records after 164 that have no CUFE
    $stmt = $db->prepare("DELETE FROM ventas WHERE id > 700 AND (cufe IS NULL OR cufe = '')");
    $stmt->execute();
    echo "- Deleted failed attempts after ID 700.\n";

    // 2. Align local codigo sequence to the last SUCCESSFUL signature
    $stmt = $db->prepare("SELECT numero_factura FROM ventas WHERE cufe IS NOT NULL AND cufe != '' AND numero_factura LIKE '%SETP%' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($last) {
        preg_match('/(\d+)$/', $last['numero_factura'], $matches);
        if (isset($matches[1])) {
            $lastNum = intval($matches[1]);
            echo "- Last successful DIAN number: $lastNum\n";

            // Advance the cache in factus_rangos
            $db->prepare("UPDATE factus_rangos SET numero_actual = :num WHERE prefijo = 'SETP' AND estado = 1")->execute([':num' => $lastNum]);
            echo "- Updated factus_rangos cache to $lastNum.\n";
        }
    }

    $db->commit();
    echo "\nSUCCESS: Clean state restored. Next available should be " . ($lastNum + 1) . "\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>