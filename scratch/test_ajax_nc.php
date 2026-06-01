<?php
// Iniciar sesión y simular entorno de autenticación
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id_bodega"] = 1;

// Cargar config y variables de entorno para que conexion funcione
require_once "config.php";
require_once "modelos/conexion.php";
require_once "modelos/csrf.php";

$db = Conexion::conectar();

// Obtener un ID de usuario válido de la base de datos
$stmtUser = $db->prepare("SELECT id FROM usuarios LIMIT 1");
$stmtUser->execute();
$userId = $stmtUser->fetchColumn();
if (!$userId) {
    die("No hay usuarios registrados en la base de datos para realizar el test.\n");
}
$_SESSION["id"] = intval($userId);

// Generar y simular token CSRF
$token = CSRF::generateToken();
$_POST['csrf_token'] = $token;

// Simular parámetros POST enviados desde la vista
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['accion'] = 'generarNotaCredito';
$_POST['idVenta'] = '12';
$_POST['motivo'] = '1';
$_POST['idCliente'] = '3';
$_POST['metodoPago'] = 'Efectivo';
$_POST['observacion'] = 'Test desde simulacion AJAX';
$_POST['listaProductos'] = json_encode([
    [
        "id" => "14",
        "codigo" => "P001",
        "descripcion" => "Cerdo",
        "precio" => "75",
        "impuesto" => "8",
        "cantidad" => "1",
        "total" => "75"
    ]
]);
$_POST['idUsuario'] = $_SESSION["id"];
$_POST['idBodegaSesion'] = $_SESSION["id_bodega"];

echo "Ejecutando simulacion de ajax/notas-credito.ajax.php...\n";

// Cambiar de directorio a 'ajax' para resolver rutas relativas
chdir('ajax');

// Capturar salida del script AJAX
ob_start();
require_once "notas-credito.ajax.php";
$output = ob_get_clean();

echo "Salida AJAX:\n";
echo $output . "\n";

// Regresar al directorio raíz
chdir('..');

// Verificar en la BD
echo "\n=== Verificando en la base de datos ===\n";
$stmt = $db->prepare("SELECT * FROM notas_credito ORDER BY id DESC LIMIT 1");
$stmt->execute();
$nc = $stmt->fetch(PDO::FETCH_ASSOC);
if ($nc && $nc['observacion'] == 'Test desde simulacion AJAX') {
    echo "Ultima nota credito insertada:\n";
    print_r($nc);
    
    // Limpiar para no ensuciar la base de datos del cliente
    $stmtDelete = $db->prepare("DELETE FROM notas_credito WHERE id = :id");
    $stmtDelete->execute([':id' => $nc['id']]);
    echo "\nLimpieza exitosa del registro temporal.\n";
} else {
    echo "Error: No se encontro la nota insertada por el AJAX!\n";
}
