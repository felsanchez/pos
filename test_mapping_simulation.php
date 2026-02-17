<?php
// Simulate logic from factus.controlador.php
$retencionesVenta = json_decode('[{"id":"114","descripcion":"ReteIVA 15.00%","base":15.97,"monto":2.3955,"tipo":"ReteIVA","porcentaje":"15"}]', true);

echo "Simulating Retention Mapping...\n";

foreach ($retencionesVenta as $ret) {
    $codigoRetencion = "05"; // Default ReteRenta
    $nombreRetencion = isset($ret['descripcion']) ? $ret['descripcion'] : (isset($ret['tipo']) ? $ret['tipo'] : '');

    echo "Procesando: " . $nombreRetencion . "\n";

    // Mapeo básico basado en nombre
    if (stripos($nombreRetencion, 'IVA') !== false) {
        $codigoRetencion = "06"; // ReteIVA
        echo "  -> Detectado IVA (Code 06)\n";
    } elseif (stripos($nombreRetencion, 'ICA') !== false) {
        $codigoRetencion = "07"; // ReteICA
        echo "  -> Detectado ICA (Code 07)\n";
    } elseif (stripos($nombreRetencion, 'Renta') !== false) {
        $codigoRetencion = "05"; // ReteRenta
        echo "  -> Detectado Renta (Code 05)\n";
    } else {
        echo "  -> No match, default 05\n";
    }

    echo "Result Code: " . $codigoRetencion . "\n";
}
?>