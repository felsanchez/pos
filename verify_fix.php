<?php
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

$next = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus();
echo "\nREAL NEXT SUGGESTED (From Model): " . $next . "\n";
?>