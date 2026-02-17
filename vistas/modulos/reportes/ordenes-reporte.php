<!-- Filtros -->
<div class="row">
  <div class="col-md-12">
    <form id="filtroOrdenesForm" class="form-inline" style="margin-bottom: 20px;">
      <div class="form-group" style="margin-right: 15px;">
        <label for="tipoFechaOrdenes" style="margin-right: 5px;">Período:</label>
        <select class="form-control" id="tipoFechaOrdenes" name="tipo">
          <option value="todo">Todas las órdenes</option>
          <option value="hoy">Hoy</option>
          <option value="ayer">Ayer</option>
          <option value="mes" selected>Este mes</option>
          <option value="personalizado">Personalizado</option>
        </select>
      </div>
      <div class="form-group" id="fechasPersonalizadasOrdenes" style="display: none; margin-right: 15px;">
        <label style="margin-right: 5px;">Desde:</label>
        <input type="date" class="form-control" id="fechaInicioOrdenes" name="fecha_inicio">
        <label style="margin: 0 5px;">Hasta:</label>
        <input type="date" class="form-control" id="fechaFinOrdenes" name="fecha_fin">
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-filter"></i> Filtrar
      </button>
    </form>
  </div>
</div>

<!-- Cajas de resumen -->
<div class="row">
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-blue">
      <div class="inner">
        <h3 id="totalOrdenesPendientes">0</h3>
        <p>Órdenes Pendientes</p>
      </div>
      <div class="icon">
        <i class="fa fa-clock-o"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-purple">
      <div class="inner">
        <h3 id="ordenesManuales">0</h3>
        <p>Órdenes Manuales</p>
      </div>
      <div class="icon">
        <i class="fa fa-user"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-teal">
      <div class="inner">
        <h3 id="ordenesIA">0</h3>
        <p>Órdenes IA</p>
      </div>
      <div class="icon">
        <i class="fa fa-robot"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-green">
      <div class="inner">
        <h3 id="tasaConversionGeneral">0%</h3>
        <p>Tasa de Conversión</p>
      </div>
      <div class="icon">
        <i class="fa fa-check-circle"></i>
      </div>
    </div>
  </div>
</div>

<!-- Gráficas -->
<div class="row">
  <!-- Gráfica de Origen de Órdenes -->
  <div class="col-md-6">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Origen de Órdenes</h3>
      </div>
      <div class="box-body">
        <canvas id="graficoOrigenOrdenes" style="height: 300px;"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfica de Tasa de Conversión -->
  <div class="col-md-6">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Conversión por Origen</h3>
      </div>
      <div class="box-body">
        <div id="graficoConversion" style="height: 300px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de resumen -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-table"></i> Resumen de Conversión</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Origen</th>
              <th>Total Creadas</th>
              <th>Convertidas</th>
              <th>Pendientes</th>
              <th>Tasa Conversión</th>
            </tr>
          </thead>
          <tbody id="tablaResumenOrdenes">
            <tr>
              <td colspan="5" class="text-center">Cargando...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Variables globales para las gráficas
let pieChartOrdenes = null;
let barChartConversion = null;

// Colores para las gráficas
const coloresPie = ['#605ca8', '#39cccc'];
const coloresBar = {
  manuales: '#605ca8',
  ia: '#39cccc'
};

// Mostrar/ocultar fechas personalizadas
document.getElementById('tipoFechaOrdenes').addEventListener('change', function() {
  const fechasPersonalizadas = document.getElementById('fechasPersonalizadasOrdenes');
  if (this.value === 'personalizado') {
    fechasPersonalizadas.style.display = 'inline-block';
  } else {
    fechasPersonalizadas.style.display = 'none';
  }
});

// Función para cargar datos
function cargarDatosOrdenes() {
  const form = document.getElementById('filtroOrdenesForm');
  const formData = new FormData(form);

  fetch('vistas/modulos/reportes/filtro_ordenes.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.error) {
      console.error('Error:', data.error);
      return;
    }

    // Debug info
    console.log('=== DEBUG ÓRDENES ===');
    console.log('Total convertidas histórico:', data.debug?.total_convertidas_historico);
    console.log('Con extra n8n:', data.debug?.con_extra_n8n);
    console.log('Con extra NULL/vacío:', data.debug?.con_extra_null);
    console.log('Convertidas Manuales:', data.conversion.manuales.convertidas);
    console.log('Convertidas IA:', data.conversion.ia.convertidas);
    console.log('=====================');

    // Actualizar cajas de resumen
    document.getElementById('totalOrdenesPendientes').textContent = data.totales.pendientes_total;
    document.getElementById('ordenesManuales').textContent = data.totales.pendientes_manuales;
    document.getElementById('ordenesIA').textContent = data.totales.pendientes_ia;
    document.getElementById('tasaConversionGeneral').textContent = data.totales.tasa_conversion + '%';

    // Actualizar gráfica de origen (dona)
    actualizarGraficoOrigen(data.origen);

    // Actualizar gráfica de conversión (barras)
    actualizarGraficoConversion(data.conversion);

    // Actualizar tabla
    actualizarTablaResumen(data.conversion);
  })
  .catch(error => {
    console.error('Error:', error);
  });
}

// Actualizar gráfica de origen (dona) - Chart.js 1.x
function actualizarGraficoOrigen(datos) {
  const canvas = document.getElementById('graficoOrigenOrdenes');
  const ctx = canvas.getContext('2d');

  // Limpiar canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const pieData = [
    {
      value: datos.manuales,
      color: '#605ca8',
      highlight: '#7d79c4',
      label: 'Manuales'
    },
    {
      value: datos.ia,
      color: '#39cccc',
      highlight: '#5fd9d9',
      label: 'IA (n8n)'
    }
  ];

  const pieOptions = {
    segmentShowStroke: true,
    segmentStrokeColor: '#fff',
    segmentStrokeWidth: 2,
    percentageInnerCutout: 50,
    animationSteps: 100,
    animationEasing: 'easeOutBounce',
    animateRotate: true,
    animateScale: false,
    responsive: true,
    maintainAspectRatio: false,
    tooltipTemplate: '<%=label%>: <%=value%>'
  };

  pieChartOrdenes = new Chart(ctx).Doughnut(pieData, pieOptions);
}

// Actualizar gráfica de conversión (barras) - Morris.js
function actualizarGraficoConversion(datos) {
  // Limpiar contenedor
  document.getElementById('graficoConversion').innerHTML = '';

  barChartConversion = new Morris.Bar({
    element: 'graficoConversion',
    resize: true,
    data: [
      { origen: 'Manuales', creadas: datos.manuales.total, convertidas: datos.manuales.convertidas },
      { origen: 'IA (n8n)', creadas: datos.ia.total, convertidas: datos.ia.convertidas }
    ],
    xkey: 'origen',
    ykeys: ['creadas', 'convertidas'],
    labels: ['Total Creadas', 'Convertidas'],
    barColors: ['#605ca8', '#00a65a'],
    hideHover: 'auto',
    gridLineColor: '#eef0f2',
    xLabelAngle: 0
  });
}

// Actualizar tabla de resumen
function actualizarTablaResumen(datos) {
  const tbody = document.getElementById('tablaResumenOrdenes');

  const tasaManual = datos.manuales.total > 0
    ? ((datos.manuales.convertidas / datos.manuales.total) * 100).toFixed(1)
    : 0;
  const tasaIA = datos.ia.total > 0
    ? ((datos.ia.convertidas / datos.ia.total) * 100).toFixed(1)
    : 0;

  tbody.innerHTML = `
    <tr>
      <td><i class="fa fa-user text-purple"></i> Manuales</td>
      <td>${datos.manuales.total}</td>
      <td>${datos.manuales.convertidas}</td>
      <td>${datos.manuales.pendientes}</td>
      <td>
        <div class="progress progress-sm">
          <div class="progress-bar progress-bar-purple" style="width: ${tasaManual}%"></div>
        </div>
        <span class="badge bg-purple">${tasaManual}%</span>
      </td>
    </tr>
    <tr>
      <td><i class="fa fa-rocket text-aqua"></i> IA (n8n)</td>
      <td>${datos.ia.total}</td>
      <td>${datos.ia.convertidas}</td>
      <td>${datos.ia.pendientes}</td>
      <td>
        <div class="progress progress-sm">
          <div class="progress-bar progress-bar-aqua" style="width: ${tasaIA}%"></div>
        </div>
        <span class="badge bg-aqua">${tasaIA}%</span>
      </td>
    </tr>
    <tr class="info">
      <td><strong>Total</strong></td>
      <td><strong>${datos.manuales.total + datos.ia.total}</strong></td>
      <td><strong>${datos.manuales.convertidas + datos.ia.convertidas}</strong></td>
      <td><strong>${datos.manuales.pendientes + datos.ia.pendientes}</strong></td>
      <td>
        <strong>${datos.tasa_general}%</strong>
      </td>
    </tr>
  `;
}

// Manejar envío del formulario
document.getElementById('filtroOrdenesForm').addEventListener('submit', function(e) {
  e.preventDefault();
  cargarDatosOrdenes();
});

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
  cargarDatosOrdenes();
});
</script>