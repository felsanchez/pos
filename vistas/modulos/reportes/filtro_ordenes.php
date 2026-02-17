<?php
require_once "../../../modelos/conexion.php";

header('Content-Type: application/json');

try {
  $conn = Conexion::conectar();
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Obtener valores del formulario
  $tipo = $_POST['tipo'] ?? 'mes';
  $fecha_inicio = $_POST['fecha_inicio'] ?? null;
  $fecha_fin = $_POST['fecha_fin'] ?? null;

  // Construir la condición de fecha
  $condicionFecha = "";
  $usaParametrosFecha = false;

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
        http_response_code(400);
        echo json_encode(["error" => "Fechas personalizadas incompletas"]);
        exit;
      }
      $condicionFecha = "DATE(fecha) BETWEEN :fecha_inicio AND :fecha_fin";
      $usaParametrosFecha = true;
      break;
    default:
      $condicionFecha = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
  }

  // =============================================
  // ÓRDENES PENDIENTES (estado = 'orden')
  // =============================================

  // Total pendientes
  $sql = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'orden' AND $condicionFecha";
  $stmt = $conn->prepare($sql);
  if ($usaParametrosFecha) {
    $stmt->bindValue(':fecha_inicio', $fecha_inicio);
    $stmt->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmt->execute();
  $pendientesTotal = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Pendientes manuales (no tienen n8n en extra)
  $sql = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'orden' AND $condicionFecha AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')";
  $stmt = $conn->prepare($sql);
  if ($usaParametrosFecha) {
    $stmt->bindValue(':fecha_inicio', $fecha_inicio);
    $stmt->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmt->execute();
  $pendientesManuales = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Pendientes IA (tienen n8n en extra)
  $sql = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'orden' AND $condicionFecha AND extra LIKE '%n8n%'";
  $stmt = $conn->prepare($sql);
  if ($usaParametrosFecha) {
    $stmt->bindValue(':fecha_inicio', $fecha_inicio);
    $stmt->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmt->execute();
  $pendientesIA = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // =============================================
  // ÓRDENES CONVERTIDAS
  // Busca: "Desde orden", "Desde Agente IA", "Origen = orden" (formato antiguo)
  // =============================================

  // Convertidas manuales (tienen "orden" en notas pero NO tienen n8n en extra)
  $sql = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'venta' AND (notas LIKE '%Desde orden%' OR notas LIKE '%Origen = orden%') AND $condicionFecha AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')";
  $stmt = $conn->prepare($sql);
  if ($usaParametrosFecha) {
    $stmt->bindValue(':fecha_inicio', $fecha_inicio);
    $stmt->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmt->execute();
  $convertidasManuales = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Convertidas IA (tienen "Agente IA" en notas O tienen n8n en extra)
  $sql = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'venta' AND (notas LIKE '%Desde Agente IA%' OR (notas LIKE '%orden%' AND extra LIKE '%n8n%')) AND $condicionFecha";
  $stmt = $conn->prepare($sql);
  if ($usaParametrosFecha) {
    $stmt->bindValue(':fecha_inicio', $fecha_inicio);
    $stmt->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmt->execute();
  $convertidasIA = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // =============================================
  // CALCULAR TOTALES Y TASAS
  // =============================================

  // Total de órdenes creadas por tipo (pendientes + convertidas)
  $totalManuales = $pendientesManuales + $convertidasManuales;
  $totalIA = $pendientesIA + $convertidasIA;
  $totalGeneral = $totalManuales + $totalIA;
  $totalConvertidas = $convertidasManuales + $convertidasIA;

  // Tasa de conversión general
  $tasaConversionGeneral = $totalGeneral > 0
    ? round(($totalConvertidas / $totalGeneral) * 100, 1)
    : 0;

  // =============================================
  // RESPUESTA JSON
  // =============================================

  // Debug: contar todas las ventas que vinieron de órdenes
  $sqlDebug = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'venta' AND (notas LIKE '%orden%' OR notas LIKE '%Agente IA%')";
  $stmtDebug = $conn->prepare($sqlDebug);
  $stmtDebug->execute();
  $debugTotalConvertidas = (int) $stmtDebug->fetch(PDO::FETCH_ASSOC)['total'];

  // Debug: ver cuántas tienen extra con n8n
  $sqlDebug2 = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'venta' AND (notas LIKE '%orden%' OR notas LIKE '%Agente IA%') AND extra LIKE '%n8n%'";
  $stmtDebug2 = $conn->prepare($sqlDebug2);
  $stmtDebug2->execute();
  $debugConExtraN8n = (int) $stmtDebug2->fetch(PDO::FETCH_ASSOC)['total'];

  // Debug: ver cuántas tienen extra NULL o vacío
  $sqlDebug3 = "SELECT COUNT(*) as total FROM ventas WHERE estado = 'venta' AND (notas LIKE '%orden%' OR notas LIKE '%Agente IA%') AND (extra IS NULL OR extra = '')";
  $stmtDebug3 = $conn->prepare($sqlDebug3);
  $stmtDebug3->execute();
  $debugConExtraNull = (int) $stmtDebug3->fetch(PDO::FETCH_ASSOC)['total'];

  echo json_encode([
    'totales' => [
      'pendientes_total' => $pendientesTotal,
      'pendientes_manuales' => $pendientesManuales,
      'pendientes_ia' => $pendientesIA,
      'tasa_conversion' => $tasaConversionGeneral
    ],
    'origen' => [
      'manuales' => $totalManuales,
      'ia' => $totalIA
    ],
    'conversion' => [
      'manuales' => [
        'total' => $totalManuales,
        'convertidas' => $convertidasManuales,
        'pendientes' => $pendientesManuales
      ],
      'ia' => [
        'total' => $totalIA,
        'convertidas' => $convertidasIA,
        'pendientes' => $pendientesIA
      ],
      'tasa_general' => $tasaConversionGeneral
    ],
    'debug' => [
      'total_convertidas_historico' => $debugTotalConvertidas,
      'con_extra_n8n' => $debugConExtraN8n,
      'con_extra_null' => $debugConExtraNull
    ]
  ]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    "error" => "Error de base de datos",
    "message" => $e->getMessage()
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    "error" => "Error del servidor",
    "message" => $e->getMessage()
  ]);
}