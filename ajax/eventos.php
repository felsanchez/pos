<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conexion = new mysqli("localhost", "root", "", "pos");

// Incluir la conexión centralizada
//require_once "../modelos/conexion.php";

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}


// Consulta para obtener las actividades
$sql = "SELECT id, descripcion, fecha FROM actividades";
$resultado = $conexion->query($sql);

$eventos = array();

while ($fila = $resultado->fetch_assoc()) {
    $eventos[] = array(
        'id' => $fila['id'],
        'title' => $fila['descripcion'],
        'start' => $fila['fecha']
    );
}

// Devolver los eventos en formato JSON
header('Content-Type: application/json');
echo json_encode($eventos);
?>
