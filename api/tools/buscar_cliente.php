<?php

require_once __DIR__ . '/../models/cliente.model.php';

// Obtener teléfono enviado por el HTTP
$telefono = trim($params['telefono'] ?? '');

if ($telefono === '') {

    errorResponse('Debe enviar el teléfono.');

}

// Buscar cliente
$cliente = ClienteModel::buscarPorTelefono($telefono);

// Respuesta
successResponse($cliente);