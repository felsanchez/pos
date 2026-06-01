<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT * FROM clientes");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($clientes as $cliente) {
    echo "=== Cliente ID: " . $cliente['id'] . " | Nombre: " . $cliente['nombre'] . " ===\n";
    echo "  tipo_persona: " . $cliente['tipo_persona'] . "\n";
    echo "  responsabilidades_fiscales: " . $cliente['responsabilidades_fiscales'] . "\n";
    echo "  tipo_documento_id: " . $cliente['tipo_documento_id'] . "\n";
    echo "  documento: " . $cliente['documento'] . "\n";
    
    // Simulate customer mapping in prepararDatosFactura
    $venta = [
        'id_cliente' => $cliente['id'],
        'productos' => '[]',
        'id' => 1,
        'total' => 0
    ];
    $datos = ControladorFactus::prepararDatosFactura($venta);
    
    echo "  MAPPED customer payload:\n";
    echo "    legal_organization_id: " . $datos['customer']['legal_organization_id'] . "\n";
    echo "    tribute_id: " . $datos['customer']['tribute_id'] . "\n";
    echo "    identification_document_id: " . $datos['customer']['identification_document_id'] . "\n";
    echo "    fiscal_responsibilities:\n";
    print_r($datos['customer']['fiscal_responsibilities']);
    echo "\n";
}
