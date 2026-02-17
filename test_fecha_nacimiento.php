<?php
// Script de prueba para verificar si la columna fecha_nacimiento se puede actualizar

require_once "modelos/conexion.php";

$db = Conexion::conectar();

// Obtener un cliente de prueba
$stmt = $db->prepare("SELECT id, nombre, fecha_nacimiento FROM clientes LIMIT 1");
$stmt->execute();
$cliente = $stmt->fetch();

echo "<h3>Cliente antes del UPDATE:</h3>";
echo "<pre>";
print_r($cliente);
echo "</pre>";

// Intentar actualizar la fecha de nacimiento
$fechaPrueba = "2000-01-15 00:00:00";
$stmt = $db->prepare("UPDATE clientes SET fecha_nacimiento = :fecha WHERE id = :id");
$stmt->bindParam(":fecha", $fechaPrueba, PDO::PARAM_STR);
$stmt->bindParam(":id", $cliente['id'], PDO::PARAM_INT);

if ($stmt->execute()) {
    echo "<h3 style='color: green;'>UPDATE ejecutado correctamente</h3>";
} else {
    echo "<h3 style='color: red;'>ERROR en UPDATE:</h3>";
    echo "<pre>";
    print_r($stmt->errorInfo());
    echo "</pre>";
}

// Verificar si se guardó
$stmt = $db->prepare("SELECT id, nombre, fecha_nacimiento FROM clientes WHERE id = :id");
$stmt->bindParam(":id", $cliente['id'], PDO::PARAM_INT);
$stmt->execute();
$clienteActualizado = $stmt->fetch();

echo "<h3>Cliente después del UPDATE:</h3>";
echo "<pre>";
print_r($clienteActualizado);
echo "</pre>";

if ($clienteActualizado['fecha_nacimiento'] == $fechaPrueba) {
    echo "<h2 style='color: green;'>✓ La fecha se guardó correctamente</h2>";
} else {
    echo "<h2 style='color: red;'>✗ La fecha NO se guardó</h2>";
    echo "<p>Esperado: $fechaPrueba</p>";
    echo "<p>Obtenido: " . $clienteActualizado['fecha_nacimiento'] . "</p>";
}
?>