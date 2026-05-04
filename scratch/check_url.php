<?php
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM configuracion WHERE clave = 'api_url_local'");
$stmt->execute();
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
?>
