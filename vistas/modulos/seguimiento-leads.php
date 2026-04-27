<?php

if ($_SESSION["perfil"] == "Especial" || !puedeAccion('seguimiento_leads', 'ver')) {

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
                                <?php if (puedeAccion('seguimiento_leads', 'eliminar')): ?>
                                    <input type="checkbox" id="checkAll">
                                <?php endif; ?>
                            </th>
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

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </section>

</div>
