<?php

require_once "controladores/productos.controlador.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/factus.modelo.php";

class TestFlexibleMapping {

    public function testMapping() {
        echo "Testing Flexible Mapping Logic...\n";
        
        $c = new ControladorProductos();
        $reflect = new ReflectionClass($c);
        
        $normalizar = $reflect->getMethod('normalizarTexto');
        $normalizar->setAccessible(true);
        
        $buscarUnidad = $reflect->getMethod('buscarUnidadPorNombre');
        $buscarUnidad->setAccessible(true);
        
        $buscarTributo = $reflect->getMethod('buscarTributoPorNombre');
        $buscarTributo->setAccessible(true);
        
        $testsUnidades = [
            "und" => 1,
            "UND" => 1,
            "kgm" => 2,
            "KGM" => 2,
            "gll" => 6
        ];
        
        foreach ($testsUnidades as $input => $expectedId) {
            $norm = $normalizar->invoke(null, $input);
            $res = $buscarUnidad->invoke(null, $norm);
            $id = $res ? $res['id'] : 'NOT FOUND';
            echo "Unidad '$input' -> ID: $id (Expected: $expectedId) " . ($id == $expectedId ? "[OK]" : "[FAIL]") . "\n";
        }
        
        $testsTributos = [
            "iva 19" => 3,
            "IVA 19" => 3,
            "inc 8" => 4,
            "INC 8" => 4,
            "excluido" => 6,
            "iva 0" => 1
        ];
        
        echo "\nTesting Tributos...\n";
        foreach ($testsTributos as $input => $expectedId) {
            $norm = $normalizar->invoke(null, $input);
            $res = $buscarTributo->invoke(null, $norm);
            $id = $res ? $res['id'] : 'NOT FOUND';
            echo "Tributo '$input' -> ID: $id (Expected: $expectedId) " . ($id == $expectedId ? "[OK]" : "[FAIL]") . "\n";
        }
    }
}

ob_start();

$test = new TestFlexibleMapping();
$test->testMapping();

$output = ob_get_clean();
file_put_contents("test_results.txt", $output);
echo "Results saved to test_results.txt\n";

