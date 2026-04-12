<?php
$path = 'vistas/modulos/facturas-electronicas.php';
$content = file_get_contents($path);

// Regex para encontrar el bloque huérfano entre la tabla y el controlador
$pattern = '/<\/div>\s+if \(\$estadoDian == \'aceptada\' \|\| \$estadoDian == \'enviada\'\) \{.*?\}\s+<\?php/s';
$replacement = "        </div>\n\n        <?php";

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent !== null && $newContent !== $content) {
    file_put_contents($path, $newContent);
    echo "Limpieza exitosa.\n";
} else {
    echo "No se encontró el patrón o ya estaba limpio.\n";
}
?>
