<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT responsabilidades_fiscales, tipo_persona FROM factus_config WHERE id = 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Responsabilidades Fiscales del Establecimiento: " . $config['responsabilidades_fiscales'] . "\n";
echo "Tipo Persona del Establecimiento: " . $config['tipo_persona'] . "\n";
