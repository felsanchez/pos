<?php

header('Content-Type: application/json; charset=utf-8');

// API Key del CRM
define('CRM_API_KEY', 'kontrolpos_2026');

require_once __DIR__ . '/controllers/CRMController.php';

$apiKey = $_SERVER["HTTP_X_API_KEY"] ?? "";

if ($apiKey !== CRM_API_KEY) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "error" => "API Key inválida."
    ]);

    exit;
}

try {

    // Leer el JSON enviado por n8n
    $json = file_get_contents("php://input");

    $datos = json_decode($json, true);

    if (!is_array($datos)) {
        throw new Exception("JSON inválido.");
    }

    // Guardar el teléfono del propietario para seleccionar el tenant
    if (!empty($datos["owner_phone"])) {
        Database::setOwnerPhone($datos["owner_phone"]);
    }

    // Procesar CRM
    $respuesta = CRMController::procesar($datos);

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}