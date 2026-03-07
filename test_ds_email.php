<?php
require_once "ajax/facturacion.ajax.php";

$_POST["idDS"] = 1;
$_POST["emailDestino"] = "kontrolpos01@gmail.com";

$enviar = new AjaxFacturacion();
$enviar->idDS = $_POST["idDS"];
$enviar->emailDestino = $_POST["emailDestino"];

echo "Iniciando prueba de envio de Documento Soporte...\n";
$enviar->ajaxEnviarPDFDSCorreo();
echo "\nPrueba finalizada.\n";
?>