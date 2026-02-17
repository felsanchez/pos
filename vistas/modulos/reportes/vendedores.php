<?php

$tabla = "ventas";
$item = null;
$valor = null;

$ventas = ControladorVentas::ctrMostrarVentasAsociativo($tabla, $item, $valor);

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


