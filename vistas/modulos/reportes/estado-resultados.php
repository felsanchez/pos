<?php
// Los controladores ya están cargados desde index.php
?>

<style>
  .info-box-number-lg {
    font-size: 24px;
    font-weight: bold;
  }

  .filtro-financiero-container {
    max-width: 100%;
    padding: 15px;
    border-radius: 10px;
    background-color: #f9f9f9;
    margin-bottom: 20px;
  }

  .filtro-financiero label {
    font-weight: 600;
    margin-top: 10px;
    font-size: 12px;
  }

  .filtros-grid-fin {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
  }

  .filtro-grupo-fin {
    min-width: 0;
  }

  .btn-filtrar-fin {
    margin-top: 25px;
  }

  .btn-daterange-fin {
    width: 100%;
    border-radius: 8px;
    border: 1px solid #d2d6de;
    background: #fff;
    color: #555;
    text-align: left;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: border-color 0.2s;
    margin-bottom: 10px;
  }
  .btn-daterange-fin:hover, .btn-daterange-fin:focus {
    border-color: #3c8dbc;
    outline: none;
    color: #333;
  }
  .btn-daterange-fin span { flex: 1; }
</style>

<!-- Filtros del reporte financiero -->
<div class="filtro-financiero-container">
  <form id="filtro-financiero" class="filtro-financiero">
    <div class="filtros-grid-fin">

      <!-- Filtro de fecha -->
      <div class="filtro-grupo-fin">
        <label>Fecha:</label>
        <button type="button" class="btn btn-default btn-daterange-fin" id="daterange-btn-fin">
          <span><i class="fa fa-calendar"></i> Rango de fecha</span>
          <i class="fa fa-caret-down"></i>
        </button>
        <input type="hidden" id="fin-fecha-inicio" name="fecha_inicio">
        <input type="hidden" id="fin-fecha-fin" name="fecha_fin">
        <input type="hidden" id="fin-tipo" name="tipo" value="todo">
      </div>

      <!-- Filtro por categoría de gasto -->
      <div class="filtro-grupo-fin">
        <label for="filtro-categoria-gasto">Categoría de Gastos:</label>
        <select id="filtro-categoria-gasto" name="id_categoria" class="form-control">
          <option value="">Mostrar Todas</option>
          <?php
          $categorias = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
          if ($categorias) {
            foreach ($categorias as $categoria) {
              echo '<option value="' . $categoria['id'] . '">' . htmlspecialchars($categoria['nombre']) . '</option>';
            }
          }
          ?>
        </select>
      </div>

      <!-- Filtro por tipo de ingresos -->
      <div class="filtro-grupo-fin">
        <label for="filtro-tipo-ingreso">Tipo de Ingreso:</label>
        <select id="filtro-tipo-ingreso" name="tipo_ingreso" class="form-control">
          <option value="todas">Mostrar todas (Ventas + FE)</option>
          <option value="ventas">Solo Ventas POS</option>
          <option value="fe">Solo Facturas Electrónicas</option>
        </select>
      </div>

      <!-- Botón de filtrar -->
      <div class="filtro-grupo-fin">
        <button type="submit" class="btn btn-primary w-100 btn-filtrar-fin">Aplicar filtros</button>
      </div>

    </div>
  </form>
</div>

<!-- Cajas Superiores: Ingresos, Gastos, Utilidad -->
<div class="row">

  <!-- Ingresos -->
  <div class="col-md-4 col-sm-6 col-xs-12">
    <div class="info-box bg-green">
      <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Ingresos (Ventas)</span>
        <span class="info-box-number info-box-number-lg" id="total-ingresos">$0</span>
      </div>
    </div>
  </div>

  <!-- Gastos -->
  <div class="col-md-4 col-sm-6 col-xs-12">
    <div class="info-box bg-red">
      <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Gastos</span>
        <span class="info-box-number info-box-number-lg" id="total-gastos">$0</span>
      </div>
    </div>
  </div>

  <!-- Utilidad -->
  <div class="col-md-4 col-sm-6 col-xs-12">
    <div class="info-box bg-aqua" id="box-utilidad">
      <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Utilidad Neta</span>
        <span class="info-box-number info-box-number-lg" id="total-utilidad">$0</span>
      </div>
    </div>
  </div>

</div>

<!-- Gráfica de Evolución Temporal -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Evolución de Ingresos vs Gastos</h3>
      </div>
      <div class="box-body">
        <div id="chart-evolucion"></div>
      </div>
    </div>
  </div>
</div>

<!-- Gráficas inferiores -->
<div class="row">

  <!-- Dona de Gastos por Categoría -->
  <div class="col-md-6">
    <div class="box box-danger">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Gastos por Categoría</h3>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-md-7">
            <div class="chart-responsive">
              <canvas id="pieChartGastos" height="200"></canvas>
            </div>
          </div>
          <div class="col-md-5">
            <ul class="chart-legend clearfix" id="leyenda-gastos">
              <!-- Se llena dinámicamente -->
            </ul>
          </div>
        </div>
      </div>
      <div class="box-footer no-padding">
        <ul class="nav nav-pills nav-stacked" id="lista-gastos-categoria">
          <!-- Se llena dinámicamente -->
        </ul>
      </div>
    </div>
  </div>

  <!-- Resumen de Margen -->
  <div class="col-md-6">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calculator"></i> Resumen Financiero</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <td><strong>Total Ingresos</strong></td>
              <td class="text-right text-green" id="resumen-ingresos">$0</td>
            </tr>
            <tr>
              <td><strong>Total Gastos</strong></td>
              <td class="text-right text-red" id="resumen-gastos">$0</td>
            </tr>
            <tr class="active">
              <td><strong>Utilidad Bruta</strong></td>
              <td class="text-right" id="resumen-utilidad">$0</td>
            </tr>
            <tr>
              <td><strong>Margen de Utilidad</strong></td>
              <td class="text-right" id="resumen-margen">0%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- ApexCharts para la gráfica de evolución -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>

<script>
  // Inicializar gráfica de evolución
  const chartEvolucion = new ApexCharts(document.querySelector('#chart-evolucion'), {
    series: [
      { name: 'Ingresos', data: [] },
      { name: 'Gastos', data: [] }
    ],
    chart: {
      height: 300,
      type: 'area',
      toolbar: { show: false }
    },
    colors: ['#00a65a', '#dd4b39'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { type: 'datetime', categories: [] },
    yaxis: {
      labels: {
        formatter: function (val) {
          return '$' + val.toLocaleString('es-CO');
        }
      }
    },
    tooltip: {
      x: { format: 'dd MMM yyyy' },
      y: {
        formatter: function (val) {
          return '$' + val.toLocaleString('es-CO');
        }
      }
    },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.7,
        opacityTo: 0.3
      }
    }
  });
  chartEvolucion.render();

  // Variables para la gráfica de dona
  let pieChartGastos = null;

  // Colores para categorías
  const coloresCategoria = ['#dd4b39', '#f39c12', '#00c0ef', '#00a65a', '#605ca8', '#d2d6de', '#3c8dbc', '#ff851b', '#39cccc', '#f56954'];

  // Inicializar DateRangePicker
  if (typeof $.fn.daterangepicker !== 'undefined') {
    $('#daterange-btn-fin').daterangepicker({
      ranges: {
        'Todas las fechas': [moment('2000-01-01'), moment()],
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
        'Este mes': [moment().startOf('month'), moment().endOf('month')],
        'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment().subtract(29, 'days'),
      endDate: moment(),
      locale: { cancelLabel: 'Limpiar' }
    }, function (start, end) {
      $('#daterange-btn-fin span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
      $('#fin-fecha-inicio').val(start.format('YYYY-MM-DD'));
      $('#fin-fecha-fin').val(end.format('YYYY-MM-DD'));
      $('#fin-tipo').val('personalizado');
      document.getElementById('filtro-financiero').dispatchEvent(new Event('submit'));
    });

    $('#daterange-btn-fin').on('cancel.daterangepicker', function () {
      $(this).find('span').html('<i class="fa fa-calendar"></i> Rango de fecha');
      $('#fin-fecha-inicio').val('');
      $('#fin-fecha-fin').val('');
      $('#fin-tipo').val('todo');
      document.getElementById('filtro-financiero').dispatchEvent(new Event('submit'));
    });
  }

  // Mostrar campos personalizados
  // (removed - now handled by daterangepicker)

  // Cargar datos al inicio
  window.addEventListener('DOMContentLoaded', function () {
    document.getElementById('filtro-financiero').dispatchEvent(new Event('submit'));
  });

  // Enviar formulario
  document.getElementById('filtro-financiero').addEventListener('submit', function (e) {
    e.preventDefault();

    const tipo = document.getElementById('fin-tipo').value;
    const fechaInicio = document.getElementById('fin-fecha-inicio').value;
    const fechaFin = document.getElementById('fin-fecha-fin').value;
    const idCategoria = document.getElementById('filtro-categoria-gasto').value;
    const tipoIngreso = document.getElementById('filtro-tipo-ingreso').value;

    const formData = new FormData();
    formData.append('tipo', tipo);
    formData.append('tipo_ingreso', tipoIngreso);

    if (tipo === 'personalizado') {
      if (!fechaInicio || !fechaFin) return;
      formData.append('fecha_inicio', fechaInicio);
      formData.append('fecha_fin', fechaFin);
    }

    if (idCategoria) formData.append('id_categoria', idCategoria);

    // Agregar id_bodega del filtro maestro si existe
    const sucursalMaestra = document.getElementById('sucursalReporteMaestro');
    const idBodega = sucursalMaestra ? sucursalMaestra.value : '';
    if (idBodega && idBodega !== 'todos') formData.append('id_bodega', idBodega);

    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";

    fetch(`${rutaBase}/vistas/modulos/reportes/filtro_financiero.php`, {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        actualizarDashboard(data);
      })
      .catch(error => {
        console.error("Error al cargar datos:", error);
      });
  });

  function actualizarDashboard(data) {
    const formatCurrency = (val) => parseFloat(val || 0).toLocaleString('es-CO', {
      style: 'currency',
      currency: 'COP',
      minimumFractionDigits: 0
    });

    // Actualizar cajas superiores
    document.getElementById('total-ingresos').textContent = formatCurrency(data.totales.ingresos);
    document.getElementById('total-gastos').textContent = formatCurrency(data.totales.gastos);
    document.getElementById('total-utilidad').textContent = formatCurrency(data.totales.utilidad);

    // Cambiar color de utilidad según sea positiva o negativa
    const boxUtilidad = document.getElementById('box-utilidad');
    if (data.totales.utilidad >= 0) {
      boxUtilidad.className = 'info-box bg-green';
    } else {
      boxUtilidad.className = 'info-box bg-red';
    }

    // Actualizar resumen
    document.getElementById('resumen-ingresos').textContent = formatCurrency(data.totales.ingresos);
    document.getElementById('resumen-gastos').textContent = formatCurrency(data.totales.gastos);
    document.getElementById('resumen-utilidad').textContent = formatCurrency(data.totales.utilidad);

    const margen = data.totales.ingresos > 0
      ? ((data.totales.utilidad / data.totales.ingresos) * 100).toFixed(1)
      : 0;
    document.getElementById('resumen-margen').textContent = margen + '%';
    document.getElementById('resumen-margen').className = margen >= 0 ? 'text-right text-green' : 'text-right text-red';

    // Actualizar gráfica de evolución
    const fechas = data.evolucion.map(item => item.fecha);
    const ingresos = data.evolucion.map(item => item.ingresos);
    const gastos = data.evolucion.map(item => item.gastos);

    chartEvolucion.updateOptions({
      xaxis: { categories: fechas },
      series: [
        { name: 'Ingresos', data: ingresos },
        { name: 'Gastos', data: gastos }
      ]
    });

    // Actualizar gráfica de dona de gastos por categoría
    actualizarDonaGastos(data.gastos_categoria);
  }

  function actualizarDonaGastos(gastosPorCategoria) {
    const leyenda = document.getElementById('leyenda-gastos');
    const lista = document.getElementById('lista-gastos-categoria');

    leyenda.innerHTML = '';
    lista.innerHTML = '';

    if (!gastosPorCategoria || gastosPorCategoria.length === 0) {
      leyenda.innerHTML = '<li>Sin gastos registrados</li>';
      return;
    }

    const totalGastos = gastosPorCategoria.reduce((sum, cat) => sum + parseFloat(cat.total), 0);

    // Crear leyenda y lista
    gastosPorCategoria.slice(0, 10).forEach((cat, i) => {
      const color = cat.color || coloresCategoria[i % coloresCategoria.length];
      const porcentaje = totalGastos > 0 ? Math.round((cat.total / totalGastos) * 100) : 0;

      leyenda.innerHTML += `<li><i class="fa fa-circle-o" style="color:${color}"></i> ${cat.nombre}</li>`;

      if (i < 5) {
        lista.innerHTML += `
        <li>
          <a>
            <i class="fa fa-tag" style="color:${color}; margin-right:10px;"></i>
            ${cat.nombre}
            <span class="pull-right" style="color:${color}">
              <strong>${porcentaje}%</strong>
              <small>($${parseFloat(cat.total).toLocaleString('es-CO')})</small>
            </span>
          </a>
        </li>
      `;
      }
    });

    // Actualizar gráfica de dona
    const ctx = document.getElementById('pieChartGastos').getContext('2d');

    if (pieChartGastos) {
      pieChartGastos.destroy();
    }

    const pieData = gastosPorCategoria.slice(0, 10).map((cat, i) => ({
      value: parseFloat(cat.total),
      color: cat.color || coloresCategoria[i % coloresCategoria.length],
      highlight: cat.color || coloresCategoria[i % coloresCategoria.length],
      label: cat.nombre
    }));

    pieChartGastos = new Chart(ctx).Doughnut(pieData, {
      segmentShowStroke: true,
      segmentStrokeColor: '#fff',
      segmentStrokeWidth: 1,
      percentageInnerCutout: 50,
      animationSteps: 100,
      animationEasing: 'easeOutBounce',
      animateRotate: true,
      animateScale: false,
      responsive: true,
      maintainAspectRatio: false
    });
  }
</script>