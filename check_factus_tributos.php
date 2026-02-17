<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

echo "<h2>Contenido de la tabla factus_tributos</h2>";
$tributos = ModeloFactus::mdlObtenerTributos();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th></tr>";
foreach ($tributos as $tributo) {
    echo "<tr>";
    echo "<td>{$tributo['id']}</td>";
    echo "<td>{$tributo['codigo']}</td>";
    echo "<td>{$tributo['nombre']}</td>";
    echo "<td>{$tributo['descripcion']}</td>";
    echo "</tr>";
}
echo "</table>";
?>