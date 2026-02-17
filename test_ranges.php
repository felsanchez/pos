<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Error auth: " . $auth['mensaje']);
}

echo "Token: " . substr($auth['token'], 0, 10) . "...\n";

$rangos = ModeloFactus::mdlConsultarRangosAPI($auth['token']);

echo "Respuesta MDL:\n";
var_dump($rangos);

echo "\nINTENTO GUARDAR:\n";
if ($rangos) {
    try {
        $res = ModeloFactus::mdlGuardarRangos($rangos);
        print_r($res);
    } catch (Exception $e) {
        echo "Excepcion SQL: " . $e->getMessage();
    }
}
?>