<div class="content-wrapper">

    <section class="content-header">

        <h1>
            Documentos soporte
            <small>Adquisiciones a no obligados</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Documentos soporte</li>
        </ol>

    </section>

    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <?php if (puedeAccion('documento_soporte', 'crear')): ?>
                  <?php if (ControladorCajas::ctrValidarCajaAbierta()): ?>
                    <a href="crear-documento-soporte">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Documento Soporte
                        </button>
                    </a>
                  <?php else: ?>
                    <button class="btn btn-primary" onclick="alertaCajaCerradaDS()">
                      <i class="fa fa-plus"></i> Crear Documento Soporte
                    </button>
                    <script>
                    function alertaCajaCerradaDS(){
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
                    <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear documento soporte">
                      <i class="fa fa-plus"></i> Crear Documento Soporte
                    </button>
                <?php endif; ?>

                <!-- Filtro por Sucursal (Administradores) -->
                <?php if (stripos($_SESSION["perfil"], "Admin") !== false): ?>
                    <div class="pull-right" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 5px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="hidden-xs"><b>Sucursal:</b></span>
                            <div class="input-group" style="width: 200px;">
                                <span class="input-group-addon"><i class="fa fa-building text-primary"></i></span>
                                <select class="form-control select2" id="sucursal_ds" name="sucursal_ds">
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
                    </div>
                <?php endif; ?>

            </div>

            <div class="box-body">

                <div class="table-responsive">
                    <table id="tablaListadoDocumentoSoporte"
                        class="table table-bordered table-striped dt-responsive tablaDocumentosSoporte display nowrap"
                        width="100%">

                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Proveedor</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Estado DIAN</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        </tbody>

                    </table>
                </div>

            </div>

        </div>

    </section>



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
</div>