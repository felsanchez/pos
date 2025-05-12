<?php

$item = null;
$valor = null;
$orden = "ventas";

// Traemos todas las ventas con estado "venta"
$ventas = ControladorVentas::ctrMostrarVentas($item, $valor);

// Array para acumular ventas por ID de producto
$productosVendidos = array();

foreach ($ventas as $venta) {

    if ($venta["estado"] != "venta") continue;

    $listaProductos = json_decode($venta["productos"], true);

    foreach ($listaProductos as $producto) {
        $idProducto = $producto["id"];
        $cantidad = $producto["cantidad"];

        if (!isset($productosVendidos[$idProducto])) {
            $productosVendidos[$idProducto] = 0;
        }

        $productosVendidos[$idProducto] += $cantidad;
    }
}

// Ordenamos los productos por cantidad vendida (descendente)
arsort($productosVendidos);

// Tomamos los 10 productos más vendidos
$productosVendidosTop10 = array_slice($productosVendidos, 0, 10, true);

// Traemos los datos de esos productos
$productosTop = array();
$totalVentas = 0;

foreach ($productosVendidosTop10 as $idProducto => $cantidad) {
    $producto = ControladorProductos::ctrMostrarProductos("id", $idProducto, null);
    if ($producto) {
    $producto["ventas_acumuladas"] = $cantidad;
    $productosTop[] = $producto;
    $totalVentas += $cantidad;
}
}

// Colores para la gráfica
$colores = array("red", "teal", "green", "yellow", "aqua", "purple", "fuchsia", "blue", "orange", "maroon");

?>

<div class="box box-default">

  <div class="box-header with-border">
    <h3 class="box-title">Productos más vendidos</h3>
  </div>

  <div class="box-body">
    <div class="row">

      <div class="col-md-7">
        <div class="chart-responsive">
          <canvas id="pieChart" height="150"></canvas>
        </div>
      </div>

      <div class="col-md-5">
        <ul class="chart-legend clearfix">
          <?php foreach ($productosTop as $i => $producto): ?>
            <li><i class="fa fa-circle-o text-<?= $colores[$i] ?>"></i> <?= $producto["descripcion"] ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>
  </div>

  <div class="box-footer no-padding">
    <ul class="nav nav-pills nav-stacked">
      <?php foreach (array_slice($productosTop, 0, 5) as $i => $producto): ?>
        <li>
          <a>
            <img src="<?= $producto["imagen"] ?>" class="img-thumbnail" width="60px" style="margin-right:10px">
            <?= $producto["descripcion"] ?>
            <span class="pull-right text-<?= $colores[$i] ?>">
              <h5>
                <i class="fa fa-shopping-cart"></i>
                <?= ceil($producto["ventas_acumuladas"] * 100 / $totalVentas) ?>%
              </h5>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

</div>

<script>
  var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
  var pieChart       = new Chart(pieChartCanvas);
  var PieData        = [

    <?php foreach ($productosTop as $i => $producto): ?>
    {
      value    : <?= $producto["ventas_acumuladas"] ?>,
      color    : '<?= $colores[$i] ?>',
      highlight: '<?= $colores[$i] ?>',
      label    : '<?= $producto["descripcion"] ?>'
    },
    <?php endforeach; ?>

  ];

  var pieOptions = {
    segmentShowStroke    : true,
    segmentStrokeColor   : '#fff',
    segmentStrokeWidth   : 1,
    percentageInnerCutout: 50,
    animationSteps       : 100,
    animationEasing      : 'easeOutBounce',
    animateRotate        : true,
    animateScale         : false,
    responsive           : true,
    maintainAspectRatio  : false,
    legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
    tooltipTemplate      : '<%=value %> <%=label%>'
  };

  pieChart.Doughnut(PieData, pieOptions);
</script>
