<?php
require_once "../../../modelos/conexion.php";
$conn = Conexion::conectar();

// Obtener valores del formulario
$tipo = $_POST['tipo'] ?? null;
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;

// Nuevos filtros
$id_vendedor = $_POST['id_vendedor'] ?? null;
$id_cliente = $_POST['id_cliente'] ?? null;
$id_producto = $_POST['id_producto'] ?? null;
$metodo_pago = $_POST['metodo_pago'] ?? null;
$id_bodega = $_POST['id_bodega'] ?? null;

// Validación básica
if (!$tipo) {
  http_response_code(400);
  echo json_encode(["error" => "Tipo de fecha no especificado"]);
  exit;
}

// Construir la condición de fecha
$condicionFecha = "";
$fechaParams = [];

switch ($tipo) {
  case 'todo':
    $condicionFecha = "1=1"; // Sin filtro de fecha
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
    $condicionFecha = "DATE(fecha) BETWEEN ? AND ?";
    $fechaParams[] = $fecha_inicio;
    $fechaParams[] = $fecha_fin;
    break;
  default:
    http_response_code(400);
    echo json_encode(["error" => "Tipo de filtro no válido"]);
    exit;
}

// 2. Construir filtros adicionales comunes
$filtrosComunes = "";
$filtroParams = [];

if (!empty($id_vendedor)) {
  $filtrosComunes .= " AND id_vendedor = ?";
  $filtroParams[] = $id_vendedor;
}

if (!empty($id_cliente)) {
  $filtrosComunes .= " AND id_cliente = ?";
  $filtroParams[] = $id_cliente;
}

if (!empty($id_bodega) && $id_bodega !== 'todos') {
  $filtrosComunes .= " AND id_bodega = ?";
  $filtroParams[] = $id_bodega;
}

if (!empty($id_producto)) {
  // El campo productos es un JSON, buscamos si contiene el id del producto
  $filtrosComunes .= " AND (productos LIKE ? OR productos LIKE ? OR productos LIKE ?)";
  $filtroParams[] = '%"id":"' . $id_producto . '"%';
  $filtroParams[] = '%"id":' . $id_producto . ',%';
  $filtroParams[] = '%"id":' . $id_producto . '}%';
}

// Para las ventas también aplica el método de pago
$filtrosVentasExtra = "";
$ventasExtraParams = [];
if (!empty($metodo_pago)) {
  $filtrosVentasExtra .= " AND (metodo_pago = ? OR metodo_pago LIKE ?)";
  $ventasExtraParams[] = $metodo_pago;
  $ventasExtraParams[] = $metodo_pago . '-%';
}

// 3. Unir condiciones para VENTAS (Incluir ventas POS y FE firmadas/enviadas, excluir borradores pendientes)
$whereVentas = "estado = 'venta' AND ( ( (resolucion_id IS NULL OR resolucion_id = 0) AND (estado_dian IS NULL OR estado_dian = '') ) OR ( resolucion_id IS NOT NULL AND resolucion_id != 0 AND estado_dian IN ('aceptada', 'enviada') ) ) AND $condicionFecha" . $filtrosComunes . $filtrosVentasExtra;
$paramsVentas = array_merge($fechaParams, $filtroParams, $ventasExtraParams);

// 4. Unir condiciones para ORDENES
$whereOrdenes = "estado = 'orden' AND $condicionFecha" . $filtrosComunes;
$paramsOrdenes = array_merge($fechaParams, $filtroParams);

// --- A. CONSULTA DE VENTAS AGRUPADAS POR FECHA (Línea de evolución) ---
$sqlGrouped = "
  SELECT
    DATE(fecha) as fecha,
    SUM(total) as total_ventas
  FROM ventas
  WHERE $whereVentas
  GROUP BY DATE(fecha)
  ORDER BY fecha ASC
";

$stmtGrouped = $conn->prepare($sqlGrouped);
foreach ($paramsVentas as $i => $val) {
  $stmtGrouped->bindValue($i + 1, $val);
}
$stmtGrouped->execute();

$datos = [];
while ($row = $stmtGrouped->fetch(PDO::FETCH_ASSOC)) {
  $datos[] = [
    'fecha' => $row['fecha'],
    'total_ventas' => (float)$row['total_ventas']
  ];
}

// --- B. CONSULTA DE DETALLES DE VENTAS (Métricas y Productos Top 10) ---
$sqlDetails = "
  SELECT total, productos, id_cliente
  FROM ventas
  WHERE $whereVentas
";

$stmtDetails = $conn->prepare($sqlDetails);
foreach ($paramsVentas as $i => $val) {
  $stmtDetails->bindValue($i + 1, $val);
}
$stmtDetails->execute();

$totalVentasMonto = 0;
$totalVentasCantidad = 0;
$productosVendidos = [];
$clientesActivos = [];

while ($row = $stmtDetails->fetch(PDO::FETCH_ASSOC)) {
  $totalVentasMonto += (float)$row['total'];
  $totalVentasCantidad++;
  
  if (!empty($row['id_cliente'])) {
    $clientesActivos[$row['id_cliente']] = true;
  }

  $listaProductos = json_decode($row['productos'], true);
  if (is_array($listaProductos)) {
    foreach ($listaProductos as $p) {
      if (isset($p['id']) && isset($p['cantidad'])) {
        $idProd = $p['id'];
        $cant = (float)$p['cantidad'];
        if (!isset($productosVendidos[$idProd])) {
          $productosVendidos[$idProd] = 0;
        }
        $productosVendidos[$idProd] += $cant;
      }
    }
  }
}

// Ordenar productos de forma descendente y tomar los 10 primeros
arsort($productosVendidos);
$productosVendidosTop10 = array_slice($productosVendidos, 0, 10, true);

$productosTopDetalle = [];
if (!empty($productosVendidosTop10)) {
  $placeholders = implode(',', array_fill(0, count($productosVendidosTop10), '?'));
  $sqlProd = "SELECT id, descripcion, imagen FROM productos WHERE id IN ($placeholders)";
  $stmtProd = $conn->prepare($sqlProd);
  
  $i = 1;
  foreach (array_keys($productosVendidosTop10) as $idProd) {
    $stmtProd->bindValue($i++, $idProd, PDO::PARAM_INT);
  }
  $stmtProd->execute();
  $prodRows = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

  // Mapear por ID para mantener el orden de ventas
  $prodMap = [];
  foreach ($prodRows as $pRow) {
    $prodMap[$pRow['id']] = $pRow;
  }

  foreach ($productosVendidosTop10 as $idProd => $cant) {
    if (isset($prodMap[$idProd])) {
      $productosTopDetalle[] = [
        'id' => $idProd,
        'descripcion' => $prodMap[$idProd]['descripcion'],
        'imagen' => $prodMap[$idProd]['imagen'],
        'ventas_acumuladas' => $cant
      ];
    }
  }
}

// --- C. CONSULTA DE CANTIDAD DE ORDENES ---
$sqlOrdenes = "
  SELECT COUNT(*) as total_ordenes
  FROM ventas
  WHERE $whereOrdenes
";
$stmtOrdenes = $conn->prepare($sqlOrdenes);
foreach ($paramsOrdenes as $i => $val) {
  $stmtOrdenes->bindValue($i + 1, $val);
}
$stmtOrdenes->execute();
$resOrdenes = $stmtOrdenes->fetch(PDO::FETCH_ASSOC);
$totalOrdenes = $resOrdenes ? (int)$resOrdenes['total_ordenes'] : 0;

$ticketPromedio = $totalVentasCantidad > 0 ? ($totalVentasMonto / $totalVentasCantidad) : 0;
$totalClientesActivos = count($clientesActivos);

// Respuesta JSON unificada
echo json_encode([
  'datos' => $datos,
  'total' => $totalVentasMonto,
  'total_ventas_cantidad' => $totalVentasCantidad,
  'ticket_promedio' => $ticketPromedio,
  'total_ordenes' => $totalOrdenes,
  'total_clientes_activos' => $totalClientesActivos,
  'productos_top' => $productosTopDetalle
]);