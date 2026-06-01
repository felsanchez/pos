<?php
require 'config.php';
require 'modelos/conexion.php';
$tributos = Conexion::conectar()->query("SELECT * FROM factus_tributos WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
foreach ($tributos as $tributo) {
    $pct = floatval($tributo['porcentaje_defecto']);
    $nombreMostrar = $tributo['nombre'] . ($pct > 0 ? " $pct%" : "");
    echo "<option value='{$tributo['id']}'>{$nombreMostrar}</option>\n";
}
