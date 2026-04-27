<?php

require_once "conexion.php";

class ModeloSeguimiento
{

    /*=============================================
    MOSTRAR SEGUIMIENTOS
    =============================================*/

    static public function mdlMostrarSeguimientos($tabla, $item, $valor)
    {

        if ($item != null) {

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch();

        } else {

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

            $stmt->execute();

            return $stmt->fetchAll();

        }

        $stmt->close();

        $stmt = null;

    }

    /*=============================================
    MOSTRAR SEGUIMIENTOS SERVER-SIDE
    =============================================*/
    static public function mdlMostrarSeguimientosServerSide($tabla, $where, $order, $limit)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla $where $order $limit");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    OBTENER TOTAL SEGUIMIENTOS (PARA SERVER-SIDE)
    =============================================*/
    static public function mdlGetTotalSeguimientos($tabla, $where)
    {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM $tabla $where");
        $stmt->execute();
        return $stmt->fetchColumn();
    }


    /*=============================================
    ELIMINAR SEGUIMIENTO
    =============================================*/
    static public function mdlEliminarSeguimiento($tabla, $datos)
    {

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return "ok";

        } else {

            return "error";

        }

        $stmt->close();
        $stmt = null;

    }

    /*=============================================
    ELIMINAR SEGUIMIENTOS MASIVO
    =============================================*/
    static public function mdlEliminarSeguimientosMasivo($tabla, $idsJson)
    {
        $ids = json_decode($idsJson, true);
        if (empty($ids))
            return "error";

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id IN ($placeholders)");

        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

}
