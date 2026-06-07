<?php
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";

function normalizarTexto($texto) {
    $texto = strtolower($texto);
    $texto = trim($texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);
    return $texto;
}

function buscarTributoPorNombre($nombreNormalizado, $tributos) {
    // Si es "excluido", buscar directamente
    if ($nombreNormalizado == "excluido") {
        foreach ($tributos as $tributo) {
            if (normalizarTexto($tributo["nombre"]) == "excluido") {
                return $tributo;
            }
        }
    }

    // Extraer valor numérico/porcentaje si existe
    preg_match('/(\d+(?:[\.,]\d+)?)/', $nombreNormalizado, $matches);
    $porcentajeBuscado = isset($matches[1]) ? floatval(str_replace(',', '.', $matches[1])) : null;

    // Obtener la parte del nombre sin el porcentaje ni el símbolo %
    $nombreSinPorcentaje = trim(preg_replace('/[\d\.,%]/', '', $nombreNormalizado));
    $nombreSinPorcentajeNorm = normalizarTexto($nombreSinPorcentaje);

    foreach ($tributos as $tributo) {
        $tributoNombreNorm = normalizarTexto($tributo["nombre"]);
        $tributoPct = floatval($tributo["porcentaje_defecto"]);

        // Verificar si los nombres coinciden de alguna manera
        $matchNombre = (strpos($tributoNombreNorm, $nombreSinPorcentajeNorm) !== false || 
                        strpos($nombreSinPorcentajeNorm, $tributoNombreNorm) !== false);

        if ($porcentajeBuscado !== null) {
            if ($matchNombre && abs($tributoPct - $porcentajeBuscado) < 0.01) {
                return $tributo;
            }
        } else {
            if ($matchNombre) {
                return $tributo;
            }
        }
    }

    return false;
}

try {
    $tributos = ModeloFactus::mdlObtenerTributos();
    
    $casosDePrueba = ["iva 19", "inc 8", "iva 0", "ica 0", "iva 5", "excluido", "iva 19%", "inc 8 %"];
    
    foreach ($casosDePrueba as $caso) {
        $norm = normalizarTexto($caso);
        $res = buscarTributoPorNombre($norm, $tributos);
        if ($res) {
            echo "Caso: '$caso' -> ENCONTRADO: ID {$res['id']} | Nombre: {$res['nombre']} | Porcentaje: {$res['porcentaje_defecto']}\n";
        } else {
            echo "Caso: '$caso' -> NO ENCONTRADO\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
