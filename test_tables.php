<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/usuarios.modelo.php";

$stmt = Conexion::conectar()->prepare("DESCRIBE notas_credito");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as $row) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
?>