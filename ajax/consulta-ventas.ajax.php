<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once "../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../modelos/conexion.php";
require_once "../modelos/csrf.php";
require_once "../modelos/helpers.php";

// Verificar sesión
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    if (ob_get_length()) ob_clean();
    http_response_code(401);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'No autorizado']));
}

// Validar CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken()) {
        if (ob_get_length()) ob_clean();
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Token de seguridad inválido. Recarga la página.']));
    }
}

// Obtener y sanitizar código
$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$codigo = preg_replace('/[^0-9]/', '', $codigo);

if (empty($codigo)) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Debes ingresar un código de venta válido (solo números).']));
}

// Buscar venta con JOIN a clientes y usuarios en una sola consulta
try {
    $db   = Conexion::conectar();
    $stmt = $db->prepare(
        "SELECT v.*,
                c.nombre AS nombre_cliente,
                c.documento AS documento_cliente,
                c.telefono AS telefono_cliente,
                u.nombre AS nombre_vendedor
         FROM ventas v
         LEFT JOIN clientes c ON v.id_cliente = c.id
         LEFT JOIN usuarios u ON v.id_vendedor = u.id
         WHERE v.codigo = :codigo
         LIMIT 1"
    );
    $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
    $stmt->execute();
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Error al consultar la base de datos.']));
}

if (!$venta) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    die(json_encode(['error' => 'No se encontró ninguna venta con el código ' . htmlspecialchars($codigo) . '.']));
}

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode($venta, JSON_UNESCAPED_UNICODE);
