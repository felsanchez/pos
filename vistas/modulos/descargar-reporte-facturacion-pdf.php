<?php

require_once "../../modelos/session-manager.php";
SessionManager::startSecure();

require_once "../../controladores/factus.controlador.php";
require_once "../../modelos/factus.modelo.php";
require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";
require_once "../../controladores/proveedores.controlador.php";
require_once "../../modelos/proveedores.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";
require_once "../../controladores/bodegas.controlador.php";
require_once "../../modelos/bodegas.modelo.php";

if (!isset($_GET["reporte"])) {
    die("Parámetro inválido.");
}

// Obtener logo de la empresa desde la configuración de Factus
$logoFactus = "";
$stmtLogo = Conexion::conectar()->prepare("SELECT logo_empresa, nombre_empresa, nit_empresa FROM factus_config LIMIT 1");
$stmtLogo->execute();
$configFactus = $stmtLogo->fetch(PDO::FETCH_ASSOC);

if ($configFactus && !empty($configFactus["logo_empresa"])) {
    $logoFactus = "../../" . $configFactus["logo_empresa"];
}

$nombreEmpresa = $configFactus["nombre_empresa"] ?? "SISTEMA POS / FACTURACIÓN ELECTRÓNICA";
$nitEmpresa = $configFactus["nit_empresa"] ?? "";

// Obtener parámetros de la URL
$categoria = $_GET['categoria'] ?? 'todos';
$fecha_inicio = $_GET['fechaInicial'] ?? null;
$fecha_fin = $_GET['fechaFinal'] ?? null;
$tercero = $_GET['tercero'] ?? 'todos';
$idUsuario = $_GET['idUsuario'] ?? 'todos';
$idBodega = $_GET['idBodega'] ?? '';

// Obtener datos del reporte
$reporte = ModeloFactus::mdlMostrarReporteDetallado($fecha_inicio, $fecha_fin, $categoria, $tercero, $idUsuario, $idBodega);

// Totales acumulados
$totalMonto = 0; // Solo suma Facturas Electrónicas
$cantFacturas = 0;
$cantNC = 0;
$cantDS = 0;
$cantNA = 0;

foreach ($reporte as $item) {
    if (stripos($item["tipo"], 'Factura') !== false) {
        $cantFacturas++;
        $totalMonto += floatval($item["monto"]);
    } else if (stripos($item["tipo"], 'Crédito') !== false) {
        $cantNC++;
    } else if (stripos($item["tipo"], 'Soporte') !== false) {
        $cantDS++;
    } else if (stripos($item["tipo"], 'Ajuste') !== false) {
        $cantNA++;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte Facturación Electrónica</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    body {
      margin: 0;
      padding: 20px;
      color: #333;
      font-size: 12px;
      background: #fff;
    }
    .reporte-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #dd4b39;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    .reporte-header .logo img {
      max-height: 60px;
      max-width: 180px;
    }
    .reporte-header .empresa-info {
      text-align: right;
    }
    .reporte-header .empresa-info h2 {
      margin: 0 0 5px 0;
      color: #dd4b39;
      font-size: 18px;
    }
    .reporte-header .empresa-info p {
      margin: 2px 0;
      color: #666;
    }
    .reporte-titulo {
      text-align: center;
      margin-bottom: 20px;
    }
    .reporte-titulo h1 {
      margin: 0;
      font-size: 18px;
      color: #333;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .reporte-titulo p {
      margin: 5px 0 0 0;
      color: #777;
    }
    .kpis-container {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }
    .kpi-box {
      flex: 1;
      background: #f9f9f9;
      border: 1px solid #e5e5e5;
      border-radius: 8px;
      padding: 12px;
      text-align: center;
    }
    .kpi-box .valor {
      font-size: 16px;
      font-weight: bold;
      color: #dd4b39;
    }
    .kpi-box .label {
      font-size: 11px;
      color: #666;
      margin-top: 4px;
    }
    table.tabla-reporte {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    table.tabla-reporte th {
      background-color: #dd4b39;
      color: #fff;
      padding: 8px 10px;
      text-align: left;
      font-size: 11px;
      text-transform: uppercase;
    }
    table.tabla-reporte td {
      padding: 8px 10px;
      border-bottom: 1px solid #eee;
      font-size: 11px;
    }
    table.tabla-reporte tr:nth-child(even) {
      background-color: #fafafa;
    }
    table.tabla-reporte tfoot td {
      font-weight: bold;
      background-color: #f5f5f5;
      border-top: 2px solid #ddd;
    }
    .estado-badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .badge-aceptada { background: #e8f5e9; color: #2e7d32; }
    .badge-enviada { background: #e3f2fd; color: #1565c0; }
    .badge-error { background: #ffebee; color: #c62828; }
    .reporte-footer {
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee;
      padding-top: 10px;
      color: #888;
      font-size: 10px;
      margin-top: 30px;
    }
    @media print {
      body { padding: 0; }
      @page { margin: 1.5cm; }
    }
  </style>
</head>
<body>

  <div class="reporte-header">
    <div class="logo">
      <?php if (!empty($logoFactus) && file_exists($logoFactus)): ?>
        <img src="<?php echo $logoFactus; ?>" alt="Logo">
      <?php else: ?>
        <h3 style="margin:0; color:#dd4b39;"><?php echo htmlspecialchars($nombreEmpresa); ?></h3>
      <?php endif; ?>
    </div>
    <div class="empresa-info">
      <h2><?php echo htmlspecialchars($nombreEmpresa); ?></h2>
      <?php if (!empty($nitEmpresa)): ?>
        <p><strong>NIT:</strong> <?php echo htmlspecialchars($nitEmpresa); ?></p>
      <?php endif; ?>
      <p>Reporte de Facturación Electrónica</p>
    </div>
  </div>

  <div class="reporte-titulo">
    <h1>Consolidado de Documentos Electrónicos</h1>
    <p>
      <?php 
        if ($fecha_inicio && $fecha_fin) {
          echo "Período: " . htmlspecialchars($fecha_inicio) . " al " . htmlspecialchars($fecha_fin);
        } else {
          echo "Período: Todas las fechas";
        }
      ?>
    </p>
  </div>

  <div class="kpis-container">
    <div class="kpi-box">
      <div class="valor"><?php echo count($reporte); ?></div>
      <div class="label">Total Documentos</div>
    </div>
    <div class="kpi-box">
      <div class="valor">$ <?php echo number_format($totalMonto, 0, ',', '.'); ?></div>
      <div class="label">Monto Total Acumulado</div>
    </div>
    <div class="kpi-box">
      <div class="valor"><?php echo $cantFacturas; ?></div>
      <div class="label">Facturas Electrónicas</div>
    </div>
    <div class="kpi-box">
      <div class="valor"><?php echo $cantNC; ?></div>
      <div class="label">Notas Crédito</div>
    </div>
  </div>

  <?php if (empty($reporte)): ?>
    <div style="text-align:center; padding:40px; color:#888;">
      <p>⚠️ No se encontraron documentos electrónicos con los filtros seleccionados.</p>
    </div>
  <?php else: ?>
    <table class="tabla-reporte">
      <thead>
        <tr>
          <th>#</th>
          <th>Tipo Doc.</th>
          <th>Número</th>
          <th>Tercero (Cliente/Proveedor)</th>
          <th>Vendedor</th>
          <th>Fecha</th>
          <th>Monto Total</th>
          <th>Estado DIAN</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reporte as $i => $item): ?>
          <?php 
            $estadoClase = 'badge-aceptada';
            if (strtolower($item["estado"]) === 'enviada') $estadoClase = 'badge-enviada';
            else if (strtolower($item["estado"]) === 'error' || strtolower($item["estado"]) === 'rechazada') $estadoClase = 'badge-error';
          ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><strong><?php echo htmlspecialchars($item["tipo"]); ?></strong></td>
            <td><?php echo htmlspecialchars($item["numero"]); ?></td>
            <td><?php echo htmlspecialchars($item["tercero"]); ?></td>
            <td><?php echo htmlspecialchars($item["vendedor"]); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($item["fecha"])); ?></td>
            <td>$ <?php echo number_format($item["monto"], 0, ',', '.'); ?></td>
            <td><span class="estado-badge <?php echo $estadoClase; ?>"><?php echo htmlspecialchars($item["estado"]); ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6" style="text-align:right;">TOTAL ACUMULADO:</td>
          <td colspan="2">$ <?php echo number_format($totalMonto, 0, ',', '.'); ?></td>
        </tr>
      </tfoot>
    </table>
  <?php endif; ?>

  <div class="reporte-footer">
    <span>Sistema POS — Reporte de Facturación Electrónica DIAN</span>
    <span><?php echo date('d/m/Y H:i:s'); ?></span>
  </div>

  <script>
    window.addEventListener('load', function () {
      setTimeout(function () {
        window.print();
      }, 500);
    });
  </script>
</body>
</html>
