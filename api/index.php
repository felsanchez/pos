<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/database.php';

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Solo se permiten peticiones POST.', 405);
}

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    errorResponse('JSON inválido.');
}

// Debug (opcional)
file_put_contents(
    __DIR__ . '/debug.json',
    json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// Tool solicitada
$tool = $input['tool'] ?? '';

if ($tool === '') {
    errorResponse('No se especificó la Tool.');
}

// ==========================================================
// Seleccionar automáticamente el tenant
// ==========================================================
$ownerPhone = $input['owner_phone'] ?? '';

if (!empty($ownerPhone)) {
    Database::setOwnerPhone($ownerPhone);
}

// Parámetros
$params = $input['params'] ?? [];

// Ruta del archivo Tool
$toolFile = __DIR__ . '/tools/' . $tool . '.php';

if (!file_exists($toolFile)) {
    errorResponse("La Tool '{$tool}' no existe.", 404);
}

// Ejecutar Tool
require_once $toolFile;