<?php
require_once "modelos/conexion.php";

$idUsuario = 43; // admin user ID

try {
    $sql1 = "SELECT n.*, 
            CASE WHEN nl.id_usuario IS NOT NULL THEN 1 ELSE 0 END as leida
            FROM notificaciones n
            CROSS JOIN configuracion c
            LEFT JOIN notificaciones_leidas nl ON n.id = nl.id_notificacion AND nl.id_usuario = :id_usuario
            LEFT JOIN actividades act ON n.referencia_tipo = 'actividad' AND n.referencia_id = act.id
            LEFT JOIN usuarios u_creator ON act.id_user = u_creator.id
            LEFT JOIN usuarios u_viewer ON u_viewer.id = :id_usuario
            WHERE n.eliminada = 0
              AND (n.tipo NOT IN ('orden_agente_ia', 'orden_creada') OR c.notif_orden_agente_ia = 1)
              AND (n.referencia_tipo != 'pago_bold' OR c.notif_transaccion_bold = 1)
              AND (n.referencia_tipo != 'solicitud_edicion' OR c.notif_solicitud_edicion = 1)
              AND (n.referencia_tipo != 'solicitud_eliminacion' OR c.notif_solicitud_eliminacion = 1)
              AND (n.referencia_tipo != 'actividad' OR u_viewer.id_bodega = u_creator.id_bodega)
            ORDER BY n.fecha DESC";

    $stmt1 = Conexion::conectar()->prepare($sql1);
    $stmt1->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
    $stmt1->execute();
    $res1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    echo "Query 1 ran successfully! Rows returned: " . count($res1) . "\n";
    print_r($res1);

} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
}
