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

                <a href="crear-documento-soporte">
                    <button class="btn btn-primary">
                        Emitir Documento Soporte
                    </button>
                </a>

            </div>

            <div class="box-body">

                <table class="table table-bordered table-striped dt-responsive tablaDocumentosSoporte" width="100%">

                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Número DS</th>
                            <th>Proveedor</th>
                            <th>Fecha Emisión</th>
                            <th>Estado DIAN</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Obtener prefijo de Documento Soporte para borradores
                        $rangoActivoDS = ModeloFactus::mdlObtenerRangoDS();
                        $prefijoDS = $rangoActivoDS ? $rangoActivoDS["prefijo"] : "";

                        $documentos = ControladorFactus::ctrMostrarDocumentosSoporte(null, null);
                        if ($documentos) {
                            foreach ($documentos as $key => $value) {
                                $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

                                echo '<tr>
                                    <td>' . ($key + 1) . '</td>
                                    <td' . ($value["numero_ds"] == "" ? ' class="text-yellow" style="font-weight:bold"' : '') . '>';
                                if ($value["numero_ds"] != "") {
                                    echo $value["numero_ds"];
                                } else {
                                    echo $prefijoDS . $value["id"];
                                }
                                echo '</td>
                                    <td>' . $proveedor["nombre"] . '</td>
                                    <td>' . $value["fecha_emision"] . '</td>
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
                                    <td>$ ' . number_format($value["monto_total"], 0) . '</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="index.php?ruta=ver-documento-soporte&idDS=' . $value["id"] . '" class="btn btn-info" title="Ver Detalle"><i class="fa fa-eye"></i></a>';

                                if ($value["estado_dian"] == "borrador") {
                                    echo '<button class="btn btn-primary btnFirmarDS" idDS="' . $value["id"] . '" title="Firmar y Enviar a Factus"><i class="fa fa-pencil-square-o"></i></button>';
                                    echo '<button class="btn btn-danger btnEliminarDS" idDS="' . $value["id"] . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                                } else {
                                    if (ModeloFactus::mdlTieneNotaAjusteDS($value["id"])) {
                                        echo '<button class="btn btn-warning btnVerNotasAjusteDS" idDS="' . $value["id"] . '" data-toggle="modal" data-target="#modalNotasAjusteDS" title="Ver Notas de Ajuste">
                                                <i class="fa fa-list"></i>
                                              </button>';
                                    }

                                    echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . $value["cuds"] . '" target="_blank" class="btn btn-warning" title="Ver en DIAN"><i class="fa fa-globe"></i></a>';
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

    </section>

</div>

<!--=====================================
MODAL VER NOTAS DE AJUSTE DS
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