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
$stmtProductos = $conn->prepare("SELECT id, descripcion FROM productos ORDER BY descripcion ASC");
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
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
  </style>

  <div class="row">
      <div class="card-body">

              <!-- Filtros combinados -->
             <div class="formulario-filtros-container">
                <form id="filtro-fechas" class="formulario-filtros">
                  <div class="filtros-grid">

                    <!-- Filtro de fecha -->
                    <div class="filtro-grupo">
                      <label for="tipo-fecha">Rango de Fecha</label>
                      <select id="tipo-fecha" name="tipo" class="form-control">
                        <option value="todo">Mostrar Todas</option>
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="mes" selected>Mes actual</option>
                        <option value="personalizado">Personalizado</option>
                      </select>

                      <div id="campo-desde" class="form-group d-none" style="margin-top: 10px; margin-bottom: 0;">
                        <label for="fecha-desde">Desde</label>
                        <input type="date" id="fecha-desde" name="fecha_inicio" class="form-control">
                      </div>

                      <div id="campo-hasta" class="form-group d-none" style="margin-top: 10px; margin-bottom: 0;">
                        <label for="fecha-hasta">Hasta</label>
                        <input type="date" id="fecha-hasta" name="fecha_fin" class="form-control">
                      </div>
                    </div>

                    <!-- Filtro por vendedor -->
                    <div class="filtro-grupo">
                      <label for="filtro-vendedor">Vendedor</label>
                      <select id="filtro-vendedor" name="id_vendedor" class="form-control">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($usuarios as $usuario): ?>
                          <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por cliente -->
                    <div class="filtro-grupo">
                      <label for="filtro-cliente">Cliente</label>
                      <select id="filtro-cliente" name="id_cliente" class="form-control">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($clientes as $cliente): ?>
                          <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por producto -->
                    <div class="filtro-grupo">
                      <label for="filtro-producto">Producto</label>
                      <select id="filtro-producto" name="id_producto" class="form-control">
                        <option value="">Mostrar Todos</option>
                        <?php foreach($productos as $producto): ?>
                          <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['descripcion']); ?></option>
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
                    <div class="filtro-grupo">
                      <button type="submit" class="btn btn-primary w-100 btn-filtrar">Aplicar filtros</button>
                    </div>

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

  // Ejecutar por defecto el filtro del mes al cargar la página
  window.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filtro-fechas');
    document.getElementById('tipo-fecha').value = 'mes'; // Establece tipo "mes"
    form.dispatchEvent(new Event('submit')); // Dispara el envío del formulario
  });

  // Mostrar campos personalizados al seleccionar "personalizado"
  document.getElementById('tipo-fecha').addEventListener('change', function () {
    const tipo = this.value;
    document.getElementById('campo-desde').classList.toggle('d-none', tipo !== 'personalizado');
    document.getElementById('campo-hasta').classList.toggle('d-none', tipo !== 'personalizado');
  });

  // Ejecutar al cargar la página para aplicar correctamente la visibilidad
  (function () {
    const tipo = document.getElementById('tipo-fecha').value;
    document.getElementById('campo-desde').classList.toggle('d-none', tipo !== 'personalizado');
    document.getElementById('campo-hasta').classList.toggle('d-none', tipo !== 'personalizado');
  })();

  // Escuchar el envío del formulario
  document.getElementById('filtro-fechas').addEventListener('submit', function (e) {
    e.preventDefault();

    const tipo = document.getElementById('tipo-fecha').value;
    const fechaInicio = document.getElementById('fecha-desde').value;
    const fechaFin = document.getElementById('fecha-hasta').value;

    // Obtener los nuevos filtros
    const idVendedor = document.getElementById('filtro-vendedor').value;
    const idCliente = document.getElementById('filtro-cliente').value;
    const idProducto = document.getElementById('filtro-producto').value;
    const metodoPago = document.getElementById('filtro-metodo-pago').value;

    const formData = new FormData();
    formData.append('tipo', tipo);

    if (tipo === 'personalizado') {
      if (!fechaInicio || !fechaFin) {
        alert("Selecciona ambas fechas para el filtro personalizado.");
        return;
      }
      formData.append('fecha_inicio', fechaInicio);
      formData.append('fecha_fin', fechaFin);
    }

    // Agregar los nuevos filtros al FormData
    if (idVendedor) formData.append('id_vendedor', idVendedor);
    if (idCliente) formData.append('id_cliente', idCliente);
    if (idProducto) formData.append('id_producto', idProducto);
    if (metodoPago) formData.append('metodo_pago', metodoPago);

    // Agregar id_bodega del filtro maestro si existe
    const sucursalMaestra = document.getElementById('sucursalReporteMaestro');
    const idBodega = sucursalMaestra ? sucursalMaestra.value : '';
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