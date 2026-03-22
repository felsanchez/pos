<?php
// Los controladores ya están cargados desde index.php
?>

<!-- Filtros del reporte financiero de facturación -->
<div class="filtro-financiero-container">
  <form id="filtro-financiero-fact" class="filtro-financiero">
    <div class="filtros-grid-fin">

      <!-- Filtro de fecha -->
      <div class="filtro-grupo-fin">
        <label for="tipo-fecha-fin-fact">Filtrar por fecha</label>
        <select id="tipo-fecha-fin-fact" name="tipo" class="form-control">
          <option value="todo" selected>Todas las fechas</option>
          <option value="hoy">Hoy</option>
          <option value="ayer">Ayer</option>
          <option value="mes">Mes actual</option>
          <option value="personalizado">Personalizado</option>
        </select>

        <div id="campo-desde-fin-fact" class="form-group" style="display:none;">
          <label for="fecha-desde-fin-fact">Desde</label>
          <input type="date" id="fecha-desde-fin-fact" name="fecha_inicio" class="form-control">
        </div>

        <div id="campo-hasta-fin-fact" class="form-group" style="display:none;">
          <label for="fecha-hasta-fin-fact">Hasta</label>
          <input type="date" id="fecha-hasta-fin-fact" name="fecha_fin" class="form-control">
        </div>
      </div>

      <!-- Filtro por categoría de gasto -->
      <div class="filtro-grupo-fin">
        <label for="filtro-categoria-gasto-fact">Categoría de gasto</label>
        <select id="filtro-categoria-gasto-fact" name="id_categoria" class="form-control">
          <option value="">Todas las categorías</option>
          <?php
            $categoriasF = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
            if ($categoriasF) {
              foreach ($categoriasF as $catF) {
                echo '<option value="'.$catF['id'].'">'.htmlspecialchars($catF['nombre']).'</option>';
              }
            }
          ?>
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
      <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Ingresos (Fact. Electrónica)</span>
        <span class="info-box-number info-box-number-lg" id="total-ingresos-fact">$0</span>
      </div>
    </div>
  </div>

  <!-- Gastos -->
  <div class="col-md-4 col-sm-6 col-xs-12">
    <div class="info-box bg-red">
      <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Gastos</span>
        <span class="info-box-number info-box-number-lg" id="total-gastos-fact">$0</span>
      </div>
    </div>
  </div>

  <!-- Utilidad -->
  <div class="col-md-4 col-sm-6 col-xs-12">
    <div class="info-box bg-aqua" id="box-utilidad-fact">
      <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Utilidad Neta</span>
        <span class="info-box-number info-box-number-lg" id="total-utilidad-fact">$0</span>
      </div>
    </div>
  </div>

</div>

<!-- Gráfica de Evolución Temporal -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Evolución: Fact. Electrónica vs Gastos</h3>
      </div>
      <div class="box-body">
        <div id="chart-evolucion-fact"></div>
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
              <canvas id="pieChartGastosFact" height="200"></canvas>
            </div>
          </div>
          <div class="col-md-5">
            <ul class="chart-legend clearfix" id="leyenda-gastos-fact">
              <!-- Se llena dinámicamente -->
            </ul>
          </div>
        </div>
      </div>
      <div class="box-footer no-padding">
        <ul class="nav nav-pills nav-stacked" id="lista-gastos-categoria-fact">
          <!-- Se llena dinámicamente -->
        </ul>
      </div>
    </div>
  </div>

  <!-- Resumen de Margen -->
  <div class="col-md-6">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calculator"></i> Resumen Financiero (Fact. Electrónica)</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered">
          <tbody>
            <tr>
              <td><strong>Total Ingresos (Fact. Electrónica)</strong></td>
              <td class="text-right text-green" id="resumen-ingresos-fact">$0</td>
            </tr>
            <tr>
              <td><strong>Total Gastos</strong></td>
              <td class="text-right text-red" id="resumen-gastos-fact">$0</td>
            </tr>
            <tr class="active">
              <td><strong>Utilidad Bruta</strong></td>
              <td class="text-right" id="resumen-utilidad-fact">$0</td>
            </tr>
            <tr>
              <td><strong>Margen de Utilidad</strong></td>
              <td class="text-right" id="resumen-margen-fact">0%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script>
(function() {
  // Variables privadas al scope para no contaminar el reporte anterior
  const coloresFactCategoria = ['#dd4b39', '#f39c12', '#00c0ef', '#00a65a', '#605ca8', '#d2d6de', '#3c8dbc', '#ff851b', '#39cccc', '#f56954'];
  let pieChartGastosFact = null;

  const chartEvolucionFact = new ApexCharts(document.querySelector('#chart-evolucion-fact'), {
    series: [
      { name: 'Ingresos (Fact. Electrónica)', data: [] },
      { name: 'Gastos', data: [] }
    ],
    chart: {
      height: 300,
      type: 'area',
      toolbar: { show: false }
    },
    colors: ['#3c8dbc', '#dd4b39'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { type: 'datetime', categories: [] },
    yaxis: {
      labels: {
        formatter: function(val) {
          return '$' + val.toLocaleString('es-CO');
        }
      }
    },
    tooltip: {
      x: { format: 'dd MMM yyyy' },
      y: {
        formatter: function(val) {
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
  chartEvolucionFact.render();

  // Mostrar campos personalizados
  document.getElementById('tipo-fecha-fin-fact').addEventListener('change', function() {
    const tipo = this.value;
    document.getElementById('campo-desde-fin-fact').style.display = tipo === 'personalizado' ? 'block' : 'none';
    document.getElementById('campo-hasta-fin-fact').style.display = tipo === 'personalizado' ? 'block' : 'none';
  });

  // Enviar formulario
  document.getElementById('filtro-financiero-fact').addEventListener('submit', function(e) {
    e.preventDefault();

    const tipo = document.getElementById('tipo-fecha-fin-fact').value;
    const fechaInicio = document.getElementById('fecha-desde-fin-fact').value;
    const fechaFin = document.getElementById('fecha-hasta-fin-fact').value;
    const idCategoria = document.getElementById('filtro-categoria-gasto-fact').value;

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

    if (idCategoria) formData.append('id_categoria', idCategoria);

    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";

    fetch(`${rutaBase}/vistas/modulos/reportes/filtro_financiero_facturacion.php`, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      actualizarDashboardFact(data);
    })
    .catch(error => {
      console.error("Error al cargar datos (Fact. Electrónica):", error);
    });
  });

  function actualizarDashboardFact(data) {
    const formatCurrency = (val) => parseFloat(val || 0).toLocaleString('es-CO', {
      style: 'currency',
      currency: 'COP',
      minimumFractionDigits: 0
    });

    // Actualizar cajas superiores
    document.getElementById('total-ingresos-fact').textContent = formatCurrency(data.totales.ingresos);
    document.getElementById('total-gastos-fact').textContent = formatCurrency(data.totales.gastos);
    document.getElementById('total-utilidad-fact').textContent = formatCurrency(data.totales.utilidad);

    // Cambiar color de utilidad según sea positiva o negativa
    const boxUtilidad = document.getElementById('box-utilidad-fact');
    if (data.totales.utilidad >= 0) {
      boxUtilidad.className = 'info-box bg-green';
    } else {
      boxUtilidad.className = 'info-box bg-red';
    }

    // Actualizar resumen
    document.getElementById('resumen-ingresos-fact').textContent = formatCurrency(data.totales.ingresos);
    document.getElementById('resumen-gastos-fact').textContent = formatCurrency(data.totales.gastos);
    document.getElementById('resumen-utilidad-fact').textContent = formatCurrency(data.totales.utilidad);

    const margen = data.totales.ingresos > 0
      ? ((data.totales.utilidad / data.totales.ingresos) * 100).toFixed(1)
      : 0;
    document.getElementById('resumen-margen-fact').textContent = margen + '%';
    document.getElementById('resumen-margen-fact').className = margen >= 0 ? 'text-right text-green' : 'text-right text-red';

    // Actualizar gráfica de evolución
    const fechas = data.evolucion.map(item => item.fecha);
    const ingresos = data.evolucion.map(item => item.ingresos);
    const gastos = data.evolucion.map(item => item.gastos);

    chartEvolucionFact.updateOptions({
      xaxis: { categories: fechas },
      series: [
        { name: 'Ingresos (Fact. Electrónica)', data: ingresos },
        { name: 'Gastos', data: gastos }
      ]
    });

    // Actualizar gráfica de dona de gastos por categoría
    actualizarDonaGastosFact(data.gastos_categoria);
  }

  function actualizarDonaGastosFact(gastosPorCategoria) {
    const leyenda = document.getElementById('leyenda-gastos-fact');
    const lista = document.getElementById('lista-gastos-categoria-fact');

    leyenda.innerHTML = '';
    lista.innerHTML = '';

    if (!gastosPorCategoria || gastosPorCategoria.length === 0) {
      leyenda.innerHTML = '<li>Sin gastos registrados</li>';
      return;
    }

    const totalGastos = gastosPorCategoria.reduce((sum, cat) => sum + parseFloat(cat.total), 0);

    gastosPorCategoria.slice(0, 10).forEach((cat, i) => {
      const color = cat.color || coloresFactCategoria[i % coloresFactCategoria.length];
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
    const ctx = document.getElementById('pieChartGastosFact').getContext('2d');

    if (pieChartGastosFact) {
      pieChartGastosFact.destroy();
    }

    const pieData = gastosPorCategoria.slice(0, 10).map((cat, i) => ({
      value: parseFloat(cat.total),
      color: cat.color || coloresFactCategoria[i % coloresFactCategoria.length],
      highlight: cat.color || coloresFactCategoria[i % coloresFactCategoria.length],
      label: cat.nombre
    }));

    pieChartGastosFact = new Chart(ctx).Doughnut(pieData, {
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

  // Disparar el formulario para la carga inicial (funciona si DOM ya está listo)
  setTimeout(function() {
    var form = document.getElementById('filtro-financiero-fact');
    if (form) form.dispatchEvent(new Event('submit'));
  }, 100);
})();
</script>
