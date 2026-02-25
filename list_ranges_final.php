<?php
$mysqli = new mysqli('localhost', 'root', '', 'pos');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT * FROM factus_rangos";
$result = $mysqli->query($query);

while ($row = $result->fetch_assoc()) {
    echo "ID Factus: " . $row['id_factus'] . " | Documento: " . $row['documento'] . " | Prefijo: " . $row['prefijo'] . " | Resolucion: " . $row['resolucion'] . "\n";
}

$mysqli->close();
?>