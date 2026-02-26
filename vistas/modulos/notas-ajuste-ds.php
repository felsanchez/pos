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
                <a href="documentos-soporte">
                    <button class="btn btn-primary">
                        Generar Nueva Nota de Ajuste (Desde DS)
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
                                    <td>' . $value["numero_nota_ajuste"] . '</td>
                                    <td>' . $value["numero_ds_original"] . '</td>
                                    <td>' . ($proveedor["nombre"] ?? "N/A") . '</td>
                                    <td>$ ' . number_format((float) ($value["monto_total"] ?? 0), 2) . '</td>
                                    <td>' . $value["fecha_envio_dian"] . '</td>';

                            if ($value["estado_dian"] == "enviada") {
                                echo '<td><button class="btn btn-success btn-xs">Exitosa</button></td>';
                            } else {
                                echo '<td><button class="btn btn-danger btn-xs">Pendiente</button></td>';
                            }

                            echo '<td>
                                        <div class="btn-group">
                                            <a href="index.php?ruta=ver-nota-ajuste-ds&idNota=' . $value["id"] . '" class="btn btn-info"><i class="fa fa-eye"></i></a>';

                            // Botón PDF si tiene URL
                            if (!empty($value["pdf_dian"])) {
                                echo '<a href="' . $value["pdf_dian"] . '" target="_blank" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i></a>';
                            }

                            // Botón XML si tiene URL
                            if (!empty($value["xml_dian"])) {
                                echo '<a href="' . $value["xml_dian"] . '" target="_blank" class="btn btn-primary"><i class="fa fa-file-code-o"></i></a>';
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