<?php
$file = "debug_factus_response_test.txt";
$data = "Test logging content";
if (file_put_contents($file, $data)) {
    echo "Log file created successfully: " . $file;
} else {
    echo "Error creating log file. Check permissions.";
}
