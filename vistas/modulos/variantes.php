<?php
if (!puedeVer('variantes')) {
  echo '<script>window.location = "inicio";</script>';
  return;
}
?>
<style>
  /* Ajuste de botones de acción en móvil */
  @media (max-width: 767px) {

    .tabla-tipos .btn-group .btn,
    .tabla-opciones .btn-group .btn {
      padding: 1px 5px !important;
      font-size: 12px !important;
      line-height: 1.5 !important;
    }
  }
</style>
<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Administrar Variantes
      <small>Tipos y Opciones</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Variantes</li>
    </ol>

  </section>

  <section class="content">

    <!-- =====================================
    TIPOS DE VARIANTES
    ====================================== -->
    <div class="box">

      <div class="box-header with-border">
        <?php if (puedeAccion('variantes', 'crear')): ?>
          <button class="btn btn-primary btnAbrirModalTipo" data-toggle="modal" data-target="#modalAgregarTipoVariante">
            <i class="fa fa-plus"></i> Agregar Tipo de Variante
          </button>
        <?php else: ?>
          <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear variantes">
            <i class="fa fa-plus"></i> Agregar Tipo de Variante
          </button>
        <?php endif; ?>
      </div>

      <div class="box-body">
        <div class="tabla-tipos table-responsive">
          <table id="tablaTiposVariantes" class="table table-bordered table-striped display nowrap"
            style="width: 100%;">

            <thead>
              <tr>
                <th>Nombre</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <!-- DataTables Server-Side -->
            </tbody>

          </table>
        </div> <!-- /.tabla-tipos -->
      </div>

    </div>

    <!-- =====================================
    OPCIONES DE VARIANTES (se muestra al hacer clic en "Opciones")
    ====================================== -->
    <div class="box box-info" id="boxOpciones" style="display:none;">

      <div class="box-header with-border">
        <h3 class="box-title">Opciones de: <span id="nombreTipoVariante"></span></h3>
        <input type="hidden" id="idTipoVarianteActual">
        <input type="hidden" id="puedeEditarVariante" value="<?php echo puedeAccion('variantes', 'editar') ? 1 : 0; ?>">
        <input type="hidden" id="puedeEliminarVariante"
          value="<?php echo puedeAccion('variantes', 'eliminar') ? 1 : 0; ?>">
        <?php if (puedeAccion('variantes', 'crear')): ?>
          <button class="btn btn-primary pull-right" data-toggle="modal" data-target="#modalAgregarOpcion">
            <i class="fa fa-plus"></i> Agregar Opción
          </button>
        <?php else: ?>
          <button class="btn btn-primary pull-right" disabled style="opacity: 0.5; cursor: not-allowed;" title="No tiene permisos para crear variantes">
            <i class="fa fa-plus"></i> Agregar Opción
          </button>
        <?php endif; ?>
      </div>

      <div class="box-body">
        <div class="tabla-opciones table-responsive">
          <table id="tablaOpciones" class="table table-bordered table-striped display nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Productos</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="bodyOpciones">
              <!-- Se carga dinámicamente con AJAX -->
            </tbody>
          </table>
        </div> <!-- /.tabla-opciones -->
      </div>

    </div>

  </section>

</div>

<!-- =====================================
MODAL AGREGAR TIPO DE VARIANTE
====================================== -->

<div id="modalAgregarTipoVariante" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formAgregarTipoVariante">

        <?php CSRF::insertToken(); ?>

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Tipo de Variante</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                <input type="text" class="form-control input-lg" name="nuevoTipoVariante" id="nuevoTipoVariante"
                  placeholder="Ingresar nombre (ej: Color, Talla, Material)" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL ORDEN -->
              <input type="hidden" id="nuevoOrdenTipo" name="nuevoOrdenTipo" value="1">


          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>



      </form>

    </div>

  </div>

</div>

<!-- =====================================
MODAL AGREGAR OPCIÓN
====================================== -->

<div id="modalAgregarOpcion" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formAgregarOpcion">

        <?php CSRF::insertToken(); ?>

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Opción</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ID TIPO VARIANTE (OCULTO) -->
            <input type="hidden" name="idTipoVarianteOpcion" id="idTipoVarianteOpcion">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                <input type="text" class="form-control input-lg" name="nuevaOpcion"
                  placeholder="Nombre de la opción (ej: Rojo, M, Algodón)" required>
              </div>
            </div>


              <input type="hidden" id="nuevoOrdenOpcion" name="nuevoOrdenOpcion" value="1">


          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>



      </form>

    </div>

  </div>

</div>




<!-- =====================================
MODAL EDITAR TIPO DE VARIANTE
====================================== -->

<div id="modalEditarTipoVariante" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formEditarTipoVariante">

        <?php CSRF::insertToken(); ?>

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Tipo de Variante</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span>
                <input type="text" class="form-control input-lg" id="editarTipoVariante" name="editarTipoVariante"
                  required>
                <input type="hidden" id="idTipo" name="idTipo">
              </div>
            </div>

              <input type="hidden" id="editarOrdenTipo" name="editarOrdenTipo">


          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>



      </form>

    </div>

  </div>

</div>


<!-- =====================================
MODAL EDITAR OPCIÓN
====================================== -->

<div id="modalEditarOpcion" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formEditarOpcion">

        <?php CSRF::insertToken(); ?>

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Opción</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">

          <div class="box-body">

            <!-- ID OPCION (OCULTO) -->
            <input type="hidden" id="idOpcion" name="idOpcion">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                <input type="text" class="form-control input-lg" id="editarOpcion" name="editarOpcion" required>
              </div>
            </div>

              <input type="hidden" id="editarOrdenOpcion" name="editarOrdenOpcion">


          </div>

        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>



      </form>

    </div>

  </div>

</div>