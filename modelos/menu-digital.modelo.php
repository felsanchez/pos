<?php

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/clientes.modelo.php";
require_once __DIR__ . "/ventas.modelo.php";

class ModeloMenuDigital
{
    /*=============================================
    VERIFICAR Y MIGRAR COLUMNAS EN TABLA PRODUCTOS
    =============================================*/
    static public function mdlVerificarTabla()
    {
        try {
            $db = Conexion::conectar();

            // 1. Verificar columna en_menu_digital
            $check1 = $db->query("SHOW COLUMNS FROM productos LIKE 'en_menu_digital'");
            if ($check1 && $check1->rowCount() == 0) {
                $db->exec("ALTER TABLE productos ADD COLUMN en_menu_digital TINYINT(1) NOT NULL DEFAULT 0");
            }

            // 2. Verificar columna caracteristicas_menu
            $check2 = $db->query("SHOW COLUMNS FROM productos LIKE 'caracteristicas_menu'");
            if ($check2 && $check2->rowCount() == 0) {
                $db->exec("ALTER TABLE productos ADD COLUMN caracteristicas_menu TEXT NULL");
            }

            // 3. Verificar columna origen en clientes (para diferenciar clientes de Menú Digital permanentemente)
            $check3 = $db->query("SHOW COLUMNS FROM clientes LIKE 'origen'");
            if ($check3 && $check3->rowCount() == 0) {
                $db->exec("ALTER TABLE clientes ADD COLUMN origen VARCHAR(50) NOT NULL DEFAULT 'sistema'");
            }

            // 4. Migrar clientes que se crearon previamente desde Menú Digital
            $db->exec("UPDATE clientes SET origen = 'menu_digital' WHERE origen = 'sistema' AND (estatus = 'Menú Digital' OR notas LIKE '%Menú Digital%')");
            $db->exec("UPDATE clientes SET estatus = 'Nuevo' WHERE estatus = 'Menú Digital'");

            // 5. Migrar clientes provenientes de WhatsApp / n8n / CRM a 'agente_ia'
            $db->exec("UPDATE clientes 
                       SET origen = 'agente_ia' 
                       WHERE (origen IS NULL OR origen = '' OR origen = 'sistema') 
                         AND (
                           notas LIKE '%Registrado por la IA%' 
                           OR notas LIKE '%WhatsApp%' 
                           OR notas LIKE '%n8n%' 
                           OR id IN (SELECT DISTINCT id_cliente FROM crm_leads WHERE (origen LIKE '%WhatsApp%' OR (resumen_ia IS NOT NULL AND resumen_ia != '')) AND id_cliente IS NOT NULL)
                         )");
        } catch (Exception $e) {
            // Ignorar si ya existen o no hay permisos
        }
    }

    /*=============================================
    MOSTRAR PRODUCTOS PARA EL MENÚ
    =============================================*/
    static public function mdlMostrarProductosMenu($soloActivos = false)
    {
        self::mdlVerificarTabla();

        $where = "WHERE p.estado = 1 ";
        if ($soloActivos) {
            $where .= "AND p.en_menu_digital = 1 ";
        }

        $sql = "SELECT p.*, c.categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id 
                $where 
                ORDER BY c.categoria ASC, p.descripcion ASC";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    ACTUALIZAR ESTADO EN MENÚ DIGITAL
    =============================================*/
    static public function mdlActualizarEstadoMenu($idProducto, $estado)
    {
        self::mdlVerificarTabla();

        $stmt = Conexion::conectar()->prepare("UPDATE productos SET en_menu_digital = :estado WHERE id = :id");
        $stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
        $stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ACTUALIZAR CARACTERÍSTICAS / INGREDIENTES
    =============================================*/
    static public function mdlActualizarCaracteristicas($idProducto, $caracteristicas)
    {
        self::mdlVerificarTabla();

        $stmt = Conexion::conectar()->prepare("UPDATE productos SET caracteristicas_menu = :caracteristicas WHERE id = :id");
        $stmt->bindParam(":caracteristicas", $caracteristicas, PDO::PARAM_STR);
        $stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    PROCESAR PEDIDO DEL MENÚ DIGITAL Y REGISTRAR ORDEN
    =============================================*/
    static public function mdlCrearOrdenMenuDigital($datos)
    {
        self::mdlVerificarTabla();
        $db = Conexion::conectar();

        try {
            $db->beginTransaction();

            $nombre = trim($datos["nombre"]);
            $celular = trim($datos["celular"]);
            $direccion = trim($datos["direccion"]);
            $notas = isset($datos["notas"]) ? trim($datos["notas"]) : "";
            $mesa = isset($datos["mesa"]) ? trim($datos["mesa"]) : "";
            $productos = $datos["productos"]; // array de items

            if (empty($nombre) || empty($celular) || empty($direccion) || empty($productos)) {
                throw new Exception("Los campos Nombre, Celular, Dirección y Productos son obligatorios.");
            }

            // 1. BUSCAR CLIENTE POR CELULAR (NORMALIZANDO DÍGITOS)
            $clienteExistente = ModeloClientes::mdlBuscarClientePorTelefono("clientes", $celular);
            $idCliente = null;

            if ($clienteExistente && !empty($clienteExistente["id"])) {
                $idCliente = $clienteExistente["id"];

                // Si no tenía dirección o cambió, actualizarla
                if (!empty($direccion)) {
                    $stmtUpdCli = $db->prepare("UPDATE clientes SET direccion = :direccion WHERE id = :id AND (direccion IS NULL OR direccion = '')");
                    $stmtUpdCli->bindParam(":direccion", $direccion, PDO::PARAM_STR);
                    $stmtUpdCli->bindParam(":id", $idCliente, PDO::PARAM_INT);
                    $stmtUpdCli->execute();
                }
            } else {
                // 2. CREAR NUEVO CLIENTE AUTOMÁTICAMENTE
                $stmtInsertCli = $db->prepare("
                    INSERT INTO clientes (
                        nombre, documento, email, telefono, direccion, estatus, notas, 
                        fecha_nacimiento, tipo_documento_id, digito_verificacion, tipo_persona, eliminado, origen
                    ) VALUES (
                        :nombre, :documento, :email, :telefono, :direccion, 'Nuevo', 'Registrado automáticamente desde Menú Digital', 
                        '1990-01-01', 3, '', '1', 0, 'menu_digital'
                    )
                ");

                $docFallback = preg_replace('/\D/', '', $celular);
                if (empty($docFallback)) {
                    $docFallback = time();
                }

                $emailVacio = "";
                $stmtInsertCli->bindParam(":nombre", $nombre, PDO::PARAM_STR);
                $stmtInsertCli->bindParam(":documento", $docFallback, PDO::PARAM_STR);
                $stmtInsertCli->bindParam(":email", $emailVacio, PDO::PARAM_STR);
                $stmtInsertCli->bindParam(":telefono", $celular, PDO::PARAM_STR);
                $stmtInsertCli->bindParam(":direccion", $direccion, PDO::PARAM_STR);
                $stmtInsertCli->execute();

                $idCliente = $db->lastInsertId();
            }

            if (empty($idCliente)) {
                throw new Exception("No se pudo identificar o registrar el cliente para la orden.");
            }

            // 3. OBTENER CONSECUTIVO DE ORDEN (TABLA VENTAS)
            $stmtConsecutivo = $db->prepare("SELECT ultimo_numero FROM consecutivos WHERE tabla = 'ventas'");
            $stmtConsecutivo->execute();
            $resConsecutivo = $stmtConsecutivo->fetch(PDO::FETCH_ASSOC);

            if ($resConsecutivo && isset($resConsecutivo["ultimo_numero"])) {
                $codigoOrden = intval($resConsecutivo["ultimo_numero"]) + 1;
            } else {
                // Fallback: máximo código existente en ventas
                $stmtMax = $db->prepare("SELECT MAX(codigo) as max_cod FROM ventas");
                $stmtMax->execute();
                $resMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
                $codigoOrden = !empty($resMax["max_cod"]) ? (intval($resMax["max_cod"]) + 1) : 10001;
            }

            // Actualizar tabla consecutivos
            $stmtUpdConsecutivo = $db->prepare("UPDATE consecutivos SET ultimo_numero = :codigo WHERE tabla = 'ventas'");
            $stmtUpdConsecutivo->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmtUpdConsecutivo->execute();

            // 4. PREPARAR LISTA DE PRODUCTOS Y CALCULAR TOTALES
            $listaProductosJSON = [];
            $totalNeto = 0;
            $totalImpuesto = 0;
            $totalVenta = 0;

            foreach ($productos as $prod) {
                $idProd = intval($prod["id"]);
                $cantidad = max(1, intval($prod["cantidad"]));

                // Obtener datos reales del producto para garantizar precio verídico
                $stmtProd = $db->prepare("SELECT id, codigo, descripcion, precio_venta FROM productos WHERE id = :id");
                $stmtProd->bindParam(":id", $idProd, PDO::PARAM_INT);
                $stmtProd->execute();
                $productoBD = $stmtProd->fetch(PDO::FETCH_ASSOC);

                if (!$productoBD) {
                    continue;
                }

                $precio = floatval($productoBD["precio_venta"]);
                $subtotalItem = $precio * $cantidad;
                $totalVenta += $subtotalItem;

                $listaProductosJSON[] = [
                    "id" => $productoBD["id"],
                    "codigo" => $productoBD["codigo"],
                    "descripcion" => $productoBD["descripcion"],
                    "cantidad" => $cantidad,
                    "stock" => "999",
                    "precio" => $precio,
                    "total" => $subtotalItem
                ];
            }

            if (empty($listaProductosJSON)) {
                throw new Exception("No se encontraron productos válidos en el carrito.");
            }

            $totalNeto = $totalVenta; // Neto base
            $jsonProductos = json_encode($listaProductosJSON, JSON_UNESCAPED_UNICODE);

            // 5. CONSTRUIR OBSERVACIÓN
            $observacionCompleta = "[Menú Digital] ";
            if (!empty($mesa)) {
                $observacionCompleta .= "MESA: " . $mesa . " | ";
            }
            $observacionCompleta .= "DIR/ENTREGA: " . $direccion . " | TEL: " . $celular;
            if (!empty($notas)) {
                $observacionCompleta .= " | NOTAS: " . $notas;
            }

            // 6. ASIGNAR VENDEDOR Y BODEGA POR DEFECTO
            // Intentar asignar primer vendedor activo o ID 1
            $idVendedor = 1;
            $stmtVendedor = $db->query("SELECT id FROM usuarios WHERE estado = 1 ORDER BY id ASC LIMIT 1");
            if ($stmtVendedor && $rowVend = $stmtVendedor->fetch(PDO::FETCH_ASSOC)) {
                $idVendedor = intval($rowVend["id"]);
            }

            $idBodega = 1;
            $stmtBodega = $db->query("SELECT id FROM bodegas WHERE estado = 1 ORDER BY id ASC LIMIT 1");
            if ($stmtBodega && $rowBod = $stmtBodega->fetch(PDO::FETCH_ASSOC)) {
                $idBodega = intval($rowBod["id"]);
            }

            date_default_timezone_set('America/Bogota');
            $fechaActual = date('Y-m-d H:i:s');

            // 7. INSERTAR EN TABLA VENTAS CON ESTADO = 'orden'
            $sqlInsertOrden = "
                INSERT INTO ventas (
                    codigo, numero_factura, id_cliente, id_vendedor, id_bodega, productos, 
                    impuesto, neto, total, metodo_pago, notas, observacion, estado, fecha
                ) VALUES (
                    :codigo, '', :id_cliente, :id_vendedor, :id_bodega, :productos, 
                    :impuesto, :neto, :total, 'Pendiente (Menú Digital)', :notas, :observacion, 'orden', :fecha
                )
            ";

            $stmtInsertOrden = $db->prepare($sqlInsertOrden);
            $stmtInsertOrden->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmtInsertOrden->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
            $stmtInsertOrden->bindParam(":id_vendedor", $idVendedor, PDO::PARAM_INT);
            $stmtInsertOrden->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
            $stmtInsertOrden->bindParam(":productos", $jsonProductos, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":impuesto", $totalImpuesto, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":neto", $totalNeto, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":total", $totalVenta, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":notas", $notas, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":observacion", $observacionCompleta, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);

            $stmtInsertOrden->execute();
            $idOrden = $db->lastInsertId();

            $db->commit();

            // 8. CREAR NOTIFICACIÓN AUTOMÁTICA EN EL MÓDULO DE NOTIFICACIONES
            try {
                require_once __DIR__ . "/notificaciones.modelo.php";
                $detalleUbicacion = !empty($mesa) ? "Mesa: " . $mesa : "Entrega: " . $direccion;
                $mensajeNotif = "Orden #" . $codigoOrden . " - Cliente: " . $nombre . " (" . $detalleUbicacion . ") - Total: $" . number_format($totalVenta, 2, ',', '.');
                $datosNotif = array(
                    "tipo" => "orden_menu_digital",
                    "titulo" => "Nuevo Pedido Menú Digital",
                    "mensaje" => $mensajeNotif,
                    "referencia_tipo" => "venta",
                    "referencia_id" => $idOrden,
                    "id_bodega" => $idBodega
                );
                ModeloNotificaciones::mdlCrearNotificacion($datosNotif);
            } catch (Exception $eNotif) {
                // No interrumpir la orden si falla la notificación
            }

            return [
                "status" => "ok",
                "codigo" => $codigoOrden,
                "id_orden" => $idOrden,
                "total" => $totalVenta,
                "mensaje" => "¡Tu pedido ha sido recibido con éxito! Código de orden: #" . $codigoOrden
            ];

        } catch (Exception $e) {
            $db->rollBack();
            return [
                "status" => "error",
                "mensaje" => $e->getMessage()
            ];
        }
    }
}
