<?php
function searchDir($dir, $pattern) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            searchDir($path, $pattern);
        } else {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $content = file_get_contents($path);
                if (preg_match($pattern, $content)) {
                    echo "Found Logger definition in $path\n";
                }
            }
        }
    }
}

searchDir('controladores', '/class Logger/i');
searchDir('modelos', '/class Logger/i');
searchDir('helpers', '/class Logger/i');
searchDir('.', '/class Logger/i');
