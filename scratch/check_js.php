<?php
$content = file_get_contents("c:\\xampp\\htdocs\\pos\\vistas\\js\\ventas.js");
if (strpos($content, '.formularioVenta') !== false) {
    echo "formularioVenta found in ventas.js!\n";
    // echo context
    $pos = strpos($content, '.formularioVenta');
    echo substr($content, max(0, $pos - 100), 200);
} else {
    echo "No occurrences of formularioVenta found in ventas.js.";
}
?>
