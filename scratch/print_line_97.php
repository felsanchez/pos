<?php
$logFile = 'storage/logs/2026-06-04.log';
$content = file_get_contents($logFile);
$lines = explode("\n", $content);
echo "=== LINE 97 ===\n" . $lines[96] . "\n\n";
echo "=== LINE 98 ===\n" . $lines[97] . "\n\n";
echo "=== LINE 99 ===\n" . $lines[98] . "\n\n";
echo "=== LINE 100 ===\n" . $lines[99] . "\n\n";
