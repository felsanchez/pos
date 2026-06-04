<?php

class ControladorPerfiles
{
    /** Lista todos los perfiles (para la UI de configuración) */
    public static function ctrObtenerPerfiles(): array
    {
        return ModeloPerfiles::mdlObtenerPerfiles();
    }

    /** Retorna módulos disponibles */
    public static function ctrObtenerModulos(): array
    {
        return ModeloPerfiles::mdlObtenerModulos();
    }

    /** Retorna permisos de un perfil en formato JSON (para modal AJAX) */
    public static function ctrObtenerPermisosPerfil(): void
    {
        header('Content-Type: application/json');
        $idPerfil = (int)($_POST['id_perfil'] ?? 0);
        if (!$idPerfil) {
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }
        $permisos = ModeloPerfiles::mdlObtenerPermisosDelPerfil($idPerfil);
        echo json_encode(['permisos' => $permisos]);
        exit;
    }

    /** Crea un nuevo perfil con sus permisos */
    public static function ctrCrearPerfil(): void
    {
        header('Content-Type: application/json');

        if (!CSRF::validateToken()) {
            echo json_encode(['resultado' => 'error_csrf']);
            exit;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $permisos    = $_POST['permisos'] ?? [];

        if (empty($nombre)) {
            echo json_encode(['resultado' => 'error_nombre']);
            exit;
        }

        $resultado = ModeloPerfiles::mdlCrearPerfil($nombre, $descripcion, $permisos);
        echo json_encode(['resultado' => $resultado]);
        exit;
    }

    /** Actualiza un perfil existente */
    public static function ctrActualizarPerfil(): void
    {
        header('Content-Type: application/json');

        if (!CSRF::validateToken()) {
            echo json_encode(['resultado' => 'error_csrf']);
            exit;
        }

        $id          = (int)($_POST['id_perfil'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $permisos    = $_POST['permisos'] ?? [];

        if (!$id || empty($nombre)) {
            echo json_encode(['resultado' => 'error_datos']);
            exit;
        }

        $resultado = ModeloPerfiles::mdlActualizarPerfil($id, $nombre, $descripcion, $permisos);
        
        // Si el perfil actualizado es el mismo del usuario en sesión, actualizar permisos en tiempo real
        if ($resultado === 'ok' && isset($_SESSION['perfil']) && $_SESSION['perfil'] === $nombre) {
            $_SESSION["permisos"] = ModeloPerfiles::mdlCargarPermisosEnSesion($nombre);
        }

        echo json_encode(['resultado' => $resultado]);
        exit;
    }

    /** Elimina un perfil */
    public static function ctrEliminarPerfil(): void
    {
        header('Content-Type: application/json');

        if (!CSRF::validateToken()) {
            echo json_encode(['resultado' => 'error_csrf']);
            exit;
        }

        $id = (int)($_POST['id_perfil'] ?? 0);
        if (!$id) {
            echo json_encode(['resultado' => 'error_datos']);
            exit;
        }

        $resultado = ModeloPerfiles::mdlEliminarPerfil($id);
        echo json_encode(['resultado' => $resultado]);
        exit;
    }
}
