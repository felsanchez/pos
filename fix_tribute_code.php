<?php
require_once "modelos/conexion.php";

$id = 6;
$nuevoCodigo = "7"; // Código para "No causa impuesto" (mismo que usamos en el fallback)

try {
    $db = Conexion::conectar();

    // Ver valor actual
    $stmt = $db->prepare("SELECT * FROM factus_tributos WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "=== ANTES ===\n";
    print_r($actual);

    // Actualizar
    $update = $db->prepare("UPDATE factus_tributos SET codigo = :codigo WHERE id = :id");
    $update->bindParam(":codigo", $nuevoCodigo, PDO::PARAM_STR);
    $update->bindParam(":id", $id, PDO::PARAM_INT);

    if ($update->execute()) {
        echo "\n✅ Tributo actualizado exitosamente a código '$nuevoCodigo'\n";
    } else {
        echo "\n❌ Error al actualizar\n";
    }

    // Ver valor nuevo
    $stmt->execute();
    $nuevo = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "\n=== DESPUÉS ===\n";
    print_r($nuevo);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
