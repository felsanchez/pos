<?php

/**
 * Sistema de Logging de Errores
 * Permite registrar errores, advertencias e información en archivos
 * organizados por fecha con formato JSON para fácil lectura y filtrado
 */
class Logger {

    // Niveles de log
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';

    private static $logDir = __DIR__ . '/../storage/logs/';
    private static $maxFileSize = 5242880; // 5MB

    /**
     * Registra un error
     */
    public static function error($message, $context = []) {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * Registra una advertencia
     */
    public static function warning($message, $context = []) {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * Registra información
     */
    public static function info($message, $context = []) {
        self::log(self::INFO, $message, $context);
    }

    /**
     * Registra información de debug
     */
    public static function debug($message, $context = []) {
        self::log(self::DEBUG, $message, $context);
    }

    /**
     * Método principal de logging
     */
    private static function log($level, $message, $context = []) {
        try {
            // Asegurar que el directorio existe
            if (!file_exists(self::$logDir)) {
                mkdir(self::$logDir, 0755, true);
            }

            // Nombre del archivo por fecha
            $filename = self::$logDir . date('Y-m-d') . '.log';

            // Rotar archivo si es muy grande
            if (file_exists($filename) && filesize($filename) > self::$maxFileSize) {
                $newFilename = self::$logDir . date('Y-m-d_His') . '.log';
                rename($filename, $newFilename);
            }

            // Preparar datos del log
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'user' => isset($_SESSION['nombre']) ? $_SESSION['nombre'] : (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'guest'),
                'ip' => self::getClientIP(),
                'url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI',
                'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI'
            ];

            // Si es una excepción, agregar más detalles
            if (isset($context['exception']) && $context['exception'] instanceof Exception) {
                $e = $context['exception'];
                $logEntry['exception'] = [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
            }

            // Escribir en el archivo (un JSON por línea)
            $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            file_put_contents($filename, $logLine, FILE_APPEND | LOCK_EX);

        } catch (Exception $e) {
            // Si falla el logging, intentar registrar en error_log de PHP
            error_log("Logger failed: " . $e->getMessage());
            error_log("Original message: " . $message);
        }
    }

    /**
     * Obtiene la IP del cliente
     */
    private static function getClientIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * Lee los logs de un archivo específico
     */
    public static function readLogs($date = null, $level = null, $limit = 100) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $filename = self::$logDir . $date . '.log';

        if (!file_exists($filename)) {
            return [];
        }

        $logs = [];
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Leer de atrás hacia adelante (logs más recientes primero)
        $lines = array_reverse($lines);

        foreach ($lines as $line) {
            $entry = json_decode($line, true);

            if ($entry === null) {
                continue;
            }

            // Filtrar por nivel si se especifica (y no es vacío)
            if ($level !== null && $level !== '' && $entry['level'] !== $level) {
                continue;
            }

            $logs[] = $entry;

            // Limitar resultados
            if (count($logs) >= $limit) {
                break;
            }
        }

        return $logs;
    }

    /**
     * Obtiene la lista de archivos de log disponibles
     */
    public static function getLogFiles() {
        if (!file_exists(self::$logDir)) {
            return [];
        }

        $files = glob(self::$logDir . '*.log');
        $logFiles = [];

        foreach ($files as $file) {
            $basename = basename($file);
            $logFiles[] = [
                'filename' => $basename,
                'date' => str_replace('.log', '', $basename),
                'size' => filesize($file),
                'modified' => filemtime($file)
            ];
        }

        // Ordenar por fecha más reciente
        usort($logFiles, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $logFiles;
    }

    /**
     * Limpia logs antiguos
     */
    public static function cleanOldLogs($days = 30) {
        if (!file_exists(self::$logDir)) {
            return 0;
        }

        $files = glob(self::$logDir . '*.log');
        $deleted = 0;
        $threshold = time() - ($days * 24 * 60 * 60);

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }

     /**
     * Elimina logs específicos por timestamp
     * @param array $logsToDelete Array de objetos con timestamp y fecha
     * @return int Número de logs eliminados
     */

    public static function deleteLogs($logsToDelete) {

        if (!is_array($logsToDelete) || empty($logsToDelete)) {
            return 0;
        }

         $deleted = 0; 

        // Agrupar logs por fecha para optimizar el proceso
        $logsByDate = [];
        foreach ($logsToDelete as $log) {
            $fecha = isset($log['fecha']) ? $log['fecha'] : date('Y-m-d');
            if (!isset($logsByDate[$fecha])) {
                $logsByDate[$fecha] = [];
            }

            $logsByDate[$fecha][] = $log['timestamp'];
        }


        // Procesar cada archivo de log
        foreach ($logsByDate as $fecha => $timestamps) {
            $filename = self::$logDir . $fecha . '.log';
 
            if (!file_exists($filename)) {
                continue;
            }
 
            // Leer todas las líneas del archivo
            $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $newLines = [];
 
            foreach ($lines as $line) {
                $entry = json_decode($line, true); 

                if ($entry === null) {
                    // Mantener líneas que no se puedan decodificar

                    $newLines[] = $line;
                    continue;
                } 

                // Si el timestamp no está en la lista de eliminación, mantener la línea
                if (!in_array($entry['timestamp'], $timestamps)) {
                    $newLines[] = $line;

                } else {
                    $deleted++;
                }
            }

             // Reescribir el archivo sin los logs eliminados
            if (count($newLines) > 0) {
                file_put_contents($filename, implode(PHP_EOL, $newLines) . PHP_EOL, LOCK_EX);
            } else {
                // Si no quedan logs, eliminar el archivo
                unlink($filename);
            }
        }

        return $deleted;
    }

    /**
     * Obtiene estadísticas de logs
     */
    public static function getStats($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $logs = self::readLogs($date, null, PHP_INT_MAX);

        $stats = [
            'total' => count($logs),
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'debug' => 0
        ];

        foreach ($logs as $log) {
            switch ($log['level']) {
                case self::ERROR:
                    $stats['errors']++;
                    break;
                case self::WARNING:
                    $stats['warnings']++;
                    break;
                case self::INFO:
                    $stats['info']++;
                    break;
                case self::DEBUG:
                    $stats['debug']++;
                    break;
            }
        }

        return $stats;
    }
}

/**
 * Registrar manejador global de errores de PHP
 */
function setupErrorHandler() {
    // Manejador de errores
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_NOTICE => 'NOTICE',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];

        $errorType = isset($errorTypes[$errno]) ? $errorTypes[$errno] : 'UNKNOWN';

        Logger::error("PHP Error: [{$errorType}] {$errstr}", [
            'file' => $errfile,
            'line' => $errline,
            'type' => $errorType,
            'errno' => $errno
        ]);

        // Retornar false para que el manejador de errores de PHP también se ejecute
        return false;
    });

    // Manejador de excepciones no capturadas
    set_exception_handler(function($exception) {
        Logger::error("Uncaught Exception: " . $exception->getMessage(), [
            'exception' => $exception
        ]);

        // En producción, mostrar mensaje genérico
        if (env('APP_ENV') === 'production') {
            echo "Ha ocurrido un error. Por favor, contacte al administrador.";
        } else {
            // En desarrollo, mostrar detalles
            echo "<pre>";
            echo "Error: " . $exception->getMessage() . "\n";
            echo "File: " . $exception->getFile() . "\n";
            echo "Line: " . $exception->getLine() . "\n";
            echo "</pre>";
        }
    });

    // Manejador de errores fatales
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            Logger::error("Fatal Error: {$error['message']}", [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);
        }
    });
}