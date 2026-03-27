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

            Seguimiento a Leads

        </h1>

        <ol class="breadcrumb">

            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

            <li class="active">Seguimiento a Leads</li>

        </ol>

    </section>

    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <?php if(puedeAccion('seguimiento_leads', 'eliminar')): ?>
                <button class="btn btn-danger" id="btnEliminarSeleccionados" disabled>
                    <i class="fa fa-trash"></i> Eliminar seleccionados
                </button>
                <?php endif; ?>

            </div>

            <div class="box-body">

                <table class="table table-bordered table-striped dt-responsive tablaSeguimiento" width="100%">

                    <thead>

                        <tr>

                            <th style="width:10px">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th style="width:10px">#</th>
                            <th>Ultimo seguimiento</th>
                            <th>Nombre Lead</th>
                            <th>Celular Lead</th>
                            <th>Contexto</th>
                            <th>Estado</th>
                            <th>Seguimiento 1</th>
                            <th>Seguimiento 2</th>
                            <th>Seguimiento 3</th>
                            <th>Completo pedido</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $item = null;
                        $valor = null;

                        $seguimientos = ControladorSeguimiento::ctrMostrarSeguimientos($item, $valor);

                        foreach ($seguimientos as $key => $value) {

                            // Lógica de colores para Estado
                            $estado = $value["estado"];
                            $colorEstado = "#d2d6de"; // Default Gris
                        
                            if (stripos($estado, "frio") !== false) {
                                $colorEstado = "#3c8dbc"; // Azul
                            } elseif (stripos($estado, "tibio") !== false) {
                                $colorEstado = "#f39c12"; // Amarillo
                            } elseif (stripos($estado, "caliente") !== false) {
                                $colorEstado = "#2ecc71"; // Verde Claro
                            }

                            $estadoBadge = '<span class="badge" style="background-color: ' . $colorEstado . '">' . $estado . '</span>';

                            // Badges para Seguimientos (Verde estándar)
                            $seg1 = !empty($value["seguimiento1"]) ? '<span class="badge" style="background-color: #28a745">' . $value["seguimiento1"] . '</span>' : '';
                            $seg2 = !empty($value["seguimiento2"]) ? '<span class="badge" style="background-color: #28a745">' . $value["seguimiento2"] . '</span>' : '';
                            $seg3 = !empty($value["seguimiento3"]) ? '<span class="badge" style="background-color: #28a745">' . $value["seguimiento3"] . '</span>' : '';

                            // Badge para Pedido (Verde Oscuro)
                            $pedido = !empty($value["hizo_pedido"]) ? '<span class="badge" style="background-color: #006400">' . $value["hizo_pedido"] . '</span>' : '';

                            echo '<tr>
                    <td><input type="checkbox" class="checkItem" value="' . $value["id"] . '"></td>
                    <td>' . ($key + 1) . '</td>
                    <td>' . $value["fecha"] . '</td>
                    <td>' . $value["nombre"] . '</td>
                    <td>' . $value["celular"] . '</td>
                    <td>' . $value["contexto"] . '</td>
                    <td>' . $estadoBadge . '</td>
                    <td>' . $seg1 . '</td>
                    <td>' . $seg2 . '</td>
                    <td>' . $seg3 . '</td>
                    <td>' . $pedido . '</td>
                  </tr>';
                        }

                        ?>

                    </tbody>

                </table>

            </div>

        </div>

    </section>

</div>