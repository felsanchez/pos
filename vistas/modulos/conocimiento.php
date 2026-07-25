<?php
if ($_SESSION["perfil"] !== "Administrador" && $_SESSION["perfil"] !== "_SystemMaster_") {
	echo '<script>window.location = "inicio";</script>';
	return;
}
?>

<style>
  @media (max-width: 767px) {
    .box-header .pull-right.form-filtros-conocimiento {
      float: none !important;
      width: 100% !important;
      margin-top: 15px !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: space-between !important;
    }
    .form-filtros-conocimiento > span {
      min-width: 80px !important;
      text-align: left !important;
    }
    .form-filtros-conocimiento > .input-group {
      flex: 1 !important;
      width: auto !important;
    }
  }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Base de Conocimiento
      <small>Artículos y Capacitación</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Conocimiento</li>
    </ol>
  </section>

  <section class="content">

    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4><i class="icon fa fa-warning"></i> ¡Atención!</h4>
      La información almacenada en este módulo será utilizada por el Agente de Inteligencia Artificial para resolver dudas, preguntas e inquietudes de tus clientes.
    </div>

    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarArticulo">
          <i class="fa fa-plus"></i> Agregar Artículo
        </button>
        <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarCategorias">
          <i class="fa fa-tags"></i> Gestionar Categorías
        </button>

        <!-- Filtro rápido por Categoría -->
        <div class="pull-right form-filtros-conocimiento" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span><b>Categoría:</b></span>
          <div class="input-group" style="width: 220px;">
            <span class="input-group-addon" style="background: #fcfcfc; border-color: #d2d6de;">
              <i class="fa fa-search text-primary"></i>
            </span>
            <select class="form-control select2" id="filtroCategoriaArticulo" style="width: 100%;">
              <option value="">Todas las categorías</option>
              <?php
              $categorias = ControladorConocimiento::ctrMostrarCategorias(null, null);
              foreach ($categorias as $cat) {
                if ($cat["estado"] == 1) {
                  echo '<option value="' . $cat["id"] . '">' . e($cat["nombre"]) . '</option>';
                }
              }
              ?>
            </select>
          </div>
        </div>
      </div>

      <div class="box-body">
        <div class="table-responsive">
          <table id="tablaArticulosConocimiento" class="table table-bordered table-striped display nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th>Título</th>
                <th>Categoría</th>
                <th>Palabras Clave</th>
                <th>Fecha de Creación</th>
                <th style="width: 120px">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- DataTables Server-Side -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<!--=====================================
MODAL VER ARTICULO
======================================-->
<div id="modalVerArticulo" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="verArticuloTitulo">Título del Artículo</h4>
      </div>
      <div class="modal-body">
        <div style="margin-bottom: 15px;">
          <span class="label label-primary" id="verArticuloCategoria" style="font-size: 12px; padding: 5px 10px;">Categoría</span>
          <span style="margin-left: 10px; color: #777;" id="verArticuloFecha">Fecha</span>
        </div>
        <hr style="margin-top: 5px; margin-bottom: 20px;">
        <div id="verArticuloContenido" style="font-size: 15px; line-height: 1.6; color: #333; min-height: 150px; overflow-y: auto;">
          <!-- Contenido enriquecido -->
        </div>
        <div id="verArticuloKeywordsContainer" style="margin-top: 30px; display: none;">
          <hr>
          <b>Etiquetas / Palabras clave:</b> <span id="verArticuloKeywords"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!--=====================================
MODAL AGREGAR ARTICULO
======================================-->
<div id="modalAgregarArticulo" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post" id="formAgregarArticulo">
        <?php CSRF::insertToken(); ?>

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Artículo de Conocimiento</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <!-- Título -->
            <div class="form-group">
              <label for="nuevoArticuloTitulo">Título del Artículo *</label>
              <div class="input-group" style="width: 100%;">
                <span class="input-group-addon" style="width: 40px;"><i class="fa fa-bookmark"></i></span>
                <input type="text" class="form-control" name="nuevoArticuloTitulo" id="nuevoArticuloTitulo" placeholder="Ingresar título del artículo" required>
              </div>
            </div>

            <div class="row">
              <!-- Categoría -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="nuevoArticuloCategoria">Categoría *</label>
                  <div class="input-group" style="width: 100%;">
                    <span class="input-group-addon" style="width: 40px;"><i class="fa fa-tags"></i></span>
                    <select class="form-control" name="nuevoArticuloCategoria" id="nuevoArticuloCategoria" required>
                      <option value="">Seleccionar categoría</option>
                      <?php
                      foreach ($categorias as $cat) {
                        if ($cat["estado"] == 1) {
                          echo '<option value="' . $cat["id"] . '">' . e($cat["nombre"]) . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Palabras Clave -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="nuevoArticuloKeywords">Palabras clave / Etiquetas</label>
                  <div class="input-group" style="width: 100%;">
                    <span class="input-group-addon" style="width: 40px;"><i class="fa fa-key"></i></span>
                    <input type="text" class="form-control" name="nuevoArticuloKeywords" id="nuevoArticuloKeywords" placeholder="Ej: ventas, facturación, ticket (separadas por coma)">
                  </div>
                </div>
              </div>
            </div>

            <!-- Contenido (CKEditor) -->
            <div class="form-group">
              <label for="nuevoArticuloContenido">Contenido del Artículo *</label>
              <textarea class="form-control" name="nuevoArticuloContenido" id="nuevoArticuloContenido" rows="10" required></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Artículo</button>
        </div>

        <?php
        $crearArticulo = new ControladorConocimiento();
        $crearArticulo->ctrCrearArticulo();
        ?>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR ARTICULO
======================================-->
<div id="modalEditarArticulo" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post" id="formEditarArticulo">
        <?php CSRF::insertToken(); ?>

        <div class="modal-header" style="background:#f39c12; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Artículo de Conocimiento</h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            
            <input type="hidden" name="idArticulo" id="idArticulo">

            <!-- Título -->
            <div class="form-group">
              <label for="editarArticuloTitulo">Título del Artículo *</label>
              <div class="input-group" style="width: 100%;">
                <span class="input-group-addon" style="width: 40px;"><i class="fa fa-bookmark"></i></span>
                <input type="text" class="form-control" name="editarArticuloTitulo" id="editarArticuloTitulo" placeholder="Editar título del artículo" required>
              </div>
            </div>

            <div class="row">
              <!-- Categoría -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editarArticuloCategoria">Categoría *</label>
                  <div class="input-group" style="width: 100%;">
                    <span class="input-group-addon" style="width: 40px;"><i class="fa fa-tags"></i></span>
                    <select class="form-control" name="editarArticuloCategoria" id="editarArticuloCategoria" required>
                      <option value="">Seleccionar categoría</option>
                      <?php
                      foreach ($categorias as $cat) {
                        if ($cat["estado"] == 1) {
                          echo '<option value="' . $cat["id"] . '">' . e($cat["nombre"]) . '</option>';
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Palabras Clave -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editarArticuloKeywords">Palabras clave / Etiquetas</label>
                  <div class="input-group" style="width: 100%;">
                    <span class="input-group-addon" style="width: 40px;"><i class="fa fa-key"></i></span>
                    <input type="text" class="form-control" name="editarArticuloKeywords" id="editarArticuloKeywords" placeholder="Ej: ventas, facturación, ticket (separadas por coma)">
                  </div>
                </div>
              </div>
            </div>

            <!-- Contenido (CKEditor) -->
            <div class="form-group">
              <label for="editarArticuloContenido">Contenido del Artículo *</label>
              <textarea class="form-control" name="editarArticuloContenido" id="editarArticuloContenido" rows="10" required></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-warning" style="color: white !important;">Guardar Cambios</button>
        </div>

        <?php
        $editarArt = new ControladorConocimiento();
        $editarArt->ctrEditarArticulo();
        ?>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL GESTIONAR CATEGORIAS
======================================-->
<div id="modalGestionarCategorias" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      
      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-tags"></i> Gestionar Categorías de Conocimiento</h4>
      </div>

      <div class="modal-body">
        
        <!-- Formulario para agregar una nueva categoría -->
        <form role="form" method="post" id="formAgregarCategoria">
          <?php CSRF::insertToken(); ?>
          <div class="row" style="margin-bottom: 20px;">
            <div class="col-xs-8">
              <input type="text" class="form-control" name="nuevaCategoriaNombre" id="nuevaCategoriaNombre" placeholder="Nueva categoría de conocimiento" required>
            </div>
            <div class="col-xs-4">
              <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Crear</button>
            </div>
          </div>
          <?php
          $crearCat = new ControladorConocimiento();
          $crearCat->ctrCrearCategoria();
          ?>
        </form>



        <hr>

        <!-- Listado de categorías en formato tabla -->
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Categoría</th>
                <th>Estado</th>
                <th style="width: 90px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $allCategorias = ControladorConocimiento::ctrMostrarCategorias(null, null);
              foreach ($allCategorias as $cat) {
                echo '<tr>
                  <td>' . e($cat["nombre"]) . '</td>';
                
                // Toggle de estado
                echo '<td>';
                if ($cat["estado"] == 1) {
                  echo '<button class="btn btn-success btn-xs btnActivarCat" idCat="' . $cat["id"] . '" estadoCat="0">Activo</button>';
                } else {
                  echo '<button class="btn btn-danger btn-xs btnActivarCat" idCat="' . $cat["id"] . '" estadoCat="1">Inactivo</button>';
                }
                echo '</td>';

                // Acciones editar / eliminar
                echo '<td>
                  <div class="btn-group">
                    <button class="btn btn-warning btn-xs btnEditarCat" idCat="' . $cat["id"] . '" nombreCat="' . e($cat["nombre"]) . '" title="Editar"><i class="fa fa-pencil"></i></button>
                    <button class="btn btn-danger btn-xs btnEliminarCat" idCat="' . $cat["id"] . '" title="Eliminar"><i class="fa fa-times"></i></button>
                  </div>
                </td>
                </tr>';
              }
              ?>
            </tbody>
          </table>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload();">Cerrar</button>
      </div>

    </div>
  </div>
</div>


<!--=====================================
MODAL EDITAR CATEGORIA
======================================-->
<div id="modalEditarCategoria" class="modal fade" role="dialog" data-backdrop="true" data-keyboard="true" style="z-index: 1060 !important;">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <?php CSRF::insertToken(); ?>

        <!-- CABEZA DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-tags"></i> Editar Categoría</h4>
        </div>

        <!-- CUERPO DEL MODAL -->
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group">
              <label>Nombre de la Categoría *</label>
              <input type="text" class="form-control" name="editarCategoriaNombre" id="editarCategoriaNombre" required>
              <input type="hidden" name="idCategoria" id="idCategoria">
            </div>
          </div>
        </div>

        <!-- PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

        <?php
        $editarCat = new ControladorConocimiento();
        $editarCat->ctrEditarCategoria();
        ?>
      </form>
    </div>
  </div>
</div>





