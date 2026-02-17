<?php
require_once "modelos/session-manager.php";
SessionManager::startSecure();
require_once "modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT * FROM factus_tributos");
$stmt->execute();
$tributos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($tributos);
echo "</pre>";
