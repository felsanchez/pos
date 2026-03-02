<?php
// Script para agregar métodos faltantes al modelo factus

$filePath = dirname(__FILE__) . '/modelos/factus.modelo.php';
$content = file_get_contents($filePath);

// Verificar si ya existen
if (strpos($content, 'mdlGuardarNotaCredito') !== false) {
    echo "Los métodos ya existen. Nada que hacer.\n";
    exit;
}

$newMethods = '
    /*=============================================
    MOSTRAR NOTAS CRÉDITO
    =============================================*/
    static public function mdlMostrarNotasCredito($tabla, $item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :item ORDER BY id DESC");
            $stmt->bindParam(":item", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /*=============================================
    GUARDAR NOTA CRÉDITO (BORRADOR)
    =============================================*/
    static public function mdlGuardarNotaCredito($datos)
    {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO notas_credito (
                id_venta_original, numero_factura_original, tipo_nota, motivo,
                productos, monto_total, id_cliente, estado_dian,
                numero_nota_credito, cufe_nc, qr_data_nc, xml_dian_nc,
                pdf_dian_nc, mensaje_dian, fecha_envio_dian, id_usuario,
                observacion, metodo_pago
            ) VALUES (
                :id_venta_original, :numero_factura_original, :tipo_nota, :motivo,
                :productos, :monto_total, :id_cliente, :estado_dian,
                :numero_nota_credito, :cufe_nc, :qr_data_nc, :xml_dian_nc,
                :pdf_dian_nc, :mensaje_dian, :fecha_envio_dian, :id_usuario,
                :observacion, :metodo_pago
            )"
        );

        $stmt->bindParam(":id_venta_original",      $datos["id_venta_original"],      PDO::PARAM_INT);
        $stmt->bindParam(":numero_factura_original", $datos["numero_factura_original"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_nota",              $datos["tipo_nota"],              PDO::PARAM_STR);
        $stmt->bindParam(":motivo",                 $datos["motivo"],                 PDO::PARAM_STR);
        $stmt->bindParam(":productos",              $datos["productos"],              PDO::PARAM_STR);
        $stmt->bindParam(":monto_total",            $datos["monto_total"],            PDO::PARAM_STR);
        $stmt->bindParam(":id_cliente",             $datos["id_cliente"],             PDO::PARAM_STR);
        $stmt->bindParam(":estado_dian",            $datos["estado_dian"],            PDO::PARAM_STR);
        $stmt->bindParam(":numero_nota_credito",    $datos["numero_nota_credito"],    PDO::PARAM_STR);
        $stmt->bindParam(":cufe_nc",                $datos["cufe_nc"],                PDO::PARAM_STR);
        $stmt->bindParam(":qr_data_nc",             $datos["qr_data_nc"],             PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian_nc",            $datos["xml_dian_nc"],            PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian_nc",            $datos["pdf_dian_nc"],            PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian",           $datos["mensaje_dian"],           PDO::PARAM_STR);
        $stmt->bindValue(":fecha_envio_dian",       $datos["fecha_envio_dian"],       PDO::PARAM_STR);
        $stmt->bindParam(":id_usuario",             $datos["id_usuario"],             PDO::PARAM_INT);
        $stmt->bindParam(":observacion",            $datos["observacion"],            PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago",            $datos["metodo_pago"],            PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            error_log("Error mdlGuardarNotaCredito: " . print_r($stmt->errorInfo(), true));
            return "error";
        }
    }

    /*=============================================
    ACTUALIZAR NOTA CRÉDITO (DESPUÉS DE FIRMAR)
    =============================================*/
    static public function mdlActualizarNotaCredito($idNota, $datos)
    {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE notas_credito SET
                estado_dian         = :estado_dian,
                numero_nota_credito = :numero_nota_credito,
                cufe_nc             = :cufe_nc,
                qr_data_nc          = :qr_data_nc,
                xml_dian_nc         = :xml_dian_nc,
                pdf_dian_nc         = :pdf_dian_nc,
                mensaje_dian        = :mensaje_dian,
                fecha_envio_dian    = :fecha_envio_dian
            WHERE id = :id"
        );

        $stmt->bindParam(":estado_dian",         $datos["estado_dian"],         PDO::PARAM_STR);
        $stmt->bindParam(":numero_nota_credito", $datos["numero_nota_credito"],  PDO::PARAM_STR);
        $stmt->bindParam(":cufe_nc",             $datos["cufe_nc"],             PDO::PARAM_STR);
        $stmt->bindParam(":qr_data_nc",          $datos["qr_data_nc"],          PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian_nc",         $datos["xml_dian_nc"],         PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian_nc",         $datos["pdf_dian_nc"],         PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian",        $datos["mensaje_dian"],        PDO::PARAM_STR);
        $stmt->bindParam(":fecha_envio_dian",    $datos["fecha_envio_dian"],    PDO::PARAM_STR);
        $stmt->bindParam(":id",                  $idNota,                       PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            error_log("Error mdlActualizarNotaCredito: " . print_r($stmt->errorInfo(), true));
            return "error";
        }
    }

';

// Insertar antes del último "}"
$lastBrace = strrpos($content, '}');
$newContent = substr($content, 0, $lastBrace) . $newMethods . "}\n";
file_put_contents($filePath, $newContent);

echo "Métodos agregados correctamente.\n";
