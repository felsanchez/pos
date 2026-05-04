<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("UPDATE ventas SET orden_compra = '10738' WHERE id = 932");
$stmt->execute();
echo "Registro 932 actualizado con orden_compra 10738.\n";
?>
