<?php

class ControladorCajas
{
    /*=============================================
    VERIFICAR CAJA ABIERTA (RETORNA REGISTRO)
    =============================================*/
    static public function ctrVerificarCajaAbierta()
    {
        if (!isset($_SESSION["id"]) || !isset($_SESSION["id_bodega"])) {
            return false;
        }

        $tabla = "cajas_turnos";
        $idUsuario = $_SESSION["id"];
        $idBodega = $_SESSION["id_bodega"];

        $respuesta = ModeloCajas::mdlVerificarCajaAbierta($tabla, $idUsuario, $idBodega);
        return $respuesta;
    }

    /*=============================================
    VALIDAR SI SE DEBE / PUEDE OPERAR (HELPER DE BLOQUEO)
    =============================================*/
    static public function ctrValidarCajaAbierta()
    {
        // 1. Obtener la configuración global
        $configuracion = ModeloConfiguracion::mdlObtenerConfiguracion();
        
        // Si no está configurada la tabla o control_caja está apagado (0), permitir paso libre
        if (!$configuracion || !isset($configuracion["control_caja"]) || intval($configuracion["control_caja"]) === 0) {
            return true;
        }

        // 2. Si está encendido (1), verificar si tiene turno abierto
        $caja = self::ctrVerificarCajaAbierta();
        return ($caja !== false);
    }

    /*=============================================
    ABRIR CAJA
    =============================================*/
    static public function ctrAbrirCaja($montoApertura, $observacionesApertura = "")
    {
        if (!isset($_SESSION["id"]) || !isset($_SESSION["id_bodega"])) {
            return "error_session";
        }

        // Validar que no tenga ya una caja abierta
        $cajaExistente = self::ctrVerificarCajaAbierta();
        if ($cajaExistente) {
            return "caja_ya_abierta";
        }

        $tabla = "cajas_turnos";
        $datos = array(
            "id_usuario" => $_SESSION["id"],
            "id_bodega" => $_SESSION["id_bodega"],
            "fecha_apertura" => date("Y-m-d H:i:s"),
            "monto_apertura" => floatval($montoApertura),
            "observaciones_apertura" => $observacionesApertura
        );

        $db = Conexion::conectar();
        try {
            $db->beginTransaction();
            $respuesta = ModeloCajas::mdlAbrirCaja($tabla, $datos);
            if ($respuesta === "ok") {
                $db->commit();
            } else {
                $db->rollBack();
            }
            return $respuesta;
        } catch (Exception $e) {
            $db->rollBack();
            return "error";
        }
    }

    /*=============================================
    REGISTRAR MOVIMIENTO MANUAL DE CAJA CHICA
    =============================================*/
    static public function ctrRegistrarMovimiento($tipo, $monto, $motivo)
    {
        $caja = self::ctrVerificarCajaAbierta();
        if (!$caja) {
            return "sin_caja_abierta";
        }

        $tabla = "cajas_movimientos";
        $datos = array(
            "id_turno" => $caja["id"],
            "tipo" => $tipo, // 'ingreso' o 'egreso'
            "monto" => floatval($monto),
            "motivo" => $motivo,
            "fecha" => date("Y-m-d H:i:s")
        );

        $db = Conexion::conectar();
        try {
            $db->beginTransaction();
            $respuesta = ModeloCajas::mdlRegistrarMovimiento($tabla, $datos);
            if ($respuesta === "ok") {
                $db->commit();
            } else {
                $db->rollBack();
            }
            return $respuesta;
        } catch (Exception $e) {
            $db->rollBack();
            return "error";
        }
    }

    /*=============================================
    CERRAR CAJA
    =============================================*/
    static public function ctrCerrarCaja($montoCierreReal, $observaciones)
    {
        $caja = self::ctrVerificarCajaAbierta();
        if (!$caja) {
            return "sin_caja_abierta";
        }

        $idTurno = $caja["id"];
        $montoApertura = floatval($caja["monto_apertura"]);

        // 1. Obtener sumatorias de ventas del turno
        $ventasTurno = ModeloCajas::mdlSumarVentasTurno($idTurno);
        
        $ventasEfectivo = 0.00;
        $ventasTarjeta = 0.00;
        $ventasTransferencia = 0.00;

        foreach ($ventasTurno as $venta) {
            $metodo = strtolower(trim($venta["metodo_pago"]));
            $total = floatval($venta["total"]);

            if (stripos($metodo, 'efectivo') !== false) {
                $ventasEfectivo += $total;
            } elseif (stripos($metodo, 'tarjeta') !== false || stripos($metodo, 'credito') !== false || stripos($metodo, 'debito') !== false) {
                $ventasTarjeta += $total;
            } else {
                // Todo lo demás (transferencias, nequi, daviplata, bancos) entra aquí
                $ventasTransferencia += $total;
            }
        }

        // 2. Obtener movimientos manuales del turno
        $movimientos = ModeloCajas::mdlObtenerSumaMovimientosTurno($idTurno);
        $ingresosManuales = 0.00;
        $egresosManuales = 0.00;

        foreach ($movimientos as $mov) {
            if ($mov["tipo"] === 'ingreso') {
                $ingresosManuales += floatval($mov["total"]);
            } elseif ($mov["tipo"] === 'egreso') {
                $egresosManuales += floatval($mov["total"]);
            }
        }

        // 3. Calcular efectivo teórico esperado
        $efectivoTeorico = $montoApertura + $ventasEfectivo + $ingresosManuales - $egresosManuales;
        
        // 4. Calcular diferencia
        $real = floatval($montoCierreReal);
        $diferencia = $real - $efectivoTeorico;

        $tabla = "cajas_turnos";
        $datos = array(
            "id" => $idTurno,
            "fecha_cierre" => date("Y-m-d H:i:s"),
            "monto_cierre_teorico" => $efectivoTeorico,
            "monto_cierre_real" => $real,
            "diferencia" => $diferencia,
            "ventas_efectivo" => $ventasEfectivo,
            "ventas_tarjeta" => $ventasTarjeta,
            "ventas_transferencia" => $ventasTransferencia,
            "observaciones" => $observaciones
        );

        $db = Conexion::conectar();
        try {
            $db->beginTransaction();
            $respuesta = ModeloCajas::mdlCerrarCaja($tabla, $datos);
            if ($respuesta === "ok") {
                $db->commit();
            } else {
                $db->rollBack();
            }
            return $respuesta;
        } catch (Exception $e) {
            $db->rollBack();
            return "error";
        }
    }

    /*=============================================
    MOSTRAR HISTORIAL DE CIERRES (SERVER-SIDE)
    =============================================*/
    static public function ctrMostrarCierresCajaServerSide($params)
    {
        $tabla = "cajas_turnos";

        // Mapeo de columnas para ordenamiento
        $columnsMap = array(
            0 => 'b.nombre',
            1 => 'u.nombre',
            2 => 'c.fecha_apertura',
            3 => 'c.fecha_cierre',
            4 => 'c.monto_apertura',
            5 => 'c.monto_cierre_teorico',
            6 => 'c.monto_cierre_real',
            7 => 'c.diferencia'
        );

        $where = " WHERE 1=1 ";

        // Filtro por Fechas
        if (!empty($params['fechaInicio']) && !empty($params['fechaFin'])) {
            $where .= " AND c.fecha_apertura BETWEEN '" . $params['fechaInicio'] . " 00:00:00' AND '" . $params['fechaFin'] . " 23:59:59' ";
        }

        // Filtro por Bodega
        if (!empty($params['bodegaId'])) {
            $where .= " AND c.id_bodega = " . intval($params['bodegaId']);
        }

        // Filtro por Cajero
        if (!empty($params['usuarioId'])) {
            $where .= " AND c.id_usuario = " . intval($params['usuarioId']);
        }

        // Búsqueda global (DataTables)
        if (!empty($params['search']['value'])) {
            $searchValue = $params['search']['value'];
            $where .= " AND (u.nombre LIKE '%$searchValue%' OR b.nombre LIKE '%$searchValue%' OR c.estado LIKE '%$searchValue%') ";
        }

        // Ordenar
        $order = " ORDER BY c.fecha_apertura DESC";
        if (isset($params['order'][0]['column'])) {
            $colIdx = $params['order'][0]['column'];
            $colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'c.fecha_apertura';
            $order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
        }

        // Paginación
        $limit = "";
        if ($params['length'] != -1) {
            $limit = " LIMIT " . $params['start'] . ", " . $params['length'];
        }

        $cierres = ModeloCajas::mdlMostrarCierresCaja($tabla, $where, $order, $limit);
        $totalData = ModeloCajas::mdlGetTotalCierresCaja($tabla, " WHERE 1=1 ");
        $totalFiltered = ModeloCajas::mdlGetTotalCierresCaja($tabla, $where);

        $data = array();
        foreach ($cierres as $cierre) {
            $nested = array();
            $nested[] = $cierre["id"];
            $nested[] = e($cierre["nombre_bodega"]);
            $nested[] = e($cierre["nombre_usuario"]);
            $nested[] = $cierre["fecha_apertura"];
            $nested[] = $cierre["fecha_cierre"] ?? '<span class="label label-success">Abierto</span>';
            $nested[] = number_format($cierre["monto_apertura"], 2);
            $nested[] = $cierre["monto_cierre_teorico"] !== null ? number_format($cierre["monto_cierre_teorico"], 2) : '-';
            $nested[] = $cierre["monto_cierre_real"] !== null ? number_format($cierre["monto_cierre_real"], 2) : '-';
            
            if ($cierre["diferencia"] === null) {
                $nested[] = '-';
            } else {
                $dif = floatval($cierre["diferencia"]);
                if ($dif === 0.00) {
                    $nested[] = '<span class="text-muted">0.00</span>';
                } elseif ($dif > 0) {
                    $nested[] = '<span class="label label-info">+' . number_format($dif, 2) . ' (Sobrante)</span>';
                } else {
                    $nested[] = '<span class="label label-danger">' . number_format($dif, 2) . ' (Faltante)</span>';
                }
            }

            // Botones de acción
            $botones = '<div class="btn-group">';
            $botones .= '<button class="btn btn-info btn-xs btnVerDetalleCaja" idCaja="' . $cierre["id"] . '" title="Ver detalles"><i class="fa fa-eye"></i></button>';
            if ($cierre["fecha_cierre"] !== null) {
                $botones .= '<a href="extensiones/tcpdf/pdf/descargar-pdf-caja.php?idCaja=' . $cierre["id"] . '" target="_blank" class="btn btn-danger btn-xs" title="Descargar PDF" style="margin-left: 2px;"><i class="fa fa-file-pdf-o"></i></a>';
            }
            $botones .= '</div>';
            $nested[] = $botones;

            $data[] = $nested;
        }

        return array(
            "draw"            => intval($params['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
    }

    static public function ctrObtenerDetalleTurno($idTurno)
    {
        $tabla = "cajas_turnos";
        $cierre = ModeloCajas::mdlObtenerDetalleTurno($tabla, $idTurno);
        if (!$cierre) {
            return null;
        }

        $movimientos = ModeloCajas::mdlObtenerMovimientosTurno($idTurno);
        $desgloseVentas = ModeloCajas::mdlSumarVentasTurno($idTurno);
        
        // Si el turno está abierto, calcular y sobreescribir las ventas en tiempo real
        if ($cierre["estado"] === 'abierto') {
            $ventasEfectivo = 0.00;
            $ventasTarjeta = 0.00;
            $ventasTransferencia = 0.00;

            foreach ($desgloseVentas as $venta) {
                $metodo = strtolower(trim($venta["metodo_pago"]));
                $total = floatval($venta["total"]);

                if (stripos($metodo, 'efectivo') !== false) {
                    $ventasEfectivo += $total;
                } elseif (stripos($metodo, 'tarjeta') !== false || stripos($metodo, 'credito') !== false || stripos($metodo, 'debito') !== false) {
                    $ventasTarjeta += $total;
                } else {
                    $ventasTransferencia += $total;
                }
            }

            $cierre["ventas_efectivo"] = $ventasEfectivo;
            $cierre["ventas_tarjeta"] = $ventasTarjeta;
            $cierre["ventas_transferencia"] = $ventasTransferencia;
        }

        return array(
            "cierre" => $cierre,
            "movimientos" => $movimientos,
            "desgloseVentas" => $desgloseVentas
        );
    }
}
