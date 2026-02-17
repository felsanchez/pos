<?php
require_once 'modelos/conexion.php';

echo "=== CÁLCULO CORRECTO DE RETENCIONES FACTURA 55 ===\n\n";

$db = Conexion::conectar();
$stmt = $db->query("SELECT productos, total, neto, impuesto, tipo_descuento, valor_descuento, monto_descuento FROM ventas WHERE codigo = 55");
$factura = $stmt->fetch(PDO::FETCH_ASSOC);

$productos = json_decode($factura['productos'], true);

echo "📊 ANÁLISIS DE PRODUCTOS:\n\n";

$subtotalSinDescuento = 0;
$totalIVA = 0;
$totalINC = 0;
$totalExcluido = 0;

foreach ($productos as $i => $prod) {
    $cantidad = floatval($prod['cantidad']);
    $precio = floatval($prod['precio']);
    $totalProducto = $cantidad * $precio;
    $impuesto = floatval($prod['impuesto']);

    echo ($i + 1) . ". {$prod['descripcion']}\n";
    echo "   Cantidad: $cantidad x Precio: $$precio = $$totalProducto\n";
    echo "   Impuesto: {$impuesto}%\n";

    $subtotalSinDescuento += $totalProducto;

    // Clasificar por tipo de impuesto
    if ($impuesto == 0) {
        $totalExcluido += $totalProducto;
        echo "   → Excluido/IVA 0%\n";
    } elseif ($impuesto == 8 || $impuesto == 10) {
        $montoINC = ($totalProducto * $impuesto) / 100;
        $totalINC += $montoINC;
        echo "   → INC {$impuesto}%: $$montoINC\n";
    } elseif ($impuesto == 19) {
        $montoIVA = ($totalProducto * $impuesto) / 100;
        $totalIVA += $montoIVA;
        echo "   → IVA 19%: $$montoIVA\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════\n";
echo "Subtotal (sin descuento): $" . number_format($subtotalSinDescuento, 2) . "\n";
echo "Total IVA 19%: $" . number_format($totalIVA, 2) . "\n";
echo "Total INC (8% + 10%): $" . number_format($totalINC, 2) . "\n";
echo "Total Excluido/IVA 0%: $" . number_format($totalExcluido, 2) . "\n";
echo "═══════════════════════════════════════\n\n";

// Aplicar descuento
$tipoDescuento = $factura['tipo_descuento'];
$valorDescuento = floatval($factura['valor_descuento']);
$montoDescuento = floatval($factura['monto_descuento']);

echo "🎁 DESCUENTO:\n";
echo "Tipo: $tipoDescuento\n";
echo "Valor: $valorDescuento%\n";
echo "Monto: $" . number_format($montoDescuento, 2) . "\n\n";

// Calcular subtotal con descuento
$subtotalConDescuento = $subtotalSinDescuento - $montoDescuento;
echo "Subtotal CON descuento: $" . number_format($subtotalConDescuento, 2) . "\n\n";

// Calcular impuestos sobre subtotal con descuento
$factorDescuento = $subtotalConDescuento / $subtotalSinDescuento;
$ivaConDescuento = $totalIVA * $factorDescuento;
$incConDescuento = $totalINC * $factorDescuento;

echo "IVA después de descuento: $" . number_format($ivaConDescuento, 2) . "\n";
echo "INC después de descuento: $" . number_format($incConDescuento, 2) . "\n\n";

echo "═══════════════════════════════════════\n";
echo "TOTAL FACTURA: $" . number_format($subtotalConDescuento + $ivaConDescuento + $incConDescuento, 2) . "\n";
echo "═══════════════════════════════════════\n\n";

echo "🔒 CÁLCULO CORRECTO DE RETENCIONES:\n\n";

// ReteIVA 100% (se aplica sobre el IVA)
$baseReteIVA = $ivaConDescuento;
$montoReteIVA = ($baseReteIVA * 100) / 100;

echo "1. ReteIVA 100%:\n";
echo "   Base (IVA total): $" . number_format($baseReteIVA, 2) . "\n";
echo "   Monto (100%): $" . number_format($montoReteIVA, 2) . "\n\n";

// ReteRenta 3% (se aplica sobre el subtotal SIN impuestos)
$baseReteRenta = $subtotalConDescuento;
$montoReteRenta = ($baseReteRenta * 3) / 100;

echo "2. ReteRenta 3%:\n";
echo "   Base (Subtotal): $" . number_format($baseReteRenta, 2) . "\n";
echo "   Monto (3%): $" . number_format($montoReteRenta, 2) . "\n\n";

$totalRetenciones = $montoReteIVA + $montoReteRenta;

echo "═══════════════════════════════════════\n";
echo "Total Retenciones: $" . number_format($totalRetenciones, 2) . "\n";
echo "Total Neto a Pagar: $" . number_format($factura['total'] - $totalRetenciones, 2) . "\n";
echo "═══════════════════════════════════════\n\n";

echo "❌ VALORES INCORRECTOS GUARDADOS:\n";
echo "ReteIVA - Base guardada: $0.00 | Debería ser: $" . number_format($baseReteIVA, 2) . "\n";
echo "ReteRenta - Base guardada: $143.18 | Debería ser: $" . number_format($baseReteRenta, 2) . "\n\n";

echo "=== FIN ANÁLISIS ===\n";
?>