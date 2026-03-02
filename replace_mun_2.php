<?php
$file = 'c:/xampp/htdocs/pos/controladores/factus.controlador.php';
$content = file_get_contents($file);

$search1 = <<<'EOD'
			"municipality_id" => strval(
				(function ($prov_mun) {
					$mun_id = '981';
					if (!empty($prov_mun)) {
						$stmt = Conexion::conectar()->prepare("SELECT id_factus FROM factus_municipios WHERE id = :id OR id_factus = :id_factus LIMIT 1");
						$stmt->execute([':id' => $prov_mun, ':id_factus' => $prov_mun]);
						$mun = $stmt->fetch();
						if ($mun)
							$mun_id = strval($mun['id_factus']);
					}
					return $mun_id;
				})(!empty($proveedor['municipio_id']) ? $proveedor['municipio_id'] : (ModeloFactus::mdlObtenerConfiguracion()['municipio_id'] ?? '981'))
			),
EOD;

$replace1 = <<<'EOD'
			"municipality_id" => strval(
				(function ($prov_mun) {
					$mun_id = '981';
					if (!empty($prov_mun)) {
						$stmt = Conexion::conectar()->prepare("SELECT id_factus FROM factus_municipios WHERE id = :id OR id_factus = :id_factus LIMIT 1");
						$stmt->execute([':id' => $prov_mun, ':id_factus' => $prov_mun]);
						$mun = $stmt->fetch();
						if ($mun) {
							$mun_id = strval($mun['id_factus']);
						}
					}
					file_put_contents(__DIR__ . '/../ajax/log_mun.txt', "Input: $prov_mun | Output: $mun_id\n", FILE_APPEND);
					return $mun_id;
				})(!empty($proveedor['municipio_id']) ? $proveedor['municipio_id'] : (ModeloFactus::mdlObtenerConfiguracion()['municipio_id'] ?? '981'))
			),
EOD;

$content = str_replace($search1, $replace1, $content);
file_put_contents($file, $content);
echo "REPLACED OK\n";
?>