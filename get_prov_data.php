<?php
require_once "modelos/conexion.php";
try {
    $stmt1 = Conexion::conectar()->prepare("SELECT * FROM factus_municipios WHERE id=653 OR id_factus=653 LIMIT 5");
    $stmt1->execute();
    echo "MUNICIPIOS 653:\n";
    print_r($stmt1->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>