<?php

require_once "conexion.php";

class ModeloFacturacion
{

    /*=============================================
    MOSTRAR CONFIGURACION
    =============================================*/

    static public function mdlMostrarConfiguracion($tabla)
    {

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id = 1");

        $stmt->execute();

        return $stmt->fetch();

    }

    /*=============================================
    GUARDAR CONFIGURACION
    =============================================*/

    static public function mdlGuardarConfiguracion($tabla, $datos)
    {

        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET token = :token, refresh_token = :refresh_token, resolucion = :resolucion, prefijo = :prefijo, consecutivo_actual = :consecutivo_actual, fecha_desde = :fecha_desde, fecha_hasta = :fecha_hasta, clave_tecnica = :clave_tecnica, ambiente = :ambiente, api_url = :api_url, email_contacto = :email_contacto WHERE id = 1");

        $stmt->bindParam(":token", $datos["token"], PDO::PARAM_STR);
        $stmt->bindParam(":refresh_token", $datos["refresh_token"], PDO::PARAM_STR);
        $stmt->bindParam(":resolucion", $datos["resolucion"], PDO::PARAM_STR);
        $stmt->bindParam(":prefijo", $datos["prefijo"], PDO::PARAM_STR);
        $stmt->bindParam(":consecutivo_actual", $datos["consecutivo_actual"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha_desde", $datos["fecha_desde"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_hasta", $datos["fecha_hasta"], PDO::PARAM_STR);
        $stmt->bindParam(":clave_tecnica", $datos["clave_tecnica"], PDO::PARAM_STR);
        $stmt->bindParam(":ambiente", $datos["ambiente"], PDO::PARAM_STR);
        $stmt->bindParam(":api_url", $datos["api_url"], PDO::PARAM_STR);
        $stmt->bindParam(":email_contacto", $datos["email_contacto"], PDO::PARAM_STR);

        if ($stmt->execute()) {

            return "ok";

        } else {

            return "error";

        }

    }

}
