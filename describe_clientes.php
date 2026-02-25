<?php
$mysqli = new mysqli('localhost', 'root', '', 'pos');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("DESCRIBE clientes");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

$mysqli->close();
?>