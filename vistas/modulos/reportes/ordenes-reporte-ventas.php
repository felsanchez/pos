<!-- Filtros -->
<style>
  .btn-daterange-ordv {
    border-radius: 8px;
    border: 1px solid #d2d6de;
    background: #fff;
    color: #555;
    padding: 7px 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: border-color 0.2s;
  }
  .btn-daterange-ordv:hover, .btn-daterange-ordv:focus {
    border-color: #3c8dbc;
    outline: none;
    color: #333;
  }
</style>
<div class="row">
  <div class="col-md-12">
    <form id="filtroOrdenesVentasForm" style="margin-bottom: 20px; display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span><b>Fecha:</b></span>
        <button type="button" class="btn btn-default btn-daterange-ordv" id="daterange-btn-ordv">
          <i class="fa fa-calendar"></i>
          <span>Rango de fecha</span>
          <i class="fa fa-caret-down"></i>
        </button>
        <input type="hidden" id="ordv-fecha-inicio" name="fecha_inicio">
        <input type="hidden" id="ordv-fecha-fin" name="fecha_fin">
        <input type="hidden" id="ordv-tipo" name="tipo" value="mes">
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
        <h3 id="totalOrdenesCreadasVentas">0</h3>
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
        <h3 id="totalOrdenesConvertidasVentas">0</h3>
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
        <h3 id="totalOrdenesPendientesVentas">0</h3>
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
        <h3 id="tasaConversionGeneralVentas">0%</h3>
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
        <canvas id="graficoOrigenOrdenesVentas" width="400" height="300" style="max-height: 300px;"></canvas>
        <div id="leyendaOrigenOrdenesVentas" style="text-align:center; margin-top: 10px;"></div>
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
        <canvas id="graficoConversionVentas" style="height: 300px;"></canvas>
        <div id="leyendaConversionVentas" style="text-align:center; margin-top: 10px;"></div>
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
          <tbody id="tablaResumenOrdenesVentas">
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
let pieChartOrdenesVentas = null;
let barChartConversionVentas = null;

// Inicializar DateRangePicker en Análisis de Órdenes de Venta
$(document).ready(function () {
  var ordvFechaInicio = moment().startOf('month').format('YYYY-MM-DD');
  var ordvFechaFin = moment().endOf('month').format('YYYY-MM-DD');
  $('#ordv-fecha-inicio').val(ordvFechaInicio);
  $('#ordv-fecha-fin').val(ordvFechaFin);
  $('#ordv-tipo').val('personalizado');
  $('#daterange-btn-ordv span').text(moment().startOf('month').format('MMMM D, YYYY') + ' - ' + moment().endOf('month').format('MMMM D, YYYY'));

  if (typeof $.fn.daterangepicker !== 'undefined') {
    $('#daterange-btn-ordv').daterangepicker({
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
      $('#daterange-btn-ordv span').text(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
      $('#ordv-fecha-inicio').val(start.format('YYYY-MM-DD'));
      $('#ordv-fecha-fin').val(end.format('YYYY-MM-DD'));
      $('#ordv-tipo').val('personalizado');
    });

    $('#daterange-btn-ordv').on('cancel.daterangepicker', function () {
      $(this).find('span').text('Rango de fecha');
      $('#ordv-fecha-inicio').val('');
      $('#ordv-fecha-fin').val('');
      $('#ordv-tipo').val('todo');
    });
  }
});

// Mostrar/ocultar fechas personalizadas - obsoleto, mantenido por compatibilidad

// Función para cargar datos
function cargarDatosOrdenesVentas() {
  const tipo = document.getElementById('ordv-tipo').value;
  const fechaInicio = document.getElementById('ordv-fecha-inicio').value;
  const fechaFin = document.getElementById('ordv-fecha-fin').value;

  const formData = new FormData();
  formData.append('tipo', tipo);
  if (tipo === 'personalizado' && fechaInicio && fechaFin) {
    formData.append('fecha_inicio', fechaInicio);
    formData.append('fecha_fin', fechaFin);
  }

  // Agregar id_bodega del filtro maestro si existe
  const idBodega = $('#sucursalReporteMaestro').val();
  
  if (idBodega && idBodega !== 'todos') {
    formData.append('id_bodega', idBodega);
  }

  fetch('vistas/modulos/reportes/filtro_ordenes_ventas.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.error) {
      console.error('Error:', data.error);
      return;
    }

    // Calcular totales del JSON
    const totalCreadas = data.conversion.manuales.total + data.conversion.ia.total;
    const totalConvertidas = data.conversion.manuales.convertidas + data.conversion.ia.convertidas;
    const totalPendientes = data.totales.pendientes_total;

    // Actualizar cajas de resumen
    document.getElementById('totalOrdenesCreadasVentas').textContent = totalCreadas;
    document.getElementById('totalOrdenesConvertidasVentas').textContent = totalConvertidas;
    document.getElementById('totalOrdenesPendientesVentas').textContent = totalPendientes;
    document.getElementById('tasaConversionGeneralVentas').textContent = data.totales.tasa_conversion + '%';

    // Actualizar gráfica de origen (dona)
    actualizarGraficoOrigenVentas(data.origen);

    // Actualizar gráfica de conversión (barras)
    actualizarGraficoConversionVentas(data.conversion);

    // Actualizar tabla
    actualizarTablaResumenVentas(data.conversion);
  })
  .catch(error => {
    console.error('Error:', error);
  });
}

// Actualizar gráfica de origen (dona) - Chart.js 1.x
function actualizarGraficoOrigenVentas(datos) {
  const canvas = document.getElementById('graficoOrigenOrdenesVentas');
  const ctx = canvas.getContext('2d');

  // Destruir instancia anterior si existe
  if (pieChartOrdenesVentas && typeof pieChartOrdenesVentas.destroy === 'function') {
    pieChartOrdenesVentas.destroy();
  }
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const pieData = [
    {
      value: datos.manuales,
      color: '#605ca8',
      highlight: '#7d79c4',
      label: 'Órdenes'
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

  pieChartOrdenesVentas = new Chart(ctx).Doughnut(pieData, pieOptions);

  // Inyectar leyenda manual bajo el canvas
  var leyendaHtml = '<ul style="list-style:none; padding:0; margin:0; display:inline-flex; gap:20px;">';
  pieData.forEach(function(seg) {
    leyendaHtml += '<li style="display:flex; align-items:center; gap:6px; font-size:13px;">' +
      '<span style="display:inline-block; width:14px; height:14px; border-radius:50%; background:' + seg.color + ';"></span>' +
      seg.label +
      '</li>';
  });
  leyendaHtml += '</ul>';
  document.getElementById('leyendaOrigenOrdenesVentas').innerHTML = leyendaHtml;
}

// Actualizar gráfica de conversión (barras) - Chart.js 1.x
function actualizarGraficoConversionVentas(datos) {
  const canvas = document.getElementById('graficoConversionVentas');
  const ctx = canvas.getContext('2d');

  // Destruir instancia anterior si existe
  if (barChartConversionVentas && typeof barChartConversionVentas.destroy === 'function') {
    barChartConversionVentas.destroy();
  }
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const barData = {
    labels: ['Órdenes'],
    datasets: [
      {
        label: 'Total Creadas',
        fillColor: 'rgba(96, 92, 168, 0.8)',
        strokeColor: 'rgba(96, 92, 168, 1)',
        highlightFill: 'rgba(96, 92, 168, 1)',
        highlightStroke: 'rgba(96, 92, 168, 1)',
        data: [datos.manuales.total]
      },
      {
        label: 'Convertidas',
        fillColor: 'rgba(0, 166, 90, 0.8)',
        strokeColor: 'rgba(0, 166, 90, 1)',
        highlightFill: 'rgba(0, 166, 90, 1)',
        highlightStroke: 'rgba(0, 166, 90, 1)',
        data: [datos.manuales.convertidas]
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

  barChartConversionVentas = new Chart(ctx).Bar(barData, barOptions);

  // Inyectar leyenda manual bajo el canvas
  var leyendaHtml = '<ul style="list-style:none; padding:0; margin:0; display:inline-flex; gap:20px;">';
  barData.datasets.forEach(function(ds) {
    leyendaHtml += '<li style="display:flex; align-items:center; gap:6px; font-size:13px;">' +
      '<span style="display:inline-block; width:14px; height:14px; border-radius:3px; background:' + ds.fillColor + ';"></span>' +
      ds.label +
      '</li>';
  });
  leyendaHtml += '</ul>';
  document.getElementById('leyendaConversionVentas').innerHTML = leyendaHtml;
}

// Actualizar tabla de resumen
function actualizarTablaResumenVentas(datos) {
  const tbody = document.getElementById('tablaResumenOrdenesVentas');

  const tasaManual = datos.manuales.total > 0
    ? ((datos.manuales.convertidas / datos.manuales.total) * 100).toFixed(1)
    : 0;
  const tasaIA = datos.ia.total > 0
    ? ((datos.ia.convertidas / datos.ia.total) * 100).toFixed(1)
    : 0;

  tbody.innerHTML = `
    <tr>
      <td><i class="fa fa-user text-purple"></i> Órdenes</td>
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
    <tr class="info">
      <td><strong>Total</strong></td>
      <td><strong>${datos.manuales.total}</strong></td>
      <td><strong>${datos.manuales.convertidas}</strong></td>
      <td><strong>${datos.manuales.pendientes}</strong></td>
      <td>
        <strong>${datos.tasa_general}%</strong>
      </td>
    </tr>
  `;
}

// Manejar envío del formulario
document.getElementById('filtroOrdenesVentasForm').addEventListener('submit', function(e) {
  e.preventDefault();
  cargarDatosOrdenesVentas();
});

// Cargar datos cuando la sección se expande (AdminLTE collapsed-box)
let ordenesVentasYaCargadas = false;
$('#seccion-analisis-ordenes-venta').on('expanded.boxwidget', function() {
  if (!ordenesVentasYaCargadas) {
    ordenesVentasYaCargadas = true;
    cargarDatosOrdenesVentas();
  } else {
    // Ya cargadas: solo redibujar para que Chart.js ajuste el tamaño
    cargarDatosOrdenesVentas();
  }
});

// Fallback: si la sección ya estuviera expandida al cargar
document.addEventListener('DOMContentLoaded', function() {
  var seccion = document.getElementById('seccion-analisis-ordenes-venta');
  if (seccion && !seccion.classList.contains('collapsed-box')) {
    cargarDatosOrdenesVentas();
    ordenesVentasYaCargadas = true;
  }
});
</script>