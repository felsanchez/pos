<?php
require_once "modelos/conexion.php";
try {
    $db = Conexion::conectar();
    $id = 3; // Option "m"

    $stmt = $db->prepare("DELETE FROM opciones_variantes WHERE id = :id");
    $stmt->bindParam(":id", $id);
    if($stmt->execute()) {
        echo "Deleted option $id successfully.";
    } else {
        echo "Failed to delete option $id.";
        print_r($stmt->errorInfo());
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
