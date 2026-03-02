<?php
require 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
require 'c:/xampp/htdocs/pos/modelos/factus.modelo.php';
require 'c:/xampp/htdocs/pos/modelos/proveedores.modelo.php';

$res = ControladorFactus::ctrAutenticar();
$tk = $res['token'];

// We are sending municipality_id '3' which corresponds to 'La Pedrera' (code 91407)
// We use a fresh random NIT so Factus hasn't associated it with Achi yet.
$payload = [
    'numbering_range_id' => 1192,
    'reference_code' => 'TEST-DOC-' . time(),
    'observation' => '',
    'payment_form' => '1',
    'payment_due_date' => '2026-02-27',
    'payment_method_code' => '10',
    'provider' => [
        'identification' => '183991208',
        'dv' => '0',
        'company' => '',
        'trade_name' => 'Brand New Provider',
        'names' => 'Brand New Provider',
        'address' => 'calle 1',
        'email' => 'test@test.com',
        'phone' => '000',
        'legal_organization_id' => '2',
        'tribute_id' => '21',
        'identification_document_id' => '3',
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