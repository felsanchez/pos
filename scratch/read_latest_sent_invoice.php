<?php
$logFile = 'storage/logs/2026-06-04.log';
if (!file_exists($logFile)) {
    die("Log file for 2026-06-04 does not exist.\n");
}
$content = file_get_contents($logFile);
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, 'ppal inc8') !== false) {
        $entry = json_decode($line, true);
        if ($entry) {
            echo "Line " . ($i + 1) . " [{$entry['timestamp']}]: {$entry['level']}\n";
            print_r($entry['message']);
            echo "\n\n";
        }
    }
}
