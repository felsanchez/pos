<?php
require_once __DIR__ . '/../modelos/logger.php';

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/clientes.modelo.php";
require_once "../controladores/clientes.controlador.php";
require_once "../controladores/productos.controlador.php";
require_once "../controladores/notificaciones.controlador.php";
require_once "../modelos/notificaciones.modelo.php";

// Configuración
$logFile = "../logs/webhook_bold.log";
$debug = true;

// Función de log
function logWebhook($msg)
{
    global $logFile;
    $date = date('Y-m-d H:i:s');
    Logger::debug("[$date] $msg\n");
}

// 1. Recibir el payload
$input = file_get_contents("php://input");
logWebhook("Recibido webhook: " . $input);

$data = json_decode($input, true);

if (!$data) {
    logWebhook("Error: Payload vacío o JSON inválido");
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// 2. Validar datos mínimos (Esto se ajustará cuando tengamos el payload real de BOLD)
// Por ahora asumimos una estructura genérica para pruebas
/*
Estructura esperada (hipotética):
{
    "payment_status": "APPROVED",
    "order_id": "BOLD-12345",
    "amount": 50000,
    "customer": {
        "email": "cliente@email.com",
        "name": "Juan Perez",
        "phone": "3001234567"
    },
    "items": [
        {
            "id": "10", // ID del producto en tu sistema
            "quantity": 1,
            "price": 50000
        }
    ]
}
*/

// Si es solo una prueba de conexión
if (isset($data['test']) && $data['test'] == true) {
    logWebhook("Test de conexión recibido exitosamente");
    echo json_encode(["status" => "success", "message" => "Webhook connection successful"]);
    exit;
}

// 3. Procesar la orden
try {
    // Aquí llamaremos al controlador para crear la venta
    // Como no tenemos el payload real, por ahora solo logueamos que "intentaríamos" crearla

    // TODO: Mapear datos de BOLD a la estructura de tu controlador
    // $datosVenta = [ ... ];
    // $respuesta = ControladorVentas::ctrCrearVentaAPI($datosVenta);

    logWebhook("Procesando orden (simulación)...");

    // Respuesta exitosa al webhook
    echo json_encode(["status" => "success", "message" => "Order processed"]);

} catch (Exception $e) {
    logWebhook("Excepción: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

