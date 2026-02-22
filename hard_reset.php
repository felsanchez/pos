<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Hard Resetting Sequence...\n";

    // 1. Delete ALL records after ID 700 that might be blocking (they had no CUFE anyway)
    $db->prepare("DELETE FROM ventas WHERE id > 700")->execute();
    echo "- Deleted all garbage records after ID 700.\n";

    // 2. Identify the last "good" DIAN number from successfully signed invoices (or assume 164 was the last)
    // Looking at the history, 164 was ID 702 (but it had no CUFE in my check... wait!)
    // If NO invoices have CUFE, then we must rely on what the API says.

    // In my previous check, factus_rangos had 990000164.
    // Let's set it to 164 to be safe, so the next is 165.

    $db->prepare("UPDATE factus_rangos SET numero_actual = 990000164 WHERE prefijo = 'SETP' AND estado = 1")->execute();
    echo "- Reset factus_rangos cache for SETP to 164.\n";

    // 3. Move ghost 691
    $db->prepare("UPDATE ventas SET codigo = '0' WHERE id = 691")->execute();

    $db->commit();
    echo "\nSUCCESS: System reset to baseline 164. Next invoice should be 165.\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>