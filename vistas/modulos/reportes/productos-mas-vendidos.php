<?php

// Obtener parámetros de filtro (por defecto mes actual para coincidir con la vista de inicio)
$idBodega = isset($idBodega) ? $idBodega : (isset($_POST["idBodega"]) ? $_POST["idBodega"] : (isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1));
$fechaInicial = isset($fechaInicial) ? $fechaInicial : (isset($_POST["fechaInicial"]) ? $_POST["fechaInicial"] : null);
$fechaFinal = isset($fechaFinal) ? $fechaFinal : (isset($_POST["fechaFinal"]) ? $_POST["fechaFinal"] : null);

if($fechaInicial == null || $fechaInicial == ""){
    $fechaInicial = date("Y-m-01"); // Primer día del mes actual
    $fechaFinal = date("Y-m-t");   // Último día del mes actual
}

$inicio = $fechaInicial . " 00:00:00";
$fin = $fechaFinal . " 23:59:59";

// Traer ventas filtradas por fecha y bodega
$db = Conexion::conectar();
$filtroBodega = ($idBodega != "" && $idBodega != "todos") ? " AND id_bodega = :idBodega " : "";

$sql = "SELECT productos, estado FROM ventas WHERE fecha BETWEEN :inicio AND :fin" . $filtroBodega;
$stmt = $db->prepare($sql);
$stmt->bindParam(":inicio", $inicio, PDO::PARAM_STR);
$stmt->bindParam(":fin", $fin, PDO::PARAM_STR);
if($filtroBodega != "") $stmt->bindParam(":idBodega", $idBodega, PDO::PARAM_INT);
$stmt->execute();
$ventas = $stmt->fetchAll();

// Array para acumular ventas por ID de producto
$productosVendidos = array();

foreach ($ventas as $venta) {

    if ($venta["estado"] != "venta") continue;

    $listaProductos = json_decode($venta["productos"], true);

    // Validar que el JSON sea válido y sea un array
    if (!is_array($listaProductos)) continue; 

    foreach ($listaProductos as $producto) {
        // Validar que existan las claves necesarias
        if (!isset($producto["id"]) || !isset($producto["cantidad"])) {
            continue;
        }

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

// Colores modernos Hex para la gráfica
$coloresDonaHex = array('#0072ff', '#7f00ff', '#fe8c00', '#11998e', '#ff5858', '#3c8dbc', '#f39c12', '#00a65a', '#605ca8', '#d2d6de');

?>

<div class="box box-default" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden;">

  <div class="box-header with-border" style="background-color: #fafafa; padding: 15px 20px;">
    <h3 class="box-title" style="font-weight: 700; color: #333;"><i class="fa fa-pie-chart" style="margin-right: 8px; color: #7f00ff;"></i>Productos más vendidos</h3>
  </div>

  <div class="box-body" style="padding: 20px;">
    <div class="row">
      <div class="col-md-12">
        <!-- Contenedor para ApexCharts -->
        <div id="productos-mas-vendidos-chart" style="min-height: 250px;"></div>
      </div>
    </div>
  </div>

  <div class="box-footer no-padding" style="background-color: #fff; border-top: 1px solid #f4f4f4;">
    <ul class="nav nav-pills nav-stacked" id="lista-productos-mas-vendidos">
      <?php 
      $divisorTotal = $totalVentas > 0 ? $totalVentas : 1; 
      foreach (array_slice($productosTop, 0, 5) as $i => $producto): 
        $color = isset($coloresDonaHex[$i]) ? $coloresDonaHex[$i] : '#888';
        $pct = round(($producto["ventas_acumuladas"] * 100) / $divisorTotal);
        $img = !empty($producto["imagen"]) ? $producto["imagen"] : 'vistas/img/productos/default/anonymous.png';
      ?>
        <li>
          <a style="display: flex; align-items: center; justify-content: space-between; padding: 12px 15px;">
            <div style="display: flex; align-items: center;">
              <img src="<?= $img ?>" class="img-thumbnail" width="45px" style="margin-right:12px; border-radius: 6px;">
              <span style="font-weight: 600; color: #444;"><?= $producto["descripcion"] ?></span>
            </div>
            <span class="pull-right" style="color: <?= $color ?>; text-align: right;">
              <h5 style="margin: 0; font-weight: 700;">
                <i class="fa fa-shopping-cart" style="margin-right: 5px;"></i><?= $pct ?>%
                <small style="display:block; font-size:10px; color:#999; margin-top:2px;">(<?= $producto["ventas_acumuladas"] ?> und)</small>
              </h5>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
      <?php if (empty($productosTop)): ?>
        <li><a class="text-center text-muted" style="padding: 20px;">No hay ventas registradas en este periodo.</a></li>
      <?php endif; ?>
    </ul>
  </div>

</div>

<!-- Cargar ApexCharts si no se ha cargado previamente -->
<script>
  if (typeof ApexCharts === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"><\/script>');
  }
</script>

<script>
  // Inicialización de la gráfica de productos con ApexCharts
  const optionsProductos = {
    series: [<?php echo implode(',', array_map(function($p) { return $p['ventas_acumuladas']; }, $productosTop)); ?>],
    chart: {
      type: 'donut',
      height: 250,
      fontFamily: 'inherit'
    },
    labels: [<?php echo implode(',', array_map(function($p) { return '"' . addslashes($p['descripcion']) . '"'; }, $productosTop)); ?>],
    colors: ['#0072ff', '#7f00ff', '#fe8c00', '#11998e', '#ff5858', '#3c8dbc', '#f39c12', '#00a65a', '#605ca8', '#d2d6de'],
    legend: {
      position: 'bottom',
      horizontalAlign: 'center',
      fontSize: '11px',
      markers: { radius: 12 }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      width: 2,
      colors: ['#ffffff']
    },
    plotOptions: {
      pie: {
        donut: {
          size: '68%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total',
              fontSize: '14px',
              fontWeight: 600,
              color: '#333',
              formatter: function (w) {
                return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' und';
              }
            }
          }
        }
      }
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val + " unidades";
        }
      }
    }
  };

  const chartProductos = new ApexCharts(document.querySelector("#productos-mas-vendidos-chart"), optionsProductos);
  chartProductos.render();

  // Función global para actualizar dinámicamente la dona y la lista de productos por AJAX
  function actualizarGraficoProductos(productos) {
    const containerLista = document.getElementById('lista-productos-mas-vendidos');
    if (!containerLista) return;

    if (!productos || productos.length === 0) {
      containerLista.innerHTML = '<li><a class="text-center text-muted" style="padding:20px;">No hay ventas registradas en este periodo.</a></li>';
      chartProductos.updateOptions({
        series: [],
        labels: []
      });
      return;
    }

    const total = productos.reduce((sum, p) => sum + parseFloat(p.ventas_acumuladas), 0);
    const series = productos.map(p => parseFloat(p.ventas_acumuladas));
    const labels = productos.map(p => p.descripcion);

    chartProductos.updateOptions({
      series: series,
      labels: labels
    });

    let html = '';
    const top5 = productos.slice(0, 5);
    const colores = ['#0072ff', '#7f00ff', '#fe8c00', '#11998e', '#ff5858', '#3c8dbc', '#f39c12', '#00a65a', '#605ca8', '#d2d6de'];

    top5.forEach((p, idx) => {
      const color = colores[idx % colores.length];
      const pct = total > 0 ? Math.round((parseFloat(p.ventas_acumuladas) * 100) / total) : 0;
      const img = p.imagen ? p.imagen : 'vistas/img/productos/default/anonymous.png';
      html += `
        <li>
          <a style="display: flex; align-items: center; justify-content: space-between; padding: 12px 15px;">
            <div style="display: flex; align-items: center;">
              <img src="${img}" class="img-thumbnail" width="45px" style="margin-right:12px; border-radius: 6px;">
              <span style="font-weight: 600; color: #444;">${p.descripcion}</span>
            </div>
            <span class="pull-right" style="color: ${color}; text-align: right;">
              <h5 style="margin: 0; font-weight: 700;">
                <i class="fa fa-shopping-cart" style="margin-right: 5px;"></i>${pct}%
                <small style="display:block; font-size:10px; color:#999; margin-top:2px;">(${p.ventas_acumuladas} und)</small>
              </h5>
            </span>
          </a>
        </li>
      `;
    });
    containerLista.innerHTML = html;
  }
</script>

