<?php
/*=============================================
 HELPERS DE PERMISOS DE PERFILES
 Incluir desde index.php para disponibilidad global.
 
 Regla: Administrador siempre tiene acceso total.
 Los demás perfiles consultan $_SESSION["permisos"].
=============================================*/

/**
 * Verifica si el usuario actual puede VER un módulo.
 * @param string $modulo  Slug del módulo (ej. 'ventas', 'clientes')
 */
function puedeVer(string $modulo): bool
{
    // Administrador y _SystemMaster_ tienen acceso total, siempre
    if (($_SESSION['perfil'] ?? '') === 'Administrador' || ($_SESSION['perfil'] ?? '') === '_SystemMaster_') {
        return true;
    }
    // Fallback: si aún no se cargaron permisos (sesión previa al deploy),
    // permitir acceso para no romper la experiencia del usuario
    if (!isset($_SESSION['permisos'])) {
        return true;
    }

    // Unificar Categorías y Variantes con Productos
    if ($modulo === 'categorias' || $modulo === 'variantes') {
        $modulo = 'productos';
    }

    return !empty($_SESSION['permisos'][$modulo]['ver']);
}

/**
 * Verifica si el usuario actual puede ejecutar una acción sobre un módulo.
 * @param string $modulo  Slug del módulo
 * @param string $accion  'crear' | 'editar' | 'eliminar' | 'imprimir' | 'exportar'
 */
function puedeAccion(string $modulo, string $accion): bool
{
    if (($_SESSION['perfil'] ?? '') === 'Administrador' || ($_SESSION['perfil'] ?? '') === '_SystemMaster_') {
        return true;
    }
    if (!isset($_SESSION['permisos'])) {
        return true;
    }

    // Unificar Categorías y Variantes con Productos
    if ($modulo === 'categorias' || $modulo === 'variantes') {
        $modulo = 'productos';
    }

    return !empty($_SESSION['permisos'][$modulo][$accion]);
}
