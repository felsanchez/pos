<?php
$files = ['controladores/factus.controlador.php', 'modelos/factus.modelo.php'];
foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'inclusive') !== false || stripos($line, 'tax_') !== false || stripos($line, 'impuesto') !== false) {
            if (strpos($line, 'precio') !== false || strpos($line, 'price') !== false || strpos($line, 'rate') !== false) {
                echo "$file L" . ($i + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}
