<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);
$result = $conn->query("SHOW COLUMNS FROM proveedores");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
$conn->close();
?>