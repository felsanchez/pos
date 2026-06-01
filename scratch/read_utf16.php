<?php
$content = file_get_contents('tmp/factus_bills_output.txt');
$utf8 = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');

// Let's print lines containing [errors] or [message] or [status] or similar
$lines = explode("\n", $utf8);
$in_error = false;
$error_count = 0;
foreach ($lines as $i => $line) {
    if (strpos($line, '[errors] =>') !== false) {
        echo "Line " . ($i+1) . ": " . trim($line) . "\n";
        // Print next 5 lines
        for ($j = 1; $j <= 5; $j++) {
            if (isset($lines[$i+$j])) {
                echo "  " . trim($lines[$i+$j]) . "\n";
            }
        }
        $error_count++;
    }
}
echo "Total error sections found: $error_count\n";
