<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Starting Deep Hard Sync...\n";

    // 1. Delete all "creada/pendiente" drafts to avoid confusion
    $db->prepare("DELETE FROM ventas WHERE estado_dian IN ('creada', 'pendiente') AND (numero_factura IS NULL OR numero_factura = '')")->execute();
    echo "- Deleted all unsaved drafts.\n";

    // 2. Identify and handle duplicates of numero_factura
    // Keep only the LATEST ID for each numero_factura, delete others (to be safe)
    // Or just align them. The user says when firmada it corrects itself.

    $stmt = $db->prepare("SELECT id, numero_factura, codigo FROM ventas WHERE numero_factura IS NOT NULL AND numero_factura != '' ORDER BY id ASC");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $seen = [];
    foreach ($sales as $sale) {
        $num = $sale['numero_factura'];

        // If we've seen this DIAN number before, it's a duplicate. 
        // We'll mark the previous one as 'archivada' or delete it if it's identical?
        // For now, let's just make sure the LATEST one is the official one.

        preg_match('/(\d+)$/', $num, $matches);
        if (isset($matches[1])) {
            $expectedCodigo = $matches[1];

            echo "- Aligning ID {$sale['id']}: DIAN $num | Code {$sale['codigo']} -> $expectedCodigo\n";
            $upd = $db->prepare("UPDATE ventas SET codigo = :codigo WHERE id = :id");
            $upd->bindParam(":codigo", $expectedCodigo, PDO::PARAM_STR);
            $upd->bindParam(":id", $sale['id'], PDO::PARAM_INT);
            $upd->execute();
        }
    }

    // 3. Move ghost ID 691
    $db->prepare("UPDATE ventas SET codigo = '0' WHERE id = 691")->execute();

    $db->commit();
    echo "\nSUCCESS: Deep Sync complete.\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>