<?php
$file = 'debug_factus_api.txt';
if (!file_exists($file)) {
    die("File not found.\n");
}
$content = file_get_contents($file);
preg_match_all('/"tribute_id":\s*(\d+)/', $content, $matches);
$ids = array_unique($matches[1]);
echo "Tribute IDs sent in payloads:\n";
print_r($ids);
