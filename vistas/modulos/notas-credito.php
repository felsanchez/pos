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
                    <a href="crear-nota-credito">
                        <button class="btn btn-primary">
                            <i class="fa fa-plus"></i> Crear Nota Crédito
                        </button>
                    </a>
                <?php endif; ?>
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


                <table id="tablaListadoNotasCredito"
                    class="table table-bordered table-striped dt-responsive tablaNotasCredito display nowrap"
                    width="100%">
                    <thead>
                        <tr>
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
                        <!-- Los datos se cargarán vía DataTables Server-Side -->
                    </tbody>
                </table>
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