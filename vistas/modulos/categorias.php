
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar categorías
      <small>Clasificación de productos</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Categorías</li>
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

      <div class="box-body">
        <div class="tabla-categorias tablas table-responsive">
          <table id="tablaCategoriasListado" class="table table-bordered table-striped tablaCategorias display nowrap"
            style="width: 100%;">
            <thead>
              <tr>
                <th>Categoría</th>
                <th>Prefijo</th>
                <th>Productos</th>
                <th style="width: 100px">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <!-- DataTables Server-Side -->
            </tbody>
          </table>
        </div> <!-- /.tabla-categorias -->
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

            <!-- entrada para prefijo -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span>

                <input type="text" class="form-control input-lg" name="nuevoPrefijo" id="nuevoPrefijo"
                  placeholder="Ingresar prefijo" required>

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

            <!-- entrada para prefijo -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span>

                <input type="text" class="form-control input-lg" name="editarPrefijo" id="editarPrefijo"
                  placeholder="Editar prefijo" required>

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