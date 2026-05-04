<?php
$lines = file("c:\\xampp\\htdocs\\pos\\modelos\\factus.modelo.php");
foreach($lines as $i => $line) {
    if (strpos($line, 'mdlActualizarDatosFactura') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
?>
