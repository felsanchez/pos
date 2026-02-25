<?php
$mysqli = new mysqli('localhost', 'root', '', 'pos');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// First check if columns already exist
$result = $mysqli->query("SHOW COLUMNS FROM proveedores LIKE 'documento'");
if ($result->num_rows == 0) {
    $q = "ALTER TABLE proveedores 
        ADD COLUMN documento VARCHAR(20) AFTER nombre,
        ADD COLUMN tipo_documento_id INT(11) DEFAULT 3 AFTER documento,
        ADD COLUMN municipio_id VARCHAR(10) DEFAULT '149' AFTER direccion,
        ADD COLUMN organizacion_id INT(11) DEFAULT 1 AFTER municipio_id";

    if ($mysqli->query($q)) {
        echo "Table altered successfully\n";
    } else {
        echo "Error altering table: " . $mysqli->error . "\n";
    }
} else {
    echo "Columns already exist\n";
}

$mysqli->close();
?>