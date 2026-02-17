<?php
/**
 * Script para depurar el payload exacto que genera el sistema
 */

require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/configuracion.modelo.php";
require_once "modelos/conexion.php";

echo "<h1>🕵️ Debug Payload Factus</h1>";
echo "<hr>";

$idVenta = 303; // La venta que falló con 500

echo "<h2>1. Obteniendo datos de venta #$idVenta</h2>";
$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

if (!$venta) {
    echo "❌ Venta no encontrada";
    exit;
}

echo "<h2>2. Generando Payload</h2>";
// Ahora que el método es public, podemos llamarlo
$payload = ControladorFactus::prepararDatosFactura($venta);

echo "<h3>JSON Generado (Sistema):</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd;'>";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo "</pre>";

echo "<h3>Comparación con Ejemplo Postman (Usuario):</h3>";
$ejemploPostman = '{
    "numbering_range_id": 8,
    "reference_code": "pruebaI3",
    "observation": "",
    "payment_form": "1",
    "payment_due_date": "2024-12-30",
    "payment_method_code": "10",
    "operation_type": 10,
    "send_email": false,
    "establishment": {
        "name": "Factus pro",
        "address": "cra 01 # 223 - 22",
        "phone_number": "123456789",
        "email": "fatuspro@factus.co",
        "municipality_id": "980"
    },
    "customer": {
        "identification": "123456789",
        "dv": "3",
        "company": "",
        "trade_name": "",
        "names": "Alan Turing",
        "address": "calle 1 # 2-68",
        "email": "alanturing@enigmasas.com",
        "phone": "1234567890",
        "legal_organization_id": "2",
        "tribute_id": "21",
        "identification_document_id": "3",
        "municipality_id": "980"
    },
    "items": [
        {
            "scheme_id": "1",
            "note": "",
            "code_reference": "12345",
            "name": "producto de prueba",
            "quantity": 1,
            "discount_rate": 20,
            "price": 50000,
            "tax_rate": "19.00",
            "unit_measure_id": 70,
            "standard_code_id": 1,
            "is_excluded": 0,
            "tribute_id": 1,
            "withholding_taxes": []
        }
    ]
}';

echo "<pre style='background: #e9ecef; padding: 15px; border: 1px solid #ddd;'>";
echo $ejemploPostman;
echo "</pre>";

echo "<h2>3. Diferencias Clave a Revisar:</h2>";
echo "<ul>";
echo "<li><strong>numbering_range_id:</strong> " . ($payload['numbering_range_id'] ?? 'N/A') . " (Sistema) vs 8 (Ejemplo)</li>";
echo "<li><strong>municipality_id (Establishment):</strong> " . ($payload['establishment']['municipality_id'] ?? 'N/A') . "</li>";
echo "<li><strong>municipality_id (Customer):</strong> " . ($payload['customer']['municipality_id'] ?? 'N/A') . "</li>";
echo "<li><strong>legal_organization_id:</strong> " . ($payload['customer']['legal_organization_id'] ?? 'N/A') . "</li>";
echo "<li><strong>tribute_id (Customer):</strong> " . ($payload['customer']['tribute_id'] ?? 'N/A') . "</li>";
echo "</ul>";

?>