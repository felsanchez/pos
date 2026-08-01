<?php
/**
 * ============================================================
 * KONTROL POS - API IA
 * Configuración General
 * ============================================================
 */

date_default_timezone_set('America/Bogota');

// ============================================================
// Seguridad
// ============================================================

define('API_KEY', 'mi_api_key_secreta_2024');

// ============================================================
// Base de Datos Master (SaaS)
// Aquí se encuentra la tabla clientes_tenants
// ============================================================

define('MASTER_DB_HOST', 'localhost');
define('MASTER_DB_NAME', 'u933614678_master');
define('MASTER_DB_USER', 'u933614678_root_gestion');
define('MASTER_DB_PASS', 'Ur6lnD~*U2l&');

// ============================================================
// Base de Datos por defecto
// Se utiliza únicamente cuando NO se recibe owner_phone
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'u933614678_gestion');
define('DB_USER', 'u933614678_root_gestion');
define('DB_PASS', 'Ur6lnD~*U2l&');

define('DB_CHARSET', 'utf8mb4');

// ============================================================
// Aplicación
// ============================================================

define('APP_URL', 'https://gestion.kontrolpos.com/');

// ============================================================
// Configuración API
// ============================================================

define('API_VERSION', '1.0');
define('MAX_RESULTADOS', 10);