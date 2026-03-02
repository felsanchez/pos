<?php
$directory = new RecursiveDirectoryIterator('c:/xampp/htdocs/pos');
$iterator = new RecursiveIteratorIterator($directory);
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php' || $file->getExtension() === 'js') {
        $content = file_get_contents($file->getPathname());
        $open = substr_count($content, '/*');
        $close = substr_count($content, '*/');
        if ($open !== $close) {
            echo "File: " . $file->getPathname() . " | /* : $open | */ : $close\n";

            // Buscar la línea aproximada del último /* que no se cierra
            $lines = explode("\n", $content);
            $lastOpen = -1;
            foreach ($lines as $index => $line) {
                if (strpos($line, '/*') !== false && strpos($line, '*/') === false) {
                    $lastOpen = $index + 1;
                }
            }
            if ($lastOpen !== -1) {
                echo "  Possible unclosed /* at line $lastOpen\n";
            }
        }
    }
}
?>