<!-- CSS para diseño responsive -->
<style>
  /* Cards para móvil */
  .cards-notificaciones {
    display: none;
  }

  .card-notificacion {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 10px;
    padding: 10px;
    padding-top: 10px;
    position: relative;
    border-left: 4px solid #3c8dbc;
  }

  .card-notificacion-checkbox {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .card-notificacion.no-leida {
    background-color: #f9f9f9;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
  }

  .card-notificacion-header {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    gap: 10px;
  }

  .card-notificacion-icon {
    font-size: 24px;
    flex-shrink: 0;
  }

  .card-notificacion-info {
    flex: 1;
    padding-right: 25px;
  }

  .card-notificacion-tipo {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #666;
    margin-bottom: 2px;
  }

  .card-notificacion-titulo {
    font-size: 14px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
  }

  .card-notificacion-mensaje {
    color: #666;
    font-size: 12px;
    line-height: 1.4;
    margin-bottom: 8px;
  }

  .card-notificacion-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #eee;
  }

  .card-notificacion-fecha {
    font-size: 11px;
    color: #999;
  }

  /* Responsive */
  @media (max-width: 767px) {
    .tabla-notificaciones {
      display: none !important;
    }

    .cards-notificaciones {
      display: block !important;
    }
  }

  @media (min-width: 768px) {
    .tabla-notificaciones {
      display: block !important;
    }

    .cards-notificaciones {
      display: none !important;
    }
  }
</style>

<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Notificaciones
      <small>Panel de Control</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Notificaciones</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <h3 class="box-title">Listado de Notificaciones</h3>

        <div class="pull-right contenedor-filtros">

          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">

            <!-- Filtro por Tipo -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Tipo:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control" id="filtroTipoNotificacion" style="width: 160px; border-left: 0;">
                  <option value="">Mostrar Todas</option>
                  <option value="Stock Agotado">Stock Agotado</option>
                  <option value="Actividad Próxima">Actividad Próxima</option>
                  <option value="Registro de Usuario">Registro de Usuario</option>
                  <?php 
                  $configNotif = ControladorConfiguracion::ctrObtenerConfiguracion(); 
                  ?>
                  <?php if (!isset($configNotif["notif_orden_agente_ia"]) || $configNotif["notif_orden_agente_ia"] == 1): ?>
                  <option value="Orden creada">Orden creada</option>
                  <?php endif; ?>
                  <?php if (!isset($configNotif["notif_transaccion_bold"]) || $configNotif["notif_transaccion_bold"] == 1): ?>
                  <option value="Transacción Bold">Transacción de Bold</option>
                  <?php endif; ?>
                  <?php if (!isset($configNotif["notif_solicitud_edicion"]) || $configNotif["notif_solicitud_edicion"] == 1): ?>
                  <option value="Edicion de pedido">Solicitud Edición de pedido</option>
                  <?php endif; ?>
                  <?php if (!isset($configNotif["notif_solicitud_eliminacion"]) || $configNotif["notif_solicitud_eliminacion"] == 1): ?>
                  <option value="Eliminacion de pedido">Solicitud Eliminación de pedido</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <!-- Botones de Acción -->
            <div class="btn-group">
              <button class="btn btn-success" id="btnMarcarTodasLeidas" title="Marcar todas como leídas">
                <i class="fa fa-check"></i> <span class="hidden-xs">Marcar todas leídas</span>
              </button>

              <?php if (puedeAccion('notificaciones', 'eliminar')): ?>
                <button class="btn btn-danger" id="btnBorrarSeleccionadas" style="display:none;"
                  title="Borrar seleccionadas">
                  <i class="fa fa-trash"></i> <span class="hidden-xs">Borrar</span>
                </button>
              <?php endif; ?>
            </div>

          </div>

        </div>

      </div>

      <div class="box-body">

        <?php

        $notificaciones = ControladorNotificaciones::ctrObtenerNotificaciones();

        if ($notificaciones && count($notificaciones) > 0) {

          echo '<div class="tabla-notificaciones">
                  <div class="table-responsive">
                    <table class="table table-hover tablas tablaNotificaciones">
                      <thead>
                        <tr>

                        <th style="width: 30px">
                            <input type="checkbox" id="checkTodos">
                          </th>

                          <th style="width: 50px"></th>
                          <th>Tipo</th>
                          <th>Mensaje</th>
                          <th>Fecha</th>
                          <th style="width: 100px">Verificar</th>
                        </tr>
                      </thead>
                      <tbody>';

          foreach ($notificaciones as $notif) {

            if ($notif["tipo"] == "stock_bajo") continue;

            // Determinar icono y color según tipo
            $icono = "fa-info-circle";
            $color = "text-blue";
            $tipoTexto = "Información";

            if ($notif["tipo"] == "stock_agotado") {
              $icono = "fa-times-circle";
              $color = "text-red";
              $tipoTexto = "Stock Agotado";
            } else if ($notif["tipo"] == "actividad_proxima") {
              $icono = "fa-calendar";
              $color = "text-blue";
              $tipoTexto = "Actividad Próxima";
            } else if ($notif["tipo"] == "registro_usuario") {
              $icono = "fa-user-plus";
              $color = "text-green";
              $tipoTexto = "Registro de Usuario";
            } else if ($notif["tipo"] == "gasto_proximo") {
              $icono = "fa-money";
              $color = "text-orange";
              $tipoTexto = "Gasto Próximo";
            } else if ($notif["tipo"] == "orden_agente_ia") {
              $icono = "fa-magic";
              $color = "text-green";
              $tipoTexto = "Orden Agente IA";
            } else if (strpos($notif["tipo"], "Edicion de pedido") !== false) {
              $icono = "fa-pencil-square-o";
              $color = "text-orange";
              $tipoTexto = $notif["tipo"];
            } else if (strpos($notif["tipo"], "Eliminacion de pedido") !== false) {
              $icono = "fa-trash";
              $color = "text-red";
              $tipoTexto = $notif["tipo"];
            } else if ($notif["tipo"] == "orden_creada") {
              $icono = "fa-android";
              $color = "text-green";
              $tipoTexto = "Orden creada";
            } else if ($notif["tipo"] == "Transaccion de BOLD") {
              $icono = "fa-credit-card";
              $color = "text-purple";
              $tipoTexto = "Transacción Bold";
            }

            // Determinar estilo de fila según si está leída
            $estiloFila = $notif["leida"] == 0 ? 'style="background-color: #f9f9f9; font-weight: bold;"' : '';

            echo '<tr ' . $estiloFila . '>

                    <td>
                      <input type="checkbox" class="checkNotificacion" value="' . $notif["id"] . '">
                    </td>

                    <td><i class="fa ' . $icono . ' ' . $color . ' fa-2x"></i></td>
                    <td>' . $tipoTexto . '</td>
                    <td>' . $notif["mensaje"] . '</td>
                    <td>' . date("d/m/Y H:i", strtotime($notif["fecha"])) . '</td>
                    <td>';

            if ($notif["leida"] == 0) {
              echo '<button class="btn btn-xs btn-primary btnMarcarLeida" data-id="' . $notif["id"] . '">
                      <i class="fa fa-check"></i>
                    </button>';
            } else {
              echo '<span class="text-muted" style="font-size: 12px;"><i class="fa fa-check-circle"></i> Leída</span>';
            }

            echo '</td>
              </tr>';
          }

          echo '</tbody></table></div></div>';

          // CARDS PARA MÓVIL
          echo '<div class="cards-notificaciones">';

          // Checkbox para seleccionar todas en móvil
          echo '<div class="card-notificacion" style="border-left: 4px solid #d2d6de; display: flex; align-items: center; padding: 10px;">
                  <input type="checkbox" id="checkTodosMovil" style="width: 18px; height: 18px; cursor: pointer; margin-right: 10px;">
                  <label for="checkTodosMovil" style="margin: 0; cursor: pointer; font-weight: normal; color: #666;">Seleccionar todas</label>
                </div>';

          foreach ($notificaciones as $notif) {

            if ($notif["tipo"] == "stock_bajo") continue;

            // Determinar icono y color según tipo
            $icono = "fa-info-circle";
            $color = "text-blue";
            $tipoTexto = "Información";

            if ($notif["tipo"] == "stock_agotado") {
              $icono = "fa-times-circle";
              $color = "text-red";
              $tipoTexto = "Stock Agotado";
            } else if ($notif["tipo"] == "actividad_proxima") {
              $icono = "fa-calendar";
              $color = "text-blue";
              $tipoTexto = "Actividad Próxima";
            } else if ($notif["tipo"] == "registro_usuario") {
              $icono = "fa-user-plus";
              $color = "text-green";
              $tipoTexto = "Registro de Usuario";
            } else if ($notif["tipo"] == "gasto_proximo") {
              $icono = "fa-money";
              $color = "text-orange";
              $tipoTexto = "Gasto Próximo";
            } else if ($notif["tipo"] == "orden_agente_ia") {
              $icono = "fa-magic";
              $color = "text-green";
              $tipoTexto = "Orden Agente IA";
            } else if (strpos($notif["tipo"], "Edicion de pedido") !== false) {
              $icono = "fa-pencil-square-o";
              $color = "text-orange";
              $tipoTexto = $notif["tipo"];
            } else if (strpos($notif["tipo"], "Eliminacion de pedido") !== false) {
              $icono = "fa-trash";
              $color = "text-red";
              $tipoTexto = $notif["tipo"];
            } else if ($notif["tipo"] == "orden_creada") {
              $icono = "fa-android";
              $color = "text-green";
              $tipoTexto = "Orden creada";
            } else if ($notif["tipo"] == "Transaccion de BOLD") {
              $icono = "fa-credit-card";
              $color = "text-purple";
              $tipoTexto = "Transacción Bold";
            }

            $claseNoLeida = $notif["leida"] == 0 ? ' no-leida' : '';

            echo '<div class="card-notificacion' . $claseNoLeida . '">

                    <input type="checkbox" class="checkNotificacion card-notificacion-checkbox" value="' . $notif["id"] . '">

                    <div class="card-notificacion-header">
                      <div class="card-notificacion-icon">
                        <i class="fa ' . $icono . ' ' . $color . '"></i>
                      </div>
                      <div class="card-notificacion-info">
                        <div class="card-notificacion-titulo">' . $tipoTexto . '</div>
                      </div>
                    </div>

                    <div class="card-notificacion-mensaje">
                      ' . $notif["mensaje"] . '
                    </div>

                    <div class="card-notificacion-footer">
                      <span class="card-notificacion-fecha">
                        <i class="fa fa-clock-o"></i> ' . date("d/m/Y H:i", strtotime($notif["fecha"])) . '
                      </span>';

            if ($notif["leida"] == 0) {
              echo '<button class="btn btn-xs btn-primary btnMarcarLeida" data-id="' . $notif["id"] . '">
                      <i class="fa fa-check"></i>
                    </button>';
            } else {
              echo '<span class="text-muted" style="font-size: 11px;"><i class="fa fa-check-circle"></i> Leída</span>';
            }

            echo '</div>
                  </div>';
          }

          echo '</div>';

        } else {

          echo '<div class="callout callout-success">
                  <h4><i class="icon fa fa-check"></i> No hay notificaciones!</h4>
                  <p>No tienes notificaciones en este momento.</p>
                </div>';

        }

        ?>

      </div>

    </div>

  </section>

</div>

<script>
  $(document).ready(function () {

    // Marcar una notificación como leída
    $(document).on("click", ".btnMarcarLeida", function () {

      var idNotificacion = $(this).attr("data-id");

      var datos = new FormData();
      datos.append("idNotificacion", idNotificacion);

      $.ajax({
        url: "ajax/notificaciones.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {
          if (respuesta == "ok") {
            window.location.reload();
          }
        }
      });

    });

    // Marcar todas como leídas
    $("#btnMarcarTodasLeidas").click(function () {

      var datos = new FormData();
      datos.append("marcarTodasLeidas", 1);

      $.ajax({
        url: "ajax/notificaciones.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function (respuesta) {
          if (respuesta == "ok") {
            window.location.reload();
          }
        }
      });

    });


    // Seleccionar/deseleccionar todas las notificaciones
    $("#checkTodos, #checkTodosMovil").change(function () {
      var isChecked = $(this).prop('checked');
      $(".checkNotificacion").prop('checked', isChecked);
      $("#checkTodos, #checkTodosMovil").prop('checked', isChecked); // Sincronizar ambos checkboxes
      mostrarBotonBorrar();
    });

    // Mostrar u ocultar el botón "Borrar seleccionadas"
    $(document).on("change", ".checkNotificacion", function () {
      mostrarBotonBorrar();
    });

    function mostrarBotonBorrar() {
      var checkedCount = $(".checkNotificacion:checked").length;
      if (checkedCount > 0) {
        $("#btnBorrarSeleccionadas").show();
      } else {
        $("#btnBorrarSeleccionadas").hide();
      }
    }

    // Borrar notificaciones seleccionadas
    $("#btnBorrarSeleccionadas").click(function () {

      var idsSeleccionados = [];
      $(".checkNotificacion:checked").each(function () {
        idsSeleccionados.push($(this).val());
      });

      if (idsSeleccionados.length == 0) {
        return;
      }

      swal({
        title: '¿Está seguro de eliminar las notificaciones seleccionadas?',
        text: "Se eliminarán " + idsSeleccionados.length + " notificación(es)",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, eliminar'
      }).then(function (result) {
        if (result.value) {

          var datos = new FormData();
          datos.append("idsEliminarNotificaciones", JSON.stringify(idsSeleccionados));

          $.ajax({
            url: "ajax/notificaciones.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            success: function (respuesta) {
              if (respuesta == "ok") {
                swal({
                  type: "success",
                  title: "Notificaciones eliminadas correctamente",
                  showConfirmButton: true,
                  confirmButtonText: "Cerrar"
                }).then(function (result) {
                  if (result.value) {
                    window.location = "notificaciones";
                  }
                });
              }
            }
          });
        }
      });

    });

    // Filtrar notificaciones
    $("#filtroTipoNotificacion").change(function () {

      var tipo = $(this).val();

      // Filtrar Tabla (Desktop) usando DataTables API si existe
      if ($.fn.DataTable.isDataTable('.tablaNotificaciones')) {
        $('.tablaNotificaciones').DataTable().column(2).search(tipo).draw();
      } else {
        // Fallback manual si no hay DataTable
        $(".tabla-notificaciones table tbody tr").hide();
        $(".tabla-notificaciones table tbody tr").each(function () {
          var textoTipo = $(this).find("td:eq(2)").text(); // Columna indice 2 es Tipo
          if (textoTipo.indexOf(tipo) != -1) {
            $(this).show();
          }
        });
      }

      // Filtrar cards (Mobile)
      $(".cards-notificaciones .card-notificacion").hide();
      $(".cards-notificaciones .card-notificacion").each(function () {
        var textoTipo = $(this).find(".card-notificacion-tipo").text();
        if (textoTipo.indexOf(tipo) != -1) {
          $(this).show();
        }
      });

    });


  });
</script>