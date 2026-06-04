<?php
$logDir = 'storage/logs/';
$files = glob($logDir . '*.log');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, 'status\":\"Created\"') !== false || strpos($line, 'registrado y validado con éxito') !== false) {
            $entry = json_decode($line, true);
            if ($entry) {
                // Try to extract the JSON response from message or print the message
                $msg = $entry['message'];
                $respStr = "";
                if (is_array($msg) && isset($msg['respuesta'])) {
                    $respStr = $msg['respuesta'];
                } else if (is_string($msg) && strpos($msg, '{') !== false) {
                    $respStr = substr($msg, strpos($msg, '{'));
                }
                
                $resp = json_decode($respStr, true);
                if ($resp && isset($resp['data'])) {
                    $bill = $resp['data']['bill'] ?? [];
                    $items = $resp['data']['items'] ?? [];
                    echo "File: " . basename($file) . " | Ref: " . ($bill['reference_code'] ?? 'N/A') . " | Consecutivo: " . ($bill['number'] ?? 'N/A') . " | Total API: " . ($bill['total'] ?? 'N/A') . "\n";
                    foreach ($items as $item) {
                        echo "  Item: {$item['name']} | Price Sent: {$item['price']} | Gross: {$item['gross_value']} | Tax Rate: {$item['tax_rate']} | Tax: {$item['tax_amount']} | Total: {$item['total']} | Tribute: " . ($item['tribute']['name'] ?? '') . "\n";
                    }
                    echo "\n";
                }
            }
        }
    }
}
