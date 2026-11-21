<?php
require_once "../controladores/configuracion.controlador.php";
require_once "../modelos/configuracion.modelo.php";
require_once "../modelos/conexion.php";

class AjaxConfiguracion
{
    /*=============================================
    SOLICITAR VINCULACIÓN BOLD
    =============================================*/
    public function ajaxSolicitarBold()
    {
        try {
            $db = Conexion::conectar();
            // Asegurar que la columna exista en la tabla configuracion
            try {
                $db->exec("ALTER TABLE configuracion ADD COLUMN IF NOT EXISTS solicitud_bold VARCHAR(255) NULL DEFAULT 'sin solicitud'");
            } catch (Exception $e1) {
                try {
                    $db->exec("ALTER TABLE configuracion ADD COLUMN solicitud_bold VARCHAR(255) NULL DEFAULT 'sin solicitud'");
                } catch (Exception $e2) {}
            }

            $stmt = $db->prepare("UPDATE configuracion SET solicitud_bold = 'El cliente solicito vincular BOLD' WHERE id = 1");
            $res = $stmt->execute();

            echo json_encode(["status" => "ok", "mensaje" => "Solicitud enviada correctamente"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
        }
        exit;
    }

    /*=============================================
    SOLICITAR FACTURACIÓN ELECTRÓNICA POR PRIMERA VEZ
    =============================================*/
    public function ajaxSolicitarFacturacionElectronica()
    {
        try {
            $db = Conexion::conectar();
            // Asegurar que la columna exista en la tabla configuracion
            try {
                $db->exec("ALTER TABLE configuracion ADD COLUMN IF NOT EXISTS solicitud_facturacion_electronica VARCHAR(255) NULL DEFAULT 'sin solicitud'");
            } catch (Exception $e1) {
                try {
                    $db->exec("ALTER TABLE configuracion ADD COLUMN solicitud_facturacion_electronica VARCHAR(255) NULL DEFAULT 'sin solicitud'");
                } catch (Exception $e2) {}
            }

            $stmt = $db->prepare("UPDATE configuracion SET solicitud_facturacion_electronica = 'El cliente solicito vincular facturas electronicas' WHERE id = 1");
            $res = $stmt->execute();

            echo json_encode(["status" => "ok", "mensaje" => "Solicitud enviada correctamente"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
        }
        exit;
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] == "solicitarBold") {
    $ajax = new AjaxConfiguracion();
    $ajax->ajaxSolicitarBold();
}

if (isset($_POST["accion"]) && $_POST["accion"] == "solicitarFacturacionElectronica") {
    $ajax = new AjaxConfiguracion();
    $ajax->ajaxSolicitarFacturacionElectronica();
}
