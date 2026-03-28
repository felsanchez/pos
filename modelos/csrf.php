<?php
/**
 * Clase para manejar protección CSRF (Cross-Site Request Forgery)
 * 
 * Esta clase proporciona métodos para generar y validar tokens CSRF
 * que protegen contra ataques de peticiones falsificadas.
 * 
 * NOTA: La sesión debe estar iniciada por SessionManager antes de usar esta clase
 */
class CSRF
{

    /**
     * Genera un nuevo token CSRF si no existe
     * 
     * @return string Token CSRF
     */
    public static function generateToken()
    {
        // La sesión ya debe estar iniciada por SessionManager

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Obtiene el token CSRF actual
     * 
     * @return string Token CSRF
     */
    public static function getToken()
    {
        return self::generateToken();
    }

    /**
     * Inserta el token CSRF como campo oculto en formularios HTML
     * 
     * Uso: <?php CSRF::insertToken(); ?> dentro de un <form>
     */
    public static function insertToken()
    {
        echo '<script>console.log("DEBUG: Entrando a CSRF::insertToken");</script>';
        $token = self::getToken();
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
        echo '<script>console.log("DEBUG: Saliendo de CSRF::insertToken");</script>';
    }

    /**
     * Valida el token CSRF recibido
     * 
     * Busca el token en $_POST['csrf_token'] o en el header HTTP_X_CSRF_TOKEN
     * 
     * @return bool True si el token es válido, False en caso contrario
     */
    public static function validateToken()
    {
        // La sesión ya debe estar iniciada por SessionManager

        // Obtener token de POST o HEADER
        $token = null;

        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        // Validar que existan ambos tokens
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        // Usar hash_equals para prevenir timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenera el token CSRF
     * 
     * Útil después de operaciones críticas como login o cambio de privilegios
     * 
     * @return string Nuevo token CSRF
     */
    public static function regenerateToken()
    {
        // La sesión ya debe estar iniciada por SessionManager

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Obtiene el token como meta tag HTML
     * 
     * Útil para incluir en el <head> y usar en JavaScript
     * 
     * @return string Meta tag HTML con el token
     */
    public static function getMetaTag()
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
