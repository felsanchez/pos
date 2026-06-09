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
                <?php if (puedeAccion('notas_credito', 'crear')): ?>
                  <?php if (ControladorCajas::ctrValidarCajaAbierta()): ?>
                    <a href="crear-nota-credito">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Nota Crédito
                        </button>
                    </a>
                  <?php else: ?>
                    <button class="btn btn-primary" onclick="alertaCajaCerradaNC()">
                      <i class="fa fa-plus"></i> Crear Nota Crédito
                    </button>
                    <script>
                    function alertaCajaCerradaNC(){
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
                    <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear nota crédito">
                      <i class="fa fa-plus"></i> Crear Nota Crédito
                    </button>
                <?php endif; ?>

                <div class="pull-right" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    
                    <input type="hidden" id="fechaInicialNC" value="">
                    <input type="hidden" id="fechaFinalNC" value="">

                    <!-- Filtro por Bodega (Administradores) -->
                    <?php if (stripos($_SESSION["perfil"], "Admin") !== false): ?>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="hidden-xs"><b>Sucursal:</b></span>
                            <div class="input-group" style="width: 180px;">
                                <span class="input-group-addon"><i class="fa fa-building text-primary"></i></span>
                                <select id="selectBodegaNC" class="form-control select2"
                                    data-default="<?php echo !empty($_SESSION['id_bodega']) ? e($_SESSION['id_bodega']) : 'todas'; ?>">
                                    <?php
                                    $bodegaSeleccionadaNC = '';
                                    if (isset($_GET['bodega']) && $_GET['bodega'] !== '') {
                                        $bodegaSeleccionadaNC = $_GET['bodega'];
                                    } elseif (!empty($_SESSION['id_bodega'])) {
                                        $bodegaSeleccionadaNC = $_SESSION['id_bodega'];
                                    }
                                    $selectedTodasNC = ($bodegaSeleccionadaNC === 'todas' || $bodegaSeleccionadaNC === '') ? 'selected' : '';
                                    echo '<option value="todas" ' . $selectedTodasNC . '>Mostrar Todas</option>';

                                    $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                                    foreach ($bodegas as $key => $valueBodega) {
                                        $selected = ($bodegaSeleccionadaNC == $valueBodega["id"]) ? 'selected' : '';
                                        echo '<option value="' . e($valueBodega["id"]) . '" ' . $selected . '>' . e($valueBodega["nombre"]) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botón Rango de Fecha -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="hidden-xs"><b>Fecha:</b></span>
                        <button type="button" class="btn btn-default" id="daterange-btn-nc">
                            <span>
                                <i class="fa fa-calendar"></i> Mostrar todas
                            </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>

                    <!-- Botón Limpiar -->
                    <button class="btn btn-default" id="btnLimpiarFiltrosNC" title="Limpiar filtros">
                        <i class="fa fa-refresh"></i>
                    </button>
                </div>
            </div>

            <style>
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
                <div class="table-responsive">
                    <table id="tablaListadoNotasCredito"
                        class="table table-bordered table-striped dt-responsive tablaNotasCredito display nowrap"
                        width="100%">
                        <thead>
                            <tr>
                                <th>Código Nota</th>
                                <th>Factura Original</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Observación</th>
                                <th>Estado DIAN</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán vía DataTables Server-Side -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="vistas/js/notas-credito.js?v=<?php echo time(); ?>"></script>


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