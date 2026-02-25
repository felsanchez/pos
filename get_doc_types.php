<?php
$mysqli = new mysqli('localhost', 'root', '', 'pos');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT * FROM factus_tipos_documento");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Factus ID: " . $row['id_factus'] . " | Nombre: " . $row['nombre'] . "\n";
}

$mysqli->close();
?>