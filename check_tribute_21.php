<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

$tributo = ModeloFactus::mdlMostrarTributo(21);
if ($tributo) {
    echo "ID: " . $tributo['id'] . " | Codigo: " . $tributo['codigo'] . " | Nombre: " . $tributo['nombre'] . "\n";
} else {
    echo "Tributo ID 21 no encontrado.\n";
    // Buscar ZZ
    echo "Buscando ZZ...\n";
    $db = Conexion::conectar();
    $stmt = $db->prepare("SELECT * FROM factus_tributos WHERE codigo = 'ZZ'");
    $stmt->execute();
    $zz = $stmt->fetch();
    if ($zz) {
        echo "Encontrado ZZ: ID: " . $zz['id'] . " | Nombre: " . $zz['nombre'] . "\n";
    }
}
?>