<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT id, concepto, monto, metodo_pago, estado FROM gastos ORDER BY id DESC LIMIT 5");
$stmt->execute();
$gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($gastos, JSON_PRETTY_PRINT);
?>
