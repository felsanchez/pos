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
            Administrar Notas de Ajuste DS
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Administrar Notas de Ajuste DS</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <?php if (puedeAccion('documento_soporte', 'crear')): ?>
                    <a href="crear-nota-ajuste-ds">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Nota de Ajuste
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

                /* 1. LÓGICA DESKTOP-FIRST: Ocultar botón de expansión por defecto en Notas Ajuste DS */
                .tablaNotasAjusteDS td.dtr-control:before,
                .tablaNotasAjusteDS th.dtr-control:before {
                    display: none !important;
                    content: "" !important;
                }

                .tablaNotasAjusteDS td.dtr-control,
                .tablaNotasAjusteDS th.dtr-control {
                    padding-left: 8px !important;
                    cursor: default !important;
                }

                /* 2. ACTIVACIÓN EXCLUSIVA PARA MÓVIL (Menos de 767px) */
                @media (max-width: 767px) {
                    .tablaNotasAjusteDS td.dtr-control {
                        position: relative !important;
                        padding-left: 30px !important;
                        cursor: pointer !important;
                    }

                    .tablaNotasAjusteDS td.dtr-control:before {
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

                    .tablaNotasAjusteDS tr.parent td.dtr-control:before {
                        content: '-' !important;
                        background-color: #dd4b39 !important; /* Rojo al estar expandido (-) */
                    }
                }

                /* Botones de acción compactos en móvil */
                @media (max-width: 767px) {
                    .tablaNotasAjusteDS td:last-child .btn {
                        padding: 1px 5px !important;
                        font-size: 12px !important;
                        line-height: 1.5 !important;
                    }

                    .tablaNotasAjusteDS td:last-child .btn-group {
                        display: flex;
                        gap: 2px;
                    }

                    /* Forzar que las acciones tengan espacio */
                    .tablaNotasAjusteDS td:last-child {
                        width: 1% !important;
                        white-space: nowrap !important;
                        text-align: right !important;
                    }
                }

                /* Hide table while loading to prevent layout jump */
                .tablaNotasAjusteDS:not(.datatable-ready) {
                    visibility: hidden;
                    height: 0;
                    overflow: hidden;
                    opacity: 0;
                }

                .tablaNotasAjusteDS.datatable-ready {
                    transition: opacity 0.5s ease;
                    opacity: 1;
                }
            </style>

            <div class="box-body">

                <div id="loader-table-na" class="loader-container">
                    <i class="fa fa-refresh fa-spin"></i>
                    <span>Cargando Notas de Ajuste...</span>
                </div>

                <table id="tablaListadoNotasAjusteDS" class="table table-bordered table-striped dt-responsive tablaNotasAjusteDS display nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>Código Nota</th>
                            <th>Doc. Original</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado DIAN</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        // Obtener el siguiente consecutivo base (SIN incluir borradores actuales) y prefijo
                        $proximoBase = ModeloFactus::mdlObtenerSiguienteConsecutivoNotaAjusteDS(false);
                        $rangoAjuste = ModeloFactus::mdlObtenerRangoAjusteDS();
                        $prefijoAjuste = $rangoAjuste ? $rangoAjuste["prefijo"] : "NA";

                        $notas = ControladorFactus::ctrMostrarNotasAjusteDS(null, null);

                        // Contar cuántos borradores hay para asignarles un número secuencial en la vista
                        $totalBorradores = 0;
                        if ($notas) {
                            foreach ($notas as $n) {
                                if ($n["estado_dian"] == "borrador")
                                    $totalBorradores++;
                            }
                        }

                        $borradorCount = 0;

                        foreach ($notas as $key => $value) {

                            $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

                            $numeroMostrar = $value["numero_nota_ajuste"];

                            if ($value["estado_dian"] == "borrador") {
                                // Es un borrador. Calculamos su número sugerido.
                                // Si hay 3 borradores, el más antiguo (abajo en la tabla) es el $proximoBase
                                $numSugerido = $proximoBase + ($totalBorradores - 1 - $borradorCount);
                                $numeroMostrar = $prefijoAjuste . $numSugerido;
                                $borradorCount++;
                            }

                            echo '<tr>
                                    <td' . ($value["estado_dian"] == "borrador" ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . e($numeroMostrar) . '</td>
                                    <td>' . e($value["numero_ds_original"]) . '</td>
                                    <td>' . e(($proveedor["nombre"] ?? "N/A")) . '</td>
                                    <td>$ ' . e(number_format((float) ($value["monto_total"] ?? 0), 2)) . '</td>
                                    <td>' . e($value["fecha_envio_dian"]) . '</td>';

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
                                            <a href="index.php?ruta=ver-nota-ajuste-ds&idNota=' . e($value["id"]) . '" class="btn btn-info"><i class="fa fa-eye"></i></a>';

                            if ($value["estado_dian"] == "borrador") {
                                // Botón Firmar
                                if (puedeAccion('documento_soporte', 'editar')) {
                                    echo '<button class="btn btnFirmarNotaAjusteDS" style="background-color: black; color: white;" idNota="' . e($value["id"]) . '" title="Firmar y Enviar a DIAN"><i class="fa fa-paper-plane"></i></button>';
                                }
                                // Botón Eliminar
                                if (puedeAccion('documento_soporte', 'eliminar')) {
                                    echo '<button class="btn btn-danger btnEliminarNotaAjusteDS" idNota="' . e($value["id"]) . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                                }
                            } else {
                                // Botón PDF si tiene URL
                                if (!empty($value["pdf_dian"])) {
                                    echo '<a href="' . e($value["pdf_dian"]) . '" target="_blank" class="btn btn-danger" title="Ver PDF Factus"><i class="fa fa-file-pdf-o"></i></a>';
                                }

                                // Botón XML si tiene URL
                                if (!empty($value["xml_dian"])) {
                                    echo '<a href="' . e($value["xml_dian"]) . '" target="_blank" class="btn btn-primary" title="Ver XML Factus"><i class="fa fa-file-code-o"></i></a>';
                                }

                                // Botón para ver en la DIAN
                                echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . e($value["cuds_ajuste"]) . '" target="_blank" class="btn btn-success" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>';

                                // Botón para enviar por correo (Solo si está aceptada o enviada)
                                if ($value["estado_dian"] == "aceptada" || $value["estado_dian"] == "enviada") {
                                    echo '<button class="btn btn-primary btnEnviarEmailNA" idNA="' . e($value["id"]) . '" nombreProveedor="' . e(($proveedor["nombre"] ?? "N/A")) . '" emailProveedor="' . e(($proveedor["correo"] ?? '')) . '" title="Enviar por Correo"><i class="fa fa-envelope"></i></button>';
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

<!--=====================================
MODAL ENVIAR EMAIL NOTA AJUSTE
======================================-->
<div id="modalEnviarEmailNA" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#3c8dbc; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Enviar Nota de Ajuste por Correo</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div class="form-group">
                        <label for="nombreProveedorEmailNA">Proveedor:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" id="nombreProveedorEmailNA" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Correo del Proveedor:</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <input type="email" class="form-control" id="emailDestinoNA"
                                placeholder="Ingrese el correo">
                            <input type="hidden" id="idNA_Email">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
                <button type="button" class="btn btn-primary btnEnviarCorreoConfirmadoNA">Enviar Correo</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables Personalizado para Notas de Ajuste DS -->
<script>
$(document).ready(function () {
  setTimeout(function () {
    if ($("#tablaListadoNotasAjusteDS").length > 0) {
      if ($.fn.DataTable.isDataTable('#tablaListadoNotasAjusteDS')) {
        $('#tablaListadoNotasAjusteDS').DataTable().destroy();
      }

      $("#tablaListadoNotasAjusteDS").DataTable({
        "autoWidth": false,
        "initComplete": function(settings, json) {
           $(this.api().table().node()).addClass('datatable-ready');
           $("#loader-table-na").fadeOut(200);
        },
        "order": [[4, "desc"]], // Fecha
        "responsive": {
          "details": {
            "type": "column",
            "target": 0, // Código Nota
            "renderer": function (api, rowIdx, columns) {
              if ($(window).width() >= 768) return false;

              // Mapeo por índices directos (ajustados tras eliminar #)
              var codigo = columns[0].data || '';
              var docOrig = columns[1].data || '';
              var proveedor = columns[2].data || '';
              var total = columns[3].data || '';
              var fecha = columns[4].data || '';
              var estadoDian = columns[5].data || '';
              var acciones = columns[6].data || '';

              var finalHtml = '';

              // SECCION 1: Información de la Nota
              finalHtml += '<div class="col-xs-12" style="margin-top:10px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Información de la Nota</h5></div>';

              // Respaldo de Proveedor
              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Proveedor: </span><span class="pull-right">' + proveedor + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Doc. Original: </span><span class="pull-right">' + docOrig + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Total: </span><span class="pull-right">' + total + '</span></div>';

              // SECCION 2: Estado y Fecha
              finalHtml += '<div class="col-xs-12" style="margin-top:15px; margin-bottom:5px; border-bottom: 2px solid #3c8dbc; text-align: left;">';
              finalHtml += '<h5 style="font-weight:bold; color:#3c8dbc; margin:0; text-align: left;">Estado y Fecha</h5></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Estado DIAN: </span><span class="pull-right">' + estadoDian + '</span></div>';

              finalHtml += '<div class="col-xs-12" style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: left;">';
              finalHtml += '<span class="text-bold">Fecha: </span><span class="pull-right">' + fecha + '</span></div>';

              return finalHtml ? $('<div class="row" style="padding: 10px; background-color: #fcfcfc; margin: 0; text-align: left;">').append(finalHtml) : false;
            }
          }
        },
        "columnDefs": [
          { "targets": 0, "className": 'dtr-control', "responsivePriority": 1 },
          { "targets": 6, "responsivePriority": 1 }, // Acciones con máxima prioridad
          { "targets": 2, "responsivePriority": 2 }, // Proveedor con prioridad 2
          { "targets": [1, 3, 4, 5], "responsivePriority": 3 }
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

<script src="vistas/js/notas-ajuste-ds.js?v=<?php echo time(); ?>"></script>