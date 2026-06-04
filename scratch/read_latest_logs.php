<?php
$logDir = 'storage/logs/';
if (!file_exists($logDir)) {
    die("Log directory does not exist.\n");
}

$files = glob($logDir . '*.log');
if (empty($files)) {
    die("No log files found.\n");
}

// Sort files to get the newest first
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

echo "=== Log Files ===\n";
foreach ($files as $file) {
    echo basename($file) . " (size: " . filesize($file) . " bytes, modified: " . date('Y-m-d H:i:s', filemtime($file)) . ")\n";
}
echo "\n";

// Let's search inside the log files for "ppal inc8" or JSON arrays
foreach ($files as $file) {
    echo "=== Searching in " . basename($file) . " ===\n";
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $msg = $entry['message'];
            if (stripos($msg, 'ppal inc8') !== false || stripos($msg, 'ITEM-1') !== false || stripos($msg, '92.59') !== false || stripos($msg, '85.73') !== false) {
                echo "Line " . ($i + 1) . " [{$entry['timestamp']}]: \n";
                echo substr(print_r($entry['message'], true), 0, 1000) . "\n\n";
            }
        }
    }
}
