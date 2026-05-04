<?php
$lines = file("c:\\xampp\\htdocs\\pos\\vistas\\js\\ventas.js");
foreach($lines as $i => $line) {
    if (strpos($line, 'nuevaVenta') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
?>
