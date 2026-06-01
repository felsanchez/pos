<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    
    // Check all options
    $stmt = $db->prepare("SELECT id, nombre FROM opciones_variantes");
    $stmt->execute();
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>ID</th><th>Nombre</th><th>Uso Antiguo (mdlVerificarUsoOpcionVariante)</th><th>Uso Global (pb.estado=1)</th></tr>";
    
    foreach($opciones as $op) {
        $idOpcion = $op["id"];
        
        // Uso Antiguo (solo en tabla pvo)
        $stmt1 = $db->prepare("SELECT COUNT(*) FROM productos_variantes_opciones WHERE id_opcion_variante = :id");
        $stmt1->bindParam(":id", $idOpcion);
        $stmt1->execute();
        $usoAntiguo = $stmt1->fetchColumn();
        
        // Uso Global Nuevo
        $stmt2 = $db->prepare("
			SELECT COUNT(DISTINCT pvo.id_producto_variante) 
			FROM productos_variantes_opciones pvo 
			JOIN productos_variantes pv ON pvo.id_producto_variante = pv.id 
			JOIN productos p ON pv.id_producto = p.id 
			JOIN productos_bodegas pb ON p.id = pb.id_producto 
			WHERE pvo.id_opcion_variante = :id_opcion 
			AND p.eliminado = 0 AND pb.estado = 1 AND pv.estado = 1
        ");
        $stmt2->bindParam(":id_opcion", $idOpcion);
        $stmt2->execute();
        $usoGlobal = $stmt2->fetchColumn();
        
        echo "<tr><td>$idOpcion</td><td>{$op['nombre']}</td><td>$usoAntiguo</td><td>$usoGlobal</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo $e->getMessage();
}
