<?php
function searchDir($dir, $pattern) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            searchDir($path, $pattern);
        } else {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php' || pathinfo($path, PATHINFO_EXTENSION) === 'js') {
                $content = file_get_contents($path);
                if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $offset = $match[1];
                        $lineNum = substr_count(substr($content, 0, $offset), "\n") + 1;
                        echo "Found in $path on line $lineNum: " . trim($match[0]) . "\n";
                    }
                }
            }
        }
    }
}

echo "=== Searching for INC Bolsas / 22 references ===\n";
searchDir('controladores', '/22|Bolsa/i');
searchDir('vistas', '/22|Bolsa/i');
searchDir('ajax', '/22|Bolsa/i');
