<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";
require_once "modelos/helpers.php";
require_once "modelos/sanitizer.php";

header('Content-Type: application/json');

$params = [
    "draw" => 1,
    "start" => 0,
    "length" => 10,
    "search" => ["value" => ""],
    "order" => [["column" => 0, "dir" => "desc"]]
];

try {
    echo "--- TEST MODELO ---\n";
    $data = ModeloFactus::mdlMostrarDocumentosSoporteServerSide(" WHERE 1=1 ", " ORDER BY ds.id DESC ", " LIMIT 0, 10 ");
    echo "Registros encontrados: " . count($data) . "\n";
    if (count($data) > 0) {
        print_r($data[0]);
    }

    echo "\n--- TEST CONTROLADOR ---\n";
    $respuesta = ControladorFactus::ctrMostrarDocumentosSoporteServerSide($params);
    echo json_encode($respuesta, JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "TRACE: " . $e->getTraceAsString();
}
