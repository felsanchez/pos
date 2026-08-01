<?php

require_once "../modelos/conexion.php";
require_once "../modelos/conocimiento.modelo.php";

// (Opcional) Proteger con un token
$token = $_GET["token"] ?? "";

if ($token !== "MI_TOKEN_SUPER_SEGURO_123") {
    http_response_code(401);
    die(json_encode([
        "success" => false,
        "message" => "No autorizado"
    ]));
}

// Obtener todos los artículos
$articulos = ModeloConocimiento::mdlMostrarArticulos(
    "empresa_conocimiento",
    null,
    null
);

// Devolver JSON
header("Content-Type: application/json; charset=utf-8");

echo json_encode($articulos, JSON_UNESCAPED_UNICODE);