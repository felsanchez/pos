<?php
$file = __DIR__ . '/controladores/productos.controlador.php';
$content = file_get_contents($file);

$search = 'if (isset($_POST["combinacionEditar_" . $i . "_ids"]) && isset($_POST["combinacionEditar_" . $i . "_nombre"])) {

								$idsCombinacion = $_POST["combinacionEditar_" . $i . "_ids"];
								$nombreCombinacion = $_POST["combinacionEditar_" . $i . "_nombre"];';
                                
$replace = '// Fallback for incorrect prefixes sent by JS
								$prefixComb = isset($_POST["combinacionEditar_" . $i . "_ids"]) ? "combinacionEditar_" : "combinacion_";
								$prefixPrecio = isset($_POST["precioAdicionalEditar_" . $_POST[$prefixComb . $i . "_ids"]]) ? "precioAdicionalEditar_" : "precioAdicional_";
								$prefixStock = isset($_POST["stockVarianteEditar_" . $_POST[$prefixComb . $i . "_ids"]]) ? "stockVarianteEditar_" : "stockVariante_";

							if (isset($_POST[$prefixComb . $i . "_ids"]) && isset($_POST[$prefixComb . $i . "_nombre"])) {

								$idsCombinacion = $_POST[$prefixComb . $i . "_ids"];
								$nombreCombinacion = $_POST[$prefixComb . $i . "_nombre"];';

$newContent = str_replace($search, $replace, $content);

// We also need to fix the price and stock variables inside the loop
$search2 = '$precioAdicional = isset($_POST["precioAdicionalEditar_" . $idsCombinacion]) && $_POST["precioAdicionalEditar_" . $idsCombinacion] !== ""
									? $_POST["precioAdicionalEditar_" . $idsCombinacion]
									: 0;

								$stockVariante = isset($_POST["stockVarianteEditar_" . $idsCombinacion]) && $_POST["stockVarianteEditar_" . $idsCombinacion] !== ""
									? $_POST["stockVarianteEditar_" . $idsCombinacion]
									: 0;';
                                    
$replace2 = '$precioAdicional = isset($_POST[$prefixPrecio . $idsCombinacion]) && $_POST[$prefixPrecio . $idsCombinacion] !== ""
									? $_POST[$prefixPrecio . $idsCombinacion]
									: 0;

								$stockVariante = isset($_POST[$prefixStock . $idsCombinacion]) && $_POST[$prefixStock . $idsCombinacion] !== ""
									? $_POST[$prefixStock . $idsCombinacion]
									: 0;';

$newContent = str_replace($search2, $replace2, $newContent);

file_put_contents($file, $newContent);
echo "✅ Prefijos dinámicos aplicados al bucle en el controlador.\n";
