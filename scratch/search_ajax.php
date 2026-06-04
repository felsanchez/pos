<?php
$content = file_get_contents('ajax/facturacion.ajax.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'generarFactura') !== false || stripos($line, 'firmar') !== false || stripos($line, 'action') !== false) {
        echo "Line " . ($i + 1) . ": $line\n";
    }
}
