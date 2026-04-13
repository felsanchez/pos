<?php

if ($_SESSION["perfil"] == "Especial") {
    echo '<script>
    window.location = "inicio";
  </script>';
    return;
}

?>

<div class="content-wrapper">

    <section class="content-header">
        <h1>
            Administrar Notas Crédito (Factura Electrónica)
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Administrar Notas Crédito</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <?php if (puedeAccion('factura_electronica', 'crear')): ?>
                    <a href="crear-nota-credito">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Nota Crédito
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

                /* 1. LÓGICA DESKTOP-FIRST: Ocultar botón de expansión por defecto en Notas Crédito */
                .tablaNotasCredito td.dtr-control:before,
                .tablaNotasCredito th.dtr-control:before {
                    display: none !important;
                    content: "" !important;
                }

                .tablaNotasCredito td.dtr-control,
                .tablaNotasCredito th.dtr-control {
                    padding-left: 8px !important;
                    cursor: default !important;
                }

                /* 2. ACTIVACIÓN EXCLUSIVA PARA MÓVIL (Menos de 767px) */
                @media (max-width: 767px) {
                    .tablaNotasCredito td.dtr-control {
                        position: relative !important;
                        padding-left: 30px !important;
                        cursor: pointer !important;
                    }

                    .tablaNotasCredito td.dtr-control:before {
                        top: 50% !important;
                        left: 5px !important;
                        height: 18px !important;
                        width: 18px !important;
                        margin-top: -9px !important;
                        display: block !important;
                        position: absolute !important;
                        color: white !important;
                        border: 2px solid white !important;
                        border-radius: 14px !important;
                        box-shadow: 0 0 3px #444 !important;
                        box-sizing: content-box !important;
                        text-align: center !important;
                        text-indent: 0 !important;
                        font-family: 'Courier New', Courier, monospace !important;
                        font-weight: bold !important;
                        line-height: 18px !important;
                        content: '+' !important;
                        background-color: #3c8dbc !important; /* Azul al estar contraído (+) */
                    }

                    .tablaNotasCredito tr.parent td.dtr-control:before {
                        content: '-' !important;
                        background-color: #dd4b39 !important; /* Rojo al estar expandido (-) */
                    }
                }

                /* Hide table while loading to prevent layout jump */
                .tablaNotasCredito:not(.datatable-ready) {
                    visibility: hidden;
                    height: 0;
                    overflow: hidden;
                    opacity: 0;
                }

                .tablaNotasCredito.datatable-ready {
                    transition: opacity 0.5s ease;
                    opacity: 1;
                }

                /* Botones de acción compactos en móvil */
                @media (max-width: 767px) {
                    .tablaNotasCredito td:last-child .btn {
                        padding: 1px 5px !important;
                        font-size: 12px !important;
                        line-height: 1.5 !important;
                    }

                    .tablaNotasCredito td:last-child .btn-group {
                        display: flex;
                        gap: 2px;
                    }
                }
            </style>

            <div class="box-body">

                <div id="loader-table" class="loader-container">
                    <i class="fa fa-refresh fa-spin"></i>
                    <span>Cargando Notas Crédito...</span>
                </div>

                <table id="tablaListadoNotasCredito" class="table table-bordered table-striped dt-responsive tablaNotasCredito display nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>Código Nota</th>
                            <th>Factura Original</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado DIAN</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php

                        $notas = ControladorFactus::ctrMostrarNotasCredito(null, null);

                        foreach ($notas as $key => $value) {

                            $cliente = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);

                            echo '<tr>
                                    <td' . ($value["estado_dian"] == "borrador" ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . e($value["numero_nota_credito"]) . '</td>
                                    <td>' . e($value["numero_factura_original"]) . '</td>
                                    <td>' . e(($cliente["nombre"] ?? "N/A")) . '</td>
                                    <td>$ ' . e(number_format((float) ($value["monto_total"] ?? 0), 2)) . '</td>
                                    <td>' . e($value["fecha_creacion"]) . '</td>';

                            if ($value["estado_dian"] == "aceptada" || $value["estado_dian"] == "enviada") {
                                echo '<td><button class="btn btn-success btn-xs">Exitosa</button></td>';
                            } else if ($value["estado_dian"] == "borrador") {
                                echo '<td><button class="btn btn-warning btn-xs">Borrador</button></td>';
                            } else if ($value["estado_dian"] == "rechazada") {
                                echo '<td><button class="btn btn-danger btn-xs">Rechazada</button></td>';
                            } else {
                                echo '<td><button class="btn btn-danger btn-xs">Pendiente</button></td>';
                            }

                            echo '<td>
                                        <div class="btn-group">
                                            <a href="index.php?ruta=ver-nota-credito&idNota=' . e($value["id"]) . '" class="btn btn-info"><i class="fa fa-eye"></i></a>';

                            if ($value["estado_dian"] == "borrador") {
                                // Botón Firmar
                                if (puedeAccion('factura_electronica', 'editar')) {
                                    echo '<button class="btn btnFirmarNotaCredito" style="background-color: black; color: white;" idNota="' . e($value["id"]) . '" title="Firmar y Enviar a DIAN"><i class="fa fa-paper-plane"></i></button>';
                                }
                                // Botón Eliminar
                                if (puedeAccion('factura_electronica', 'eliminar')) {
                                    echo '<button class="btn btn-danger btnEliminarNotaCredito" idNota="' . e($value["id"]) . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                                }
                            } else {
                                // Botón XML si tiene URL
                                if (!empty($value["xml_dian_nc"])) {
                                    echo '<a href="' . e($value["xml_dian_nc"]) . '" target="_blank" class="btn btn-primary" title="Ver XML Factus"><i class="fa fa-file-code-o"></i></a>';
                                }

                                // Botón para ver en la DIAN
                                if (!empty($value["cufe_nc"])) {
                                    echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . e($value["cufe_nc"]) . '" target="_blank" class="btn btn-success" title="Ver en la DIAN"><i class="fa fa-external-link"></i></a>';
                                }

                                // Botón para enviar por correo
                                if ($value["estado_dian"] == "aceptada" || $value["estado_dian"] == "enviada") {
                                    echo '<button class="btn btn-primary btnEnviarEmailNC" idNota="' . e($value["id"]) . '" nombreCliente="' . e(($cliente["nombre"] ?? "N/A")) . '" emailCliente="' . e(($cliente["email"] ?? "")) . '" title="Enviar por Correo"><i class="fa fa-envelope"></i></button>';
                                }
                            }

                            echo '</div>
                                    </td>
                                </tr>';
                        }

                        ?>

                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script src="vistas/js/notas-credito.js?v=<?php echo time(); ?>"></script>

<!-- DataTables Personalizado para Notas Crédito -->
<script>
$(document).ready(function () {
  setTimeout(function () {
    if ($("#tablaListadoNotasCredito").length > 0) {
      if ($.fn.DataTable.isDataTable('#tablaListadoNotasCredito')) {
        $('#tablaListadoNotasCredito').DataTable().destroy();
      }

      $("#tablaListadoNotasCredito").DataTable({
        "autoWidth": false,
        "initComplete": function(settings, json) {
           $(this.api().table().node()).addClass('datatable-ready');
           $("#loader-table").fadeOut(200);
        },
        "order": [[4, "desc"]], // Fecha (ahora índice 4)
        "responsive": {
          "details": {
            "type": "column",
            "target": 0, // En la columna de Código Nota (ahora índice 0)
            "renderer": function (api, rowIdx, columns) {
              if ($(window).width() >= 768) return false;

              // Mapeo por índices directos (ajustados tras eliminar la columna #)
              var codigo = columns[0].data || '';
              var facturaOrig = columns[1].data || '';
              var cliente = columns[2].data || '';
              var total = columns[3].data || '';
              var fecha = columns[4].data || '';
              var estadoDian = columns[5].data || '';
              var acciones = columns[6].data || '';

              var finalHtml = '';

              // SECCION 1: Información de Nota
              finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Información de Nota</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold">Factura Original: </span><span class="pull-right">' + facturaOrig + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold">Total: </span><span class="pull-right">' + total + '</span></div>';

              // SECCION 2: Estado y Fecha
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0;">Estado y Fecha</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold">Estado DIAN: </span><span class="pull-right">' + estadoDian + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
              finalHtml += '<span class="text-bold">Fecha: </span><span class="pull-right">' + fecha + '</span></div>';

              return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0;">').append(finalHtml) : false;
            }
          }
        },
        "columnDefs": [
          { "targets": 0, "className": 'dtr-control', "responsivePriority": 1 },
          { "targets": [2, 6], "responsivePriority": 1 },
          { "targets": [1, 3, 4, 5], "responsivePriority": 2 }
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
MODAL ENVIAR EMAIL NC
======================================-->
<div id="modalEnviarEmailNC" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" method="post">
                <?php CSRF::insertToken(); ?>
                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Enviar Nota Crédito por Correo</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <!-- ENTRADA PARA EL NOMBRE DEL CLIENTE -->
                        <div class="form-group">
                            <label for="clienteEmailNC">Cliente:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" id="nombreClienteEmailNC" readonly>
                            </div>
                        </div>

                        <!-- ENTRADA PARA EL CORREO ELECTRONICO -->
                        <div class="form-group">
                            <label for="emailDestinoNC">Correo Electrónico:</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" class="form-control" id="emailDestinoNC"
                                    placeholder="Ingresar correo electrónico" required>
                            </div>
                        </div>

                        <input type="hidden" id="idNotaEmailNC">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                    <button type="button" class="btn btn-primary btnEnviarCorreoConfirmadoNC">Enviar Correo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$eliminarNota = new ControladorFactus();
$eliminarNota->ctrEliminarNotaCredito();
?>