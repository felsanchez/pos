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
                <a href="crear-nota-credito">
                    <button class="btn btn-primary">
                        Generar Nueva Nota Crédito
                    </button>
                </a>
            </div>

            <div class="box-body">
                <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
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
                                    <td>' . ($key + 1) . '</td>
                                    <td' . ($value["estado_dian"] == "borrador" ? ' class="text-yellow" style="font-weight:bold"' : '') . '>' . $value["numero_nota_credito"] . '</td>
                                    <td>' . $value["numero_factura_original"] . '</td>
                                    <td>' . ($cliente["nombre"] ?? "N/A") . '</td>
                                    <td>$ ' . number_format((float) ($value["monto_total"] ?? 0), 2) . '</td>
                                    <td>' . $value["fecha_creacion"] . '</td>';

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
                                            <a href="index.php?ruta=ver-nota-credito&idNota=' . $value["id"] . '" class="btn btn-info"><i class="fa fa-eye"></i></a>';

                            if ($value["estado_dian"] == "borrador") {
                                // Botón Firmar
                                echo '<button class="btn btn-success btnFirmarNotaCredito" idNota="' . $value["id"] . '" title="Firmar y Enviar a DIAN"><i class="fa fa-paper-plane"></i></button>';
                                // Botón Eliminar
                                echo '<button class="btn btn-danger btnEliminarNotaCredito" idNota="' . $value["id"] . '" title="Eliminar Borrador"><i class="fa fa-trash"></i></button>';
                            } else {
                                // Botón XML si tiene URL
                                if (!empty($value["xml_dian_nc"])) {
                                    echo '<a href="' . $value["xml_dian_nc"] . '" target="_blank" class="btn btn-primary" title="Ver XML Factus"><i class="fa fa-file-code-o"></i></a>';
                                }

                                // Botón para ver en la DIAN
                                if (!empty($value["cufe_nc"])) {
                                    echo '<a href="https://catalogo-vpfe-hab.dian.gov.co/User/SearchDocument?DocumentKey=' . $value["cufe_nc"] . '" target="_blank" class="btn btn-warning" title="Ver en la DIAN"><i class="fa fa-institution"></i></a>';
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

<?php
$eliminarNota = new ControladorFactus();
$eliminarNota->ctrEliminarNotaCredito();
?>