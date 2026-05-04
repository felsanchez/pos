<?php
$content = file_get_contents("c:\\xampp\\htdocs\\pos\\vistas\\modulos\\orden-a-factura-electronica.php");
$lines = explode("\n", $content);
foreach($lines as $i => $line) {
    if (stripos($line, 'nuevaVenta') !== false) {
        echo "Line " . ($i+1) . ": " . trim($line) . "\n";
    }
}
?>
