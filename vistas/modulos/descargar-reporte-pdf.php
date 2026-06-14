<?php

require_once "../../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../../controladores/ventas.controlador.php";
require_once "../../modelos/ventas.modelo.php";
require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";
require_once "../../controladores/productos.controlador.php";
require_once "../../modelos/productos.modelo.php";
require_once "../../controladores/bodegas.controlador.php";
require_once "../../modelos/bodegas.modelo.php";

if (!isset($_GET["reporte"])) {
    die("Parámetro inválido.");
}

// Obtener logo de facturación electrónica
$logoFactus = "";
$stmtLogo = Conexion::conectar()->prepare("SELECT logo_empresa FROM factus_config LIMIT 1");
$stmtLogo->execute();
$configFactus = $stmtLogo->fetch(PDO::FETCH_ASSOC);

if ($configFactus && !empty($configFactus["logo_empresa"])) {
    $logoFactus = "../../" . $configFactus["logo_empresa"];
}


$tabla = "ventas";

// 1. Obtener valores de la URL (GET)
$tipo = $_GET['tipo'] ?? 'todo';
$fecha_inicio = $_GET['fechaInicial'] ?? null;
$fecha_fin = $_GET['fechaFinal'] ?? null;
$id_vendedor = $_GET['vendedor'] ?? $_GET['usuario'] ?? null;
$id_cliente = $_GET['cliente'] ?? null;
$id_producto = $_GET['producto'] ?? null;
$metodo_pago = $_GET['metodoPago'] ?? null;
$id_bodega = $_GET['idBodega'] ?? null;

// Construir la condición de fecha
$condicionFecha = "";
$fechaParams = [];

switch ($tipo) {
    case 'todo':
        $condicionFecha = "1=1";
        break;
    case 'hoy':
        $condicionFecha = "DATE(fecha) = CURDATE()";
        break;
    case 'ayer':
        $condicionFecha = "DATE(fecha) = CURDATE() - INTERVAL 1 DAY";
        break;
    case 'mes':
        $condicionFecha = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
        break;
    case 'personalizado':
        if (!$fecha_inicio || !$fecha_fin) {
            $condicionFecha = "1=1";
        } else {
            $condicionFecha = "DATE(fecha) BETWEEN ? AND ?";
            $fechaParams[] = $fecha_inicio;
            $fechaParams[] = $fecha_fin;
        }
        break;
    default:
        $condicionFecha = "1=1";
        break;
}

// Construir filtros comunes
$filtrosComunes = "";
$filtroParams = [];

if (!empty($id_vendedor)) {
    $filtrosComunes .= " AND id_vendedor = ?";
    $filtroParams[] = $id_vendedor;
}

if (!empty($id_cliente) && $id_cliente !== 'todos') {
    $filtrosComunes .= " AND id_cliente = ?";
    $filtroParams[] = $id_cliente;
}

if (!empty($id_bodega) && $id_bodega !== 'todos') {
    $filtrosComunes .= " AND id_bodega = ?";
    $filtroParams[] = $id_bodega;
}

if (!empty($id_producto)) {
    if (strpos($id_producto, 'v_') === 0) {
        // Es una variante de producto
        $id_variante = substr($id_producto, 2);
        $filtrosComunes .= " AND (productos LIKE ? OR productos LIKE ? OR productos LIKE ?)";
        $filtroParams[] = '%"idVariante":"' . $id_variante . '"%';
        $filtroParams[] = '%"idVariante":' . $id_variante . ',%';
        $filtroParams[] = '%"idVariante":' . $id_variante . '}%';
    } else {
        // Es un producto base
        $filtrosComunes .= " AND (productos LIKE ? OR productos LIKE ? OR productos LIKE ?)";
        $filtroParams[] = '%"id":"' . $id_producto . '"%';
        $filtroParams[] = '%"id":' . $id_producto . ',%';
        $filtroParams[] = '%"id":' . $id_producto . '}%';
    }
}

$filtrosVentasExtra = "";
$ventasExtraParams = [];
if (!empty($metodo_pago)) {
    $filtrosVentasExtra .= " AND (metodo_pago = ? OR metodo_pago LIKE ?)";
    $ventasExtraParams[] = $metodo_pago;
    $ventasExtraParams[] = $metodo_pago . '-%';
}

$whereVentas = "estado = 'venta' AND ( (resolucion_id IS NULL OR resolucion_id = 0) OR ( resolucion_id IS NOT NULL AND resolucion_id != 0 AND estado_dian IN ('aceptada', 'enviada') ) ) AND $condicionFecha" . $filtrosComunes . $filtrosVentasExtra;
$paramsVentas = array_merge($fechaParams, $filtroParams, $ventasExtraParams);

$sql = "SELECT * FROM $tabla WHERE $whereVentas ORDER BY id ASC";

$stmt = Conexion::conectar()->prepare($sql);
foreach ($paramsVentas as $i => $val) {
    $stmt->bindValue($i + 1, $val);
}
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Aplicar filtros y construir filas
$filas = [];
$totalNeto    = 0;
$totalImpuesto = 0;
$totalGeneral  = 0;

foreach ($ventas as $item) {
    $cliente  = ControladorClientes::ctrMostrarClientes("id", $item["id_cliente"]);
    $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

    $productos = json_decode($item["productos"], true);
    $listaProductos = [];
    if (is_array($productos)) {
        foreach ($productos as $p) {
            $listaProductos[] = $p["cantidad"] . "x " . $p["descripcion"];
        }
    }

    $totalNeto     += $item["neto"];
    $totalImpuesto += $item["impuesto"];
    $totalGeneral  += $item["total"];

    $filas[] = [
        "codigo"       => $item["codigo"],
        "cliente"      => $cliente["nombre"] ?? "—",
        "vendedor"     => $vendedor["nombre"] ?? "—",
        "productos"    => implode("<br>", $listaProductos),
        "impuesto"     => $item["impuesto"],
        "neto"         => $item["neto"],
        "total"        => $item["total"],
        "metodo_pago"  => $item["metodo_pago"],
        "fecha"        => substr($item["fecha"], 0, 10),
    ];
}

// Titulo del período
$periodo = "Todas las ventas";
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $periodo = "Del " . $fecha_inicio . " al " . $fecha_fin;
} elseif ($tipo == "hoy") {
    $periodo = "Hoy (" . date("d-m-Y") . ")";
} elseif ($tipo == "ayer") {
    $periodo = "Ayer (" . date("d-m-Y", strtotime("-1 day")) . ")";
} elseif ($tipo == "mes") {
    $periodo = "Mes actual (" . date("m-Y") . ")";
}

$nombreUsuario = "";
if (!empty($id_vendedor)) {
    $usr = ControladorUsuarios::ctrMostrarUsuarios("id", $id_vendedor);
    $nombreUsuario = $usr ? "Vendedor: " . $usr["nombre"] : "";
}

$nombreCliente = "";
if (!empty($id_cliente) && $id_cliente !== "todos") {
    $cli = ControladorClientes::ctrMostrarClientes("id", $id_cliente);
    $nombreCliente = $cli ? "Cliente: " . $cli["nombre"] : "";
}

$nombreProducto = "";
if (!empty($id_producto)) {
    if (strpos($id_producto, 'v_') === 0) {
        $id_variante = substr($id_producto, 2);
        $variante = ModeloProductos::mdlObtenerVariantePorId($id_variante);
        if ($variante) {
            $prod = ControladorProductos::ctrMostrarProductos("id", $variante["id_producto"], null);
            // Fetch option values
            $stmt = Conexion::conectar()->prepare("
                SELECT ov.nombre 
                FROM productos_variantes_opciones pvo
                INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
                WHERE pvo.id_producto_variante = :id_variante
            ");
            $stmt->bindParam(":id_variante", $id_variante, PDO::PARAM_INT);
            $stmt->execute();
            $opciones = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $nombreProducto = "Producto: " . ($prod ? $prod["descripcion"] : "") . " - " . implode(" / ", $opciones);
        } else {
            $nombreProducto = "Variante ID: " . $id_variante;
        }
    } else {
        $prod = ControladorProductos::ctrMostrarProductos("id", $id_producto, null);
        $nombreProducto = $prod ? "Producto: " . $prod["descripcion"] : "";
    }
}

$nombreMetodoPago = "";
if (!empty($metodo_pago)) {
    $nombreMetodoPago = "Método de Pago: " . htmlspecialchars($metodo_pago);
}

$nombreBodega = "";
if (!empty($id_bodega) && $id_bodega !== 'todos') {
    $bod = ControladorBodegas::ctrMostrarBodegas("id", $id_bodega);
    $nombreBodega = $bod ? "Sucursal: " . $bod["nombre"] : "";
}

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Ventas - PDF</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #222; background: #fff; }

    /* Cabecera del reporte */
    .reporte-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 18px 24px 10px;
      border-bottom: 3px solid #c0392b;
      margin-bottom: 12px;
    }
    .reporte-header .titulo { font-size: 20px; font-weight: bold; color: #c0392b; }
    .reporte-header .subtitulo { font-size: 12px; color: #555; margin-top: 2px; }
    .reporte-header .meta { text-align: right; font-size: 11px; color: #555; line-height: 1.6; }

    /* Filtros aplicados */
    .filtros-aplicados {
      padding: 6px 24px;
      background: #fdf0ef;
      border-left: 4px solid #c0392b;
      margin: 0 24px 14px;
      font-size: 11px;
      color: #555;
      border-radius: 2px;
    }

    /* Tabla principal */
    .tabla-reporte {
      width: calc(100% - 48px);
      margin: 0 24px;
      border-collapse: collapse;
    }
    .tabla-reporte thead tr {
      background: #c0392b;
      color: #fff;
    }
    .tabla-reporte thead th {
      padding: 7px 8px;
      text-align: left;
      font-size: 11px;
      font-weight: bold;
      border: 1px solid #a93226;
    }
    .tabla-reporte tbody tr:nth-child(even) { background: #fdf0ef; }
    .tabla-reporte tbody tr:nth-child(odd)  { background: #fff; }
    .tabla-reporte tbody td {
      padding: 6px 8px;
      border: 1px solid #e0e0e0;
      vertical-align: top;
      font-size: 10.5px;
    }
    .tabla-reporte tbody tr:hover { background: #fce8e6; }

    /* Fila de totales */
    .tabla-reporte tfoot tr {
      background: #2c3e50;
      color: #fff;
      font-weight: bold;
    }
    .tabla-reporte tfoot td {
      padding: 8px;
      border: 1px solid #1a252f;
      font-size: 11px;
    }

    /* Resumen KPIs */
    .kpi-row {
      display: flex;
      gap: 16px;
      margin: 16px 24px;
    }
    .kpi-box {
      flex: 1;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      padding: 12px 16px;
      text-align: center;
      background: #fafafa;
    }
    .kpi-box.red   { border-color: #c0392b; background: #fdf0ef; }
    .kpi-box.green { border-color: #27ae60; background: #f0fdf4; }
    .kpi-box.blue  { border-color: #2980b9; background: #eaf4fb; }
    .kpi-box .kpi-label { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-box .kpi-value { font-size: 16px; font-weight: bold; margin-top: 4px; }
    .kpi-box.red   .kpi-value { color: #c0392b; }
    .kpi-box.green .kpi-value { color: #27ae60; }
    .kpi-box.blue  .kpi-value { color: #2980b9; }

    /* Pie */
    .reporte-footer {
      margin: 20px 24px 10px;
      padding-top: 10px;
      border-top: 1px solid #ddd;
      font-size: 10px;
      color: #999;
      display: flex;
      justify-content: space-between;
    }

    /* Sin resultados */
    .sin-resultados {
      text-align: center;
      padding: 40px;
      color: #999;
      font-size: 14px;
    }

    /* Botones de acción (solo pantalla, no se imprimen) */
    .acciones-imprimir {
      text-align: center;
      padding: 14px;
      background: #f5f5f5;
      border-bottom: 2px solid #ddd;
      margin-bottom: 6px;
    }
    .btn-imprimir {
      background: #c0392b;
      color: #fff;
      border: none;
      padding: 10px 28px;
      font-size: 14px;
      border-radius: 4px;
      cursor: pointer;
      font-family: Arial, sans-serif;
      margin-right: 8px;
    }
    .btn-imprimir:hover { background: #a93226; }
    .btn-cerrar {
      background: #7f8c8d;
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      border-radius: 4px;
      cursor: pointer;
      font-family: Arial, sans-serif;
    }
    .btn-cerrar:hover { background: #636e72; }

    @media print {
      .acciones-imprimir { display: none !important; }
      body { margin: 0; }
      .reporte-header { padding: 10px 14px 8px; }
      .tabla-reporte { width: calc(100% - 28px); margin: 0 14px; }
      .kpi-row { margin: 10px 14px; }
      .filtros-aplicados { margin: 0 14px 10px; }
      .reporte-footer { margin: 10px 14px; }
    }
  </style>
</head>
<body>

  <!-- Barra de acciones (no se imprime) -->
  <div class="acciones-imprimir">
    <button class="btn-imprimir" onclick="window.print()">
      🖨️ Imprimir / Guardar como PDF
    </button>
    <button class="btn-cerrar" onclick="window.close()">✕ Cerrar</button>
  </div>

  <!-- Cabecera del reporte -->
  <div class="reporte-header">
    <div style="display: flex; align-items: center; gap: 15px;">
      <?php if (!empty($logoFactus) && file_exists($logoFactus)): ?>
        <img src="<?php echo $logoFactus; ?>" alt="Logo Empresa" style="max-height: 50px; object-fit: contain;">
      <?php endif; ?>
      <div>
        <div class="titulo">Reporte de Ventas</div>
        <div class="subtitulo"><?php echo htmlspecialchars($periodo); ?></div>
      <?php if ($nombreUsuario || $nombreCliente || $nombreProducto || $nombreMetodoPago || $nombreBodega): ?>
        <div class="subtitulo" style="margin-top:4px; color:#c0392b;">
          <?php echo htmlspecialchars(implode(" | ", array_filter([$nombreUsuario, $nombreCliente, $nombreProducto, $nombreMetodoPago, $nombreBodega]))); ?>
        </div>
      <?php endif; ?>
      </div>
    </div>
    <div class="meta">
      <strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
      <strong>Total de registros:</strong> <?php echo count($filas); ?>
    </div>
  </div>

  <!-- KPIs -->
  <div class="kpi-row">
    <div class="kpi-box blue">
      <div class="kpi-label">Total Ventas</div>
      <div class="kpi-value"><?php echo count($filas); ?></div>
    </div>
    <div class="kpi-box green">
      <div class="kpi-label">Neto Total</div>
      <div class="kpi-value">$ <?php echo number_format($totalNeto, 0, ',', '.'); ?></div>
    </div>
    <div class="kpi-box red">
      <div class="kpi-label">IVA Total</div>
      <div class="kpi-value">$ <?php echo number_format($totalImpuesto, 0, ',', '.'); ?></div>
    </div>
    <div class="kpi-box" style="border-color:#8e44ad; background:#f9f0ff;">
      <div class="kpi-label" style="color:#888;">Total General</div>
      <div class="kpi-value" style="color:#8e44ad;">$ <?php echo number_format($totalGeneral, 0, ',', '.'); ?></div>
    </div>
  </div>

  <!-- Filtros aplicados -->
  <?php if ($nombreUsuario || $nombreCliente || $nombreProducto || $nombreMetodoPago || $nombreBodega || !empty($fecha_inicio) || $tipo !== 'todo'): ?>
    <div class="filtros-aplicados">
      <strong>Filtros aplicados:</strong>
      <?php
      $filtros = [];
      if ($tipo !== 'todo' || !empty($fecha_inicio)) $filtros[] = "Período: " . $periodo;
      if ($nombreUsuario) $filtros[] = $nombreUsuario;
      if ($nombreCliente) $filtros[] = $nombreCliente;
      if ($nombreProducto) $filtros[] = $nombreProducto;
      if ($nombreMetodoPago) $filtros[] = $nombreMetodoPago;
      if ($nombreBodega) $filtros[] = $nombreBodega;
      echo implode(" &nbsp;|&nbsp; ", $filtros);
      ?>
    </div>
  <?php endif; ?>

  <!-- Tabla de datos -->
  <?php if (empty($filas)): ?>
    <div class="sin-resultados">
      <p>⚠️ No se encontraron ventas con los filtros seleccionados.</p>
    </div>
  <?php else: ?>
    <table class="tabla-reporte">
      <thead>
        <tr>
          <th>#</th>
          <th>Código</th>
          <th>Cliente</th>
          <th>Vendedor</th>
          <th>Productos</th>
          <th>IVA</th>
          <th>Neto</th>
          <th>Total</th>
          <th>Método Pago</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filas as $i => $fila): ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($fila["codigo"]); ?></td>
            <td><?php echo htmlspecialchars($fila["cliente"]); ?></td>
            <td><?php echo htmlspecialchars($fila["vendedor"]); ?></td>
            <td><?php echo $fila["productos"]; ?></td>
            <td>$ <?php echo number_format($fila["impuesto"], 0, ',', '.'); ?></td>
            <td>$ <?php echo number_format($fila["neto"], 0, ',', '.'); ?></td>
            <td><strong>$ <?php echo number_format($fila["total"], 0, ',', '.'); ?></strong></td>
            <td><?php echo htmlspecialchars($fila["metodo_pago"]); ?></td>
            <td><?php echo $fila["fecha"]; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" style="text-align:right;">TOTALES:</td>
          <td>$ <?php echo number_format($totalImpuesto, 0, ',', '.'); ?></td>
          <td>$ <?php echo number_format($totalNeto, 0, ',', '.'); ?></td>
          <td>$ <?php echo number_format($totalGeneral, 0, ',', '.'); ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
  <?php endif; ?>

  <!-- Pie del reporte -->
  <div class="reporte-footer">
    <span>Sistema POS — Reporte generado automáticamente</span>
    <span><?php echo date('d/m/Y H:i:s'); ?></span>
  </div>

  <script>
    // Auto-disparar diálogo de impresión al cargar
    window.addEventListener('load', function () {
      // Pequeño delay para que los estilos carguen completamente
      setTimeout(function () {
        window.print();
      }, 500);
    });
  </script>

</body>
</html>
