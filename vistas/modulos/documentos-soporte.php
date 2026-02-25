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
                        $documentos = ControladorFactus::ctrMostrarDocumentosSoporte(null, null);
                        if ($documentos) {
                            foreach ($documentos as $key => $value) {
                                $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

                                echo '<tr>
                                    <td>' . ($key + 1) . '</td>
                                    <td>';
                                if ($value["numero_ds"] != "") {
                                    echo '<strong>' . $value["numero_ds"] . '</strong>';
                                } else {
                                    echo '<span class="text-primary">DS-' . $value["id"] . ' (Borrador)</span>';
                                }
                                echo '</td>
                                    <td>' . $proveedor["nombre"] . '</td>
                                    <td>' . $value["fecha_emision"] . '</td>
                                    <td>';
                                if ($value["estado_dian"] == "borrador") {
                                    echo '<span class="label label-info">Borrador</span>';
                                } else {
                                    echo '<span class="label label-success">Enviada</span>';
                                }
                                echo '</td>
                                    <td>$ ' . number_format($value["monto_total"], 0) . '</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="index.php?ruta=ver-documento-soporte&idDS=' . $value["id"] . '" class="btn btn-default btn-xs" title="Ver Detalle Local"><i class="fa fa-eye"></i></a>';

                                if ($value["estado_dian"] == "borrador") {
                                    echo '<button class="btn btn-primary btn-xs btnFirmarDS" idDS="' . $value["id"] . '" title="Firmar y Enviar a Factus"><i class="fa fa-pencil-square-o"></i></button>';
                                    echo '<button class="btn btn-danger btn-xs btnEliminarDS" idDS="' . $value["id"] . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                                } else {
                                    echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . $value["cuds"] . '" target="_blank" class="btn btn-warning btn-xs" title="Ver en DIAN"><i class="fa fa-globe"></i></a>';
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