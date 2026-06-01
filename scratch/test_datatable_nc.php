<?php
// Iniciar sesión y simular entorno de administrador
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id_bodega"] = 1;

require_once "config.php";
require_once "modelos/conexion.php";
require_once "modelos/csrf.php";

$db = Conexion::conectar();

// 1. Obtener un ID de usuario válido de la base de datos
$stmtUser = $db->prepare("SELECT id FROM usuarios LIMIT 1");
$stmtUser->execute();
$userId = $stmtUser->fetchColumn();
if (!$userId) {
    die("No hay usuarios registrados en la base de datos.\n");
}

// Generar y simular token CSRF
$token = CSRF::generateToken();
$_POST['csrf_token'] = $token;

// 2. Insertar fila simulada en notas_credito
$stmt = $db->prepare("INSERT INTO notas_credito (
    id_venta_original, numero_factura_original, tipo_nota, motivo,
    productos, monto_total, estado_dian, numero_nota_credito,
    cufe_nc, qr_data_nc, xml_dian_nc, pdf_dian_nc, mensaje_dian,
    fecha_envio_dian, id_usuario, id_cliente, id_bodega, observacion, metodo_pago
) VALUES (
    12, 'SETP990000254', 'devolucion_parcial', '1',
    '[]', 75.00, 'borrador', 'NC71-TEST-DT',
    '', '', '', '', 'Borrador test',
    NULL, :id_usuario, 3, 1, 'Mock row for DataTable test', 'Efectivo'
)");
$stmt->execute([':id_usuario' => $userId]);
$idNota = $db->lastInsertId();
echo "Insertada nota credito de prueba ID: $idNota\n";

// 3. Simular parámetros de Datatable POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['search']['value'] = 'NC71-TEST-DT';
$_POST['order'][0]['column'] = 0;
$_POST['order'][0]['dir'] = 'desc';

// chdir a ajax
chdir('ajax');

ob_start();
require "notas-credito.ajax.php";
$res = ob_get_clean();

// chdir de vuelta
chdir('..');

echo "Respuesta JSON del DataTable:\n";
echo $res . "\n";

// 4. Limpieza
$db->exec("DELETE FROM notas_credito WHERE id = $idNota");
echo "Limpieza exitosa de la nota de prueba.\n";
