<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/response.php';

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey !== API_KEY) {

    errorResponse('API Key inválida.', 401);

}