<?php
require_once 'modelos/conexion.php';

echo "=== ANÁLISIS FACTURA 55 - RETENCIONES ===\n\n";

$db = Conexion::conectar();

// Obtener datos de la factura 55
$stmt = $db->query("
    SELECT 
        id, codigo, id_cliente, productos, total, neto, impuesto,
        retenciones, tipo_descuento, valor_descuento, monto_descuento,
        estado_dian, numero_factura, fecha
    FROM ventas 
    WHERE codigo = 55
");

$factura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$factura) {
    echo "❌ No se encontró la factura 55\n";
    exit;
}

echo "📄 DATOS GENERALES:\n";
echo "  ID: {$factura['id']}\n";
echo "  Código: {$factura['codigo']}\n";
echo "  Número Factura: {$factura['numero_factura']}\n";
echo "  Estado DIAN: {$factura['estado_dian']}\n";
echo "  Fecha: {$factura['fecha']}\n\n";

echo "💰 VALORES:\n";
echo "  Total: $" . number_format($factura['total'], 2) . "\n";
echo "  Neto: $" . number_format($factura['neto'], 2) . "\n";
echo "  Impuesto: $" . number_format($factura['impuesto'], 2) . "\n\n";

echo "🎁 DESCUENTOS:\n";
echo "  Tipo: " . ($factura['tipo_descuento'] ?: 'Ninguno') . "\n";
echo "  Valor: " . ($factura['valor_descuento'] ?: '0') . "\n";
echo "  Monto: $" . number_format($factura['monto_descuento'], 2) . "\n\n";

echo "📦 PRODUCTOS:\n";
$productos = json_decode($factura['productos'], true);
if ($productos) {
    foreach ($productos as $i => $prod) {
        echo "  " . ($i + 1) . ". {$prod['descripcion']}\n";
        echo "     Cantidad: {$prod['cantidad']}\n";
        echo "     Precio: $" . number_format($prod['precio'], 2) . "\n";
        echo "     Total: $" . number_format($prod['total'], 2) . "\n";
        echo "     Impuesto: {$prod['impuesto']}%\n\n";
    }
} else {
    echo "  ⚠️ No se pudieron decodificar los productos\n\n";
}

echo "🔒 RETENCIONES GUARDADAS:\n";
if (!empty($factura['retenciones'])) {
    $retenciones = json_decode($factura['retenciones'], true);
    if ($retenciones && is_array($retenciones)) {
        echo "  JSON válido con " . count($retenciones) . " retención(es):\n\n";
        foreach ($retenciones as $i => $ret) {
            echo "  Retención " . ($i + 1) . ":\n";
            echo "    Tipo: " . ($ret['tipo'] ?? 'N/A') . "\n";
            echo "    Porcentaje: " . ($ret['porcentaje'] ?? 'N/A') . "%\n";
            echo "    Base: $" . number_format($ret['base'] ?? 0, 2) . "\n";
            echo "    Monto: $" . number_format($ret['monto'] ?? 0, 2) . "\n\n";
        }

        // Calcular total de retenciones
        $totalRetenciones = 0;
        foreach ($retenciones as $ret) {
            $totalRetenciones += ($ret['monto'] ?? 0);
        }
        echo "  💵 Total Retenciones: $" . number_format($totalRetenciones, 2) . "\n";
        echo "  💵 Total Neto (Total - Retenciones): $" . number_format($factura['total'] - $totalRetenciones, 2) . "\n\n";

    } else {
        echo "  ⚠️ JSON inválido o vacío\n";
        echo "  Contenido: " . $factura['retenciones'] . "\n\n";
    }
} else {
    echo "  ❌ No hay retenciones guardadas (campo vacío o NULL)\n\n";
}

echo "🔍 VERIFICACIÓN DE CÁLCULOS:\n";
if (!empty($factura['retenciones'])) {
    $retenciones = json_decode($factura['retenciones'], true);
    if ($retenciones && is_array($retenciones)) {
        foreach ($retenciones as $i => $ret) {
            $tipo = $ret['tipo'] ?? 'N/A';
            $porcentaje = floatval($ret['porcentaje'] ?? 0);
            $base = floatval($ret['base'] ?? 0);
            $montoGuardado = floatval($ret['monto'] ?? 0);

            // Calcular monto esperado
            $montoEsperado = ($base * $porcentaje) / 100;

            echo "  Retención " . ($i + 1) . " ($tipo {$porcentaje}%):\n";
            echo "    Base declarada: $" . number_format($base, 2) . "\n";
            echo "    Monto guardado: $" . number_format($montoGuardado, 2) . "\n";
            echo "    Monto esperado: $" . number_format($montoEsperado, 2) . "\n";

            if (abs($montoGuardado - $montoEsperado) > 0.01) {
                echo "    ⚠️ DISCREPANCIA: Diferencia de $" . number_format(abs($montoGuardado - $montoEsperado), 2) . "\n";
            } else {
                echo "    ✅ Cálculo correcto\n";
            }
            echo "\n";
        }
    }
}

echo "=== FIN ANÁLISIS ===\n";
?>