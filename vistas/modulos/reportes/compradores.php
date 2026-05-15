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

$sql = "SELECT c.nombre as nombre_cliente, v.total, v.estado 
        FROM ventas v 
        LEFT JOIN clientes c ON v.id_cliente = c.id 
        WHERE v.fecha BETWEEN :inicio AND :fin" . $filtroBodega;
$stmt = $db->prepare($sql);
$stmt->bindParam(":inicio", $inicio, PDO::PARAM_STR);
$stmt->bindParam(":fin", $fin, PDO::PARAM_STR);
if($filtroBodega != "") $stmt->bindParam(":idBodega", $idBodega, PDO::PARAM_INT);
$stmt->execute();
$ventas = $stmt->fetchAll();

$sumaTotalClientes = array();
$arrayClientes = array();

foreach ($ventas as $valueVentas) {
    if (!isset($valueVentas["estado"]) || $valueVentas["estado"] !== "venta") continue;

    $nombreCliente = !empty($valueVentas["nombre_cliente"]) ? $valueVentas["nombre_cliente"] : "Venta General";
    $arrayClientes[] = $nombreCliente;

    if (!isset($sumaTotalClientes[$nombreCliente])) {
        $sumaTotalClientes[$nombreCliente] = 0;
    }
    $sumaTotalClientes[$nombreCliente] += floatval($valueVentas["total"]);
}

$noRepetirNombres = array_unique($arrayClientes);

?>


<!--=====================================
VENDEDORES
======================================-->

<div class="box box-primary">
	
	<div class="box-header with-border">
    
    	<h3 class="box-title">Mejores Compradores</h3>
  
  	</div>

  	<div class="box-body">
  		
		<div class="chart-responsive">
			
			<div class="chart" id="bar-chart2" style="height: 300px;"></div>

		</div>

  	</div>

</div>

<script>

	//BAR CHART
    var bar = new Morris.Bar({
      element: 'bar-chart2',
      resize: true,
      data: [
       <?php
    
          foreach($noRepetirNombres as $value){

            echo "{y: '".$value."', a: '".$sumaTotalClientes[$value]."'},";

          }

        ?>
      ],
      barColors: ['#f6a'],
      xkey: 'y',
      ykeys: ['a'],
      labels: ['ventas'],
      preUnits: '$',
      hideHover: 'auto'
    });
	
</script>


