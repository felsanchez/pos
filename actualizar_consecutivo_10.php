<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Actualizar Consecutivo a 10</h2>";

$db = Conexion::conectar();

echo "<p><strong>Situación:</strong></p>";
echo "<ul>";
echo "<li>La factura 10 existe en la DIAN (con error FAX07)</li>";
echo "<li>El consecutivo local está en 9</li>";
echo "<li>Necesitamos actualizar a 10 para que la próxima sea 11</li>";
echo "</ul>";

// Actualizar
$stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = 10 WHERE id_factus = 1040");
$result = $stmt->execute();

if ($result) {
    echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin:10px 0;'>";
    echo "<h3 style='color:#155724;'>✅ Actualización Exitosa</h3>";
    echo "<p>El consecutivo se actualizó a <strong>10</strong></p>";
    echo "<p>La próxima factura será la <strong>FEFG11</strong></p>";
    echo "</div>";

    // Verificar
    $stmt = $db->query("SELECT numero_actual FROM factus_rangos WHERE id_factus = 1040");
    $nuevo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Verificación - Consecutivo actual: <strong>" . $nuevo['numero_actual'] . "</strong></p>";

    echo "<hr>";
    echo "<h3>Próximos Pasos:</h3>";
    echo "<ol>";
    echo "<li>Vaya a <strong>Crear Factura Electrónica</strong></li>";
    echo "<li>El campo 'Código Venta' debería mostrar <strong>11</strong></li>";
    echo "<li>Cree la nueva factura con los productos correctos</li>";
    echo "<li>La corrección del tributo que hice debería evitar el error FAX07</li>";
    echo "</ol>";
} else {
    echo "<p style='color:red;'>❌ Error al actualizar</p>";
}
