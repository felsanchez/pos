<?php
require_once "modelos/conexion.php";
$hash = '$2y$12$AJrw6Xd4AElLEadOBP1a4.uHFNUhLD/eszLWQwELXda7n4uKdH.gG';
$db = Conexion::conectar();
$stmt = $db->prepare("DELETE FROM usuarios WHERE usuario = 'admin_test'");
$stmt->execute();
$stmt = $db->prepare("INSERT INTO usuarios (nombre, usuario, password, perfil, id_bodega, estado) VALUES ('Admin Test', 'admin_test', :pass, 'Administrador', 1, 1)");
$stmt->bindParam(":pass", $hash);
$stmt->execute();
echo "Usuario insertado correctamente\n";
?>
