<style>
  .excelbtn {
    z-index: 9999;
  }

  .filtro-excel-container {
    padding: 15px;
    border-radius: 10px;
    background-color: #f9f9f9;
    margin-bottom: 15px;
  }

  .filtro-excel-container label {
    font-weight: 600;
    margin-top: 10px;
  }

  .filtro-excel-container select,
  .filtro-excel-container input[type="date"] {
    border-radius: 8px;
    margin-bottom: 10px;
  }

  /* Toast notification */
  .toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #00a65a;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideInRight 0.3s ease-out;
    font-size: 15px;
  }

  .toast-notification.toast-hide {
    animation: slideOutRight 0.3s ease-out;
  }

  @keyframes slideInRight {
    from {
      transform: translateX(400px);
      opacity: 0;
    }

    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }

    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }

  /* Colapsar secciones en móvil por defecto */
  @media (max-width: 767px) {
    .box.collapsed-box-mobile .box-body {
      display: none;
    }

    .box.collapsed-box-mobile .fa-minus:before {
      content: "\f067";
      /* Icono de plus */
    }

    .box:not(.collapsed-box-mobile) .fa-minus:before {
      content: "\f068";
      /* Icono de minus */
    }
  }
</style>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Reportes de ventas
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Reportes de ventas</li>
    </ol>

  </section>

  <section class="content">

    <!-- SECCIÓN 1: ANÁLISIS DE VENTAS -->
    <div class="box box-info" id="seccion-analisis-ventas">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Análisis de Ventas</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-success btn-sm" style="margin-right: 5px;" data-toggle="modal"
            data-target="#modalDescargarExcel">
            <i class="fa fa-file-excel-o"></i> Excel
          </button>
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <!-- Análisis de ventas -->
        <div id="contenedor-barras-formas-pago">
          <div class="col-12 col-md-12">
            <?php include "reportes/analisis-ventas1.php"; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- SECCIÓN 2: GRÁFICOS DE RENDIMIENTO -->
    <div class="box box-success" id="seccion-graficos-rendimiento">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Gráficos de Rendimiento</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">

        <div class="row">

          <div class="col-md-6 col-xs-12">
            <?php
            include "reportes/metodos-pago-mas-usados.php";
            ?>
          </div>

          <div class="col-md-6 col-xs-12">
            <?php
            include "reportes/productos-mas-vendidos.php";
            ?>
          </div>

          <div class="col-md-6 col-xs-12">
            <?php
            include "reportes/vendedores.php";
            ?>
          </div>

          <div class="col-md-6 col-xs-12">
            <?php
            include "reportes/compradores.php";
            ?>
          </div>

        </div>

      </div>

    </div>

    <!-- SECCIÓN 3: REPORTE FINANCIERO -->
    <div class="box box-primary" id="seccion-estado-resultados">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-balance-scale"></i> Estado de Resultados</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <?php include "reportes/estado-resultados.php"; ?>
      </div>
    </div>

    <!-- SECCIÓN 4: REPORTE DE ÓRDENES -->
    <div class="box box-warning" id="seccion-analisis-ordenes">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Análisis de Órdenes</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <?php include "reportes/ordenes-reporte.php"; ?>
      </div>
    </div>

    <!-- SECCIÓN 5: ESTADO DE RESULTADOS (FACTURACIÓN ELECTRÓNICA) -->
    <div class="box box-info" id="seccion-estado-resultados-facturacion">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-balance-scale"></i> Estado de Resultados (Facturación Electrónica)</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>

      <div class="box-body">
        <?php include "reportes/estado-resultados-facturacion.php"; ?>
      </div>
    </div>

    <!-- SECCIÓN 6: REPORTE DE FACTURACIÓN ELECTRÓNICA -->
    <div class="box box-danger" id="seccion-reportes-facturacion">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Reportes Facturación Electrónica</h3>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse">
            <i class="fa fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="box-body">
        <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
          <div class="col-md-3" style="margin-bottom: 10px;">
            <div class="input-group">
              <button type="button" class="btn btn-default" id="daterange-btn-reportes" style="width: 100%;">
                <span><i class="fa fa-calendar"></i> Rango de fecha</span>
                <i class="fa fa-caret-down"></i>
              </button>
            </div>
          </div>
          <div class="col-md-2" style="margin-bottom: 10px;">
            <div class="input-group">
              <span class="input-group-addon" style="background-color: #f4f4f4;"><i class="fa fa-filter"></i> Categoría</span>
              <select class="form-control" id="seleccionarCategoriaReporte">
                <option value="todos">Todos los documentos</option>
                <option value="facturas">Facturas Electrónicas</option>
                <option value="nc">Notas Crédito</option>
                <option value="ds">Documentos Soporte</option>
                <option value="na">Notas de Ajuste DS</option>
              </select>
            </div>
          </div>
          <div class="col-md-3" style="margin-bottom: 10px;">
            <div class="input-group" style="width: 100%;">
              <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i class="fa fa-users"></i></span>
              <select class="form-control" id="seleccionarClienteReporte" style="display:block; width: 100%;">
                <option value="todos">Todos los clientes</option>
                <?php
                $clientes = ControladorClientes::ctrMostrarClientes(null, null);
                foreach ($clientes as $key => $value) {
                  echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                }
                ?>
              </select>
              <select class="form-control" id="seleccionarProveedorReporte" style="display:none; width: 100%;">
                <option value="todos">Todos los proveedores</option>
                <?php
                $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                foreach ($proveedores as $key => $value) {
                  echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                }
                ?>
              </select>
            </div>
          </div>
          <div class="col-md-3" style="margin-bottom: 10px;">
            <div class="input-group" style="width: 100%;">
              <span class="input-group-addon" style="background-color: #f4f4f4; width: 40px;"><i class="fa fa-user"></i></span>
              <select class="form-control" id="seleccionarUsuarioReporte" style="width: 100%;">
                <option value="todos">Todos los usuarios</option>
                <?php
                $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                foreach ($usuarios as $key => $value) {
                  echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                }
                ?>
              </select>
            </div>
          </div>
          <div class="col-md-1" style="margin-bottom: 10px; display: flex; gap: 5px;">
            <button type="button" class="btn btn-primary" id="btnFiltrarReportes" style="flex: 1;">
              <i class="fa fa-search"></i> Buscar
            </button>
            <button type="button" class="btn btn-default" id="btnLimpiarFiltrosReportes" style="flex: 1;" title="Limpiar filtros">
              <i class="fa fa-refresh"></i>
            </button>
          </div>
        </div>

        <!-- WIDGETS DE RESUMEN (KPIs) -->
        <div class="row" id="kpi-reports" style="margin-top: 20px;">
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3 id="widget-total-ventas">$0</h3>
                <p>Venta Neta (Facturado - NC)</p>
              </div>
              <div class="icon"><i class="ion ion-social-usd"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
              <div class="inner">
                <h3 id="widget-total-iva">$0</h3>
                <p>Total IVA Recaudado</p>
              </div>
              <div class="icon"><i class="ion ion-pie-graph"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3 id="widget-total-ds">$0</h3>
                <p>Documentos Soporte</p>
              </div>
              <div class="icon"><i class="ion ion-ios-paper"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
              <div class="inner">
                <h3 id="widget-total-docs">0</h3>
                <p>Docs. Electrónicos Totales</p>
              </div>
              <div class="icon"><i class="ion ion-ios-albums"></i></div>
            </div>
          </div>
        </div>

        <!-- Fila para Tabla Detallada -->
        <div class="row" style="margin-top: 20px;">
          <div class="col-md-12">
            <div class="box-header with-border" style="padding-left: 0; padding-right: 0;">
              <h3 class="box-title">Listado Consolidado de Documentos</h3>
              <button type="button" class="btn btn-success pull-right" id="btnExportarExcelFacturacion">
                <i class="fa fa-file-excel-o"></i> Exportar a Excel
              </button>
            </div>
            <div class="box-body" style="padding-left: 0; padding-right: 0;">
              <table class="table table-bordered table-striped dt-responsive tablaReporteFacturacion" width="100%">
                <thead>
                  <tr>
                    <th style="width:10px">#</th>
                    <th>Tipo</th>
                    <th>Número</th>
                    <th>Cliente/Proveedor</th>
                    <th>Vendedor</th>
                    <th>Fecha</th>
                    <th>Monto Total</th>
                    <th>Estado</th>
                    <th>Ver</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>

</div>

<!-- Modal para descargar Excel con filtro de fechas -->
<div class="modal fade" id="modalDescargarExcel" tabindex="-1" role="dialog" aria-labelledby="modalDescargarExcelLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalDescargarExcelLabel"><i class="fa fa-file-excel-o"></i> Descargar Reporte en
          Excel</h4>
      </div>
      <div class="modal-body">
        <div class="filtro-excel-container">
          <div class="form-group">
            <label for="filtro-usuario-excel">Filtrar por usuario</label>
            <select id="filtro-usuario-excel" class="form-control">
              <option value="">Todos los usuarios</option>
              <?php
              $item = null;
              $valor = null;
              $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
              foreach ($usuarios as $key => $value) {
                echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label for="tipo-fecha-excel">Filtrar por fecha</label>
            <select id="tipo-fecha-excel" class="form-control">
              <option value="todo">Todas las ventas</option>
              <option value="hoy">Hoy</option>
              <option value="ayer">Ayer</option>
              <option value="mes">Mes actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>

          <div id="campo-desde-excel" class="form-group" style="display:none;">
            <label for="fecha-desde-excel">Desde</label>
            <input type="date" id="fecha-desde-excel" class="form-control">
          </div>

          <div id="campo-hasta-excel" class="form-group" style="display:none;">
            <label for="fecha-hasta-excel">Hasta</label>
            <input type="date" id="fecha-hasta-excel" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <a id="btn-descargar-excel" href="vistas/modulos/descargar-reporte.php?reporte=reporte" class="btn btn-success">
          <i class="fa fa-download"></i> Descargar
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  // Mostrar/ocultar campos de fecha personalizada
  document.getElementById('tipo-fecha-excel').addEventListener('change', function () {
    const tipo = this.value;
    const campoDesde = document.getElementById('campo-desde-excel');
    const campoHasta = document.getElementById('campo-hasta-excel');

    if (tipo === 'personalizado') {
      campoDesde.style.display = 'block';
      campoHasta.style.display = 'block';
    } else {
      campoDesde.style.display = 'none';
      campoHasta.style.display = 'none';
    }

    actualizarEnlaceExcel();
  });

  // Actualizar enlace cuando cambian las fechas
  document.getElementById('fecha-desde-excel').addEventListener('change', actualizarEnlaceExcel);
  document.getElementById('fecha-hasta-excel').addEventListener('change', actualizarEnlaceExcel);
  document.getElementById('filtro-usuario-excel').addEventListener('change', actualizarEnlaceExcel);

  function actualizarEnlaceExcel() {
    const tipo = document.getElementById('tipo-fecha-excel').value;
    const btnDescargar = document.getElementById('btn-descargar-excel');
    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-reporte.php?reporte=reporte`;

    let fechaInicial, fechaFinal;
    const hoy = new Date();

    switch (tipo) {
      case 'hoy':
        fechaInicial = fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'ayer':
        const ayer = new Date(hoy);
        ayer.setDate(ayer.getDate() - 1);
        fechaInicial = fechaFinal = ayer.toISOString().split('T')[0];
        break;
      case 'mes':
        fechaInicial = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
        fechaFinal = hoy.toISOString().split('T')[0];
        break;
      case 'personalizado':
        fechaInicial = document.getElementById('fecha-desde-excel').value;
        fechaFinal = document.getElementById('fecha-hasta-excel').value;
        break;
      default:
        // "todo" - sin filtro de fechas
        break;
    }

    if (fechaInicial && fechaFinal) {
      url += `&fechaInicial=${fechaInicial}&fechaFinal=${fechaFinal}`;
    }

    const usuario = document.getElementById('filtro-usuario-excel').value;
    if (usuario) {
      url += `&usuario=${usuario}`;
    }

    btnDescargar.href = url;
  }

  // Función para mostrar toast notification
  function mostrarToast(mensaje) {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<i class="fa fa-check-circle" style="font-size: 20px;"></i> <span>' + mensaje + '</span>';

    // Agregar al body
    document.body.appendChild(toast);

    // Remover después de 3 segundos
    setTimeout(function () {
      toast.classList.add('toast-hide');
      setTimeout(function () {
        document.body.removeChild(toast);
      }, 300);
    }, 3000);
  }

  // Mostrar toast ANTES de que se cierre el modal
  $('#btn-descargar-excel').on('click', function (e) {
    // Mostrar toast inmediatamente
    mostrarToast('¡Descarga iniciada! El archivo Excel se está descargando...');
  });

  // Limpiar completamente cuando el modal se cierra
  $('#modalDescargarExcel').on('hidden.bs.modal', function () {
    setTimeout(function () {
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open');
      $('body').css('padding-right', '');
      $('body').css('overflow', '');
    }, 50);
  });

  // Colapsar secciones al cargar la página
  $(document).ready(function () {
    // En móvil: colapsar todas las secciones
    if ($(window).width() < 768) {
      $('.box').addClass('collapsed-box-mobile');
    } else {
      // En desktop: colapsar solo las secciones 2, 3, 4, 5 y 6 (dejar la 1ra expandida)
      $('#seccion-graficos-rendimiento').addClass('collapsed-box');
      $('#seccion-estado-resultados').addClass('collapsed-box');
      $('#seccion-analisis-ordenes').addClass('collapsed-box');
      $('#seccion-estado-resultados-facturacion').addClass('collapsed-box');
      $('#seccion-reportes-facturacion').addClass('collapsed-box');
    }

    // Manejar el clic en el botón de colapso
    $('[data-widget="collapse"]').on('click', function () {
      var box = $(this).closest('.box');

      if ($(window).width() < 768) {
        // En móvil usar la clase mobile
        box.toggleClass('collapsed-box-mobile');
      }
      // En desktop AdminLTE maneja el colapso automáticamente con collapsed-box
    });

    // Re-evaluar al cambiar tamaño de ventana
    $(window).resize(function () {
      if ($(window).width() >= 768) {
        // En desktop, remover clase móvil y aplicar collapsed-box a secciones 2, 3, 4
        $('.box').removeClass('collapsed-box-mobile');

        // Colapsar secciones 2, 3, 4 solo si no están ya expandidas manualmente
        if (!$('#seccion-graficos-rendimiento').hasClass('manually-expanded')) {
          $('#seccion-graficos-rendimiento').addClass('collapsed-box');
        }
        if (!$('#seccion-estado-resultados').hasClass('manually-expanded')) {
          $('#seccion-estado-resultados').addClass('collapsed-box');
        }
        if (!$('#seccion-analisis-ordenes').hasClass('manually-expanded')) {
          $('#seccion-analisis-ordenes').addClass('collapsed-box');
        }
        if (!$('#seccion-reportes-facturacion').hasClass('manually-expanded')) {
          $('#seccion-reportes-facturacion').addClass('collapsed-box');
        }
      } else {
        // En móvil, remover collapsed-box y aplicar collapsed-box-mobile
        $('.box').removeClass('collapsed-box');
        $('.box').addClass('collapsed-box-mobile');
      }
    });
  });
</script>
<script src="vistas/js/reportes-facturacion.js?v=<?php echo time(); ?>"></script>