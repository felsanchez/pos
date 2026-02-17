<?php
// Script para agregar el botón de autenticación a configuracion-factus.php

$archivo = 'c:\xampp\htdocs\pos\vistas\modulos\configuracion-factus.php';
$contenido = file_get_contents($archivo);

// Buscar la línea del botón "Probar Conexión" y agregar el nuevo botón después
$buscar = '<button type="button" class="btn btn-info" id="btnProbarConexion">
					<i class="fa fa-refresh"></i> Probar Conexión
				</button>';

$reemplazar = '<button type="button" class="btn btn-info" id="btnProbarConexion">
					<i class="fa fa-refresh"></i> Probar Conexión
				</button>
				<button type="button" class="btn btn-success" id="btnAutenticar" style="margin-left: 10px;">
					<i class="fa fa-key"></i> Autenticar y Obtener Tokens
				</button>';

$contenidoNuevo = str_replace($buscar, $reemplazar, $contenido);

if ($contenidoNuevo !== $contenido) {
    file_put_contents($archivo, $contenidoNuevo);
    echo "✅ Botón agregado exitosamente\n";
} else {
    echo "❌ No se encontró el patrón para reemplazar\n";
    echo "Intentando con tabs...\n";

    // Intentar con tabs
    $buscar2 = "\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-info\" id=\"btnProbarConexion\">\r\n\t\t\t\t\t\t\t\t\t<i class=\"fa fa-refresh\"></i> Probar Conexión\r\n\t\t\t\t\t\t\t\t</button>";

    $reemplazar2 = "\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-info\" id=\"btnProbarConexion\">\r\n\t\t\t\t\t\t\t\t\t<i class=\"fa fa-refresh\"></i> Probar Conexión\r\n\t\t\t\t\t\t\t\t</button>\r\n\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-success\" id=\"btnAutenticar\" style=\"margin-left: 10px;\">\r\n\t\t\t\t\t\t\t\t\t<i class=\"fa fa-key\"></i> Autenticar y Obtener Tokens\r\n\t\t\t\t\t\t\t\t</button>";

    $contenidoNuevo = str_replace($buscar2, $reemplazar2, $contenido);

    if ($contenidoNuevo !== $contenido) {
        file_put_contents($archivo, $contenidoNuevo);
        echo "✅ Botón agregado exitosamente (con tabs)\n";
    } else {
        echo "❌ Aún no se pudo agregar. Mostrando contexto:\n";
        $lineas = explode("\n", $contenido);
        for ($i = 215; $i < 222; $i++) {
            echo "Línea $i: " . bin2hex(substr($lineas[$i], 0, 50)) . "\n";
        }
    }
}
