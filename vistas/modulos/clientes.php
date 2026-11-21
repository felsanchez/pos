<?php
if (!puedeVer('clientes')) {
    echo '<script>window.location = "inicio";</script>';
    return;
}
?>
<!-- Librería de estilos de Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<!-- Ruta clientes.css -->
<link rel="stylesheet" href="assets/css/clientes.css">




<style>
  td.details-control {
    background: url('https://cdn.datatables.net/1.13.6/images/details_open.png') no-repeat center center;
    cursor: pointer;
  }

  tr.shown td.details-control {
    background: url('https://cdn.datatables.net/1.13.6/images/details_close.png') no-repeat center center;
  }

  @media (max-width: 767px) {
    .box-header .pull-right.form-filtros-clientes {
      float: none !important;
      width: 100% !important;
      margin-top: 15px !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: space-between !important;
    }
    .form-filtros-clientes > span {
      min-width: 80px !important;
      text-align: left !important;
    }
    .form-filtros-clientes > .input-group {
      flex: 1 !important;
      width: auto !important;
    }
    .form-filtros-clientes .select2-container {
      width: 100% !important;
    }
  }
</style>


<!-- Estilos para que el dropdown de Choices.js no se corte -->
<style>
  /* Permitir que el dropdown se muestre fuera del contenedor de la tabla */
  .table-responsive {
    overflow: visible !important;
  }

  /* Asegurar que el dropdown de Choices.js tenga z-index alto y se posicione correctamente */
  .choices__list--dropdown {
    position: absolute !important;
    z-index: 9999 !important;
    overflow-y: auto !important;
  }

  /* Asegurar que el contenedor box-body permita overflow visible */
  .box-body {
    overflow: visible !important;
  }

  /* Pero mantener scroll horizontal solo si es necesario en pantallas pequeñas */
  @media (max-width: 991px) {
    .table-responsive {
      overflow-x: auto !important;
      overflow-y: visible !important;
    }
  }
</style>




<!-- Estilos dinámicos para colores de estados -->
<style>
  <?php
  $estadosParaEstilos = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
  foreach ($estadosParaEstilos as $estadoEstilo) {
    $nombreLimpio = str_replace(" ", "-", strtolower($estadoEstilo["nombre"]));

    $color = $estadoEstilo["color"];

    // Estilos para el contenedor cerrado de Choices.js (select cuando NO está abierto)
    echo '.estatus-' . $nombreLimpio . ' .choices__inner,';
    echo '.estatus-' . $nombreLimpio . '.choices .choices__inner {';
    echo '  background-color: ' . $color . ' !important;';
    echo '  border-color: ' . $color . ' !important;';
    echo '  color: #fff !important;';
    echo '}';


    // Estilos para cada opción individual en el dropdown (basado SOLO en data-value)
    echo '.choices__list--dropdown .choices__item--selectable[data-value="' . $estadoEstilo["nombre"] . '"] {';
    echo '  background-color: ' . $color . ' !important;';
    echo '  color: #fff !important;';
    echo '}';

    echo '.choices__list--dropdown .choices__item--selectable[data-value="' . $estadoEstilo["nombre"] . '"]:hover {';
    echo '  background-color: ' . adjustBrightness($color, -20) . ' !important;';
    echo '  color: #fff !important;';
    echo '}';
  }

  // Función helper para ajustar brillo (hover más oscuro)
  function adjustBrightness($hex, $steps)
  {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
  }
  ?>
</style>


<!-- Mapa de colores de estados para JavaScript -->
<script>
  window.estadosColores = {
    <?php
    foreach ($estadosParaEstilos as $key => $estadoEstilo) {
      $coma = ($key < count($estadosParaEstilos) - 1) ? ',' : '';
      echo '"' . strtolower($estadoEstilo["nombre"]) . '": "' . $estadoEstilo["color"] . '"' . $coma . "\n";
    }
    ?>
  };

  console.log("Colores de estados cargados:", window.estadosColores);
</script>

<?php
$editarCliente = new ControladorClientes();
$editarCliente->ctrEditarCliente();
?>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar Clientes
      <small>Fidelización y seguimiento</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Clientes</li>
    </ol>

  </section>

  <section class="content">

    <div class="nav-tabs-custom" style="box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-radius: 4px; overflow: hidden;">
      
      <ul class="nav nav-tabs" style="font-size: 14px; background: #fafafa; border-bottom: 2px solid #3c8dbc;">
        <li class="active">
          <a href="#tab_directorio_clientes" data-toggle="tab" style="font-weight: 600; padding: 12px 20px;">
            <i class="fa fa-users text-primary" style="margin-right: 6px;"></i> Directorio de Clientes
          </a>
        </li>
        <li>
          <a href="#tab_inactividad_clientes" data-toggle="tab" style="font-weight: 600; padding: 12px 20px;">
            <i class="fa fa-clock-o text-yellow" style="margin-right: 6px;"></i> Análisis de Compras e Inactividad
          </a>
        </li>
      </ul>

      <div class="tab-content" style="padding: 15px 15px 25px 15px;">

        <!--=====================================
        PESTAÑA 1: DIRECTORIO DE CLIENTES (ACTUAL)
        ======================================-->
        <div class="tab-pane active" id="tab_directorio_clientes">

          <div class="box" style="border-top: none; box-shadow: none; margin-bottom: 0;">

            <div class="box-header with-border" style="padding-left: 0; padding-right: 0;">
              <?php if (puedeAccion('clientes', 'crear')): ?>
                <a href="cliente-detalle" class="btn btn-primary">
                  <i class="fa fa-plus"></i> Agregar Cliente
                </a>
              <?php else: ?>
                <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear clientes">
                  <i class="fa fa-plus"></i> Agregar Cliente
                </button>
              <?php endif; ?>

              <?php if (puedeAccion('clientes', 'crear')): ?>
                <button class="btn btn-success" data-toggle="modal" data-target="#modalImportarClientes">
                  <i class="fa fa-upload"></i> Exportar / Importar Clientes
                </button>
              <?php else: ?>
                <button class="btn btn-success" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para importar/exportar">
                  <i class="fa fa-upload"></i> Exportar / Importar Clientes
                </button>
              <?php endif; ?>

              <?php if (puedeAccion('clientes', 'editar')): ?>
                <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarEstados">
                  <i class="fa fa-flag"></i> Gestionar estados
                </button>
              <?php else: ?>
                <button class="btn btn-default" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para gestionar estados">
                  <i class="fa fa-flag"></i> Gestionar estados
                </button>
              <?php endif; ?>

              <!-- Filtros en el Header: Estado y Origen -->
              <?php
              $configGlobalClientes = ControladorConfiguracion::ctrObtenerConfiguracion();
              $mostrarFiltroAgenteIA = (!isset($configGlobalClientes["leads_whatsapp_activos"]) || $configGlobalClientes["leads_whatsapp_activos"] == 1);
              ?>
              <div class="pull-right form-filtros-clientes" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span><b>Origen:</b></span>
                <div class="input-group" style="width: 160px;">
                  <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                    <i class="fa fa-filter text-success"></i>
                  </span>
                  <select class="form-control select2" id="filtroOrigenCliente" style="width: 100%;">
                    <option value="">Todos los canales</option>
                    <option value="menu_digital">Menú Digital</option>
                    <?php if ($mostrarFiltroAgenteIA): ?>
                      <option value="agente_ia">Agente IA</option>
                    <?php endif; ?>
                    <option value="sistema">POS / Caja</option>
                  </select>
                </div>

                <span><b>Estado:</b></span>
                <div class="input-group" style="width: 180px;">
                  <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                    <i class="fa fa-search text-primary"></i>
                  </span>
                  <select class="form-control select2" id="filtroEstatus1" style="width: 100%;">
                    <option value="">Mostrar Todos</option>
                    <?php
                    $estadosDisponibles = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
                    foreach ($estadosDisponibles as $estado) {
                      $selected = ($filtroEstatus1 == $estado["nombre"]) ? "selected" : "";
                      echo '<option value="' . $estado["nombre"] . '" ' . $selected . '>' . ucfirst($estado["nombre"]) . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>

            <?php
            $filtroEstatus1 = isset($_GET['filtroEstatus1']) ? $_GET['filtroEstatus1'] : '';
            $item = "estatus";
            $valor = $filtroEstatus1;
            $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
            ?>

            <div class="box-body" style="padding-left: 0; padding-right: 0;">
              <div class="tabla-clientes table-responsive">
              <table class="table table-bordered table-striped tablas1 display nowrap" style="width: 100%;">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Notas</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                   <!-- DataTables Server-Side -->
                </tbody>
              </table>
              </div><!-- /.tabla-clientes -->
            </div>

          </div>

        </div>

        <!--=====================================
        PESTAÑA 2: ANÁLISIS DE COMPRAS E INACTIVIDAD
        ======================================-->
        <div class="tab-pane" id="tab_inactividad_clientes">

          <?php
          // Cargar datos analíticos de compras e inactividad
          $clientesInactividad = ControladorClientes::ctrMostrarClientesInactividad();
          $totalClientesAnalisis = is_array($clientesInactividad) ? count($clientesInactividad) : 0;

          $conteoActivos15 = 0;   // < 15 días
          $conteoActivos30 = 0;   // 15 a 30 días
          $conteoEnRiesgo = 0;    // 31 a 60 días
          $conteoAlerta = 0;      // 61 a 90 días
          $conteoInactivos = 0;   // > 90 días
          $conteoSinCompras = 0;  // Sin compras
          $totalFacturadoGlobal = 0;

          if (!empty($clientesInactividad)) {
              foreach ($clientesInactividad as $cliItem) {
                  $totalFacturadoGlobal += floatval($cliItem["total_gastado"] ?? 0);
                  if ($cliItem["ultima_compra"] === null || $cliItem["dias_inactivo"] === null) {
                      $conteoSinCompras++;
                  } else {
                      $dias = intval($cliItem["dias_inactivo"]);
                      if ($dias < 15) {
                          $conteoActivos15++;
                      } elseif ($dias <= 30) {
                          $conteoActivos30++;
                      } elseif ($dias <= 60) {
                          $conteoEnRiesgo++;
                      } elseif ($dias <= 90) {
                          $conteoAlerta++;
                      } else {
                          $conteoInactivos++;
                      }
                  }
              }
          }
          $conteoTotalActivos = $conteoActivos15 + $conteoActivos30;
          ?>

          <style>
          .card-kpi-inact {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 12px;
          }
          .card-kpi-inact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
          }
          .card-kpi-inact.active {
            border-color: #3c8dbc;
            background: #f0f9ff;
            box-shadow: 0 2px 8px rgba(60,141,188,0.25);
          }
          .card-kpi-inact .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
          }
          .card-kpi-inact .kpi-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
          }
          .card-kpi-inact .kpi-num {
            font-size: 19px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
          }
          .card-kpi-inact .kpi-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
          }
          </style>

          <!-- KPI Cards / Resumen Estadístico Interactivo -->
          <div class="row" style="margin-bottom: 15px;">
            
            <div class="col-md-2 col-sm-4 col-xs-6">
              <div class="card-kpi-inact active" data-filtro-kpi="todos" title="Ver todos los clientes">
                <div class="kpi-icon" style="background: rgba(60, 141, 188, 0.12); color: #3c8dbc;">
                  <i class="fa fa-users"></i>
                </div>
                <div class="kpi-info">
                  <span class="kpi-num"><?= $totalClientesAnalisis ?></span>
                  <span class="kpi-label">Total Clientes</span>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-4 col-xs-6">
              <div class="card-kpi-inact" data-filtro-kpi="activos" title="Filtrar Clientes Activos (&le; 30 días)">
                <div class="kpi-icon" style="background: rgba(0, 166, 90, 0.12); color: #00a65a;">
                  <i class="fa fa-check-circle"></i>
                </div>
                <div class="kpi-info">
                  <span class="kpi-num" style="color: #00a65a;"><?= $conteoTotalActivos ?></span>
                  <span class="kpi-label">Activos (&le; 30d)</span>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-4 col-xs-6">
              <div class="card-kpi-inact" data-filtro-kpi="30_a_60" title="Filtrar Clientes En Riesgo (31 a 60 días)">
                <div class="kpi-icon" style="background: rgba(243, 156, 18, 0.12); color: #f39c12;">
                  <i class="fa fa-clock-o"></i>
                </div>
                <div class="kpi-info">
                  <span class="kpi-num" style="color: #f39c12;"><?= $conteoEnRiesgo ?></span>
                  <span class="kpi-label">En Riesgo (31-60d)</span>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-4 col-xs-6">
              <div class="card-kpi-inact" data-filtro-kpi="60_a_90" title="Filtrar Clientes en Alerta (61 a 90 días)">
                <div class="kpi-icon" style="background: rgba(255, 133, 27, 0.12); color: #ff851b;">
                  <i class="fa fa-exclamation-triangle"></i>
                </div>
                <div class="kpi-info">
                  <span class="kpi-num" style="color: #ff851b;"><?= $conteoAlerta ?></span>
                  <span class="kpi-label">Alerta (61-90d)</span>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-4 col-xs-6">
              <div class="card-kpi-inact" data-filtro-kpi="mas_90" title="Filtrar Clientes Inactivos (> 90 días)">
                <div class="kpi-icon" style="background: rgba(221, 75, 57, 0.12); color: #dd4b39;">
                  <i class="fa fa-times-circle"></i>
                </div>
                <div class="kpi-info">
                  <span class="kpi-num" style="color: #dd4b39;"><?= $conteoInactivos ?></span>
                  <span class="kpi-label">Inactivos (&gt; 90d)</span>
                </div>
              </div>
            </div>

            <div class="col-md-2 col-sm-4 col-xs-6">
              <div class="card-kpi-inact" data-filtro-kpi="sin_compras" title="Filtrar Clientes Sin Compras">
                <div class="kpi-icon" style="background: rgba(108, 117, 125, 0.12); color: #6c757d;">
                  <i class="fa fa-minus-circle"></i>
                </div>
                <div class="kpi-info">
                  <span class="kpi-num" style="color: #6c757d;"><?= $conteoSinCompras ?></span>
                  <span class="kpi-label">Sin Compras</span>
                </div>
              </div>
            </div>

          </div>

          <!-- Barra de Filtros y Botón de Exportar a Excel -->
          <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <div class="box-body" style="padding: 15px 20px;">
              <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                
                <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                  <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 5px; display: block;">
                    <i class="fa fa-filter text-primary"></i> Rango de Inactividad:
                  </label>
                  <select class="form-control" id="filtroRangoInactividad" style="font-weight: 500; height: 38px; border-radius: 4px;">
                    <option value="todos">Todos los Rangos (<?= $totalClientesAnalisis ?> clientes)</option>
                    <option value="menos_15">Menos de 15 días (Recientes - <?= $conteoActivos15 ?>)</option>
                    <option value="15_a_30">15 a 30 días (Activos - <?= $conteoActivos30 ?>)</option>
                    <option value="activos">Todos los Activos &le; 30 días (<?= $conteoTotalActivos ?>)</option>
                    <option value="30_a_60">30 a 60 días (En Riesgo - <?= $conteoEnRiesgo ?>)</option>
                    <option value="60_a_90">60 a 90 días (Alerta - <?= $conteoAlerta ?>)</option>
                    <option value="mas_90">Más de 90 días (Inactivos - <?= $conteoInactivos ?>)</option>
                    <option value="sin_compras">Sin Compras Registradas (<?= $conteoSinCompras ?>)</option>
                  </select>
                </div>

                <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                  <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 5px; display: block;">
                    <i class="fa fa-search text-primary"></i> Búsqueda Rápida:
                  </label>
                  <div class="input-group" style="width: 100%;">
                    <span class="input-group-addon" style="background: #f8fafc; border-color: #cbd5e1;"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" id="buscarTablaInactividad" class="form-control" placeholder="Buscar por cliente, documento, teléfono..." style="height: 38px; border-radius: 0 4px 4px 0;">
                  </div>
                </div>

                <div class="col-md-4 col-sm-12 col-xs-12 text-right" style="margin-bottom: 10px; display: flex; align-items: flex-end; justify-content: flex-end; gap: 8px;">
                  <button type="button" id="btnExportarExcelInactividad" class="btn btn-success" style="font-weight: 600; height: 38px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(40,167,69,0.25);">
                    <i class="fa fa-file-excel-o" style="font-size: 16px;"></i> Exportar a Excel
                  </button>
                  <button type="button" id="btnResetFiltrosInactividad" class="btn btn-default" style="height: 38px;" title="Restablecer filtros">
                    <i class="fa fa-refresh"></i>
                  </button>
                </div>

              </div>

              <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 8px; flex-wrap: wrap; gap: 6px;">
                <div>
                  <span id="contadorClientesInactividad">Mostrando <b><?= $totalClientesAnalisis ?></b> de <b><?= $totalClientesAnalisis ?></b> clientes</span>
                </div>
                <div>
                  <span>Total facturado histórico: <b style="color: #1e293b;">$ <?= number_format($totalFacturadoGlobal, 2, ',', '.') ?></b></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla Analítica de Inactividad -->
          <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
            <div class="box-body" style="padding: 0;">
              <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaAnalisisInactividad" style="width: 100%; margin-bottom: 0;">
                  <thead style="background: #f8fafc; color: #334155; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                      <th style="width: 40px;" class="text-center">#</th>
                      <th style="min-width: 180px;">Cliente</th>
                      <th style="min-width: 120px;">Documento</th>
                      <th style="min-width: 180px;">Contacto</th>
                      <th class="text-center" style="min-width: 110px;">Última Compra</th>
                      <th class="text-center" style="min-width: 130px;">Días Transcurridos</th>
                      <th class="text-center" style="min-width: 100px;">Total Compras</th>
                      <th class="text-right" style="min-width: 130px;">Monto Acumulado</th>
                      <th class="text-center" style="min-width: 130px;">Estado / Semáforo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($clientesInactividad)): ?>
                      <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                          <i class="fa fa-info-circle fa-2x" style="opacity: 0.5; margin-bottom: 8px;"></i>
                          <p style="margin: 0;">No se encontraron clientes para mostrar.</p>
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php
                      $contadorFila = 1;
                      foreach ($clientesInactividad as $cli):
                          $dias = $cli["dias_inactivo"] !== null ? intval($cli["dias_inactivo"]) : null;
                          
                          if ($cli["ultima_compra"] === null || $dias === null) {
                              $rangoSlug = 'sin_compras';
                              $fechaTexto = '<span class="text-muted" style="font-style: italic;">Sin registro</span>';
                              $fechaExport = 'Nunca';
                              $diasTexto = '<span class="text-muted" style="font-style: italic;">Sin compras</span>';
                              $diasNum = 999999;
                              $badgeSemaforo = '<span class="badge" style="background-color: #6c757d; font-size: 11px; padding: 4px 8px;"><i class="fa fa-minus-circle"></i> Sin compras</span>';
                          } else {
                              $fechaExport = date("d/m/Y", strtotime($cli["ultima_compra"]));
                              $fechaTexto = '<span style="font-weight: 500;">' . $fechaExport . '</span>' .
                                            '<br><small class="text-muted">' . date("h:i A", strtotime($cli["ultima_compra"])) . '</small>';
                              $diasNum = $dias;

                              if ($dias < 15) {
                                  $rangoSlug = 'menos_15';
                                  $diasTexto = ($dias == 0) ? '<span class="badge" style="background-color: #00a65a; font-size: 11px;">¡Hoy!</span>' : '<b class="text-success">Hace ' . $dias . ' día' . ($dias == 1 ? '' : 's') . '</b>';
                                  $badgeSemaforo = '<span class="badge" style="background-color: #00a65a; font-size: 11px; padding: 4px 8px;"><i class="fa fa-check-circle"></i> Activo (&lt; 15d)</span>';
                              } elseif ($dias <= 30) {
                                  $rangoSlug = '15_a_30';
                                  $diasTexto = '<span style="color: #28a745; font-weight: 600;">Hace ' . $dias . ' días</span>';
                                  $badgeSemaforo = '<span class="badge" style="background-color: #28a745; font-size: 11px; padding: 4px 8px;"><i class="fa fa-check"></i> Activo (15-30d)</span>';
                              } elseif ($dias <= 60) {
                                  $rangoSlug = '30_a_60';
                                  $diasTexto = '<b style="color: #f39c12;">Hace ' . $dias . ' días</b>';
                                  $badgeSemaforo = '<span class="badge" style="background-color: #f39c12; font-size: 11px; padding: 4px 8px;"><i class="fa fa-clock-o"></i> En Riesgo (31-60d)</span>';
                              } elseif ($dias <= 90) {
                                  $rangoSlug = '60_a_90';
                                  $diasTexto = '<b style="color: #ff851b;">Hace ' . $dias . ' días</b>';
                                  $badgeSemaforo = '<span class="badge" style="background-color: #ff851b; font-size: 11px; padding: 4px 8px;"><i class="fa fa-exclamation-triangle"></i> Alerta (61-90d)</span>';
                              } else {
                                  $rangoSlug = 'mas_90';
                                  $diasTexto = '<b style="color: #dd4b39;">Hace ' . $dias . ' días</b>';
                                  $badgeSemaforo = '<span class="badge" style="background-color: #dd4b39; font-size: 11px; padding: 4px 8px;"><i class="fa fa-times-circle"></i> Inactivo (&gt; 90d)</span>';
                              }
                          }

                          $docDisplay = htmlspecialchars($cli['documento'] ?? '');
                          if (!empty($cli['tipo_documento_id']) && $cli['tipo_documento_id'] == 6 && isset($cli['digito_verificacion']) && $cli['digito_verificacion'] !== '') {
                              $docDisplay .= '-' . htmlspecialchars($cli['digito_verificacion']);
                          }

                          $busquedaTexto = mb_strtolower($cli['nombre'] . ' ' . $docDisplay . ' ' . ($cli['telefono'] ?? '') . ' ' . ($cli['email'] ?? '') . ' ' . ($cli['ciudad'] ?? ''));
                      ?>
                        <tr data-rango="<?= $rangoSlug ?>" data-dias="<?= $diasNum ?>" data-search="<?= htmlspecialchars($busquedaTexto) ?>">
                          <!-- # -->
                          <td class="text-center text-muted" style="font-size: 12px;"><?= $contadorFila++ ?></td>

                          <!-- Cliente -->
                          <td>
                            <div style="font-weight: 700; color: #1e293b; font-size: 13px;">
                              <?= htmlspecialchars($cli['nombre']) ?>
                            </div>
                            <?php if (!empty($cli['ciudad'])): ?>
                              <small class="text-muted" style="font-size: 11px;"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($cli['ciudad']) ?></small>
                            <?php endif; ?>
                          </td>

                          <!-- Documento -->
                          <td>
                            <span style="font-family: monospace; font-size: 12px; font-weight: 600; color: #334155;"><?= $docDisplay ?></span>
                          </td>

                          <!-- Contacto (Teléfono y Email - SIN Botón de WhatsApp) -->
                          <td style="font-size: 12px;">
                            <?php if (!empty($cli['telefono'])): ?>
                              <div style="margin-bottom: 2px;">
                                <i class="fa fa-phone text-muted" style="width: 14px;"></i> <?= htmlspecialchars($cli['telefono']) ?>
                              </div>
                            <?php endif; ?>
                            <?php if (!empty($cli['email'])): ?>
                              <div>
                                <i class="fa fa-envelope-o text-muted" style="width: 14px;"></i> <a href="mailto:<?= htmlspecialchars($cli['email']) ?>" style="color: #3c8dbc; word-break: break-all;"><?= htmlspecialchars($cli['email']) ?></a>
                              </div>
                            <?php endif; ?>
                            <?php if (empty($cli['telefono']) && empty($cli['email'])): ?>
                              <span class="text-muted" style="font-style: italic; font-size: 11px;">Sin contacto</span>
                            <?php endif; ?>
                          </td>

                          <!-- Última Compra -->
                          <td class="text-center" data-order="<?= !empty($cli['ultima_compra']) ? strtotime($cli['ultima_compra']) : 0 ?>">
                            <?= $fechaTexto ?>
                          </td>

                          <!-- Días Transcurridos -->
                          <td class="text-center" data-order="<?= $diasNum ?>">
                            <?= $diasTexto ?>
                          </td>

                          <!-- Total Compras -->
                          <td class="text-center" data-order="<?= intval($cli['total_compras']) ?>">
                            <span class="badge" style="background-color: #f1f5f9; color: #334155; font-size: 12px; border: 1px solid #cbd5e1; font-weight: 600;">
                              <?= intval($cli['total_compras']) ?>
                            </span>
                          </td>

                          <!-- Monto Acumulado -->
                          <td class="text-right" style="font-weight: 600; color: #1e293b;" data-order="<?= floatval($cli['total_gastado']) ?>">
                            $ <?= number_format($cli['total_gastado'], 2, ',', '.') ?>
                          </td>

                          <!-- Estado / Semáforo -->
                          <td class="text-center">
                            <?= $badgeSemaforo ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>

  </section>

</div>


<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->

<!-- Modal -->
<div id="modalAgregarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formAgregarCliente">

        <?php CSRF::insertToken(); ?>
        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoCliente" id="nuevoCliente"
                  placeholder="Ingresar nombre *" required>

              </div>

            </div>


            <!-- entrada para documento ID -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-key"></i></span>

                <input type="number" min="0" max="9999999999" class="form-control input-lg" name="nuevoDocumentoId"
                  placeholder="Ingresar documento *" required>

              </div>

            </div>


            <!-- entrada para telefono -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono *"
                  data-inputmask="'mask':'(999) 999-9999'" data-mask required>

              </div>

            </div>


            <!-- entrada para Email -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <!--<input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email" required>-->
                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">

              </div>

            </div>


            <!-- entrada para departamento -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-building"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoDepartamento"
                  placeholder="Ingresar departamento">

              </div>

            </div>


            <!-- entrada para ciudad -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoCiudad" placeholder="Ingresar Ciudad">

              </div>

            </div>


            <!-- entrada para la direccion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-home"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaDireccion"
                  placeholder="Ingresar dirección *" required>

              </div>

            </div>


            <!-- entrada para Tipo de Documento (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>

                <select class="form-control input-lg" name="nuevoTipoDocumento" id="nuevoTipoDocumento">
                  <option value="">Seleccione tipo de documento</option>
                  <?php
                  $tiposDoc = ControladorFactus::ctrMostrarTiposDocumento();
                  foreach ($tiposDoc as $tipo) {
                    $selected = ($tipo['id'] == 3) ? 'selected' : '';
                    echo '<option value="' . $tipo['id'] . '" ' . $selected . '>' . $tipo['nombre'] . '</option>';
                  }
                  ?>
                </select>

              </div>
              <small class="text-muted">Tipo de documento para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para Municipio (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <select class="form-control input-lg" name="nuevoMunicipio" id="nuevoMunicipio">
                  <option value="">Seleccione municipio</option>
                  <option value="11001" selected>Bogotá D.C.</option>
                  <option value="05001">Medellín - Antioquia</option>
                  <option value="76001">Cali - Valle del Cauca</option>
                  <option value="08001">Barranquilla - Atlántico</option>
                  <option value="13001">Cartagena - Bolívar</option>
                  <option value="68001">Bucaramanga - Santander</option>
                  <option value="66001">Pereira - Risaralda</option>
                  <option value="17001">Manizales - Caldas</option>
                  <option value="54001">Cúcuta - Norte de Santander</option>
                  <option value="63001">Armenia - Quindío</option>
                  <option value="73001">Ibagué - Tolima</option>
                  <option value="20001">Valledupar - Cesar</option>
                  <option value="50001">Villavicencio - Meta</option>
                  <option value="19001">Popayán - Cauca</option>
                  <option value="52001">Pasto - Nariño</option>
                </select>

              </div>
              <small class="text-muted">Municipio para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para estatus -->
            <input type="hidden" name="nuevoEstatus" value="nuevo">

            <!-- crear estado clientes -->
            <input type="hidden" name="vistaOrigen" value="clientes">



            <!-- Estatus 
            <div class="form-group"> 
              <label for="editarEstatus">Estatus</label>
              <select class="form-control" name="editarEstatus" id="editarEstatus">
                <option value="contactado">Contactado</option>
                <option value="en espera">En espera</option>
                <option value="interesado">Interesado</option>
                <option value="no interesado">No interesado</option>
              </select>
            </div>
            -->

            <!-- entrada para la fecha naciminiento -->
            <!--
            <div class="form-group">          
            <div class="input-group">              
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha de nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>
             </div>
           </div>
           -->

            <!-- entrada para notas -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaNota" placeholder="Ingresar Nota">

              </div>

            </div>


          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cliente</button>

        </div>

      </form>


      <?php

      $crearCliente = new ControladorClientes();
      $crearCliente->ctrCrearCliente();

      ?>

    </div>

  </div>

</div>


<!--==========================================================================
MODAL EDITAR CLIENTE
===========================================================================-->

<!-- Modal -->
<div id="modalEditarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formEditarCliente">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar cliente</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control input-lg" name="editarCliente" id="editarCliente" required>
                <input type="hidden" id="idCliente" name="idCliente">

              </div>

            </div>


            <!-- entrada para documento ID -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-key"></i></span>

                <input type="number" min="0" class="form-control input-lg" name="editarDocumentoId"
                  id="editarDocumentoId" placeholder="Documento" required>

              </div>

            </div>


            <!-- entrada para telefono -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono"
                  data-inputmask="'mask':'(999) 999-9999'" data-mask placeholder="Celular" required>

              </div>

            </div>


            <!-- entrada para Email -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>

                <!--<input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" required>-->
                <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail"
                  placeholder="Correo Electrónico">

              </div>

            </div>


            <!-- entrada para la departamento -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-building"></i></span>

                <input type="text" class="form-control input-lg" name="editarDepartamento" id="editarDepartamento"
                  placeholder="Departamento">

              </div>

            </div>


            <!-- entrada para la ciudad -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="editarCiudad" id="editarCiudad"
                  placeholder="Ciudad">

              </div>

            </div>


            <!-- entrada para la direccion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-home"></i></span>

                <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion"
                  placeholder="Dirección" required>

              </div>

            </div>


            <!-- entrada para Tipo de Documento (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-id-card"></i></span>

                <select class="form-control input-lg" name="editarTipoDocumento" id="editarTipoDocumento">
                  <option value="">Seleccione tipo de documento</option>
                  <?php
                  if (!isset($tiposDoc)) {
                    $tiposDoc = ControladorFactus::ctrMostrarTiposDocumento();
                  }
                  foreach ($tiposDoc as $tipo) {
                    echo '<option value="' . $tipo['id'] . '">' . $tipo['nombre'] . '</option>';
                  }
                  ?>
                </select>

              </div>
              <small class="text-muted">Tipo de documento para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para Municipio (DIAN) -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <select class="form-control input-lg" name="editarMunicipio" id="editarMunicipio">
                  <option value="">Seleccione municipio</option>
                  <option value="11001">Bogotá D.C.</option>
                  <option value="05001">Medellín - Antioquia</option>
                  <option value="76001">Cali - Valle del Cauca</option>
                  <option value="08001">Barranquilla - Atlántico</option>
                  <option value="13001">Cartagena - Bolívar</option>
                  <option value="68001">Bucaramanga - Santander</option>
                  <option value="66001">Pereira - Risaralda</option>
                  <option value="17001">Manizales - Caldas</option>
                  <option value="54001">Cúcuta - Norte de Santander</option>
                  <option value="63001">Armenia - Quindío</option>
                  <option value="73001">Ibagué - Tolima</option>
                  <option value="20001">Valledupar - Cesar</option>
                  <option value="50001">Villavicencio - Meta</option>
                  <option value="19001">Popayán - Cauca</option>
                  <option value="52001">Pasto - Nariño</option>
                </select>

              </div>
              <small class="text-muted">Municipio para facturación electrónica (DIAN)</small>

            </div>


            <!-- entrada para la fecha naciminiento -->
            <!--
            <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input type="text" class="form-control input-lg" name="editarFechaNacimiento" id="editarFechaNacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>
             </div>
           </div>
           -->


            <!-- entrada para estado -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                <select class="form-control input-lg" name="editarEstado" id="editarEstado" required>
                  <option value="">Seleccionar estado</option>

                  <?php
                  $item = null;
                  $valor = null;
                  $estadosS = ControladorEstadosClientes::ctrMostrarEstadosClientes($item, $valor);
                  foreach ($estadosS as $key => $v) {
                    echo '<option value="' . e($v["id"]) . '">' . e($v["nombre"]) . '</option>';
                  }
                  ?>

                </select>
              </div>
            </div>

            <!-- entrada para nota -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                <input type="text" class="form-control input-lg" name="editarNota" id="editarNota" placeholder="Notas">

              </div>

            </div>


            <!-- entrada para estatus -->
            <!--<input type="hidden" name="editarEstatus" id="editarEstatus">-->



          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      </form>

    </div>

  </div>

</div>


<?php
/*
$eliminarCliente = new ControladorClientes();
$eliminarCliente->ctrEliminarCliente();
*/
?>



<!-- Choices.js para Campo estatus-->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


<!-- Filtro estatus tabla 1 -->
<script>
  function filterTable1() {
    var filtro = $('#filtroEstatus1').val();
    var $rows = $('.tablas1 tbody tr').not('.fila-detalle-row');
    $rows.each(function () {
      var $mainRow = $(this);
      var $detalleRow = $mainRow.next('.fila-detalle-row');
      var estado = $mainRow.find('select.cambiarEstatus').val();
      if (filtro === "" || estado === filtro) {
        $mainRow.show();
        $detalleRow.show();
      } else {
        $mainRow.hide();
        $detalleRow.hide();
      }
    });
  }
  $(document).ready(function () {
    // Ejecutar filtro al cargar si hay valor
    filterTable1();
    // Si usas AJAX para cambiar estatus, llama a filterTable1() después de actualizar
  });
  $(document).ready(function () {
    // Mover modales al body para evitar conflictos de posicionamiento
    if ($('#modalImportarClientes').length) $('#modalImportarClientes').appendTo('body');
    if ($('#modalGestionarEstados').length) $('#modalGestionarEstados').appendTo('body');
    if ($('#modalEditarEstado').length) $('#modalEditarEstado').appendTo('body');
  });
</script>





<!--=====================================
MODAL IMPORTAR CLIENTES DESDE CSV
======================================-->

<div id="modalImportarClientes" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Exportar / Importar Clientes</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="alert alert-info">

              <h4><i class="icon fa fa-info"></i> Instrucciones:</h4>

              <ol>
                <li>Descarga la plantilla CSV haciendo clic en el botón de abajo</li>
                <li>Completa los datos de los clientes (Los campos marcados con * son obligatorios)</li>
                <li><strong>Tipo Persona:</strong> Use "Persona natural" o "Persona juridica"</li>
                <li><strong>Tipo Documento:</strong> Use CC, CE, DE, NIT, NUIP, PA, PASAPORTE, PEP, RC, TE o TI</li>
                <li><strong>Persona Jurídica:</strong> Requiere que el tipo de documento sea NIT obligatoriamente</li>
                <li><strong>Dígito de Verificación:</strong> Es obligatorio cuando el tipo de documento es NIT</li>
                <li><strong>Municipio:</strong> Use el formato "Municipio - Departamento" (Ej: Medellin - Antioquia)
                </li>
                <li>Sube el archivo CSV completado</li>
              </ol>

            </div>


            <!-- BOTÓN PARA DESCARGAR PLANTILLA -->

            <div class="form-group text-center">

              <a href="vistas/modulos/descargar-plantilla-clientes.php" class="btn btn-info">

                <i class="fa fa-download"></i> Descargar Plantilla CSV para Clientes

              </a>

            </div>

            <hr>

            <!-- ENTRADA PARA SUBIR ARCHIVO CSV -->

            <div class="form-group">

              <label>Seleccionar archivo CSV:</label>

              <input type="file" class="form-control" name="archivoCSV" accept=".csv" required>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-success">Importar Clientes</button>

        </div>

        <?php
        $importar = new ControladorClientes();
        $importar->ctrImportarClientes();
        ?>

      </form>

    </div>

  </div>

</div>


<!--=====================================
MODAL GESTIONAR ESTADOS
======================================-->

<div id="modalGestionarEstados" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Gestionar Estados de Clientes</h4>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <!-- Formulario agregar estado -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nuevo Estado</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarEstado">

              <?php CSRF::insertToken(); ?>

              <!-- Campo oculto para indicar origen -->
              <input type="hidden" name="origenModal" value="clientes">

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoEstadoNombre" placeholder="Nombre del estado *"
                      required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <input type="color" class="form-control" name="nuevoEstadoColor" value="#3c8dbc"
                      style="height: 34px;">
                  </div>
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- Lista de estados -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Estados Existentes</h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped tablaEstadosClientes">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $estados = ControladorEstadosClientes::ctrMostrarEstadosClientes(null, null);
                  $estadosBloqueados = ['nuevo', 'leads'];

                  foreach ($estados as $key => $value) {
                    $nombreMinuscula = strtolower(trim($value["nombre"]));
                    $esBloqueado = in_array($nombreMinuscula, $estadosBloqueados);

                    echo '<tr>
                      <td>' . e($key + 1) . '</td>
                      <td>
                        <span class="badge" style="background-color: ' . e($value["color"]) . '">' . e($value["nombre"]) . '</span>';
                    if ($esBloqueado) {
                      echo ' <i class="fa fa-lock text-muted" title="Estado protegido del sistema" style="margin-left: 4px;"></i>';
                    }
                    echo '</td>
                      <td><input type="color" value="' . e($value["color"]) . '" disabled style="width: 50px;"></td>
                      <td>';

                    if ($esBloqueado) {
                      echo '<button class="btn btn-default btn-xs" disabled style="opacity: 0.5; cursor: not-allowed; margin-right: 2px;" title="Este estado no se puede editar (protegido por el sistema)">
                              <i class="fa fa-lock"></i>
                            </button>
                            <button class="btn btn-default btn-xs" disabled style="opacity: 0.5; cursor: not-allowed;" title="Este estado no se puede eliminar (protegido por el sistema)">
                              <i class="fa fa-ban"></i>
                            </button>';
                    } else {
                      echo '<button class="btn btn-warning btn-xs btnEditarEstado"
                              data-id="' . e($value["id"]) . '"
                              data-nombre="' . e($value["nombre"]) . '"
                              data-color="' . e($value["color"]) . '"
                              data-orden="' . e($value["orden"]) . '"
                              data-toggle="modal"
                              data-target="#modalEditarEstado"
                              title="Editar estado">
                              <i class="fa fa-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-xs btnEliminarEstado" idEstado="' . e($value["id"]) . '" nombreEstado="' . e($value["nombre"]) . '" title="Eliminar estado">
                              <i class="fa fa-times"></i>
                            </button>';
                    }

                    echo '</td>
                    </tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR ESTADO
======================================-->

<div id="modalEditarEstado" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Estado</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-flag"></i></span>
                <input type="text" class="form-control input-lg" name="editarEstadoNombre" id="editarEstadoNombre"
                  required>
                <input type="hidden" name="idEstado" id="idEstado">
              </div>
            </div>

            <!-- ENTRADA PARA EL COLOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-paint-brush"></i></span>
                <input type="color" class="form-control input-lg" name="editarEstadoColor" id="editarEstadoColor"
                  required style="height: 46px;">
              </div>
            </div>

            <!-- Campo oculto para el orden -->
            <input type="hidden" name="editarEstadoOrden" id="editarEstadoOrden">

            <!-- Campo oculto para indicar origen clientes -->
            <input type="hidden" name="origenEdicion" value="clientes">

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

      </form>

    </div>

  </div>

</div>

<!-- Script para recargar la página después de agregar un estado -->
<script>
  $(document).ready(function () {
    // Detectar si se agregó un estado exitosamente
    <?php if (isset($_GET["estadoCreado"]) && $_GET["estadoCreado"] == "ok"): ?>
      swal({
        type: "success",
        title: "¡El estado ha sido guardado correctamente!",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      }).then(function (result) {
        if (result.value) {
          window.location = "clientes";
        }
      });
      <?php
    endif; ?>

    <?php if (isset($_GET["estadoCreado"]) && $_GET["estadoCreado"] == "error"): ?>
      swal({
        type: "error",
        title: "¡El estado no pudo ser guardado!",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      });
      <?php
    endif; ?>
  });
</script>

<?php
// Procesar acciones de estados AL FINAL de la página para evitar romper el HTML
$crearEstado = new ControladorEstadosClientes();
$crearEstado->ctrCrearEstado();

$editarEstado = new ControladorEstadosClientes();
$editarEstado->ctrEditarEstado();

$eliminarEstado = new ControladorEstadosClientes();
$eliminarEstado->ctrEliminarEstado();

$eliminarCliente = new ControladorClientes();
$eliminarCliente->ctrEliminarCliente();
?>