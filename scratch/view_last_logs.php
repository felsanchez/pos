<?php
$file = "tmp/log_numbering.txt";
if (file_exists($file)) {
    $lines = file($file);
    $last_lines = array_slice($lines, -20);
    echo implode("", $last_lines);
} else {
    echo "Log file does not exist.\n";
}
