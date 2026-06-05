<?php
$file = "tmp/log_numbering.txt";
if (file_exists($file)) {
    $lines = file($file);
    foreach ($lines as $line) {
        if (strpos($line, "2026-06-05 01:3") !== false) {
            echo $line;
        }
    }
}
