<?php

require_once __DIR__ . '/../crm/models/ClienteCRMModel.php';

$telefono  = trim($params['telefono'] ?? '');
$nombre    = trim($params['nombre'] ?? '');
$direccion = trim($params['direccion'] ?? '');

if ($telefono === '') {
    errorResponse('Debe enviar el teléfono.');
}

$resultado = ClienteCRMModel::procesarCliente(
    $telefono,
    $nombre,
    $direccion
);

successResponse($resultado);