<?php
$lines = file("c:\\xampp\\htdocs\\pos\\controladores\\ventas.controlador.php");
foreach($lines as $i => $line) {
    if (strpos($line, 'ctrMostrarFacturasElectronicasServerSide') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
?>
