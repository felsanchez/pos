<?php

function successResponse($data = null, $message = '')
{
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function errorResponse($message, $code = 400)
{
    http_response_code($code);

    echo json_encode([
        "success" => false,
        "message" => $message,
        "data" => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
}