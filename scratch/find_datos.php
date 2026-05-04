<?php
$content = file_get_contents("c:\\xampp\\htdocs\\pos\\controladores\\ventas.controlador.php");
$lines = explode("\n", $content);
foreach($lines as $i => $line) {
    if (strpos($line, '$datos = array(') !== false) {
        echo "Line " . ($i+1) . ": " . trim($line) . "\n";
    }
}
?>
