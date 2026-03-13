<?php
require_once 'modelos/conexion.php';
require_once 'modelos/factus.modelo.php';

$proximo = ModeloFactus::mdlObtenerSiguienteConsecutivoFactus();
echo "PROXIMO NUMERO SUGERIDO: " . $proximo . "\n";

// Ver logs generados
if (file_exists('tmp/log_numbering.txt')) {
    $lines = file('tmp/log_numbering.txt');
    echo "\nULTIMA LINEA DE LOG:\n";
    echo end($lines);
}
?>