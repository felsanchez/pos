<!-- En tu <head>, usar SOLO esta línea: -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


<style>
/* Mejoras visuales para el modal */
.modal-content {
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.modal-header {
  border-radius: 10px 10px 0 0;
  border-bottom: 3px solid rgba(255,255,255,0.2);
}

.card {
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,0.12);
  transform: translateY(-1px);
}

.card-title {
  color: #495057;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 8px;
  margin-bottom: 15px;
}

.form-control[readonly] {
  background-color: #f8f9fa !important;
  opacity: 1;
  cursor: default;
}

.form-control[readonly]:focus {
  border-color: #80bdff;
  box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

label.font-weight-bold {
  color: #495057;
  font-size: 13px;
  margin-bottom: 5px;
}

.modal-footer {
  padding: 20px;
}

/* Indicadores de estado por colores */
.border-primary { border-color: #007bff !important; }
.border-success { border-color: #28a745 !important; }
.border-warning { border-color: #ffc107 !important; }
.border-secondary { border-color: #6c757d !important; }

/* Animaciones suaves */
.modal.fade .modal-dialog {
  transition: transform 0.4s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-dialog {
    margin: 10px;
  }
  
  .card {
    margin-bottom: 15px;
  }
  
  .row .col-md-6 {
    margin-bottom: 10px;
  }
}
</style>


<!--Cambia los tamaños de los botones del calendario-->
<style>
/* Botones normales en desktop */
#calendar .fc-button {
    font-size: 0.95em !important;
    padding: 0.25em 0.5em !important;
}

/* Botones más pequeños en móvil */
@media (max-width: 768px) {
    #calendar .fc-button {
        font-size: 0.80em !important;
        padding: 0.2em 0.4em !important;
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
        <div class="card-header bg-primary text-white">
          <!--<h3 class="card-title"><i class="fa fa-calendar-alt"></i> Calendario</h3>-->
          <h3 class="card-title"></h3>
        </div>
        <div class="card-body">
          <div id="calendar"></div>
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

          <!-- Sección de Observaciones -->
          <div class="card mb-3" style="border-left: 4px solid #6c757d;">
            <div class="card-body">
              <h6 class="card-title mb-3">
                <i class="fa fa-sticky-note text-secondary"></i> Observaciones
              </h6>
              <div class="form-group">
                <label for="observacion" class="font-weight-bold">
                  <i class="fa fa-comment-dots text-muted mr-1"></i> Notas adicionales
                </label>
                <textarea class="form-control" id="observacion" name="observacion" rows="4" readonly
                          style="background-color: #f8f9fa; border: 1px solid #ced4da; resize: none;"
                          placeholder="Sin observaciones registradas..."></textarea>
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

                        <label>Observación</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>

                            <input type="text" class="form-control" name="nuevaObservacion" id="nuevaObservacion" placeholder="Observaciones adicionales">

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

  var calendarEl = document.getElementById('calendar');
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
  // Función para mostrar modal sin actividades
  // -------------------------
  function mostrarModalSinActividades(fecha) {
    fechaSeleccionada = fecha; // Guardar fecha
    var fechaFormateada = new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
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

    // CLICK EN UNA FECHA
    dateClick: function(info) {
      console.log("Fecha clickeada:", info.dateStr);
      
      $.ajax({
        url: 'ajax/actividades.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { fecha: info.dateStr },
        success: function(respuesta) {
          console.log("Respuesta del servidor:", respuesta);
          
          var hayActividades = false;
          if (respuesta) {
            if (Array.isArray(respuesta)) {
              hayActividades = respuesta.length > 0;
            } else if (typeof respuesta === 'object') {
              hayActividades = Object.keys(respuesta).length > 0;
            }
          }
          
          if (hayActividades) {
            // HAY ACTIVIDADES: Mostrar modal normal
            console.log("✅ Hay actividades, mostrando modal de edición");
            fillFields(respuesta);
            if (modalSelector) $(modalSelector).find('.modal-title').text('Actividad en ' + info.dateStr);
            setTimeout(function() {
              if (modalSelector) $(modalSelector).modal('show');
            }, 50);
          } else {
            // NO HAY ACTIVIDADES: Mostrar modal de "Sin Actividades"
            console.log("❌ No hay actividades, mostrando modal de aviso");
            mostrarModalSinActividades(info.dateStr);
          }
        },
        error: function(err) {
          console.error('Error al consultar actividades por fecha', err);
          mostrarModalSinActividades(info.dateStr);
        }
      });
    },

    // CLICK EN UN EVENTO
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
