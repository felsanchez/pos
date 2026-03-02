<?php
require 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
require 'c:/xampp/htdocs/pos/modelos/factus.modelo.php';
require 'c:/xampp/htdocs/pos/modelos/proveedores.modelo.php';

$res = ControladorFactus::ctrAutenticar();
$tk = $res['token'];

// Sending ID '3' with a brand new NIT to see what Factus thinks 3 is
$payload = [
    'numbering_range_id' => 1192,
    'reference_code' => 'TEST-DOC-' . time(),
    'observation' => '',
    'payment_form' => '1',
    'payment_due_date' => '2026-02-27',
    'payment_method_code' => '10',
    'provider' => [
        'identification' => '900123555',
        'dv' => '0',
        'company' => '',
        'trade_name' => 'Unique Brand Provider',
        'names' => 'Unique Brand Provider',
        'address' => 'calle 1',
        'email' => 'test@test.com',
        'phone' => '000',
        'legal_organization_id' => '2',
        'tribute_id' => '21',
        'identification_document_id' => '6',
        'municipality_id' => '3',
        'country_code' => 'CO'
    ],
    'items' => [
        [
            'code_reference' => '1',
            'name' => 'it',
            'quantity' => '1',
            'discount_rate' => '0',
            'price' => '100',
            'tax_rate' => '0',
            'unit_measure_id' => 70,
            'standard_code_id' => 1,
            'is_excluded' => 1,
            'tribute_id' => 7,
            'withholding_taxes' => []
        ]
    ]
];

$factusRes = ModeloFactus::mdlCrearDocumentoSoporte($tk, $payload);
echo $factusRes['respuesta'];
?>