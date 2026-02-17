<?php
require_once "../../../modelos/conexion.php";

header('Content-Type: application/json');

try {
  $conn = Conexion::conectar();
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Obtener valores del formulario
  $tipo = $_POST['tipo'] ?? null;
  $fecha_inicio = $_POST['fecha_inicio'] ?? null;
  $fecha_fin = $_POST['fecha_fin'] ?? null;
  $id_categoria = isset($_POST['id_categoria']) && $_POST['id_categoria'] !== '' ? (int)$_POST['id_categoria'] : null;

  // Validación básica
  if (!$tipo) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo de fecha no especificado"]);
    exit;
  }

  // Construir la condición de fecha para ventas y gastos
  // Nota: ventas usa DATE(fecha) porque almacena datetime, gastos usa fecha directamente
  $condicionFechaVentas = "";
  $condicionFechaGastos = "";
  $condicionFechaGastosAlias = ""; // Para consultas con alias g.
  $usaParametrosFecha = false;

  switch ($tipo) {
    case 'todo':
      $condicionFechaVentas = "1=1";
      $condicionFechaGastos = "1=1";
      $condicionFechaGastosAlias = "1=1";
      break;
    case 'hoy':
      $condicionFechaVentas = "DATE(fecha) = CURDATE()";
      $condicionFechaGastos = "fecha = CURDATE()";
      $condicionFechaGastosAlias = "g.fecha = CURDATE()";
      break;
    case 'ayer':
      $condicionFechaVentas = "DATE(fecha) = CURDATE() - INTERVAL 1 DAY";
      $condicionFechaGastos = "fecha = CURDATE() - INTERVAL 1 DAY";
      $condicionFechaGastosAlias = "g.fecha = CURDATE() - INTERVAL 1 DAY";
      break;
    case 'mes':
      $condicionFechaVentas = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
      $condicionFechaGastos = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
      $condicionFechaGastosAlias = "MONTH(g.fecha) = MONTH(CURDATE()) AND YEAR(g.fecha) = YEAR(CURDATE())";
      break;
    case 'personalizado':
      if (!$fecha_inicio || !$fecha_fin) {
        http_response_code(400);
        echo json_encode(["error" => "Fechas personalizadas incompletas"]);
        exit;
      }
      $condicionFechaVentas = "DATE(fecha) BETWEEN :fecha_inicio AND :fecha_fin";
      $condicionFechaGastos = "fecha BETWEEN :fecha_inicio AND :fecha_fin";
      $condicionFechaGastosAlias = "g.fecha BETWEEN :fecha_inicio AND :fecha_fin";
      $usaParametrosFecha = true;
      break;
    default:
      http_response_code(400);
      echo json_encode(["error" => "Tipo de filtro no válido"]);
      exit;
  }

  // =============================================
  // TOTAL DE INGRESOS (VENTAS)
  // =============================================
  $sqlIngresos = "SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE estado = 'venta' AND $condicionFechaVentas";
  $stmtIngresos = $conn->prepare($sqlIngresos);
  if ($usaParametrosFecha) {
    $stmtIngresos->bindValue(':fecha_inicio', $fecha_inicio);
    $stmtIngresos->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmtIngresos->execute();
  $totalIngresos = (float) $stmtIngresos->fetch(PDO::FETCH_ASSOC)['total'];

  // =============================================
  // TOTAL DE GASTOS (con filtro de categoría)
  // =============================================
  $whereGastos = "estado = 'aprobado' AND $condicionFechaGastos";

  // Filtro por categoría para el total
  if ($id_categoria !== null) {
    $whereGastos .= " AND id_categoria_gasto = :id_categoria";
  }

  $sqlGastos = "SELECT COALESCE(SUM(monto), 0) as total FROM gastos WHERE $whereGastos";
  $stmtGastos = $conn->prepare($sqlGastos);
  if ($usaParametrosFecha) {
    $stmtGastos->bindValue(':fecha_inicio', $fecha_inicio);
    $stmtGastos->bindValue(':fecha_fin', $fecha_fin);
  }
  if ($id_categoria !== null) {
    $stmtGastos->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
  }
  $stmtGastos->execute();
  $totalGastos = (float) $stmtGastos->fetch(PDO::FETCH_ASSOC)['total'];

  // Calcular utilidad
  $utilidad = $totalIngresos - $totalGastos;

  // =============================================
  // EVOLUCIÓN TEMPORAL (INGRESOS VS GASTOS POR DÍA)
  // =============================================
  $evolucion = [];

  // Obtener ingresos por día
  $sqlEvolucionIngresos = "
    SELECT DATE(fecha) as fecha, COALESCE(SUM(total), 0) as total
    FROM ventas
    WHERE estado = 'venta' AND $condicionFechaVentas
    GROUP BY DATE(fecha)
    ORDER BY fecha ASC
  ";
  $stmtEvolucionIngresos = $conn->prepare($sqlEvolucionIngresos);
  if ($usaParametrosFecha) {
    $stmtEvolucionIngresos->bindValue(':fecha_inicio', $fecha_inicio);
    $stmtEvolucionIngresos->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmtEvolucionIngresos->execute();
  $ingresosEvolucion = [];
  while ($row = $stmtEvolucionIngresos->fetch(PDO::FETCH_ASSOC)) {
    $ingresosEvolucion[$row['fecha']] = (float) $row['total'];
  }

  // Obtener gastos por día (con filtro de categoría si se especifica)
  $whereEvolucionGastos = "estado = 'aprobado' AND $condicionFechaGastos";
  if ($id_categoria !== null) {
    $whereEvolucionGastos .= " AND id_categoria_gasto = :id_categoria";
  }

  $sqlEvolucionGastos = "
    SELECT DATE(fecha) as fecha, COALESCE(SUM(monto), 0) as total
    FROM gastos
    WHERE $whereEvolucionGastos
    GROUP BY DATE(fecha)
    ORDER BY fecha ASC
  ";
  $stmtEvolucionGastos = $conn->prepare($sqlEvolucionGastos);
  if ($usaParametrosFecha) {
    $stmtEvolucionGastos->bindValue(':fecha_inicio', $fecha_inicio);
    $stmtEvolucionGastos->bindValue(':fecha_fin', $fecha_fin);
  }
  if ($id_categoria !== null) {
    $stmtEvolucionGastos->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
  }
  $stmtEvolucionGastos->execute();
  $gastosEvolucion = [];
  while ($row = $stmtEvolucionGastos->fetch(PDO::FETCH_ASSOC)) {
    $gastosEvolucion[$row['fecha']] = (float) $row['total'];
  }

  // Combinar fechas únicas
  $todasFechas = array_unique(array_merge(array_keys($ingresosEvolucion), array_keys($gastosEvolucion)));
  sort($todasFechas);

  foreach ($todasFechas as $fecha) {
    $evolucion[] = [
      'fecha' => $fecha,
      'ingresos' => $ingresosEvolucion[$fecha] ?? 0,
      'gastos' => $gastosEvolucion[$fecha] ?? 0
    ];
  }

  // =============================================
  // GASTOS POR CATEGORÍA
  // =============================================
  // La gráfica de dona siempre muestra todas las categorías para el período seleccionado
  // (sin filtro de categoría específica, para mostrar la distribución completa)
  $sqlGastosCategoria = "
    SELECT c.nombre, c.color, COALESCE(SUM(g.monto), 0) as total
    FROM gastos g
    INNER JOIN categorias_gastos c ON g.id_categoria_gasto = c.id
    WHERE g.estado = 'aprobado' AND $condicionFechaGastosAlias
    GROUP BY g.id_categoria_gasto, c.nombre, c.color
    ORDER BY total DESC
  ";

  $stmtGastosCategoria = $conn->prepare($sqlGastosCategoria);
  if ($usaParametrosFecha) {
    $stmtGastosCategoria->bindValue(':fecha_inicio', $fecha_inicio);
    $stmtGastosCategoria->bindValue(':fecha_fin', $fecha_fin);
  }
  $stmtGastosCategoria->execute();
  $gastosCategoria = $stmtGastosCategoria->fetchAll(PDO::FETCH_ASSOC);

  // Convertir totales a float para consistencia
  foreach ($gastosCategoria as &$cat) {
    $cat['total'] = (float) $cat['total'];
  }
  unset($cat);

  // =============================================
  // RESPUESTA JSON
  // =============================================
  echo json_encode([
    'totales' => [
      'ingresos' => $totalIngresos,
      'gastos' => $totalGastos,
      'utilidad' => $utilidad
    ],
    'evolucion' => $evolucion,
    'gastos_categoria' => $gastosCategoria
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