<?php
$logFile = "storage/logs/" . date('Y-m-d') . ".log";
if (file_exists($logFile)) {
    $lines = file($logFile);
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data) {
            if (strpos($data['message'], 'DRAFT CREATION') !== false || strpos($data['message'], 'DATOS:') !== false || strpos($data['message'], 'UPDATE') !== false || strpos($data['message'], 'Error') !== false) {
                echo "Time: {$data['timestamp']} | Message: {$data['message']}\n";
                if (!empty($data['context'])) {
                    print_r($data['context']);
                }
                echo "-------------------\n";
            }
        }
    }
} else {
    echo "Log file $logFile does not exist.\n";
}
