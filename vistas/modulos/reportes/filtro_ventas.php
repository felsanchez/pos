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

// Validación básica
if (!$tipo) {
  http_response_code(400);
  echo json_encode(["error" => "Tipo de fecha no especificado"]);
  exit;
}

// Construir la condición de fecha
$condicionFecha = "";
$params = [];
$paramIndex = 1;

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
    $params[$paramIndex++] = $fecha_inicio;
    $params[$paramIndex++] = $fecha_fin;
    break;
  default:
    http_response_code(400);
    echo json_encode(["error" => "Tipo de filtro no válido"]);
    exit;
}

// Agregar condición del estado
$where = "estado = 'venta' AND $condicionFecha";

// Agregar filtros adicionales
if (!empty($id_vendedor)) {
  $where .= " AND id_vendedor = ?";
  $params[$paramIndex++] = $id_vendedor;
}

if (!empty($id_cliente)) {
  $where .= " AND id_cliente = ?";
  $params[$paramIndex++] = $id_cliente;
}

if (!empty($metodo_pago)) {
  // Usar LIKE porque el método de pago puede incluir código de transacción (ej: "Tarjeta-ABC123")
  $where .= " AND (metodo_pago = ? OR metodo_pago LIKE ?)";
  $params[$paramIndex++] = $metodo_pago;
  $params[$paramIndex++] = $metodo_pago . '-%';
}

// Filtro por producto (buscar en el campo JSON de productos)
if (!empty($id_producto)) {
  // El campo productos es un JSON, buscamos si contiene el id del producto
  $where .= " AND (productos LIKE ? OR productos LIKE ? OR productos LIKE ?)";
  $params[$paramIndex++] = '%"id":"' . $id_producto . '"%';
  $params[$paramIndex++] = '%"id":' . $id_producto . ',%';
  $params[$paramIndex++] = '%"id":' . $id_producto . '}%';
}

// Consulta preparada
$sql = "
  SELECT
    DATE(fecha) as fecha,
    SUM(total) as total_ventas
  FROM ventas
  WHERE $where
  GROUP BY DATE(fecha)
  ORDER BY fecha ASC
";

$stmt = $conn->prepare($sql);

// Vincular parámetros
if (!empty($params)) {
  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
  }
}

$stmt->execute();

// Procesar resultados
$datos = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $datos[] = [
    'fecha' => $row['fecha'],
    'total_ventas' => (float)$row['total_ventas']
  ];
}

// Total general
$totalVentas = array_sum(array_column($datos, 'total_ventas'));

// Respuesta JSON
echo json_encode([
  'datos' => $datos,
  'total' => $totalVentas
]);