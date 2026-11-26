<?php

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/clientes.modelo.php";
require_once __DIR__ . "/ventas.modelo.php";

class ModeloMenuDigital
{
    static private $tablaVerificada = false;

    /*=============================================
    VERIFICAR Y MIGRAR COLUMNAS EN TABLA PRODUCTOS
    =============================================*/
    static public function mdlVerificarTabla()
    {
        if (self::$tablaVerificada) {
            return;
        }

        try {
            $db = Conexion::conectar();
            if ($db && $db->inTransaction()) {
                return;
            }

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

            // 6. Verificar columnas para pasarela de pagos Bold en configuracion
            $checkBold1 = $db->query("SHOW COLUMNS FROM configuracion LIKE 'bold_activo'");
            if ($checkBold1 && $checkBold1->rowCount() == 0) {
                $db->exec("ALTER TABLE configuracion ADD COLUMN bold_activo TINYINT(1) NOT NULL DEFAULT 0");
            }
            $checkBold2 = $db->query("SHOW COLUMNS FROM configuracion LIKE 'bold_api_key'");
            if ($checkBold2 && $checkBold2->rowCount() == 0) {
                $db->exec("ALTER TABLE configuracion ADD COLUMN bold_api_key VARCHAR(255) NULL");
            }
            $checkBold3 = $db->query("SHOW COLUMNS FROM configuracion LIKE 'bold_secret_key'");
            if ($checkBold3 && $checkBold3->rowCount() == 0) {
                $db->exec("ALTER TABLE configuracion ADD COLUMN bold_secret_key VARCHAR(255) NULL");
            }

            // 7. Crear automáticamente tabla para pre-órdenes y sesiones temporales de Bold
            $db->exec("
                CREATE TABLE IF NOT EXISTS `ordenes_temporales_bold` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `session_id` VARCHAR(100) NOT NULL UNIQUE,
                    `datos_orden` LONGTEXT NOT NULL,
                    `total` DECIMAL(12,2) NOT NULL,
                    `estado` ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
                    `id_orden_final` INT NULL,
                    `codigo_orden_final` VARCHAR(50) NULL,
                    `transaccion_id` VARCHAR(100) NULL,
                    `fecha_creacion` DATETIME NOT NULL,
                    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX (`session_id`),
                    INDEX (`estado`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            self::$tablaVerificada = true;
        } catch (Exception $e) {
            // Ignorar si ya existen o no hay permisos
            self::$tablaVerificada = true;
        }
    }

    /*=============================================
    OBTENER CONFIGURACIÓN BOLD
    =============================================*/
    static public function mdlObtenerConfiguracionBold()
    {
        self::mdlVerificarTabla();
        try {
            $stmt = Conexion::conectar()->prepare("SELECT bold_activo, bold_api_key, bold_secret_key FROM configuracion WHERE id = 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["bold_activo" => 0, "bold_api_key" => "", "bold_secret_key" => ""];
        }
    }

    /*=============================================
    ACTUALIZAR CONFIGURACIÓN BOLD
    =============================================*/
    static public function mdlActualizarConfiguracionBold($datos)
    {
        self::mdlVerificarTabla();
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE configuracion SET bold_activo = :bold_activo, bold_api_key = :bold_api_key, bold_secret_key = :bold_secret_key WHERE id = 1");
            $boldActivo = intval($datos["bold_activo"] ?? 0);
            $boldApiKey = !empty($datos["bold_api_key"]) ? trim($datos["bold_api_key"]) : null;
            $boldSecretKey = !empty($datos["bold_secret_key"]) ? trim($datos["bold_secret_key"]) : null;

            $stmt->bindParam(":bold_activo", $boldActivo, PDO::PARAM_INT);
            $stmt->bindParam(":bold_api_key", $boldApiKey, PDO::PARAM_STR);
            $stmt->bindParam(":bold_secret_key", $boldSecretKey, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (Exception $e) {
            return "error: " . $e->getMessage();
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
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si tiene variantes activas, cargarlas
        $db = Conexion::conectar();
        $idBodega = 1;
        try {
            $stmtBod = $db->query("SELECT id FROM bodegas WHERE estado = 1 ORDER BY id ASC LIMIT 1");
            if ($stmtBod && $rowBod = $stmtBod->fetch(PDO::FETCH_ASSOC)) {
                $idBodega = intval($rowBod["id"]);
            }
        } catch (Exception $e) {}

        require_once __DIR__ . "/productos.modelo.php";

        foreach ($productos as &$prod) {
            $prod["variantes"] = [];
            if (!empty($prod["tiene_variantes"]) && intval($prod["tiene_variantes"]) === 1) {
                try {
                    $vars = ModeloProductos::mdlObtenerVariantesProducto($prod["id"], $idBodega);
                    if (!empty($vars)) {
                        foreach ($vars as $v) {
                            if (isset($v["estado"]) && intval($v["estado"]) !== 1) {
                                continue;
                            }
                            $opcs = ModeloProductos::mdlObtenerOpcionesVariante($v["id"]);
                            $nombresOpc = [];
                            if (!empty($opcs)) {
                                foreach ($opcs as $o) {
                                    $nombresOpc[] = $o["nombre"];
                                }
                            }
                            $nombreVarStr = !empty($nombresOpc) ? implode(" / ", $nombresOpc) : "Opción #" . $v["id"];
                            $precioAdicional = floatval($v["precio_adicional"] ?? 0);
                            $precioFinal = floatval($prod["precio_venta"]) + $precioAdicional;

                            $prod["variantes"][] = [
                                "id" => intval($v["id"]),
                                "sku" => $v["sku"] ?? "",
                                "nombre_opcion" => $nombreVarStr,
                                "precio_adicional" => $precioAdicional,
                                "precio_final" => $precioFinal,
                                "stock" => intval($v["stock"] ?? 0),
                                "imagen" => !empty($v["imagen"]) ? $v["imagen"] : ""
                            ];
                        }
                    }
                } catch (Exception $eVar) {
                    $prod["variantes"] = [];
                }
            }
        }
        unset($prod);

        return $productos;
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

            $nombre = trim($datos["nombre"] ?? "");
            $celular = trim($datos["celular"] ?? ($datos["telefono"] ?? ""));
            $direccion = trim($datos["direccion"] ?? "");
            $notas = isset($datos["notas"]) ? trim($datos["notas"]) : "";
            $mesa = isset($datos["mesa"]) ? trim($datos["mesa"]) : "";
            $metodoPago = isset($datos["metodo_pago"]) ? trim($datos["metodo_pago"]) : "efectivo";
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

            // 3. PREPARAR LISTA DE PRODUCTOS Y CALCULAR TOTALES
            $listaProductosJSON = [];
            $totalNeto = 0;
            $totalImpuesto = 0;
            $totalVenta = 0;

            // Determinar bodega activa por defecto
            $idBodega = 1;
            $stmtBodega = $db->query("SELECT id FROM bodegas WHERE estado = 1 ORDER BY id ASC LIMIT 1");
            if ($stmtBodega && $rowBod = $stmtBodega->fetch(PDO::FETCH_ASSOC)) {
                $idBodega = intval($rowBod["id"]);
            }

            foreach ($productos as $prod) {
                $idProd = intval($prod["id"]);
                $cantidad = max(1, intval($prod["cantidad"]));
                $esVariante = (!empty($prod["es_variante"]) || !empty($prod["esVariante"])) ? 1 : 0;
                $idVariante = (!empty($prod["id_variante"]) ? intval($prod["id_variante"]) : (!empty($prod["idVariante"]) ? intval($prod["idVariante"]) : null));

                // Obtener datos reales del producto para garantizar precio verídico e impuesto correcto
                $stmtProd = $db->prepare("SELECT id, codigo, descripcion, precio_venta, tiene_variantes, tributo_id, tasa_impuesto, es_excluido FROM productos WHERE id = :id");
                $stmtProd->bindParam(":id", $idProd, PDO::PARAM_INT);
                $stmtProd->execute();
                $productoBD = $stmtProd->fetch(PDO::FETCH_ASSOC);

                if (!$productoBD) {
                    continue;
                }

                $descripcionItem = $productoBD["descripcion"];
                $precio = floatval($productoBD["precio_venta"]);
                $skuItem = $productoBD["codigo"];

                // Obtener impuesto configurado del producto en base de datos
                $impuestoPorcentaje = 0;
                $impuestoNombre = "Exento";

                if (empty($productoBD["es_excluido"])) {
                    if (!empty($productoBD["tributo_id"])) {
                        try {
                            $stmtTrib = $db->prepare("SELECT nombre, codigo, porcentaje_defecto FROM factus_tributos WHERE id = :id");
                            $stmtTrib->bindParam(":id", $productoBD["tributo_id"], PDO::PARAM_INT);
                            $stmtTrib->execute();
                            $tributoBD = $stmtTrib->fetch(PDO::FETCH_ASSOC);
                            if ($tributoBD) {
                                if (strtoupper($tributoBD["codigo"] ?? '') !== 'ZA') {
                                    $impuestoPorcentaje = floatval($tributoBD["porcentaje_defecto"] ?? 0);
                                    $impuestoNombre = $tributoBD["nombre"] ?? "IVA";
                                } else {
                                    $impuestoPorcentaje = 0;
                                    $impuestoNombre = "Excluido";
                                }
                            }
                        } catch (Exception $eTrib) {}
                    }
                    // Fallback si no tiene tributo_id o tabla factus, pero tiene tasa_impuesto directa
                    if ($impuestoPorcentaje == 0 && !empty($productoBD["tasa_impuesto"]) && floatval($productoBD["tasa_impuesto"]) > 0) {
                        $impuestoPorcentaje = floatval($productoBD["tasa_impuesto"]);
                        $impuestoNombre = "IVA";
                    }
                } else {
                    $impuestoPorcentaje = 0;
                    $impuestoNombre = "Excluido";
                }

                // Si el item viene con variante, validar variante y sumar precio adicional
                if ($esVariante && !empty($idVariante)) {
                    $stmtVar = $db->prepare("SELECT pv.*, pvb.stock FROM productos_variantes pv LEFT JOIN productos_variantes_bodegas pvb ON pv.id = pvb.id_variante AND pvb.id_bodega = :id_bodega WHERE pv.id = :id_variante AND pv.id_producto = :id_producto");
                    $stmtVar->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
                    $stmtVar->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
                    $stmtVar->bindParam(":id_producto", $idProd, PDO::PARAM_INT);
                    $stmtVar->execute();
                    $varianteBD = $stmtVar->fetch(PDO::FETCH_ASSOC);

                    if ($varianteBD) {
                        $precio += floatval($varianteBD["precio_adicional"]);
                        if (!empty($varianteBD["sku"])) {
                            $skuItem = $varianteBD["sku"];
                        }
                        $stmtOpc = $db->prepare("SELECT ov.nombre FROM productos_variantes_opciones pvo INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id WHERE pvo.id_producto_variante = :id_variante");
                        $stmtOpc->bindParam(":id_variante", $idVariante, PDO::PARAM_INT);
                        $stmtOpc->execute();
                        $opcsBD = $stmtOpc->fetchAll(PDO::FETCH_ASSOC);
                        $nombresOpc = [];
                        foreach ($opcsBD as $oBD) {
                            $nombresOpc[] = $oBD["nombre"];
                        }
                        if (!empty($nombresOpc)) {
                            $descripcionItem .= " - " . implode(" / ", $nombresOpc);
                        }
                    }
                }

                $subtotalItem = $precio * $cantidad;
                $totalVenta += $subtotalItem;

                // Calcular impuesto monetario del producto (precios incluyen impuesto)
                if ($impuestoPorcentaje > 0) {
                    $baseItem = $subtotalItem / (1 + ($impuestoPorcentaje / 100));
                    $impuestoItem = $subtotalItem - $baseItem;
                    $totalImpuesto += $impuestoItem;
                }

                $itemJSON = [
                    "id" => $productoBD["id"],
                    "codigo" => $skuItem,
                    "descripcion" => $descripcionItem,
                    "cantidad" => $cantidad,
                    "stock" => "999",
                    "precio" => $precio,
                    "total" => $subtotalItem,
                    "impuesto" => $impuestoPorcentaje,
                    "impuestoNombre" => $impuestoNombre
                ];

                if ($esVariante && !empty($idVariante)) {
                    $itemJSON["esVariante"] = "1";
                    $itemJSON["idVariante"] = (string)$idVariante;
                    $itemJSON["skuVariante"] = $skuItem;
                }

                $listaProductosJSON[] = $itemJSON;
            }

            if (empty($listaProductosJSON)) {
                throw new Exception("No se encontraron productos válidos en el carrito.");
            }

            $totalImpuesto = round($totalImpuesto, 2);
            $totalNeto = round($totalVenta - $totalImpuesto, 2);
            $jsonProductos = json_encode($listaProductosJSON, JSON_UNESCAPED_UNICODE);

            // 4. CONSTRUIR OBSERVACIÓN
            $prefijoObs = ($metodoPago === "bold") ? "[Menú Digital] [Pago en Línea Bold] " : "[Menú Digital] ";

            $observacionCompleta = $prefijoObs;
            if (!empty($mesa)) {
                $observacionCompleta .= "MESA: " . $mesa . " | ";
            }
            $observacionCompleta .= "DIR/ENTREGA: " . $direccion . " | TEL: " . $celular;
            if (!empty($notas)) {
                $observacionCompleta .= " | NOTAS: " . $notas;
            }

            // 5. ASIGNAR VENDEDOR Y BODEGA POR DEFECTO
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

            // =========================================================================
            // FLUJO 1: PAGO EN LÍNEA CON BOLD (NO CREAR ORDEN HASTA QUE EL PAGO SE APRUEBE)
            // =========================================================================
            if ($metodoPago === "bold") {
                $configBold = self::mdlObtenerConfiguracionBold();
                if (empty($configBold["bold_activo"]) || intval($configBold["bold_activo"]) !== 1 || empty($configBold["bold_api_key"]) || empty($configBold["bold_secret_key"])) {
                    throw new Exception("La pasarela de pagos Bold no está activa o configurada en este momento. Por favor selecciona 'Pago contra entrega' o revisa la configuración en el panel.");
                }

                $amountInt = intval(round($totalVenta));
                $currency = "COP";
                $sessionId = "BOLD-" . time() . "-" . mt_rand(1000, 9999);
                $secretKey = trim($configBold["bold_secret_key"]);
                $integritySignature = hash("sha256", $sessionId . $amountInt . $currency . $secretKey);

                $isHttps = (
                    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                    ($_SERVER['SERVER_PORT'] ?? 0) == 443 ||
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
                    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
                );
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $isLocal = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost:') === 0);
                $protocolo = ($isHttps || !$isLocal) ? "https" : "http";

                $uri = $_SERVER['REQUEST_URI'] ?? '';
                $cleanUri = preg_replace('/\/ajax(\/.*)?$/i', '', strtok($uri, '?'));
                if ($cleanUri == '/' || $cleanUri == '\\') {
                    $cleanUri = '';
                }
                $redirectionUrl = $protocolo . "://" . $host . $cleanUri . "/index.php?ruta=menu&bold_session=" . $sessionId;
                if (!empty($mesa)) {
                    $redirectionUrl .= "&mesa=" . urlencode($mesa);
                }

                $boldParams = [
                    "orderId" => $sessionId,
                    "currency" => $currency,
                    "amount" => (string)$amountInt,
                    "apiKey" => trim($configBold["bold_api_key"]),
                    "integritySignature" => $integritySignature,
                    "redirectionUrl" => $redirectionUrl,
                    "description" => "Pedido Menu Digital"
                ];
                $checkoutUrl = self::mdlGenerarUrlBold($boldParams);

                // Guardar pre-orden temporal en ordenes_temporales_bold SIN crear la orden en ventas todavía
                $datosOrdenTemp = [
                    "nombre" => $nombre,
                    "celular" => $celular,
                    "direccion" => $direccion,
                    "mesa" => $mesa,
                    "notas" => $notas,
                    "id_cliente" => $idCliente,
                    "id_vendedor" => $idVendedor,
                    "id_bodega" => $idBodega,
                    "json_productos" => $jsonProductos,
                    "impuesto" => $totalImpuesto,
                    "neto" => $totalNeto,
                    "total" => $totalVenta,
                    "observacion" => $observacionCompleta
                ];

                $stmtTemp = $db->prepare("INSERT INTO ordenes_temporales_bold (session_id, datos_orden, total, estado, fecha_creacion) VALUES (:session_id, :datos_orden, :total, 'pendiente', :fecha)");
                $stmtTemp->bindParam(":session_id", $sessionId, PDO::PARAM_STR);
                $jsonDatosTemp = json_encode($datosOrdenTemp, JSON_UNESCAPED_UNICODE);
                $stmtTemp->bindParam(":datos_orden", $jsonDatosTemp, PDO::PARAM_STR);
                $stmtTemp->bindParam(":total", $totalVenta, PDO::PARAM_STR);
                $stmtTemp->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);
                $stmtTemp->execute();

                if ($db && $db->inTransaction()) {
                    $db->commit();
                }

                return [
                    "status" => "ok",
                    "pago_online" => true,
                    "esperando_pago" => true,
                    "session_id" => $sessionId,
                    "total" => $totalVenta,
                    "bold" => [
                        "api_key" => trim($configBold["bold_api_key"]),
                        "order_id" => $sessionId,
                        "amount" => (string)$amountInt,
                        "currency" => $currency,
                        "integrity_signature" => $integritySignature,
                        "description" => "Pedido Menu Digital",
                        "redirection_url" => $redirectionUrl,
                        "checkout_url" => $checkoutUrl
                    ]
                ];
            }

            // =========================================================================
            // FLUJO 2: PAGO CONTRA ENTREGA O EN MESA (CREAR ORDEN INMEDIATAMENTE)
            // =========================================================================

            // Obtener consecutivo de orden (Tabla ventas)
            $stmtConsecutivo = $db->prepare("SELECT ultimo_numero FROM consecutivos WHERE tabla = 'ventas'");
            $stmtConsecutivo->execute();
            $resConsecutivo = $stmtConsecutivo->fetch(PDO::FETCH_ASSOC);

            if ($resConsecutivo && isset($resConsecutivo["ultimo_numero"])) {
                $codigoOrden = intval($resConsecutivo["ultimo_numero"]) + 1;
            } else {
                $stmtMax = $db->prepare("SELECT MAX(codigo) as max_cod FROM ventas");
                $stmtMax->execute();
                $resMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
                $codigoOrden = !empty($resMax["max_cod"]) ? (intval($resMax["max_cod"]) + 1) : 10001;
            }

            $stmtUpdConsecutivo = $db->prepare("UPDATE consecutivos SET ultimo_numero = :codigo WHERE tabla = 'ventas'");
            $stmtUpdConsecutivo->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmtUpdConsecutivo->execute();

            $metodoPagoTexto = "Pendiente (Menú Digital)";

            // Insertar en tabla ventas con estado = 'orden'
            $sqlInsertOrden = "
                INSERT INTO ventas (
                    codigo, numero_factura, id_cliente, id_vendedor, id_bodega, productos, 
                    impuesto, neto, total, metodo_pago, notas, observacion, estado, fecha
                ) VALUES (
                    :codigo, '', :id_cliente, :id_vendedor, :id_bodega, :productos, 
                    :impuesto, :neto, :total, :metodo_pago, :notas, :observacion, 'orden', :fecha
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
            $stmtInsertOrden->bindParam(":metodo_pago", $metodoPagoTexto, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":notas", $notas, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":observacion", $observacionCompleta, PDO::PARAM_STR);
            $stmtInsertOrden->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);

            $stmtInsertOrden->execute();
            $idOrden = $db->lastInsertId();

            if ($db && $db->inTransaction()) {
                $db->commit();
            }

            // Notificación al restaurante para pedidos contra entrega
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
                "metodo_pago" => $metodoPago,
                "mensaje" => "¡Tu pedido ha sido recibido con éxito! Código de orden: #" . $codigoOrden
            ];

        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            return [
                "status" => "error",
                "mensaje" => $e->getMessage()
            ];
        }
    }

    /*=============================================
    GENERAR URL OFICIAL DE CHECKOUT BOLD
    =============================================*/
    static public function mdlGenerarUrlBold($config)
    {
        $y = "BoldPaymentButton";
        $E = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.~=&";

        $f = function($t, $e) use ($E) {
            $n = strpos($E, $t);
            if ($n === false) return $t;
            return $E[($n + $e + 68) % 68];
        };

        $parts = [];
        foreach ($config as $k => $v) {
            if ($v !== null && $v !== '') {
                $keyParam = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $k));
                $parts[] = $keyParam . "=" . $v;
            }
        }
        $str = implode("<bold>", $parts);

        $encoded = "";
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $o = ord($y[$i % 17]);
            $encoded .= $f($str[$i], $o);
        }

        return "https://checkout.bold.co/btn?" . rawurlencode($encoded);
    }

    /*=============================================
    CONFIRMAR PAGO BOLD Y CREAR ORDEN DEFINITIVA
    =============================================*/
    static public function mdlConfirmarPagoYCrearOrden($sessionId, $transaccionId = '')
    {
        self::mdlVerificarTabla();
        try {
            $db = Conexion::conectar();
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT * FROM ordenes_temporales_bold WHERE session_id = :session_id FOR UPDATE");
            $stmt->bindParam(":session_id", $sessionId, PDO::PARAM_STR);
            $stmt->execute();
            $temp = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$temp) {
                if ($db && $db->inTransaction()) {
                    $db->rollBack();
                }
                return ["status" => "error", "mensaje" => "Sesión de pago no encontrada: " . $sessionId];
            }

            // Si ya fue aprobada y creada previamente, retornar el código existente (Idempotencia)
            if ($temp["estado"] === "aprobado" && !empty($temp["codigo_orden_final"])) {
                if ($db && $db->inTransaction()) {
                    $db->rollBack();
                }
                return [
                    "status" => "ok",
                    "codigo" => intval($temp["codigo_orden_final"]),
                    "id_orden" => intval($temp["id_orden_final"]),
                    "ya_procesado" => true
                ];
            }

            $datos = json_decode($temp["datos_orden"], true);
            if (!is_array($datos)) {
                if ($db && $db->inTransaction()) {
                    $db->rollBack();
                }
                return ["status" => "error", "mensaje" => "Datos de orden temporal corruptos"];
            }

            // 1. OBTENER CONSECUTIVO DE ORDEN (TABLA VENTAS)
            $stmtConsecutivo = $db->prepare("SELECT ultimo_numero FROM consecutivos WHERE tabla = 'ventas'");
            $stmtConsecutivo->execute();
            $resConsecutivo = $stmtConsecutivo->fetch(PDO::FETCH_ASSOC);

            if ($resConsecutivo && isset($resConsecutivo["ultimo_numero"])) {
                $codigoOrden = intval($resConsecutivo["ultimo_numero"]) + 1;
            } else {
                $stmtMax = $db->prepare("SELECT MAX(codigo) as max_cod FROM ventas");
                $stmtMax->execute();
                $resMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
                $codigoOrden = !empty($resMax["max_cod"]) ? (intval($resMax["max_cod"]) + 1) : 10001;
            }

            $stmtUpdConsecutivo = $db->prepare("UPDATE consecutivos SET ultimo_numero = :codigo WHERE tabla = 'ventas'");
            $stmtUpdConsecutivo->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmtUpdConsecutivo->execute();

            // 2. PREPARAR DATOS DE LA VENTA
            date_default_timezone_set('America/Bogota');
            $fechaActual = date('Y-m-d H:i:s');

            $metodoPagoTexto = "Bold (Aprobado)";
            $refTxt = (!empty($transaccionId) && $transaccionId !== 'RETORNO_CLIENTE') ? $transaccionId : $sessionId;
            $observacionCompleta = $datos["observacion"] . " | Ref Pago Bold: " . $refTxt;

            $sqlInsertOrden = "
                INSERT INTO ventas (
                    codigo, numero_factura, id_cliente, id_vendedor, id_bodega, productos, 
                    impuesto, neto, total, metodo_pago, notas, observacion, estado, fecha
                ) VALUES (
                    :codigo, '', :id_cliente, :id_vendedor, :id_bodega, :productos, 
                    :impuesto, :neto, :total, :metodo_pago, :notas, :observacion, 'orden', :fecha
                )
            ";

            $stmtInsert = $db->prepare($sqlInsertOrden);
            $stmtInsert->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmtInsert->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmtInsert->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
            $stmtInsert->bindParam(":id_bodega", $datos["id_bodega"], PDO::PARAM_INT);
            $stmtInsert->bindParam(":productos", $datos["json_productos"], PDO::PARAM_STR);
            $stmtInsert->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
            $stmtInsert->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
            $stmtInsert->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmtInsert->bindParam(":metodo_pago", $metodoPagoTexto, PDO::PARAM_STR);
            $stmtInsert->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
            $stmtInsert->bindParam(":observacion", $observacionCompleta, PDO::PARAM_STR);
            $stmtInsert->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);
            $stmtInsert->execute();

            $idOrden = $db->lastInsertId();

            // 3. ACTUALIZAR ORDEN TEMPORAL COMO APROBADA
            $stmtUpdTemp = $db->prepare("UPDATE ordenes_temporales_bold SET estado = 'aprobado', id_orden_final = :id_orden, codigo_orden_final = :codigo, transaccion_id = :transaccion_id, fecha_actualizacion = :fecha WHERE id = :id");
            $stmtUpdTemp->bindParam(":id_orden", $idOrden, PDO::PARAM_INT);
            $stmtUpdTemp->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmtUpdTemp->bindParam(":transaccion_id", $refTxt, PDO::PARAM_STR);
            $stmtUpdTemp->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);
            $stmtUpdTemp->bindParam(":id", $temp["id"], PDO::PARAM_INT);
            $stmtUpdTemp->execute();

            if ($db && $db->inTransaction()) {
                $db->commit();
            }

            // 4. NOTIFICACIÓN AUTOMÁTICA AL RESTAURANTE (Se dispara en el POS al confirmar pago)
            try {
                require_once __DIR__ . "/notificaciones.modelo.php";
                $detalleUbicacion = !empty($datos["mesa"]) ? "Mesa: " . $datos["mesa"] : "Entrega: " . $datos["direccion"];
                $mensajeNotif = "Orden #" . $codigoOrden . " (Pago Bold Aprobado) - Cliente: " . $datos["nombre"] . " (" . $detalleUbicacion . ") - Total: $" . number_format(floatval($datos["total"]), 2, ',', '.');
                $datosNotif = array(
                    "tipo" => "orden_menu_digital",
                    "titulo" => "Nuevo Pedido Menú Digital (Bold Aprobado)",
                    "mensaje" => $mensajeNotif,
                    "referencia_tipo" => "venta",
                    "referencia_id" => $idOrden,
                    "id_bodega" => $datos["id_bodega"]
                );
                ModeloNotificaciones::mdlCrearNotificacion($datosNotif);
            } catch (Exception $eNotif) {
                // No interrumpir si falla notificación
            }

            return [
                "status" => "ok",
                "codigo" => $codigoOrden,
                "id_orden" => $idOrden
            ];

        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            return [
                "status" => "error",
                "mensaje" => $e->getMessage()
            ];
        }
    }

    /*=============================================
    RECHAZAR ORDEN TEMPORAL BOLD
    =============================================*/
    static public function mdlRechazarOrdenTemporalBold($sessionId, $motivo = 'RECHAZADO')
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("UPDATE ordenes_temporales_bold SET estado = 'rechazado', transaccion_id = :motivo, fecha_actualizacion = NOW() WHERE session_id = :session_id AND estado = 'pendiente'");
            $stmt->bindParam(":motivo", $motivo, PDO::PARAM_STR);
            $stmt->bindParam(":session_id", $sessionId, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /*=============================================
    ACTUALIZAR ESTADO PAGO BOLD DESDE WEBHOOK (LEGACY)
    =============================================*/
    static public function mdlActualizarPagoBold($codigoOrden, $estadoPago, $transaccionId = '')
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("SELECT id, observacion FROM ventas WHERE codigo = :codigo");
            $stmt->bindParam(":codigo", $codigoOrden, PDO::PARAM_INT);
            $stmt->execute();
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orden) {
                return false;
            }

            $esAprobado = in_array(strtoupper($estadoPago), ['APROBADO', 'SALE_APPROVED', 'APPROVED', 'EXITOSO']);
            $nuevoMetodo = $esAprobado ? 'Bold (Aprobado)' : 'Bold (Rechazado)';
            $notaAdicional = " | Estado Bold: " . strtoupper($estadoPago) . ($transaccionId ? " (Ref: $transaccionId)" : "");
            $nuevaObservacion = $orden["observacion"] . $notaAdicional;

            $stmtUpd = $db->prepare("UPDATE ventas SET metodo_pago = :metodo_pago, observacion = :observacion WHERE id = :id");
            $stmtUpd->bindParam(":metodo_pago", $nuevoMetodo, PDO::PARAM_STR);
            $stmtUpd->bindParam(":observacion", $nuevaObservacion, PDO::PARAM_STR);
            $stmtUpd->bindParam(":id", $orden["id"], PDO::PARAM_INT);
            return $stmtUpd->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
