<?php

require_once "conexion.php";

class ModeloTraslados
{

    /*=============================================
    MOSTRAR TRASLADOS
    =============================================*/
    static public function mdlMostrarTraslados($tabla, $item, $valor, $fechaInicial = null, $fechaFinal = null)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT t.*, 
                                                        bo.nombre AS bodega_origen, 
                                                        bd.nombre AS bodega_destino, 
                                                        u.nombre AS usuario 
                                                  FROM $tabla t
                                                  INNER JOIN bodegas bo ON t.id_bodega_origen = bo.id
                                                  INNER JOIN bodegas bd ON t.id_bodega_destino = bd.id
                                                  INNER JOIN usuarios u ON t.id_usuario = u.id
                                                  WHERE t.$item = :$item 
                                                  ORDER BY t.id DESC");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            if ($fechaInicial != null && $fechaFinal != null) {
                if ($fechaInicial == $fechaFinal) {
                    $stmt = Conexion::conectar()->prepare("SELECT t.*, 
                                                                bo.nombre AS bodega_origen, 
                                                                bd.nombre AS bodega_destino, 
                                                                u.nombre AS usuario 
                                                          FROM $tabla t
                                                          INNER JOIN bodegas bo ON t.id_bodega_origen = bo.id
                                                          INNER JOIN bodegas bd ON t.id_bodega_destino = bd.id
                                                          INNER JOIN usuarios u ON t.id_usuario = u.id
                                                          WHERE DATE(t.fecha) = :fechaInicial
                                                          ORDER BY t.id DESC");
                    $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
                } else {
                    $stmt = Conexion::conectar()->prepare("SELECT t.*, 
                                                                bo.nombre AS bodega_origen, 
                                                                bd.nombre AS bodega_destino, 
                                                                u.nombre AS usuario 
                                                          FROM $tabla t
                                                          INNER JOIN bodegas bo ON t.id_bodega_origen = bo.id
                                                          INNER JOIN bodegas bd ON t.id_bodega_destino = bd.id
                                                          INNER JOIN usuarios u ON t.id_usuario = u.id
                                                          WHERE DATE(t.fecha) BETWEEN :fechaInicial AND :fechaFinal
                                                          ORDER BY t.id DESC");
                    $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
                    $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
                }
            } else {
                $stmt = Conexion::conectar()->prepare("SELECT t.*, 
                                                            bo.nombre AS bodega_origen, 
                                                            bd.nombre AS bodega_destino, 
                                                            u.nombre AS usuario 
                                                      FROM $tabla t
                                                      INNER JOIN bodegas bo ON t.id_bodega_origen = bo.id
                                                      INNER JOIN bodegas bd ON t.id_bodega_destino = bd.id
                                                      INNER JOIN usuarios u ON t.id_usuario = u.id
                                                      ORDER BY t.id DESC");
            }
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /*=============================================
    REGISTRAR TRASLADO
    =============================================*/
    static public function mdlIngresarTraslado($tabla, $datos)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("INSERT INTO $tabla(codigo, id_bodega_origen, id_bodega_destino, id_usuario, total_items, notas, estado) 
                               VALUES (:codigo, :id_bodega_origen, :id_bodega_destino, :id_usuario, :total_items, :notas, :estado)");

        $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
        $stmt->bindParam(":id_bodega_origen", $datos["id_bodega_origen"], PDO::PARAM_INT);
        $stmt->bindParam(":id_bodega_destino", $datos["id_bodega_destino"], PDO::PARAM_INT);
        $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":total_items", $datos["total_items"], PDO::PARAM_INT);
        $stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $pdo->lastInsertId();
        } else {
            return "error";
        }
    }

    /*=============================================
    INGRESAR ITEM TRASLADO
    =============================================*/
    static public function mdlIngresarItemTraslado($tabla, $datos)
    {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_traslado, tipo_producto, id_producto, id_variante, cantidad) 
                                               VALUES (:id_traslado, :tipo_producto, :id_producto, :id_variante, :cantidad)");

        $stmt->bindParam(":id_traslado", $datos["id_traslado"], PDO::PARAM_INT);
        $stmt->bindParam(":tipo_producto", $datos["tipo_producto"], PDO::PARAM_STR);
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":id_variante", $datos["id_variante"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ACTUALIZAR ESTADO TRASLADO
    =============================================*/
    static public function mdlActualizarTraslado($tabla, $item1, $valor1, $item2, $valor2)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1, fecha_completado = IF(:$item1 = 'completado', NOW(), fecha_completado) WHERE $item2 = :$item2");

        $stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
        $stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    MOSTRAR ITEMS TRASLADO
    =============================================*/
    static public function mdlMostrarItemsTraslado($tabla, $idTraslado)
    {
        $stmt = Conexion::conectar()->prepare("SELECT i.*, 
                                                     p.descripcion AS nombre_producto,
                                                     (SELECT GROUP_CONCAT(ov.nombre SEPARATOR ' - ') 
                                                      FROM productos_variantes_opciones pvo 
                                                      INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id 
                                                      WHERE pvo.id_producto_variante = i.id_variante) AS nombre_variante
                                              FROM $tabla i
                                              INNER JOIN productos p ON i.id_producto = p.id
                                              WHERE i.id_traslado = :id_traslado");
        $stmt->bindParam(":id_traslado", $idTraslado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    /*=============================================
    LIMPIAR TRASLADOS ANTIGUOS (MÁS DE 3 MESES)
    =============================================*/
    static public function mdlLimpiarTrasladosAntiguos($tabla)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE fecha < DATE_SUB(NOW(), INTERVAL 3 MONTH)");

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->closeCursor();
        $stmt = null;
    }
}
