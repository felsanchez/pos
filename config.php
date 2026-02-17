<?php

 

/**
 * Carga las variables de entorno desde el archivo .env
 * Este archivo debe ser incluido antes de usar las variables de entorno
 */

 

function cargarEnv($rutaArchivo = __DIR__ . '/.env') {

    if (!file_exists($rutaArchivo)) {
        throw new Exception("El archivo .env no existe en: " . $rutaArchivo);

    }

 
    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

 
    foreach ($lineas as $linea) {

        // Ignorar comentarios
        if (strpos(trim($linea), '#') === 0) {
            continue;
        }

 

        // Parsear líneas con formato CLAVE=valor
        if (strpos($linea, '=') !== false) {
            list($nombre, $valor) = explode('=', $linea, 2);
            $nombre = trim($nombre);
            $valor = trim($valor);
 

            // Establecer la variable de entorno
            if (!array_key_exists($nombre, $_ENV)) {
                $_ENV[$nombre] = $valor;
                putenv("$nombre=$valor");
            }

        }

    }

}

 

/**
 * Obtiene el valor de una variable de entorno
 *

 * @param string $clave Nombre de la variable

 * @param mixed $porDefecto Valor por defecto si no existe

 * @return mixed

 */

function env($clave, $porDefecto = null) {

    $valor = getenv($clave);
 

    if ($valor === false) {
        $valor = isset($_ENV[$clave]) ? $_ENV[$clave] : $porDefecto;
    }
 
    return $valor;
}

 
// Cargar las variables al incluir este archivo
cargarEnv();

// Configurar zona horaria
$timezone = env('APP_TIMEZONE', 'America/Guatemala');
date_default_timezone_set($timezone);

// Cargar el sistema de logging
require_once __DIR__ . '/modelos/logger.php';

// Configurar manejadores de errores globales si estamos en modo desarrollo
if (env('APP_DEBUG', false) === 'true' || env('APP_DEBUG', false) === true) {
    setupErrorHandler();
}