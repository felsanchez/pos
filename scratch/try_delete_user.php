<?php
require_once __DIR__ . "/../modelos/conexion.php";

$db = Conexion::conectar();

// 1. List all users
$stmt = $db->prepare("SELECT id, nombre, usuario, perfil FROM usuarios");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Available Users:\n";
foreach ($users as $u) {
    echo "ID: {$u['id']} | Nombre: {$u['nombre']} | Usuario: {$u['usuario']} | Perfil: {$u['perfil']}\n";
}

echo "\nSimulating deletion for each user to catch database errors (all will be rolled back):\n";
foreach ($users as $u) {
    $id = $u['id'];
    $db->beginTransaction();
    try {
        $stmtDel = $db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmtDel->bindParam(":id", $id, PDO::PARAM_INT);
        $stmtDel->execute();
        echo "User ID {$id} ({$u['usuario']}) delete simulation: SUCCESS (would be deleted)\n";
    } catch (PDOException $e) {
        echo "User ID {$id} ({$u['usuario']}) delete simulation: FAILED - " . $e->getMessage() . "\n";
    }
    $db->rollBack();
}
