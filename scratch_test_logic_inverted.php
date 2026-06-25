<?php
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "=== USUARIOS ===\n";
$stmt = $db->prepare("SELECT id, usuario, nombre, id_bodega FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($usuarios);

echo "\n=== ACTIVIDADES ===\n";
$stmt = $db->prepare("SELECT a.id, a.descripcion, a.id_user, a.id_bodega, u.nombre as creator_name, u.id_bodega as creator_user_bodega FROM actividades a LEFT JOIN usuarios u ON a.id_user = u.id");
$stmt->execute();
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($actividades);

echo "\n=== NOTIFICACIONES ===\n";
$stmt = $db->prepare("SELECT id, tipo, referencia_tipo, referencia_id FROM notificaciones WHERE referencia_tipo = 'actividad'");
$stmt->execute();
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($notificaciones);

echo "\n=== COMPARISON SIMULATION ===\n";
foreach ($usuarios as $viewer) {
    echo "Viewer: {$viewer['usuario']} (ID: {$viewer['id']}, Bodega: {$viewer['id_bodega']})\n";
    foreach ($notificaciones as $n) {
        // Find activity
        $act = null;
        foreach ($actividades as $a) {
            if ($a['id'] == $n['referencia_id']) {
                $act = $a;
                break;
            }
        }
        if (!$act) {
            echo "  Notif ID {$n['id']}: Activity not found!\n";
            continue;
        }
        
        $creator_user_bodega = $act['creator_user_bodega'];
        $activity_bodega = $act['id_bodega'];
        
        $match_creator = ($viewer['id_bodega'] == $creator_user_bodega) ? "TRUE" : "FALSE";
        $match_activity = ($viewer['id_bodega'] == $activity_bodega) ? "TRUE" : "FALSE";
        
        echo "  Notif ID {$n['id']} for Activity '{$act['descripcion']}':\n";
        echo "    Creator User: {$act['creator_name']} (ID: {$act['id_user']}, Bodega: {$creator_user_bodega})\n";
        echo "    Activity id_bodega (in activities table): {$activity_bodega}\n";
        echo "    Condition [viewer.id_bodega = creator_user.id_bodega]: $match_creator\n";
        echo "    Condition [viewer.id_bodega = activity.id_bodega]: $match_activity\n";
    }
    echo "\n";
}
