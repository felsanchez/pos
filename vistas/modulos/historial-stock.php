<style>
  /* Estilos para campo notas editable */
  .celda-notas-movimiento {
    background: #fff9e6;
    padding: 8px;
    border-radius: 3px;
    cursor: text;
    font-size: 13px;
    color: #333;
    min-height: 30px;
    position: relative;
  }

  /* Placeholder para cuando está vacío */
  .celda-notas-movimiento:empty:before,
  .celda-notas-movimiento[data-placeholder]:before {
    content: "Escribe una nota...";
    color: #999;
    font-style: italic;
  }

  /* Ocultar placeholder cuando tiene foco */
  .celda-notas-movimiento:focus:before {
    content: none;
  }

  .celda-notas-movimiento:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
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
</style>

<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Historial de Stock
      <small>Auditoría de inventario</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Historial de Stock</li>
    </ol>

  </section>

  <section class="content">

    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4><i class="icon fa fa-warning"></i> Atención!</h4>
      Los registros del historial de stock se eliminan automáticamente del sistema después de transcurrir <b>3 meses</b>
      desde su creación.
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="row" id="tarjetasResumen">

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3 id="totalVentas">0</h3>
            <p>Ventas Totales</p>
          </div>
          <div class="icon">
            <i class="fa fa-shopping-cart"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <h3 id="totalCreaciones">0</h3>
            <p>Creación Productos/Variantes</p>
          </div>
          <div class="icon">
            <i class="fa fa-plus-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3 id="totalEdiciones">0</h3>
            <p>Edición de Stock</p>
          </div>
          <div class="icon">
            <i class="fa fa-edit"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <h3 id="totalMovimientos">0</h3>
            <p>Total Movimientos</p>
          </div>
          <div class="icon">
            <i class="fa fa-list"></i>
          </div>
        </div>
      </div>

    </div>


    <!-- TABLA DE MOVIMIENTOS -->
    <div class="box">

      <div class="box-header with-border">

        <h3 class="box-title">Registro de Movimientos</h3>

        <div class="pull-right contenedor-filtros">

          <form id="formFiltros" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

            <!-- Filtro por Producto -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Filtrar por Producto:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="filtroProducto" name="filtroProducto"
                  style="width: 140px; border-left: 0;">
                  <option value="">Seleccionar Producto</option>
                  <?php
                  $item = null;
                  $valor = null;
                  $orden = "descripcion";
                  $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
                  foreach ($productos as $key => $value) {
                    echo '<option value="' . e($value["id"]) . '">' . e($value["descripcion"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por Movimiento -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Filtrar por Tipo:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="filtroTipo" name="filtroTipo"
                  style="width: 130px; border-left: 0;">
                  <option value="">Seleccionar Tipo</option>
                  <option value="venta">Venta</option>
                  <option value="eliminacion_venta">Eliminación Venta</option>
                  <option value="creacion_producto">Creación</option>
                  <option value="creacion_variante">Creación Variación</option>
                  <option value="edicion_stock">Edición Stock</option>
                </select>
              </div>
            </div>

            <!-- Filtro por Usuario -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Filtrar por Usuario:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="filtroUsuario" name="filtroUsuario"
                  style="width: 120px; border-left: 0;">
                  <option value="">Seleccionar Usuario</option>
                  <?php
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                  foreach ($usuarios as $key => $value) {
                    echo '<option value="' . e($value["id"]) . '">' . e($value["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por Rango de Fecha -->
            <div class="form-group" style="margin-bottom: 0;">
              <button type="button" class="btn btn-default" id="daterange-btn">
                <span>
                  <i class="fa fa-calendar"></i> Rango
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
              <input type="hidden" id="filtroFechaDesde" name="filtroFechaDesde">
              <input type="hidden" id="filtroFechaHasta" name="filtroFechaHasta">
            </div>

            <!-- Botones de Acción (Separados para mantener gap consistente) -->
            <button type="button" class="btn btn-primary" id="btnFiltrar" title="Filtrar">
              <i class="fa fa-search"></i>
            </button>
            <button type="button" class="btn btn-default" id="btnLimpiar" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalDescargarExcelStock">
              <i class="fa fa-file-excel-o"></i> Exportar a Excel
            </button>

          </form>

        </div>

      </div>


      <div class="box-body">

        <table class="table table-bordered table-striped tablaHistorialStock" width="100%">

          <thead>

            <tr>

              <th>Fecha</th>
              <th>Producto</th>
              <th>Tipo</th>
              <th>Tipo Movimiento</th>
              <th>Cantidad</th>
              <th>Stock Anterior</th>
              <th>Stock Nuevo</th>
              <th>Usuario</th>
              <th>Referencia</th>
              <th><i class="fa fa-pencil-square"></i> Notas</th>
            </tr>

          </thead>

          <tbody>
            <!-- Se llenará dinámicamente con DataTables -->
          </tbody>

        </table>

      </div>

    </div>

  </section>

</div>

<!-- Modal para descargar Excel con filtro de fechas -->
<div class="modal fade" id="modalDescargarExcelStock" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"><i class="fa fa-file-excel-o"></i> Descargar Historial de Stock en Excel</h4>
      </div>
      <div class="modal-body">
        <div style="padding: 15px; border-radius: 10px; background-color: #f9f9f9;">
          <div class="form-group">
            <label for="tipo-fecha-excel-stock">Filtrar por fecha</label>
            <select id="tipo-fecha-excel-stock" class="form-control" style="border-radius: 8px;">
              <option value="todo">Todo el historial</option>
              <option value="hoy">Hoy</option>
              <option value="ayer">Ayer</option>
              <option value="mes">Mes actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>

          <div id="campo-desde-excel-stock" class="form-group" style="display:none;">
            <label for="fecha-desde-excel-stock">Desde</label>
            <input type="date" id="fecha-desde-excel-stock" class="form-control" style="border-radius: 8px;">
          </div>

          <div id="campo-hasta-excel-stock" class="form-group" style="display:none;">
            <label for="fecha-hasta-excel-stock">Hasta</label>
            <input type="date" id="fecha-hasta-excel-stock" class="form-control" style="border-radius: 8px;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <a id="btn-descargar-excel-stock" href="vistas/modulos/descargar-historial-stock.php" class="btn btn-success">
          <i class="fa fa-download"></i> Descargar
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  // Mostrar/ocultar campos de fecha personalizada para historial de stock
  document.getElementById('tipo-fecha-excel-stock').addEventListener('change', function () {
    const tipo = this.value;
    const campoDesde = document.getElementById('campo-desde-excel-stock');
    const campoHasta = document.getElementById('campo-hasta-excel-stock');

    if (tipo === 'personalizado') {
      campoDesde.style.display = 'block';
      campoHasta.style.display = 'block';
    } else {
      campoDesde.style.display = 'none';
      campoHasta.style.display = 'none';
    }

    actualizarEnlaceExcelStock();
  });

  // Actualizar enlace cuando cambian las fechas
  document.getElementById('fecha-desde-excel-stock').addEventListener('change', actualizarEnlaceExcelStock);
  document.getElementById('fecha-hasta-excel-stock').addEventListener('change', actualizarEnlaceExcelStock);

  function actualizarEnlaceExcelStock() {
    const tipo = document.getElementById('tipo-fecha-excel-stock').value;
    const btnDescargar = document.getElementById('btn-descargar-excel-stock');
    let rutaBase = window.location.hostname.includes("localhost") ? "/pos" : "";
    let url = `${rutaBase}/vistas/modulos/descargar-historial-stock.php`;

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
        fechaInicial = document.getElementById('fecha-desde-excel-stock').value;
        fechaFinal = document.getElementById('fecha-hasta-excel-stock').value;
        break;
      case 'todo':
      default:
        // Sin filtros de fecha
        btnDescargar.href = url;
        return;
    }

    if (fechaInicial && fechaFinal) {
      url += `?fechaInicial=${fechaInicial}&fechaFinal=${fechaFinal}`;
    }

    btnDescargar.href = url;
  }

  // Inicializar el enlace al cargar
  document.addEventListener('DOMContentLoaded', function () {
    actualizarEnlaceExcelStock();
  });

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
  $('#btn-descargar-excel-stock').on('click', function (e) {
    // Mostrar toast inmediatamente
    mostrarToast('¡Descarga iniciada! El archivo Excel se está descargando...');
  });

  // Limpiar completamente cuando el modal se cierra
  $('#modalDescargarExcelStock').on('hidden.bs.modal', function () {
    setTimeout(function () {
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open');
      $('body').css('padding-right', '');
      $('body').css('overflow', '');
    }, 50);
  });
</script>