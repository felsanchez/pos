<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "ajax/facturacion.ajax.php";

$_POST["idDS"] = 1;
$_POST["emailDestino"] = "kontrolpos01@gmail.com";

$enviar = new AjaxFacturacion();
$enviar->idDS = $_POST["idDS"];
$enviar->emailDestino = $_POST["emailDestino"];

echo "Iniciando prueba de envio de Documento Soporte...\n";
// Capture output to avoid noise
ob_start();
$enviar->ajaxEnviarPDFDSCorreo();
$response = ob_get_clean();
echo "Respuesta: " . $response . "\n";
echo "Prueba finalizada.\n";
?>