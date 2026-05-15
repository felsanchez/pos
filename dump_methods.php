<?php
require_once "modelos/productos.modelo.php";

$class = new ReflectionClass('ModeloProductos');
$methods = $class->getMethods();

foreach ($methods as $method) {
    if (stripos($method->getName(), 'variante') !== false) {
        echo $method->getName() . "\n";
    }
}
