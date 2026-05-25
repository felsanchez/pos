<?php
require_once __DIR__ . "/../modelos/conexion.php";

$stmt = Conexion::conectar()->prepare("SELECT id, codigo, productos FROM ventas ORDER BY id DESC");
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Checking " . count($ventas) . " sales...\n";
$missingCount = 0;
foreach ($ventas as $v) {
    $prods = json_decode($v["productos"], true);
    if (!is_array($prods)) {
        echo "Sale ID {$v['id']} has invalid JSON: {$v['productos']}\n";
        continue;
    }
    foreach ($prods as $p) {
        if (!isset($p["precio"]) || $p["precio"] === null || $p["precio"] === "" || !isset($p["total"]) || $p["total"] === null || $p["total"] === "") {
            echo "Sale ID {$v['id']} (Code {$v['codigo']}): product ID {$p['id']} '{$p['descripcion']}' has missing/null price or total.\n";
            echo "Keys: " . implode(", ", array_keys($p)) . "\n";
            print_r($p);
            echo "------------------\n";
            $missingCount++;
            if ($missingCount >= 10) {
                break 2;
            }
        }
    }
}
echo "Done checking. Found $missingCount cases.\n";
