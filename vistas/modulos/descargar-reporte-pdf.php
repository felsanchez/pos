<?php

require_once "../../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../../controladores/ventas.controlador.php";
require_once "../../modelos/ventas.modelo.php";
require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";

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

// Obtener ventas según filtro de fechas
if (isset($_GET["fechaInicial"]) && isset($_GET["fechaFinal"]) && !empty($_GET["fechaInicial"])) {
    $ventas = ModeloVentas::mdlRangoFechasVentas($tabla, $_GET["fechaInicial"], $_GET["fechaFinal"]);
} else {
    $ventas = ModeloVentas::mdlMostrarVentas($tabla, null, null);
}

// Aplicar filtros y construir filas
$filas = [];
$totalNeto    = 0;
$totalImpuesto = 0;
$totalGeneral  = 0;

foreach ($ventas as $item) {
    if (!isset($item["estado"]) || $item["estado"] != "venta") continue;

    // Filtro usuario
    if (isset($_GET["usuario"]) && $_GET["usuario"] != "" && (string)$item["id_vendedor"] != (string)$_GET["usuario"]) continue;

    // Filtro cliente
    if (isset($_GET["cliente"]) && $_GET["cliente"] != "" && $_GET["cliente"] != "todos" && (string)$item["id_cliente"] != (string)$_GET["cliente"]) continue;

    $cliente  = ControladorClientes::ctrMostrarClientes("id", $item["id_cliente"]);
    $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $item["id_vendedor"]);

    $productos = json_decode($item["productos"], true);
    $listaProductos = [];
    foreach ($productos as $p) {
        $listaProductos[] = $p["cantidad"] . "x " . $p["descripcion"];
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
if (!empty($_GET["fechaInicial"]) && !empty($_GET["fechaFinal"])) {
    $periodo = "Del " . $_GET["fechaInicial"] . " al " . $_GET["fechaFinal"];
}

$nombreUsuario = "";
if (!empty($_GET["usuario"])) {
    $usr = ControladorUsuarios::ctrMostrarUsuarios("id", $_GET["usuario"]);
    $nombreUsuario = $usr ? "Usuario: " . $usr["nombre"] : "";
}

$nombreCliente = "";
if (!empty($_GET["cliente"]) && $_GET["cliente"] != "todos") {
    $cli = ControladorClientes::ctrMostrarClientes("id", $_GET["cliente"]);
    $nombreCliente = $cli ? "Cliente: " . $cli["nombre"] : "";
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
      <?php if ($nombreUsuario || $nombreCliente): ?>
        <div class="subtitulo" style="margin-top:4px; color:#c0392b;">
          <?php echo htmlspecialchars(implode(" | ", array_filter([$nombreUsuario, $nombreCliente]))); ?>
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
  <?php if ($nombreUsuario || $nombreCliente || !empty($_GET["fechaInicial"])): ?>
    <div class="filtros-aplicados">
      <strong>Filtros aplicados:</strong>
      <?php
      $filtros = [];
      if (!empty($_GET["fechaInicial"])) $filtros[] = "Período: " . $periodo;
      if ($nombreUsuario) $filtros[] = $nombreUsuario;
      if ($nombreCliente) $filtros[] = $nombreCliente;
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
