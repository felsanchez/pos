<?php
// Script de prueba para verificar la lógica de tribute_id

// Simular producto sin tributo
$productoBD = array('tributo_id' => 0);

echo "=== TEST TRIBUTE_ID LOGIC ===\n\n";
echo "Producto tributo_id: " . $productoBD['tributo_id'] . "\n\n";

// Lógica actual
$tributoIdOriginal = isset($productoBD['tributo_id']) ? intval($productoBD['tributo_id']) : 0;
echo "tributoIdOriginal: " . $tributoIdOriginal . "\n";
echo "tributoIdOriginal === 0: " . ($tributoIdOriginal === 0 ? "TRUE" : "FALSE") . "\n\n";

if ($tributoIdOriginal === 0) {
    $tributo = null;
    $tasaImpuesto = 0.00;
    $codeTributo = 7;
    echo "✅ ENTRÓ AL IF (tributo_id === 0)\n";
    echo "codeTributo asignado: " . $codeTributo . "\n";
} else {
    echo "❌ NO ENTRÓ AL IF\n";
    $codeTributo = 1;
}

echo "\n=== RESULTADO FINAL ===\n";
echo "codeTributo: " . $codeTributo . "\n";
echo "Esperado: 7\n";
echo "Match: " . ($codeTributo === 7 ? "✅ SÍ" : "❌ NO") . "\n";
