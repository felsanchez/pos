<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!--Metodos de pago-->
<?php
require_once __DIR__ . "/../../../modelos/conexion.php";

$gruposPago = [
  "Efectivo" => "Efectivo",
  "TD" => "TD-%",
  "TC" => "TC-%",
  "Transferencia" => "Transferencia%",
  "Cheque" => "Cheque%"
];

$datosMetodoPago = [];

foreach ($gruposPago as $nombre => $patron) {
    $stmt = Conexion::conectar()->prepare("
        SELECT SUM(total) as total 
        FROM ventas 
        WHERE metodo_pago LIKE :patron 
        AND estado = 'venta'
    ");
    $stmt->bindParam(":patron", $patron, PDO::PARAM_STR);
    $stmt->execute();
    $resultado = $stmt->fetch();
    $datosMetodoPago[$nombre] = $resultado["total"] ?? 0;
}
?>



<style>
  .d-none {
    display: none !important;
  }
</style>


<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      
      <div class="card-body">
        <div class="row">

          <div class="col-md-8">
            <p class="text-center">
              <strong>Ventas</strong>
            </p>
              <!--VALOR VENTAS -->
              <div class="text-center mb-3">
                <h5>Total de ventas en el periodo seleccionado:</h5>
                <h3 id="total-ventas" class="text-success">$0</h3>
              </div>

            <div id="sales-chart"></div>
          </div>

          <!-- /.col **************************************************************-->
          <!--Metodos de pago-->

          <div class="col-md-4">
            <p class="text-center"><strong>Formas de Pago</strong></p>

            <?php
            $maxTotal = max($datosMetodoPago) ?: 1; // Evita división por cero

            $colores = [
              "Efectivo" => "primary",
              "TD" => "info",
              "TC" => "success",
              "Transferencia" => "warning",
              "Cheque" => "danger"
            ];

            foreach ($datosMetodoPago as $metodo => $total) {
              $porcentaje = round(($total / $maxTotal) * 100);
              $nombre = match ($metodo) {
                "TD" => "Tarjeta Débito",
                "TC" => "Tarjeta Crédito",
                default => $metodo
              };
              echo '
                <div class="progress-group">
                  <span class="progress-text">' . $nombre . '</span>
                  <span class="float-end"><b>' . number_format($total, 0) . '</b>/' . number_format($maxTotal, 0) . '</span>
                  <div class="progress progress-sm">
                    <div class="progress-bar text-bg-' . $colores[$metodo] . '" style="width: ' . $porcentaje . '%"></div>
                  </div>
                </div>
              ';
            }
            ?>
        </div>

      </div>
      
    </div>
  </div>

      <!-- /.col **************************************************************************-->
       
      <div class="card-footer">
        <div class="row">
          <div class="col-md-3 col-6">
            <div class="text-center border-end">
              <span class="text-success">
                <i class="bi bi-caret-up-fill"></i> 17%
              </span>
              <h5 class="fw-bold mb-0">$35,210.43</h5>
              <span class="text-uppercase">TOTAL REVENUE</span>
            </div>
          </div>

          <div class="col-md-3 col-6">
            <div class="text-center border-end">
              <span class="text-info"><i class="bi bi-caret-left-fill"></i> 0%</span>
              <h5 class="fw-bold mb-0">$10,390.90</h5>
              <span class="text-uppercase">TOTAL COST</span>
            </div>
          </div>

          <div class="col-md-3 col-6">
            <div class="text-center border-end">
              <span class="text-success"><i class="bi bi-caret-up-fill"></i> 20%</span>
              <h5 class="fw-bold mb-0">$24,813.53</h5>
              <span class="text-uppercase">TOTAL PROFIT</span>
            </div>
          </div>

          <div class="col-md-3 col-6">
            <div class="text-center">
              <span class="text-danger"><i class="bi bi-caret-down-fill"></i> 18%</span>
              <h5 class="fw-bold mb-0">1200</h5>
              <span class="text-uppercase">GOAL COMPLETIONS</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card-footer -->
    </div>
  </div>
</div>

<!-- ApexCharts -->
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

  
    fetch('/pos/vistas/modulos/reportes/filtro_ventas.php', {
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


