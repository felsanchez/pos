<?php
$file = __DIR__ . '/vistas/js/producto-detalle.js';
$content = file_get_contents($file);

// Replace ALL occurrences of $('input[name="idProducto"]') with a more specific selector
// such as checking if any of them has a value.
$search = '$(\'input[name="idProducto"]\').length > 0 && $(\'input[name="idProducto"]\').val() !== ""';
$replace = '($(\'input[name="idProducto"]\').first().val() !== "" || $(\'input[name="idProducto"]\').last().val() !== "")';

$newContent = str_replace($search, $replace, $content);
file_put_contents($file, $newContent);
echo "✅ JS de producto-detalle actualizado.\n";

// Now, update ctrEditarProducto to fallback to totalCombinaciones if totalCombinacionesEditar is empty!
$fileCtrl = __DIR__ . '/controladores/productos.controlador.php';
$contentCtrl = file_get_contents($fileCtrl);

$searchCtrl = 'if (isset($_POST["totalCombinacionesEditar"]) && $_POST["totalCombinacionesEditar"] > 0) {';
$replaceCtrl = '$totalCombinacionesPost = isset($_POST["totalCombinacionesEditar"]) ? $_POST["totalCombinacionesEditar"] : (isset($_POST["totalCombinaciones"]) ? $_POST["totalCombinaciones"] : 0);
					if ($totalCombinacionesPost > 0) {
						$_POST["totalCombinacionesEditar"] = $totalCombinacionesPost; // Para mantener compatibilidad con el resto del codigo';

$newContentCtrl = str_replace($searchCtrl, $replaceCtrl, $contentCtrl);
file_put_contents($fileCtrl, $newContentCtrl);
echo "✅ Controlador actualizado para aceptar ambos nombres de variable.\n";
