<?php
if (!puedeVer('crm')) {
    echo '<script>window.location = "inicio";</script>';
    return;
}
$urlActual = "crm";

// Inicializar controladores necesarios
$crmController = new ControladorCRM();
$crmController->ctrCrearLead();
$crmController->ctrEditarLead();
$crmController->ctrEliminarLead();

$crmController->ctrCrearEtapa();
$crmController->ctrEditarEtapa();
$crmController->ctrEliminarEtapa();
?>

<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* Diseño General del Módulo CRM */
.crm-wrapper {
    font-family: 'Inter', sans-serif !important;
    background-color: #f4f6f9;
}

/* Cabecera de Métricas */
.crm-metrics-container {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.crm-metric-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 15px 20px;
    flex: 1;
    min-width: 200px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-left: 4px solid #3c8dbc;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.crm-metric-title {
    font-size: 11px;
    text-transform: uppercase;
    color: #888;
    font-weight: 600;
    margin-bottom: 5px;
}

.crm-metric-value {
    font-size: 20px;
    font-weight: 700;
    color: #333;
}

/* Área de Filtros */
.crm-filters-bar {
    background: #ffffff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.crm-filters-left {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.crm-filters-right {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Correcciones de visualización y alineación para Select2 dentro de un input-group flex */
.input-group[style*="display: flex"] .select2-container {
    flex: 1 !important;
    min-width: 0 !important;
    width: auto !important;
}
.input-group[style*="display: flex"] .select2-selection--single {
    height: 34px !important;
    border-color: #d2d6de !important;
    display: flex !important;
    align-items: center !important;
}
/* Si el select2 no es el último elemento (hay un botón al lado derecho) */
.input-group[style*="display: flex"] .select2-container:not(:last-child) .select2-selection--single {
    border-radius: 0 !important;
}
/* Si el select2 es el último elemento (no hay botón al lado derecho) */
.input-group[style*="display: flex"] .select2-container:last-child .select2-selection--single {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    border-top-right-radius: 4px !important;
    border-bottom-right-radius: 4px !important;
}
.input-group[style*="display: flex"] .select2-selection__arrow {
    height: 32px !important;
}

/* Reglas para select estándar dentro de un input-group flex */
.input-group[style*="display: flex"] select.form-control {
    flex: 1 !important;
    min-width: 0 !important;
    width: auto !important;
    height: 34px !important;
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
.input-group[style*="display: flex"] select.form-control:last-child {
    border-top-right-radius: 4px !important;
    border-bottom-right-radius: 4px !important;
}

/* Tablero Kanban */
.crm-kanban-board {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 20px;
    align-items: flex-start;
}

.crm-kanban-column {
    background-color: #ebecf0;
    border-radius: 10px;
    width: 280px;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    max-height: 80vh;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
}

.crm-column-header {
    padding: 15px;
    font-weight: 600;
    font-size: 14px;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    border-bottom: 2px solid rgba(0,0,0,0.05);
}

.crm-column-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.crm-column-color-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.crm-column-count {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    color: #666;
    font-weight: 700;
}

.crm-column-value-total {
    padding: 4px 15px;
    font-size: 11px;
    font-weight: 600;
    color: #666;
    background: rgba(255,255,255,0.5);
    border-bottom: 1px solid rgba(0,0,0,0.02);
}

.crm-cards-container {
    padding: 10px;
    overflow-y: auto;
    flex-grow: 1;
    min-height: 150px;
}

.crm-cards-container.dragover {
    background-color: rgba(60, 141, 188, 0.1);
    border: 2px dashed #3c8dbc;
    border-radius: 0 0 10px 10px;
}

/* Tarjetas (Leads) */
.crm-lead-card {
    background: #ffffff;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    cursor: grab;
    border-left: 4px solid #cbd5e0;
    transition: all 0.2s ease;
    user-select: none;
    position: relative;
}

.crm-lead-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(0,0,0,0.1);
}

.crm-lead-card.dragging {
    opacity: 0.4;
    cursor: grabbing;
}

.crm-lead-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.crm-lead-client {
    font-weight: 700;
    font-size: 13px;
    color: #1a202c;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 170px;
}

.crm-lead-badge-priority {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}

/* Estilos de Temperatura */
.crm-badge-frio {
    background-color: #ebf8ff;
    color: #2b6cb0;
}
.crm-badge-tibio {
    background-color: #fffaf0;
    color: #dd6b20;
}
.crm-badge-caliente {
    background-color: #fff5f5;
    color: #e53e3e;
}

/* Bordes Dinámicos */
.crm-card-frio { border-left-color: #3182ce; }
.crm-card-tibio { border-left-color: #dd6b20; }
.crm-card-caliente { border-left-color: #e53e3e; }

.crm-lead-title {
    font-size: 12px;
    color: #4a5568;
    margin-bottom: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.crm-lead-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f7fafc;
    padding-top: 8px;
    font-size: 11px;
    color: #718096;
}

.crm-lead-value {
    font-weight: 700;
    font-size: 13px;
    color: #2d3748;
}

/* Badge Distinción Órdenes vs Leads Manuales */
.crm-badge-type {
    font-size: 9px;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.crm-badge-order {
    background-color: #e3faf2;
    color: #0ca678;
    border: 1px solid #a3e9d2;
}

.crm-badge-manual {
    background-color: #f1f3f5;
    color: #495057;
    border: 1px solid #dee2e6;
}

/* Leads Archivados */
.crm-lead-card.crm-lead-archived {
    opacity: 0.55;
    filter: grayscale(80%);
}

/* Agregar botón en columna */
.crm-column-add-btn {
    width: 100%;
    background: transparent;
    border: none;
    padding: 10px;
    text-align: center;
    color: #6c757d;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.crm-column-add-btn:hover {
    background: rgba(0,0,0,0.05);
    color: #333;
}
</style>

<div class="content-wrapper crm-wrapper">

  <!-- CABECERA -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1><i class="fa fa-filter text-primary"></i> Pipeline de Ventas (CRM)</h1>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTENIDO PRINCIPAL -->
  <section class="content">
    <div class="container-fluid">

      <!-- MÉTIRCAS CLAVE -->
      <div class="crm-metrics-container">
        <?php
        $leadsMétricas = ControladorCRM::ctrMostrarLeads(null, null);
        $totalNegociacion = 0;
        $montoNegociacion = 0;
        $totalGanados = 0;
        $montoGanados = 0;
        $totalPerdidos = 0;
        $montoPerdidos = 0;

        foreach ($leadsMétricas as $l) {
            if ($l["etapa"] == "Facturado") {
                $totalGanados++;
                $montoGanados += $l["valor_estimado"];
            } else if ($l["etapa"] == "Perdido") {
                $totalPerdidos++;
                $montoPerdidos += $l["valor_estimado"];
            } else {
                $totalNegociacion++;
                $montoNegociacion += $l["valor_estimado"];
            }
        }
        ?>
        <div class="crm-metric-card" style="border-left-color: #3c8dbc;">
          <div>
            <div class="crm-metric-title">En Seguimiento / Pipeline</div>
            <div class="crm-metric-value" id="metricTotalSeguimiento"><?php echo $totalNegociacion; ?> Oportunidades</div>
          </div>
          <i class="fa fa-folder-open fa-2x text-muted" style="opacity: 0.4;"></i>
        </div>

        <div class="crm-metric-card" style="border-left-color: #00c0ef;">
          <div>
            <div class="crm-metric-title">Monto Estimado en Trámite</div>
            <div class="crm-metric-value" id="metricMontoSeguimiento">$ <?php echo number_format($montoNegociacion, 2); ?></div>
          </div>
          <i class="fa fa-money fa-2x text-muted" style="opacity: 0.4;"></i>
        </div>

        <div class="crm-metric-card" style="border-left-color: #00a65a;">
          <div>
            <div class="crm-metric-title">Leads Facturados (Cierres)</div>
            <div class="crm-metric-value" id="metricTotalFacturado"><?php echo $totalGanados; ?> Ganados</div>
          </div>
          <i class="fa fa-trophy fa-2x text-muted" style="opacity: 0.4;"></i>
        </div>

        <div class="crm-metric-card" style="border-left-color: #f39c12;">
          <div>
            <div class="crm-metric-title">Monto Facturado</div>
            <div class="crm-metric-value" id="metricMontoFacturado">$ <?php echo number_format($montoGanados, 2); ?></div>
          </div>
          <i class="fa fa-shopping-bag fa-2x text-muted" style="opacity: 0.4;"></i>
        </div>

        <div class="crm-metric-card" style="border-left-color: #dd4b39;">
          <div>
            <div class="crm-metric-title">Leads Perdidos</div>
            <div class="crm-metric-value" id="metricTotalPerdidos"><?php echo $totalPerdidos; ?> Perdidos</div>
            <div style="font-size: 14px; font-weight: 600; color: #777; margin-top: 2px;" id="metricMontoPerdidos">$ <?php echo number_format($montoPerdidos, 2); ?></div>
          </div>
          <i class="fa fa-thumbs-down fa-2x text-muted" style="opacity: 0.4;"></i>
        </div>
      </div>

      <!-- BARRA DE FILTROS -->
      <div class="crm-filters-bar">
        <div class="crm-filters-left">
          
          <!-- Filtro por Vendedor -->
          <div style="display: flex; align-items: center; gap: 8px;">
            <span><b>Vendedor:</b></span>
            <div class="input-group" style="width: 200px;">
              <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                <i class="fa fa-user text-primary"></i>
              </span>
              <select class="form-control select2" id="filtroVendedorCRM" style="width: 100%;">
                <option value="">Mostrar Todos</option>
                <?php
                $usuariosList = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                foreach ($usuariosList as $u) {
                    if ($u["perfil"] == "_SystemMaster_") continue;
                    echo '<option value="' . e($u["nombre"]) . '">' . e($u["nombre"]) . '</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <!-- Filtro por Temperatura -->
          <div style="display: flex; align-items: center; gap: 8px;">
            <span><b>Temperatura:</b></span>
            <div class="input-group" style="width: 180px;">
              <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
                <i class="fa fa-thermometer-half text-primary"></i>
              </span>
              <select class="form-control" id="filtroPrioridadCRM" style="width: 100%;">
                <option value="">Mostrar Todas</option>
                <option value="frio">❄️ Frío</option>
                <option value="tibio">☀️ Tibio</option>
                <option value="caliente">🔥 Caliente</option>
              </select>
            </div>
          </div>

          <!-- Buscador -->
          <div class="input-group" style="width: 250px;">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control" id="buscadorCRM" placeholder="Buscar cliente o negocio...">
          </div>

        </div>

        <div class="crm-filters-right">
          <!-- Toggle Archivados -->
          <div class="checkbox" style="margin: 0px;">
            <label style="font-weight: 600;">
              <input type="checkbox" id="toggleArchivadosCRM"> Mostrar Historial/Archivados
            </label>
          </div>

          <!-- Botones Gestión -->
          <?php if(puedeAccion('crm', 'crear')): ?>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarLead">
              <i class="fa fa-plus"></i> Nuevo Lead
            </button>
          <?php else: ?>
            <button class="btn btn-primary" disabled title="No tienes permisos para crear leads">
              <i class="fa fa-plus"></i> Nuevo Lead
            </button>
          <?php endif; ?>
          <?php if(puedeAccion('crm', 'editar')): ?>
            <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarEtapas">
              <i class="fa fa-cog"></i> Etapas
            </button>
          <?php else: ?>
            <button class="btn btn-default" disabled title="No tienes permisos para gestionar etapas">
              <i class="fa fa-cog"></i> Etapas
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- TABLERO KANBAN -->
      <div class="crm-kanban-board">
        <?php
        $etapas = ControladorCRM::ctrMostrarEtapas(null, null);
        $todosLeads = ControladorCRM::ctrMostrarLeads(null, null);

        foreach ($etapas as $etapa):
            // Filtrar leads para esta columna
            $leadsColumna = array_filter($todosLeads, function($l) use ($etapa) {
                return $l["etapa"] == $etapa["nombre"];
            });

            // Inicialmente, en las columnas Facturado y Perdido no hay leads visibles
            $esEtapaArchivada = ($etapa["nombre"] == "Facturado" || $etapa["nombre"] == "Perdido");
            $conteoInicial = $esEtapaArchivada ? 0 : count($leadsColumna);

            // Calcular el valor acumulado inicial de la columna
            $valorAcumuladoInicial = 0;
            if (!$esEtapaArchivada) {
                foreach ($leadsColumna as $lc) {
                    $valorAcumuladoInicial += $lc["valor_estimado"];
                }
            }
        ?>
          <div class="crm-kanban-column" data-etapa="<?php echo $etapa["nombre"]; ?>">
            
            <!-- Cabecera de Columna -->
            <div class="crm-column-header" style="border-top: 3px solid <?php echo $etapa["color"]; ?>;">
              <div class="crm-column-title-wrapper">
                <span class="crm-column-color-indicator" style="background-color: <?php echo $etapa["color"]; ?>;"></span>
                <span><?php echo $etapa["nombre"]; ?></span>
              </div>
              <div style="display: flex; gap: 5px; align-items: center;">
                <span class="crm-column-count"><?php echo $conteoInicial; ?></span>
              </div>
            </div>

            <!-- Total Monetario en la Columna -->
            <div class="crm-column-value-total">
               Acumulado: <strong>$ <?php echo number_format($valorAcumuladoInicial, 2); ?></strong>
            </div>

            <!-- Contenedor de Tarjetas -->
            <div class="crm-cards-container" data-etapa="<?php echo $etapa["nombre"]; ?>">
              <?php foreach ($leadsColumna as $lead): 
                  $claseTemperatura = "crm-card-tibio";
                  if ($lead["prioridad"] == "frio") $claseTemperatura = "crm-card-frio";
                  else if ($lead["prioridad"] == "caliente") $claseTemperatura = "crm-card-caliente";
              ?>
                <div class="crm-lead-card <?php echo $claseTemperatura; ?> <?php echo ($lead["etapa"] == "Facturado" || $lead["etapa"] == "Perdido") ? 'crm-lead-archived' : ''; ?>" 
                     data-id="<?php echo $lead["id"]; ?>" 
                     data-vendedor="<?php echo htmlspecialchars($lead["nombre_vendedor"]); ?>"
                     data-prioridad="<?php echo $lead["prioridad"]; ?>"
                     data-cliente="<?php echo htmlspecialchars($lead["nombre_cliente"]); ?>"
                     data-titulo="<?php echo htmlspecialchars($lead["titulo"]); ?>"
                     draggable="<?php echo puedeAccion('crm', 'editar') ? 'true' : 'false'; ?>"
                     <?php
                     $estilosCard = [];
                     if (!puedeAccion('crm', 'editar')) {
                         $estilosCard[] = 'cursor: default;';
                     }
                     if ($lead["etapa"] == "Facturado" || $lead["etapa"] == "Perdido") {
                         $estilosCard[] = 'display: none;';
                     }
                     if (!empty($estilosCard)) {
                         echo 'style="' . implode(' ', $estilosCard) . '"';
                     }
                     ?>>
                  
                  <div class="crm-lead-card-header">
                    <h4 class="crm-lead-client"><?php echo $lead["nombre_cliente"]; ?></h4>
                    <span class="crm-lead-badge-priority crm-badge-<?php echo $lead["prioridad"]; ?>">
                      <?php 
                      if ($lead["prioridad"] == "frio") echo "❄️ Frío";
                      else if ($lead["prioridad"] == "tibio") echo "☀️ Tibio";
                      else if ($lead["prioridad"] == "caliente") echo "🔥 Caliente";
                      ?>
                    </span>
                  </div>

                  <div class="crm-lead-title" title="<?php echo htmlspecialchars($lead["titulo"]); ?>">
                    <?php echo $lead["titulo"]; ?>
                  </div>

                  <!-- Footer con Metadatos -->
                  <div class="crm-lead-card-footer">
                    <div>
                      <?php if (!empty($lead["codigo_orden"])): ?>
                        <span class="crm-badge-type crm-badge-order" title="Creado desde Órdenes de Venta">
                          <i class="fa fa-shopping-cart"></i> Orden #<?php echo $lead["codigo_orden"]; ?>
                        </span>
                      <?php else: ?>
                        <span class="crm-badge-type crm-badge-manual" title="Lead creado manualmente">
                          <i class="fa fa-user"></i> Prospecto
                        </span>
                      <?php endif; ?>
                    </div>
                    <span class="crm-lead-value">$ <?php echo number_format($lead["valor_estimado"], 2); ?></span>
                  </div>

                </div>
              <?php endforeach; ?>
            </div>

            <!-- Botón Agregar en columna -->
            <?php if(puedeAccion('crm', 'crear')): ?>
              <button class="crm-column-add-btn btnCrearLeadDesdeColumna" data-etapa="<?php echo $etapa["nombre"]; ?>">
                <i class="fa fa-plus-circle"></i> Agregar Lead
              </button>
            <?php else: ?>
              <button class="crm-column-add-btn" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tienes permisos para crear leads">
                <i class="fa fa-plus-circle"></i> Agregar Lead
              </button>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

</div>

<!--=====================================
MODAL AGREGAR LEAD
======================================-->
<div id="modalAgregarLead" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <?php CSRF::insertToken(); ?>

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" style="color: white !important;"><i class="fa fa-plus"></i> Registrar Lead Comercial</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <!-- Título de Oportunidad -->
            <div class="form-group">
              <label>Título del Negocio *</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tasks"></i></span>
                <input type="text" class="form-control" name="nuevoLeadTitulo" placeholder="Ej: Venta de Equipos Oficina" required>
              </div>
            </div>

            <!-- Cliente -->
            <div class="form-group">
              <label>Cliente *</label>
              <div class="input-group" style="display: flex; width: 100%;">
                <span class="input-group-addon" style="width: 40px;"><i class="fa fa-user"></i></span>
                <select class="form-control" name="nuevoLeadCliente" id="selectLeadCliente" required style="width: 100%;">
                  <option value="">Seleccionar cliente</option>
                  <?php
                  $clientes = ControladorClientes::ctrMostrarClientes(null, null);
                  foreach ($clientes as $c) {
                      echo '<option value="' . $c["id"] . '">' . $c["nombre"] . '</option>';
                  }
                  ?>
                </select>
                <!-- Botón rápido para agregar cliente -->
                <span class="input-group-btn" style="width: auto;">
                  <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal" title="Registrar nuevo cliente" style="border-radius: 0 4px 4px 0;"><i class="fa fa-plus"></i></button>
                </span>
              </div>
            </div>

            <div class="row">
              <!-- Valor Estimado -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Monto Estimado ($) *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-money"></i></span>
                    <input type="number" class="form-control" name="nuevoLeadValor" min="0" step="0.01" value="0.00" required>
                  </div>
                </div>
              </div>

              <!-- Prioridad -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Temperatura (Prioridad) *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                    <select class="form-control" name="nuevoLeadPrioridad" required>
                      <option value="frio">❄️ Frío</option>
                      <option value="tibio" selected>☀️ Tibio</option>
                      <option value="caliente">🔥 Caliente</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- Vendedor Responsable -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Vendedor Responsable *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>
                    <select class="form-control" name="nuevoLeadVendedor" required>
                      <?php
                      foreach ($usuariosList as $u) {
                          if ($u["perfil"] == "_SystemMaster_") continue;
                          $selected = ($u["id"] == $_SESSION["id"]) ? "selected" : "";
                          echo '<option value="' . $u["id"] . '" ' . $selected . '>' . $u["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Fecha Cierre -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Cierre Estimado</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="nuevoLeadFechaCierre">
                  </div>
                </div>
              </div>
            </div>

            <!-- Etapa del Pipeline -->
            <div class="form-group">
              <label>Etapa del Pipeline *</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-arrow-right"></i></span>
                <select class="form-control" name="nuevoLeadEtapa" id="nuevoLeadEtapa" required>
                  <?php
                  foreach ($etapas as $et) {
                      echo '<option value="' . $et["nombre"] . '">' . $et["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Notas -->
            <div class="form-group">
              <label>Observaciones / Bitácora de seguimiento</label>
              <textarea class="form-control" name="nuevoLeadNotas" rows="3" placeholder="Escribe notas sobre el primer contacto o estado del negocio..."></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Registrar Lead</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR LEAD
======================================-->
<div id="modalEditarLead" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <?php CSRF::insertToken(); ?>

        <div class="modal-header" style="background:#f39c12; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" style="color: white !important;"><i class="fa fa-pencil"></i> Editar Oportunidad Comercial</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <input type="hidden" name="editarLeadId" id="editarLeadId">

            <!-- Título de Oportunidad -->
            <div class="form-group">
              <label>Título del Negocio *</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tasks"></i></span>
                <input type="text" class="form-control" name="editarLeadTitulo" id="editarLeadTitulo" required>
              </div>
            </div>

            <!-- Cliente -->
            <div class="form-group">
              <label>Cliente *</label>
              <div class="input-group" style="display: flex; width: 100%;">
                <span class="input-group-addon" style="width: 40px;"><i class="fa fa-user"></i></span>
                <select class="form-control" name="editarLeadCliente" id="editarLeadCliente" required style="width: 100%;">
                  <?php
                  foreach ($clientes as $c) {
                      echo '<option value="' . $c["id"] . '">' . $c["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="row">
              <!-- Valor Estimado -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Monto ($) *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-money"></i></span>
                    <input type="number" class="form-control" name="editarLeadValor" id="editarLeadValor" min="0" step="0.01" required>
                  </div>
                </div>
              </div>

              <!-- Prioridad -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Temperatura *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                    <select class="form-control" name="editarLeadPrioridad" id="editarLeadPrioridad" required>
                      <option value="frio">❄️ Frío</option>
                      <option value="tibio">☀️ Tibio</option>
                      <option value="caliente">🔥 Caliente</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- Vendedor Responsable -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Vendedor Responsable *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>
                    <select class="form-control" name="editarLeadVendedor" id="editarLeadVendedor" required>
                      <?php
                      foreach ($usuariosList as $u) {
                          if ($u["perfil"] == "_SystemMaster_") continue;
                          echo '<option value="' . $u["id"] . '">' . $u["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Fecha Cierre -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Cierre Estimado</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="editarLeadFechaCierre" id="editarLeadFechaCierre">
                  </div>
                </div>
              </div>
            </div>

            <!-- Etapa -->
            <div class="form-group">
              <label>Etapa del Pipeline *</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-arrow-right"></i></span>
                <select class="form-control" name="editarLeadEtapa" id="editarLeadEtapa" required>
                  <?php
                  foreach ($etapas as $et) {
                      echo '<option value="' . $et["nombre"] . '">' . $et["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Notas -->
            <div class="form-group">
              <label>Observaciones / Bitácora de seguimiento</label>
              <textarea class="form-control" name="editarLeadNotas" id="editarLeadNotas" rows="4"></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
          <button type="button" class="btn btn-default" data-dismiss="modal">Salir</button>
          <?php if(puedeAccion('crm', 'editar')): ?>
            <button type="submit" class="btn btn-warning" style="color: white !important;">Guardar Cambios</button>
          <?php else: ?>
            <button type="submit" class="btn btn-warning" style="color: white !important;" disabled title="No tienes permisos para editar leads">Guardar Cambios</button>
          <?php endif; ?>
          <?php if(puedeAccion('crm', 'eliminar')): ?>
            <button type="button" class="btn btn-danger btnEliminarLeadCRM" style="font-weight: 500; border-radius: 6px;">
              <i class="fa fa-trash mr-1"></i> Eliminar Lead
            </button>
          <?php else: ?>
            <button type="button" class="btn btn-danger" style="font-weight: 500; border-radius: 6px;" disabled title="No tienes permisos para eliminar leads">
              <i class="fa fa-trash mr-1"></i> Eliminar Lead
            </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL GESTIONAR ETAPAS (COLUMNAS)
======================================-->
<div id="modalGestionarEtapas" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" style="color: white !important;"><i class="fa fa-cog"></i> Gestionar Etapas del CRM</h4>
      </div>

      <div class="modal-body">
        
        <!-- Formulario agregar etapa -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nueva Etapa Personalizada</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarEtapaCRM">
              <?php CSRF::insertToken(); ?>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevaEtapaNombre" placeholder="Nombre de la etapa *" required>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <input type="color" class="form-control" name="nuevaEtapaColor" value="#3c8dbc" title="Color de acento">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <select class="form-control" name="nuevaEtapaPosicion" title="Posición de la etapa">
                      <option value="ultimo">Al final</option>
                      <option value="primero">Al inicio</option>
                      <?php
                      foreach ($etapas as $key => $value) {
                          echo '<option value="' . $value["orden"] . '">Después de: ' . e($value["nombre"]) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Lista de etapas -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Etapas Existentes</h3>
          </div>
          <div class="panel-body">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Color</th>
                  <th>Tipo</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($etapas as $key => $value) {
                  $badgeTipo = ($value["editable"] == 0) ? '<span class="label label-primary">Sistema (Protegida)</span>' : '<span class="label label-default">Personalizada</span>';
                  
                  $btnAcciones = '';
                  if ($value["editable"] == 1) {
                      $btnAcciones = '
                        <button class="btn btn-warning btn-xs btnEditarEtapaCRM"
                          data-id="' . $value["id"] . '"
                          data-nombre="' . $value["nombre"] . '"
                          data-color="' . $value["color"] . '"
                          data-orden="' . $value["orden"] . '"
                          data-toggle="modal"
                          data-target="#modalEditarEtapaCRM">
                          <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btnEliminarEtapaCRM" idEtapa="' . $value["id"] . '" nombreEtapa="' . $value["nombre"] . '"><i class="fa fa-times"></i></button>
                      ';
                  } else {
                      $btnAcciones = '<span class="text-muted"><i class="fa fa-lock"></i> Bloqueada</span>';
                  }

                  echo '<tr>
                      <td>' . ($value["orden"]) . '</td>
                      <td><span class="badge" style="background-color: ' . $value["color"] . '; font-size: 13px;">' . $value["nombre"] . '</span></td>
                      <td><input type="color" value="' . $value["color"] . '" disabled style="width: 50px;"></td>
                      <td>' . $badgeTipo . '</td>
                      <td>' . $btnAcciones . '</td>
                    </tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR ETAPA
======================================-->
<div id="modalEditarEtapaCRM" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <?php CSRF::insertToken(); ?>

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" style="color: white !important;">Editar Etapa Personalizada</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <input type="hidden" name="idEtapa" id="editarIdEtapa">
            <input type="hidden" name="editarEtapaOrden" id="editarEtapaOrden">

            <div class="form-group">
              <label>Nombre de la etapa *</label>
              <input type="text" class="form-control" name="editarEtapaNombre" id="editarEtapaNombre" required>
            </div>

            <div class="form-group">
              <label>Color</label>
              <input type="color" class="form-control" name="editarEtapaColor" id="editarEtapaColor">
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->

<div id="modalAgregarCliente" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

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

            <!-- Fila 1: Nombre y Documento -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre Completo *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="nuevoCliente" placeholder="Nombre del cliente" required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Documento *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input type="number" min="0" class="form-control" name="nuevoDocumentoId" placeholder="Número de documento" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 2: Teléfono y Email -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Teléfono *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control" name="nuevoTelefono" placeholder="(300) 123-4567" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control" name="nuevoEmail" placeholder="correo@ejemplo.com">
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 3: Municipio -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Municipio *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <select class="form-control" name="nuevoMunicipio" required>
                      <option value="">-- Seleccionar Municipio --</option>
                      <?php
                      require_once "modelos/factus.modelo.php";
                      $municipios = ModeloFactus::mdlObtenerMunicipios();
                      foreach ($municipios as $municipio) {
                        $textoMunicipio = $municipio['nombre'] . ' - ' . $municipio['departamento'];
                        echo "<option value='{$municipio['id_factus']}'>{$textoMunicipio}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 4: Dirección -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Dirección *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-home"></i></span>
                    <input type="text" class="form-control" name="nuevaDireccion" placeholder="Calle, carrera, número, etc." required>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fila 5: Notas -->
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>Notas</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                    <input type="text" class="form-control" name="nuevaNota" placeholder="Información adicional (opcional)">
                  </div>
                </div>
              </div>
            </div>

            <!-- Campos ocultos -->
            <input type="hidden" name="nuevoEstatus" value="nuevo">
            <input type="hidden" name="origen" value="crm">
            <input type="hidden" name="vistaOrigen" value="crm">
            <input type="hidden" name="urlActual" value="crm">

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

<script>
/* =============================================
   VALIDAR DOCUMENTO DUPLICADO - MODAL AGREGAR CLIENTE
   (crm)
   ============================================= */
$(document).on("submit", "#modalAgregarCliente form", function (e) {
  e.preventDefault();
  var form = this;
  var documento = $(form).find('[name="nuevoDocumentoId"]').val();

  if (!documento || documento.trim() === "") {
    return; // La validación HTML nativa de "required" se encargará
  }

  var csrfToken = $('meta[name="csrf-token"]').attr('content');

  $.ajax({
    url: "ajax/clientes.ajax.php",
    method: "POST",
    data: {
      validarDocumento: documento,
      csrf_token: csrfToken
    },
    dataType: "json",
    success: function (respuesta) {
      if (respuesta.existe) {
        swal({
          type: "warning",
          title: "Documento ya registrado",
          text: respuesta.mensaje,
          showConfirmButton: true,
          confirmButtonText: "Entendido"
        });
      } else {
        // No hay duplicado, enviar el formulario normalmente
        form.submit();
      }
    },
    error: function () {
      // Si hay error de conexión, permitir envío para no bloquear al usuario
      form.submit();
    }
  });
});
</script>

<!-- Cargar scripts del CRM -->
<script src="vistas/js/crm.js?v=<?php echo time(); ?>"></script>
