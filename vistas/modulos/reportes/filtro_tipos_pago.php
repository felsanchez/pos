<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $fechaInicio = $_POST["fecha_inicio"];
  $fechaFin = $_POST["fecha_fin"];

  try {
    $conn = new PDO("mysql:host=localhost;dbname=pos", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
      SELECT metodo_pago, SUM(total) AS total
      FROM ventas
      WHERE estado = 'venta'
        AND DATE(fecha) BETWEEN :inicio AND :fin
      GROUP BY metodo_pago
    ");

    $stmt->bindParam(":inicio", $fechaInicio);
    $stmt->bindParam(":fin", $fechaFin);
    $stmt->execute();

    $resultados = []; 
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Extraer la parte antes del guion (por ejemplo: "TC" de "TC-123")
        $metodoBase = explode('-', $row["metodo_pago"])[0];

        // Sumar al método base
        if (!isset($resultados[$metodoBase])) {
            $resultados[$metodoBase] = 0;
        }
        $resultados[$metodoBase] += (float) $row["total"];
    }

    header("Content-Type: application/json");
    echo json_encode($resultados);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al conectar a la base de datos"]);
  }

} else {
  http_response_code(405);
  echo "Método no permitido";
}

