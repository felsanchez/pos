<?php
/**
 * Clase para gestión segura de sesiones
 * 
 * Proporciona métodos para iniciar sesiones con configuración segura,
 * validar sesiones, manejar timeouts y prevenir ataques de hijacking.
 */
class SessionManager
{

    // Tiempo de inactividad permitido (30 minutos)
    const TIMEOUT_DURATION = 1800; // 30 minutos en segundos

    /**
     * Inicia sesión con configuración segura
     */
    public static function startSecure()
    {
        // Si la sesión ya está iniciada, no hacer nada
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configurar parámetros de cookies de sesión
        session_set_cookie_params([
            'lifetime' => 0,           // Cookie expira al cerrar navegador
            'path' => '/',
            'domain' => '',
            'secure' => false,         // Cambiar a true si usas HTTPS
            'httponly' => true,        // Previene acceso vía JavaScript (XSS)
            'samesite' => 'Strict'     // Previene CSRF
        ]);

        // Configurar nombre de sesión personalizado
        session_name('POS_SESSION');

        // Iniciar sesión
        session_start();

        // Validar sesión
        self::validateSession();

        // Verificar timeout de inactividad
        self::checkTimeout();
    }

    /**
     * Regenera el ID de sesión
     * Usar después del login exitoso para prevenir fijación de sesión
     */
    public static function regenerate()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);

            // Actualizar timestamps
            $_SESSION['last_activity'] = time();
            $_SESSION['created_at'] = time();
        }
    }

    /**
     * Valida la sesión contra hijacking
     */
    private static function validateSession()
    {
        // Primera vez que se crea la sesión
        if (!isset($_SESSION['created_at'])) {
            $_SESSION['created_at'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        // NOTA: Validación de User-Agent desactivada porque algunos navegadores
        // cambian el User-Agent al abrir DevTools, lo que causaría logout involuntario
        // Para mayor seguridad en producción, considerar validar solo cambios drásticos
        /*
        if (isset($_SESSION['user_agent'])) {
            $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if ($_SESSION['user_agent'] !== $currentUserAgent) {
                self::destroy();
                return;
            }
        }
        */

        // Regenerar ID cada 30 minutos (previene fijación de sesión)
        if (isset($_SESSION['created_at'])) {
            if (time() - $_SESSION['created_at'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['created_at'] = time();
            }
        }
    }

    /**
     * Verifica timeout de inactividad
     */
    private static function checkTimeout()
    {
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];

            if ($inactiveTime > self::TIMEOUT_DURATION) {
                // Sesión expirada por inactividad
                $wasLoggedIn = isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] === 'ok';

                self::destroy();

                // Redirigir al login con mensaje solo si estaba autenticado
                if ($wasLoggedIn) {
                    header('Location: login?timeout=1');
                    exit;
                }
            }
        }

        // Actualizar timestamp de última actividad
        $_SESSION['last_activity'] = time();
    }

    /**
     * Destruye la sesión de forma segura
     */
    public static function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Limpiar variables de sesión
            $_SESSION = [];

            // Destruir cookie de sesión
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    '/',
                    '',
                    false,
                    true
                );
            }

            // Destruir sesión
            session_destroy();
        }
    }

    /**
     * Verifica si la sesión está activa
     */
    public static function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE &&
            isset($_SESSION['iniciarSesion']) &&
            $_SESSION['iniciarSesion'] === 'ok';
    }
}
