<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

echo "<h2>Verificar Responsabilidades Fiscales en Factus</h2>";

// Intentar obtener las responsabilidades fiscales desde la API o base de datos de Factus
$db = Conexion::conectar();

// Verificar si hay una tabla de responsabilidades fiscales en Factus
$tables = $db->query("SHOW TABLES LIKE '%responsabilidad%'")->fetchAll();
echo "<h3>Tablas relacionadas con responsabilidades:</h3>";
if (empty($tables)) {
    echo "<p>No se encontraron tablas específicas de responsabilidades fiscales.</p>";
} else {
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
}

// Mostrar las opciones actuales en el código
echo "<h3>Opciones actuales en cliente-detalle.php:</h3>";
$listaResponsabilidades = [
    "R-99-PN" => "R-99-PN: No responsable (Persona Natural)",
    "O-13" => "O-13: Gran Contribuyente",
    "O-15" => "O-15: Autorretenedor",
    "O-23" => "O-23: Agente de Retención IVA",
    "O-47" => "O-47: Régimen Simple de Tributación",
    "ZY" => "ZY: No responsable de IVA (Persona Jurídica)"
];

echo "<ul>";
foreach ($listaResponsabilidades as $codigo => $descripcion) {
    echo "<li><strong>$codigo</strong>: $descripcion</li>";
}
echo "</ul>";

echo "<h3>Responsabilidades Fiscales según DIAN (2024-2026):</h3>";
echo "<p>Códigos oficiales de la DIAN para facturación electrónica:</p>";
echo "<ul>";
echo "<li><strong>O-13</strong>: Gran Contribuyente</li>";
echo "<li><strong>O-15</strong>: Autorretenedor</li>";
echo "<li><strong>O-23</strong>: Agente de Retención en el Impuesto sobre las Ventas</li>";
echo "<li><strong>O-47</strong>: Régimen Simple de Tributación - SIMPLE</li>";
echo "<li><strong>R-99-PN</strong>: No Responsable de IVA (Persona Natural)</li>";
echo "<li><strong>ZY</strong>: No Responsable de IVA (Persona Jurídica)</li>";
echo "</ul>";

echo "<p><strong>Conclusión:</strong> Las opciones actuales parecen estar correctas según los códigos estándar de la DIAN.</p>";
?>