<?php
if (!headers_sent()) {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}
$urlActual = "calendario";
?>
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

/* Modales Flexibles y Desplazables (Responsive Scrollable Modals) */
.modal-dialog {
    margin: 30px auto;
}
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
        width: auto !important;
    }
}
.modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    border-radius: 10px;
    overflow: hidden;
}
.modal-header {
    flex-shrink: 0;
}
.modal-body {
    max-height: calc(90vh - 120px);
    overflow-y: auto;
    padding: 15px 20px;
}
.modal-footer {
    flex-shrink: 0;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}
/* Estilo moderno para la barra de desplazamiento (Scrollbar) */
.modal-body::-webkit-scrollbar {
    width: 6px;
}
.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
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
          <h1><i class="fa fa-calendar"></i> Calendario de Google</h1>
        </div>
      </div>
    </div>
  </section>

  <!-- Contenido principal -->
  <section class="content">
    <div class="container-fluid">
      <div class="box">
        <div class="box-header with-border">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; width: 100%;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              <?php
                require_once "controladores/google.controlador.php";
                $idUsuarioLogueado = $_SESSION["id"] ?? null;
                $urlAutorizacionGoogle = ControladorGoogle::ctrObtenerUrlAutorizacion($idUsuarioLogueado);

                $dbUser = Conexion::conectar()->prepare("SELECT google_account_email FROM usuarios WHERE id = :id");
                $dbUser->execute([":id" => $idUsuarioLogueado]);
                $dataUserG = $dbUser->fetch(PDO::FETCH_ASSOC);
                $emailGoogleUser = $dataUserG["google_account_email"] ?? null;
              ?>

              <?php if (!puedeAccion('actividades', 'crear')): ?>
                <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear actividades">
                  <i class="fa fa-plus"></i> Agregar
                </button>
              <?php elseif (!$emailGoogleUser): ?>
                <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="Debe conectar su cuenta de Google Calendar para agregar actividades">
                  <i class="fa fa-plus"></i> Agregar
                </button>
              <?php else: ?>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarActividad">
                  <i class="fa fa-plus"></i> Agregar
                </button>
              <?php endif; ?>

              <?php if (puedeAccion('actividades', 'editar')): ?>
                <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarEstados">
                  <i class="fa fa-flag"></i> Gestionar estados
                </button>
              <?php else: ?>
                <button class="btn btn-default" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para gestionar estados">
                  <i class="fa fa-flag"></i> Gestionar estados
                </button>
              <?php endif; ?>

              <?php if ($emailGoogleUser): ?>
                <div class="btn-group">
                  <span class="btn btn-success disabled" style="opacity: 1; cursor: default; background-color: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; font-weight: 600;">
                    <i class="fa fa-google text-danger"></i> Google Conectado: <?php echo htmlspecialchars($emailGoogleUser); ?>
                  </span>
                  <button type="button" class="btn btn-info btnSincronizarGoogle" style="font-weight: 600;" title="Sincronizar eventos y tareas desde Google">
                    <i class="fa fa-refresh"></i> Sincronizar desde Google
                  </button>
                  <button type="button" class="btn btn-danger btnDesconectarGoogle" title="Desconectar Google Calendar">
                    <i class="fa fa-power-off"></i>
                  </button>
                </div>
              <?php else: ?>
                <a href="<?php echo $urlAutorizacionGoogle; ?>" class="btn btn-default" style="border-color: #4285F4; color: #4285F4; font-weight: 600;" title="Vincular con tu cuenta de Google Calendar & Tasks">
                  <i class="fa fa-google text-danger"></i> Conectar Google Calendar
                </a>
              <?php endif; ?>

              <a href="https://calendar.google.com" target="_blank" rel="noopener noreferrer" class="btn btn-default" style="font-weight: 600;" title="Abrir Google Calendar en una nueva pestaña">
                <i class="fa fa-external-link text-primary"></i> Ir a Google Calendar
              </a>
            </div>

            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
              <input type="hidden" id="filtroTipo" value="">
              <div style="display: flex; align-items: center; gap: 8px;">
                <span><b>Estado:</b></span>
                <div class="input-group" style="width: 200px;">
                  <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;"><i class="fa fa-search text-primary"></i></span>
                  <select class="form-control select2" id="filtroEstado" style="width: 100%;">
                    <option value="">Mostrar Todos</option>
                    <?php
                    $estadosFiltro = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
                    foreach ($estadosFiltro as $estadoFiltroItem) {
                      echo '<option value="' . $estadoFiltroItem["nombre"] . '">' . ucfirst($estadoFiltroItem["nombre"]) . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="box-body">

          <div id="calendar-principal"></div>
        </div>
      </div>
    </div>
  </section>

</div>

<!-- Modal Mostrar Actividad -->
<div class="modal fade" id="actividadModal" tabindex="-1" role="dialog" aria-labelledby="actividadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: none; border-top: 5px solid #3c8dbc;">
      
      <div class="modal-header" style="border-bottom: 1px solid #f4f4f4; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between;">
        <h4 class="modal-title" id="actividadModalLabel" style="font-weight: 600; color: #3c8dbc; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i class="fa fa-calendar-check-o" style="font-size: 20px;"></i> Detalles de la Actividad
        </h4>
      </div>

      <div class="modal-body" style="padding: 20px 25px;">
        <!-- Descripción destacada -->
        <div style="margin-bottom: 20px; border-bottom: 1px solid #f4f4f4; padding-bottom: 15px;">
          <small class="text-muted" style="text-transform: uppercase; font-weight: 600; font-size: 11px; letter-spacing: 0.5px; color: #999;">Actividad / Tarea</small>
          <h3 id="detDescripcion" style="margin: 5px 0 0 0; font-weight: 600; color: #333; font-size: 20px; line-height: 1.3;"></h3>
        </div>

        <div class="row">
          <!-- Columna Izquierda -->
          <div class="col-sm-6" style="border-right: 1px solid #f4f4f4; padding-right: 20px;">
            <div style="margin-bottom: 15px;">
              <span style="display: block; font-size: 11px; text-transform: uppercase; color: #999; font-weight: 600; margin-bottom: 3px;">📅 Fecha y Hora</span>
              <span id="detFecha" style="font-size: 14px; font-weight: 500; color: #444;"></span>
            </div>
          </div>

          <!-- Columna Derecha -->
          <div class="col-sm-6" style="padding-left: 20px;">
            <div style="margin-bottom: 15px;">
              <span style="display: block; font-size: 11px; text-transform: uppercase; color: #999; font-weight: 600; margin-bottom: 5px;">🚩 Estado</span>
              <div id="detEstado" style="display: inline-block;"></div>
            </div>
          </div>
        </div>

        <!-- Notas adicionales -->
        <div style="background: #fffdf5; border-left: 4px solid #f39c12; padding: 15px; border-radius: 6px; margin-top: 20px; border-top: 1px solid #fdf8e2; border-right: 1px solid #fdf8e2; border-bottom: 1px solid #fdf8e2;">
          <h5 style="margin: 0 0 8px 0; font-weight: 600; color: #8a6d3b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
            <i class="fa fa-sticky-note-o"></i> Notas adicionales
          </h5>
          <p id="detObservacion" style="color: #666; font-size: 13px; margin: 0; line-height: 1.4; white-space: pre-wrap; font-style: italic;"></p>
        </div>

        <!-- Ubicación (si aplica) -->
        <div id="detUbicacionContainer"></div>

        <!-- Invitados (si aplica) -->
        <div id="detInvitadosContainer"></div>

        <!-- Botón Videoconferencia Google Meet (si aplica) -->
        <div id="detMeetContainer"></div>

        <!-- Archivo adjunto (si aplica) -->
        <div id="detArchivoContainer"></div>
      </div>

      <div class="modal-footer" style="background-color: #fcfcfc; border-top: 1px solid #f4f4f4; padding: 12px 20px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 500; border-radius: 6px;">
          <i class="fa fa-times mr-1"></i> Cerrar
        </button>
        <?php if(puedeAccion('actividades', 'editar')): ?>
        <button type="button" class="btn btn-warning" id="btnIrAEditarActividad" style="font-weight: 500; border-radius: 6px; color: white !important;">
           <i class="fa fa-pencil"></i> Editar
        </button>
        <?php endif; ?>
        <?php if(puedeAccion('actividades', 'eliminar')): ?>
        <button type="button" class="btn btn-danger btnEliminarActividad" style="font-weight: 500; border-radius: 6px;">
          <i class="fa fa-trash mr-1"></i> Eliminar
        </button>
        <?php endif; ?>
      </div>

      <!-- Mantener los inputs originales ocultos para no romper ninguna lógica de JS existente -->
      <form id="actividadForm" style="display: none;">
        <input type="text" id="descripcion" name="descripcion">
        <input type="text" id="fecha" name="fecha">
        <input type="text" id="tipo" name="tipo">
        <input type="text" id="estado" name="estado">
        <input type="text" id="id_user" name="id_user">
        <input type="text" id="id_cliente" name="id_cliente">
        <textarea id="observacion" name="observacion"></textarea>
      </form>

    </div>
  </div>
</div>


<!--=====================================
MODAL EDITAR ACTIVIDAD
======================================-->
<div id="modalEditarActividad" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post" enctype="multipart/form-data">
        <?php CSRF::insertToken(); ?>

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#f39c12; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" style="color: white !important;"><i class="fa fa-pencil"></i> Editar Tarea/Evento</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">
          <div class="box-body"> 

            <!-- TOGGLE MODO GOOGLE EN EDITAR: EVENTO VS TAREA -->
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
              <div style="background: #f1f5f9; padding: 4px; border-radius: 24px; display: inline-flex; gap: 4px; border: 1px solid #cbd5e1;">
                <button type="button" class="btn btn-primary btnToggleEditarModo" data-modo="evento" id="btnEditarModoEvento" style="border-radius: 20px; padding: 6px 20px; font-weight: 600; box-shadow: none;">
                  <i class="fa fa-calendar"></i> Evento
                </button>
                <button type="button" class="btn btn-default btnToggleEditarModo" data-modo="tarea" id="btnEditarModoTarea" style="border-radius: 20px; padding: 6px 20px; font-weight: 600; background: transparent; border: none; color: #64748b;">
                  <i class="fa fa-check-square-o"></i> Tarea
                </button>
              </div>
              <input type="hidden" name="editarCategoriaGoogle" id="editarCategoriaGoogle" value="evento">
            </div>

            <div class="row"> 
              <!-- Título / Descripción -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Título / Descripción *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tasks"></i></span>
                    <input type="text" class="form-control" name="editarActividad" id="editarActividad" placeholder="Añade un título..." required>
                  </div>
                </div>
              </div> 
            </div>

            <div class="row">
              <!-- Fecha y Hora con Checkbox Todo el día -->
              <div class="col-md-12">
                <div class="form-group">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label id="lblEditarFecha" style="margin: 0; font-weight: 600;">Fecha y Hora *</label>
                    <label style="font-weight: 600; cursor: pointer; margin: 0; font-size: 13px; color: #475569;" title="Marcar si la actividad o tarea dura todo el día sin hora específica">
                      <input type="checkbox" name="editarChkTodoElDia" id="editarChkTodoElDia" value="1" style="vertical-align: middle; cursor: pointer;"> Todo el día
                    </label>
                  </div>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="datetime-local" class="form-control" name="editarFecha" id="editarFecha" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- FILA CAMPOS ESPECÍFICOS DE GOOGLE EVENTO -->
            <div class="row secEditarGoogleEvento">
              <!-- Videoconferencia Google Meet -->
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #0284c7; font-weight: 600;"><i class="fa fa-video-camera"></i> Videoconferencia</label>
                  <div class="checkbox" style="margin-top: 5px;">
                    <label style="font-weight: 600; cursor: pointer;">
                      <input type="checkbox" name="editarGenerarMeet" id="editarGenerarMeet" value="1">
                      Añadir videoconferencia de Google Meet 📹
                    </label>
                  </div>
                </div>
              </div>

              <!-- Ubicación -->
              <div class="col-md-6">
                <div class="form-group">
                  <label><i class="fa fa-map-marker text-danger"></i> Ubicación</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <input type="text" class="form-control" name="editarUbicacion" id="editarUbicacion" placeholder="Añadir ubicación presencial...">
                  </div>
                </div>
              </div>
            </div>

            <div class="row secEditarGoogleEvento" style="margin-bottom: 10px;">
              <!-- Invitados Email -->
              <div class="col-md-12">
                <div class="form-group">
                  <label><i class="fa fa-envelope text-primary"></i> Invitados (Correos electrónicos separados por coma)</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    <input type="text" class="form-control" name="editarInvitados" id="editarInvitados" placeholder="ejemplo1@gmail.com, cliente@empresa.com">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- Observación -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Notas / Descripción adicional</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                    <input type="text" class="form-control" name="editarObservacion" id="editarObservacion" placeholder="Escribe una nota o detalles adicionales...">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- Archivo Adjunto -->
              <div class="col-md-12">
                <div class="form-group">
                  <label><i class="fa fa-paperclip"></i> Archivo Adjunto</label>
                  <div id="previewArchivoActual" style="margin-bottom: 8px;"></div>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-file"></i></span>
                    <input type="file" class="form-control" name="editarArchivoActividad" id="editarArchivoActividad">
                  </div>
                  <input type="hidden" name="archivoActual" id="archivoActual">
                  <p class="help-block" style="font-size: 11px; margin-top: 3px; color: #777;">Formatos permitidos: imágenes, PDF, Word, Excel, etc.</p>
                </div>
              </div>
            </div>

            <div class="row"> 
              <!-- Estado -->
              <div class="col-md-12">
                <div class="form-group">
                  <label>Estado *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    <select class="form-control" name="editarEstado" id="editarEstado" required>
                      <option value="">Seleccionar estado</option>
                      <?php
                      $estadosModalEditar = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);
                      foreach($estadosModalEditar as $estadoModal){
                          echo '<option value="'.$estadoModal["nombre"].'">'.ucfirst($estadoModal["nombre"]).'</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <input type="hidden" id="editarTipo" name="editarTipo" value="Reunión">
              <input type="hidden" id="editarUsuario" name="editarUsuario">
              <input type="hidden" id="editarCliente" name="editarCliente" value="0">
            </div>

          </div>
        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-warning" style="color: white !important;">Guardar cambios</button>
        </div>
        <input type="hidden" name="idActividad" id="editarIdActividad">
        <input type="hidden" name="urlActual" value="calendario">
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

        <h4 class="modal-title">Agregar Tarea/Evento</h4>

      </div>

 

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <div class="box-body"> 

            <!-- TOGGLE MODO GOOGLE: EVENTO VS TAREA (ESTILO GOOGLE CALENDAR) -->
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
              <div style="background: #f1f5f9; padding: 4px; border-radius: 24px; display: inline-flex; gap: 4px; border: 1px solid #cbd5e1;">
                <button type="button" class="btn btn-primary btnToggleModo" data-modo="evento" id="btnModoEvento" style="border-radius: 20px; padding: 6px 20px; font-weight: 600; box-shadow: none;">
                  <i class="fa fa-calendar"></i> Evento
                </button>
                <button type="button" class="btn btn-default btnToggleModo" data-modo="tarea" id="btnModoTarea" style="border-radius: 20px; padding: 6px 20px; font-weight: 600; background: transparent; border: none; color: #64748b;">
                  <i class="fa fa-check-square-o"></i> Tarea
                </button>
              </div>
              <input type="hidden" name="categoriaGoogle" id="categoriaGoogle" value="evento">
            </div>

            <div class="row"> 

                <!-- Descripción -->

                <div class="col-md-12">

                    <div class="form-group">

                        <label>Título / Descripción *</label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-tasks"></i></span>

                            <input type="text" class="form-control" name="nuevaActividad" id="nuevaActividad" placeholder="Añade un título..." required>

                        </div>

                    </div>

                </div> 

            </div>

            <div class="row">
                <!-- Fecha y Hora con Checkbox Todo el día (Estilo Google Calendar) -->
                <div class="col-md-12">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label id="lblNuevaFecha" style="margin: 0; font-weight: 600;">Fecha y Hora *</label>
                            <label style="font-weight: 600; cursor: pointer; margin: 0; font-size: 13px; color: #475569;" title="Marcar si la actividad o tarea dura todo el día sin hora específica">
                                <input type="checkbox" name="chkTodoElDia" id="chkTodoElDia" value="1" style="vertical-align: middle; cursor: pointer;"> Todo el día
                            </label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="datetime-local" class="form-control" name="nuevaFecha" id="nuevaFecha" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA CAMPOS ESPECÍFICOS DE GOOGLE EVENTO -->
            <div class="row secGoogleEvento">
                <!-- Videoconferencia Google Meet -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="color: #0284c7; font-weight: 600;"><i class="fa fa-video-camera"></i> Videoconferencia</label>
                        <div class="checkbox" style="margin-top: 5px;">
                            <label style="font-weight: 600; cursor: pointer;">
                                <input type="checkbox" name="generarMeet" id="generarMeet" value="1">
                                Añadir videoconferencia de Google Meet 📹
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-map-marker text-danger"></i> Ubicación</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                            <input type="text" class="form-control" name="nuevaUbicacion" id="nuevaUbicacion" placeholder="Añadir ubicación presencial...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row secGoogleEvento" style="margin-bottom: 10px;">
                <!-- Invitados Email -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label><i class="fa fa-envelope text-primary"></i> Invitados (Correos electrónicos separados por coma)</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-users"></i></span>
                            <input type="text" class="form-control" name="nuevosInvitados" id="nuevosInvitados" placeholder="ejemplo1@gmail.com, cliente@empresa.com">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Observación -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Notas / Descripción adicional</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                            <input type="text" class="form-control" name="nuevaObservacion" id="nuevaObservacion" placeholder="Escribe una nota o detalles adicionales...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Archivo Adjunto -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label><i class="fa fa-paperclip"></i> Adjuntar Archivo</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-file"></i></span>
                            <input type="file" class="form-control" name="nuevoArchivoActividad" id="nuevoArchivoActividad">
                        </div>
                        <p class="help-block" style="font-size: 11px; margin-top: 3px; color: #777;">Formatos permitidos: imágenes, PDF, Word, Excel, etc.</p>
                    </div>
                </div>
            </div>

            <div class="row"> 
                <!-- Estado -->
                <div class="col-md-12">
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

                <input type="hidden" id="nuevoTipo" name="nuevoTipo" value="Reunión">
                <input type="hidden" id="nuevoUsuario" name="nuevoUsuario" value="<?php echo $_SESSION['id']; ?>">
                <input type="hidden" id="nuevoCliente" name="nuevoCliente" value="0">
            </div>

 

        </div>

      </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        <input type="hidden" name="urlActual" value="calendario">
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
          <i class="fa fa-calendar-times"></i> Sin tarea/evento
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="fa fa-calendar-times fa-4x text-warning"></i>
        </div>
        <h6>Sin tarea/evento para esta fecha</h6>
        <p class="text-muted mb-0">No hay tareas o eventos programados para el <strong id="fechaSinActividad"></strong></p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-warning" data-dismiss="modal">
          <i class="fa fa-check"></i> Entendido
        </button>
        <?php if(!puedeAccion('actividades', 'crear')): ?>
        <button type="button" class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear tareas o eventos">
          <i class="fa fa-plus"></i> Crear nueva tarea/evento
        </button>
        <?php elseif(!$emailGoogleUser): ?>
        <button type="button" class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="Debe conectar su cuenta de Google Calendar para crear tareas o eventos">
          <i class="fa fa-plus"></i> Crear nueva tarea/evento
        </button>
        <?php else: ?>
        <button type="button" class="btn btn-primary" id="crearNuevaActividad">
          <i class="fa fa-plus"></i> Crear nueva tarea/evento
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
  // Función para convertir fecha a formato datetime-local o date
  // -------------------------
  function formatearFechaParaInput(fechaStr, isAllDay) {
    if (!fechaStr) {
      var d = new Date();
      var year = d.getFullYear();
      var month = String(d.getMonth() + 1).padStart(2, '0');
      var day = String(d.getDate()).padStart(2, '0');
      var hours = String(d.getHours()).padStart(2, '0');
      var minutes = String(d.getMinutes()).padStart(2, '0');
      return isAllDay ? `${year}-${month}-${day}` : `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    var cleanStr = String(fechaStr).trim().replace(' ', 'T');

    if (cleanStr.length === 10) {
      return isAllDay ? cleanStr : cleanStr + 'T08:00';
    }

    var parts = cleanStr.split('T');
    var datePart = parts[0];
    var timePart = parts[1] ? parts[1].substring(0, 5) : '08:00';

    if (isAllDay) {
      return datePart;
    } else {
      return datePart + 'T' + timePart;
    }
  }

  function manejadorDeClicCalendario(fechaObj, fechaStr) {
    if (!fechaStr) return;
    fechaSeleccionada = fechaStr; // Guardar fecha seleccionada inmediatamente
    
    $.ajax({
      url: 'ajax/actividades.ajax.php',
      type: 'POST',
      dataType: 'json',
      data: { 
        fecha: fechaStr,
        filtroTipo: $('#filtroTipo').val() || '',
        filtroEstado: $('#filtroEstado').val() || ''
      },
      success: function(respuesta) {
        var hayActividades = false;
        if (respuesta && !respuesta.error) {
          if (Array.isArray(respuesta) && respuesta.length > 0) hayActividades = true;
          else if (typeof respuesta === 'object' && respuesta.id !== undefined) hayActividades = true;
        }
        
        if (hayActividades) {
          mostrarModalYaTieneActividades(fechaObj, fechaStr);
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
  // Función para mostrar modal cuando ya tiene actividades
  // -------------------------
  function mostrarModalYaTieneActividades(fechaObj, fechaStr) {
    fechaSeleccionada = fechaStr; // Guardar fecha string para el input
    
    var opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    var fechaFormateada = "";
    
    try {
      if (fechaObj instanceof Date && !isNaN(fechaObj)) {
        fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);
      } else {
        fechaFormateada = new Date(fechaStr + 'T12:00:00').toLocaleDateString('es-ES', opciones);
      }
    } catch (e) {
      fechaFormateada = fechaStr;
    }

    $('#fechaConActividad').text(fechaFormateada);
    $('#yaTieneActividadesModal').modal('show');
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
  var fieldPrioridad = pick(['#prioridad','#estado']);
  var fieldDescripcion = pick(['#observacion','#descripcion','#editarObservacion','#nuevaObservacion']);
  var fieldIdHidden = pick(['#idActividad','#id_actividad','input[name="idActividad"]']);

  // -------------------------
  // Helper para rellenar campos existentes
  // -------------------------
  function fillFields(obj){
    window.ultimaActividad = Array.isArray(obj) ? obj[0] : obj;
    var actividad = window.ultimaActividad;
    if (!actividad) return;

    var catG = (actividad.categoria_google || '').toLowerCase();
    var tipoAct = (actividad.tipo || '').toLowerCase();
    var esTarea = (catG === 'tarea' || tipoAct === 'tarea');
    var tituloModal = esTarea ? 'Tarea Asignada' : 'Evento Asignado';
    if (modalSelector) $(modalSelector).find('.modal-title').text(tituloModal);

    // Rellenar elementos visuales del nuevo modal
    $('#detDescripcion').text(actividad.descripcion !== undefined ? actividad.descripcion : (actividad.title !== undefined ? actividad.title : ''));
    $('#detFecha').text(actividad.fecha || '');
    $('#detTipo').text(actividad.tipo ? actividad.tipo.charAt(0).toUpperCase() + actividad.tipo.slice(1) : 'Sin tipo');
    
    var estadoHtml = '';
    if (actividad.estado) {
      var colorEstado = '#3c8dbc';
      var estadoLower = actividad.estado.toLowerCase();
      if (estadoLower === 'programada' || estadoLower === 'pendiente') colorEstado = '#f39c12';
      else if (estadoLower === 'completada' || estadoLower === 'finalizada') colorEstado = '#808080';
      else if (estadoLower === 'cancelada') colorEstado = '#dd4b39';
      estadoHtml = `<span class="label" style="background-color: ${colorEstado}; font-size: 11px; padding: 4px 8px; text-transform: uppercase;">${actividad.estado}</span>`;
    }
    $('#detEstado').html(estadoHtml);

    $('#detUsuario').text(actividad.nombre_usuario || 'Sin responsable');
    $('#detCliente').text(actividad.nombre_cliente && actividad.nombre_cliente !== 'Sin cliente' ? actividad.nombre_cliente : 'Sin cliente');
    
    var obsText = actividad.observacion ? actividad.observacion : 'Sin notas registradas...';
    var safeObs = $('<div>').text(obsText).html();
    var urlRegex = /((?:https?:\/\/|www\.|drive\.google\.com|docs\.google\.com)[^\s<]+[^\s<.,:;"')\]>])/gi;
    safeObs = safeObs.replace(urlRegex, function(url) {
      var href = url.match(/^https?:\/\//i) ? url : 'http://' + url;
      return '<a href="' + href + '" target="_blank" rel="noopener noreferrer" style="text-decoration:underline; font-weight:600; color:#3c8dbc;">' + url + '</a>';
    });
    safeObs = safeObs.replace(/\n/g, '<br>');
    $('#detObservacion').html(safeObs);

    if (actividad.ubicacion) {
      var safeUbic = $('<div>').text(actividad.ubicacion).html();
      $('#detUbicacionContainer').html('<div style="margin-top: 12px; background: #fff5f5; border-left: 4px solid #dd4b39; padding: 10px 12px; border-radius: 6px;"><span style="display: block; font-size: 11px; text-transform: uppercase; color: #dd4b39; font-weight: 600; margin-bottom: 3px;"><i class="fa fa-map-marker"></i> Ubicación</span><span style="font-size: 13px; color: #333;">' + safeUbic + '</span></div>');
    } else {
      $('#detUbicacionContainer').html('');
    }

    if (actividad.invitados_email) {
      var safeInv = $('<div>').text(actividad.invitados_email).html();
      $('#detInvitadosContainer').html('<div style="margin-top: 12px; background: #f0f7ff; border-left: 4px solid #3c8dbc; padding: 10px 12px; border-radius: 6px;"><span style="display: block; font-size: 11px; text-transform: uppercase; color: #3c8dbc; font-weight: 600; margin-bottom: 3px;"><i class="fa fa-users"></i> Invitados</span><span style="font-size: 13px; color: #333;">' + safeInv + '</span></div>');
    } else {
      $('#detInvitadosContainer').html('');
    }

    var isMeetValid = actividad.google_meet_link && (actividad.google_meet_link.indexOf('meet.google.com') !== -1 || actividad.google_meet_link.indexOf('hangouts') !== -1);

    if (isMeetValid) {
      $('#detMeetContainer').html('<a href="'+actividad.google_meet_link+'" target="_blank" class="btn btn-info btn-block" style="background-color: #00832d; border-color: #00832d; font-weight: 600; margin-top: 15px; border-radius: 6px;"><i class="fa fa-video-camera"></i> Unirse con Google Meet 📹</a>');
    } else {
      $('#detMeetContainer').html('');
    }

    var fileLink = actividad.archivo || null;
    if (!fileLink && actividad.observacion) {
      var matchFile = actividad.observacion.match(/(https?:\/\/[^\s<]+)/i);
      if (matchFile) {
        fileLink = matchFile[0].replace(/[.,;:)"\']+$/, '');
      }
    }

    if (fileLink) {
      $('#detArchivoContainer').html('<a href="'+fileLink+'" target="_blank" class="btn btn-default btn-block" style="font-weight: 600; margin-top: 10px; border-radius: 6px; border-color: #3c8dbc; color: #3c8dbc;"><i class="fa fa-paperclip"></i> Ver / Descargar archivo adjunto 📎</a>');
      $('#archivoActual').val(fileLink);
      $('#previewArchivoActual').html('<a href="'+fileLink+'" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-file"></i> Ver archivo adjunto actual</a>');
    } else {
      $('#detArchivoContainer').html('');
      $('#archivoActual').val('');
      $('#previewArchivoActual').html('');
    }

    // Rellenar campos originales ocultos (por compatibilidad)
    if (fieldIdHidden && actividad.id !== undefined) $(fieldIdHidden).val(actividad.id);
    if (fieldFecha && actividad.fecha !== undefined) $(fieldFecha).val(actividad.fecha);
    if (fieldTitulo) $(fieldTitulo).val(actividad.descripcion !== undefined ? actividad.descripcion : (actividad.title !== undefined ? actividad.title : ''));
    if (fieldTipo && actividad.tipo !== undefined) $(fieldTipo).val(actividad.tipo);
    if (fieldPrioridad && actividad.estado !== undefined) $(fieldPrioridad).val(actividad.estado);
    if (fieldDescripcion && actividad.observacion !== undefined) {
      $(fieldDescripcion).val(actividad.observacion);
    }

    // Rellenar formulario de edición (modalEditarActividad)
    $('#editarIdActividad').val(actividad.id || '');
    $('#editarActividad').val(actividad.descripcion || '');
    $('#editarTipo').val(actividad.tipo || 'Reunión');
    $('#editarUsuario').val(actividad.id_user || '');
    
    // Rellenar estado de manera insensible a mayúsculas/minúsculas y con soporte de alias
    if (actividad.estado) {
      var estBuscado = $.trim(actividad.estado).toLowerCase();
      var valorEncontrado = '';
      $('#editarEstado option').each(function() {
        var optVal = $.trim($(this).val());
        if (optVal.toLowerCase() === estBuscado) {
          valorEncontrado = optVal;
          return false;
        }
      });

      if (!valorEncontrado) {
        if (estBuscado === 'finalizada' || estBuscado === 'completed') estBuscado = 'completada';
        else if (estBuscado === 'scheduled') estBuscado = 'programada';
        else if (estBuscado === 'pending' || estBuscado === 'needsaction') estBuscado = 'pendiente';
        else if (estBuscado === 'canceled') estBuscado = 'cancelada';

        $('#editarEstado option').each(function() {
          var optVal = $.trim($(this).val());
          if (optVal.toLowerCase() === estBuscado) {
            valorEncontrado = optVal;
            return false;
          }
        });
      }

      if (valorEncontrado) {
        $('#editarEstado').val(valorEncontrado);
      } else if (actividad.estado) {
        var textoEstado = actividad.estado.charAt(0).toUpperCase() + actividad.estado.slice(1);
        if (!$('#editarEstado option[value="' + actividad.estado + '"]').length) {
          $('#editarEstado').append(new Option(textoEstado, actividad.estado, true, true));
        }
        $('#editarEstado').val(actividad.estado);
      }
    } else {
      $('#editarEstado').val('');
    }

    $('#editarCliente').val(actividad.id_cliente || '0');
    $('#editarObservacion').val(actividad.observacion || '');
    $('#editarUbicacion').val(actividad.ubicacion || '');
    $('#editarInvitados').val(actividad.invitados_email || '');
    $('#editarGenerarMeet').prop('checked', !!isMeetValid);

    var modoG = (actividad.categoria_google || 'evento').toLowerCase();
    $('#editarCategoriaGoogle').val(modoG);
    $('.btnToggleEditarModo').removeClass('btn-primary active').addClass('btn-default').css({'background':'transparent', 'color':'#64748b', 'border':'none'});
    if (modoG === 'tarea') {
      $('#btnEditarModoTarea').removeClass('btn-default').addClass('btn-primary active').css({'background':'#3c8dbc', 'color':'#fff', 'border':'none'});
      $('.secEditarGoogleEvento').hide();
      $('#lblEditarFecha').text('Fecha y Hora *');
      $('#editarTipo').val('Tarea');
      $('#modalEditarActividad .modal-title').html('<i class="fa fa-pencil"></i> Editar Tarea');
    } else {
      $('#btnEditarModoEvento').removeClass('btn-default').addClass('btn-primary active').css({'background':'#3c8dbc', 'color':'#fff', 'border':'none'});
      $('.secEditarGoogleEvento').show();
      $('#lblEditarFecha').text('Fecha y Hora *');
      $('#editarTipo').val('Reunión');
      $('#modalEditarActividad .modal-title').html('<i class="fa fa-pencil"></i> Editar Evento');
    }

    var isAllDay = actividad.allDay || false;
    var rawFecha = actividad.fecha_full || actividad.fecha || '';
    if (rawFecha && (rawFecha.indexOf('00:00:00') !== -1 || rawFecha.length <= 10)) {
      isAllDay = true;
    }
    $('#editarChkTodoElDia').prop('checked', isAllDay);
    var dateFormatted = formatearFechaParaInput(rawFecha, isAllDay);
    $('#editarFecha').attr('type', isAllDay ? 'date' : 'datetime-local').val(dateFormatted);
    
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
  window.fillFields = fillFields;

  // -------------------------
  // MANEJADOR DE TOGGLE EVENTO VS TAREA (ESTILO GOOGLE CALENDAR)
  // -------------------------
  $(document).on('click', '.btnToggleModo', function() {
    $('.btnToggleModo').removeClass('btn-primary active').addClass('btn-default').css({'background':'transparent', 'color':'#64748b', 'border':'none'});
    $(this).removeClass('btn-default').addClass('btn-primary active').css({'background':'#3c8dbc', 'color':'#fff', 'border':'none'});
    
    var modo = $(this).attr('data-modo');
    $('#categoriaGoogle').val(modo);

    if (modo === 'tarea') {
      $('.secGoogleEvento').slideUp(150);
      $('#lblNuevaFecha').text($('#chkTodoElDia').is(':checked') ? 'Fecha de Recordatorio *' : 'Fecha y Hora de Recordatorio *');
      $('#nuevoTipo').val('Tarea');
    } else {
      $('.secGoogleEvento').slideDown(150);
      $('#lblNuevaFecha').text($('#chkTodoElDia').is(':checked') ? 'Fecha *' : 'Fecha y Hora *');
      $('#nuevoTipo').val('Reunión');
    }
  });

  $(document).on('click', '.btnToggleEditarModo', function() {
    $('.btnToggleEditarModo').removeClass('btn-primary active').addClass('btn-default').css({'background':'transparent', 'color':'#64748b', 'border':'none'});
    $(this).removeClass('btn-default').addClass('btn-primary active').css({'background':'#3c8dbc', 'color':'#fff', 'border':'none'});
    
    var modo = $(this).attr('data-modo');
    $('#editarCategoriaGoogle').val(modo);

    if (modo === 'tarea') {
      $('.secEditarGoogleEvento').slideUp(150);
      $('#lblEditarFecha').text($('#editarChkTodoElDia').is(':checked') ? 'Fecha de Recordatorio *' : 'Fecha y Hora de Recordatorio *');
      $('#editarTipo').val('Tarea');
      $('#modalEditarActividad .modal-title').html('<i class="fa fa-pencil"></i> Editar Tarea');
    } else {
      $('.secEditarGoogleEvento').slideDown(150);
      $('#lblEditarFecha').text($('#editarChkTodoElDia').is(':checked') ? 'Fecha *' : 'Fecha y Hora *');
      $('#editarTipo').val('Reunión');
      $('#modalEditarActividad .modal-title').html('<i class="fa fa-pencil"></i> Editar Evento');
    }
  });

  // -------------------------
  // MANEJADOR DE CHECKBOX "TODO EL DÍA" (ESTILO GOOGLE CALENDAR)
  // -------------------------
  $(document).on('change', '#chkTodoElDia', function() {
    var isAllDay = $(this).is(':checked');
    var $input = $('#nuevaFecha');
    var val = $input.val() || '';

    if (isAllDay) {
      var dateOnly = val ? val.split('T')[0] : new Date().toISOString().split('T')[0];
      $input.attr('type', 'date').val(dateOnly);
      var modo = $('#categoriaGoogle').val();
      $('#lblNuevaFecha').html('<i class="fa fa-calendar-check-o text-success"></i> ' + (modo === 'tarea' ? 'Fecha de Recordatorio *' : 'Fecha *'));
    } else {
      var dateOnly = val ? val.split('T')[0] : new Date().toISOString().split('T')[0];
      var timePart = (val.indexOf('T') !== -1 && val.split('T')[1]) ? val.split('T')[1].substring(0, 5) : '08:00';
      $input.attr('type', 'datetime-local').val(dateOnly + 'T' + timePart);
      var modo = $('#categoriaGoogle').val();
      $('#lblNuevaFecha').html(modo === 'tarea' ? 'Fecha y Hora de Recordatorio *' : 'Fecha y Hora *');
    }
  });



  // -------------------------
  // MANEJADOR DE CHECKBOX "TODO EL DÍA" EN EDITAR
  // -------------------------
  $(document).on('change', '#editarChkTodoElDia', function() {
    var isAllDay = $(this).is(':checked');
    var $input = $('#editarFecha');
    var val = $input.val() || '';

    if (isAllDay) {
      var dateOnly = val ? val.split('T')[0] : new Date().toISOString().split('T')[0];
      $input.attr('type', 'date').val(dateOnly);
      var modo = $('#editarCategoriaGoogle').val();
      $('#lblEditarFecha').html('<i class="fa fa-calendar-check-o text-success"></i> ' + (modo === 'tarea' ? 'Fecha de Recordatorio *' : 'Fecha *'));
    } else {
      var dateOnly = val ? val.split('T')[0] : new Date().toISOString().split('T')[0];
      var timePart = (val.indexOf('T') !== -1 && val.split('T')[1]) ? val.split('T')[1].substring(0, 5) : '08:00';
      $input.attr('type', 'datetime-local').val(dateOnly + 'T' + timePart);
      var modo = $('#editarCategoriaGoogle').val();
      $('#lblEditarFecha').html(modo === 'tarea' ? 'Fecha y Hora de Recordatorio *' : 'Fecha y Hora *');
    }
  });

  // -------------------------
  // DESCONECTAR GOOGLE CALENDAR
  // -------------------------
  $(document).on('click', '.btnDesconectarGoogle', function() {
    swal({
      title: '¿Desconectar Google Calendar?',
      text: "Se desvinculará tu cuenta y se eliminarán las tareas y eventos sincronizados con Google.",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, desconectar'
    }).then((result) => {
      if (result.value) {
        $.ajax({
          url: 'ajax/actividades.ajax.php',
          method: 'POST',
          data: { accion: 'desconectarGoogle' },
          dataType: 'json',
          success: function(res) {
            swal({
              type: 'success',
              title: '¡Desconectado!',
              text: 'Se ha desvinculado la cuenta y se limpiaron los eventos y tareas.',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              window.location.reload();
            });
          }
        });
      }
    });
  });

  // -------------------------
  // SINCRONIZAR DESDE GOOGLE
  // -------------------------
  $(document).on('click', '.btnSincronizarGoogle', function() {
    var $btn = $(this);
    var originalHtml = $btn.html();
    $btn.html('<i class="fa fa-refresh fa-spin"></i> Sincronizando...').prop('disabled', true);

    $.ajax({
      url: 'ajax/actividades.ajax.php',
      method: 'POST',
      data: {
        accion: 'sincronizarDesdeGoogle',
        csrf_token: $('meta[name="csrf-token"]').attr('content')
      },
      dataType: 'json',
      success: function(res) {
        $btn.html(originalHtml).prop('disabled', false);
        if (res.status === 'ok') {
          var eliminadosHtml = (res.eliminados && res.eliminados > 0) ? ('🗑️ Novedades eliminadas: <b>' + res.eliminados + '</b><br>') : '';
          swal({
            type: 'success',
            title: '¡Sincronización completada!',
            html: '<b>Se han sincronizado las novedades desde Google:</b><br><br>' +
                  '✨ Novedades nuevas creadas: <b>' + res.importados + '</b><br>' +
                  '🔄 Novedades actualizadas: <b>' + res.actualizados + '</b><br>' +
                  eliminadosHtml +
                  '📋 Total procesadas: <b>' + res.total + '</b>',
            confirmButtonText: 'Excelente'
          }).then(() => {
            if (typeof calendar !== 'undefined') {
              calendar.refetchEvents();
            }
          });
        } else {
          console.error("Respuesta error de sincronización:", res);
          var msgError = (res && res.message) ? res.message : 'No se pudieron sincronizar las novedades desde Google.';
          swal({
            type: 'error',
            title: 'Error de sincronización',
            html: msgError,
            confirmButtonText: 'Cerrar'
          });
        }
      },
      error: function(xhr, status, error) {
        console.error("Error AJAX sincronización:", xhr.responseText);
        $btn.html(originalHtml).prop('disabled', false);
        swal({
          type: 'error',
          title: 'Error de red o servidor',
          html: 'Ocurrió un error al comunicarse con el servidor.<br><small style="color:#777;">' + (xhr.responseText || error) + '</small>',
          confirmButtonText: 'Cerrar'
        });
      }
    });
  });

  <?php if ($emailGoogleUser): ?>
  // Sincronización automática silenciosa al cargar el calendario
  setTimeout(function() {
    $.ajax({
      url: 'ajax/actividades.ajax.php',
      method: 'POST',
      data: {
        accion: 'sincronizarDesdeGoogle',
        csrf_token: $('meta[name="csrf-token"]').attr('content')
      },
      dataType: 'json',
      success: function(res) {
        if (res.status === 'ok' && res.total > 0) {
          if (typeof calendar !== 'undefined') {
            calendar.refetchEvents();
          }
        }
      }
    });
  }, 1000);
  <?php endif; ?>

  // -------------------------
  // Inicializar FullCalendar
  // -------------------------
  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'es',
    initialView: 'dayGridMonth',
    height: 'auto',
    displayEventTime: true,
    eventTimeFormat: {
      hour: '2-digit',
      minute: '2-digit',
      meridiem: 'short',
      hour12: true
    },
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
    events: function(info, successCallback, failureCallback) {
      $.ajax({
        url: 'ajax/actividades.ajax.php?action=listar',
        type: 'GET',
        data: {
          filtroTipo: $('#filtroTipo').val() || '',
          filtroEstado: $('#filtroEstado').val() || ''
        },
        dataType: 'json',
        success: function(response) {
          successCallback(response);
        },
        error: function(err) {
          failureCallback(err);
        }
      });
    },
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
            var actObj = Array.isArray(respuesta) ? respuesta[0] : respuesta;
            var catG = (actObj.categoria_google || '').toLowerCase();
            var tipoAct = (actObj.tipo || '').toLowerCase();
            var esTarea = (catG === 'tarea' || tipoAct === 'tarea');
            var tituloModal = esTarea ? 'Tarea Asignada' : 'Evento Asignado';

            if (modalSelector) $(modalSelector).find('.modal-title').text(tituloModal);
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
    eventDrop: function(info) {
      var idActividad = info.event.id;
      var date = info.event.start;
      
      var year = date.getFullYear();
      var month = String(date.getMonth() + 1).padStart(2, '0');
      var day = String(date.getDate()).padStart(2, '0');
      var hours = String(date.getHours()).padStart(2, '0');
      var minutes = String(date.getMinutes()).padStart(2, '0');
      var seconds = String(date.getSeconds()).padStart(2, '0');
      var nuevaFecha = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
      
      console.log("Moviendo actividad ID " + idActividad + " a fecha: " + nuevaFecha);
      
      $.ajax({
        url: 'ajax/actividades.ajax.php',
        method: 'POST',
        data: {
          id: idActividad,
          fecha: nuevaFecha,
          todo_dia: info.event.allDay ? 1 : 0,
          accion: 'actualizarFecha',
          csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
          if (response === 'ok') {
            swal({
              type: "success",
              title: "¡Guardado!",
              text: "La actividad se ha movido correctamente.",
              timer: 1500,
              showConfirmButton: false
            });
          } else {
            swal({
              type: "error",
              title: "Error",
              text: "No se pudo actualizar la fecha de la actividad.",
              confirmButtonText: "Cerrar"
            });
            info.revert();
          }
        },
        error: function() {
          swal({
            type: "error",
            title: "Error de conexión",
            text: "No se pudo comunicar con el servidor.",
            confirmButtonText: "Cerrar"
          });
          info.revert();
        }
      });
    },
    eventResize: function(info) {
      var idActividad = info.event.id;
      var date = info.event.start;
      
      var year = date.getFullYear();
      var month = String(date.getMonth() + 1).padStart(2, '0');
      var day = String(date.getDate()).padStart(2, '0');
      var hours = String(date.getHours()).padStart(2, '0');
      var minutes = String(date.getMinutes()).padStart(2, '0');
      var seconds = String(date.getSeconds()).padStart(2, '0');
      var nuevaFecha = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

      $.ajax({
        url: 'ajax/actividades.ajax.php',
        method: 'POST',
        data: {
          id: idActividad,
          fecha: nuevaFecha,
          todo_dia: info.event.allDay ? 1 : 0,
          accion: 'actualizarFecha',
          csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
          if (response === 'ok') {
            swal({
              type: "success",
              title: "¡Guardado!",
              text: "Se ha actualizado la duración de la actividad.",
              timer: 1500,
              showConfirmButton: false
            });
          } else {
            info.revert();
          }
        },
        error: function() {
          info.revert();
        }
      });
    },

    // -------------------------
    // LÓGICA DE TOOLTIPS PREMIUM (FIN DEL DIAGNÓSTICO)
    // -------------------------
    eventDidMount: function(info) {
        var props = info.event.extendedProps;
        var estadoLower = (props.estado || '').toLowerCase();
        
        if (estadoLower === 'completada' || estadoLower === 'completed' || estadoLower === 'finalizada') {
            info.el.style.opacity = '0.55';
            info.el.style.filter = 'opacity(55%)';
            info.el.style.textDecoration = 'line-through';
        }

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

  // Escuchar cambios en los filtros para refrescar el calendario
  $('#filtroTipo, #filtroEstado').on('change', function() {
    console.log("Refrescando eventos del calendario por cambio de filtro...");
    calendar.refetchEvents();
  });

  // -------------------------
  // EVENTO DEL BOTÓN "Crear Nueva Actividad" 
  // -------------------------
  $('#crearNuevaActividad, #crearNuevaActividadDeConflicto').on('click', function() {
    $('#sinActividadesModal, #yaTieneActividadesModal').modal('hide');
    
    setTimeout(function() {
      $('#modalAgregarActividad form')[0].reset();
      
      var isAllDay = $('#chkTodoElDia').is(':checked');
      $('#nuevaFecha').attr('type', isAllDay ? 'date' : 'datetime-local');

      if (fechaSeleccionada) {
        var fechaFormateada = formatearFechaParaInput(fechaSeleccionada, isAllDay);
        $('#nuevaFecha').val(fechaFormateada);
      }

      $('#modalAgregarActividad').modal('show');
    }, 300);
  });

  // Al mostrar modalAgregarActividad directamente
  $('#modalAgregarActividad').on('show.bs.modal', function() {
    var isAllDay = $('#chkTodoElDia').is(':checked');
    $('#nuevaFecha').attr('type', isAllDay ? 'date' : 'datetime-local');
    if (fechaSeleccionada) {
      var fechaFormateada = formatearFechaParaInput(fechaSeleccionada, isAllDay);
      $('#nuevaFecha').val(fechaFormateada);
    }
  });

  // -------------------------
  // EVENTO DEL BOTÓN "Eliminar Actividad"
  // -------------------------
  $(document).on('click', '.btnEliminarActividad', function() {
    var idActividad = window.ultimaActividad ? window.ultimaActividad.id : null;
    if (!idActividad) return;

    swal({
      title: '¿Está seguro de eliminar esta actividad?',
      text: "¡No podrá revertir esta acción!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
      if (result.value) {
        $.ajax({
          url: 'ajax/actividades.ajax.php',
          method: 'POST',
          data: {
            idActividadEliminar: idActividad,
            csrf_token: csrfTokenValue
          },
          success: function(respuesta) {
            if (respuesta.trim() === 'ok') {
              swal({
                type: 'success',
                title: '¡Eliminado!',
                text: 'La actividad ha sido eliminada correctamente.',
                timer: 1500,
                showConfirmButton: false
              });
              $('#actividadModal').modal('hide');
              calendar.refetchEvents();
            } else if (respuesta.trim() === 'error_csrf') {
              swal({
                type: 'error',
                title: 'Error de seguridad',
                text: 'Token CSRF inválido. Por favor recarga la página.',
                confirmButtonText: 'Cerrar'
              });
            } else {
              swal({
                type: 'error',
                title: 'Error',
                text: 'No se pudo eliminar la actividad.',
                confirmButtonText: 'Cerrar'
              });
            }
          },
          error: function() {
            swal({
              type: 'error',
              title: 'Error de conexión',
              text: 'No se pudo comunicar con el servidor.',
              confirmButtonText: 'Cerrar'
            });
          }
        });
      }
    });
  });

  // Variables globales
  window.ultimaActividad = null;

});
</script>

<?php
// Inclusión de modales
?>
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
        <h4 class="modal-title">Gestionar Estados de Actividades</h4>
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
            <form role="form" method="post" id="formAgregarEstadoActividad">

              <?php CSRF::insertToken(); ?>
              <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoEstadoNombre" placeholder="Nombre del estado *"
                      required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <input type="color" class="form-control" name="nuevoEstadoColor" value="#3c8dbc">
                  </div>
                </div>
                <div class="col-md-3">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>

              <!-- CAMPO OCULTO PARA ORIGEN -->
              <input type="hidden" name="origenModal" value="calendario">

            </form>
          </div>
        </div>

        <!-- Lista de estados -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Estados Existentes</h3>
          </div>
          <div class="panel-body">
            <table class="table table-bordered table-striped tablaEstadosActividades">
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
                $estadosGestion = ControladorEstadosActividades::ctrMostrarEstadosActividades(null, null);

                // Colocar el estado 'completada' al inicio de la lista
                $completadoItem = null;
                $otrosEstados = array();

                if (is_array($estadosGestion)) {
                    foreach ($estadosGestion as $value) {
                        if (strcasecmp(trim($value["nombre"]), "completada") === 0) {
                            $completadoItem = $value;
                        } else {
                            $otrosEstados[] = $value;
                        }
                    }
                }

                if ($completadoItem !== null) {
                    array_unshift($otrosEstados, $completadoItem);
                }
                $estadosGestion = $otrosEstados;

                foreach ($estadosGestion as $key => $value) {
                  $esCompletada = (strcasecmp(trim($value["nombre"]), "completada") === 0);

                  $acciones = '';
                  if ($esCompletada) {
                      $acciones = '<span class="text-muted" style="font-size: 11px; font-weight: 600;" title="Estado predeterminado del sistema"><i class="fa fa-lock"></i> Protegido</span>';
                  } else {
                      $acciones = '<button class="btn btn-warning btn-xs btnEditarEstadoActividad"
                          data-id="' . $value["id"] . '"
                          data-nombre="' . $value["nombre"] . '"
                          data-color="' . $value["color"] . '"
                          data-orden="' . $value["orden"] . '"
                          data-toggle="modal"
                          data-target="#modalEditarEstadoActividad">
                          <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-xs btnEliminarEstadoActividad" idEstado="' . $value["id"] . '" nombreEstado="' . $value["nombre"] . '"><i class="fa fa-times"></i></button>';
                  }

                  echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td><span class="badge" style="background-color: ' . $value["color"] . '">' . ucfirst($value["nombre"]) . '</span></td>
                      <td><input type="color" value="' . $value["color"] . '" disabled style="width: 50px;"></td>
                      <td>' . $acciones . '</td>
                    </tr>';
                }
                ?>
              </tbody>
            </table>
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

<div id="modalEditarEstadoActividad" class="modal fade" role="dialog" data-backdrop="true" data-keyboard="true" style="z-index: 1060 !important;">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

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

            <div class="form-group">
              <label>Nombre *</label>
              <input type="text" class="form-control" name="editarEstadoNombre" id="editarEstadoNombre" required>
              <input type="hidden" name="idEstado" id="idEstado">
              <input type="hidden" name="editarEstadoOrden" id="editarEstadoOrden">
              <input type="hidden" name="origenModal" value="calendario">
            </div>

            <div class="form-group">
              <label>Color</label>
              <input type="color" class="form-control" name="editarEstadoColor" id="editarEstadoColor">
            </div>

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


<!--=====================================
MODAL GESTIONAR TIPOS
======================================-->

<div id="modalGestionarTipos" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Gestionar Tipos de Actividades</h4>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <!-- Formulario agregar tipo -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nuevo Tipo</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarTipoActividad">

              <?php CSRF::insertToken(); ?>
              <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">
              <div class="row">
                <div class="col-md-9">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nuevoTipoNombre" placeholder="Nombre del tipo *"
                      required>
                  </div>
                </div>
                <div class="col-md-3">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>

              <!-- CAMPO OCULTO PARA ORIGEN -->
              <input type="hidden" name="origenModal" value="calendario">

            </form>
          </div>
        </div>

        <!-- Lista de tipos -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Tipos Existentes</h3>
          </div>
          <div class="panel-body">
            <table class="table table-bordered table-striped tablaTiposActividades">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $tiposGestion = ControladorTiposActividades::ctrMostrarTiposActividades(null, null);
                foreach ($tiposGestion as $key => $value) {
                  echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td>' . ucfirst($value["nombre"]) . '</td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarTipoActividad" idTipo="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarTipoActividad"><i class="fa fa-pencil"></i></button>
                        <button class="btn btn-danger btn-xs btnEliminarTipoActividad" idTipo="' . $value["id"] . '" nombreTipo="' . $value["nombre"] . '"><i class="fa fa-times"></i></button>
                      </td>
                    </tr>';
                }
                ?>
              </tbody>
            </table>
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
MODAL EDITAR TIPO
======================================-->

<div id="modalEditarTipoActividad" class="modal fade" role="dialog" data-backdrop="true" data-keyboard="true" style="z-index: 1060 !important;">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formEditarTipoActividad">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="urlActual" value="<?php echo $urlActual; ?>">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Tipo</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="form-group">
              <label>Nombre *</label>
              <input type="text" class="form-control" name="editarTipoNombre" id="editarTipoNombre" required>
              <input type="hidden" name="idTipo" id="idTipo">
              <input type="hidden" name="editarTipoOrden" id="editarTipoOrden">
              <input type="hidden" name="origenModal" value="calendario">
            </div>

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



<script>
$(document).ready(function() {
	// Detectar si la página se restauró desde el caché del navegador (BFCache / Botón Atrás) y forzar recarga limpia
	window.addEventListener('pageshow', function(e) {
		var isBackNav = e.persisted || 
			(window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0] && window.performance.getEntriesByType('navigation')[0].type === 'back_forward') ||
			(window.performance && window.performance.navigation && window.performance.navigation.type === 2);
		if (isBackNav) {
			window.location.reload();
		}
	});

	// Mover los nuevos modales al body para evitar conflictos de z-index con AdminLTE
	$('#modalGestionarEstados').appendTo("body");
	$('#modalEditarEstadoActividad').appendTo("body");
	$('#modalGestionarTipos').appendTo("body");
	$('#modalEditarTipoActividad').appendTo("body");
	$('#modalEditarActividad').appendTo("body");

	// Transición entre el modal de detalle y el modal de edición
	$(document).on('click', '#btnIrAEditarActividad', function() {
		$('#actividadModal').modal('hide');
		setTimeout(function() {
			if (window.ultimaActividad && typeof window.fillFields === 'function') {
				window.fillFields(window.ultimaActividad);
			}
			$('#modalEditarActividad').modal('show');
		}, 300);
	});

	// Al abrir directamente el modal de edición, rellenar sus campos si existe última actividad
	$('#modalEditarActividad').on('show.bs.modal', function() {
		if (window.ultimaActividad && typeof window.fillFields === 'function') {
			window.fillFields(window.ultimaActividad);
		}
	});

	// Clic en el botón Editar desde la tabla server-side de actividades
	$(document).on('click', '.btnEditarActividad', function(e) {
		e.preventDefault();
		var idActividad = $(this).attr('idActividad') || $(this).attr('data-id');
		if (!idActividad) return;

		$.ajax({
			url: 'ajax/actividades.ajax.php',
			type: 'POST',
			dataType: 'json',
			data: { idActividad: idActividad },
			success: function(respuesta) {
				if (respuesta) {
					var actObj = Array.isArray(respuesta) ? respuesta[0] : respuesta;
					window.ultimaActividad = actObj;
					if (typeof window.fillFields === 'function') {
						window.fillFields(actObj);
					}
					$('#modalEditarActividad').modal('show');
				}
			}
		});
	});

	// Corrección de z-index para modales anidados
	$(document).on('show.bs.modal', '.modal', function () {
		var zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function() {
			$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
		}, 0);
	});

	// Mantener el scrollbar del body cuando hay modales aún abiertos
	$(document).on('hidden.bs.modal', '.modal', function () {
		if ($('.modal:visible').length > 0) {
			$('body').addClass('modal-open');
		}
	});
});
</script>

<?php
$editarActividad = new ControladorActividades();
$editarActividad->ctrEditarActividad();

$eliminarActividad = new ControladorActividades();
$eliminarActividad->ctrEliminarActividad();
?>
