<?php
if (!puedeVer('crm') && !puedeVer('data-crm')) {
    echo '<script>window.location = "inicio";</script>';
    return;
}

// Obtener todos los leads activos
$leads = ControladorCRM::ctrMostrarLeads(null, null);
$totalLeads = is_array($leads) ? count($leads) : 0;

$leadsFacturados = 0;
$leadsPerdidos = 0;
$leadsEnProceso = 0;
$montoFacturado = 0;
$montoPerdido = 0;
$montoEnProceso = 0;

$leadsWhatsApp = 0;
$leadsManuales = 0;
$leadsWhatsAppFacturados = 0;
$leadsManualesFacturados = 0;
$leadsWhatsAppPerdidos = 0;
$leadsManualesPerdidos = 0;

// Variables para Mapa de Calor y Horarios Pico (WhatsApp)
$diasSemana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];

$matrizCalorWA = [];
for ($d = 1; $d <= 7; $d++) {
    $matrizCalorWA[$d] = array_fill(0, 24, 0);
}
$totalPorDiaWA = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
$totalPorHoraWA = array_fill(0, 24, 0);
$totalPorFranjaWA = [
    'Madrugada (00:00 - 05:59)' => 0,
    'Mañana (06:00 - 11:59)'    => 0,
    'Tarde (12:00 - 17:59)'     => 0,
    'Noche (18:00 - 23:59)'     => 0
];
$maxConsultasCeldaWA = 0;
$totalConsultasWA = 0;

// Variables para Análisis de Tiempo Transcurrido desde Última Interacción
$ahora = time();
$leadsInactividad = [];
$leadsPorCliente = [];
$conteoLeadsPorCliente = [];
$rangosInactividad = [
    'menos_24h'   => 0,
    '1_a_3_dias'  => 0,
    '4_a_7_dias'  => 0,
    '8_a_15_dias' => 0,
    'mas_15_dias' => 0
];

if (!empty($leads)) {
    foreach ($leads as $l) {
        $etapa = trim(mb_strtolower($l["etapa"] ?? ''));
        $origen = trim(mb_strtolower($l["origen"] ?? ''));
        $valor = floatval($l["valor_estimado"] ?? 0);
        $esWhatsApp = !empty($origen) && (strpos($origen, 'whatsapp') !== false);

        if ($esWhatsApp) {
            $leadsWhatsApp++;
        } else {
            $leadsManuales++;
        }

        if ($etapa === 'facturado') {
            $leadsFacturados++;
            $montoFacturado += $valor;
            if ($esWhatsApp) {
                $leadsWhatsAppFacturados++;
            } else {
                $leadsManualesFacturados++;
            }
        } elseif ($etapa === 'perdido') {
            $leadsPerdidos++;
            $montoPerdido += $valor;
            if ($esWhatsApp) {
                $leadsWhatsAppPerdidos++;
            } else {
                $leadsManualesPerdidos++;
            }
        } else {
            $leadsEnProceso++;
            $montoEnProceso += $valor;
        }

        // Fecha de última interacción (con fallback a fecha_creacion)
        $fechaRaw = (!empty($l["fecha_ultima_interaccion"]) && $l["fecha_ultima_interaccion"] != "0000-00-00 00:00:00")
            ? $l["fecha_ultima_interaccion"]
            : ((!empty($l["fecha_creacion"]) && $l["fecha_creacion"] != "0000-00-00 00:00:00") ? $l["fecha_creacion"] : "");

        if (!empty($fechaRaw)) {
            $time = strtotime($fechaRaw);
            if ($time) {

                // 1. Procesar Mapa de Calor de WhatsApp
                if ($esWhatsApp) {
                    $diaNum = intval(date('N', $time)); // 1 (Lunes) a 7 (Domingo)
                    $horaNum = intval(date('G', $time)); // 0 a 23

                    if ($diaNum >= 1 && $diaNum <= 7 && $horaNum >= 0 && $horaNum <= 23) {
                        $matrizCalorWA[$diaNum][$horaNum]++;
                        $totalPorDiaWA[$diaNum]++;
                        $totalPorHoraWA[$horaNum]++;
                        $totalConsultasWA++;

                        if ($matrizCalorWA[$diaNum][$horaNum] > $maxConsultasCeldaWA) {
                            $maxConsultasCeldaWA = $matrizCalorWA[$diaNum][$horaNum];
                        }

                        if ($horaNum < 6) {
                            $totalPorFranjaWA['Madrugada (00:00 - 05:59)']++;
                        } elseif ($horaNum < 12) {
                            $totalPorFranjaWA['Mañana (06:00 - 11:59)']++;
                        } elseif ($horaNum < 18) {
                            $totalPorFranjaWA['Tarde (12:00 - 17:59)']++;
                        } else {
                            $totalPorFranjaWA['Noche (18:00 - 23:59)']++;
                        }
                    }
                }

                // 2. Procesar Tiempo Transcurrido desde la Última Interacción (Única por Cliente)
                $telOriginal = !empty($l["telefono_cliente"]) ? trim($l["telefono_cliente"]) : '';
                if (empty($telOriginal) && preg_match('/\d{7,}/', $l["origen"] ?? '', $m)) {
                    $telOriginal = $m[0];
                }
                $telDigitos = preg_replace('/\D/', '', $telOriginal);

                // Determinar clave única del cliente
                if (!empty($l["id_cliente"]) && intval($l["id_cliente"]) > 0) {
                    $claveCliente = "cli_" . intval($l["id_cliente"]);
                } elseif (!empty($telDigitos)) {
                    $claveCliente = "tel_" . $telDigitos;
                } else {
                    $claveCliente = "lead_" . $l["id"];
                }

                $conteoLeadsPorCliente[$claveCliente] = ($conteoLeadsPorCliente[$claveCliente] ?? 0) + 1;

                // Solo guardar si es el primer registro del cliente o si este lead tiene una interacción más reciente (mayor timestamp)
                if (!isset($leadsPorCliente[$claveCliente]) || $time > $leadsPorCliente[$claveCliente]['time_timestamp']) {
                    $segundos = max(0, $ahora - $time);
                    $minutos = floor($segundos / 60);
                    $horas = floor($segundos / 3600);
                    $dias = floor($segundos / 86400);

                    if ($dias < 1) {
                        $nivelAlerta = 'success';
                        $nivelTexto = '< 24 Horas';
                    } elseif ($dias <= 3) {
                        $nivelAlerta = 'info';
                        $nivelTexto = '1 - 3 Días';
                    } elseif ($dias <= 7) {
                        $nivelAlerta = 'warning';
                        $nivelTexto = '4 - 7 Días';
                    } elseif ($dias <= 15) {
                        $nivelAlerta = 'danger';
                        $nivelTexto = '8 - 15 Días';
                    } else {
                        $nivelAlerta = 'critical';
                        $nivelTexto = '> 15 Días';
                    }

                    if ($dias > 0) {
                        $horasRestantes = $horas % 24;
                        $tiempoFormateado = $dias == 1 ? "1 día" : "{$dias} días";
                        if ($horasRestantes > 0) {
                            $tiempoFormateado .= " y {$horasRestantes}h";
                        }
                    } elseif ($horas > 0) {
                        $minRestantes = $minutos % 60;
                        $tiempoFormateado = $horas == 1 ? "1 hora" : "{$horas} horas";
                        if ($minRestantes > 0) {
                            $tiempoFormateado .= " y {$minRestantes}m";
                        }
                    } elseif ($minutos > 0) {
                        $tiempoFormateado = $minutos == 1 ? "1 minuto" : "{$minutos} minutos";
                    } else {
                        $tiempoFormateado = "Hace un momento";
                    }

                    $nombreLead = !empty($l["nombre_cliente"]) ? trim($l["nombre_cliente"]) : (!empty($l["titulo"]) ? trim($l["titulo"]) : "Lead #".$l["id"]);
                    $emailLead = !empty($l["email_cliente"]) ? trim($l["email_cliente"]) : '';

                    $leadsPorCliente[$claveCliente] = [
                        'id'                => $l["id"],
                        'id_cliente'        => $l["id_cliente"] ?? null,
                        'nombre'            => $nombreLead,
                        'titulo'            => $l["titulo"] ?? '',
                        'telefono'          => $telOriginal,
                        'telefono_digitos'  => $telDigitos,
                        'email'             => $emailLead,
                        'origen'            => $l["origen"] ?? '',
                        'es_whatsapp'       => $esWhatsApp,
                        'etapa'             => $l["etapa"] ?? 'Sin etapa',
                        'prioridad'         => $l["prioridad"] ?? 'normal',
                        'fecha_interaccion' => $fechaRaw,
                        'time_timestamp'    => $time,
                        'segundos'          => $segundos,
                        'horas'             => $horas,
                        'dias'              => $dias,
                        'tiempo_formateado' => $tiempoFormateado,
                        'nivel_alerta'      => $nivelAlerta,
                        'nivel_texto'       => $nivelTexto
                    ];
                }
            }
        }
    }
}

// Consolidar únicamente el lead más reciente de cada cliente y calcular rangos
foreach ($leadsPorCliente as $clave => $item) {
    $item['total_leads_cliente'] = $conteoLeadsPorCliente[$clave] ?? 1;

    if ($item['dias'] < 1) {
        $rangosInactividad['menos_24h']++;
    } elseif ($item['dias'] <= 3) {
        $rangosInactividad['1_a_3_dias']++;
    } elseif ($item['dias'] <= 7) {
        $rangosInactividad['4_a_7_dias']++;
    } elseif ($item['dias'] <= 15) {
        $rangosInactividad['8_a_15_dias']++;
    } else {
        $rangosInactividad['mas_15_dias']++;
    }

    $leadsInactividad[] = $item;
}

// Ordenar leads por inactividad descendente (los que llevan más tiempo primero)
usort($leadsInactividad, function($a, $b) {
    return $b['segundos'] <=> $a['segundos'];
});

// Top 10 leads con mayor tiempo de inactividad para la gráfica
$top10Inactivos = array_slice($leadsInactividad, 0, 10);
$labelsTopLeads = [];
$diasTopLeads = [];
foreach ($top10Inactivos as $tLead) {
    $nombreCorto = mb_strlen($tLead['nombre']) > 16 ? mb_substr($tLead['nombre'], 0, 15) . '..' : $tLead['nombre'];
    $labelsTopLeads[] = $nombreCorto;
    $diasTopLeads[] = $tLead['dias'];
}

// Datos de distribución por rangos
$labelsRangosInactividad = ['< 24h', '1 a 3 días', '4 a 7 días', '8 a 15 días', '> 15 días'];
$dataRangosInactividad = [
    $rangosInactividad['menos_24h'],
    $rangosInactividad['1_a_3_dias'],
    $rangosInactividad['4_a_7_dias'],
    $rangosInactividad['8_a_15_dias'],
    $rangosInactividad['mas_15_dias']
];

// Hallar Picos de WhatsApp
$diaPicoNombre = "Sin datos";
$maxDiaTotal = 0;
foreach ($totalPorDiaWA as $d => $cant) {
    if ($cant > $maxDiaTotal) {
        $maxDiaTotal = $cant;
        $diaPicoNombre = $diasSemana[$d];
    }
}

$horaPicoTexto = "Sin datos";
$maxHoraTotal = 0;
foreach ($totalPorHoraWA as $h => $cant) {
    if ($cant > $maxHoraTotal) {
        $maxHoraTotal = $cant;
        $horaPicoTexto = sprintf("%02d:00 - %02d:59", $h, $h);
    }
}

$franjaPicoTexto = "Sin datos";
$maxFranjaTotal = 0;
foreach ($totalPorFranjaWA as $franja => $cant) {
    if ($cant > $maxFranjaTotal) {
        $maxFranjaTotal = $cant;
        $franjaPicoTexto = $franja;
    }
}

// Función para calcular estilo y color de cada celda del Mapa de Calor
function estiloCeldaCalorWA($valor, $max) {
    if ($valor <= 0) {
        return 'background-color: #f8fafc; color: #cbd5e1;';
    }
    if ($max <= 0) $max = 1;
    $ratio = $valor / $max;
    if ($ratio <= 0.25) {
        return 'background-color: #dcfce7; color: #166534; font-weight: 600;';
    } elseif ($ratio <= 0.50) {
        return 'background-color: #86efac; color: #14532d; font-weight: 700;';
    } elseif ($ratio <= 0.75) {
        return 'background-color: #22c55e; color: #ffffff; font-weight: 700;';
    } else {
        return 'background-color: #075e54; color: #ffffff; font-weight: 700; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.15);';
    }
}

// Porcentajes sobre el total de leads
$pctFacturados = $totalLeads > 0 ? round(($leadsFacturados / $totalLeads) * 100, 1) : 0;
$pctPerdidos = $totalLeads > 0 ? round(($leadsPerdidos / $totalLeads) * 100, 1) : 0;
$pctEnProceso = $totalLeads > 0 ? round(($leadsEnProceso / $totalLeads) * 100, 1) : 0;

// Porcentajes entre leads cerrados (Facturado vs Perdido)
$totalCerrados = $leadsFacturados + $leadsPerdidos;
$pctFacturadosCerrados = $totalCerrados > 0 ? round(($leadsFacturados / $totalCerrados) * 100, 1) : 0;
$pctPerdidosCerrados = $totalCerrados > 0 ? round(($leadsPerdidos / $totalCerrados) * 100, 1) : 0;

// Porcentajes de origen sobre total
$pctWhatsApp = $totalLeads > 0 ? round(($leadsWhatsApp / $totalLeads) * 100, 1) : 0;
$pctManuales = $totalLeads > 0 ? round(($leadsManuales / $totalLeads) * 100, 1) : 0;
?>

<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
.data-crm-wrapper {
    font-family: 'Inter', sans-serif !important;
    background-color: #f4f6f9;
}

.data-crm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.data-crm-metrics-container {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.data-crm-metric-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 16px 20px;
    flex: 1;
    min-width: 210px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-left: 4px solid #3c8dbc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.data-crm-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.data-crm-metric-title {
    font-size: 11px;
    text-transform: uppercase;
    color: #888;
    font-weight: 600;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.data-crm-metric-value {
    font-size: 22px;
    font-weight: 700;
    color: #222;
    line-height: 1.2;
}

.data-crm-metric-sub {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
    font-weight: 500;
}

.chart-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 32px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.chart-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.chart-card .box-header {
    border-bottom: 1px solid #f1f5f9;
    padding: 16px 22px;
    background: #fafbfc;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-card .box-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

/* SEPARADORES DE SECCIÓN */
.data-crm-section-divider {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin: 35px 0 18px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
    flex-wrap: wrap;
}

.data-crm-section-divider .section-title {
    font-size: 17px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.data-crm-section-divider .section-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
}

.data-crm-section-divider .section-desc {
    color: #64748b;
    font-size: 12px;
    font-weight: 400;
    display: block;
    margin-top: 3px;
}

/* SUBPANELES DE GRÁFICAS */
.chart-subpanel {
    background: #fafbfc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.chart-subpanel:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
}

.chart-inner-divider {
    margin: 35px 0 25px 0;
    border-top: 1px solid #e2e8f0;
    position: relative;
    text-align: left;
}

.chart-inner-divider span {
    position: relative;
    top: -12px;
    background: #ffffff;
    padding: 0 14px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.chart-legend-custom {
    list-style: none;
    padding: 0;
    margin: 15px 0 0 0;
}

.chart-legend-custom li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #f0f0f0;
    font-size: 13px;
    color: #444;
}

.chart-legend-custom li:last-child {
    border-bottom: none;
}

.chart-legend-bullet {
    width: 12px;
    height: 12px;
    border-radius: 3px;
    display: inline-block;
    margin-right: 8px;
}

.badge-pct {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
}

/* ESTILOS DEL MAPA DE CALOR */
.heatmap-container {
    overflow-x: auto;
    padding: 5px 0 15px 0;
}

.heatmap-table {
    width: 100%;
    min-width: 820px;
    border-collapse: separate;
    border-spacing: 3px;
    font-size: 11px;
}

.heatmap-table th {
    text-align: center;
    padding: 7px 3px;
    font-weight: 600;
    color: #475569;
    background: #f1f5f9;
    border-radius: 4px;
}

.heatmap-table td.heatmap-day {
    font-weight: 700;
    color: #334155;
    background: #f8fafc;
    text-align: left;
    padding: 8px 12px;
    border-radius: 4px;
    white-space: nowrap;
}

.heatmap-table td.heatmap-cell {
    text-align: center;
    padding: 8px 2px;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    user-select: none;
    min-width: 27px;
}

.heatmap-table td.heatmap-cell:hover {
    transform: scale(1.18);
    z-index: 10;
    position: relative;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25) !important;
}

.heatmap-table td.heatmap-total-day {
    font-weight: 700;
    background: #e2e8f0;
    color: #1e293b;
    text-align: center;
    border-radius: 4px;
    padding: 8px;
}

.heatmap-peak-badge {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    flex: 1;
    min-width: 190px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
}

.heatmap-legend-scale {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 11px;
    color: #64748b;
    margin-top: 10px;
}

.heatmap-legend-box {
    width: 18px;
    height: 18px;
    border-radius: 3px;
    display: inline-block;
}

/* ESTILOS DE LA TABLA DE INACTIVIDAD */
.inactivity-table th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 13px;
    padding: 10px 12px !important;
}

.inactivity-table td {
    padding: 10px 12px !important;
    vertical-align: middle !important;
}

.badge-inact-success {
    background-color: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.badge-inact-info {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.badge-inact-warning {
    background-color: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}

.badge-inact-danger {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
}

.badge-inact-critical {
    background-color: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-weight: 700;
}
</style>

<div class="content-wrapper data-crm-wrapper">

  <!-- CABECERA DE SECCIÓN -->
  <section class="content-header">
    <div class="data-crm-header">
      <div>
        <h1 style="margin: 0; font-weight: 700; font-size: 24px; color: #1e293b;">
          <i class="fa fa-bar-chart text-primary"></i> Data CRM
        </h1>
        <small style="color: #64748b; font-size: 13px;">Panel analítico y estadísticas de rendimiento del Pipeline</small>
      </div>
      <div>
        <a href="crm" class="btn btn-default" style="font-weight: 600; border-radius: 6px;">
          <i class="fa fa-filter text-primary"></i> Ir al Tablero CRM
        </a>
      </div>
    </div>
  </section>

  <!-- CONTENIDO PRINCIPAL -->
  <section class="content">

    <!-- FILA DE TARJETAS DE MÉTRICAS -->
    <div class="data-crm-metrics-container">

      <!-- Total Leads -->
      <div class="data-crm-metric-card" style="border-left-color: #3c8dbc;">
        <div>
          <div class="data-crm-metric-title">Total Oportunidades</div>
          <div class="data-crm-metric-value"><?= number_format($totalLeads) ?></div>
          <div class="data-crm-metric-sub">Leads registrados en CRM</div>
        </div>
        <i class="fa fa-users fa-2x text-muted" style="opacity: 0.35; color: #3c8dbc !important;"></i>
      </div>

      <!-- Facturados -->
      <div class="data-crm-metric-card" style="border-left-color: #00a65a;">
        <div>
          <div class="data-crm-metric-title">Leads Facturados</div>
          <div class="data-crm-metric-value text-success"><?= number_format($leadsFacturados) ?> <small style="font-size: 14px; font-weight: 700; color: #00a65a;">(<?= $pctFacturados ?>%)</small></div>
          <div class="data-crm-metric-sub">
            <?php if ($totalCerrados > 0): ?>
              Efectividad de cierre: <b><?= $pctFacturadosCerrados ?>%</b>
            <?php else: ?>
              Sin cierres registrados
            <?php endif; ?>
          </div>
        </div>
        <i class="fa fa-trophy fa-2x" style="opacity: 0.4; color: #00a65a !important;"></i>
      </div>

      <!-- Perdidos -->
      <div class="data-crm-metric-card" style="border-left-color: #dd4b39;">
        <div>
          <div class="data-crm-metric-title">Leads Perdidos</div>
          <div class="data-crm-metric-value text-danger"><?= number_format($leadsPerdidos) ?> <small style="font-size: 14px; font-weight: 700; color: #dd4b39;">(<?= $pctPerdidos ?>%)</small></div>
          <div class="data-crm-metric-sub">
            Monto perdido: <b>$ <?= number_format($montoPerdido, 0) ?></b>
          </div>
        </div>
        <i class="fa fa-thumbs-down fa-2x" style="opacity: 0.4; color: #dd4b39 !important;"></i>
      </div>

      <!-- WhatsApp -->
      <div class="data-crm-metric-card" style="border-left-color: #25D366;">
        <div>
          <div class="data-crm-metric-title">Origen WhatsApp</div>
          <div class="data-crm-metric-value" style="color: #25D366;"><?= number_format($leadsWhatsApp) ?> <small style="font-size: 14px; font-weight: 700; color: #25D366;">(<?= $pctWhatsApp ?>%)</small></div>
          <div class="data-crm-metric-sub">Generados por canal WhatsApp</div>
        </div>
        <i class="fa fa-whatsapp fa-2x" style="opacity: 0.45; color: #25D366 !important;"></i>
      </div>

      <!-- Manuales -->
      <div class="data-crm-metric-card" style="border-left-color: #605ca8;">
        <div>
          <div class="data-crm-metric-title">Origen Manual</div>
          <div class="data-crm-metric-value" style="color: #605ca8;"><?= number_format($leadsManuales) ?> <small style="font-size: 14px; font-weight: 700; color: #605ca8;">(<?= $pctManuales ?>%)</small></div>
          <div class="data-crm-metric-sub">Ingresados manualmente</div>
        </div>
        <i class="fa fa-pencil-square-o fa-2x" style="opacity: 0.4; color: #605ca8 !important;"></i>
      </div>

    </div>

    <!-- ========================================================
         SECCIÓN 1: RESUMEN DE CONVERSIÓN Y CANALES DE CAPTACIÓN
    ======================================================== -->
    <div class="data-crm-section-divider" style="margin-top: 10px;">
      <div>
        <div class="section-title">
          <i class="fa fa-pie-chart text-success"></i> 1. Rendimiento Comercial & Canales de Captación
        </div>
        <span class="section-desc">Proporción de cierre de oportunidades y distribución del origen de prospectos</span>
      </div>
      <span class="badge section-badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
        <i class="fa fa-pie-chart"></i> Gráficas de Cierre
      </span>
    </div>

    <!-- SECCIÓN DE GRÁFICAS DE ETAPAS Y ORIGEN (2 COLUMNAS) -->
    <div class="row">

      <!-- GRÁFICA 1: FACTURADO VS PERDIDO -->
      <div class="col-md-6 col-sm-12">
        <div class="box chart-card">
          <div class="box-header">
            <h3 class="box-title">
              <i class="fa fa-pie-chart text-success"></i> Leads por Etapa: Facturado vs. Perdido
            </h3>
            <span class="label label-success" style="font-size: 11px; padding: 4px 8px;">Cierres & Efectividad</span>
          </div>
          <div class="box-body" style="padding: 20px;">
            <?php if ($totalLeads == 0): ?>
              <div class="text-center" style="padding: 50px 20px; color: #94a3b8;">
                <i class="fa fa-folder-open-o fa-3x" style="opacity: 0.5; margin-bottom: 10px;"></i>
                <p>No hay leads registrados en el sistema para graficar.</p>
              </div>
            <?php else: ?>
              <div class="row">
                <div class="col-xs-12 col-sm-6 text-center">
                  <div class="chart-responsive" style="height: 240px; position: relative;">
                    <canvas id="pieChartEtapas" height="240"></canvas>
                  </div>
                </div>

                <div class="col-xs-12 col-sm-6">
                  <ul class="chart-legend-custom">
                    <li>
                      <div>
                        <span class="chart-legend-bullet" style="background: #00a65a;"></span>
                        <b>Facturado</b>
                      </div>
                      <div>
                        <span class="badge" style="background: #00a65a; color: white;"><?= $leadsFacturados ?> leads</span>
                        <span class="badge-pct label-success" style="margin-left: 5px;"><?= $pctFacturados ?>%</span>
                      </div>
                    </li>
                    <li>
                      <div>
                        <span class="chart-legend-bullet" style="background: #dd4b39;"></span>
                        <b>Perdido</b>
                      </div>
                      <div>
                        <span class="badge" style="background: #dd4b39; color: white;"><?= $leadsPerdidos ?> leads</span>
                        <span class="badge-pct label-danger" style="margin-left: 5px;"><?= $pctPerdidos ?>%</span>
                      </div>
                    </li>
                    <li>
                      <div>
                        <span class="chart-legend-bullet" style="background: #f39c12;"></span>
                        <b>En Proceso</b>
                      </div>
                      <div>
                        <span class="badge" style="background: #f39c12; color: white;"><?= $leadsEnProceso ?> leads</span>
                        <span class="badge-pct label-warning" style="margin-left: 5px;"><?= $pctEnProceso ?>%</span>
                      </div>
                    </li>
                  </ul>

                  <!-- Resumen de Efectividad de Cierre -->
                  <div style="margin-top: 20px; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 6px;">
                      Tasa de Éxito sobre Cierres (Facturado vs Perdido)
                    </div>
                    <div class="progress progress-xs" style="margin-bottom: 6px; height: 8px; border-radius: 4px; background: #e2e8f0;">
                      <div class="progress-bar progress-bar-success" style="width: <?= $pctFacturadosCerrados ?>%; background-color: #00a65a;"></div>
                      <div class="progress-bar progress-bar-danger" style="width: <?= $pctPerdidosCerrados ?>%; background-color: #dd4b39;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569;">
                      <span><b class="text-success"><?= $pctFacturadosCerrados ?>%</b> Ganados</span>
                      <span><b class="text-danger"><?= $pctPerdidosCerrados ?>%</b> Perdidos</span>
                    </div>
                  </div>

                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- GRÁFICA 2: MANUAL VS WHATSAPP -->
      <div class="col-md-6 col-sm-12">
        <div class="box chart-card">
          <div class="box-header">
            <h3 class="box-title">
              <i class="fa fa-pie-chart" style="color: #25D366;"></i> Origen de Leads: Manual vs. WhatsApp
            </h3>
            <span class="label" style="background-color: #25D366; color: white; font-size: 11px; padding: 4px 8px;">Canales de Adquisición</span>
          </div>
          <div class="box-body" style="padding: 20px;">
            <?php if ($totalLeads == 0): ?>
              <div class="text-center" style="padding: 50px 20px; color: #94a3b8;">
                <i class="fa fa-folder-open-o fa-3x" style="opacity: 0.5; margin-bottom: 10px;"></i>
                <p>No hay leads registrados en el sistema para graficar.</p>
              </div>
            <?php else: ?>
              <div class="row">
                <div class="col-xs-12 col-sm-6 text-center">
                  <div class="chart-responsive" style="height: 240px; position: relative;">
                    <canvas id="pieChartOrigen" height="240"></canvas>
                  </div>
                </div>

                <div class="col-xs-12 col-sm-6">
                  <ul class="chart-legend-custom">
                    <li>
                      <div>
                        <span class="chart-legend-bullet" style="background: #25D366;"></span>
                        <b>WhatsApp</b>
                      </div>
                      <div>
                        <span class="badge" style="background: #25D366; color: white;"><?= $leadsWhatsApp ?> leads</span>
                        <span class="badge-pct" style="background-color: #e8f5e9; color: #2e7d32; margin-left: 5px;"><?= $pctWhatsApp ?>%</span>
                      </div>
                    </li>
                    <li>
                      <div>
                        <span class="chart-legend-bullet" style="background: #3c8dbc;"></span>
                        <b>Manual</b>
                      </div>
                      <div>
                        <span class="badge" style="background: #3c8dbc; color: white;"><?= $leadsManuales ?> leads</span>
                        <span class="badge-pct" style="background-color: #e0f2fe; color: #0284c7; margin-left: 5px;"><?= $pctManuales ?>%</span>
                      </div>
                    </li>
                  </ul>

                  <!-- Proporción de Canales -->
                  <div style="margin-top: 20px; padding: 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 6px;">
                      Distribución por Canal
                    </div>
                    <div class="progress progress-xs" style="margin-bottom: 6px; height: 8px; border-radius: 4px; background: #e2e8f0;">
                      <div class="progress-bar" style="width: <?= $pctWhatsApp ?>%; background-color: #25D366;"></div>
                      <div class="progress-bar" style="width: <?= $pctManuales ?>%; background-color: #3c8dbc;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #475569;">
                      <span><b style="color: #25D366;"><?= $pctWhatsApp ?>%</b> WhatsApp</span>
                      <span><b class="text-primary"><?= $pctManuales ?>%</b> Manual</span>
                    </div>
                  </div>

                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- ========================================================
         SECCIÓN 2: MAPA DE CALOR Y HORARIOS PICO (WHATSAPP)
    ======================================================== -->
    <div class="data-crm-section-divider">
      <div>
        <div class="section-title">
          <i class="fa fa-whatsapp" style="color: #25D366;"></i> 2. Inteligencia de Horarios y Consultas WhatsApp
        </div>
        <span class="section-desc">Identificación de momentos clave con mayor tráfico de consultas y tasa de interacción</span>
      </div>
      <span class="badge section-badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
        <i class="fa fa-clock-o"></i> Mapa 24/7 & Curva Horaria
      </span>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <div class="box chart-card">
          <div class="box-header">
            <h3 class="box-title">
              <i class="fa fa-calendar-check-o" style="color: #25D366;"></i> Mapa de Calor / Horarios Pico de Consultas (WhatsApp)
            </h3>
            <span class="label" style="background-color: #25D366; color: white; font-size: 11px; padding: 4px 10px;">
              <i class="fa fa-clock-o"></i> Basado en Última Interacción
            </span>
          </div>

          <div class="box-body" style="padding: 20px;">

            <!-- FILA DE INSIGHTS PICO -->
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px;">

              <!-- Día Pico -->
              <div class="heatmap-peak-badge" style="border-left: 4px solid #25D366;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #e8f5e9; display: flex; align-items: center; justify-content: center;">
                  <i class="fa fa-calendar" style="color: #25D366; font-size: 18px;"></i>
                </div>
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Día con Mayor Demanda</div>
                  <div style="font-size: 17px; font-weight: 700; color: #1e293b;"><?= $diaPicoNombre ?></div>
                  <small style="color: #25D366; font-weight: 600;"><?= $maxDiaTotal ?> leads registrados</small>
                </div>
              </div>

              <!-- Franja Horaria Pico -->
              <div class="heatmap-peak-badge" style="border-left: 4px solid #3c8dbc;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #e0f2fe; display: flex; align-items: center; justify-content: center;">
                  <i class="fa fa-clock-o text-primary" style="font-size: 20px;"></i>
                </div>
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Franja Horaria Pico</div>
                  <div style="font-size: 16px; font-weight: 700; color: #1e293b;"><?= $franjaPicoTexto ?></div>
                  <small style="color: #0284c7; font-weight: 600;"><?= $maxFranjaTotal ?> leads acumulados</small>
                </div>
              </div>

              <!-- Hora Punta -->
              <div class="heatmap-peak-badge" style="border-left: 4px solid #ff851b;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #fff7ed; display: flex; align-items: center; justify-content: center;">
                  <i class="fa fa-bolt" style="color: #ea580c; font-size: 20px;"></i>
                </div>
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Hora Punta Específica</div>
                  <div style="font-size: 17px; font-weight: 700; color: #1e293b;"><?= $horaPicoTexto ?></div>
                  <small style="color: #ea580c; font-weight: 600;"><?= $maxHoraTotal ?> leads en esa hora</small>
                </div>
              </div>

              <!-- Total Consultas WhatsApp -->
              <div class="heatmap-peak-badge" style="border-left: 4px solid #605ca8;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #f3e8ff; display: flex; align-items: center; justify-content: center;">
                  <i class="fa fa-whatsapp" style="color: #7e22ce; font-size: 20px;"></i>
                </div>
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Total Interacciones Analizadas</div>
                  <div style="font-size: 17px; font-weight: 700; color: #1e293b;"><?= number_format($totalConsultasWA) ?></div>
                  <small style="color: #7e22ce; font-weight: 600;">Leads con registro de fecha</small>
                </div>
              </div>

            </div>

            <!-- TABLA MATRIZ MAPA DE CALOR (7 DÍAS x 24 HORAS) -->
            <div style="margin-bottom: 10px;">
              <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 10px 0; display: flex; align-items: center; gap: 6px;">
                <i class="fa fa-th text-success"></i> Matriz Semanal de Intensidad de Consultas
                <small style="color: #64748b; font-size: 12px;">(Pasa el cursor sobre cualquier casilla para ver los detalles)</small>
              </h4>
            </div>

            <div class="heatmap-container">
              <table class="heatmap-table">
                <thead>
                  <tr>
                    <th style="width: 110px; text-align: left; padding-left: 12px;">Día / Hora</th>
                    <?php for ($h = 0; $h < 24; $h++): ?>
                      <th title="<?= sprintf('%02d:00 - %02d:59', $h, $h) ?>"><?= sprintf('%02d', $h) ?>h</th>
                    <?php endfor; ?>
                    <th style="background: #e2e8f0; color: #0f172a; width: 65px;">Total Día</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($d = 1; $d <= 7; $d++): ?>
                    <tr>
                      <td class="heatmap-day">
                        <b><?= $diasSemana[$d] ?></b>
                      </td>
                      <?php for ($h = 0; $h < 24; $h++): 
                        $cant = $matrizCalorWA[$d][$h];
                        $estilo = estiloCeldaCalorWA($cant, $maxConsultasCeldaWA);
                        $textoCelda = $cant > 0 ? $cant : '-';
                        $tooltip = sprintf('%s a las %02d:00: %d leads de WhatsApp', $diasSemana[$d], $h, $cant);
                      ?>
                        <td class="heatmap-cell" style="<?= $estilo ?>" data-toggle="tooltip" data-placement="top" title="<?= $tooltip ?>">
                          <?= $textoCelda ?>
                        </td>
                      <?php endfor; ?>
                      <td class="heatmap-total-day">
                        <b><?= $totalPorDiaWA[$d] ?></b>
                      </td>
                    </tr>
                  <?php endfor; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td class="heatmap-day" style="background: #e2e8f0; font-weight: 800;">
                      Total Hora
                    </td>
                    <?php for ($h = 0; $h < 24; $h++): ?>
                      <td class="heatmap-total-day" style="font-size: 10px; padding: 6px 2px;">
                        <?= $totalPorHoraWA[$h] ?>
                      </td>
                    <?php endfor; ?>
                    <td class="heatmap-total-day" style="background: #cbd5e1; font-weight: 800; color: #0f172a;">
                      <?= $totalConsultasWA ?>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- ESCALA DE COLORES / LEYENDA DEL MAPA -->
            <div class="heatmap-legend-scale">
              <span><b>Intensidad de consultas:</b></span>
              <span style="display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;">
                <span class="heatmap-legend-box" style="background-color: #f8fafc; border: 1px solid #e2e8f0;"></span> 0 (Sin actividad)
              </span>
              <span style="display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;">
                <span class="heatmap-legend-box" style="background-color: #dcfce7; border: 1px solid #bbf7d0;"></span> 1 - 25% (Baja)
              </span>
              <span style="display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;">
                <span class="heatmap-legend-box" style="background-color: #86efac;"></span> 26 - 50% (Media)
              </span>
              <span style="display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;">
                <span class="heatmap-legend-box" style="background-color: #22c55e;"></span> 51 - 75% (Alta)
              </span>
              <span style="display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;">
                <span class="heatmap-legend-box" style="background-color: #075e54;"></span> 76 - 100% (Pico / Muy Alta)
              </span>
            </div>

            <!-- SEPARADOR INTERNO VISUAL -->
            <div class="chart-inner-divider">
              <span>
                <i class="fa fa-bar-chart text-success"></i> Curva Horaria de Demanda (00:00 - 23:00) y Franjas Clave
              </span>
            </div>

            <!-- GRÁFICA DE BARRAS POR HORAS Y DESGLOSE POR FRANJAS -->
            <div class="row">

              <!-- Gráfica de Barras: Curva Horaria (00:00 a 23:00) -->
              <div class="col-md-8 col-sm-12">
                <div class="chart-subpanel">
                  <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 15px 0;">
                    <i class="fa fa-bar-chart text-success"></i> Volumen de Consultas por Hora del Día (00:00 - 23:00)
                  </h4>
                  <div class="chart-responsive" style="height: 250px; position: relative;">
                    <canvas id="barChartHorasWhatsApp" height="250"></canvas>
                  </div>
                </div>
              </div>

              <!-- Desglose por Franjas Horarias Principales -->
              <div class="col-md-4 col-sm-12">
                <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 15px 0;">
                  <i class="fa fa-pie-chart text-primary"></i> Franjas Horarias del Día
                </h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                  <?php 
                  $iconosFranjas = [
                      'Madrugada (00:00 - 05:59)' => 'fa-moon-o',
                      'Mañana (06:00 - 11:59)'    => 'fa-sun-o',
                      'Tarde (12:00 - 17:59)'     => 'fa-coffee',
                      'Noche (18:00 - 23:59)'     => 'fa-star'
                  ];
                  $coloresFranjas = [
                      'Madrugada (00:00 - 05:59)' => '#64748b',
                      'Mañana (06:00 - 11:59)'    => '#f59e0b',
                      'Tarde (12:00 - 17:59)'     => '#25D366',
                      'Noche (18:00 - 23:59)'     => '#3c8dbc'
                  ];

                  foreach ($totalPorFranjaWA as $nombreFranja => $cantFranja): 
                      $pctFranja = $totalConsultasWA > 0 ? round(($cantFranja / $totalConsultasWA) * 100, 1) : 0;
                      $colorF = $coloresFranjas[$nombreFranja];
                      $iconoF = $iconosFranjas[$nombreFranja];
                  ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px;">
                      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-weight: 600; font-size: 12px; color: #334155;">
                          <i class="fa <?= $iconoF ?>" style="color: <?= $colorF ?>; margin-right: 5px;"></i> <?= $nombreFranja ?>
                        </span>
                        <span style="font-weight: 700; font-size: 12px; color: #0f172a;">
                          <?= $cantFranja ?> leads <small style="color: #64748b;">(<?= $pctFranja ?>%)</small>
                        </span>
                      </div>
                      <div class="progress progress-xs" style="height: 6px; margin: 0; background: #e2e8f0; border-radius: 3px;">
                        <div class="progress-bar" style="width: <?= $pctFranja ?>%; background-color: <?= $colorF ?>;"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ========================================================
         SECCIÓN 3: TIEMPO TRANSCURRIDO Y SEGUIMIENTO DE LEADS
    ======================================================== -->
    <div class="data-crm-section-divider">
      <div>
        <div class="section-title">
          <i class="fa fa-hourglass-half text-warning"></i> 3. Monitoreo de Tiempos sin Contacto y Retención
        </div>
        <span class="section-desc">Alertas tempranas de leads desatendidos basadas en la fecha de última interacción</span>
      </div>
      <span class="badge section-badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
        <i class="fa fa-clock-o"></i> Detección de Inactividad
      </span>
    </div>

    <!-- TARJETA 3A: GRÁFICAS Y BADGES DE INACTIVIDAD -->
    <div class="row">
      <div class="col-xs-12">
        <div class="box chart-card">
          <div class="box-header">
            <h3 class="box-title">
              <i class="fa fa-bar-chart text-warning"></i> Análisis y Distribución de Inactividad por Lead
            </h3>
            <span class="label label-warning" style="font-size: 11px; padding: 4px 10px;">
              <i class="fa fa-user"></i> 1 Lead más reciente por cliente
            </span>
          </div>

          <div class="box-body" style="padding: 24px;">

            <!-- RESUMEN POR RANGOS DE TIEMPO -->
            <div style="display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 28px;">
              
              <div class="heatmap-peak-badge" style="border-left: 4px solid #00a65a;">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Atendidos Hoy</div>
                  <div style="font-size: 18px; font-weight: 700; color: #00a65a;"><?= $rangosInactividad['menos_24h'] ?> leads</div>
                  <small style="color: #64748b;">< 24 Horas transcurridas</small>
                </div>
              </div>

              <div class="heatmap-peak-badge" style="border-left: 4px solid #3c8dbc;">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Recientes</div>
                  <div style="font-size: 18px; font-weight: 700; color: #3c8dbc;"><?= $rangosInactividad['1_a_3_dias'] ?> leads</div>
                  <small style="color: #64748b;">1 a 3 Días transcurridos</small>
                </div>
              </div>

              <div class="heatmap-peak-badge" style="border-left: 4px solid #f39c12;">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">En Seguimiento</div>
                  <div style="font-size: 18px; font-weight: 700; color: #f39c12;"><?= $rangosInactividad['4_a_7_dias'] ?> leads</div>
                  <small style="color: #64748b;">4 a 7 Días transcurridos</small>
                </div>
              </div>

              <div class="heatmap-peak-badge" style="border-left: 4px solid #e67e22;">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">En Riesgo</div>
                  <div style="font-size: 18px; font-weight: 700; color: #e67e22;"><?= $rangosInactividad['8_a_15_dias'] ?> leads</div>
                  <small style="color: #64748b;">8 a 15 Días transcurridos</small>
                </div>
              </div>

              <div class="heatmap-peak-badge" style="border-left: 4px solid #dd4b39;">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Inactivos Críticos</div>
                  <div style="font-size: 18px; font-weight: 700; color: #dd4b39;"><?= $rangosInactividad['mas_15_dias'] ?> leads</div>
                  <small style="color: #dd4b39; font-weight: 600;">Más de 15 Días</small>
                </div>
              </div>

            </div>

            <!-- FILA DE GRÁFICAS DE TIEMPO TRANSCURRIDO -->
            <div class="row">

              <!-- Gráfica 1: Distribución por Rangos de Antigüedad -->
              <div class="col-md-6 col-sm-12" style="margin-bottom: 15px;">
                <div class="chart-subpanel">
                  <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 15px 0; display: flex; align-items: center; gap: 6px;">
                    <i class="fa fa-bar-chart text-primary"></i> Distribución de Leads por Antigüedad
                  </h4>
                  <div class="chart-responsive" style="height: 250px; position: relative;">
                    <canvas id="barChartDistribucionInactividad" height="250"></canvas>
                  </div>
                </div>
              </div>

              <!-- Gráfica 2: Top 10 Leads con Mayor Tiempo Transcurrido -->
              <div class="col-md-6 col-sm-12" style="margin-bottom: 15px;">
                <div class="chart-subpanel">
                  <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0 0 15px 0; display: flex; align-items: center; gap: 6px;">
                    <i class="fa fa-clock-o text-danger"></i> Top Leads con Mayor Tiempo sin Contacto (Días)
                  </h4>
                  <div class="chart-responsive" style="height: 250px; position: relative;">
                    <canvas id="barChartTopInactivos" height="250"></canvas>
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- TARJETA 3B: TABLA AUDITORÍA DETALLADA -->
    <div class="row">
      <div class="col-xs-12">
        <div class="box chart-card">
          <div class="box-header">
            <div>
              <h3 class="box-title">
                <i class="fa fa-list-alt text-primary"></i> Detalle de Clientes y Tiempo Transcurrido
              </h3>
              <div style="color: #64748b; font-size: 12px; margin-top: 3px;">
                Listado ordenado por tiempo sin interacción (mostrando únicamente la última interacción más reciente por cliente)
              </div>
            </div>

            <div style="width: 320px; max-width: 100%;">
              <div class="input-group">
                <span class="input-group-addon" style="background: #f8fafc; border-color: #cbd5e1;"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="buscarTablaInactividad" class="form-control" placeholder="Buscar por código (#), cliente, WhatsApp..." style="border-radius: 0 4px 4px 0; font-size: 12px;">
              </div>
            </div>
          </div>

          <div class="box-body" style="padding: 20px;">
            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 8px; max-height: 520px; overflow-y: auto;">
              <table class="table table-hover table-striped inactivity-table" style="margin-bottom: 0;">
                <thead style="position: sticky; top: 0; z-index: 5;">
                  <tr>
                    <th style="min-width: 180px;">Cliente / Lead</th>
                    <th style="min-width: 140px;">WhatsApp</th>
                    <th style="min-width: 170px;">Correo Electrónico</th>
                    <th class="text-center" style="min-width: 110px;">Etapa</th>
                    <th style="min-width: 140px;">Última Interacción</th>
                    <th style="min-width: 170px;">Tiempo Transcurrido</th>
                  </tr>
                </thead>
                <tbody id="tablaCuerpoInactividad">
                  <?php if (empty($leadsInactividad)): ?>
                    <tr>
                      <td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">
                        <i class="fa fa-info-circle fa-2x" style="opacity: 0.5; margin-bottom: 8px;"></i>
                        <p style="margin: 0;">No se encontraron leads con registro de fecha para mostrar.</p>
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($leadsInactividad as $leadItem): 
                      $badgeClass = 'badge-inact-' . $leadItem['nivel_alerta'];
                      $porcentajeBarra = min(100, round(($leadItem['dias'] / 30) * 100)); // Relativo a 30 días
                    ?>
                      <tr>
                        <!-- Nombre del Lead / Cliente -->
                        <td>
                          <div style="font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 700; border-radius: 4px; padding: 2px 6px; border: 1px solid #e2e8f0; flex-shrink: 0;" title="Código del Lead">#<?= $leadItem['id'] ?></span>
                            <span><?= htmlspecialchars($leadItem['nombre']) ?></span>
                            <?php if ($leadItem['total_leads_cliente'] > 1): ?>
                              <span class="badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 10px; font-weight: 600;" title="Este cliente tiene <?= $leadItem['total_leads_cliente'] ?> leads en total. Se muestra el más reciente.">
                                <i class="fa fa-refresh"></i> <?= $leadItem['total_leads_cliente'] ?> leads
                              </span>
                            <?php endif; ?>
                          </div>
                          <?php if (!empty($leadItem['titulo']) && $leadItem['titulo'] !== $leadItem['nombre']): ?>
                            <small style="color: #64748b; font-size: 11px; display: block; margin-top: 2px;">
                              <i class="fa fa-briefcase text-muted"></i> <?= htmlspecialchars($leadItem['titulo']) ?>
                            </small>
                          <?php endif; ?>
                        </td>

                        <!-- WhatsApp con botón interactivo -->
                        <td>
                          <?php if (!empty($leadItem['telefono_digitos'])): ?>
                            <a href="https://wa.me/<?= $leadItem['telefono_digitos'] ?>" target="_blank" class="btn btn-xs btn-success" style="background-color: #25D366; border-color: #25D366; font-weight: 600; border-radius: 4px; padding: 3px 8px;" title="Abrir chat de WhatsApp">
                              <i class="fa fa-whatsapp"></i> <?= htmlspecialchars($leadItem['telefono']) ?>
                            </a>
                          <?php elseif (!empty($leadItem['telefono'])): ?>
                            <span class="text-muted"><i class="fa fa-phone"></i> <?= htmlspecialchars($leadItem['telefono']) ?></span>
                          <?php else: ?>
                            <span class="text-muted" style="font-size: 12px; font-style: italic;">Sin teléfono</span>
                          <?php endif; ?>
                        </td>

                        <!-- Correo Electrónico -->
                        <td>
                          <?php if (!empty($leadItem['email'])): ?>
                            <a href="mailto:<?= htmlspecialchars($leadItem['email']) ?>" class="text-primary" style="font-size: 12px; word-break: break-all;" title="Enviar correo">
                              <i class="fa fa-envelope-o text-muted"></i> <?= htmlspecialchars($leadItem['email']) ?>
                            </a>
                          <?php else: ?>
                            <span class="text-muted" style="font-size: 12px; font-style: italic;">Sin correo registrado</span>
                          <?php endif; ?>
                        </td>

                        <!-- Etapa -->
                        <td class="text-center">
                          <span class="label label-default" style="font-size: 11px; padding: 3px 7px; background: #e2e8f0; color: #334155;">
                            <?= htmlspecialchars($leadItem['etapa']) ?>
                          </span>
                        </td>

                        <!-- Fecha de Última Interacción -->
                        <td>
                          <div style="font-size: 12px; color: #334155; font-weight: 500;">
                            <i class="fa fa-calendar-o text-muted" style="margin-right: 3px;"></i>
                            <?= date("d/m/Y", strtotime($leadItem['fecha_interaccion'])) ?>
                          </div>
                          <small style="color: #64748b; font-size: 11px;">
                            <i class="fa fa-clock-o text-muted" style="margin-right: 3px;"></i>
                            <?= date("g:i a", strtotime($leadItem['fecha_interaccion'])) ?>
                          </small>
                        </td>

                        <!-- Tiempo Transcurrido -->
                        <td>
                          <span class="badge <?= $badgeClass ?>" style="padding: 4px 8px; font-size: 11px; border-radius: 4px; display: inline-block;">
                            <i class="fa fa-hourglass-end" style="margin-right: 3px;"></i>
                            <?= $leadItem['tiempo_formateado'] ?>
                          </span>
                          <div class="progress progress-xs" style="height: 4px; margin-top: 5px; margin-bottom: 0; background: #e2e8f0; border-radius: 2px;">
                            <?php 
                              $colorBarra = '#00a65a';
                              if ($leadItem['nivel_alerta'] === 'info') $colorBarra = '#3c8dbc';
                              elseif ($leadItem['nivel_alerta'] === 'warning') $colorBarra = '#f39c12';
                              elseif ($leadItem['nivel_alerta'] === 'danger') $colorBarra = '#e67e22';
                              elseif ($leadItem['nivel_alerta'] === 'critical') $colorBarra = '#dd4b39';
                            ?>
                            <div class="progress-bar" style="width: <?= max(5, $porcentajeBarra) ?>%; background-color: <?= $colorBarra ?>;"></div>
                          </div>
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

    <!-- ========================================================
         SECCIÓN 4: AUDITORÍA Y RENDIMIENTO POR CANAL
    ======================================================== -->
    <div class="data-crm-section-divider">
      <div>
        <div class="section-title">
          <i class="fa fa-table text-primary"></i> 4. Rendimiento Comparativo por Canal Comercial
        </div>
        <span class="section-desc">Totales consolidados, volumen facturado, leads perdidos y tasa de conversión neta</span>
      </div>
      <span class="badge section-badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
        <i class="fa fa-check-square-o"></i> Resumen Ejecutivo
      </span>
    </div>

    <!-- TABLA DE DETALLE COMPARATIVO POR CANAL -->
    <div class="row">
      <div class="col-xs-12">
        <div class="box chart-card">
          <div class="box-header">
            <h3 class="box-title">
              <i class="fa fa-table text-primary"></i> Resumen de Conversión por Canal
            </h3>
          </div>
          <div class="box-body table-responsive" style="padding: 0;">
            <table class="table table-hover table-striped" style="margin-bottom: 0;">
              <thead>
                <tr style="background-color: #f8fafc; color: #475569; font-size: 13px;">
                  <th>Canal de Origen</th>
                  <th class="text-center">Total Leads</th>
                  <th class="text-center">Participación</th>
                  <th class="text-center">Facturados (Ganados)</th>
                  <th class="text-center">Perdidos</th>
                  <th class="text-center">En Proceso</th>
                  <th class="text-center">Tasa de Cierre</th>
                </tr>
              </thead>
              <tbody style="font-size: 13px;">
                <tr>
                  <td>
                    <span class="label" style="background-color: #25D366; color: white; padding: 4px 8px;">
                      <i class="fa fa-whatsapp"></i> WhatsApp
                    </span>
                  </td>
                  <td class="text-center font-weight-bold"><b><?= $leadsWhatsApp ?></b></td>
                  <td class="text-center"><?= $pctWhatsApp ?>%</td>
                  <td class="text-center text-success">
                    <b><?= $leadsWhatsAppFacturados ?></b>
                    <small style="color: #64748b;">(<?= $leadsWhatsApp > 0 ? round(($leadsWhatsAppFacturados / $leadsWhatsApp) * 100, 1) : 0 ?>%)</small>
                  </td>
                  <td class="text-center text-danger">
                    <b><?= $leadsWhatsAppPerdidos ?></b>
                    <small style="color: #64748b;">(<?= $leadsWhatsApp > 0 ? round(($leadsWhatsAppPerdidos / $leadsWhatsApp) * 100, 1) : 0 ?>%)</small>
                  </td>
                  <td class="text-center">
                    <?= $leadsWhatsApp - ($leadsWhatsAppFacturados + $leadsWhatsAppPerdidos) ?>
                  </td>
                  <td class="text-center">
                    <?php
                    $cerradosWA = $leadsWhatsAppFacturados + $leadsWhatsAppPerdidos;
                    $winRateWA = $cerradosWA > 0 ? round(($leadsWhatsAppFacturados / $cerradosWA) * 100, 1) : 0;
                    ?>
                    <span class="badge label-success"><?= $winRateWA ?>%</span>
                  </td>
                </tr>
                <tr>
                  <td>
                    <span class="label label-primary" style="padding: 4px 8px;">
                      <i class="fa fa-pencil"></i> Manual
                    </span>
                  </td>
                  <td class="text-center font-weight-bold"><b><?= $leadsManuales ?></b></td>
                  <td class="text-center"><?= $pctManuales ?>%</td>
                  <td class="text-center text-success">
                    <b><?= $leadsManualesFacturados ?></b>
                    <small style="color: #64748b;">(<?= $leadsManuales > 0 ? round(($leadsManualesFacturados / $leadsManuales) * 100, 1) : 0 ?>%)</small>
                  </td>
                  <td class="text-center text-danger">
                    <b><?= $leadsManualesPerdidos ?></b>
                    <small style="color: #64748b;">(<?= $leadsManuales > 0 ? round(($leadsManualesPerdidos / $leadsManuales) * 100, 1) : 0 ?>%)</small>
                  </td>
                  <td class="text-center">
                    <?= $leadsManuales - ($leadsManualesFacturados + $leadsManualesPerdidos) ?>
                  </td>
                  <td class="text-center">
                    <?php
                    $cerradosMan = $leadsManualesFacturados + $leadsManualesPerdidos;
                    $winRateMan = $cerradosMan > 0 ? round(($leadsManualesFacturados / $cerradosMan) * 100, 1) : 0;
                    ?>
                    <span class="badge label-success"><?= $winRateMan ?>%</span>
                  </td>
                </tr>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                  <td><b>Total General</b></td>
                  <td class="text-center"><?= $totalLeads ?></td>
                  <td class="text-center">100%</td>
                  <td class="text-center text-success"><?= $leadsFacturados ?> (<?= $pctFacturados ?>%)</td>
                  <td class="text-center text-danger"><?= $leadsPerdidos ?> (<?= $pctPerdidos ?>%)</td>
                  <td class="text-center"><?= $leadsEnProceso ?> (<?= $pctEnProceso ?>%)</td>
                  <td class="text-center">
                    <span class="badge" style="background: #1e293b;"><?= $pctFacturadosCerrados ?>%</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>

<!-- SCRIPTS DE LAS GRÁFICAS -->
<?php if ($totalLeads > 0): ?>
<script>
$(document).ready(function() {

    // Inicializar tooltips de Bootstrap
    if (typeof $.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
    }

    // Buscador interactivo de la tabla de inactividad
    $('#buscarTablaInactividad').on('keyup', function() {
        var valor = $(this).val().toLowerCase();
        $('#tablaCuerpoInactividad tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(valor) > -1);
        });
    });

    var pieOptions = {
        segmentShowStroke: true,
        segmentStrokeColor: '#ffffff',
        segmentStrokeWidth: 2,
        percentageInnerCutout: 50,
        animationSteps: 90,
        animationEasing: 'easeOutBounce',
        animateRotate: true,
        animateScale: false,
        responsive: true,
        maintainAspectRatio: false,
        tooltipTemplate: '<%=label%>: <%=value%> leads'
    };

    // 1. Gráfica de Etapas (Facturado vs Perdido vs En Proceso)
    var canvasEtapas = document.getElementById('pieChartEtapas');
    if (canvasEtapas) {
        var ctxEtapas = canvasEtapas.getContext('2d');
        var dataEtapas = [
            {
                value: <?= $leadsFacturados ?>,
                color: '#00a65a',
                highlight: '#008d4c',
                label: 'Facturado'
            },
            {
                value: <?= $leadsPerdidos ?>,
                color: '#dd4b39',
                highlight: '#d73925',
                label: 'Perdido'
            },
            {
                value: <?= $leadsEnProceso ?>,
                color: '#f39c12',
                highlight: '#e08e0b',
                label: 'En Proceso'
            }
        ];
        new Chart(ctxEtapas).Doughnut(dataEtapas, pieOptions);
    }

    // 2. Gráfica de Origen (WhatsApp vs Manual)
    var canvasOrigen = document.getElementById('pieChartOrigen');
    if (canvasOrigen) {
        var ctxOrigen = canvasOrigen.getContext('2d');
        var dataOrigen = [
            {
                value: <?= $leadsWhatsApp ?>,
                color: '#25D366',
                highlight: '#20ba5a',
                label: 'WhatsApp'
            },
            {
                value: <?= $leadsManuales ?>,
                color: '#3c8dbc',
                highlight: '#367fa9',
                label: 'Manual'
            }
        ];
        new Chart(ctxOrigen).Doughnut(dataOrigen, pieOptions);
    }

    // 3. Gráfica de Barras por Horas de WhatsApp (00h a 23h)
    var canvasHoras = document.getElementById('barChartHorasWhatsApp');
    if (canvasHoras) {
        var ctxHoras = canvasHoras.getContext('2d');
        var dataHoras = {
            labels: [
                '00h', '01h', '02h', '03h', '04h', '05h',
                '06h', '07h', '08h', '09h', '10h', '11h',
                '12h', '13h', '14h', '15h', '16h', '17h',
                '18h', '19h', '20h', '21h', '22h', '23h'
            ],
            datasets: [
                {
                    label: 'Consultas WhatsApp',
                    fillColor: 'rgba(37, 211, 102, 0.75)',
                    strokeColor: 'rgba(37, 211, 102, 1)',
                    highlightFill: 'rgba(18, 140, 126, 0.95)',
                    highlightStroke: 'rgba(18, 140, 126, 1)',
                    data: <?= json_encode(array_values($totalPorHoraWA)) ?>
                }
            ]
        };

        var barOptionsHoras = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: false,
            barShowStroke: true,
            barStrokeWidth: 1,
            barValueSpacing: 4,
            barDatasetSpacing: 1,
            responsive: true,
            maintainAspectRatio: false,
            tooltipTemplate: '<%=label%>: <%=value%> consultas WhatsApp'
        };

        new Chart(ctxHoras).Bar(dataHoras, barOptionsHoras);
    }

    // 4. Gráfica de Distribución de Inactividad por Rangos
    var canvasDistribucion = document.getElementById('barChartDistribucionInactividad');
    if (canvasDistribucion) {
        var ctxDist = canvasDistribucion.getContext('2d');
        var dataDist = {
            labels: <?= json_encode($labelsRangosInactividad) ?>,
            datasets: [
                {
                    label: 'Cantidad de Leads',
                    fillColor: 'rgba(60, 141, 188, 0.8)',
                    strokeColor: 'rgba(60, 141, 188, 1)',
                    highlightFill: 'rgba(54, 127, 169, 0.95)',
                    highlightStroke: 'rgba(54, 127, 169, 1)',
                    data: <?= json_encode($dataRangosInactividad) ?>
                }
            ]
        };

        var barOptionsDist = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: false,
            barShowStroke: true,
            barStrokeWidth: 1,
            barValueSpacing: 6,
            barDatasetSpacing: 1,
            responsive: true,
            maintainAspectRatio: false,
            tooltipTemplate: '<%=label%>: <%=value%> leads'
        };

        new Chart(ctxDist).Bar(dataDist, barOptionsDist);
    }

    // 5. Gráfica Top Leads con Mayor Inactividad (Días)
    var canvasTopLeads = document.getElementById('barChartTopInactivos');
    if (canvasTopLeads) {
        var ctxTop = canvasTopLeads.getContext('2d');
        var dataTop = {
            labels: <?= json_encode($labelsTopLeads) ?>,
            datasets: [
                {
                    label: 'Días Transcurridos',
                    fillColor: 'rgba(221, 75, 57, 0.8)',
                    strokeColor: 'rgba(221, 75, 57, 1)',
                    highlightFill: 'rgba(215, 57, 37, 0.95)',
                    highlightStroke: 'rgba(215, 57, 37, 1)',
                    data: <?= json_encode($diasTopLeads) ?>
                }
            ]
        };

        var barOptionsTop = {
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleShowHorizontalLines: true,
            scaleShowVerticalLines: false,
            barShowStroke: true,
            barStrokeWidth: 1,
            barValueSpacing: 6,
            barDatasetSpacing: 1,
            responsive: true,
            maintainAspectRatio: false,
            tooltipTemplate: '<%=label%>: <%=value%> días sin interacción'
        };

        new Chart(ctxTop).Bar(dataTop, barOptionsTop);
    }

});
</script>
<?php endif; ?>
