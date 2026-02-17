<?php
require_once "modelos/conexion.php";

echo "<h1>Actualizando Medios de Pago...</h1>";

try {
    $db = Conexion::conectar();

    $nuevosMedios = "Efectivo,Cheque,Consignación,Transferencia,Tarjeta débito,Tarjeta crédito,Bonos,Vales,Otro,Medio de pago no definido";

    $stmt = $db->prepare("UPDATE configuracion SET medios_pago = :medios WHERE id = 1");
    $stmt->bindParam(":medios", $nuevosMedios, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "<p style='color:green'>Medios de pago actualizados correctamente.</p>";
        echo "<p>Nuevos valores: " . $nuevosMedios . "</p>";
    } else {
        echo "<p style='color:red'>Error al actualizar.</p>";
        print_r($stmt->errorInfo());
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Excepción: " . $e->getMessage() . "</p>";
}
