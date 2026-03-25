<?php
require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../modelos/conexion.php";
require_once "../modelos/perfiles.modelo.php";
require_once "../modelos/csrf.php";

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'obtenerPermisos':
        $idPerfil = (int)($_POST['id_perfil'] ?? 0);
        if (!$idPerfil) {
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }
        $permisos = ModeloPerfiles::mdlObtenerPermisosDelPerfil($idPerfil);
        echo json_encode(['permisos' => $permisos]);
        break;

    case 'crearPerfil':
        if (!CSRF::validateToken()) { echo json_encode(['resultado' => 'error_csrf']); exit; }
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $permisos    = $_POST['permisos'] ?? [];
        if (empty($nombre)) { echo json_encode(['resultado' => 'error_nombre']); exit; }
        $resultado = ModeloPerfiles::mdlCrearPerfil($nombre, $descripcion, $permisos);
        echo json_encode(['resultado' => $resultado]);
        break;

    case 'actualizarPerfil':
        if (!CSRF::validateToken()) { echo json_encode(['resultado' => 'error_csrf']); exit; }
        $id          = (int)($_POST['id_perfil'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $permisos    = $_POST['permisos'] ?? [];
        if (!$id || empty($nombre)) { echo json_encode(['resultado' => 'error_datos']); exit; }
        $resultado = ModeloPerfiles::mdlActualizarPerfil($id, $nombre, $descripcion, $permisos);
        echo json_encode(['resultado' => $resultado]);
        break;

    case 'eliminarPerfil':
        if (!CSRF::validateToken()) { echo json_encode(['resultado' => 'error_csrf']); exit; }
        $id = (int)($_POST['id_perfil'] ?? 0);
        if (!$id) { echo json_encode(['resultado' => 'error_datos']); exit; }
        $resultado = ModeloPerfiles::mdlEliminarPerfil($id);
        echo json_encode(['resultado' => $resultado]);
        break;

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
}
