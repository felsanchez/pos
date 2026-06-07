<!-- Ruta actividades.css -->
<link rel="stylesheet" href="assets/css/actividades.css">

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css' rel='stylesheet' />

<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>
/* Diseño Base y Tipografía Modernizada */
#calendar-principal {
    font-family: 'Inter', sans-serif !important;
    background: #ffffff;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

/* Eventos Estilo floating-card (Glassmorphism sutil) */
.fc-event {
    border-radius: 8px !important;
    border: none !important;
    padding: 3px 6px !important;
    margin: 2px 0 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08) !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    font-weight: 500 !important;
    font-size: 0.88em !important;
}

.fc-event:hover {
    transform: translateY(-2px) scale(1.02) !important;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
    z-index: 5 !important;
}

/* Rediseño de Botones de Navegación */
.fc .fc-button-primary {
    background-color: #f8f9fa !important;
    border-color: #e9ecef !important;
    color: #495057 !important;
    border-radius: 8px !important;
    text-transform: capitalize !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
}

.fc .fc-button-primary:hover {
    background-color: #e9ecef !important;
    border-color: #dee2e6 !important;
    color: #212529 !important;
}

.fc .fc-button-active {
    background-color: #3c8dbc !important;
    border-color: #3c8dbc !important;
    color: #ffffff !important;
    box-shadow: 0 4px 10px rgba(60, 141, 188, 0.3) !important;
}

.fc .fc-today-button {
    background-color: #fff !important;
    border-color: #3c8dbc !important;
    color: #3c8dbc !important;
    opacity: 1 !important;
}

/* Cabecera del Calendario */
.fc-toolbar-title {
    font-size: 1.4em !important;
    font-weight: 600 !important;
    color: #333 !important;
    letter-spacing: -0.5px;
}

/* Ajustes de Cuadrícula */
.fc-theme-standard td, .fc-theme-standard th {
    border-color: #f1f3f5 !important;
}

.fc-col-header-cell {
    background: #f8f9fa !important;
    padding: 10px 0 !important;
    color: #6c757d !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    font-size: 0.75em !important;
}

/* Ajuste móvil */
@media (max-width: 768px) {
    .fc-toolbar {
        flex-direction: column !important;
        gap: 10px;
    }
    .fc-toolbar-title {
        font-size: 1.1em !important;
    }
}
</style>


<div class="content-wrapper">
  
<!--=====================================
MODAL MOSTRAR actividad
======================================-->  
  <!-- Encabezado -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1><i class="fa fa-calendar"></i> Calendario de Actividades</h1>
        </div>
      </div>
    </div>
  </section>

  <!-- Contenido principal -->
  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header" style="background-color: #3c8dbc; color: white; display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
          <h3 class="card-title" style="color: white !important; margin: 0; font-size: 18px;">
            <i class="fa fa-calendar"></i> Calendario de Actividades
          </h3>
          <?php if(puedeAccion('actividades', 'crear')): ?>
            <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#modalAgregarActividad" style="font-weight: bold; color: #333;">
              <i class="fa fa-plus"></i> Agregar actividad
            </button>
          <?php else: ?>
            <button class="btn btn-default btn-sm" disabled style="font-weight: bold; color: #333; opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear actividades">
              <i class="fa fa-plus"></i> Agregar actividad
            </button>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div id="calendar-principal"></div>
        </div>
      </div>
    </div>
  </section>

</div>

<!-- Modal Mostrar Actividad -->
<div class="modal fade" id="actividadModal" tabindex="-1" aria-labelledby="actividadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title" id="actividadModalLabel">
          <i class="fa fa-calendar-plus"></i> <span id="modalTitle">Detalles de Actividad</span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="actividadForm">
        <div class="modal-body">
          
          <!-- Sección Principal: Descripción -->
          <div class="card mb-4" style="border-left: 4px solid #007bff;">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="fa fa-info-circle text-primary"></i> Información Principal
              </h6>
              <div class="form-group">
                <label for="descripcion" class="font-weight-bold">
                  <i class="fa fa-tasks text-muted mr-1"></i> Descripción
                </label>
                <input type="text" class="form-control form-control-lg" id="descripcion" name="descripcion" required readonly 
                       style="background-color: #f8f9fa; border: 2px solid #e9ecef; font-size: 16px;">
              </div>

              <div class="form-group">
                <label for="fecha" class="font-weight-bold">
                  <i class="fa fa-calendar-alt text-muted mr-1"></i> Fecha y Hora
                </label>
                <input type="text" class="form-control" id="fecha" name="fecha" required readonly
                       style="background-color: #f8f9fa; border: 1px solid #ced4da; font-size: 15px;">
              </div>

            </div>
          </div>

          <!-- Sección de Detalles en dos columnas -->
          <div class="card mb-4" style="border-left: 4px solid #28a745;">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="fa fa-cog text-success"></i> Detalles de la Actividad
              </h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="tipo" class="font-weight-bold">
                      <i class="fa fa-tag text-muted mr-1"></i> Tipo
                    </label>
                    <input type="text" class="form-control" id="tipo" name="tipo" required readonly
                           style="background-color: #f8f9fa; border: 1px solid #ced4da;">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="estado" class="font-weight-bold">
                      <i class="fa fa-flag text-muted mr-1"></i> Estado
                    </label>
                    <input type="text" class="form-control" id="estado" name="estado" required readonly
                           style="background-color: #f8f9fa; border: 1px solid #ced4da;">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Asignación en dos columnas -->
          <div class="card mb-4" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="fa fa-users text-warning"></i> Asignación
              </h6>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="id_user" class="font-weight-bold">
                      <i class="fa fa-user-tie text-muted mr-1"></i> Responsable
                    </label>
                    <input type="text" class="form-control" id="id_user" name="id_user" required readonly
                           style="background-color: #f8f9fa; border: 1px solid #ced4da;">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="id_cliente" class="font-weight-bold">
                      <i class="fa fa-building text-muted mr-1"></i> Cliente
                    </label>
                    <input type="text" class="form-control" id="id_cliente" name="id_cliente" required readonly
                           style="background-color: #f8f9fa; border: 1px solid #ced4da;">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Notas -->
          <div class="card mb-3" style="border-left: 4px solid #6c757d;">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="fa fa-sticky-note text-secondary"></i> Notas
              </h6>
              <div class="form-group">
                <label for="observacion" class="font-weight-bold">
                  <i class="fa fa-comment-dots text-muted mr-1"></i> Notas adicionales
                </label>
                <textarea class="form-control" id="observacion" name="observacion" rows="4" readonly
                          style="background-color: #f8f9fa; border: 1px solid #ced4da; resize: none;"
                          placeholder="Sin notas registradas..."></textarea>
              </div>
            </div>
          </div>

        </div>
        
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fa fa-times mr-1"></i> Cerrar
          </button>
          <!--
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save mr-1"></i> Actualizar
          </button>
           -->
        </div>
      </form>
    </div>
  </div>
</div>



<!--=====================================
MODAL AGREGAR actividad
======================================-->

<!-- Modal -->

<div id="modalAgregarActividad" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

 

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>

        <h4 class="modal-title">Agregar actividad</h4>

      </div>

 

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <div class="box-body"> 

            <div class="row"> 

                <!-- Descripción -->

                <div class="col-md-12">

                    <div class="form-group">

                        <label>Descripción *</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-tasks"></i></span>

                            <input type="text" class="form-control" name="nuevaActividad" id="nuevaActividad" placeholder="Descripción de la actividad" required>

                        </div>

                    </div>

                </div> 

            </div>


            <div class="row"> 

                <!-- Tipo -->

                <div class="col-md-4">

                    <div class="form-group">

                        <label>Tipo *</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-info-circle"></i></span>

                            <select class="form-control" name="nuevoTipo" id="nuevoTipo" required>

                                <option value="">Seleccionar tipo</option>

                                <?php

                                $tiposModalAgregar = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);

                                foreach($tiposModalAgregar as $tipoModal){

                                    echo '<option value="'.$tipoModal["nombre"].'">'.ucfirst($tipoModal["nombre"]).'</option>';

                                }

                                ?>

                            </select>

                        </div>

                    </div>

                </div>
 

                <!-- Responsable -->

                <div class="col-md-4">

                    <div class="form-group">

                        <label>Responsable *</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-user-plus"></i></span>

                            <select class="form-control" id="nuevoUsuario" name="nuevoUsuario" required>

                                <option value="">Seleccionar responsable</option>

                                <?php

                                $item = null;

                                $valor = null;

                                $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                                foreach ($usuarios as $key => $value) {

                                    echo'<option value="'.$value["id"].'">'.$value["nombre"].'</option>';

                                }

                                ?>

                            </select>

                        </div>

                    </div>

                </div>
 

                <!-- Estado -->

                <div class="col-md-4">

                    <div class="form-group">

                        <label>Estado *</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>

                            <select class="form-control" name="nuevoEstado" id="nuevoEstado" required>

                                <option value="">Seleccionar estado</option>

                                <?php

                                $estadosModalAgregar = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);

                                foreach($estadosModalAgregar as $estadoModal){

                                    echo '<option value="'.$estadoModal["nombre"].'">'.ucfirst($estadoModal["nombre"]).'</option>';

                                }

                                ?>

                            </select>

                        </div>

                    </div>

                </div>

 

            </div>

 

            <div class="row">

 

                <!-- Fecha -->

                <div class="col-md-6">

                    <div class="form-group">

                        <label>Fecha *</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                            <input type="datetime-local" class="form-control" name="nuevaFecha" id="nuevaFecha" required>

                        </div>

                    </div>

                </div>

 

                <!-- Cliente -->

                <div class="col-md-6">

                    <div class="form-group">

                        <label>Cliente</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-user"></i></span>

                            <select class="form-control" id="nuevoCliente" name="nuevoCliente" required>

                                <option value="0">Sin cliente</option>

                                <?php

                                $item = null;

                                $valor = null;

                                $clientes = ControladorClientes::ctrMostrarClientes($item, $valor);

                                foreach ($clientes as $key => $value) {

                                    echo'<option value="'.$value["id"].'">'.$value["nombre"].'</option>';

                                }

                                ?>

                            </select>

                        </div>

                    </div>

                </div>

 

            </div>

 

            <div class="row">

 

                <!-- Observación -->

                <div class="col-md-12">

                    <div class="form-group">

                        <label>Notas</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                            <input type="text" class="form-control" name="nuevaObservacion" id="nuevaObservacion" placeholder="Escribe una nota...">

                        </div>

                    </div>

                </div>

 

            </div>

 

        </div>

      </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar actividad</button>
        </div>
        <input type="hidden" name="paginaOrigen" value="actividades-cuadro.php">
     </form>
     <?php
      $crearActividad = new ControladorActividades();
      $crearActividad -> ctrCrearActividad();
     ?>
    </div>
  </div>
</div>



<!-- MODAL: Cuando no hay actividades -->
<div class="modal fade" id="sinActividadesModal" tabindex="-1" aria-labelledby="sinActividadesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="sinActividadesModalLabel">
          <i class="fa fa-calendar-times"></i> Sin Actividades
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa fa-calendar-times fa-4x text-warning"></i>
        </div>
        <h6>Sin Actividades para esta fecha</h6>
        <p class="text-muted mb-0">No hay actividades programadas para el <strong id="fechaSinActividad"></strong></p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-warning" data-dismiss="modal">
          <i class="fa fa-check"></i> Entendido
        </button>
        <?php if(puedeAccion('actividades', 'crear')): ?>
        <button type="button" class="btn btn-primary" id="crearNuevaActividad">
          <i class="fa fa-plus"></i> Crear Nueva Actividad
        </button>
        <?php else: ?>
        <button type="button" class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear actividades">
          <i class="fa fa-plus"></i> Crear Nueva Actividad
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Scripts necesarios -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales/es.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  
  // Obtener el token directamente de PHP para máxima seguridad
  var csrfTokenValue = '<?php echo CSRF::getToken(); ?>';

  // Configuración global de AJAX para incluir el token CSRF
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfTokenValue
    }
  });

  var calendarEl = document.getElementById('calendar-principal');
  var fechaSeleccionada = null; // Variable para guardar la fecha

  // -------------------------
  // Función para convertir fecha a formato datetime-local
  // -------------------------
  function formatearFechaParaInput(fechaStr) {
    var fecha = new Date(fechaStr + 'T00:00:00');
    var year = fecha.getFullYear();
    var month = String(fecha.getMonth() + 1).padStart(2, '0');
    var day = String(fecha.getDate()).padStart(2, '0');
    var hours = '08'; // Hora por defecto 8:00 AM
    var minutes = '00';
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  // -------------------------
  // Manejador único de eventos del calendario (clics y selección)
  // -------------------------
  function manejadorDeClicCalendario(fechaObj, fechaStr) {
    if (!fechaStr) return;
    
    $.ajax({
      url: 'ajax/actividades.ajax.php',
      type: 'POST',
      dataType: 'json',
      data: { fecha: fechaStr },
      success: function(respuesta) {
        var hayActividades = false;
        if (respuesta && !respuesta.error) {
          if (Array.isArray(respuesta) && respuesta.length > 0) hayActividades = true;
          else if (typeof respuesta === 'object' && respuesta.id !== undefined) hayActividades = true;
        }
        
        if (hayActividades) {
          fillFields(respuesta);
          if (modalSelector) $(modalSelector).find('.modal-title').text('Actividad en ' + fechaStr);
          setTimeout(function() { if (modalSelector) $(modalSelector).modal('show'); }, 50);
        } else {
          mostrarModalSinActividades(fechaObj, fechaStr);
        }
      },
      error: function(err) {
        console.error('Error en AJAX:', err);
        mostrarModalSinActividades(fechaObj, fechaStr);
      }
    });
  }

  // -------------------------
  // Función para mostrar modal sin actividades
  // -------------------------
  function mostrarModalSinActividades(fechaObj, fechaStr) {
    fechaSeleccionada = fechaStr; // Guardar fecha string para el input
    
    var opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    var fechaFormateada = "";
    
    try {
      // Intentar formatear desde el objeto fecha si existe
      if (fechaObj instanceof Date && !isNaN(fechaObj)) {
        fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);
      } else {
        // Fallback a string
        fechaFormateada = new Date(fechaStr + 'T12:00:00').toLocaleDateString('es-ES', opciones);
      }
    } catch (e) {
      fechaFormateada = fechaStr;
    }

    $('#fechaSinActividad').text(fechaFormateada);
    $('#sinActividadesModal').modal('show');
  }

  // -------------------------
  // Detectar modal y formulario existente
  // -------------------------
  var modalSelector = $('#actividadModal').length ? '#actividadModal' : null;
  var formSelector = $('#actividadForm').length ? '#actividadForm' : null;

  function pick(selectors){
    for(var i=0;i<selectors.length;i++){
      if ($(selectors[i]).length) return selectors[i];
    }
    return null;
  }

  var fieldFecha = pick(['#fechaSeleccionada','#fecha']);
  var fieldTitulo = pick(['#tituloEvento','#titulo','#descripcionTitulo','#descripcion']);
  var fieldTipo = pick(['#tipoEvento','#tipo','#editarTipo']);
  var fieldPrioridad = pick(['#prioridad','#estado','#editarEstado']);
  var fieldDescripcion = pick(['#observacion','#descripcion','#editarObservacion','#nuevaObservacion']);
  var fieldIdHidden = pick(['#idActividad','#id_actividad','input[name="idActividad"]']);

  // -------------------------
  // Helper para rellenar campos existentes
  // -------------------------
  function fillFields(obj){
    var actividad = Array.isArray(obj) ? obj[0] : obj;
    if (!actividad) return;

    if (fieldIdHidden && actividad.id !== undefined) $(fieldIdHidden).val(actividad.id);
    if (fieldFecha && actividad.fecha !== undefined) $(fieldFecha).val(actividad.fecha);
    if (fieldTitulo) $(fieldTitulo).val(actividad.descripcion !== undefined ? actividad.descripcion : (actividad.title !== undefined ? actividad.title : ''));
    if (fieldTipo && actividad.tipo !== undefined) $(fieldTipo).val(actividad.tipo);
    if (fieldPrioridad && actividad.estado !== undefined) $(fieldPrioridad).val(actividad.estado);
    if (fieldDescripcion && actividad.observacion !== undefined) {
      $(fieldDescripcion).val(actividad.observacion);
    }
    
    setTimeout(function() {
      if ($('#id_cliente').length && actividad.id_cliente !== undefined) {
        var valorCliente = actividad.nombre_cliente || actividad.id_cliente;
        $('#id_cliente').val(valorCliente);
      }
      if ($('#id_user').length && actividad.id_user !== undefined) {
        var valorUsuario = actividad.nombre_usuario || actividad.id_user;
        $('#id_user').val(valorUsuario);
      }
      if ($('#observacion').length && actividad.observacion !== undefined) {
        $('#observacion').val(actividad.observacion);
        $('#observacion').trigger('change');
      }
      $('#id_cliente').trigger('change');
      $('#id_user').trigger('change');
    }, 100);

    window.ultimaActividad = actividad;
  }

  // -------------------------
  // Inicializar FullCalendar
  // -------------------------
  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'es',
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
     prev: 'Mes anterior',
     next: 'Mes siguiente',
     today: 'Hoy',
     month: 'Mes',
     week: 'Sem',
     day: 'Día',
     list: 'Lista'
    },
    events: 'ajax/actividades.ajax.php?action=listar',
    dateClick: function(info) { manejadorDeClicCalendario(info.date, info.dateStr); },
    select: function(info) { 
      calendar.unselect();
      manejadorDeClicCalendario(info.start, info.startStr); 
    },
    eventClick: function(info) {
      info.jsEvent.preventDefault();
      $.ajax({
        url: 'ajax/actividades.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { idActividad: info.event.id },
        success: function(respuesta) {
          if (respuesta) {
            if (modalSelector) $(modalSelector).find('.modal-title').text('Actividad Asignada');
            if (modalSelector) {
              $(modalSelector).modal('show');
              setTimeout(function() {
                fillFields(respuesta);
              }, 200);
            }
          }
        },
        error: function(err) {
          console.error('Error al pedir actividad por id', err);
        }
      });
    },

    // -------------------------
    // LÓGICA DE TOOLTIPS PREMIUM (FIN DEL DIAGNÓSTICO)
    // -------------------------
    eventDidMount: function(info) {
        var props = info.event.extendedProps;
        var statusColor = info.event.backgroundColor || '#3c8dbc';
        var titulo = props.descripcion_original || info.event.title;
        var cliente = (props.nombre_cliente && props.nombre_cliente !== 'Sin cliente') ? "\n👤 Cliente: " + props.nombre_cliente : "";
        var estado = props.estado ? "\n🚩 Estado: " + props.estado : "";
        var fecha = props.fecha_full ? "\n📅 Fecha: " + props.fecha_full : "";
        var usuario = props.nombre_usuario ? "\n👤 Resp: " + props.nombre_usuario : "";

        // Tooltip nativo multilínea
        var tooltipFull = "📝 Actividad: " + titulo + 
                         cliente + 
                         estado + 
                         fecha + 
                         usuario;

        info.el.setAttribute('title', tooltipFull);
    },

    editable: true,
    selectable: true,
    dayMaxEvents: true
  });

  calendar.render();

  // -------------------------
  // EVENTO DEL BOTÓN "Crear Nueva Actividad" 
  // -------------------------
  $('#crearNuevaActividad').on('click', function() {
    // Cerrar modal actual
    $('#sinActividadesModal').modal('hide');
    
    // Esperar y abrir modal de agregar
    setTimeout(function() {
      // Limpiar formulario
      $('#modalAgregarActividad form')[0].reset();
      
      // Abrir modal
      $('#modalAgregarActividad').modal('show');
      
      // Prellenar fecha
      if (fechaSeleccionada) {
        console.log("📅 Prellenando fecha:", fechaSeleccionada);
        var fechaFormateada = formatearFechaParaInput(fechaSeleccionada);
        console.log("📅 Fecha formateada:", fechaFormateada);
        
        $('#nuevaFecha').val(fechaFormateada);
        
        setTimeout(function() {
          console.log("📅 Fecha verificada en campo:", $('#nuevaFecha').val());
        }, 100);
      }
    }, 300);
  });

  // Variables globales
  window.ultimaActividad = null;

});
</script>

<?php
// Inclusión de modales
