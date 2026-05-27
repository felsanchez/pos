<?php

require_once "conexion.php";

class ModeloCajas
{
    /*=============================================
    VERIFICAR CAJA ABIERTA
    =============================================*/
    static public function mdlVerificarCajaAbierta($tabla, $idUsuario, $idBodega)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id_usuario = :id_usuario AND id_bodega = :id_bodega AND estado = 'abierto' AND fecha_cierre IS NULL LIMIT 1");
        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*=============================================
    ABRIR CAJA
    =============================================*/
    static public function mdlAbrirCaja($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_usuario, id_bodega, fecha_apertura, monto_apertura, observaciones_apertura, estado) VALUES (:id_usuario, :id_bodega, :fecha_apertura, :monto_apertura, :observaciones_apertura, 'abierto')");
        $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":id_bodega", $datos["id_bodega"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha_apertura", $datos["fecha_apertura"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_apertura", $datos["monto_apertura"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones_apertura", $datos["observaciones_apertura"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    REGISTRAR MOVIMIENTO MANUAL
    =============================================*/
    static public function mdlRegistrarMovimiento($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_turno, tipo, monto, motivo, fecha) VALUES (:id_turno, :tipo, :monto, :motivo, :fecha)");
        $stmt->bindParam(":id_turno", $datos["id_turno"], PDO::PARAM_INT);
        $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
        $stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    SUMAR VENTAS DEL TURNO AGRUPADAS POR MÉTODO DE PAGO
    =============================================*/
    static public function mdlSumarVentasTurno($idTurno)
    {
        $stmt = Conexion::conectar()->prepare("SELECT SUBSTRING_INDEX(metodo_pago, '-', 1) as metodo_pago, SUM(total) as total FROM ventas WHERE id_turno_caja = :id_turno AND estado = 'venta' AND ( ( (resolucion_id IS NULL OR resolucion_id = 0) AND (estado_dian IS NULL OR estado_dian = '') ) OR ( resolucion_id IS NOT NULL AND resolucion_id != 0 AND estado_dian IN ('aceptada', 'enviada') ) ) GROUP BY SUBSTRING_INDEX(metodo_pago, '-', 1)");
        $stmt->bindParam(":id_turno", $idTurno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    OBTENER SUMATORIA DE MOVIMIENTOS MANUALES POR TIPO
    =============================================*/
    static public function mdlObtenerSumaMovimientosTurno($idTurno)
    {
        $stmt = Conexion::conectar()->prepare("SELECT tipo, SUM(monto) as total FROM cajas_movimientos WHERE id_turno = :id_turno GROUP BY tipo");
        $stmt->bindParam(":id_turno", $idTurno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    CERRAR CAJA
    =============================================*/
    static public function mdlCerrarCaja($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET fecha_cierre = :fecha_cierre, monto_cierre_teorico = :monto_cierre_teorico, monto_cierre_real = :monto_cierre_real, diferencia = :diferencia, ventas_efectivo = :ventas_efectivo, ventas_tarjeta = :ventas_tarjeta, ventas_transferencia = :ventas_transferencia, observaciones = :observaciones, estado = 'cerrado' WHERE id = :id");
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha_cierre", $datos["fecha_cierre"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_cierre_teorico", $datos["monto_cierre_teorico"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_cierre_real", $datos["monto_cierre_real"], PDO::PARAM_STR);
        $stmt->bindParam(":diferencia", $datos["diferencia"], PDO::PARAM_STR);
        $stmt->bindParam(":ventas_efectivo", $datos["ventas_efectivo"], PDO::PARAM_STR);
        $stmt->bindParam(":ventas_tarjeta", $datos["ventas_tarjeta"], PDO::PARAM_STR);
        $stmt->bindParam(":ventas_transferencia", $datos["ventas_transferencia"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    MOSTRAR CIERRES DE CAJA (PARA DATATABLES SERVER-SIDE)
    =============================================*/
    static public function mdlMostrarCierresCaja($tabla, $where, $order, $limit)
    {
        $sql = "SELECT c.*, u.nombre as nombre_usuario, b.nombre as nombre_bodega 
                FROM $tabla c
                INNER JOIN usuarios u ON c.id_usuario = u.id
                INNER JOIN bodegas b ON c.id_bodega = b.id
                $where $order $limit";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    OBTENER TOTAL DE CIERRES DE CAJA
    =============================================*/
    static public function mdlGetTotalCierresCaja($tabla, $where)
    {
        $sql = "SELECT COUNT(*) as total FROM $tabla c
                INNER JOIN usuarios u ON c.id_usuario = u.id
                INNER JOIN bodegas b ON c.id_bodega = b.id
                $where";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res["total"];
    }

    /*=============================================
    OBTENER DETALLES DE UN TURNO
    =============================================*/
    static public function mdlObtenerDetalleTurno($tabla, $idTurno)
    {
        $stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre as nombre_usuario, b.nombre as nombre_bodega 
                                                FROM $tabla c
                                                INNER JOIN usuarios u ON c.id_usuario = u.id
                                                INNER JOIN bodegas b ON c.id_bodega = b.id
                                                WHERE c.id = :id LIMIT 1");
        $stmt->bindParam(":id", $idTurno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*=============================================
    OBTENER MOVIMIENTOS DETALLADOS DEL TURNO
    =============================================*/
    static public function mdlObtenerMovimientosTurno($idTurno)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM cajas_movimientos WHERE id_turno = :id_turno ORDER BY fecha ASC");
        $stmt->bindParam(":id_turno", $idTurno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
