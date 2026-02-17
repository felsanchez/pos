<?php
// Script para ejecutar UPDATE directamente y verificar
require_once "modelos/conexion.php";

$db = Conexion::conectar();

echo "<h2>Test de UPDATE Directo</h2>";

// Intentar actualizar la venta 608 directamente
$idVenta = 608;

echo "<h3>Antes del UPDATE:</h3>";
$stmt = $db->prepare("SELECT id, codigo, estado_dian, mensaje_dian, fecha_envio_dian FROM ventas WHERE id = ?");
$stmt->execute([$idVenta]);
$antes = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($antes, true) . "</pre>";

// Ejecutar UPDATE
echo "<h3>Ejecutando UPDATE...</h3>";
$sql = "UPDATE ventas SET
        estado_dian = :estado_dian,
        cufe = :cufe,
        qr_data = :qr_data,
        xml_dian = :xml_dian,
        pdf_dian = :pdf_dian,
        mensaje_dian = :mensaje_dian,
        fecha_envio_dian = :fecha_envio_dian,
        numero_factura = :numero_factura
        WHERE id = :id";

$stmt = $db->prepare($sql);

$estado_dian = "creada";
$cufe = "";
$qr_data = "";
$xml_dian = "";
$pdf_dian = "";
$mensaje_dian = "Factura guardada localmente (Borrador). Pendiente de firma.";
$fecha_envio_dian = null;
$numero_factura = "";

$stmt->bindParam(":estado_dian", $estado_dian, PDO::PARAM_STR);
$stmt->bindParam(":cufe", $cufe, PDO::PARAM_STR);
$stmt->bindParam(":qr_data", $qr_data, PDO::PARAM_STR);
$stmt->bindParam(":xml_dian", $xml_dian, PDO::PARAM_STR);
$stmt->bindParam(":pdf_dian", $pdf_dian, PDO::PARAM_STR);
$stmt->bindParam(":mensaje_dian", $mensaje_dian, PDO::PARAM_STR);

if ($fecha_envio_dian === null) {
    $stmt->bindValue(":fecha_envio_dian", null, PDO::PARAM_NULL);
} else {
    $stmt->bindParam(":fecha_envio_dian", $fecha_envio_dian, PDO::PARAM_STR);
}

$stmt->bindParam(":numero_factura", $numero_factura, PDO::PARAM_STR);
$stmt->bindParam(":id", $idVenta, PDO::PARAM_INT);

$resultado = $stmt->execute();

echo "Resultado execute(): " . ($resultado ? "TRUE" : "FALSE") . "<br>";
echo "Filas afectadas: " . $stmt->rowCount() . "<br>";

if (!$resultado) {
    echo "<pre>Error: " . print_r($stmt->errorInfo(), true) . "</pre>";
}

echo "<h3>Después del UPDATE:</h3>";
$stmt = $db->prepare("SELECT id, codigo, estado_dian, mensaje_dian, fecha_envio_dian, numero_factura FROM ventas WHERE id = ?");
$stmt->execute([$idVenta]);
$despues = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($despues, true) . "</pre>";

// Verificar estructura de la tabla
echo "<h3>Estructura de la tabla ventas (columnas relacionadas):</h3>";
$stmt = $db->query("DESCRIBE ventas");
$columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columnas as $col) {
    if (in_array($col['Field'], ['estado_dian', 'cufe', 'qr_data', 'xml_dian', 'pdf_dian', 'mensaje_dian', 'fecha_envio_dian', 'numero_factura'])) {
        echo "<pre>" . print_r($col, true) . "</pre>";
    }
}
?>