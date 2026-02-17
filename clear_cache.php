<?php
// Script para limpiar el caché de PHP (opcache)

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ Opcache limpiado exitosamente\n";
    } else {
        echo "❌ No se pudo limpiar opcache\n";
    }
} else {
    echo "ℹ️  Opcache no está habilitado\n";
}

// También limpiar cualquier otro caché de PHP
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC cache limpiado\n";
}

echo "\n🔄 Por favor, intenta generar la factura nuevamente.\n";
