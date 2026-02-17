<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Saltar al Número 11 (Anular el 10)</h2>";

$db = Conexion::conectar();

// Verificar estado actual
$stmt = $db->query("SELECT numero_actual FROM factus_rangos WHERE id_factus = 1040");
$rango = $stmt->fetch(PDO::FETCH_ASSOC);
$actual = $rango['numero_actual'];

echo "<p><strong>Consecutivo actual:</strong> $actual</p>";
echo "<p><strong>Próxima factura sería:</strong> " . ($actual + 1) . "</p>";

echo "<hr>";
echo "<h3>⚠️ ADVERTENCIA</h3>";
echo "<p>Al ejecutar esta acción:</p>";
echo "<ul>";
echo "<li>El consecutivo se actualizará a <strong>10</strong></li>";
echo "<li>La próxima factura será la <strong>11</strong></li>";
echo "<li>El número <strong>10</strong> quedará sin usar (anulado)</li>";
echo "<li>Esto puede generar observaciones en auditorías de la DIAN</li>";
echo "</ul>";

echo "<form method='POST'>";
echo "<p><strong>¿Está seguro de que desea continuar?</strong></p>";
echo "<button type='submit' name='confirmar' value='si' style='background:orange; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>SÍ, Saltar al Número 11</button>";
echo " ";
echo "<button type='button' onclick='window.history.back()' style='background:gray; color:white; padding:10px 20px; font-size:16px; cursor:pointer;'>Cancelar</button>";
echo "</form>";

if (isset($_POST['confirmar']) && $_POST['confirmar'] == 'si') {
    echo "<hr>";
    echo "<h3>Ejecutando...</h3>";

    // Actualizar a 10
    $stmt = $db->prepare("UPDATE factus_rangos SET numero_actual = 10 WHERE id_factus = 1040");
    $result = $stmt->execute();

    if ($result) {
        echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin:10px 0;'>";
        echo "<h3 style='color:#155724;'>✅ Actualización Exitosa</h3>";
        echo "<p>El consecutivo se actualizó a <strong>10</strong></p>";
        echo "<p>La próxima factura que cree será la <strong>FEFG11</strong></p>";
        echo "<p><strong>Recomendación:</strong> Documente que el número 10 fue anulado por problemas técnicos.</p>";
        echo "</div>";

        // Verificar
        $stmt = $db->query("SELECT numero_actual FROM factus_rangos WHERE id_factus = 1040");
        $nuevo = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Verificación - Consecutivo actual: <strong>" . $nuevo['numero_actual'] . "</strong></p>";
    } else {
        echo "<p style='color:red;'>❌ Error al actualizar</p>";
    }
}
