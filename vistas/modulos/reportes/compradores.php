<?php

$item = null;
$valor = null;

$ventas = ControladorVentas::ctrMostrarVentas($item, $valor);
$clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

$arrayClientes = array();
$sumaTotalClientes = array();

foreach ($ventas as $valueVentas) {

  // ✅ Filtrar solo las ventas que tengan estado "venta"
  if (!isset($valueVentas["estado"]) || $valueVentas["estado"] !== "venta") continue;

  foreach ($clientes as $valueClientes) {

    if ($valueClientes["id"] == $valueVentas["id_cliente"]) {

      $nombreCliente = $valueClientes["nombre"];

      $arrayClientes[] = $nombreCliente;

      if (!isset($sumaTotalClientes[$nombreCliente])) {
        $sumaTotalClientes[$nombreCliente] = 0;
      }

      $sumaTotalClientes[$nombreCliente] += floatval($valueVentas["total"]);
    }
  }
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


