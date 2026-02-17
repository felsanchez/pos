<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

echo "Probando envío de factura con ID de documento inválido para obtener lista...\n";

// Login para token
$token = ModeloFactus::mdlGarantizarTokenValido();
if (!$token)
    die("No token\n");

// Datos mínimos para provocar validación de tipos de documento
// Usamos un ID inválido (9999)
$datos = [
    "numbering_range_id" => 1,
    "reference_code" => "TEST-" . time(),
    "observation" => "Prueba tipos doc",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "billing_period" => [
        "start_date" => date('Y-m-d'),
        "start_time" => "00:00:00",
        "end_date" => date('Y-m-d'),
        "end_time" => "23:59:59"
    ],
    "customer" => [
        "identification" => "123456789",
        "dv" => "0",
        "company" => "Prueba SAS",
        "trade_name" => "Prueba",
        "names" => "Prueba",
        "address" => "Calle Falsa 123",
        "email" => "prueba@example.com",
        "phone" => "1234567890",
        "legal_organization_id" => "2",
        "tribute_id" => "21",
        "identification_document_id" => "9999", // ID INVÁLIDO INTENCIONAL
        "municipality_id" => "169"
    ],
    "items" => [] // Sin items para no generar factura real, solo validar cabecera
];

$respuesta = ModeloFactus::mdlCrearFacturaElectronica($token, $datos);

print_r($respuesta);
