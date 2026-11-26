<?php

require_once "../controladores/menu-digital.controlador.php";
require_once "../modelos/menu-digital.modelo.php";

header('Content-Type: application/json; charset=utf-8');

// Leer datos enviados por Bold vía POST en formato JSON
$inputRaw = file_get_contents('php://input');

if (empty($inputRaw)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "No se recibieron datos"]);
    exit;
}

$payload = json_decode($inputRaw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "JSON inválido"]);
    exit;
}

// Extraer identificadores del evento Bold
$tipoEvento = $payload["type"] ?? $payload["event"] ?? $payload["data"]["status"] ?? "DESCONOCIDO";
$subject = $payload["subject"] ?? $payload["order_id"] ?? $payload["data"]["order_id"] ?? $payload["data"]["subject"] ?? "";
$transaccionId = $payload["id"] ?? $payload["data"]["id"] ?? "";

$esAprobado = in_array(strtoupper($tipoEvento), ['APROBADO', 'SALE_APPROVED', 'APPROVED', 'EXITOSO']);

// 1. Verificar si corresponde a una sesión de pre-orden Bold (ej: BOLD-1732238491-1234)
if (!empty($subject) && stripos($subject, 'BOLD-') !== false) {
    if ($esAprobado) {
        $res = ModeloMenuDigital::mdlConfirmarPagoYCrearOrden($subject, $transaccionId);
        http_response_code(200);
        echo json_encode([
            "status" => "ok",
            "mensaje" => "Orden confirmada y creada tras aprobación en Bold",
            "resultado" => $res
        ]);
        exit;
    } else {
        ModeloMenuDigital::mdlRechazarOrdenTemporalBold($subject, $tipoEvento);
        http_response_code(200);
        echo json_encode([
            "status" => "ok",
            "mensaje" => "Pago no aprobado ($tipoEvento)"
        ]);
        exit;
    }
}

// 2. Soporte para código numérico de orden previo (ej: ORD-10025-...)
$codigoOrden = null;
if (preg_match('/ORD-(\d+)/i', $subject, $coincidencias)) {
    $codigoOrden = intval($coincidencias[1]);
} else if (is_numeric($subject)) {
    $codigoOrden = intval($subject);
}

if ($codigoOrden) {
    ControladorMenuDigital::ctrActualizarPagoBold($codigoOrden, $tipoEvento, $transaccionId);

    http_response_code(200);
    echo json_encode([
        "status" => "ok",
        "mensaje" => "Webhook procesado exitosamente",
        "codigo_orden" => $codigoOrden,
        "evento" => $tipoEvento
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    "status" => "ok",
    "mensaje" => "Evento recibido sin orden asociada"
]);
