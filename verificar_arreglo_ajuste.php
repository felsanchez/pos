<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

session_start();
$_SESSION['id'] = 14;

$auth = ControladorFactus::ctrAutenticar();
$token = $auth['token'];

// Datos ficticios para probar el payload y el endpoint
$datosNota = [
    "numbering_range_id" => 1193,
    "billing_reference" => [
        "number" => "SEDS984000008",
        "uuid" => "d60eafc73e3f597dae276552010598864a2a65ddd34fc5fccea83bf9d7d49036f56217d9b2bec56310d7c6153148c3f0",
        "issue_date" => "2026-02-24"
    ],
    "correction_concept_id" => 2,
    "observation" => "Prueba final de corrección endpoint",
    "payment_form" => "1",
    "payment_due_date" => date('Y-m-d'),
    "payment_method_code" => "10",
    "items" => [
        [
            "scheme_id" => "1",
            "name" => "Item de prueba",
            "code_reference" => "TEST01",
            "quantity" => 1,
            "discount_rate" => "0.00",
            "price" => "100.00",
            "tax_rate" => "0.00",
            "unit_measure_id" => 70,
            "standard_code_id" => 1,
            "is_excluded" => 1,
            "tribute_id" => 7
        ]
    ]
];

echo "Enviando Nota de Ajuste al nuevo endpoint...\n";
$resultado = ModeloFactus::mdlCrearNotaAjusteDS($token, $datosNota);

echo "HTTP CODE: " . $resultado['http_code'] . "\n";
echo "RESPONSE: " . $resultado['respuesta'] . "\n";

if ($resultado['http_code'] == 201 || $resultado['http_code'] == 200) {
    echo "\n✅ ÉXITO: El endpoint está funcionando correctamente.\n";
} else {
    echo "\n❌ ERROR: Sigue habiendo problemas con el endpoint o los datos.\n";
}
