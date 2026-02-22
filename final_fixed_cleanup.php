<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Final Cleanup of 167 Duplicates...\n";

    // 1. Delete all records that have 167 but are essentially duplicates
    // Keep ID 715 as the "official" (first) 167 attempt, delete others
    $db->prepare("DELETE FROM ventas WHERE id > 716 AND (numero_factura LIKE '%167' OR codigo LIKE '%167') AND (cufe IS NULL OR cufe = '')")->execute();
    echo "- Deleted duplicate 167 drafts after ID 716.\n";

    // 2. Align ID 716 as the last good one (168)
    $db->prepare("UPDATE ventas SET codigo = '990000168' WHERE id = 716")->execute();
    echo "- Confirmed ID 716 as 168.\n";

    // 3. Reset factus_rangos to 168 (so next is 169)
    $db->prepare("UPDATE factus_rangos SET numero_actual = 990000168 WHERE prefijo = 'SETP' AND estado = 1")->execute();
    echo "- Updated factus_rangos cache to 168.\n";

    $db->commit();
    echo "\nSUCCESS: Clean state 168 reached. NEXT is 169.\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>