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

    // =============================================
    // CONDICIÓN DE FECHA PARA REGISTROS ACTUALES
    // (aplica a la fecha del registro en sí)
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

    // =============================================
    // CONDICIÓN DE FECHA PARA LA ORDEN ORIGINAL
    // (aplica a la fecha de la orden referenciada
    //  mediante orden_compra → se busca en la misma tabla)
    // =============================================
    $condicionFechaOrden = str_replace("fecha", "orig.fecha", $condicionFecha);

    // =============================================
    // MODELO DE DATOS:
    //
    // Una orden puede convertirse de dos formas:
    //
    // A) En-sitio (editar-orden → guardar como venta):
    //    - La MISMA fila cambia estado='orden' → estado='venta'
    //    - Se asigna orden_compra = codigo (fix del controlador)
    //    - La fecha del registro se actualiza a la fecha de conversión
    //    → Filtrar por fecha del registro (fecha de conversión ≈ fecha creación, mismo día normalmente)
    //
    // B) Vía Factura Electrónica (orden-a-factura-electronica):
    //    - La orden original SE ELIMINA
    //    - Se crea una NUEVA fila con:
    //        código nuevo (de Factus), fecha = hoy, orden_compra = código_orden_original
    //    - La fecha del nuevo registro es HOY, no la de la orden original
    //    → Para filtrar correctamente, necesitamos filtrar por fecha de la ORDEN ORIGINAL
    //      Pero como la orden fue eliminada, NO podemos hacer JOIN.
    //      La solución: filtrar las convertidas FE por la fecha de la NUEVA venta (fecha actual),
    //      y compensar en la lógica de "Total Creadas":
    //      Total Creadas = Pendientes_en_período + Convertidas_en_período (misma lógica de fechas)
    //
    // En ambos casos, "Total Creadas" = pendientes + convertidas dentro del período.
    // Esto es consistente: si conviertes una orden de este mes, sigue contando en "este mes".
    // =============================================

    // Helper para ejecutar consultas
    function ejecutarConteo($conn, $sql, $usaParametrosFecha, $fecha_inicio, $fecha_fin) {
        $stmt = $conn->prepare($sql);
        if ($usaParametrosFecha) {
            $stmt->bindValue(':fecha_inicio', $fecha_inicio);
            $stmt->bindValue(':fecha_fin', $fecha_fin);
        }
        $stmt->execute();
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // =============================================
    // BLOQUE 1: ÓRDENES PENDIENTES (estado = 'orden')
    // =============================================

    $pendientesTotal = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas WHERE estado = 'orden' AND $condicionFecha",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    $pendientesManuales = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'orden' AND $condicionFecha
         AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    $pendientesIA = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'orden' AND $condicionFecha
         AND extra LIKE '%n8n%'",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    // =============================================
    // BLOQUE 2: ÓRDENES CONVERTIDAS
    //
    // CASO A - Conversión en-sitio (orden→venta, misma fila):
    //   orden_compra = codigo (mismo valor en la misma fila)
    //   Filtro: fecha del registro actual (es la misma fila)
    //
    // CASO B - Conversión a FE (nueva fila, orden original eliminada):
    //   orden_compra = <código_orden_original> (valor diferente al código actual)
    //   Filtro: fecha del NUEVO registro (ya que la orden fue borrada)
    //   Para distinguir CASO A de CASO B: verificar si orden_compra != codigo
    //
    // FALLBACK HISTÓRICO - IA sin orden_compra (pre-fix):
    //   extra='n8n', orden_compra vacío
    //   Filtro: fecha del registro actual
    // =============================================

    // CASO A: En-sitio - orden_compra == codigo (misma fila convertida)
    // Manuales en-sitio
    $convertidasManualesEnSitio = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra = codigo
         AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')
         AND $condicionFecha",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    // IA en-sitio (post-fix)
    $convertidasIAEnSitio = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra = codigo
         AND extra LIKE '%n8n%'
         AND $condicionFecha",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    // CASO B: FE - orden_compra != codigo (nueva fila, la orden fue eliminada)
    // Filtro por fecha del NUEVO registro (hoy/período actual)
    // Manuales FE
    $convertidasManualesFE = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra != codigo
         AND (extra NOT LIKE '%n8n%' OR extra IS NULL OR extra = '')
         AND $condicionFecha",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    // IA FE (orden IA convertida a FE)
    $convertidasIAFE = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND orden_compra IS NOT NULL AND orden_compra != ''
         AND orden_compra != codigo
         AND extra LIKE '%n8n%'
         AND $condicionFecha",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    // FALLBACK HISTÓRICO: IA sin orden_compra (anteriores al fix del controlador)
    $convertidasIAFallback = ejecutarConteo(
        $conn,
        "SELECT COUNT(*) as total FROM ventas
         WHERE estado = 'venta'
         AND extra LIKE '%n8n%'
         AND (orden_compra IS NULL OR orden_compra = '')
         AND $condicionFecha",
        $usaParametrosFecha, $fecha_inicio, $fecha_fin
    );

    // Totales por origen
    $convertidasManuales = $convertidasManualesEnSitio + $convertidasManualesFE;
    $convertidasIA       = $convertidasIAEnSitio + $convertidasIAFE + $convertidasIAFallback;

    // =============================================
    // BLOQUE 3: CALCULAR TOTALES
    // Total creadas = Pendientes + Convertidas (mismo período)
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
            'pendientes_total'              => $pendientesTotal
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