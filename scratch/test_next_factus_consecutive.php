<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";
require_once "controladores/factus.controlador.php";

$auth = ControladorFactus::ctrAutenticar();
if ($auth['error']) {
    die("Auth error: " . $auth['mensaje']);
}

echo "Running mdlObtenerSiguienteConsecutivoFactus(false) (queries API):\n";
$next = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus(false);
echo "Siguiente (con API): $next\n\n";

echo "Running mdlObtenerSiguienteConsecutivoFactus(true) (omits API):\n";
$nextOmit = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus(true);
echo "Siguiente (sin API): $nextOmit\n\n";
