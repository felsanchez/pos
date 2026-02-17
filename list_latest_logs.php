<?php
$files = glob("debug_factus_response_*.txt");
usort($files, function ($a, $b) {
    return filemtime($a) < filemtime($b);
});

echo "=== TOP 5 LATEST RESPONSE FILES ===\n";
foreach (array_slice($files, 0, 5) as $file) {
    echo $file . " - " . date("Y-m-d H:i:s", filemtime($file)) . "\n";
}

$filesReq = glob("debug_factus_request_*.txt");
usort($filesReq, function ($a, $b) {
    return filemtime($a) < filemtime($b);
});

echo "\n=== TOP 5 LATEST REQUEST FILES ===\n";
foreach (array_slice($filesReq, 0, 5) as $file) {
    echo $file . " - " . date("Y-m-d H:i:s", filemtime($file)) . "\n";
}
