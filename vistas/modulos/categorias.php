<!-- Estilos para tabla de Categorías -->
<style>
  @media (max-width: 767px) {
    .tablaCategorias .btn-group .btn {
      padding: 1px 5px;
      font-size: 12px;
      line-height: 1.5;
      border-radius: 3px;
    }
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Administrar categorías</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar categorías</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <?php if (puedeAccion('categorias', 'crear')): ?>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCategoria">
            <i class="fa fa-plus"></i> Agregar categoría
          </button>
        <?php endif; ?>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped tablaCategorias">
          <thead>
              <th>Categoría</th>
              <th>Productos</th>
              <th style="width: 100px">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <?php
            $item = null;
            $valor = null;
            $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

            foreach ($categorias as $key => $value) {
              $totalProductos = ModeloCategorias::mdlContarProductosPorCategoria($value["id"]);

              echo '<tr>
                      <td class="text-uppercase">' . $value["categoria"] . '</td> 
                      <td><span class="badge bg-blue">' . $totalProductos . '</span></td> 
                      <td>
                        <div class="btn-group">';
              
              if (puedeAccion('categorias', 'editar')) {
                echo '<button class="btn btn-warning btnEditarCategoria" idCategoria="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarCategoria"><i class="fa fa-pencil"></i></button>';
              }

              if (puedeAccion('categorias', 'eliminar')) {
                echo '<button class="btn btn-danger btnEliminarCategoria" idCategoria="' . $value["id"] . '"><i class="fa fa-times"></i></button>';
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
  </section>
</div>


<!--=====================================
MODAL AGREGAR CATEGORIA
======================================-->

<!-- Modal -->
<div id="modalAgregarCategoria" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar categoría</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaCategoria" id="nuevaCategoria"
                  placeholder="Ingresar categoría" required>

              </div>

            </div>



          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar categoría</button>

        </div>


        <?php

        $crearCategoria = new ControladorCategorias();
        $crearCategoria->ctrCrearCategoria();

        ?>

      </form>

    </div>


  </div>

</div>


<!--==========================================================================
MODAL EDITAR CATEGORIA
===========================================================================-->

<!-- Modal -->
<div id="modalEditarCategoria" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar categoría</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para nombre -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>

                <input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>

                <input type="hidden" name="idCategoria" id="idCategoria" required>

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

        $editarCategoria = new ControladorCategorias();
        $editarCategoria->ctrEditarCategoria();

        ?>

      </form>

    </div>


  </div>

</div>


<?php

$borrarCategoria = new ControladorCategorias();
$borrarCategoria->ctrBorrarCategoria();

?>