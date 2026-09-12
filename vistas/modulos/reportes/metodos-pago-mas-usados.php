<?php

require_once __DIR__ . "/../../../modelos/configuracion.modelo.php";

// Obtener parámetros de filtro (pueden venir de AJAX o del scope global)
$idBodega = isset($idBodega) ? $idBodega : (isset($_POST["idBodega"]) ? $_POST["idBodega"] : "todos");
$fechaInicial = isset($fechaInicial) ? $fechaInicial : (isset($_POST["fechaInicial"]) ? $_POST["fechaInicial"] : null);
$fechaFinal = isset($fechaFinal) ? $fechaFinal : (isset($_POST["fechaFinal"]) ? $_POST["fechaFinal"] : null);

// Ajustar fechas
if($fechaInicial == null || $fechaInicial == ""){
    $fechaInicial = "2000-01-01";
    $fechaFinal = "2100-12-31";
}

$inicio = $fechaInicial . " 00:00:00";
$fin = $fechaFinal . " 23:59:59";

// Traer ventas filtradas por fecha y bodega si aplica
$db = Conexion::conectar();
$filtroBodega = ($idBodega != "" && $idBodega != "todos") ? " AND id_bodega = :idBodega " : "";

$sql = "SELECT metodo_pago, estado FROM ventas WHERE fecha BETWEEN :inicio AND :fin" . $filtroBodega;
$stmt = $db->prepare($sql);
$stmt->bindParam(":inicio", $inicio, PDO::PARAM_STR);
$stmt->bindParam(":fin", $fin, PDO::PARAM_STR);
if($filtroBodega != "") $stmt->bindParam(":idBodega", $idBodega, PDO::PARAM_INT);
$stmt->execute();
$ventas = $stmt->fetchAll();

// Obtener métodos de pago desde la configuración
$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();
$metodosConfigurados = [];
if (!empty($configuracion["medios_pago"])) {
    $metodosPagoRaw = explode(',', $configuracion["medios_pago"]);
    foreach ($metodosPagoRaw as $metodo) {
        $metodo = trim($metodo);
        if (!empty($metodo)) {
            $metodosConfigurados[] = $metodo;
        }
    }
}

// Array para acumular conteo por método de pago
$metodosPagoConteo = array();
$totalVentas = 0;

// Inicializar contadores para cada método configurado
foreach ($metodosConfigurados as $metodo) {
    $metodosPagoConteo[$metodo] = 0;
}

foreach ($ventas as $venta) {

    if ($venta["estado"] != "venta") continue;

    $metodoPago = $venta["metodo_pago"];

    // Si está vacío, saltar
    if (empty($metodoPago)) continue;

    // Extraer solo el nombre del método (sin código de transacción)
    $nombreMetodo = explode('-', $metodoPago)[0];
    $nombreMetodo = trim($nombreMetodo);

    if (empty($nombreMetodo)) continue;

    // Solo contar si está en los métodos configurados
    if (in_array($nombreMetodo, $metodosConfigurados)) {
        $metodosPagoConteo[$nombreMetodo]++;
        $totalVentas++;
    }
}

// Ordenamos los métodos por cantidad de uso (descendente)
arsort($metodosPagoConteo);

// Filtramos solo los que tienen al menos una venta
$metodosTop10 = array_filter($metodosPagoConteo, function($cantidad) {
    return $cantidad > 0;
});

// Tomamos los 10 primeros
$metodosTop10 = array_slice($metodosTop10, 0, 10, true);

// Colores para la gráfica
$colores = array("blue", "green", "yellow", "red", "aqua", "purple", "teal", "orange", "fuchsia", "maroon");

// Iconos para métodos de pago comunes
$iconosMetodos = array(
    "Efectivo" => "fa-money",
    "Tarjeta" => "fa-credit-card",
    "Tarjeta Débito" => "fa-credit-card",
    "Tarjeta Crédito" => "fa-credit-card",
    "Transferencia" => "fa-exchange",
    "Nequi" => "fa-mobile",
    "Bancolombia" => "fa-university",
    "Daviplata" => "fa-mobile",
    "PSE" => "fa-globe",
    "Cheque" => "fa-file-text-o"
);

?>

<div class="box box-default" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 20px;">

  <div class="box-header with-border" style="background-color: #fafafa; padding: 15px 20px;">
    <h3 class="box-title" style="font-weight: 700; color: #333;"><i class="fa fa-credit-card" style="margin-right: 8px; color: #0072ff;"></i>Métodos de pago más usados</h3>
  </div>

  <div class="box-body" style="padding: 20px;">
    <?php if (empty($metodosTop10) || $totalVentas == 0): ?>
      <div class="text-center text-muted" style="padding: 40px 15px; font-weight: 600; font-size: 14px;">
        <i class="fa fa-info-circle" style="margin-right: 5px; font-size: 18px;"></i> Sin ventas registradas
      </div>
    <?php else: ?>
      <div class="row">
        <div class="col-md-7">
          <div class="chart-responsive">
            <canvas id="pieChartMetodosPago" height="150"></canvas>
          </div>
        </div>

        <div class="col-md-5">
          <ul class="chart-legend clearfix">
            <?php $i = 0; foreach ($metodosTop10 as $metodo => $cantidad): ?>
              <li><i class="fa fa-circle-o text-<?= $colores[$i] ?>"></i> <?= htmlspecialchars($metodo) ?></li>
            <?php $i++; endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($metodosTop10) && $totalVentas > 0): ?>
  <div class="box-footer no-padding" style="background-color: #fff; border-top: 1px solid #f4f4f4;">
    <ul class="nav nav-pills nav-stacked">
      <?php $i = 0; foreach (array_slice($metodosTop10, 0, 5, true) as $metodo => $cantidad): ?>
        <?php
          $icono = isset($iconosMetodos[$metodo]) ? $iconosMetodos[$metodo] : "fa-money";
          $porcentaje = $totalVentas > 0 ? ceil($cantidad * 100 / $totalVentas) : 0;
        ?>
        <li>
          <a>
            <i class="fa <?= $icono ?> fa-2x" style="margin-right:10px; color: <?= $colores[$i] ?>"></i>
            <?= htmlspecialchars($metodo) ?>
            <span class="pull-right text-<?= $colores[$i] ?>">
              <h5>
                <i class="fa fa-shopping-cart"></i>
                <?= $porcentaje ?>%
                <small>(<?= $cantidad ?>)</small>
              </h5>
            </span>
          </a>
        </li>
      <?php $i++; endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

</div>

<?php if (!empty($metodosTop10) && $totalVentas > 0): ?>
<script>
  var pieChartCanvasMetodos = $('#pieChartMetodosPago').get(0).getContext('2d');
  var pieChartMetodos       = new Chart(pieChartCanvasMetodos);
  var PieDataMetodos        = [

    <?php $i = 0; foreach ($metodosTop10 as $metodo => $cantidad): ?>
    {
      value    : <?= $cantidad ?>,
      color    : '<?= $colores[$i] ?>',
      highlight: '<?= $colores[$i] ?>',
      label    : '<?= htmlspecialchars($metodo) ?>'
    },
    <?php $i++; endforeach; ?>

  ];

  var pieOptionsMetodos = {
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

  pieChartMetodos.Doughnut(PieDataMetodos, pieOptionsMetodos);
</script>
<?php endif; ?>