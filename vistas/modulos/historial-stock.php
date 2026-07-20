<style>
  /* Estilos para campo notas editable */
  .celda-notas-movimiento {
    background: #fff9e6;
    padding: 8px;
    border: 1px solid #eee;
    border-radius: 4px;
    cursor: text;
    font-size: 13px;
    color: #333;
    min-height: 35px;
    position: relative;
    transition: background 0.4s ease, border-color 0.4s ease, color 0.4s ease;
  }

  /* Placeholder para cuando está vacío */
  .celda-notas-movimiento:empty:before,
  .celda-notas-movimiento[data-placeholder]:before {
    content: "Escribe una nota...";
    color: #999;
    font-style: italic;
  }

  /* Ocultar placeholder cuando tiene foco */
  .celda-notas-movimiento:focus:before {
    content: none;
  }

  .celda-notas-movimiento:focus {
    outline: 2px solid #f39c12;
    background: #fffef5;
  }

  /* Toast notification */
  .toast-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #00a65a;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideInRight 0.3s ease-out;
    font-size: 15px;
  }

  .toast-notification.toast-hide {
    animation: slideOutRight 0.3s ease-out;
  }

  @keyframes slideInRight {
    from {
      transform: translateX(400px);
      opacity: 0;
    }

    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }

    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }

  @media (max-width: 767px) {
    .box-header .pull-right.contenedor-filtros {
      float: none !important;
      width: 100% !important;
      margin-top: 15px !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    .form-filtros-stock {
      flex-direction: column !important;
      align-items: stretch !important;
      width: 100% !important;
      gap: 12px !important;
    }
    .form-filtros-stock > div {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      width: 100% !important;
      gap: 10px !important;
      margin-bottom: 0 !important;
    }
    .form-filtros-stock > div > label,
    .form-filtros-stock > div > span {
      min-width: 80px !important;
      text-align: left !important;
      margin-bottom: 0 !important;
    }
    .form-filtros-stock > div > .input-group,
    .form-filtros-stock > div > div {
      flex: 1 !important;
      width: auto !important;
    }
    .form-filtros-stock > div .select2-container {
      width: 100% !important;
    }
    .form-filtros-stock > div > div > #btn-rango-stock {
      width: 100% !important;
      text-align: left !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
    }
    .form-filtros-stock > button {
      width: 100% !important;
      text-align: center !important;
    }

    /* Permitir que Producto y Tipo de Movimiento se muestren juntos en móvil */
    .tablaHistorialStock tbody td:nth-child(2) {
      white-space: normal !important;
      max-width: 140px !important;
    }
    .tablaHistorialStock tbody td:nth-child(3) {
      white-space: normal !important;
      max-width: 100px !important;
    }
  }
</style>

<?php
// Asegurar carga de controladores/modelos para la pre-carga
require_once "controladores/movimientos.controlador.php";
require_once "modelos/movimientos.modelo.php";

// Definir idBodega para la carga inicial de los gráficos
$idBodegaInicial = (stripos($_SESSION["perfil"], "Admin") !== false || $_SESSION["perfil"] == "_SystemMaster_") ? (!empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : "todos") : $_SESSION["id_bodega"];

// Pre-cargar solo el resumen para aparición inmediata (Las tarjetas)
$pre_resumen = ControladorMovimientos::ctrObtenerResumen($idBodegaInicial);

$tv = 0;
$tc = 0;
$te = 0;
$tm = 0;

foreach ($pre_resumen as $item) {
  $tm += intval($item["total_movimientos"]);
  if ($item["tipo_movimiento"] == "venta")
    $tv = $item["total_unidades"];
  if ($item["tipo_movimiento"] == "eliminacion_venta")
    $tc += intval($item["total_unidades"]);
  if ($item["tipo_movimiento"] == "edicion_stock" || $item["tipo_movimiento"] == "ajuste_manual")
    $te += intval($item["total_movimientos"]);
}
?>

<script>
  window.preloadedResumen = <?php echo json_encode($pre_resumen); ?>;
</script>

<div class="content-wrapper">

  <section class="content-header">

    <h1>
      Historial de Stock
      <small>Auditoría de inventario</small>
    </h1>

    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Historial de Stock</li>
    </ol>

  </section>

  <section class="content">

    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4><i class="icon fa fa-warning"></i> Atención!</h4>
      Los registros del historial de stock se eliminan automáticamente del sistema después de transcurrir <b>3 meses</b>
      desde su creación.
    </div>

    <!-- TARJETAS DE RESUMEN -->
    <div class="row" id="tarjetasResumen">

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3 id="totalVentas"><?php echo $tv; ?></h3>
            <p>Ventas Totales</p>
          </div>
          <div class="icon">
            <i class="fa fa-shopping-cart"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <h3 id="totalEliminacionesVentas"><?php echo $tc; ?></h3>
            <p>Ventas Eliminadas</p>
          </div>
          <div class="icon">
            <i class="fa fa-times-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3 id="totalEdiciones"><?php echo $te; ?></h3>
            <p>Ajustes y Ediciones</p>
          </div>
          <div class="icon">
            <i class="fa fa-edit"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <h3 id="totalMovimientos"><?php echo $tm; ?></h3>
            <p>Total Movimientos</p>
          </div>
          <div class="icon">
            <i class="fa fa-list"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- FILTRO MAESTRO DE SUCURSAL -->
    <?php 
    $configuracionGlobal = ControladorConfiguracion::ctrObtenerConfiguracion();
    $sucursalesActivas = !isset($configuracionGlobal["activar_sucursales"]) || $configuracionGlobal["activar_sucursales"] == 1;
    if ($sucursalesActivas && (stripos($_SESSION["perfil"], "Admin") !== false || $_SESSION["perfil"] == "_SystemMaster_")): 
    ?>
      <div class="box box-default">
        <div class="box-body" style="padding: 15px 25px;">
          <div class="row" style="display: flex; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="col-md-4 col-sm-6 col-xs-12">
              <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 14px; color: #555;"><i class="fa fa-building text-primary"></i> Filtrar por Sucursal (Vista Global):</label>
                <select class="form-control select2" id="sucursalReporteMaestro" style="width: 100%;" autocomplete="off">
                  <option value="todos" <?php echo empty($_SESSION["id_bodega"]) ? "selected" : ""; ?>>Filtrar por Sucursal (Vista Global):</option>
                  <?php
                  $bodegas = ControladorBodegas::ctrMostrarBodegas(null, null);
                  foreach ($bodegas as $key => $value) {
                    $selected = (!empty($_SESSION["id_bodega"]) && $_SESSION["id_bodega"] == $value["id"]) ? "selected" : "";
                    echo '<option value="' . $value["id"] . '" ' . $selected . '>' . $value["nombre"] . '</option>';
                  }
                  ?>
                </select>
                <script>
                  var defaultSucursalHistorial = "<?php echo !empty($_SESSION['id_bodega']) ? $_SESSION['id_bodega'] : 'todos'; ?>";
                </script>
              </div>
            </div>
            <div class="col-md-8 col-sm-6 hidden-xs">
              <p class="text-muted" style="margin-top: 22px; font-style: italic;">
                <i class="fa fa-info-circle"></i> Seleccione una sucursal para auditar los movimientos de inventario específicos de esa tienda.
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <input type="hidden" id="sucursalReporteMaestro" value="<?php echo !empty($_SESSION['id_bodega']) ? $_SESSION['id_bodega'] : 1; ?>">
    <?php endif; ?>

    <!-- TABLA DE MOVIMIENTOS -->
    <div class="box">

      <div class="box-header with-border">

        <h3 class="box-title">Registro de Movimientos</h3>

        <div class="pull-right contenedor-filtros">

          <div id="formFiltros" class="form-filtros-stock" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">

            <!-- Filtro por Producto -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label style="margin-bottom: 0;">Producto:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="cat_s" style="width: 140px; border-left: 0;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $conn = Conexion::conectar();
                  $stmtProductos = $conn->prepare("SELECT id, descripcion, tiene_variantes FROM productos ORDER BY descripcion ASC");
                  $stmtProductos->execute();
                  $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

                  // Obtener todas las variantes activas con sus opciones
                  $sqlVariantes = "
                    SELECT pv.id as id_variante, pv.id_producto, ov.nombre as opcion_nombre
                    FROM productos_variantes pv
                    INNER JOIN productos_variantes_opciones pvo ON pv.id = pvo.id_producto_variante
                    INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
                    INNER JOIN tipos_variantes tv ON ov.id_tipo_variante = tv.id
                    WHERE pv.estado = 1
                    ORDER BY pv.id_producto, pv.id, tv.orden, ov.orden
                  ";
                  $stmtVariantes = $conn->prepare($sqlVariantes);
                  $stmtVariantes->execute();
                  $varianteRows = $stmtVariantes->fetchAll(PDO::FETCH_ASSOC);

                  $variantesAgrupadas = [];
                  foreach ($varianteRows as $row) {
                      $idProd = $row['id_producto'];
                      $idVar = $row['id_variante'];
                      if (!isset($variantesAgrupadas[$idProd])) {
                          $variantesAgrupadas[$idProd] = [];
                      }
                      if (!isset($variantesAgrupadas[$idProd][$idVar])) {
                          $variantesAgrupadas[$idProd][$idVar] = [];
                      }
                      $variantesAgrupadas[$idProd][$idVar][] = $row['opcion_nombre'];
                  }

                  foreach ($productos as $producto) {
                    $hasVariants = (isset($producto['tiene_variantes']) && $producto['tiene_variantes'] == 1 && isset($variantesAgrupadas[$producto['id']]) && count($variantesAgrupadas[$producto['id']]) > 0);
                    $disabled = $hasVariants ? 'disabled' : '';

                    echo '<option value="' . e($producto['id']) . '" ' . $disabled . '>' . e($producto['descripcion']) . '</option>';

                    if ($hasVariants) {
                      foreach ($variantesAgrupadas[$producto['id']] as $idVar => $opciones) {
                        $nombreVarianteStr = implode(" - ", $opciones);
                        $descripcionCompleta = "└─ " . $producto['descripcion'] . " - " . $nombreVarianteStr;
                        echo '<option value="v_' . e($idVar) . '">&nbsp;&nbsp;&nbsp;&nbsp;' . e($descripcionCompleta) . '</option>';
                      }
                    }
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por Movimiento -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label style="margin-bottom: 0;">Movimiento:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="tipo_s" style="width: 130px; border-left: 0;">
                  <option value="">Mostrar Todos</option>
                  <option value="venta">Venta</option>
                  <option value="eliminacion_venta">Eliminación Venta</option>
                  <option value="devolucion">Devolución</option>
                  <option value="creacion_producto">Creación Producto</option>
                  <option value="eliminacion_producto">Eliminación de Producto</option>
                  <option value="creacion_variante">Creación Variante</option>
                  <option value="eliminacion_variante">Eliminación de Variantes</option>
                  <option value="edicion_stock">Edición Stock</option>
                  <option value="ajuste_manual">Ajuste Manual</option>
                  <option value="traslado_salida">Traslado (Salida)</option>
                  <option value="traslado_entrada">Traslado (Entrada)</option>
                </select>
              </div>
            </div>

            <!-- Filtro por Usuario -->
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
              <label style="margin-bottom: 0;">Usuario:</label>
              <div class="input-group">
                <span class="input-group-addon" style="background-color: #f9f9f9;"><i
                    class="fa fa-search text-primary"></i></span>
                <select class="form-control select2" id="user_s" style="width: 120px; border-left: 0;">
                  <option value="">Mostrar Todos</option>
                  <?php
                  $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
                  foreach ($usuarios as $key => $value) {
                    if ($value["perfil"] === "SystemMaster" || $value["perfil"] === "_SystemMaster_" || $value["perfil"] === "Visitante") {
                      continue;
                    }
                    echo '<option value="' . e($value["id"]) . '">' . e($value["nombre"]) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Filtro por Rango de Fecha -->
            <div style="display: flex; align-items: center; gap: 8px;">
              <span><b>Fecha:</b></span>
              <div class="form-group" style="margin-bottom: 0;">
                <button type="button" class="btn btn-default" id="btn-rango-stock">
                  <span id="span-rango-stock">
                    <i class="fa fa-calendar"></i> Rango de fecha
                  </span>
                  <i class="fa fa-caret-down"></i>
                </button>
                <input type="hidden" id="fi_s">
                <input type="hidden" id="ff_s">
              </div>
            </div>

            <!-- Botones de Acción (Separados para mantener gap consistente) -->
            <button type="button" class="btn btn-default" id="btnLimpiar" title="Limpiar">
              <i class="fa fa-refresh"></i>
            </button>
            <?php if (puedeAccion('historial_stock', 'imprimir')): ?>
              <button type="button" class="btn btn-success" id="btnDescargarExcelStockDirecto">
                <i class="fa fa-file-excel-o"></i> Exportar a Excel
              </button>
            <?php endif; ?>

          </div>

        </div>

      </div>


      <div class="box-body">

        <table class="table table-bordered table-striped tablaHistorialStock display nowrap" width="100%">

          <thead>

            <tr>
              <th width="10"></th>
              <th>Producto</th>
              <th>Tipo Movimiento</th>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Cantidad</th>
              <th>Stock Anterior</th>
              <th>Stock Nuevo</th>
              <th>Usuario</th>
              <th>Referencia</th>
              <th>Notas</th>
            </tr>

          </thead>

          <tbody>
          </tbody>

        </table>

      </div>

    </div>

  </section>

</div>