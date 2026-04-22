<div class="content-wrapper">

    <section class="content-header">

        <h1>
            Administrar Documentos Soporte
        </h1>

        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Administrar Documentos Soporte</li>
        </ol>

    </section>

    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <?php if (puedeAccion('documento_soporte', 'crear')): ?>
                    <a href="crear-documento-soporte">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Documento Soporte
                        </button>
                    </a>
                <?php endif; ?>

            </div>

            <style>
                .loader-container {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 60px;
                    background: #fff;
                    margin-bottom: 20px;
                    transition: opacity 0.3s ease;
                }

                .loader-container i {
                    font-size: 45px;
                    color: #3c8dbc;
                    margin-bottom: 15px;
                }

                .loader-container span {
                    font-size: 16px;
                    color: #666;
                    font-weight: 500;
                }



                /* Botones de acción compactos en móvil */
                @media (max-width: 767px) {
                    .tablaDocumentosSoporte td:last-child .btn {
                        padding: 1px 5px !important;
                        font-size: 12px !important;
                        line-height: 1.5 !important;
                    }

                    .tablaDocumentosSoporte td:last-child .btn-group {
                        display: flex;
                        gap: 2px;
                    }

                    /* Forzar que las acciones tengan espacio */
                    .tablaDocumentosSoporte td:last-child {
                        width: 1% !important;
                        white-space: nowrap !important;
                        text-align: right !important;
                    }
                }

                /* Hide table while loading to prevent layout jump */
                .tablaDocumentosSoporte:not(.datatable-ready) {
                    visibility: hidden;
                    height: 0;
                    overflow: hidden;
                    opacity: 0;
                }

                .tablaDocumentosSoporte.datatable-ready {
                    transition: opacity 0.5s ease;
                    opacity: 1;
                }
            </style>

            <div class="box-body">

                <div id="loader-table-ds" class="loader-container">
                    <i class="fa fa-refresh fa-spin"></i>
                    <span>Cargando Documentos Soporte...</span>
                </div>

                <table id="tablaListadoDocumentoSoporte" class="table table-bordered table-striped dt-responsive tablaDocumentosSoporte display nowrap" width="100%">

                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado DIAN</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Obtener el siguiente consecutivo base y prefijo
                        $proximoBase = ModeloFactus::mdlObtenerSiguienteConsecutivoDS();
                        $rangoActivoDS = ModeloFactus::mdlObtenerRangoDS();
                        $prefijoDS = $rangoActivoDS ? $rangoActivoDS["prefijo"] : "";

                        $documentos = ControladorFactus::ctrMostrarDocumentosSoporte(null, null);

                        // Contar cuántos borradores hay para asignarles un número secuencial en la vista
                        $totalBorradores = 0;
                        if ($documentos) {
                            foreach ($documentos as $d) {
                                if (empty($d["numero_ds"]))
                                    $totalBorradores++;
                            }
                        }

                        $borradorCount = 0;

                        if ($documentos) {
                            foreach ($documentos as $key => $value) {
                                $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

                                echo '<tr>
                                    <td' . (empty($value["numero_ds"]) ? ' class="text-yellow" style="font-weight:bold"' : '') . '>';

                                if (!empty($value["numero_ds"])) {

                                    echo e($value["numero_ds"]);

                                } else {
                                    // Es un borrador. Calculamos su número sugerido.
                                    // Si hay 3 borradores, el más antiguo (abajo en la tabla ya que ordenamos DESC) es el $proximoBase,
                                    // el siguiente es $proximoBase + 1, etc.
                                    // Pero como la tabla ordena DESC, el primero que vemos es el más reciente.
                                    $numSugerido = $proximoBase + ($totalBorradores - 1 - $borradorCount);
                                    echo e($prefijoDS) . e($numSugerido);
                                    $borradorCount++;
                                }
                                echo '</td>
                                    <td>' . e($proveedor["nombre"]) . '</td>
                                    <td>$ ' . e(number_format($value["monto_total"], 0)) . '</td>
                                    <td>' . e($value["fecha_emision"]) . '</td>
                                    <td>';
                                if ($value["estado_dian"] == "aceptada" || $value["estado_dian"] == "enviada") {
                                    echo '<button class="btn btn-success btn-xs">Exitosa</button>';
                                } else if ($value["estado_dian"] == "borrador") {
                                    echo '<button class="btn btn-warning btn-xs">Borrador</button>';
                                } else if ($value["estado_dian"] == "rechazada") {
                                    echo '<button class="btn btn-danger btn-xs">Rechazada</button>';
                                } else {
                                    echo '<button class="btn btn-danger btn-xs">Pendiente</button>';
                                }
                                echo '</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="index.php?ruta=ver-documento-soporte&idDS=' . e($value["id"]) . '" class="btn btn-info" title="Ver Detalle"><i class="fa fa-eye"></i></a>';

                                if ($value["estado_dian"] == "borrador") {
                                    if (puedeAccion('documento_soporte', 'editar')) {
                                        echo '<button class="btn btnFirmarDS" style="background-color: black; color: white;" idDS="' . e($value["id"]) . '" title="Firmar y Enviar a Factus"><i class="fa fa-paper-plane"></i></button>';
                                    }
                                    if (puedeAccion('documento_soporte', 'eliminar')) {
                                        echo '<button class="btn btn-danger btnEliminarDS" idDS="' . e($value["id"]) . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                                    }
                                } else {
                                    echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . e($value["cuds"]) . '" target="_blank" class="btn btn-success" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>';

                                    // Botón para enviar por correo
                                    if ($value["estado_dian"] == "aceptada" || $value["estado_dian"] == "enviada") {
                                        echo '<button class="btn btn-primary btnEnviarEmailDS" idDS="' . e($value["id"]) . '" nombreProveedor="' . e(($proveedor["nombre"] ?? "N/A")) . '" emailProveedor="' . e(($proveedor["correo"] ?? "")) . '" title="Enviar por Correo"><i class="fa fa-envelope"></i></button>';
                                    }

                                    if (ModeloFactus::mdlTieneNotaAjusteDS($value["id"])) {
                                        echo '<button class="btn btn-warning btnVerNotasAjusteDS" idDS="' . e($value["id"]) . '" data-toggle="modal" data-target="#modalNotasAjusteDS" title="Ver Notas de Ajuste">
                                                <i class="fa fa-list"></i>
                                              </button>';
                                    }
                                }

                                echo '</div>
                                    </td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>

                </table>

            </div>

        </div>

    </section><!-- DataTables Personalizado para Documento Soporte -->
<script>
$(document).ready(function () {
  setTimeout(function () {
    if ($("#tablaListadoDocumentoSoporte").length > 0) {
      if ($.fn.DataTable.isDataTable('#tablaListadoDocumentoSoporte')) {
        $('#tablaListadoDocumentoSoporte').DataTable().destroy();
      }

      $("#tablaListadoDocumentoSoporte").DataTable({
        "autoWidth": false,
        "initComplete": function(settings, json) {
           $(this.api().table().node()).addClass('datatable-ready');
           $("#loader-table-ds").fadeOut(200);
        },
        "order": [[3, "desc"]], // Fecha (nuevo índice 3)
        "responsive": {
          "details": {
            "type": "inline",
            "renderer": function (api, rowIdx, columns) {
              var finalHtml = '';
              var hasHidden = false;

              $.each(columns, function (i, col) {
                if (!col.hidden) return;
                hasHidden = true;

                var label = col.title || ('Columna ' + col.columnIndex);
                
                finalHtml += '<div style="padding:8px 0; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:4px;">';
                finalHtml += '<span class="text-bold" style="color:#555;">' + label + ':</span>';
                finalHtml += '<span style="color:#333;">' + col.data + '</span>';
                finalHtml += '</div>';
              });

              if (!hasHidden) return false;
              return $('<div style="padding:8px 12px; background:#fcfcfc;">').append(finalHtml);
            }
          }
        },
        "columnDefs": [
            { "targets": 0, "responsivePriority": 1 }, // Código
            { "targets": 5, "responsivePriority": 2, "orderable": false }, // Acciones
            { "targets": 1, "responsivePriority": 3 }, // Proveedor
            { "targets": 2, "responsivePriority": 4 }, // Total (nuevo índice 2)
            { "targets": 3, "responsivePriority": 5 }, // Fecha (nuevo índice 3)
            { "targets": 4, "responsivePriority": 6 }  // Estado Dian (nuevo índice 4)
        ],
        "language": {
          "sProcessing": "Procesando...",
          "sLengthMenu": "Mostrar _MENU_ registros",
          "sZeroRecords": "No se encontraron resultados",
          "sEmptyTable": "Ningún dato disponible en esta tabla",
          "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
          "sSearch": "Buscar:",
          "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
        }
      });
    }
  }, 200);
});
</script>

<!--=====================================
MODAL ENVIAR EMAIL DS
======================================-->
<div id="modalEnviarEmailDS" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <?php CSRF::insertToken(); ?>
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Enviar Documento Soporte por Correo</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <!-- ENTRADA PARA EL NOMBRE DEL PROVEEDOR -->
                        <div class="form-group">
                            <label for="nombreProveedorEmailDS">Proveedor:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" id="nombreProveedorEmailDS" readonly>
                            </div>
                        </div>

                        <!-- ENTRADA PARA EL CORREO ELECTRONICO -->
                        <div class="form-group">
                            <label for="emailDestinoDS">Correo Electrónico:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" class="form-control" id="emailDestinoDS"
                                    placeholder="Ingresar correo electrónico" required>
                            </div>
                        </div>

                        <input type="hidden" id="idDSEmailDS">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-primary btnEnviarCorreoConfirmadoDS">Enviar Correo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=====================================
MODAL VER NOTAS DE AJUSTE DS
======================================-->
<div id="modalNotasAjusteDS" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- CABEZA DEL MODAL -->
            <div class="modal-header" style="background:#f39c12; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-list"></i> Notas de Ajuste Asociadas</h4>
            </div>

            <!-- CUERPO DEL MODAL -->
            <div class="modal-body">
                <div class="box-body">

                    <!-- TABLA NOTAS DE AJUSTE -->
                    <table class="table table-bordered table-striped dt-responsive" width="100%">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyNotasAjusteDS">
                            <!-- Filas inyectadas por AJAX -->
                        </tbody>
                    </table>

                </div>
            </div>

            <!-- PIE DEL MODAL -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>