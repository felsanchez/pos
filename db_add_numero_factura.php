<?php
require_once "modelos/conexion.php";

echo "<h1>Actualizando Tabla Ventas: Columna numero_factura</h1>";

try {
    $db = Conexion::conectar();

    // Verificar si existe la columna
    $stmt = $db->prepare("SHOW COLUMNS FROM ventas LIKE 'numero_factura'");
    $stmt->execute();

    if (!$stmt->fetch()) {
        echo "Agregando columna 'numero_factura' (INT)... ";
        // Agregamos después de 'codigo' para orden
        $sql = "ALTER TABLE ventas ADD COLUMN numero_factura VARCHAR(50) DEFAULT NULL AFTER codigo";
        $db->exec($sql);
        echo "<span style='color:green'>OK</span><br>";

        // Opcional: Para las facturas YA exitosas, intentar igualar numero_factura = codigo 
        // (Aunque sabemos que están desfasadas las últimas, las viejas quizás coincidían).
        // Mejor dejarlo NULL y llenar solo las nuevas, o llenar con codigo por defecto.
        // Dado el problema actual, mejor dejar NULL para distinguir.
    } else {
        echo "La columna 'numero_factura' ya existe.<br>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
