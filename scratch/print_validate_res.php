<?php
$file = 'scratch/validate_res.json';
if (!file_exists($file)) {
    die("File $file does not exist.\n");
}
$data = json_decode(file_get_contents($file), true);
if (isset($data['data'])) {
    echo "=== API Response Data ===\n";
    print_r($data['data']);
} else {
    print_r($data);
}
