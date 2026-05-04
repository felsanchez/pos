<?php
$files = glob("c:\\xampp\\htdocs\\pos\\vistas\\js\\*.js");
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'editarVenta') !== false) {
        echo "Found 'editarVenta' in $file\n";
    }
}
?>
