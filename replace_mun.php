<?php
$file = 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
$content = file_get_contents($file);

$search1 = <<<'EOD'
		$municipio_proveedor = !empty($proveedor['municipio_id']) ? $proveedor['municipio_id'] : (!empty($configFactus['municipio_id']) ? $configFactus['municipio_id'] : '981'); // 981 Bogotá default

		$datosDS = [
EOD;

$replace1 = <<<'EOD'
		$municipio_proveedor = !empty($proveedor['municipio_id']) ? $proveedor['municipio_id'] : (!empty($configFactus['municipio_id']) ? $configFactus['municipio_id'] : '981'); // 981 Bogotá default

		$municipio_id_enviar = '981';
		if (!empty($municipio_proveedor)) {
			$stmt = Conexion::conectar()->prepare("SELECT id_factus FROM factus_municipios WHERE id = :id OR id_factus = :id_factus LIMIT 1");
			$stmt->execute([':id' => $municipio_proveedor, ':id_factus' => $municipio_proveedor]);
			$mun = $stmt->fetch();
			if ($mun) {
				$municipio_id_enviar = strval($mun['id_factus']);
			}
		}

		$datosDS = [
EOD;

$search2 = '"municipality_id" => strval($municipio_proveedor),';
$replace2 = '"municipality_id" => $municipio_id_enviar,';

$search3 = <<<'EOD'
		// Usamos un default robusto a Bogotá (981) por si acaso todo falla para evitar rechazos
		$municipio_proveedor = !empty($proveedor['municipio_id']) ? $proveedor['municipio_id'] : (!empty($configFactus['municipio_id']) ? $configFactus['municipio_id'] : '981');


		// Preparar array de la nota de ajuste
		$datosNota = [
EOD;

$replace3 = <<<'EOD'
		// Usamos un default robusto a Bogotá (981) por si acaso todo falla para evitar rechazos
		$municipio_proveedor = !empty($proveedor['municipio_id']) ? $proveedor['municipio_id'] : (!empty($configFactus['municipio_id']) ? $configFactus['municipio_id'] : '981');

		$municipio_id_enviar = '981';
		if (!empty($municipio_proveedor)) {
			$stmt = Conexion::conectar()->prepare("SELECT id_factus FROM factus_municipios WHERE id = :id OR id_factus = :id_factus LIMIT 1");
			$stmt->execute([':id' => $municipio_proveedor, ':id_factus' => $municipio_proveedor]);
			$mun = $stmt->fetch();
			if ($mun) {
				$municipio_id_enviar = strval($mun['id_factus']);
			}
		}

		// Preparar array de la nota de ajuste
		$datosNota = [
EOD;

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);
$content = str_replace($search3, $replace3, $content);

file_put_contents($file, $content);
echo "REPLACED OK\n";
?>