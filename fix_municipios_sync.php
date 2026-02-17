<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

echo "<h2>Fixing Municipios Table</h2>";

$db = Conexion::conectar();

// 1. Add column if not exists
try {
    $db->query("ALTER TABLE factus_municipios ADD COLUMN id_factus INT(11) AFTER id");
    echo "Column id_factus added.<br>";
} catch (Exception $e) {
    echo "Column likely exists or error: " . $e->getMessage() . "<br>";
}

// 2. Update Model to save id_factus (Done via replace_file_content)

echo "Running API Sync to populate id_factus... This might take a while.<br>";

// Increase execution time
set_time_limit(300);

$sync = ControladorFactus::ctrSincronizarMunicipios();
echo "<pre>";
print_r($sync);
echo "</pre>";

// 3. Verify IDs for Bogota/Cali
echo "<h3>Verified IDs</h3>";
$stmt = $db->query("SELECT id, id_factus, codigo, nombre FROM factus_municipios WHERE codigo IN ('11001', '76001')");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($results);
echo "</pre>";

