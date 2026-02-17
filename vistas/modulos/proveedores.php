<!-- Solo muestra 2 campos en movil en la Tabla 1-->
<style>
  /* Estilos para el botón de expansión en móvil */
  @media (max-width: 767px) {
    .tablaProveedores td.control {
      cursor: pointer;
    }

    /* Resize action buttons on mobile */
    .tablaProveedores .btn-group .btn {
      padding: 1px 5px;
      font-size: 12px;
      line-height: 1.5;
      border-radius: 3px;
    }
  }

  /* Estilos para campo notas editable */
  .celda-notas-proveedor {
    background: #fff9e6;
    padding: 8px;
    border-radius: 3px;
    cursor: text;
    font-size: 13px;
    color: #333;
    min-height: 30px;
    position: relative;
  }

  /* Placeholder para cuando está vacío */
  .celda-notas-proveedor:empty:before,
  .celda-notas-proveedor[data-placeholder]:before {
    content: "Escribe una nota...";
    color: #999;
    font-style: italic;
  }

  /* Ocultar placeholder cuando tiene foco */
  .celda-notas-proveedor:focus:before {
    content: none;
  }

  .celda-notas-proveedor:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
  }
</style>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar proveedores
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar proveedores</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProveedor">

          Agregar proveedor

        </button>

      </div>


      <div class="box-body table-responsive">

        <table class="table table-bordered table-striped dt-responsive tablaProveedores" width="100%">

          <thead>
            <tr>
              <th style="width: 10px"></th>
              <th style="width: 10px">#</th>
              <th>Nombre</th>
              <th>Marca</th>
              <th>Celular</th>
              <th>Correo</th>
              <th>Dirección</th>
              <th>Productos</th>
              <th>Notas</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>

            <?php

            $item = null;
            $valor = null;

            $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);


            foreach ($proveedores as $key => $value) {

              // Contar productos asociados a este proveedor
            
              $totalProductos = ModeloProveedores::mdlContarProductosPorProveedor($value["id"]);

              echo '<tr> 
                        <td></td>
                        <td>' . ($key + 1) . '</td>
                        <td>' . $value["nombre"] . '</td>
                        <td>' . $value["marca"] . '</td>';

              echo '<td>' . $value["celular"] . '</td>';
              echo '<td>' . $value["correo"] . '</td>';
              echo '<td>' . $value["direccion"] . '</td>';

              echo '<td><span class="badge bg-blue">' . $totalProductos . '</span></td>';

              // Columna notas editable
              $notas = isset($value["notas"]) ? $value["notas"] : '';
              echo '<td contenteditable="true" class="celda-notas-proveedor" data-id="' . $value['id'] . '">' . $notas . '</td>';

              echo '<td>
                      <div class="btn-group">

                        <button class="btn btn-warning btnEditarProveedor" idProveedor="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarProveedor"><i class="fa fa-pencil"></i></button>';

              if ($totalProductos == 0) {
                echo '<button class="btn btn-danger btnEliminarProveedor" idProveedor="' . $value["id"] . '"><i class="fa fa-times"></i></button>';

              } else {
                echo '<button class="btn btn-danger" disabled title="No se puede eliminar porque tiene productos asociados"><i class="fa fa-times"></i></button>';
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
MODAL AGREGAR Proveedor
======================================-->

<!-- Modal -->
<div id="modalAgregarProveedor" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Proveedor</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

              <!-- Nombre -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="nuevoProveedor" placeholder="Nombre del proveedor"
                      required>
                  </div>
                </div>
              </div>

              <!-- Marca -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Marca *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" name="nuevaMarca" placeholder="Marca del proveedor"
                      required>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Celular -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Celular *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control" name="nuevoCelular" placeholder="Número de celular"
                      required>
                  </div>
                </div>
              </div>

              <!-- Correo -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Correo</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control" name="nuevoCorreo" placeholder="Correo electrónico">
                  </div>
                </div>
              </div>

            </div>

            <!-- Dirección -->
            <div class="form-group">
              <label>Dirección</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-home"></i></span>
                <input type="text" class="form-control" name="nuevaDireccion" placeholder="Dirección del proveedor">
              </div>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar proveedor</button>

        </div>

        <?php

        $crearProveedor = new ControladorProveedores();
        $crearProveedor->ctrCrearProveedor();

        ?>

      </form>

    </div>


  </div>

</div>




<!--==========================================================================================================
MODAL EDITAR Proveedor
===========================================================================================================-->

<!-- Modal -->
<div id="modalEditarProveedor" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Proveedor</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

              <!-- Nombre -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nombre *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" name="editarProveedor" id="editarProveedor"
                      placeholder="Nombre del proveedor" required>
                    <input type="hidden" id="idProveedor" name="idProveedor">
                  </div>
                </div>
              </div>

              <!-- Marca -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Marca *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" name="editarMarca" id="editarMarca"
                      placeholder="Marca del proveedor" required>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Celular -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Celular *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control" name="editarCelular" id="editarCelular"
                      placeholder="Número de celular" required>
                  </div>
                </div>
              </div>

              <!-- Correo -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Correo</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control" name="editarCorreo" id="editarCorreo"
                      placeholder="Correo electrónico">
                  </div>
                </div>
              </div>

            </div>

            <!-- Dirección -->
            <div class="form-group">
              <label>Dirección</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-home"></i></span>
                <input type="text" class="form-control" name="editarDireccion" id="editarDireccion"
                  placeholder="Dirección del proveedor">
              </div>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar proveedor</button>

        </div>

        <?php

        $editarProveedor = new ControladorProveedores();
        $editarProveedor->ctrEditarProveedor();

        ?>

      </form>

    </div>


  </div>

</div>


<?php

$borrarProveedor = new ControladorProveedores();
$borrarProveedor->ctrBorrarProveedor();

?>