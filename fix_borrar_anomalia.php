<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Cleaning up sequence anomalies...\n";

    // 1. Delete ID 694 (Incomplete draft with code 164)
    $db->prepare("DELETE FROM ventas WHERE id = 694")->execute();
    echo "- Deleted garbage record ID 694.\n";

    // 2. Delete ID 693 (Incomplete draft with code 162 and duplicate DIAN 160)
    $db->prepare("DELETE FROM ventas WHERE id = 693")->execute();
    echo "- Deleted corrupted record ID 693.\n";

    // 3. Move the blocking ghost record ID 691
    $db->prepare("UPDATE ventas SET codigo = '000000000' WHERE id = 691")->execute();
    echo "- Moved ghost record ID 691 to safe code range.\n";

    // 4. Ensure ID 692 (The real 161) has matching code
    $db->prepare("UPDATE ventas SET codigo = '990000161' WHERE id = 692")->execute();
    echo "- Aligned ID 692 to code 161.\n";

    // 5. Ensure ID 690 (The real 160) has matching code
    $db->prepare("UPDATE ventas SET codigo = '990000160' WHERE id = 690")->execute();
    echo "- Aligned ID 690 to code 160.\n";

    $db->commit();
    echo "\nSUCCESS: Database cleaned and aligned. Next available code is 990000162.\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>