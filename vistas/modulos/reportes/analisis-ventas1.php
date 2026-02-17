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
      padding: 15px;
      border-radius: 10px;
      background-color: #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .formulario-filtros label {
      font-weight: 600;
      margin-top: 10px;
      font-size: 12px;
    }
    .formulario-filtros select,
    .formulario-filtros input[type="date"] {
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .d-none {
      display: none !important;
    }
    .filtros-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
    }
    .filtro-grupo {
      min-width: 0;
    }
    .btn-filtrar {
      margin-top: 25px;
    }
  </style>

<style>
  .d-none {
    display: none !important;
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
                      <label for="tipo-fecha">Filtrar por fecha</label>
                      <select id="tipo-fecha" name="tipo" class="form-control">
                        <option value="todo">Todas las ventas</option>
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="mes" selected>Mes actual</option>
                        <option value="personalizado">Personalizado</option>
                      </select>

                      <div id="campo-desde" class="form-group d-none">
                        <label for="fecha-desde">Desde</label>
                        <input type="date" id="fecha-desde" name="fecha_inicio" class="form-control">
                      </div>

                      <div id="campo-hasta" class="form-group d-none">
                        <label for="fecha-hasta">Hasta</label>
                        <input type="date" id="fecha-hasta" name="fecha_fin" class="form-control">
                      </div>
                    </div>

                    <!-- Filtro por vendedor -->
                    <div class="filtro-grupo">
                      <label for="filtro-vendedor">Vendedor</label>
                      <select id="filtro-vendedor" name="id_vendedor" class="form-control">
                        <option value="">Todos los vendedores</option>
                        <?php foreach($usuarios as $usuario): ?>
                          <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por cliente -->
                    <div class="filtro-grupo">
                      <label for="filtro-cliente">Cliente</label>
                      <select id="filtro-cliente" name="id_cliente" class="form-control">
                        <option value="">Todos los clientes</option>
                        <?php foreach($clientes as $cliente): ?>
                          <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por producto -->
                    <div class="filtro-grupo">
                      <label for="filtro-producto">Producto</label>
                      <select id="filtro-producto" name="id_producto" class="form-control">
                        <option value="">Todos los productos</option>
                        <?php foreach($productos as $producto): ?>
                          <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['descripcion']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Filtro por método de pago -->
                    <div class="filtro-grupo">
                      <label for="filtro-metodo-pago">Método de pago</label>
                      <select id="filtro-metodo-pago" name="metodo_pago" class="form-control">
                        <option value="">Todos los métodos</option>
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

        <div class="row">  
          <!--<div class="col-md-8">-->
            <p class="text-center">
              <strong>Ventas</strong>
            </p>
              <!--VALOR VENTAS -->
              <div class="text-center mb-3">
                <h5>Total de ventas en el periodo seleccionado:</h5>
                <h3 id="total-ventas" class="text-success">$0</h3>
              </div>

            <div id="sales-chart"></div>         
          <!--</div>-->               
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
      height: 180,
      type: 'area',
      toolbar: { show: false }
    },
    colors: ['#0d6efd'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth' },
    xaxis: { type: 'datetime', categories: [] },
    tooltip: { x: { format: 'dd MMM yyyy' } }
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


    //fetch('/pos/vistas/modulos/reportes/filtro_ventas.php', {
    let rutaBase = window.location.hostname.includes("localhost")
      ? "/pos"
      : ""; // en producción no va "/pos"

    fetch(`${rutaBase}/vistas/modulos/reportes/filtro_ventas.php`, {

      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      const datos = data.datos;
      const total = data.total;

      // Mostrar el total en el HTML
      document.getElementById('total-ventas').textContent = total.toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP'
      });

      // Extraer fechas y totales para la gráfica
      const fechas = datos.map(item => item.fecha);
      const totales = datos.map(item => item.total_ventas);

      // Actualizar el gráfico
      salesChart.updateOptions({
        xaxis: { categories: fechas },
        series: [{
          name: "Ventas",
          data: totales
        }]
      });

    })
    .catch(error => {
      console.error("Error al cargar datos:", error);
    });
  }); // <- cierre correcto del addEventListener
</script>