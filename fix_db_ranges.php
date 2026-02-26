<?php
require_once "modelos/conexion.php";

try {
    $db = Conexion::conectar();

    // Actualizamos el rango 3 (Antiguo) y 31 (Nuevo) para que apunten al ID 1193 (DATO REAL DE API)
    $stmt = $db->prepare("UPDATE factus_rangos SET id_factus = 1193 WHERE id IN (3, 31)");
    $stmt->execute();

    echo "OK: Rangos actualizados a 1193. Filas afectadas: " . $stmt->rowCount() . "\n";

    // Verificamos
    $stmt = $db->query("SELECT id, id_factus, documento FROM factus_rangos WHERE documento LIKE '%Ajuste%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | FactusID: {$row['id_factus']} | Doc: {$row['documento']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>