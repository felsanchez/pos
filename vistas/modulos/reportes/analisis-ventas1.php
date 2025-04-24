<style>
  .d-none {
    display: none !important;
  }
</style>



<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header">
        <!--<h5 class="card-title">Monthly Recap Report</h5>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
          </button>
        </div>
          -->
      </div>
      <!-- /.card-header -->

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

          <!-- /.col -->
          <div class="col-md-4">
            <p class="text-center"><strong>Goal Completion</strong></p>

            <div class="progress-group">
              Add Products to Cart
              <span class="float-end"><b>160</b>/200</span>
              <div class="progress progress-sm">
                <div class="progress-bar text-bg-primary" style="width: 80%"></div>
              </div>
            </div>

            <div class="progress-group">
              Complete Purchase
              <span class="float-end"><b>310</b>/400</span>
              <div class="progress progress-sm">
                <div class="progress-bar text-bg-danger" style="width: 75%"></div>
              </div>
            </div>

            <div class="progress-group">
              <span class="progress-text">Visit Premium Page</span>
              <span class="float-end"><b>480</b>/800</span>
              <div class="progress progress-sm">
                <div class="progress-bar text-bg-success" style="width: 60%"></div>
              </div>
            </div>

            <div class="progress-group">
              Send Inquiries
              <span class="float-end"><b>250</b>/500</span>
              <div class="progress progress-sm">
                <div class="progress-bar text-bg-warning" style="width: 50%"></div>
              </div>
            </div>
          </div>
          <!-- /.col -->
        </div>
      </div>

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


