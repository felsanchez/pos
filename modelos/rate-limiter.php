<?php
/**
 * Clase para Rate Limiting - Prevención de ataques de fuerza bruta
 * 
 * Limita los intentos de login fallidos por dirección IP
 * y bloquea temporalmente después de exceder el límite
 */

require_once "conexion.php";

class RateLimiter
{

    const MAX_ATTEMPTS = 5;        // Máximo de intentos permitidos
    const LOCKOUT_TIME = 900;      // 15 minutos en segundos
    const CLEANUP_TIME = 86400;    // 24 horas en segundos

    /**
     * Verifica si una IP está bloqueada
     * 
     * @param string $ip Dirección IP a verificar
     * @return bool True si está bloqueada, False si no
     */
    public static function isBlocked($ip)
    {
        $attempts = self::getAttempts($ip);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lastAttempt = self::getLastAttemptTime($ip);

            if ($lastAttempt) {
                $timeSince = time() - strtotime($lastAttempt);

                if ($timeSince < self::LOCKOUT_TIME) {
                    return true;
                } else {
                    // Limpiar intentos antiguos si ya pasó el tiempo de bloqueo
                    self::clearAttempts($ip);
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Registra un intento de login fallido
     * 
     * @param string $ip Dirección IP
     * @param string $username Usuario intentado (opcional)
     */
    public static function recordAttempt($ip, $username = null)
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO login_attempts (ip_address, username, attempt_time) 
                 VALUES (:ip, :username, NOW())"
            );

            $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->execute();
            $stmt = null;

            // Limpiar registros antiguos
            self::cleanup();
        } catch (PDOException $e) {
            // Error 1146 o SQLSTATE 42S02: Tabla no existe
            if ($e->getCode() == '42S02' || strpos($e->getMessage(), '1146') !== false) {
                self::createTable();
            } else {
                error_log("Error en RateLimiter::recordAttempt: " . $e->getMessage());
            }
        }
    }

    /**
     * Obtiene el número de intentos en el período de bloqueo
     * 
     * @param string $ip Dirección IP
     * @return int Número de intentos
     */
    public static function getAttempts($ip)
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT COUNT(*) as attempts 
                 FROM login_attempts 
                 WHERE ip_address = :ip 
                 AND attempt_time > DATE_SUB(NOW(), INTERVAL :lockout SECOND)"
            );

            $lockout = self::LOCKOUT_TIME;
            $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->bindParam(":lockout", $lockout, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch();
            $stmt = null;

            return $result['attempts'] ?? 0;
        } catch (PDOException $e) {
            if ($e->getCode() == '42S02' || strpos($e->getMessage(), '1146') !== false) {
                self::createTable();
                return 0;
            }
            error_log("Error en RateLimiter::getAttempts: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crea la tabla de intentos de login si no existe
     */
    private static function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ip_address` varchar(45) NOT NULL,
          `username` varchar(100) DEFAULT NULL,
          `attempt_time` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `ip_address` (`ip_address`),
          KEY `attempt_time` (`attempt_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        try {
            Conexion::conectar()->exec($sql);
        } catch (PDOException $e) {
            error_log("Error crítico: No se pudo crear la tabla login_attempts: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el tiempo del último intento
     * 
     * @param string $ip Dirección IP
     * @return string|null Timestamp del último intento
     */
    private static function getLastAttemptTime($ip)
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT attempt_time 
                 FROM login_attempts 
                 WHERE ip_address = :ip 
                 ORDER BY attempt_time DESC 
                 LIMIT 1"
            );

            $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch();
            $stmt = null;

            return $result['attempt_time'] ?? null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Limpia todos los intentos de una IP
     * 
     * @param string $ip Dirección IP
     */
    public static function clearAttempts($ip)
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM login_attempts WHERE ip_address = :ip"
            );

            $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->execute();
            $stmt = null;
        } catch (PDOException $e) {
            // Si falla la limpieza, no bloqueamos el flujo
        }
    }

    /**
     * Limpia registros antiguos (más de 24 horas)
     */
    private static function cleanup()
    {
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM login_attempts 
                 WHERE attempt_time < DATE_SUB(NOW(), INTERVAL :cleanup SECOND)"
            );

            $cleanup = self::CLEANUP_TIME;
            $stmt->bindParam(":cleanup", $cleanup, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
        } catch (PDOException $e) {
            // Ignorar errores en limpieza automática
        }
    }

    /**
     * Obtiene el tiempo restante de bloqueo en minutos
     * 
     * @param string $ip Dirección IP
     * @return int Minutos restantes de bloqueo
     */
    public static function getRemainingTime($ip)
    {
        $lastAttempt = self::getLastAttemptTime($ip);

        if ($lastAttempt) {
            $timeSince = time() - strtotime($lastAttempt);
            $remaining = self::LOCKOUT_TIME - $timeSince;

            return max(1, ceil($remaining / 60)); // Mínimo 1 minuto
        }

        return 0;
    }
}
