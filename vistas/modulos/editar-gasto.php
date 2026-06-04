<?php
if (!puedeAccion('gastos', 'editar')) {
    echo '<script>window.location = "inicio";</script>';
    return;
}

$idGasto = isset($_GET["idGasto"]) ? (int)$_GET["idGasto"] : 0;
$gasto = ControladorGastos::ctrMostrarGastos("id", $idGasto);

if (!$gasto) {
    echo '<script>window.location = "gastos";</script>';
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
      Editar gasto
      <small>Modificar egreso</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <?php if (puedeVer('gastos')): ?>
        <li><a href="gastos">Gastos</a></li>
      <?php endif; ?>
      <li class="active">Editar gasto</li>
    </ol>
  </section>

  <section class="content">

    <div class="box box-warning">
      <div class="box-header with-border">
        <h3 class="box-title">Modificar Información del Gasto</h3>
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
                  <input type="text" class="form-control" name="editarConceptoGasto" value="<?php echo e($gasto["concepto"]); ?>" required>
                  <input type="hidden" name="idGasto" value="<?php echo $gasto["id"]; ?>">
                </div>
              </div>
            </div>

            <!-- Monto -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Monto *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                  <input type="number" class="form-control" name="editarMontoGasto" value="<?php echo $gasto["monto"]; ?>" min="0" step="0.01" required>
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
                  <input type="date" class="form-control" name="editarFechaGasto" value="<?php echo $gasto["fecha"]; ?>" required>
                </div>
              </div>
            </div>

            <!-- Categoría -->
            <div class="col-md-4">
              <div class="form-group">
                <label>Categoría *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                  <select class="form-control" name="editarCategoriaGasto" required>
                    <option value="">Seleccionar categoría</option>
                    <?php
                    $categorias = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);
                    foreach ($categorias as $key => $value) {
                      $selected = ($value["id"] == $gasto["id_categoria_gasto"]) ? "selected" : "";
                      echo '<option value="' . $value["id"] . '" ' . $selected . '>' . $value["nombre"] . '</option>';
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
                  <select class="form-control" name="editarProveedorGasto">
                    <option value="">Sin proveedor</option>
                    <?php
                    $proveedores = ControladorProveedores::ctrMostrarProveedores(null, null);
                    foreach ($proveedores as $key => $value) {
                      $selected = ($value["id"] == $gasto["id_proveedor"]) ? "selected" : "";
                      echo '<option value="' . $value["id"] . '" ' . $selected . '>' . $value["nombre"] . '</option>';
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
                  <select class="form-control" name="editarMetodoPagoGasto" required>
                    <?php
                    foreach ($mediosPago as $medio) {
                      $medio = trim($medio);
                      $selected = ($medio == $gasto["metodo_pago"]) ? "selected" : "";
                      echo '<option value="' . $medio . '" ' . $selected . '>' . $medio . '</option>';
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
                  <input type="text" class="form-control" name="editarNumeroComprobante" value="<?php echo e($gasto["numero_comprobante"]); ?>" placeholder="Número de comprobante">
                </div>
              </div>
            </div>

            <!-- Estado -->
            <div class="col-md-4">
              <div class="form-group">
                <label>Estado *</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-check"></i></span>
                  <select class="form-control" name="editarEstadoGasto" required>
                    <option value="aprobado" <?php echo ($gasto["estado"] == "aprobado") ? "selected" : ""; ?>>Aprobado</option>
                    <option value="pendiente" <?php echo ($gasto["estado"] == "pendiente") ? "selected" : ""; ?>>Pendiente</option>
                    <option value="rechazado" <?php echo ($gasto["estado"] == "rechazado") ? "selected" : ""; ?>>Rechazado</option>
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
            <input type="hidden" name="imagenActual" value="<?php echo $gasto["imagen_comprobante"]; ?>">
            <?php if (!empty($gasto["imagen_comprobante"])): ?>
              <div style="margin-top: 10px;">
                <p>Comprobante actual:</p>
                <img src="<?php echo $gasto["imagen_comprobante"]; ?>" class="img-thumbnail" width="150px">
              </div>
            <?php endif; ?>
          </div>

          <!-- Notas -->
          <div class="form-group">
            <label>Notas</label>
            <textarea class="form-control" name="editarNotasGasto" rows="3" placeholder="Notas adicionales"><?php echo e($gasto["notas"]); ?></textarea>
          </div>

        </div>

        <div class="box-footer">
          <?php if (puedeVer('gastos')): ?>
            <a href="gastos" class="btn btn-default">Cancelar</a>
          <?php else: ?>
            <a href="inicio" class="btn btn-default">Cancelar</a>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary pull-right">Guardar cambios</button>
        </div>

        <?php
        $editarGasto = new ControladorGastos();
        $editarGasto->ctrEditarGasto();
        ?>

      </form>

    </div>

  </section>

</div>
