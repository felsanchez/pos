<?php
$mysqli = new mysqli('localhost', 'root', '', 'pos');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}

$mysqli->close();
?>