<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Starting Hard Sync of Electronic Invoices...\n";

    // 1. Get all signed electronic invoices
    $stmt = $db->prepare("SELECT id, numero_factura, codigo FROM ventas WHERE numero_factura IS NOT NULL AND numero_factura != '' AND estado_dian IN ('enviada', 'aceptada')");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sales as $sale) {
        // Extract number from SETP990000164
        preg_match('/(\d+)$/', $sale['numero_factura'], $matches);
        if (isset($matches[1])) {
            $expectedCodigo = $matches[1];
            if ($sale['codigo'] != $expectedCodigo) {
                echo "- Aligning ID {$sale['id']}: Current Code {$sale['codigo']} -> New Code $expectedCodigo\n";
                $upd = $db->prepare("UPDATE ventas SET codigo = :codigo WHERE id = :id");
                $upd->bindParam(":codigo", $expectedCodigo, PDO::PARAM_STR);
                $upd->bindParam(":id", $sale['id'], PDO::PARAM_INT);
                $upd->execute();
            }
        }
    }

    // 2. Clear out any "creada" drafts that are blocking the sequence
    $db->prepare("DELETE FROM ventas WHERE estado_dian = 'creada' AND (numero_factura IS NULL OR numero_factura = '')")->execute();
    echo "- Deleted unsaved drafts to clear the path.\n";

    // 3. Reset ID 691 ghost if it still exists
    $db->prepare("UPDATE ventas SET codigo = '000000000' WHERE id = 691")->execute();

    $db->commit();
    echo "\nSUCCESS: Hard Sync complete. Local sequence should now match DIAN sequence.\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>