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
                <a href="crear-nota-ajuste-ds">
                    <button class="btn btn-primary">
                        Generar Nueva Nota de Ajuste
                    </button>
                </a>
            </div>

            <div class="box-body">
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

                        $notas = ControladorFactus::ctrMostrarNotasAjusteDS(null, null);

                        foreach ($notas as $key => $value) {

                            $proveedor = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

                            echo '<tr>
                                    <td>' . ($key + 1) . '</td>
                                    <td' . ($value["estado_dian"] == "borrador" ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . $value["numero_nota_ajuste"] . '</td>
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
                                echo '<button class="btn btn-success btnFirmarNotaAjusteDS" idNota="' . $value["id"] . '" title="Firmar y Enviar a DIAN"><i class="fa fa-paper-plane"></i></button>';
                                // Botón Eliminar
                                echo '<button class="btn btn-danger btnEliminarNotaAjusteDS" idNota="' . $value["id"] . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
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
                                echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . $value["cuds_ajuste"] . '" target="_blank" class="btn btn-warning" title="Ver en DIAN"><i class="fa fa-globe"></i></a>';
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

<script src="vistas/js/notas-ajuste-ds.js?v=<?php echo time(); ?>"></script>