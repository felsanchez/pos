<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar datos para los filtros
require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../../../modelos/clientes.modelo.php";

require_once __DIR__ . "/../../../modelos/configuracion.modelo.php";

 // Obtener usuarios/vendedores
$usuarios = ModeloUsuarios::mdlMostrarUsuarios("usuarios", null, null); 

// Obtener clientes
$clientes = ModeloClientes::mdlMostrarClientes("clientes", null, null);

// Obtener métodos de pago desde la configuración
$configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();

$metodosPago = [];

if (!empty($configuracion["medios_pago"])) {
    $metodosPagoRaw = explode(',', $configuracion["medios_pago"]);

    foreach ($metodosPagoRaw as $metodo) {
        $metodo = trim($metodo);
        if (!empty($metodo)) {
            $metodosPago[] = $metodo;
        }
    }
}

 // Obtener productos únicos (de la tabla productos)

$conn = Conexion::conectar();
$stmtProductos = $conn->prepare("SELECT id, descripcion, tiene_variantes FROM productos ORDER BY descripcion ASC");
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las variantes activas con sus opciones
$sqlVariantes = "
  SELECT pv.id as id_variante, pv.id_producto, ov.nombre as opcion_nombre
  FROM productos_variantes pv
  INNER JOIN productos_variantes_opciones pvo ON pv.id = pvo.id_producto_variante
  INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
  INNER JOIN tipos_variantes tv ON ov.id_tipo_variante = tv.id
  WHERE pv.estado = 1
  ORDER BY pv.id_producto, pv.id, tv.orden, ov.orden
";
$stmtVariantes = $conn->prepare($sqlVariantes);
$stmtVariantes->execute();
$varianteRows = $stmtVariantes->fetchAll(PDO::FETCH_ASSOC);

$variantesAgrupadas = [];
foreach ($varianteRows as $row) {
    $idProd = $row['id_producto'];
    $idVar = $row['id_variante'];
    if (!isset($variantesAgrupadas[$idProd])) {
        $variantesAgrupadas[$idProd] = [];
    }
    if (!isset($variantesAgrupadas[$idProd][$idVar])) {
        $variantesAgrupadas[$idProd][$idVar] = [];
    }
    $variantesAgrupadas[$idProd][$idVar][] = $row['opcion_nombre'];
}
?>

<!--Estilo Filtro de fechas -->
  <style>
    .formulario-filtros-container {
      max-width: 100%;
      padding: 20px;
      border-radius: 12px;
      background-color: #ffffff;
      box-shadow: 0 4px 15px rgba(0,0,0,0.04);
      margin-bottom: 25px;
      border: 1px solid #f0f0f0;
    }
    .formulario-filtros label {
      font-weight: 600;
      margin-top: 5px;
      margin-bottom: 8px;
      font-size: 11px;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .formulario-filtros select,
    .formulario-filtros input[type="date"] {
      border-radius: 8px;
      margin-bottom: 12px;
      border: 1px solid #dcdcdc;
      padding: 8px 12px;
      height: auto;
      box-shadow: none;
      transition: border-color 0.2s;
    }
    .formulario-filtros select:focus,
    .formulario-filtros input[type="date"]:focus {
      border-color: #0072ff;
    }
    .d-none {
      display: none !important;
    }
    .filtros-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 15px;
      align-items: end;
    }
    .filtro-grupo {
      min-width: 0;
    }
    .btn-filtrar {
      margin-top: 0;
      margin-bottom: 12px;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      background: linear-gradient(135deg, #0072ff, #00c6ff);
      border: none;
      color: #fff;
      transition: opacity 0.2s, transform 0.2s;
    }
    .btn-filtrar:hover {
      opacity: 0.9;
      transform: translateY(-1px);
      color: #fff;
    }
    .btn-daterange-av {
      width: 100%;
      margin-bottom: 12px;
      border-radius: 8px;
      border: 1px solid #dcdcdc;
      background: #fff;
      color: #555;
      text-align: left;
      padding: 8px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .btn-daterange-av:hover, .btn-daterange-av:focus {
      border-color: #0072ff;
      outline: none;
      box-shadow: none;
      color: #333;
    }
    .btn-daterange-av span { flex: 1; }
    .btn-limpiar {
      margin-top: 0;
      margin-bottom: 12px;
      height: 40px;
      width: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      border: 1px solid #dcdcdc;
      color: #666;
      background: #fff;
      transition: background-color 0.2s, border-color 0.2s, transform 0.2s;
    }
    .btn-limpiar:hover {
      background-color: #f5f5f5;
      border-color: #ccc;
      color: #333;
      transform: translateY(-1px);
    }
    /* Normalizar Select2 para que coincida en altura y alineación con los selects nativos */
    .formulario-filtros .select2-container {
      display: block;
      width: 100% !important;
      margin-bottom: 12px;
    }
    .formulario-filtros .select2-container .select2-selection--single {
      height: 38px;
      border-radius: 8px;
      border: 1px solid #dcdcdc;
      padding: 4px 12px;
      box-shadow: none;
      transition: border-color 0.2s;
      display: flex;
      align-items: center;
    }
    .formulario-filtros .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: normal;
      padding-left: 0;
      color: #555;
    }
    .formulario-filtros .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px;
    }
    .formulario-filtros .select2-container--default.select2-container--focus .select2-selection--single,
    .formulario-filtros .select2-container--default.select2-container--open .select2-selection--single {
      border-color: #0072ff;
      outline: none;
    }
  </style>

  <div class="row">
      <div class="card-body">

              <!-- Filtros combinados -->
             <div class="formulario-filtros-container">
                <form id="filtro-fechas" class="formulario-filtros">
                  <div class="filtros-grid">

                    <!-- Filtro de fecha -->
                    <div class="filtro-grupo">
                      <label>Rango de Fecha</label>
                      <button type="button" class="btn btn-default btn-daterange-av" id="daterange-btn-av">
                        <span><i class="fa fa-calendar"></i> Rango de fecha</span>
                        <i class="fa fa-caret-down"></i>
                      </button>
                      <input type="hidden" id="av-fecha-inicio" name="fecha_inicio">
                      <input type="hidden" id="av-fecha-fin" name="fecha_fin">
                      <input type="hidden" id="av-tipo" name="tipo" value="mes">
                    </div>

                    <!-- Filtro por vendedor -->
                    <div class="filtro-grupo">
                      <label for="filtro-vendedor">Vendedor</label>
                      <select id="filtro-vendedor" name="id_vendedor" class="form-control select2">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($usuarios as $usuario): ?>
                          <?php if ($usuario['perfil'] === '_SystemMaster_') continue; ?>
                          <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por cliente -->
                    <div class="filtro-grupo">
                      <label for="filtro-cliente">Cliente</label>
                      <select id="filtro-cliente" name="id_cliente" class="form-control select2">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($clientes as $cliente): ?>
                          <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por producto -->
                    <div class="filtro-grupo">
                      <label for="filtro-producto">Producto</label>
                      <select id="filtro-producto" name="id_producto" class="form-control select2">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($productos as $producto): ?>
                          <?php 
                            $hasVariants = (isset($producto['tiene_variantes']) && $producto['tiene_variantes'] == 1 && isset($variantesAgrupadas[$producto['id']]) && count($variantesAgrupadas[$producto['id']]) > 0);
                            $disabled = $hasVariants ? 'disabled' : '';
                          ?>
                          <option value="<?php echo $producto['id']; ?>" <?php echo $disabled; ?>><?php echo htmlspecialchars($producto['descripcion']); ?></option>
                          <?php if (isset($producto['tiene_variantes']) && $producto['tiene_variantes'] == 1 && isset($variantesAgrupadas[$producto['id']])): ?>
                            <?php foreach($variantesAgrupadas[$producto['id']] as $idVar => $opciones): ?>
                              <?php 
                                $nombreVarianteStr = implode(" - ", $opciones);
                                $descripcionCompleta = "└─ " . $producto['descripcion'] . " - " . $nombreVarianteStr;
                              ?>
                              <option value="v_<?php echo $idVar; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($descripcionCompleta); ?></option>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por método de pago -->
                    <div class="filtro-grupo">
                      <label for="filtro-metodo-pago">Método de pago</label>
                      <select id="filtro-metodo-pago" name="metodo_pago" class="form-control">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($metodosPago as $metodo): ?>
                          <option value="<?php echo htmlspecialchars($metodo); ?>"><?php echo htmlspecialchars($metodo); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Botón de filtrar -->
                    <div class="filtro-grupo" style="display: flex; gap: 10px; align-items: center;">
                      <button type="submit" class="btn btn-primary w-100 btn-filtrar" style="flex: 1;">Aplicar filtros</button>
                      <a href="index.php?ruta=<?php echo $_GET['ruta'] ?? 'reportes'; ?>" class="btn btn-default btn-limpiar" title="Limpiar">
                        <i class="fa fa-refresh"></i>
                      </a>
                    </div>

                    <!-- Botones de descarga -->
                    <?php if (puedeAccion('reporte_ventas', 'imprimir')): ?>
                      <div class="filtro-grupo" style="display: flex; flex-direction: column; gap: 8px; align-items: stretch; margin-bottom: 12px;">
                        <a class="btn btn-success w-100" style="height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-weight: 600; margin: 0;" id="btn-descargar-excel-directo" href="#">
                          <i class="fa fa-file-excel-o" style="margin-right: 5px;"></i> Descargar Excel
                        </a>
                        <a class="btn btn-danger w-100" style="height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-weight: 600; margin: 0;" id="btn-descargar-pdf-directo" href="#" target="_blank">
                          <i class="fa fa-file-pdf-o" style="margin-right: 5px;"></i> Descargar PDF
                        </a>
                      </div>
                    <?php endif; ?>

                  </div>
                </form>
              </div>

        <!-- Contenedor del Gráfico de Ventas -->
        <div class="box box-primary" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 25px;">
          <div class="box-header with-border" style="background-color: #fafafa; padding: 15px 20px;">
            <h3 class="box-title" style="font-weight: 700; color: #333;"><i class="fa fa-line-chart" style="margin-right: 8px; color: #0072ff;"></i>Evolución de Ventas</h3>
          </div>
          <div class="box-body" style="padding: 20px;">
            <div class="text-center" style="margin-bottom: 20px;">
              <h4 style="color: #666; font-weight: 500; margin: 0 0 5px 0; font-size: 14px;">Total Ventas Periodo</h4>
              <h2 id="total-ventas" style="color: #2e7d32; font-weight: 800; margin: 0; font-size: 32px; letter-spacing: -1px;">$0</h2>
            </div>
            <div id="sales-chart" style="min-height: 220px;"></div>
          </div>
        </div>
       
      </div>
  </div>


<!-- GRAFICO DE VENTAS ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>

<!-- Script para inicializar y actualizar el gráfico -->
<script>
  const salesChart = new ApexCharts(document.querySelector('#sales-chart'), {
    series: [],
    chart: {
      height: 220,
      type: 'area',
      toolbar: { show: false },
      fontFamily: 'inherit'
    },
    colors: ['#0072ff'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.5,
        opacityTo: 0.05,
        stops: [0, 90, 100]
      }
    },
    grid: {
      borderColor: '#f1f1f1',
      strokeDashArray: 4,
      xaxis: { lines: { show: false } }
    },
    xaxis: { 
      type: 'datetime', 
      categories: [],
      labels: {
        style: { colors: '#999', fontSize: '11px' }
      }
    },
    yaxis: {
      labels: {
        style: { colors: '#999', fontSize: '11px' },
        formatter: function (val) {
          return '$' + Math.round(val).toLocaleString('es-CO');
        }
      }
    },
    tooltip: { 
      x: { format: 'dd MMM yyyy' },
      y: {
        formatter: function (val) {
          return '$' + Math.round(val).toLocaleString('es-CO');
        }
      }
    }
  });

  salesChart.render();

  // Inicializar DateRangePicker en el filtro de fecha
  $(document).ready(function () {
    // Establecer rango por defecto: mes actual
    var avFechaInicio = moment().startOf('month').format('YYYY-MM-DD');
    var avFechaFin = moment().endOf('month').format('YYYY-MM-DD');
    $('#av-fecha-inicio').val(avFechaInicio);
    $('#av-fecha-fin').val(avFechaFin);
    $('#av-tipo').val('personalizado');
    $('#daterange-btn-av span').html('<i class="fa fa-calendar"></i> ' + moment().startOf('month').format('MMMM D, YYYY') + ' - ' + moment().endOf('month').format('MMMM D, YYYY'));

    if (typeof $.fn.daterangepicker !== 'undefined') {
      $('#daterange-btn-av').daterangepicker({
        ranges: {
          'Todas las fechas': [moment('2000-01-01'), moment()],
          'Hoy': [moment(), moment()],
          'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
          'Este mes': [moment().startOf('month'), moment().endOf('month')],
          'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: { cancelLabel: 'Limpiar' }
      }, function (start, end) {
        $('#daterange-btn-av span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#av-fecha-inicio').val(start.format('YYYY-MM-DD'));
        $('#av-fecha-fin').val(end.format('YYYY-MM-DD'));
        $('#av-tipo').val('personalizado');
        // Disparar filtrado automático
        document.getElementById('filtro-fechas').dispatchEvent(new Event('submit'));
      });

      $('#daterange-btn-av').on('cancel.daterangepicker', function () {
        $(this).find('span').html('<i class="fa fa-calendar"></i> Rango de fecha');
        $('#av-fecha-inicio').val('');
        $('#av-fecha-fin').val('');
        $('#av-tipo').val('todo');
        document.getElementById('filtro-fechas').dispatchEvent(new Event('submit'));
      });
    }

    // Disparar filtro inicial al cargar
    document.getElementById('filtro-fechas').dispatchEvent(new Event('submit'));
  });

  // Escuchar el envío del formulario
  document.getElementById('filtro-fechas').addEventListener('submit', function (e) {
    e.preventDefault();

    const tipo = document.getElementById('av-tipo').value;
    const fechaInicio = document.getElementById('av-fecha-inicio').value;
    const fechaFin = document.getElementById('av-fecha-fin').value;

    // Obtener los nuevos filtros
    const idVendedor = document.getElementById('filtro-vendedor').value;
    const idCliente = document.getElementById('filtro-cliente').value;
    const idProducto = document.getElementById('filtro-producto').value;
    const metodoPago = document.getElementById('filtro-metodo-pago').value;

    const formData = new FormData();
    formData.append('tipo', tipo);

    if (tipo === 'personalizado') {
      if (!fechaInicio || !fechaFin) return;
      formData.append('fecha_inicio', fechaInicio);
      formData.append('fecha_fin', fechaFin);
    }

    // Agregar los nuevos filtros al FormData
    if (idVendedor) formData.append('id_vendedor', idVendedor);
    if (idCliente) formData.append('id_cliente', idCliente);
    if (idProducto) formData.append('id_producto', idProducto);
    if (metodoPago) formData.append('metodo_pago', metodoPago);

    // Agregar id_bodega del filtro maestro si existe o de la sesion actual
    const sucursalMaestra = document.getElementById('sucursalReporteMaestro');
    const idBodega = sucursalMaestra ? sucursalMaestra.value : '<?php echo isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1; ?>';
    if (idBodega && idBodega !== 'todos') formData.append('id_bodega', idBodega);

    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";

    fetch(`${rutaBase}/vistas/modulos/reportes/filtro_ventas.php`, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      const datos = data.datos;
      const total = data.total;

      // 1. Mostrar el total en el título del gráfico
      document.getElementById('total-ventas').textContent = total.toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      });

      // Extraer fechas y totales para la gráfica
      const fechas = datos.map(item => item.fecha);
      const totales = datos.map(item => item.total_ventas);

      // Actualizar el gráfico de línea
      salesChart.updateOptions({
        xaxis: { categories: fechas },
        series: [{
          name: "Ventas",
          data: totales
        }]
      });

      // 2. Actualizar el gráfico de dona de productos más vendidos
      if (typeof actualizarGraficoProductos === 'function') {
        actualizarGraficoProductos(data.productos_top);
      }

      // 3. Actualizar las cajas superiores
      const formatCOP = (val) => '$' + Math.round(val).toLocaleString('es-CO');
      const formatQty = (val) => Math.round(val).toLocaleString('es-CO');

      const cajaTotal = document.getElementById('caja-total-ventas');
      const cajaTicket = document.getElementById('caja-ticket-promedio');
      const cajaCantVentas = document.getElementById('caja-cant-ventas');
      const cajaCantOrdenes = document.getElementById('caja-cant-ordenes');

      if (cajaTotal) cajaTotal.textContent = formatCOP(data.total);
      if (cajaTicket) cajaTicket.textContent = formatCOP(data.ticket_promedio);
      if (cajaCantVentas) cajaCantVentas.textContent = formatQty(data.total_ventas_cantidad);
      if (cajaCantOrdenes) cajaCantOrdenes.textContent = formatQty(data.total_ordenes);
    })
    .catch(error => {
      console.error("Error al cargar datos:", error);
    });
  });
</script>

<script>
  // Inicializar Select2 en los filtros de búsqueda
  $(document).ready(function () {
    $('#filtro-vendedor').select2({
      placeholder: 'Mostrar Todos',
      allowClear: true,
      language: { noResults: function () { return 'Sin resultados'; } }
    });
    $('#filtro-cliente').select2({
      placeholder: 'Mostrar Todos',
      allowClear: true,
      language: { noResults: function () { return 'Sin resultados'; } }
    });
    $('#filtro-producto').select2({
      placeholder: 'Mostrar Todos',
      allowClear: true,
      language: { noResults: function () { return 'Sin resultados'; } }
    });
  });
</script>