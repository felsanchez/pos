<!-- Filtros -->
<div class="row">
  <div class="col-md-12">
    <form id="filtroOrdenesForm" class="form-inline" style="margin-bottom: 20px;">
      <div class="form-group" style="margin-right: 15px;">
        <label for="tipoFechaOrdenes" style="margin-right: 5px;">Fecha:</label>
        <select class="form-control" id="tipoFechaOrdenes" name="tipo">
          <option value="todo">Mostrar Todas</option>
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
        <h3 id="totalOrdenesCreadas">0</h3>
        <p>Total Órdenes Creadas</p>
      </div>
      <div class="icon">
        <i class="fa fa-list-alt"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-green">
      <div class="inner">
        <h3 id="totalOrdenesConvertidas">0</h3>
        <p>Total Convertidas</p>
      </div>
      <div class="icon">
        <i class="fa fa-check-circle"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-orange">
      <div class="inner">
        <h3 id="totalOrdenesPendientes">0</h3>
        <p>Pendientes (Sin convertir)</p>
      </div>
      <div class="icon">
        <i class="fa fa-clock-o"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-purple">
      <div class="inner">
        <h3 id="tasaConversionGeneral">0%</h3>
        <p>Tasa de Conversión</p>
      </div>
      <div class="icon">
        <i class="fa fa-percent"></i>
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
        <canvas id="graficoOrigenOrdenes" width="400" height="300" style="max-height: 300px;"></canvas>
        <div id="leyendaOrigenOrdenes" style="text-align:center; margin-top: 10px;"></div>
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
        <canvas id="graficoConversion" style="height: 300px;"></canvas>
        <div id="leyendaConversion" style="text-align:center; margin-top: 10px;"></div>
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

// Destruir instancia Chart.js si ya existe
function destruirChart(chart) {
  if (chart && typeof chart.destroy === 'function') {
    chart.destroy();
  }
}

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

  // Agregar id_bodega del filtro maestro si existe
  const idBodega = $('#sucursalReporteMaestro').val();
  
  if (idBodega && idBodega !== 'todos') {
    formData.append('id_bodega', idBodega);
  }

  console.log("=== ENVIANDO PETICIÓN A filtro_ordenes.php ===");
  console.log("idBodega extraído:", idBodega);
  for (let pair of formData.entries()) {
    console.log(pair[0] + ': ' + pair[1]);
  }

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
    console.log('Manuales en-sitio:', data.debug?.convertidas_manuales_en_sitio);
    console.log('Manuales FE:', data.debug?.convertidas_manuales_fe);
    console.log('IA en-sitio:', data.debug?.convertidas_ia_en_sitio);
    console.log('IA FE:', data.debug?.convertidas_ia_fe);
    console.log('IA fallback (histórico):', data.debug?.convertidas_ia_fallback);
    console.log('Pendientes:', data.debug?.pendientes_total);
    console.log('Total manuales:', data.conversion.manuales.total, '| Convertidas:', data.conversion.manuales.convertidas);
    console.log('Total IA:', data.conversion.ia.total, '| Convertidas:', data.conversion.ia.convertidas);
    console.log('=====================');

    // Calcular totales del JSON
    const totalCreadas = data.conversion.manuales.total + data.conversion.ia.total;
    const totalConvertidas = data.conversion.manuales.convertidas + data.conversion.ia.convertidas;
    const totalPendientes = data.totales.pendientes_total;

    // Actualizar cajas de resumen
    document.getElementById('totalOrdenesCreadas').textContent = totalCreadas;
    document.getElementById('totalOrdenesConvertidas').textContent = totalConvertidas;
    document.getElementById('totalOrdenesPendientes').textContent = totalPendientes;
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

  // Destruir instancia anterior si existe
  destruirChart(pieChartOrdenes);
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

  // Inyectar leyenda manual bajo el canvas
  var leyendaHtml = '<ul style="list-style:none; padding:0; margin:0; display:inline-flex; gap:20px;">';
  pieData.forEach(function(seg) {
    leyendaHtml += '<li style="display:flex; align-items:center; gap:6px; font-size:13px;">' +
      '<span style="display:inline-block; width:14px; height:14px; border-radius:50%; background:' + seg.color + ';"></span>' +
      seg.label +
      '</li>';
  });
  leyendaHtml += '</ul>';
  document.getElementById('leyendaOrigenOrdenes').innerHTML = leyendaHtml;
}

// Actualizar gráfica de conversión (barras) - Chart.js 1.x
function actualizarGraficoConversion(datos) {
  const canvas = document.getElementById('graficoConversion');
  const ctx = canvas.getContext('2d');

  // Destruir instancia anterior si existe
  destruirChart(barChartConversion);
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const barData = {
    labels: ['Manuales', 'IA (n8n)'],
    datasets: [
      {
        label: 'Total Creadas',
        fillColor: 'rgba(96, 92, 168, 0.8)',
        strokeColor: 'rgba(96, 92, 168, 1)',
        highlightFill: 'rgba(96, 92, 168, 1)',
        highlightStroke: 'rgba(96, 92, 168, 1)',
        data: [datos.manuales.total, datos.ia.total]
      },
      {
        label: 'Convertidas',
        fillColor: 'rgba(0, 166, 90, 0.8)',
        strokeColor: 'rgba(0, 166, 90, 1)',
        highlightFill: 'rgba(0, 166, 90, 1)',
        highlightStroke: 'rgba(0, 166, 90, 1)',
        data: [datos.manuales.convertidas, datos.ia.convertidas]
      }
    ]
  };

  const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scaleBeginAtZero: true,
    scaleShowGridLines: true,
    scaleGridLineColor: 'rgba(0,0,0,.05)',
    scaleGridLineWidth: 1,
    barShowStroke: true,
    barStrokeWidth: 2,
    barValueSpacing: 5,
    barDatasetSpacing: 1,
    legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>'
  };

  barChartConversion = new Chart(ctx).Bar(barData, barOptions);

  // Inyectar leyenda manual bajo el canvas
  var leyendaHtml = '<ul style="list-style:none; padding:0; margin:0; display:inline-flex; gap:20px;">';
  barData.datasets.forEach(function(ds) {
    leyendaHtml += '<li style="display:flex; align-items:center; gap:6px; font-size:13px;">' +
      '<span style="display:inline-block; width:14px; height:14px; border-radius:3px; background:' + ds.fillColor + ';"></span>' +
      ds.label +
      '</li>';
  });
  leyendaHtml += '</ul>';
  document.getElementById('leyendaConversion').innerHTML = leyendaHtml;
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

// Cargar datos cuando la sección se expande (AdminLTE collapsed-box)
// Esto evita renderizar en un canvas con dimensiones 0 (sección colapsada)
let ordenesYaCargadas = false;
$('#seccion-analisis-ordenes').on('expanded.boxwidget', function() {
  if (!ordenesYaCargadas) {
    ordenesYaCargadas = true;
    cargarDatosOrdenes();
  } else {
    // Ya cargadas: solo redibujar para que Chart.js ajuste el tamaño
    cargarDatosOrdenes();
  }
});

// Fallback: si la sección ya estuviera expandida al cargar (ej: móvil o cambio de config)
document.addEventListener('DOMContentLoaded', function() {
  var seccion = document.getElementById('seccion-analisis-ordenes');
  if (seccion && !seccion.classList.contains('collapsed-box')) {
    cargarDatosOrdenes();
    ordenesYaCargadas = true;
  }
});
</script>