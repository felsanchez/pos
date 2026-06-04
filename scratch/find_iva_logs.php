<?php
$files = ['storage/logs/2026-06-04.log', 'storage/logs/2026-06-03.log'];
foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, '"status":"Created"') !== false && strpos($line, 'tax_rate') !== false) {
            $entry = json_decode($line, true);
            if ($entry) {
                echo "Line " . ($i + 1) . " in " . basename($file) . " [{$entry['timestamp']}]:\n";
                // Decode the response
                $resp = json_decode($entry['message']['respuesta'], true);
                if (isset($resp['data']['items'])) {
                    foreach ($resp['data']['items'] as $item) {
                        echo "  Item: " . $item['name'] . "\n";
                        echo "    price:          " . $item['price'] . "\n";
                        echo "    gross_value:    " . $item['gross_value'] . "\n";
                        echo "    tax_rate:       " . $item['tax_rate'] . "\n";
                        echo "    tax_amount:     " . $item['tax_amount'] . "\n";
                        echo "    total:          " . $item['total'] . "\n";
                        if (isset($item['tribute'])) {
                            echo "    tribute name:   " . $item['tribute']['name'] . "\n";
                        }
                    }
                }
                echo "\n";
            }
        }
    }
}
