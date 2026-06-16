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
            Administrar Notas de Ajuste
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Administrar Notas de Ajuste DS</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <?php if (puedeAccion('notas_ajuste', 'crear')): ?>
                  <?php if (ControladorCajas::ctrValidarCajaAbierta()): ?>
                    <a href="crear-nota-ajuste-ds">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Nota de Ajuste
                        </button>
                    </a>
                  <?php else: ?>
                    <button class="btn btn-primary" onclick="alertaCajaCerradaNA()">
                      <i class="fa fa-plus"></i> Crear Nota de Ajuste
                    </button>
                    <script>
                    function alertaCajaCerradaNA(){
                        swal({
                            title: '¡Caja Cerrada!',
                            text: 'Debe abrir caja antes de realizar esta operación.',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3c8dbc',
                            cancelButtonColor: '#6c757d',
                            cancelButtonText: 'Entendido',
                            confirmButtonText: 'Abrir caja'
                        }).then(function(result){
                            if (result.value) {
                                $('#modalAperturaCaja').modal('show');
                            }
                        });
                    }
                    </script>
                  <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear nota de ajuste">
                      <i class="fa fa-plus"></i> Crear Nota de Ajuste
                    </button>
                <?php endif; ?>

                <div class="pull-right form-filtros-na" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">

                    <input type="hidden" id="fechaInicialNA" value="">
                    <input type="hidden" id="fechaFinalNA" value="">

                    <!-- Filtro por Sucursal (Administradores) -->
                    <?php 
                    $configuracionGlobal = ControladorConfiguracion::ctrObtenerConfiguracion();
                    $sucursalesActivas = !isset($configuracionGlobal["activar_sucursales"]) || $configuracionGlobal["activar_sucursales"] == 1;
                    if ($sucursalesActivas && stripos($_SESSION["perfil"], "Admin") !== false): 
                    ?>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span><b>Sucursal:</b></span>
                            <div class="input-group" style="width: 200px;">
                                <span class="input-group-addon"><i class="fa fa-building text-primary"></i></span>
                                <select class="form-control select2" id="sucursal_na" name="sucursal_na">
                                    <?php
                                    $bodegaSeleccionada = '';
                                    if (!empty($_SESSION['id_bodega'])) {
                                        $bodegaSeleccionada = $_SESSION['id_bodega'];
                                    }
                                    $selectedTodas = ($bodegaSeleccionada === 'todas' || $bodegaSeleccionada === '') ? 'selected' : '';
                                    echo '<option value="" ' . $selectedTodas . '>Todas las Sucursales</option>';

                                    $item = null;
                                    $valor = null;
                                    $bodegas = ControladorBodegas::ctrMostrarBodegas($item, $valor);
                                    foreach ($bodegas as $key => $value) {
                                        $selected = ($bodegaSeleccionada == $value["id"]) ? 'selected' : '';
                                        echo '<option value="' . $value["id"] . '" ' . $selected . '>' . $value["nombre"] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botón Rango de Fecha -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span><b>Fecha:</b></span>
                        <button type="button" class="btn btn-default" id="daterange-btn-na">
                            <span>
                                <i class="fa fa-calendar"></i> Mostrar todas
                            </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>

                    <!-- Botón Limpiar -->
                    <button class="btn btn-default" id="btnLimpiarFiltrosNA" title="Limpiar filtros">
                        <i class="fa fa-refresh"></i>
                    </button>
                </div>
            </div>

            <style>
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

                    .form-filtros-na {
                        float: none !important;
                        width: 100% !important;
                        margin-top: 15px !important;
                        padding-left: 0 !important;
                        padding-right: 0 !important;
                        display: flex !important;
                        flex-direction: column !important;
                        align-items: stretch !important;
                        gap: 12px !important;
                    }
                    .form-filtros-na > div {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        width: 100% !important;
                        gap: 10px !important;
                    }
                    .form-filtros-na > div > span {
                        min-width: 80px !important;
                        text-align: left !important;
                    }
                    .form-filtros-na > div > .input-group {
                        flex: 1 !important;
                        width: auto !important;
                    }
                    .form-filtros-na > div .select2-container {
                        width: 100% !important;
                    }
                    .form-filtros-na > div > #daterange-btn-na {
                        flex: 1 !important;
                        width: auto !important;
                        text-align: left !important;
                        display: flex !important;
                        justify-content: space-between !important;
                        align-items: center !important;
                    }
                    .form-filtros-na > button {
                        width: 100% !important;
                        text-align: center !important;
                    }
                }
            </style>

            <div class="box-body">
                <div class="table-responsive">
                    <table id="tablaListadoNotasAjusteDS"
                        class="table table-bordered table-striped dt-responsive tablas tablaNotasAjusteDS display nowrap"
                        width="100%">
                        <thead>
                            <tr>
                                <th>Código Nota</th>
                                <th>Doc. Original</th>
                                <th>Proveedor</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Estado DIAN</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán mediante Server-Side DataTables -->
                        </tbody>
                    </table>
                </div>
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
        // Inicializar Select2 para el filtro de sucursal
        if($(".select2").length > 0){
            $(".select2").select2();
        }

        var tablaListadoNotasAjusteDS = null;

        function cargarTablaNotasAjusteDS() {
            if ($("#tablaListadoNotasAjusteDS").length > 0) {
                if ($.fn.DataTable.isDataTable('#tablaListadoNotasAjusteDS')) {
                    $('#tablaListadoNotasAjusteDS').DataTable().destroy();
                }

                tablaListadoNotasAjusteDS = $("#tablaListadoNotasAjusteDS").DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "ajax/factus.ajax.php",
                        "type": "POST",
                        "data": function(d) {
                            d.accion = "mostrarNotasAjusteDSServerSide";
                            d.idBodega = $("#sucursal_na").val();
                            d.fechaInicial = $("#fechaInicialNA").val();
                            d.fechaFinal = $("#fechaFinalNA").val();
                        }
                    },
                    "autoWidth": false,
                    "order": [[5, "desc"]], // Fecha
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
                        { "targets": 7, "responsivePriority": 2, "orderable": false }, // Acciones
                        { "targets": 1, "responsivePriority": 3 }, // Doc Original
                        { "targets": 2, "responsivePriority": 4 }, // Proveedor
                        { "targets": 3, "responsivePriority": 5 }, // Vendedor
                        { "targets": 4, "responsivePriority": 6 }, // Total
                        { "targets": 5, "responsivePriority": 7 }, // Fecha
                        { "targets": 6, "responsivePriority": 8 }  // Estado DIAN
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
        }

        // Cargar tabla inicial
        setTimeout(cargarTablaNotasAjusteDS, 200);

        // Recargar tabla al cambiar sucursal
        $("#sucursal_na").on("change", function() {
            if (tablaListadoNotasAjusteDS) {
                tablaListadoNotasAjusteDS.ajax.reload();
            } else {
                cargarTablaNotasAjusteDS();
            }
        });

        /*=============================================
        RANGO DE FECHAS NOTAS AJUSTE DS
        =============================================*/
        if ($('#daterange-btn-na').length > 0 && typeof $.fn.daterangepicker !== 'undefined') {
            $('#daterange-btn-na span').html('<i class="fa fa-calendar"></i> Mostrar todas');

            $('#daterange-btn-na').daterangepicker(
                {
                    ranges: {
                        'Mostrar todas': [moment('2000-01-01'), moment()],
                        'Hoy': [moment(), moment()],
                        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                        'Este mes': [moment().startOf('month'), moment().endOf('month')],
                        'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    startDate: moment(),
                    endDate: moment()
                },
                function (start, end) {
                    if (start.format('YYYY-MM-DD') === '2000-01-01') {
                        $('#daterange-btn-na span').html('<i class="fa fa-calendar"></i> Mostrar todas');
                        $('#fechaInicialNA').val('');
                        $('#fechaFinalNA').val('');
                    } else {
                        $('#daterange-btn-na span').html('<i class="fa fa-calendar"></i> ' + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                        $('#fechaInicialNA').val(start.format('YYYY-MM-DD'));
                        $('#fechaFinalNA').val(end.format('YYYY-MM-DD'));
                    }
                    if (tablaListadoNotasAjusteDS) {
                        tablaListadoNotasAjusteDS.ajax.reload();
                    }
                }
            );

            $('#daterange-btn-na').on('cancel.daterangepicker', function () {
                $(this).find('span').html('<i class="fa fa-calendar"></i> Mostrar todas');
                $('#fechaInicialNA').val('');
                $('#fechaFinalNA').val('');
                if (tablaListadoNotasAjusteDS) {
                    tablaListadoNotasAjusteDS.ajax.reload();
                }
            });
        }

        /*=============================================
        LIMPIAR FILTROS NOTAS AJUSTE DS
        =============================================*/
        $(document).on("click", "#btnLimpiarFiltrosNA", function() {
            if ($("#sucursal_na").length > 0) {
                $("#sucursal_na").val('').trigger("change.select2");
            }
            $("#fechaInicialNA").val("");
            $("#fechaFinalNA").val("");
            $('#daterange-btn-na span').html('<i class="fa fa-calendar"></i> Mostrar todas');
            if (tablaListadoNotasAjusteDS) {
                tablaListadoNotasAjusteDS.ajax.reload();
            }
        });
    });
</script>

<script src="vistas/js/notas-ajuste-ds.js?v=<?php echo time(); ?>"></script>