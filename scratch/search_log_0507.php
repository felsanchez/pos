<?php
$logFile = 'storage/logs/2026-06-04.log';
if (!file_exists($logFile)) {
    die("Log file does not exist.\n");
}
$content = file_get_contents($logFile);
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, '2026-06-04 05:') !== false) {
        echo "Line " . ($i + 1) . ": " . substr($line, 0, 500) . "\n";
    }
}
