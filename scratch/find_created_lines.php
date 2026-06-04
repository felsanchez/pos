<?php
$logDir = 'storage/logs/';
$files = glob($logDir . '*.log');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, '"status":"Created"') !== false) {
            echo "File: " . basename($file) . " Line: " . ($i + 1) . "\n";
            echo substr($line, 0, 500) . "\n\n";
        }
    }
}
