<?php
$dir = new RecursiveDirectoryIterator('c:\\xampp\\htdocs\\pos\\vistas\\modulos');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
foreach($files as $file) {
    $content = file_get_contents($file[0]);
    if (strpos($content, 'ctrCrearVentaFactus') !== false) {
        echo "Found in: " . $file[0] . "\n";
    }
}
?>
