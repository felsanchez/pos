<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Administrar Sucursales (Bodegas)
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Sucursales</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarBodega">
          <i class="fa fa-plus"></i> Agregar Sucursal
        </button>

      </div>

      <div class="box-body">

        <div class="table-responsive">
          <table class="table table-bordered table-striped tablaBodegas display nowrap" width="100%">

            <thead>

              <tr>

                <th style="width:10px">#</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Acciones</th>

              </tr>

            </thead>

            <tbody>

              <?php

              $item = null;
              $valor = null;

              $bodegas = ControladorBodegas::ctrMostrarBodegas($item, $valor);

              foreach ($bodegas as $key => $value) {

                echo '<tr>

                        <td>' . ($key + 1) . '</td>
                        <td>' . $value["nombre"] . '</td>
                        <td>' . $value["direccion"] . '</td>
                        <td>' . $value["telefono"] . '</td>';

                echo '<td>

                          <div class="btn-group">

                            <button class="btn btn-primary btnIngresarBodega" idBodega="' . $value["id"] . '" title="Ingresar a esta sucursal"><i class="fa fa-sign-in"></i></button>
                              
                            <button class="btn btn-warning btnEditarBodega" idBodega="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarBodega" title="Editar bodega"><i class="fa fa-pencil"></i></button>';

                if ($value["id"] != 1) {
                  echo '<button class="btn btn-danger btnEliminarBodega" idBodega="' . $value["id"] . '" title="Eliminar bodega"><i class="fa fa-times"></i></button>';
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

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR BODEGA
======================================-->

<div id="modalAgregarBodega" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar Sucursal</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-building"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaBodega"
                  placeholder="Ingresar nombre de la sucursal" required>

              </div>

            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaDireccionBodega"
                  placeholder="Ingresar dirección" required>

              </div>

            </div>

            <!-- ENTRADA PARA EL TELÉFONO -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoTelefonoBodega"
                  placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask>

              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar Sucursal</button>

        </div>

        <?php

        $crearBodega = new ControladorBodegas();
        $crearBodega->ctrCrearBodega();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR BODEGA
======================================-->

<div id="modalEditarBodega" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar Sucursal</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-building"></i></span>

                <input type="text" class="form-control input-lg" name="editarBodega" id="editarBodega" required>
                <input type="hidden" name="idBodega" id="idBodega">

              </div>

            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>

                <input type="text" class="form-control input-lg" name="editarDireccionBodega" id="editarDireccionBodega"
                  required>

              </div>

            </div>

            <!-- ENTRADA PARA EL TELÉFONO -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-phone"></i></span>

                <input type="text" class="form-control input-lg" name="editarTelefonoBodega" id="editarTelefonoBodega"
                  data-inputmask="'mask':'(999) 999-9999'" data-mask>

              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

        <?php

        $editarBodega = new ControladorBodegas();
        $editarBodega->ctrEditarBodega();

        ?>

      </form>

    </div>

  </div>

</div>

<?php

$borrarBodega = new ControladorBodegas();
$borrarBodega->ctrBorrarBodega();

?>