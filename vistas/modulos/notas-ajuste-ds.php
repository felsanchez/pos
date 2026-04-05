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

                /* Hide table while loading to prevent layout jump */
                .tablas:not(.datatable-ready) {
                    visibility: hidden;
                    height: 0;
                    overflow: hidden;
                    opacity: 0;
                }

                .tablas.datatable-ready {
                    transition: opacity 0.5s ease;
                    opacity: 1;
                }
            </style>

            <div class="box-body">

                <div id="loader-table-na" class="loader-container">
                    <i class="fa fa-refresh fa-spin"></i>
                    <span>Cargando Notas de Ajuste...</span>
                </div>

                <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
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
                                    <td>' . ($key + 1) . '</td>
                                    <td' . ($value["estado_dian"] == "borrador" ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . $numeroMostrar . '</td>
                                    <td>' . $value["numero_ds_original"] . '</td>
                                    <td>' . ($proveedor["nombre"] ?? "N/A") . '</td>
                                    <td>$ ' . number_format((float) ($value["monto_total"] ?? 0), 2) . '</td>
                                    <td>' . $value["fecha_envio_dian"] . '</td>';

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
                                            <a href="index.php?ruta=ver-nota-ajuste-ds&idNota=' . $value["id"] . '" class="btn btn-info"><i class="fa fa-eye"></i></a>';

                            if ($value["estado_dian"] == "borrador") {
                                // Botón Firmar
                                if (puedeAccion('documento_soporte', 'editar')) {
                                    echo '<button class="btn btnFirmarNotaAjusteDS" style="background-color: black; color: white;" idNota="' . $value["id"] . '" title="Firmar y Enviar a DIAN"><i class="fa fa-paper-plane"></i></button>';
                                }
                                // Botón Eliminar
                                if (puedeAccion('documento_soporte', 'eliminar')) {
                                    echo '<button class="btn btn-danger btnEliminarNotaAjusteDS" idNota="' . $value["id"] . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                                }
                            } else {
                                // Botón PDF si tiene URL
                                if (!empty($value["pdf_dian"])) {
                                    echo '<a href="' . $value["pdf_dian"] . '" target="_blank" class="btn btn-danger" title="Ver PDF Factus"><i class="fa fa-file-pdf-o"></i></a>';
                                }

                                // Botón XML si tiene URL
                                if (!empty($value["xml_dian"])) {
                                    echo '<a href="' . $value["xml_dian"] . '" target="_blank" class="btn btn-primary" title="Ver XML Factus"><i class="fa fa-file-code-o"></i></a>';
                                }

                                // Botón para ver en la DIAN
                                echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . $value["cuds_ajuste"] . '" target="_blank" class="btn btn-success" title="Ver en DIAN"><i class="fa fa-external-link"></i></a>';

                                // Botón para enviar por correo (Solo si está aceptada o enviada)
                                if ($value["estado_dian"] == "aceptada" || $value["estado_dian"] == "enviada") {
                                    echo '<button class="btn btn-primary btnEnviarEmailNA" idNA="' . $value["id"] . '" nombreProveedor="' . ($proveedor["nombre"] ?? "N/A") . '" emailProveedor="' . ($proveedor["correo"] ?? '') . '" title="Enviar por Correo"><i class="fa fa-envelope"></i></button>';
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

<script src="vistas/js/notas-ajuste-ds.js?v=<?php echo time(); ?>"></script>