<?php
if (!puedeAccion('gastos', 'crear')) {
    echo '<script>window.location = "inicio";</script>';
    return;
}

// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$mediosPago = !empty($configuracion["medios_pago"]) ? explode(",", $configuracion["medios_pago"]) : array("Efectivo", "Tarjeta Débito", "Tarjeta Crédito", "Nequi", "Bancolombia", "Cheque");

// Validar si el control de caja está activo y hay caja abierta
if (class_exists("ControladorCajas") && !ControladorCajas::ctrValidarCajaAbierta()) {
    echo '<script>
        swal({
            type: "error",
            title: "Caja Cerrada",
            text: "Debe abrir caja antes de realizar esta operación.",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        }).then(() => {
            window.location = "inicio";
        });
    </script>';
    return;
}
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Crear gasto
      <small>Nuevo egreso</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <?php if (puedeVer('gastos')): ?>
        <li><a href="gastos">Gastos</a></li>
      <?php endif; ?>
      <li class="active">Crear gasto</li>
    </ol>
  </section>

  <section class="content">

    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title">Registrar Información del Gasto</h3>
      </div>

      <form role="form" method="post" enctype="multipart/form-data">

        <?php CSRF::insertToken(); ?>

        <div class="box-body">

          <div class="row">

            <!-- Concepto -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Concepto *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                  <input type="text" class="form-control" name="nuevoConceptoGasto" placeholder="Concepto del gasto" required>
                </div>
              </div>
            </div>

            <!-- Monto -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Monto *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                  <input type="number" class="form-control" name="nuevoMontoGasto" placeholder="0" min="0" step="0.01" required>
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
                  <input type="date" class="form-control" name="nuevaFechaGasto" value="<?php echo date('Y-m-d'); ?>" required>
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
                  <input type="text" class="form-control" name="nuevoNumeroComprobante" placeholder="Número de comprobante">
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
            <textarea class="form-control" name="nuevasNotasGasto" rows="3" placeholder="Notas adicionales"></textarea>
          </div>

        </div>

        <div class="box-footer">
          <?php if (puedeVer('gastos')): ?>
            <a href="gastos" class="btn btn-default">Cancelar</a>
          <?php else: ?>
            <a href="inicio" class="btn btn-default">Cancelar</a>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary pull-right">Guardar gasto</button>
        </div>

        <?php
        $crearGasto = new ControladorGastos();
        $crearGasto->ctrCrearGasto();
        ?>

      </form>

    </div>

  </section>

</div>
