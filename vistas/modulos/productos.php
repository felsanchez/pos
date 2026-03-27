<!-- Estilos para el filtro de categoría -->
<style>
  @media (max-width: 767px) {
    .tablaProductos .btn-group .btn {
      padding: 1px 5px;
      font-size: 12px;
      line-height: 1.5;
      border-radius: 3px;
    }

    /* Prevent wrapping in the control column (ID + Icon) */
    .tablaProductos td.control {
      white-space: nowrap;
      vertical-align: middle;
    }
  }
</style>
<style>
  .filtro-categoria-wrapper {
    min-width: 250px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .filtro-categoria-wrapper label {
    font-weight: 400 !important;
    margin-bottom: 0;
  }

  .filtro-categoria {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
  }

  @media (max-width: 767px) {
    .filtro-categoria-wrapper {
      float: none !important;
      justify-content: center !important;
      text-align: center;
      width: 100%;
    }

    .filtro-categoria-wrapper label {
      margin-bottom: 5px;
    }
  }
</style>


<style>
  /* Solo muestra el botón en móvil */
  .solo-movil {
    display: none;
  }

  @media (max-width: 767px) {
    .solo-movil {
      display: inline-block !important;
    }
  }
</style>

<!--Agregar espacio entre los btones en móvil-->
<style>
  @media (max-width: 767px) {
    .solo-movil {
      margin-left: 3px !important;
    }
  }
</style>


<!-- Fix para modal de importación -->
<style>
  /* Forzar z-index del modal por encima del backdrop */

  #modalImportarProductos {
    z-index: 10050 !important;
    opacity: 1 !important;
  }

  #modalImportarProductos .modal-dialog {
    z-index: 10051 !important;
  }

  #modalImportarProductos .modal-content {
    opacity: 1 !important;
  }

  /* Ajustar z-index del backdrop debajo del modal */
  #modalImportarProductos~.modal-backdrop,
  .modal-backdrop.in {
    z-index: 10040 !important;
  }

  /* Asegurar que el modal sea completamente visible */
  #modalImportarProductos .modal-body,
  #modalImportarProductos .modal-header,
  #modalImportarProductos .modal-footer {
    opacity: 1 !important;
  }
</style>


<!-- Fix para modal de agregar producto -->
<style>
  /* Forzar z-index del modal por encima del backdrop */
  #modalAgregarProducto {
    z-index: 10050 !important;
    opacity: 1 !important;
  }

  #modalAgregarProducto .modal-dialog {
    z-index: 10051 !important;
  }

  #modalAgregarProducto .modal-content {
    opacity: 1 !important;
  }

  /* Ajustar z-index del backdrop debajo del modal */
  #modalAgregarProducto~.modal-backdrop,
  .modal-backdrop.in {
    z-index: 10040 !important;
  }

  /* Asegurar que el modal sea completamente visible */
  #modalAgregarProducto.in {
    opacity: 1 !important;
  }
</style>

<!-- Fix para modal de editar variante -->
<style>
  /* Forzar z-index del modal por encima del backdrop */
  #modalEditarVariante {
    z-index: 10060 !important;
    opacity: 1 !important;
  }

  #modalEditarVariante .modal-dialog {
    z-index: 10061 !important;
  }

  #modalEditarVariante .modal-content {
    opacity: 1 !important;
  }

  /* Asegurar que el modal sea completamente visible */
  #modalEditarVariante .modal-body,
  #modalEditarVariante .modal-header,
  #modalEditarVariante .modal-footer {
    opacity: 1 !important;
  }

  /* Asegurar que los inputs sean visibles y funcionales */
  #modalEditarVariante input {
    pointer-events: auto !important;
  }
</style>

<!-- Fix para modal de ajuste de stock -->
<style>
  /* Forzar z-index del modal por encima del backdrop */
  #modalAjusteStock {
    z-index: 10050 !important;
    opacity: 1 !important;
  }

  #modalAjusteStock .modal-dialog {
    z-index: 10051 !important;
  }

  #modalAjusteStock .modal-content {
    opacity: 1 !important;
  }

  /* Ajustar z-index del backdrop debajo del modal */
  #modalAjusteStock~.modal-backdrop,
  .modal-backdrop.in {
    z-index: 10040 !important;
  }

  /* Asegurar que el modal sea completamente visible */
  #modalAjusteStock.in {
    opacity: 1 !important;
  }
</style>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$tipoCodigoProducto = !empty($configuracion["tipo_codigo_producto"]) ? $configuracion["tipo_codigo_producto"] : "automatico";
?>

<script>
  // Variable global con la configuración del tipo de código de producto
  var tipoCodigoProducto = "<?php echo $tipoCodigoProducto; ?>";
</script>

<div class="content-wrapper">
  <section class="content-header">

    <h1>
      Administrar productos
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar productos</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <?php if(puedeAccion('productos', 'crear')): ?>
        <a href="producto-detalle" class="btn btn-primary">

          <i class="fa fa-plus"></i> Agregar producto

        </a>

        <button class="btn btn-success" data-toggle="modal" data-target="#modalImportarProductos">
          <i class="fa fa-upload"></i> Exportar / Importar Productos
        </button>
        <?php endif; ?>
      </div>


      <!--CODIGO PARA LLAMAR AL WEBHOOK DE n8n -->
      <form id="formN8N"
        action="https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/ed25e621-dcc5-45c0-918c-5ec3c9ecbdc3" method="POST">

        <?php CSRF::insertToken(); ?>
        <input type="hidden" name="origen" value="productos">
        <button type="submit" class="btn btn-success">Actualizar</button>
      </form>


      <!-- Filtros -->
      <div class="box-body">
        <div class="clearfix mb-2">

          <!-- Filtro Categoría -->
          <div class="pull-right filtro-categoria-wrapper" style="margin-left: 10px;">
            <label for="filtroCategoria" class="control-label">Categoría:</label>
            <select id="filtroCategoria" class="form-control filtro-categoria">
              <option value="">Todas</option>
              <?php
$categoriasFiltro = ControladorCategorias::ctrMostrarCategorias(null, null);
foreach ($categoriasFiltro as $categoriaFiltro) {
  echo '<option value="' . e($categoriaFiltro["categoria"]) . '">' . e(ucfirst($categoriaFiltro["categoria"])) . '</option>';
}
?>
            </select>
          </div>

          <!-- Filtro Proveedor -->
          <div class="pull-right filtro-categoria-wrapper">
            <label for="filtroProveedor" class="control-label">Proveedor:</label>
            <select id="filtroProveedor" class="form-control filtro-categoria">
              <option value="">Todos</option>
              <?php
$proveedoresFiltro = ControladorProveedores::ctrMostrarProveedores(null, null);
foreach ($proveedoresFiltro as $proveedorFiltro) {
  echo '<option value="' . e($proveedorFiltro["nombre"]) . '">' . e(ucfirst($proveedorFiltro["nombre"])) . '</option>';
}
?>
            </select>
          </div>

        </div>
        <br>
      </div>


      <div class="box-body table-responsive">

        <table class="table table-bordered table-striped dt-responsive tablaProductos" style="width: 100%">

          <thead>
            <tr>
              <th style="width: 5px">#</th>
              <th>Imagen</th>
              <th>Código</th>
              <th>Descripción</th>
              <th>Categoría</th>
              <th>Stock</th>
              <th>Impuesto</th>
              <th>Precio de Venta</th>
              <th>Proveedor</th>
              <th>Agregado</th>
              <th>Acciones</th>
            </tr>
          </thead>

        </table>

        <input type="hidden" value="<?php echo $_SESSION['perfil']; ?>" class="perfilUsuario" id="perfilOculto">

      </div>

    </div>

  </section>

</div>


<!-- Modal CUSTOM para ampliar/editar imagen de producto -->
<div id="modalAmpliarImagenProducto" style="display: none;">
  <!-- Backdrop -->
  <div id="modalProductoBackdrop"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1040;">
  </div>

  <!-- Contenedor del Modal -->
  <div
    style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1050; width: 90%; max-width: 800px; max-height: 90vh; background: white; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); display: flex; flex-direction: column;">

    <!-- Header -->
    <div style="background: #3c8dbc; color: white; padding: 15px; border-radius: 5px 5px 0 0; flex-shrink: 0;">
      <button type="button" class="close" id="btnCerrarModalProducto"
        style="color: white; opacity: 1; font-size: 28px; font-weight: 300; float: right; background: none; border: none; cursor: pointer;">
        <span>&times;</span>
      </button>
      <h4 style="margin: 0; padding-right: 30px;"><i class="fa fa-camera"></i> Imagen del Producto</h4>
    </div>

    <!-- Body con scroll -->
    <div style="padding: 20px; text-align: center; overflow-y: auto; flex: 1 1 auto; min-height: 0;">
      <img id="imagenProductoAmpliada" src="" class="img-responsive"
        style="max-width: 100%; margin: 0 auto 20px auto; border-radius: 5px;">
      <hr>
      <div class="form-group">
        <label><i class="fa fa-upload"></i> Cambiar Imagen del Producto</label>
        <input type="file" class="form-control nuevaImagenProducto" accept="image/*">
        <p class="help-block"><i class="fa fa-info-circle"></i> Peso máximo de la imagen 2MB</p>
      </div>
      <input type="hidden" id="idProductoImagen">
      <input type="hidden" id="codigoProductoImagen">
    </div>

    <!-- Footer sticky -->
    <div
      style="background: #f4f4f4; padding: 15px; border-radius: 0 0 5px 5px; text-align: right; border-top: 1px solid #ddd; flex-shrink: 0;">
      <button type="button" class="btn btn-default" id="btnCancelarModalProducto">
        <i class="fa fa-times"></i> Cancelar
      </button>
      <button type="button" class="btn btn-primary btnGuardarImagenProducto">
        <i class="fa fa-save"></i> Guardar Imagen
      </button>
    </div>

  </div>
</div>


<!--=====================================
MODAL AGREGAR PRODUCTO
======================================-->

<!-- Modal -->
<div id="modalAgregarProducto" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>
<!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar producto</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para seleccionar categoria -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>

                <select class="form-control input-lg" id="nuevaCategoria" name="nuevaCategoria" required>

                  <option value="">Seleccionar categoría</option>

                  <?php

$item = null;
$valor = null;
$categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

foreach ($categorias as $key => $value) {

  echo '<option value="' . e($value["id"]) . '">' . e($value["categoria"]) . '</option>';
}

?>

                </select>

              </div>

            </div>

            <!-- entrada para el codigo -->

            <div class="form-group">

              <div class="input-group col-xs-5">

                <span class="input-group-addon"><i class="fa fa-code"></i></span>

                <input type="text" class="form-control input-lg" id="nuevoCodigo" name="nuevoCodigo"
                  placeholder="<?php echo $tipoCodigoProducto == 'manual' ? 'Ingrese el código' : 'Código'; ?>" <?php echo $tipoCodigoProducto == 'automatico' ? 'readonly' : ''; ?> required>

              </div>

            </div>



            <!-- entrada para la descripcion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                <input type="text" class="form-control input-lg" name="nuevaDescripcion" id="nuevaDescripcion"
                  placeholder="Ingresar descripción" required>

              </div>

            </div>


            <!-- entrada para el stock -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-check"></i></span>

                <input type="number" class="form-control input-lg" name="nuevoStock" min="0" placeholder="Stock"
                  required>

              </div>

              <p class="help-block" id="helpStockProducto">Stock disponible del producto.</p>
              <p class="help-block text-info" id="helpStockVariantes" style="display:none;"><i
                  class="fa fa-info-circle"></i> <strong>El stock se calculará automáticamente</strong> como la suma de
                todas las variantes.</p>

            </div>


            <!-- entrada para seleccionar proveedor -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-users"></i></span>

                <select class="form-control input-lg" id="nuevoProveedor" name="nuevoProveedor">

                  <option value="0" selected>Sin proveedor</option>

                  <?php

$item = null;
$valor = null;
$proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);

if ($proveedores) {
  foreach ($proveedores as $key => $value) {
    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
  }
}

?>

                </select>

              </div>

            </div>


            <!-- entrada para el precio de compra -->

            <div class="form-group row">

              <div class="col-xs-6">

                <div class="input-group">

                  <span class="input-group-addon"><i class="fa fa-arrow-up"></i></span>

                  <input type="number" class="form-control input-lg" id="nuevoPrecioCompra" name="nuevoPrecioCompra"
                    min="0" placeholder="Precio de Compra" required>

                </div>

                <p class="help-block">Precio base. Las variantes pueden tener un precio adicional.</p>

              </div>

              <!-- entrada para el precio de venta -->

              <div class="col-xs-6">

                <div class="input-group">

                  <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span>

                  <input type="number" class="form-control input-lg" id="nuevoPrecioVenta" name="nuevoPrecioVenta"
                    min="0" placeholder="Precio de Venta" required>

                </div>

                <br>

                <!-- checkbox para porcentaje -->

                <div class="col-xs-6">

                  <div class="form-group">

                    <label>

                      <input type="checkbox" class="minimal porcentaje" checked>
                      Utilizar porcentaje

                    </label>

                  </div>

                </div>

                <!-- entrada para porcentaje -->

                <div class="col-xs-6" style="padding:0">

                  <div class="input-group">

                    <input type="number" class="form-control input-lg nuevoPorcentaje" min="0" value="40" required>

                    <span class="input-group-addon"><i class="fa fa-percent"></i></span>

                  </div>

                </div>

              </div>

            </div>


            <!-- entrada para imagen -->

            <div class="form-group">

              <div class="panel">SUBIR IMAGEN</div>

              <input type="file" class="nuevaImagen" name="nuevaImagen">

              <p class="help-block">Peso máximo de la imagen 2MB</p>

              <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">

            </div>

            <!-- TOGGLE PARA VARIANTES -->

            <div class="form-group">

              <label>
                <input type="checkbox" class="minimal" id="checkTieneVariantes" name="tieneVariantes">
                ¿Este producto tiene variantes? (Ej: Colores, Tallas, etc.)
              </label>
            </div>

            <!-- CONTENEDOR DE VARIANTES (Oculto inicialmente) -->
            <div id="contenedorVariantes"
              style="display:none; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9;">

              <h4 style="margin-top: 0;">Configuración de Variantes</h4>

              <!-- Tipos de variantes disponibles -->

              <div class="form-group">

                <label>Selecciona los tipos de variantes:</label>

                <div id="tiposVariantesContainer">

                  <!-- Se cargará dinámicamente con AJAX -->

                  <p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando tipos de variantes...</p>

                </div>

              </div>

              <!-- Contenedor para opciones de cada tipo seleccionado -->

              <div id="opcionesVariantesContainer" style="display:none;">

                <!-- Se cargará dinámicamente cuando se seleccionen tipos -->

              </div>

              <!-- Contenedor para las combinaciones finales -->

              <div id="combinacionesContainer" style="display:none;">

                <hr>

                <h4>Variantes a crear:</h4>

                <p class="text-muted">Selecciona las combinaciones que deseas crear y configura su precio/stock:</p>

                <div id="listaCombinaciones">

                  <!-- Se generará dinámicamente -->

                </div>

              </div>

            </div>

          </div>

          <!--=====================================
        PIE DEL MODAL
        ======================================-->

          <div class="modal-footer">

            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
            <button type="submit" class="btn btn-primary">Guardar producto</button>

          </div>

      </form>


      <?php

$crearProducto = new ControladorProductos();
$crearProducto->ctrCrearProducto();

?>

    </div>


  </div>

</div>



<!--==========================================================================
MODAL EDITAR PRODUCTO
============================================================================-->

<!-- Modal -->
<div id="modalEditarProducto" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar producto</h4>

        </div>

        <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- entrada para el codigo -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span>

                <input type="text" class="form-control input-lg" id="editarCodigo" name="editarCodigo" readonly
                  required>

              </div>

            </div>

            <!-- entrada para seleccionar categoria -->
            <!--
              <div class="form-group">
                <div class="input-group">
                   <span class="input-group-addon"><i class="fa fa-th"></i></span>
                     <select class="form-control input-lg" name="editarCategoria" readonly required>
                      <option id="editarCategoria"></option>
                      </select>
                 </div>
              </div>
              -->


            <!-- entrada para seleccionar categoria -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>

                <select class="form-control input-lg" id="editarCategoria" name="editarCategoria">

                  <option value="">Editar Categoria</option>

                  <?php
$item = null;
$valor = null;
$categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

foreach ($categorias as $key => $value) {

  $selected = ($producto["id_categoria"] == $value["id"]) ? "selected" : "";

  echo '<option value="' . $value["id"] . '" ' . $selected . '>' . $value["categoria"] . '</option>';
}
?>

                </select>

              </div>
            </div>


            <!-- entrada para la descripcion -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span>

                <input type="text" class="form-control input-lg" id="editarDescripcion" name="editarDescripcion"
                  required>

              </div>

            </div>


            <!-- entrada para el stock -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-check"></i></span>

                <input type="number" class="form-control input-lg" id="editarStock" name="editarStock" min="0" required>

              </div>

            </div>


            <!-- entrada para el proveedor -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-users"></i></span>

                <select class="form-control input-lg" id="editarProveedor" name="editarProveedor">
                  <option value="0">Sin proveedor</option>

                  <?php
$item = null;
$valor = null;
$proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);

if ($proveedores) {
  foreach ($proveedores as $key => $value) {
    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
  }
}
?>
                </select>

              </div>

            </div>


            <!-- entrada para el precio de compra -->

            <div class="form-group row">

              <div class="col-xs-6">

                <div class="input-group">

                  <span class="input-group-addon"><i class="fa fa-arrow-up"></i></span>

                  <input type="number" class="form-control input-lg" id="editarPrecioCompra" name="editarPrecioCompra"
                    min="0" required>

                </div>

              </div>

              <!-- entrada para el precio de venta -->

              <div class="col-xs-6">

                <div class="input-group">

                  <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span>

                  <input type="number" class="form-control input-lg" id="editarPrecioVenta" name="editarPrecioVenta"
                    min="0" readonly required>

                </div>

                <br>

                <!-- checkbox para porcentaje -->

                <div class="col-xs-6">

                  <div class="form-group">

                    <label>

                      <input type="checkbox" class="minimal porcentaje" checked>
                      Utilizar porcentaje

                    </label>

                  </div>

                </div>

                <!-- entrada para porcentaje -->

                <div class="col-xs-6" style="padding:0">

                  <div class="input-group">

                    <input type="number" class="form-control input-lg nuevoPorcentaje" min="0" value="40" required>

                    <span class="input-group-addon"><i class="fa fa-percent"></i></span>

                  </div>

                </div>

              </div>

            </div>


            <!-- entrada para imagen -->

            <!--
           <div class="form-group">                    
              <div class="panel">SUBIR IMAGEN</div>
                 <input type="file" class="nuevaImagen" name="editarImagen">
                 <p class="help-block">Peso máximo de la imagen 2MB</p>
                 <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
                 <input type="hidden" name="imagenActual" id="imagenActual">
              </div>
           </div>
           -->

            <!-- abrir imagen con modal-->
            <div class="form-group">

              <div class="panel">SUBIR IMAGEN</div>

              <input type="file" class="nuevaImagen" name="editarImagen">

              <p class="help-block">Peso máximo de la imagen 2MB</p>

              <img src="vistas/img/productos/default/anonymous.png"
                class="img-thumbnail previsualizar img-ampliar-producto-modal" width="100px" style="cursor: pointer;">

              <input type="hidden" name="imagenActual" id="imagenActual">

            </div>


            <!-- TOGGLE PARA AGREGAR NUEVAS VARIANTES -->

            <div class="form-group">

              <label>

                <input type="checkbox" class="minimal" id="checkAgregarVariantes" name="agregarVariantes">

                ¿Desea agregar nuevas variantes? (Ej: Colores, Tallas, etc.)

              </label>

              <p class="help-block">Las variantes existentes se editan desde la tabla de variantes. Aquí solo puedes
                agregar nuevas.</p>

            </div>

            <!-- CONTENEDOR DE VARIANTES (Oculto inicialmente) -->

            <div id="contenedorAgregarVariantes"
              style="display:none; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9;">

              <h4 style="margin-top: 0;">Configuración de Nuevas Variantes</h4>

              <!-- Tipos de variantes disponibles -->

              <div class="form-group">

                <label>Selecciona los tipos de variantes:</label>

                <div id="tiposVariantesEditarContainer">

                  <!-- Se cargará dinámicamente con AJAX -->

                  <p class="text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando tipos de variantes...</p>

                </div>

              </div>

              <!-- Contenedor para opciones de cada tipo seleccionado -->

              <div id="opcionesVariantesEditarContainer" style="display:none;">

                <!-- Se cargará dinámicamente cuando se seleccionen tipos -->

              </div>

              <!-- Contenedor para las combinaciones finales -->

              <div id="combinacionesEditarContainer" style="display:none;">

                <hr>

                <h4>Variantes a crear:</h4>

                <p class="text-muted">Selecciona las combinaciones que deseas crear y configura su precio/stock:</p>

                <div id="listaCombinacionesEditar">

                  <!-- Se generará dinámicamente -->

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

      </form>


      <?php
$editarProducto = new ControladorProductos();
$editarProducto->ctrEditarProducto();
?>

    </div>

  </div>

</div>



<!-- Modal para ampliar imagen de producto desde la tabla-->
<div class="modal fade" id="modalAmpliarFotoProducto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Imagen del Producto</h4>
      </div>
      <div class="modal-body text-center">
        <img id="fotoProductoAmpliada" src="" class="img-responsive" style="max-width: 100%; margin: 0 auto;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<!--=============================================
MODAL EDITAR VARIANTE
=============================================-->

<div id="modalEditarVariante" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" id="formEditarVariante">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar Variante</h4>

        </div>

        <!--====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA ID -->

            <input type="hidden" id="idVariante" name="idVariante">

            <!-- ENTRADA PARA PRECIO ADICIONAL -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-dollar"></i></span>

                <input type="number" class="form-control input-lg" id="editarPrecioAdicionalVariante"
                  name="editarPrecioAdicionalVariante" step="0.01" placeholder="Precio adicional" required>

              </div>

            </div>


            <!-- ENTRADA PARA STOCK -->

            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-cubes"></i></span>

                <input type="number" class="form-control input-lg" id="editarStockVariante" name="editarStockVariante"
                  min="0" placeholder="Stock" required>

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

      </form>

    </div>

  </div>

</div>


<!--=====================================
MODAL IMPORTAR PRODUCTOS DESDE CSV
======================================-->

<div id="modalImportarProductos" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Importar Productos desde CSV</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="alert alert-info">

              <h4><i class="icon fa fa-info"></i> Instrucciones:</h4>

              <ol>
                <li>Descarga la plantilla CSV haciendo clic en el botón de abajo</li>
                <li>Completa los datos de los productos (Todos los campos son obligatorios)</li>
                <li>El campo <strong>proveedor es opcional</strong></li>
                <li>Asegúrate de que las categorías existan en el sistema</li>
                <li>Sube el archivo CSV completado</li>
              </ol>

            </div>


            <!-- BOTÓN PARA DESCARGAR PLANTILLA -->

            <div class="form-group text-center">

              <a href="vistas/modulos/descargar-plantilla-productos.php" class="btn btn-info">

                <i class="fa fa-download"></i> Descargar Plantilla CSV

              </a>

            </div>

            <hr>

            <!-- ENTRADA PARA SUBIR ARCHIVO CSV -->

            <div class="form-group">

              <label>Seleccionar archivo CSV:</label>

              <input type="file" class="form-control" name="archivoCSV" accept=".csv" required>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

          <button type="submit" class="btn btn-success">Importar Productos</button>

        </div>

        <?php
$importar = new ControladorProductos();
$importar->ctrImportarProductos();
?>

      </form>

    </div>

  </div>

</div>

<?php
$eliminarProducto = new ControladorProductos();
$eliminarProducto->ctrEliminarProducto();
?>


<!--=============================================
AMPLIAR Y EDITAR IMAGEN DE PRODUCTO DESDE LA TABLA
=============================================-->
<script>
  // Abrir modal al hacer clic en imagen de producto
  $(document).off("click", ".img-ampliar-producto"); // Evitar eventos duplicados
  $(document).on("click", ".img-ampliar-producto", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var rutaImagen = $(this).attr("data-imagen");
    var idProducto = $(this).attr("data-idproducto");
    var codigo = $(this).closest("tr").find("td:nth-child(3)").text();

    console.log("ID Producto:", idProducto);
    console.log("Código:", codigo);
    console.log("Ruta Imagen:", rutaImagen);

    // Preparar datos del modal
    $("#imagenProductoAmpliada").attr("src", rutaImagen);
    $("#idProductoImagen").val(idProducto);
    $("#codigoProductoImagen").val(codigo);
    $(".nuevaImagenProducto").val("");

    // Mostrar modal personalizado
    $("#modalAmpliarImagenProducto").fadeIn(300);
    $('body').css('overflow', 'hidden');
  });

  // Cerrar modal con botón X
  $(document).on("click", "#btnCerrarModalProducto", function () {
    $("#modalAmpliarImagenProducto").fadeOut(300);
    $('body').css('overflow', '');
  });

  // Cerrar modal con botón Cancelar
  $(document).on("click", "#btnCancelarModalProducto", function () {
    $("#modalAmpliarImagenProducto").fadeOut(300);
    $('body').css('overflow', '');
  });

  // Cerrar modal al hacer clic en el backdrop
  $(document).on("click", "#modalProductoBackdrop", function () {
    $("#modalAmpliarImagenProducto").fadeOut(300);
    $('body').css('overflow', '');
  });

  // Cerrar con tecla ESC
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && $("#modalAmpliarImagenProducto").is(":visible")) {
      $("#modalAmpliarImagenProducto").fadeOut(300);
      $('body').css('overflow', '');
    }
  });

  // Previsualizar nueva imagen cuando se selecciona
  $(".nuevaImagenProducto").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaImagenProducto").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaImagenProducto").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen no debe pesar más de 2MB!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else {
        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function (event) {
          var rutaImagen = event.target.result;
          $("#imagenProductoAmpliada").attr("src", rutaImagen);
        });
      }
    }
  });

  // Guardar la nueva imagen del producto
  $(document).on("click", ".btnGuardarImagenProducto", function () {

    var idProducto = $("#idProductoImagen").val();
    var codigo = $("#codigoProductoImagen").val();
    var imagen = $(".nuevaImagenProducto")[0].files[0];

    console.log("ID al guardar:", idProducto);
    console.log("Código al guardar:", codigo);
    console.log("Imagen al guardar:", imagen);

    if (!imagen) {
      swal({
        title: "Advertencia",
        text: "No has seleccionado ninguna imagen",
        type: "warning",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    if (!idProducto || !codigo) {
      swal({
        title: "Error",
        text: "No se pudo obtener el ID o código del producto",
        type: "error",
        confirmButtonText: "¡Cerrar!"
      });
      return;
    }

    var datos = new FormData();
    datos.append("idProductoImagen", idProducto);
    datos.append("codigoProductoImagen", codigo);
    datos.append("nuevaImagenProducto", imagen);

    console.log("=== ENVIANDO AL SERVIDOR ===");
    console.log("FormData idProductoImagen:", datos.get("idProductoImagen"));
    console.log("FormData codigoProductoImagen:", datos.get("codigoProductoImagen"));
    console.log("FormData nuevaImagenProducto:", datos.get("nuevaImagenProducto"));

    // Mostrar loading
    swal({
      title: 'Cargando...',
      allowOutsideClick: false,
      onBeforeOpen: () => {
        swal.showLoading()
      }
    });

    $.ajax({
      url: "ajax/productos.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (respuesta) {
        console.log("=== RESPUESTA DEL SERVIDOR ===");
        console.log("Respuesta:", respuesta);
        console.log("Tipo de respuesta:", typeof respuesta);

        // La respuesta ahora es un objeto JSON
        if (respuesta && respuesta.status == "ok") {
          swal({
            type: "success",
            title: "¡La imagen ha sido actualizada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
          }).then(function (result) {
            if (result.value) {
              $("#modalAmpliarImagenProducto").fadeOut(300);
              $('body').css('overflow', '');

              // Recargar la tabla de DataTables sin recargar toda la página
              if (typeof tablaProductos !== 'undefined' && tablaProductos) {
                tablaProductos.ajax.reload(null, false); // false = mantener la paginación actual
              } else {
                // Si no existe la tabla, recargar la página completa
                window.location.reload();
              }
            }
          });
        } else {
          // Mostrar el mensaje de error específico del servidor
          swal({
            type: "error",
            title: "Error al actualizar la imagen",
            text: (respuesta && respuesta.message) ? respuesta.message : "Ocurrió un error desconocido.",
            confirmButtonText: "Cerrar"
          });
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log("Error AJAX:", textStatus, errorThrown);
        console.log("Respuesta:", jqXHR.responseText);

        swal({
          type: "error",
          title: "Error en la petición",
          text: "Por favor revisa la consola para más detalles",
          confirmButtonText: "Cerrar"
        });
      }
    });
  });
</script>


<!-- Ampliar Imagen desde Editar Modal -->
<script>
  $(document).on("click", ".img-ampliar-producto-modal", function () {
    var rutaImagen = $(this).attr("src");
    $("#fotoProductoAmpliada").attr("src", rutaImagen);
    $("#modalAmpliarFotoProducto").modal("show");
  });

  $(".nuevaImagen").change(function () {
    var imagen = this.files[0];

    if (imagen) {
      if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
        $(".nuevaImagen").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen debe estar en formato JPG o PNG!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else if (imagen["size"] > 2000000) {
        $(".nuevaImagen").val("");
        swal({
          title: "Error al subir la imagen",
          text: "¡La imagen no debe pesar más de 2MB!",
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
      } else {
        var datosImagen = new FileReader;
        datosImagen.readAsDataURL(imagen);

        $(datosImagen).on("load", function (event) {
          var rutaImagen = event.target.result;
          $(".previsualizar").attr("src", rutaImagen);
        });
      }
    }
  });
</script>




<!--=====================================
MODAL AJUSTE DE STOCK
======================================-->
<div id="modalAjusteStock" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <?php CSRF::insertToken(); ?>
        
        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Ajuste Rápido de Stock</h4>
        </div>
        
        <div class="modal-body">
          <div class="box-body">
            
            <!-- entrada oculta ID -->
            <input type="hidden" id="idProductoAjuste" name="idProductoAjuste" required>

            <!-- entrada tipo de ajuste -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-random"></i></span>
                <select class="form-control input-lg" name="tipoAjuste" required>
                  <option value="aumentar">Aumentar</option>
                  <option value="disminuir">Disminuir</option>
                </select>
              </div>
            </div>

            <!-- entrada cantidad -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                <input type="number" class="form-control input-lg" name="cantidadAjuste" id="cantidadAjuste" min="1" placeholder="Ingresar la cantidad a ajustar" required>
              </div>
            </div>

          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
        
        <?php
          $ajusteStock = new ControladorProductos();
          $ajusteStock->ctrAjusteStockLocal();
        ?>
        
      </form>
    </div>
  </div>
</div>

<!-- Mensaje al actualizar productos a n8n -->
<!--<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('formN8N').addEventListener('submit', function(e) {
  e.preventDefault(); // Evitar el envío tradicional del formulario
  
  // Obtener los datos del formulario
  const formData = new FormData(this);
  
  // Enviar con fetch
  fetch('https://6eddcbd9ed49.ngrok-free.app/webhook/mipos', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    alert('Productos actualizados correctamente');
  })
  .catch(error => {
    alert('Error al actualizar productos');
    console.error('Error:', error);
  });
});
</script>
-->

<!-- Actualizar productos a n8n con SweetAlert -->
<script>
  document.getElementById('formN8N').addEventListener('submit', function (e) {
    e.preventDefault();

    // URL Webhook explícita
    const webhookUrl = "https://demo-ppal-n8n.lhs6l6.easypanel.host/webhook/ed25e621-dcc5-45c0-918c-5ec3c9ecbdc3";

    // Usar URLSearchParams es más seguro para peticiones simples
    const formData = new FormData(this);
    const dataToSend = new URLSearchParams(formData);

    console.log("Intentando enviar a:", webhookUrl);

    // Mostrar loading
    swal({
      title: 'Actualizando productos...',
      text: 'Conectando con n8n...',
      type: 'info',
      showConfirmButton: false,
      allowOutsideClick: false
    });

    fetch(webhookUrl, {
      method: 'POST',
      mode: 'no-cors', // Necesario si el servidor no devuelve headers CORS
      cache: 'no-cache',
      credentials: 'omit', // No enviar cookies
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: dataToSend
    })
      .then(response => {
        // En modo no-cors SIEMPRE entra aquí si la red funciona,
        // aunque el servidor de error 500 o 404.
        console.log('Petición finalizada (respuesta opaca)');

        swal({
          title: '¡Enviado!',
          text: 'La solicitud se ha enviado a n8n correctamente.',
          type: 'success',
          timer: 2000
        });
      })
      .catch(error => {
        console.error('Error FETCH:', error);
        swal({
          title: 'Error de Conexión',
          text: 'No se pudo contactar con el servidor. Revisa tu conexión a internet o si la URL es accesible.',
          type: 'error'
        });
      });
  });
</script>


<script>   /*=============================================   FILTRO DE CATEGORÍA EN PRODUCTOS   =============================================*/

  $(document).ready(function () {
    var tablaProductos = $('.tablaProductos').DataTable();

    // Agregar filtro personalizado a DataTables
    $.fn.dataTable.ext.search.push(
      function (settings, data, dataIndex) {
        // Verificar si es la tabla de productos
        if (settings.nTable.className.indexOf('tablaProductos') === -1) {
          return true;
        }

        var filtroCategoria = $('#filtroCategoria').val().toLowerCase();
        var filtroProveedor = $('#filtroProveedor').val().toLowerCase();

        // Si no hay filtro seleccionado, mostrar todo
        if (filtroCategoria === "" && filtroProveedor === "") {
          return true;
        }

        // La columna 4 (índice 4) es la categoría
        var categoriaTexto = data[4].toLowerCase();
        // La columna 8 (índice 8) es el proveedor
        var proveedorTexto = data[8].toLowerCase();

        // Verificar coincidencia
        var matchCategoria = (filtroCategoria === "" || categoriaTexto.indexOf(filtroCategoria) !== -1);
        var matchProveedor = (filtroProveedor === "" || proveedorTexto.indexOf(filtroProveedor) !== -1);

        return matchCategoria && matchProveedor;
      }
    );

    // Evento al cambiar el filtro
    $('#filtroCategoria, #filtroProveedor').on('change', function () {
      tablaProductos.draw();
    });
  });
</script>


<script>   /*=============================================   FIX MODALES DE PRODUCTOS - Mover al body para evitar bloqueos   =============================================*/

  $(document).ready(function () {
    // SOLUCIÓN: Mover todos los modales al body para evitar problemas de z-index/contenedores

    // Modal de importación
    if ($('#modalImportarProductos').length) {
      $('#modalImportarProductos').appendTo('body');
      console.log('✓ Modal de importación movido al body correctamente');
    }

    // Modal de editar producto
    if ($('#modalEditarProducto').length) {
      $('#modalEditarProducto').appendTo('body');
      console.log('✓ Modal de editar producto movido al body correctamente');
    }

    // Modal de agregar producto
    if ($('#modalAgregarProducto').length) {
      $('#modalAgregarProducto').appendTo('body');
      console.log('✓ Modal de agregar producto movido al body correctamente');
    }

    // Modal de ajuste de stock
    if ($('#modalAjusteStock').length) {
      $('#modalAjusteStock').appendTo('body');
    }

    // Modal de ampliar imagen - YA NO ES NECESARIO, Bootstrap lo maneja automáticamente
    // if ($('#modalAmpliarImagenProducto').length) {
    //   $('#modalAmpliarImagenProducto').appendTo('body');
    //   console.log('✓ Modal de ampliar imagen movido al body correctamente');
    // }

    // Modal de editar variante
    if ($('#modalEditarVariante').length) {
      $('#modalEditarVariante').appendTo('body');
      console.log('✓ Modal de editar variante movido al body correctamente');
    }
  });
</script>