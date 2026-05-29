<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");
?>

<!-- Estilos de gastos -->
<link rel="stylesheet" href="assets/css/gastos.css">


<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Administrar gastos
      <small>Control de egresos</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Gastos</li>
    </ol>
  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <?php if (puedeAccion('gastos', 'crear')): ?>
          <?php if (ControladorCajas::ctrValidarCajaAbierta()): ?>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarGasto">
              <i class="fa fa-plus"></i> Agregar gasto
            </button>
          <?php else: ?>
            <button class="btn btn-primary" onclick="alertaCajaCerradaGastos()">
              <i class="fa fa-plus"></i> Agregar gasto
            </button>
            <script>
            function alertaCajaCerradaGastos(){
                swal({
                    title: '¡Caja Cerrada!',
                    text: 'Debe abrir caja antes de realizar esta operación.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3c8dbc',
                    cancelButtonColor: '#6c757d',
                    cancelButtonText: 'Entendido',
                    confirmButtonText: 'Abrir caja'
                }).then(function(result){
                    if (result.value) {
                        $('#modalAperturaCaja').modal('show');
                    }
                });
            }
            </script>
          <?php endif; ?>
        <?php endif; ?>

        <button class="btn btn-default" data-toggle="modal" data-target="#modalGestionarCategorias">
          <i class="fa fa-tags"></i> Gestionar categorías
        </button>

        <div class="pull-right contenedor-filtros">

          <div id="formFiltrosGastos" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

            <!-- Filtro por Sucursal (Solo Admin) -->
            <?php if ($_SESSION["perfil"] == "Administrador"): ?>
              <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
                <label class="hidden-xs" style="margin-bottom: 0;">Sucursal:</label>
                <div class="input-group">
                  <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                      class="fa fa-building text-primary"></i></span>
                  <select class="form-control select2" id="sucursal_g" style="width: 150px; border-left: 0;">
                    <option value="">Mostrar Todas</option>
                    <?php
                    $sucursales = ControladorBodegas::ctrMostrarBodegas(null, null);
                    foreach ($sucursales as $key => $value) {
                      echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
            <?php endif; ?>

            <!-- Filtro por Categoría -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Categoría:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="cat_g" style="width: 150px; border-left: 0;">
                  <option value="">Mostrar Todas</option>
                  <?php
                  $categorias = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
                  foreach ($categorias as $key => $value) {
                    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por Proveedor -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Proveedor:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="prov_g" style="width: 150px; border-left: 0;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                  foreach ($proveedores as $key => $value) {
                    echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por Fecha -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label class="hidden-xs" style="margin-bottom: 0;">Fecha:</label>
              <button type="button" class="btn btn-default" id="daterange-btn">
                <span>
                  <i class="fa fa-calendar"></i> Rango de fecha
                </span>
                <i class="fa fa-caret-down"></i>
              </button>
              <input type="hidden" id="filtroFechaInicio" name="filtroFechaInicio">
              <input type="hidden" id="filtroFechaFin" name="filtroFechaFin">
            </div>

            <!-- Botones (Separados para mantener gap consistente) -->
            <button type="button" class="btn btn-default" id="btnLimpiarGastos" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </button>

          </div>

        </div>

      </div>


      <div class="box-body">

        <div class="tabla-gastos table-responsive">
          <table id="tablaGastos" class="table table-bordered table-striped display nowrap" width="100%">

            <thead>
              <tr>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Proveedor</th>
                <?php if ($_SESSION["perfil"] == "Administrador"): ?>
                  <th>Sucursal</th>
                <?php endif; ?>
                <th>Imagen</th>
                <th>Fecha</th>
                <th>Notas</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <!-- Los datos se cargarán por DataTables Server-Side -->
            </tbody>

          </table>
        </div>



      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR GASTO
======================================-->

<div id="modalAgregarGasto" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Gasto</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

              <!-- Concepto -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Concepto *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                    <input type="text" class="form-control" name="nuevoConceptoGasto" placeholder="Concepto del gasto"
                      required>
                  </div>
                </div>
              </div>

              <!-- Monto -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Monto *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                    <input type="number" class="form-control" name="nuevoMontoGasto" placeholder="0" min="0" step="0.01"
                      required>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Fecha -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Fecha *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="nuevaFechaGasto" value="<?php echo date('Y-m-d'); ?>"
                      required>
                  </div>
                </div>
              </div>

              <!-- Categoría -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Categoría *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <select class="form-control" name="nuevaCategoriaGasto" required>
                      <option value="">Seleccionar categoría</option>
                      <?php
                      $categorias = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
                      foreach ($categorias as $key => $value) {
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Proveedor -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Proveedor</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-truck"></i></span>
                    <select class="form-control" name="nuevoProveedorGasto">
                      <option value="">Sin proveedor</option>
                      <?php
                      $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                      foreach ($proveedores as $key => $value) {
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Método de Pago -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Método de Pago *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                    <select class="form-control" name="nuevoMetodoPagoGasto" required>
                      <?php
                      foreach ($mediosPago as $medio) {
                        $medio = trim($medio); // Eliminar espacios en blanco
                        echo '<option value="' . $medio . '">' . $medio . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Número de Comprobante -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>N° Comprobante</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                    <input type="text" class="form-control" name="nuevoNumeroComprobante"
                      placeholder="Número de comprobante">
                  </div>
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Estado *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                    <select class="form-control" name="nuevoEstadoGasto" required>
                      <option value="aprobado">Aprobado</option>
                      <option value="pendiente">Pendiente</option>
                      <option value="rechazado">Rechazado</option>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <!-- Imagen Comprobante -->
            <div class="form-group">
              <label>Imagen Comprobante</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-image"></i></span>
                <input type="file" class="form-control" name="nuevaImagenComprobante" accept="image/*">
              </div>
            </div>

            <!-- Notas -->
            <div class="form-group">
              <label>Notas</label>
              <textarea class="form-control" name="nuevasNotasGasto" rows="3"
                placeholder="Notas adicionales"></textarea>
            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar gasto</button>
        </div>

        <?php

        $crearGasto = new ControladorGastos();
        $crearGasto->ctrCrearGasto();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR GASTO
======================================-->

<div id="modalEditarGasto" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Gasto</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="row">

              <!-- Concepto -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Concepto *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                    <input type="text" class="form-control" name="editarConceptoGasto" id="editarConceptoGasto"
                      required>
                    <input type="hidden" id="idGasto" name="idGasto">
                  </div>
                </div>
              </div>

              <!-- Monto -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Monto *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                    <input type="number" class="form-control" name="editarMontoGasto" id="editarMontoGasto" min="0"
                      step="0.01" required>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Fecha -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Fecha *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="editarFechaGasto" id="editarFechaGasto" required>
                  </div>
                </div>
              </div>

              <!-- Categoría -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Categoría *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <select class="form-control" name="editarCategoriaGasto" id="editarCategoriaGasto" required>
                      <option value="">Seleccionar categoría</option>
                      <?php
                      $categorias = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
                      foreach ($categorias as $key => $value) {
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Proveedor -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Proveedor</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-truck"></i></span>
                    <select class="form-control" name="editarProveedorGasto" id="editarProveedorGasto">
                      <option value="">Sin proveedor</option>
                      <?php
                      $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                      foreach ($proveedores as $key => $value) {
                        echo '<option value="' . $value["id"] . '">' . $value["nombre"] . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <div class="row">

              <!-- Método de Pago -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Método de Pago *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                    <select class="form-control" name="editarMetodoPagoGasto" id="editarMetodoPagoGasto" required>
                      <?php
                      foreach ($mediosPago as $medio) {
                        $medio = trim($medio);
                        echo '<option value="' . $medio . '">' . $medio . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Número de Comprobante -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>N° Comprobante</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                    <input type="text" class="form-control" name="editarNumeroComprobante" id="editarNumeroComprobante">
                  </div>
                </div>
              </div>

              <!-- Estado -->
              <div class="col-md-4">
                <div class="form-group">
                  <label>Estado *</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                    <select class="form-control" name="editarEstadoGasto" id="editarEstadoGasto" required>
                      <option value="aprobado">Aprobado</option>
                      <option value="pendiente">Pendiente</option>
                      <option value="rechazado">Rechazado</option>
                    </select>
                  </div>
                </div>
              </div>

            </div>

            <!-- Imagen Comprobante -->
            <div class="form-group">
              <label>Imagen Comprobante</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-image"></i></span>
                <input type="file" class="form-control" name="editarImagenComprobante" accept="image/*">
              </div>
              <input type="hidden" name="imagenActual" id="imagenActual">
              <div id="previsualizarImagen" style="margin-top: 10px;"></div>
            </div>

            <!-- Notas -->
            <div class="form-group">
              <label>Notas</label>
              <textarea class="form-control" name="editarNotasGasto" id="editarNotasGasto" rows="3"></textarea>
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

        $editarGasto = new ControladorGastos();
        $editarGasto->ctrEditarGasto();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL GESTIONAR CATEGORÍAS
======================================-->

<div id="modalGestionarCategorias" class="modal fade" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Gestionar Categorías de Gastos</h4>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <!-- Formulario agregar categoría -->
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Agregar Nueva Categoría</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post" id="formAgregarCategoria">

              <?php CSRF::insertToken(); ?>
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <input type="text" class="form-control" name="nombreCategoriaGasto"
                      placeholder="Nombre de la categoría *" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <input type="color" class="form-control" name="colorCategoriaGasto" value="#3c8dbc">
                  </div>
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fa fa-plus"></i> Agregar
                  </button>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <textarea class="form-control" name="descripcionCategoriaGasto" rows="2"
                      placeholder="Descripción (opcional)"></textarea>
                  </div>
                </div>
              </div>

              <?php
              $crearCategoria = new ControladorCategoriasGastos();
              $crearCategoria->ctrCrearCategoriaGasto();
              ?>

            </form>
          </div>
        </div>

        <!-- Lista de categorías -->
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Categorías Existentes</h3>
          </div>
          <div class="panel-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped tablaCategoriasGastos">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Color</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $categorias = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
                  foreach ($categorias as $key => $value) {
                    echo '<tr>
                      <td>' . ($key + 1) . '</td>
                      <td><span class="badge" style="background-color: ' . $value["color"] . '">' . $value["nombre"] . '</span></td>
                      <td><input type="color" value="' . $value["color"] . '" disabled style="width: 50px;"></td>
                      <td>' . $value["descripcion"] . '</td>
                      <td>
                        <button class="btn btn-warning btn-xs btnEditarCategoriaGasto" idCategoria="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarCategoria"><i class="fa fa-pencil"></i></button>
                        <button class="btn btn-danger btn-xs btnEliminarCategoriaGasto" idCategoria="' . $value["id"] . '" nombreCategoria="' . $value["nombre"] . '"><i class="fa fa-times"></i></button>
                      </td>
                    </tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR CATEGORÍA
======================================-->

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
          <h4 class="modal-title">Editar Categoría</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <div class="form-group">
              <label>Nombre *</label>
              <input type="text" class="form-control" name="editarNombreCategoriaGasto" id="editarNombreCategoriaGasto"
                required>
              <input type="hidden" name="idCategoriaGasto" id="idCategoriaGasto">
            </div>

            <div class="form-group">
              <label>Color</label>
              <input type="color" class="form-control" name="editarColorCategoriaGasto" id="editarColorCategoriaGasto">
            </div>

            <div class="form-group">
              <label>Descripción</label>
              <textarea class="form-control" name="editarDescripcionCategoriaGasto" id="editarDescripcionCategoriaGasto"
                rows="3"></textarea>
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

        $editarCategoria = new ControladorCategoriasGastos();
        $editarCategoria->ctrEditarCategoriaGasto();

        ?>

      </form>

    </div>

  </div>

</div>




<!--=====================================
MODAL AMPLIAR Y EDITAR IMAGEN COMPROBANTE
======================================-->

<div id="modalAmpliarComprobanteGasto" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#3c8dbc; color: white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Comprobante de Gasto</h4>
      </div>
      <div class="modal-body text-center">
        <img id="imagenComprobanteAmpliada" src="" class="img-responsive"
          style="max-width: 100%; margin: 0 auto; margin-bottom: 20px;">
        <hr>
        <div class="form-group">
          <label>Cambiar Imagen del Comprobante</label>
          <input type="file" class="form-control nuevaImagenComprobante" accept="image/*">
          <p class="help-block">Peso máximo de la imagen 2MB</p>
        </div>
        <input type="hidden" id="idGastoImagen">
        <input type="hidden" id="conceptoGasto">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btnGuardarImagenComprobante">Guardar Imagen</button>
      </div>
    </div>
  </div>
</div>




<?php

$borrarGasto = new ControladorGastos();
$borrarGasto->ctrEliminarGasto();

$borrarCategoria = new ControladorCategoriasGastos();
$borrarCategoria->ctrEliminarCategoriaGasto();

?>