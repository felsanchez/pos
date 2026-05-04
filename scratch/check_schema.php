<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("DESCRIBE ventas");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    if ($col['Field'] == 'orden_compra') {
        print_r($col);
    }
}
?>
