<?php
$logFile = 'storage/logs/2026-06-03.log';
if (!file_exists($logFile)) {
    die("Log file does not exist.\n");
}
$content = file_get_contents($logFile);
$lines = explode("\n", $content);
$found = false;
foreach ($lines as $i => $line) {
    if (strpos($line, '"reference_code":"54"') !== false || strpos($line, 'reference_code] => 54') !== false) {
        $found = true;
        // Print the lines around it
        $start = max(0, $i - 5);
        $end = min(count($lines) - 1, $i + 15);
        echo "=== Found reference_code 54 on line " . ($i + 1) . " ===\n";
        for ($j = $start; $j <= $end; $j++) {
            $entry = json_decode($lines[$j], true);
            if ($entry) {
                echo "Line " . ($j + 1) . " [{$entry['timestamp']}]: \n";
                print_r($entry['message']);
                echo "\n\n";
            } else {
                echo "Line " . ($j + 1) . ": {$lines[$j]}\n";
            }
        }
    }
}
if (!$found) {
    echo "reference_code 54 not found in log.\n";
}
