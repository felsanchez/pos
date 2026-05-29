<?php
// Script to replace file_put_contents with Logger::debug()

$files = [
    __DIR__ . '/../controladores/factus.controlador.php',
    __DIR__ . '/../controladores/gastos.controlador.php',
    __DIR__ . '/../controladores/productos.controlador.php',
    __DIR__ . '/../controladores/ventas.controlador.php',
    __DIR__ . '/../ajax/notas-credito.ajax.php',
    __DIR__ . '/../ajax/notificaciones.ajax.php',
    __DIR__ . '/../ajax/webhook_bold.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Pattern to match file_put_contents(..., ..., FILE_APPEND); or similar
    // Since some spans multiple lines, we will do a simpler approach:
    // Regex: file_put_contents\(.*?,\s*(.*?)(?:,\s*FILE_APPEND)?\);
    // But print_r(..., true) inside the 2nd arg will break simple regex if it has commas.
    // It's safer to use regex that extracts the entire function call.
    
    $modified = preg_replace_callback('/@?file_put_contents\s*\(\s*[^,]+,\s*(.+?)(?:,\s*FILE_APPEND\s*)?\)/is', function($matches) {
        $msg = trim($matches[1]);
        return "Logger::debug($msg)";
    }, $content);
    
    if ($content !== $modified) {
        file_put_contents($file, $modified);
        echo "Modificado: $file\n";
    } else {
        echo "Sin cambios: $file\n";
    }
}
