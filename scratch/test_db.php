<?php
require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";

$tabla = "ventas";
$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 1");
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Campos encontrados en la tabla ventas:\n";
print_r(array_keys($resultado));
?>
