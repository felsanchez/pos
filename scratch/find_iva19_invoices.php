<?php
$logDir = 'storage/logs/';
$files = glob($logDir . '*.log');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'medias sur') !== false || strpos($content, 'IVA 19%') !== false || strpos($content, '19.00') !== false) {
        echo "Found IVA 19% reference in " . basename($file) . "\n";
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (strpos($line, 'medias sur') !== false || strpos($line, '19.00') !== false) {
                echo "Line " . ($i + 1) . ": " . substr($line, 0, 1000) . "\n\n";
            }
        }
    }
}
