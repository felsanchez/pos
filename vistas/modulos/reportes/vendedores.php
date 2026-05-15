<?php

// Obtener parámetros de filtro
$idBodega = isset($idBodega) ? $idBodega : (isset($_POST["idBodega"]) ? $_POST["idBodega"] : "todos");
$fechaInicial = isset($fechaInicial) ? $fechaInicial : (isset($_POST["fechaInicial"]) ? $_POST["fechaInicial"] : null);
$fechaFinal = isset($fechaFinal) ? $fechaFinal : (isset($_POST["fechaFinal"]) ? $_POST["fechaFinal"] : null);

if($fechaInicial == null || $fechaInicial == ""){
    $fechaInicial = "2000-01-01";
    $fechaFinal = "2100-12-31";
}

$inicio = $fechaInicial . " 00:00:00";
$fin = $fechaFinal . " 23:59:59";

// Traer ventas filtradas por fecha y bodega
$db = Conexion::conectar();
$filtroBodega = ($idBodega != "" && $idBodega != "todos") ? " AND v.id_bodega = :idBodega " : "";

$sql = "SELECT u.nombre as nombre_vendedor, v.total, v.estado 
        FROM ventas v 
        LEFT JOIN usuarios u ON v.id_vendedor = u.id 
        WHERE v.fecha BETWEEN :inicio AND :fin" . $filtroBodega;
$stmt = $db->prepare($sql);
$stmt->bindParam(":inicio", $inicio, PDO::PARAM_STR);
$stmt->bindParam(":fin", $fin, PDO::PARAM_STR);
if($filtroBodega != "") $stmt->bindParam(":idBodega", $idBodega, PDO::PARAM_INT);
$stmt->execute();
$ventas = $stmt->fetchAll();

$sumaTotalVendedores = [];

foreach ($ventas as $venta) {

    /*
    echo "<pre>";
    print_r($venta);
    echo "</pre>";
    */

  if (!isset($venta["estado"])) continue;
  if ($venta["estado"] !== "venta") continue;

  $nombre = $venta["nombre_vendedor"];

      /*
      echo "<pre>";
      echo "Vendedor: " . $nombre . "\n";
      echo "Neto: ";
      var_dump($venta["total"]);
      echo "</pre>";
      */

  if (!isset($sumaTotalVendedores[$nombre])) {
    $sumaTotalVendedores[$nombre] = 0;
  }

  $sumaTotalVendedores[$nombre] += $venta["total"];
}

$noRepetirNombres = array_keys($sumaTotalVendedores);

?>


<!--=====================================
VENDEDORES
======================================-->

<div class="box box-success">
	
	<div class="box-header with-border">
    
    	<h3 class="box-title">Mejores Vendedores</h3>
  
  	</div>

  	<div class="box-body">
  		
		<div class="chart-responsive">
			
			<div class="chart" id="bar-chart1" style="height: 300px;"></div>

		</div>

  	</div>

</div>

<script>

	//BAR CHART
    var bar = new Morris.Bar({
      element: 'bar-chart1',
      resize: true,
      data: [
       <?php
    
        foreach($noRepetirNombres as $value){

          echo "{y: '".$value."', a: '".$sumaTotalVendedores[$value]."'},";

        }
      ?>
      ],
      barColors: ['#0af'],
      xkey: 'y',
      ykeys: ['a'],
      labels: ['ventas'],
      preUnits: '$',
      hideHover: 'auto'
    });
	
</script>


