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
    $id_bodega = $_POST['id_bodega'] ?? null;

    // =============================================
    // CONDICIÓN DE FECHA PARA REGISTROS ACTUALES
    // =============================================
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

    // Filtro de sucursal
    $whereBodega = "";
    if (!empty($id_bodega) && $id_bodega !== 'todos') {
        $whereBodega = " AND id_bodega = :id_bodega";
    }

    // Helper para ejecutar consultas
    function ejecutarConteo($conn, $sql, $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega) {
        $stmt = $conn->prepare($sql);
        if ($usaParametrosFecha) {
            $stmt->bindValue(':fecha_inicio', $fecha_inicio);
            $stmt->bindValue(':fecha_fin', $fecha_fin);
        }
        if (!empty($id_bodega) && $id_bodega !== 'todos') {
            $stmt->bindValue(':id_bodega', $id_bodega, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // =============================================
    // BLOQUE 1: ÓRDENES PENDIENTES (estado = 'orden')
    // =============================================

    $pendientesTotal = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas WHERE estado = 'orden' AND $condicionFecha $whereBodega",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    $pendientesManuales = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'orden' AND $condicionFecha $whereBodega
         AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    $pendientesIA = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'orden' AND $condicionFecha $whereBodega
         AND extra LIKE '%n8n%'",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    // =============================================
    // BLOQUE 2: ÓRDENES CONVERTIDAS
    // =============================================

    // CASO A: En-sitio - orden_compra == codigo (misma fila convertida)
    $convertidasManualesEnSitio = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra = codigo
         AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')
         AND $condicionFecha $whereBodega",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    $convertidasIAEnSitio = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra = codigo
         AND extra LIKE '%n8n%'
         AND $condicionFecha $whereBodega",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    // CASO B: FE - orden_compra != codigo (nueva fila)
    $convertidasManualesFE = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra != codigo
         AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')
         AND $condicionFecha $whereBodega",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    $convertidasIAFE = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra != codigo
         AND extra LIKE '%n8n%'
         AND $condicionFecha $whereBodega",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    // FALLBACK HISTÓRICO: IA sin orden_compra
    $convertidasIAFallback = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND extra LIKE '%n8n%'
         AND (orden_compra IS NULL OR orden_compra = '')
         AND $condicionFecha $whereBodega",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin, $id_bodega
    );

    // Totales por origen
    $convertidasManuales = $convertidasManualesEnSitio + $convertidasManualesFE;
    $convertidasIA       = $convertidasIAEnSitio + $convertidasIAFE + $convertidasIAFallback;

    // =============================================
    // BLOQUE 3: CALCULAR TOTALES
    // =============================================

    $totalManuales    = $pendientesManuales + $convertidasManuales;
    $totalIA          = $pendientesIA + $convertidasIA;
    $totalGeneral     = $totalManuales + $totalIA;
    $totalConvertidas = $convertidasManuales + $convertidasIA;

    $tasaConversionGeneral = $totalGeneral > 0
        ? round(($totalConvertidas / $totalGeneral) * 100, 1)
        : 0;

    // =============================================
    // RESPUESTA JSON
    // =============================================

    echo json_encode([
        'totales' => [
            'pendientes_total'    => $pendientesTotal,
            'pendientes_manuales' => $pendientesManuales,
            'pendientes_ia'       => $pendientesIA,
            'tasa_conversion'     => $tasaConversionGeneral
        ],
        'origen' => [
            'manuales' => $totalManuales,
            'ia'       => $totalIA
        ],
        'conversion' => [
            'manuales' => [
                'total'      => $totalManuales,
                'convertidas'=> $convertidasManuales,
                'pendientes' => $pendientesManuales
            ],
            'ia' => [
                'total'      => $totalIA,
                'convertidas'=> $convertidasIA,
                'pendientes' => $pendientesIA
            ],
            'tasa_general' => $tasaConversionGeneral
        ],
        'debug' => [
            'convertidas_manuales_en_sitio' => $convertidasManualesEnSitio,
            'convertidas_manuales_fe'       => $convertidasManualesFE,
            'convertidas_ia_en_sitio'       => $convertidasIAEnSitio,
            'convertidas_ia_fe'             => $convertidasIAFE,
            'convertidas_ia_fallback'       => $convertidasIAFallback,
            'pendientes_total'              => $pendientesTotal,
            'id_bodega_recibido'            => $id_bodega,
            'where_bodega_generado'         => $whereBodega
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error de base de datos",
        "message" => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Error del servidor",
        "message" => $e->getMessage()
    ]);
}