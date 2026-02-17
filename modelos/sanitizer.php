<?php
/**
 * Clase para sanitización de datos y prevención de XSS
 * 
 * Proporciona métodos para sanitizar diferentes tipos de salidas
 * y prevenir ataques de Cross-Site Scripting (XSS)
 */
class Sanitizer
{

    /**
     * Sanitiza texto para salida HTML
     * Previene XSS convirtiendo caracteres especiales a entidades HTML
     * 
     * @param string $text Texto a sanitizar
     * @return string Texto sanitizado
     */
    public static function html($text)
    {
        if ($text === null || $text === '') {
            return '';
        }
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitiza para uso en atributos HTML
     * 
     * @param string $text Texto a sanitizar
     * @return string Texto sanitizado
     */
    public static function attr($text)
    {
        return self::html($text);
    }

    /**
     * Sanitiza para uso en JavaScript
     * Codifica el texto como JSON seguro
     * 
     * @param string $text Texto a sanitizar
     * @return string Texto sanitizado en formato JSON
     */
    public static function js($text)
    {
        if ($text === null || $text === '') {
            return '""';
        }
        return json_encode($text, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Sanitiza URL
     * Solo permite esquemas seguros (http, https, mailto)
     * 
     * @param string $url URL a sanitizar
     * @return string URL sanitizada o vacía si no es segura
     */
    public static function url($url)
    {
        if ($url === null || $url === '') {
            return '';
        }

        // Solo permitir http, https, mailto
        $allowed = ['http', 'https', 'mailto'];
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($scheme && !in_array(strtolower($scheme), $allowed)) {
            return '';
        }

        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitiza array recursivamente
     * Útil para sanitizar arrays de datos antes de mostrarlos
     * 
     * @param mixed $data Array o string a sanitizar
     * @return mixed Datos sanitizados
     */
    public static function array($data)
    {
        if (!is_array($data)) {
            return self::html($data);
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_array($value) ? self::array($value) : self::html($value);
        }
        return $sanitized;
    }
}

/**
 * Función helper global para sanitización rápida
 * Alias corto de Sanitizer::html()
 * 
 * @param string $text Texto a sanitizar
 * @return string Texto sanitizado
 */
function e($text)
{
    return Sanitizer::html($text);
}
