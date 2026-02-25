<?php
$mysqli = new mysqli('localhost', 'root', '', 'pos');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT * FROM factus_rangos";
$result = $mysqli->query($query);

while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['document_name'] . " | Document: " . $row['document'] . " | Prefix: " . $row['prefix'] . "\n";
}

$mysqli->close();
?>