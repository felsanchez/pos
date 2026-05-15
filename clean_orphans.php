<?php
require_once "modelos/session-manager.php";
require_once "controladores/productos.controlador.php";
require_once "modelos/productos.modelo.php";

// Buscar variantes huerfanas (que no tienen opciones asociadas)
$stmt = Conexion::conectar()->prepare("
    SELECT pv.id, pv.sku, p.codigo 
    FROM productos_variantes pv 
    LEFT JOIN productos_variantes_opciones pvo ON pv.id = pvo.id_producto_variante 
    LEFT JOIN productos p ON pv.id_producto = p.id
    WHERE pvo.id_opcion_variante IS NULL
");
$stmt->execute();
$huerfanas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Variantes huerfanas encontradas: " . count($huerfanas) . "\n";

foreach ($huerfanas as $h) {
    echo "Eliminando variante corrupta ID: " . $h['id'] . " (SKU: " . $h['sku'] . ")\n";
    $del = Conexion::conectar()->prepare("DELETE FROM productos_variantes WHERE id = :id");
    $del->bindParam(":id", $h['id'], PDO::PARAM_INT);
    $del->execute();
}
echo "Limpieza completada.\n";
