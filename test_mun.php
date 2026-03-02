<?php
require 'c:/xampp/htdocs/pos/modelos/conexion.php';

$prov_mun = 3;

$mun_id = '981';
if (!empty($prov_mun)) {
    $stmt = Conexion::conectar()->prepare("SELECT id_factus FROM factus_municipios WHERE id = :id OR id_factus = :id_factus LIMIT 1");
    $stmt->execute([':id' => $prov_mun, ':id_factus' => $prov_mun]);
    $mun = $stmt->fetch();
    if ($mun)
        $mun_id = strval($mun['id_factus']);
}

echo "Result for $prov_mun is: " . $mun_id . "\n";
?>