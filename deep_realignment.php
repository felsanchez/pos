<?php
require_once 'modelos/conexion.php';
try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    echo "Realigning sequences and clearing collisions...\n";

    // 1. Delete all current drafts (those with empty numero_factura and after ID 710)
    $db->prepare("DELETE FROM ventas WHERE id >= 717 AND (numero_factura IS NULL OR numero_factura = '')")->execute();
    echo "- Deleted unsaved draft ID 717.\n";

    // 2. Align all signed records with their DIAN numbers
    $stmt = $db->prepare("SELECT id, numero_factura, codigo FROM ventas WHERE numero_factura IS NOT NULL AND numero_factura != '' ORDER BY id ASC");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sales as $sale) {
        preg_match('/(\d+)$/', $sale['numero_factura'], $matches);
        if (isset($matches[1])) {
            $expectedCodigo = $matches[1];
            if ($sale['codigo'] != $expectedCodigo) {
                echo "- Aligning ID {$sale['id']}: '{$sale['numero_factura']}' | Code {$sale['codigo']} -> $expectedCodigo\n";
                $upd = $db->prepare("UPDATE ventas SET codigo = :codigo WHERE id = :id");
                $upd->bindParam(":codigo", $expectedCodigo, PDO::PARAM_STR);
                $upd->bindParam(":id", $sale['id'], PDO::PARAM_INT);
                $upd->execute();
            }
        }
    }

    // 3. Reset the Factus range cache to the last signed number
    $stmt = $db->prepare("SELECT numero_factura FROM ventas WHERE numero_factura LIKE '%SETP%' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $last = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($last) {
        preg_match('/(\d+)$/', $last['numero_factura'], $matches);
        if (isset($matches[1])) {
            $lastNum = intval($matches[1]);
            $db->prepare("UPDATE factus_rangos SET numero_actual = :num WHERE prefijo = 'SETP' AND estado = 1")->execute([':num' => $lastNum]);
            echo "- Updated factus_rangos cache to $lastNum.\n";
        }
    }

    $db->commit();
    echo "\nSUCCESS: Database aligned. Next suggested should be " . ($lastNum + 1) . "\n";
} catch (Exception $e) {
    if (isset($db))
        $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>