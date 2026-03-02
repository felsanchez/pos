<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

$db = Conexion::conectar();

$result = [];
$stmt = $db->query("SELECT id, numero_nota_credito, estado_dian FROM notas_credito ORDER BY id DESC LIMIT 3");
$result['notas_credito'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT id, id_factus, documento, prefijo, numero_actual, estado FROM factus_rangos WHERE documento LIKE '%credit-note%' AND estado = 1");
$result['rango_credit_note'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT id, id_factus, documento, prefijo, numero_actual, estado FROM factus_rangos WHERE documento LIKE '%Nota Crédito%' OR documento LIKE '%credit%'");
$result['rango_all'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents("debug_rango.json", json_encode($result, JSON_PRETTY_PRINT));
echo "Done";
?>