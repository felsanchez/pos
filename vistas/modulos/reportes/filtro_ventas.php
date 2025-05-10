<?php
require_once "../../../modelos/conexion.php"; // Ajusta la ruta según tu estructura
$conn = Conexion::conectar(); // Asegúrate de tener esta línea



// Obtener valores del formulario
$tipo = $_POST['tipo'] ?? null;
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;

// Validación básica
if (!$tipo) {
  http_response_code(400);
  echo json_encode(["error" => "Tipo de fecha no especificado"]);
  exit;
}

// Construir la condición de fecha
$where = "";
$params = [];

switch ($tipo) {
  case 'hoy':
    $where = "DATE(fecha) = CURDATE()";
    break;
  case 'ayer':
    $where = "DATE(fecha) = CURDATE() - INTERVAL 1 DAY";
    break;
  case 'mes':
    $where = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
    break;
  case 'personalizado':
    if (!$fecha_inicio || !$fecha_fin) {
      http_response_code(400);
      echo json_encode(["error" => "Fechas personalizadas incompletas"]);
      exit;
    }
    $where = "DATE(fecha) BETWEEN ? AND ?";
    $params = [$fecha_inicio, $fecha_fin];
    break;
  default:
    http_response_code(400);
    echo json_encode(["error" => "Tipo de filtro no válido"]);
    exit;
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

if ($params) {
  foreach ($params as $key => $value) {
    $stmt->bindValue($key + 1, $value); // Los parámetros en PDO son 1-indexados si usas ?
  }
}

$stmt->execute();

$datos = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $datos[] = [
    'fecha' => $row['fecha'],
    'total_ventas' => (float)$row['total_ventas']
  ];
}


////MOSTRAR EL VALOR TOTAL
$totalVentas = array_sum(array_column($datos, 'total_ventas'));

echo json_encode([
  'datos' => $datos,
  'total' => $totalVentas
]);
