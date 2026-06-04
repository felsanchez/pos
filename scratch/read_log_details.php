<?php
$logDir = 'storage/logs/';
$todayLog = $logDir . date('Y-m-d') . '.log';
$yesterdayLog = $logDir . date('Y-m-d', strtotime('-1 day')) . '.log';

function scanLog($filename) {
    if (!file_exists($filename)) {
        echo "File $filename does not exist.\n";
        return;
    }
    echo "=== Scanning $filename ===\n";
    $lines = file($filename);
    foreach ($lines as $i => $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $msg = $entry['message'];
            // Check if this log entry contains details of a sent payload or response
            if (strpos($line, 'ppal inc8') !== false || strpos($line, 'price') !== false || strpos($line, '92.59') !== false || strpos($line, '85.73') !== false) {
                echo "Line " . ($i + 1) . " [{$entry['timestamp']}]: {$entry['level']}\n";
                // Print a portion of the message
                echo substr(print_r($entry['message'], true), 0, 1500) . "\n\n";
            }
        }
    }
}

scanLog($todayLog);
scanLog($yesterdayLog);
