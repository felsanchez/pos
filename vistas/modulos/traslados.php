<?php
if (!puedeVer("traslados")) {
  echo '<script>
    window.location = "inicio";
  </script>';
  return;
}

if (isset($_GET["idTrasladoCancelar"])) {
  $idTraslado = $_GET["idTrasladoCancelar"];
  $respuesta = ControladorTraslados::ctrCancelarTraslado($idTraslado);
  if ($respuesta == "ok") {
    echo '<script>
      swal({
        type: "success",
        title: "El traslado ha sido cancelado",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      }).then((result) => {
        if (result.value) {
          window.location = "traslados";
        }
      })
    </script>';
  }
}
?>
<!-- DateRangePicker -->
<link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">

<style>
  .celda-notas-traslado {
    background: #fff9e6;
    padding: 8px;
    border-radius: 3px;
    font-size: 12px;
    color: #666;
    border-left: 2px solid #f39c12;
    cursor: text;
    min-height: 30px;
    position: relative;
    display: block;
    transition: all 0.3s ease;
  }

  .celda-notas-traslado:empty:not(:focus):before {
    content: attr(data-placeholder);
    color: #999;
    font-style: italic;
    position: absolute;
    left: 8px;
    top: 8px;
  }

  .celda-notas-traslado:focus:before,
  .celda-notas-traslado:not(:empty):before {
    display: none !important;
    content: none !important;
  }

  .celda-notas-traslado:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
    box-shadow: 0 0 5px rgba(243, 156, 18, 0.3);
  }
</style>

<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Administrar traslados entre sucursales
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar traslados</li>
    </ol>

  </section>

  <section class="content">

    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4><i class="icon fa fa-warning"></i> Atención!</h4>
      Los registros de traslados se eliminan automáticamente del sistema después de transcurrir <b>3 meses</b> desde su
      creación.
    </div>

    <div class="box">

      <div class="box-header with-border">

        <?php if (puedeAccion('traslados', 'crear')): ?>
          <a href="crear-traslado">
            <button class="btn btn-primary">
              <i class="fa fa-plus"></i> Crear Traslados
            </button>
          </a>
        <?php endif; ?>

        <div class="pull-right">
          <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="ruta" value="traslados">
            
            <input type="hidden" name="fechaInicial" id="fechaInicial"
              value="<?php echo isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null; ?>">
            <input type="hidden" name="fechaFinal" id="fechaFinal"
              value="<?php echo isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null; ?>">

            <!-- Botón Rango de Fecha -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="hidden-xs"><b>Fecha:</b></span>
              <button type="button" class="btn btn-default" id="daterange-btn-traslados">
                <span>
                  <i class="fa fa-calendar"></i> Rango de fecha
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
            </div>

            <!-- Botón Limpiar -->
            <a href="index.php?ruta=traslados" class="btn btn-default" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </a>
          </form>
        </div>

      </div>

      <div class="box-body">

        <div class="table-responsive">
          <table class="table table-bordered table-striped dt-responsive tablas" width="100%">

            <thead>

              <tr>
                <th style="width:10px">#</th>
                <th>Código</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Usuario</th>
                <th>Items</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Notas</th>
                <th>Acciones</th>
              </tr>

            </thead>

            <tbody>

              <?php

              $item = null;
              $valor = null;
              
              $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
              $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

              $traslados = ControladorTraslados::ctrMostrarTraslados($item, $valor, $fechaInicial, $fechaFinal);

              foreach ($traslados as $key => $value) {

                echo '<tr>
                        <td>' . ($key + 1) . '</td>
                        <td>' . $value["codigo"] . '</td>
                        <td>' . $value["bodega_origen"] . '</td>
                        <td>' . $value["bodega_destino"] . '</td>
                        <td>' . $value["usuario"] . '</td>
                        <td>' . $value["total_items"] . '</td>';

                if ($value["estado"] == "pendiente") {
                  echo '<td><button class="btn btn-warning btn-xs">Pendiente</button></td>';
                } else if ($value["estado"] == "completado") {
                  echo '<td><button class="btn btn-success btn-xs">Completado</button></td>';
                } else {
                  echo '<td><button class="btn btn-danger btn-xs">Cancelado</button></td>';
                }

                echo '<td>' . $value["fecha"] . '</td>
                      <td><div contenteditable="true" class="celda-observacion celda-notas-traslado" data-id="' . $value["id"] . '" data-placeholder="Escribe una nota...">' . e(trim($value["notas"])) . '</div></td>
                      <td>
                          <div class="btn-group">
                            <button class="btn btn-info btnVerTraslado" idTraslado="' . $value["id"] . '" data-toggle="modal" data-target="#modalVerTraslado" title="Ver traslado"><i class="fa fa-eye"></i></button>';

                if ($value["estado"] == "pendiente") {
                  echo '<button class="btn btn-success btnCompletarTraslado" idTraslado="' . $value["id"] . '" title="Completar traslado"><i class="fa fa-check"></i></button>
                                    <button class="btn btn-danger btnCancelarTraslado" idTraslado="' . $value["id"] . '" title="Eliminar traslado"><i class="fa fa-times"></i></button>';
                }

                echo '    </div>  
                        </td>
                      </tr>';
              }

              ?>

            </tbody>

          </table>
        </div>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL VER DETALLE TRASLADO
======================================-->

<div id="modalVerTraslado" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color:white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>

        <h4 class="modal-title">Detalle del Traslado</h4>

      </div>

      <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

      <div class="modal-body">

        <div class="box-body">

          <table class="table table-bordered table-striped dt-responsive" width="100%">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Variante</th>
                <th>Cantidad</th>
              </tr>
            </thead>
            <tbody id="detalleTrasladoBody">
              <!-- Se llena por AJAX -->
            </tbody>
          </table>

          <div class="form-group">
            <label>Notas:</label>
            <p id="verNotasTraslado"></p>
          </div>

        </div>

      </div>

      <!--=====================================
        PIE DEL MODAL
        ======================================-->

      <div class="modal-footer">

        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

      </div>

    </div>

  </div>

</div>
