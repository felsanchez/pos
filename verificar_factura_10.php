<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Verificar Estado Real de Factura 10</h2>";

$db = Conexion::conectar();

// Buscar TODAS las ventas con código 10
$stmt = $db->query("SELECT id, codigo, numero_factura, estado_dian, mensaje_dian, cufe, fecha_envio_dian 
                     FROM ventas 
                     WHERE codigo = 10
                     ORDER BY id DESC");
$ventas10 = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Ventas con código 10:</h3>";
echo "<pre>";
print_r($ventas10);
echo "</pre>";

// Buscar facturas con número FEFG10
$stmt = $db->query("SELECT id, codigo, numero_factura, estado_dian, mensaje_dian, cufe, fecha_envio_dian 
                     FROM ventas 
                     WHERE numero_factura LIKE '%10%'
                     ORDER BY id DESC");
$fefg10 = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Facturas con número que contiene '10':</h3>";
echo "<pre>";
print_r($fefg10);
echo "</pre>";

// Ver consecutivo actual
$stmt = $db->query("SELECT numero_actual FROM factus_rangos WHERE id_factus = 1040");
$rango = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Consecutivo Actual:</h3>";
echo "<p>numero_actual: {$rango['numero_actual']}</p>";
echo "<p>Próxima factura: " . ($rango['numero_actual'] + 1) . "</p>";
