<?php
$dirs = ['controladores', 'vistas'];
$patterns = [
    '/icon:\s*"(success|error|warning|info|question)"/' => 'type: "$1"',
    "/icon:\s*'(success|error|warning|info|question)'/" => "type: '$1'"
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if (!in_array($file->getExtension(), ['php', 'js'])) continue;
        
        $content = file_get_contents($file->getPathname());
        $newContent = preg_replace(array_keys($patterns), array_values($patterns), $content);
        
        if ($newContent !== $content) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Reverted: " . $file->getPathname() . "\n";
        }
    }
}
?>
