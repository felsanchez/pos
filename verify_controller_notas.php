<?php
require_once 'controladores/factus.controlador.php';
require_once 'modelos/factus.modelo.php';
require_once 'modelos/conexion.php';

$notas = ControladorFactus::ctrMostrarNotasAjusteDS(null, null);
echo "TOTAL NOTAS: " . count($notas) . "\n";
foreach ($notas as $n) {
    echo "ID: {$n['id']} | NUMERO: '{$n['numero_nota_ajuste']}'\n";
}
