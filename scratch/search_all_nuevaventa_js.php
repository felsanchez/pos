<?php
$files = glob("c:\\xampp\\htdocs\\pos\\vistas\\js\\*.js");
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'nuevaVenta') !== false) {
        echo "Found 'nuevaVenta' in $file\n";
    }
}
?>
