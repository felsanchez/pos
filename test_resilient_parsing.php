<?php

function testResilientParsing($respuestaJson)
{
    $respuesta = json_decode($respuestaJson, true);
    $data = $respuesta['data'] ?? [];

    // Nueva lógica implementada
    $adjData = $data['adjustment_note'] ?? $data;

    $extracted = [
        "numero_nota_ajuste" => $adjData['number'] ?? $adjData['number_adjustment_note'] ?? '',
        "cuds_ajuste" => $adjData['cuds'] ?? $adjData['uuid'] ?? '',
        "qr_data" => $adjData['qr'] ?? $adjData['qr_code'] ?? '',
        "xml_dian" => $adjData['xml'] ?? $adjData['xml_url'] ?? '',
        "pdf_dian" => $adjData['pdf'] ?? $adjData['pdf_url'] ?? $adjData['public_url'] ?? '',
    ];

    echo "RESULTADOS EXTRAIDOS:\n";
    print_r($extracted);
    echo "\n";
}

// Escenario 1: Estructura plana (lo que vi en el test previo)
$json1 = '{
    "data": {
        "number": "NA-1",
        "cuds": "cuds123",
        "qr": "qr123",
        "xml": "http://xml123",
        "pdf": "http://pdf123"
    },
    "message": "OK"
}';

// Escenario 2: Estructura envuelta (como sospecho que a veces llega)
$json2 = '{
    "data": {
        "adjustment_note": {
            "number": "NA-2",
            "cuds": "cuds456",
            "qr": "qr456",
            "xml": "http://xml456",
            "pdf": "http://pdf456"
        }
    },
    "message": "OK"
}';

// Escenario 3: Estructura con nombres alternativos (basado en CreditNote)
$json3 = '{
    "data": {
        "adjustment_note": {
            "number_adjustment_note": "NA-3",
            "uuid": "cuds789",
            "qr_code": "qr789",
            "xml_url": "http://xml789",
            "public_url": "http://pdf789"
        }
    },
    "message": "OK"
}';

echo "--- PRUEBA ESCENARIO 1 (Plano) ---\n";
testResilientParsing($json1);

echo "--- PRUEBA ESCENARIO 2 (Envuelto) ---\n";
testResilientParsing($json2);

echo "--- PRUEBA ESCENARIO 3 (Alternativo) ---\n";
testResilientParsing($json3);
