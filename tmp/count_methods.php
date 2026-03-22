<?php
$content = file_get_contents('c:/xampp/htdocs/pos/modelos/factus.modelo.php');
$pattern = '/function\s+mdlObtenerRangoDS/i';
$matches = [];
preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

echo "Encontradas " . count($matches[0]) . " ocurrencias.\n";
foreach ($matches[0] as $match) {
    echo "Posición: " . $match[1] . " - Texto: " . $match[0] . "\n";
    // Ver un poco del contexto
    echo "Contexto: " . substr($content, $match[1] - 50, 100) . "\n\n";
}
?>
