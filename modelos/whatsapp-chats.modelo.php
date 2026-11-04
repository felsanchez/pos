<?php

require_once "conexion.php";

class ModeloWhatsAppChats {

    /*=============================================
    NORMALIZAR TELÉFONO PARA WHATSAPP
    =============================================*/
    static public function normalizarTelefono($telefono) {
        $limpio = preg_replace('/\D/', '', $telefono ?? '');
        if (empty($limpio)) {
            return '';
        }
        // Celular Colombia estándar de 10 dígitos (ej: 3218142039)
        if (strlen($limpio) === 10 && substr($limpio, 0, 1) === '3') {
            return '57' . $limpio;
        }
        // Si ya viene con 57 y 12 dígitos (ej: 573218142039)
        if (strlen($limpio) === 12 && substr($limpio, 0, 2) === '57') {
            return $limpio;
        }
        return $limpio;
    }

    /*=============================================
    VERIFICAR SI UN NOMBRE ES GENÉRICO
    =============================================*/
    static public function esNombreGenerico($nom) {
        if (empty($nom) || !is_string($nom)) return true;
        $t = trim(strtolower($nom));
        if ($t === '' || $t === 'null' || $t === 'undefined') return true;
        if (strpos($t, 'usuario whatsapp') !== false || strpos($t, 'usuario whats app') !== false) return true;
        if ($t === 'no registra' || $t === 'no_registra' || $t === 'cliente' || strpos($t, 'cliente ') === 0) return true;
        if (!preg_match('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/', $t)) return true;
        return false;
    }

    /*=============================================
    AUTO-REPARAR NOMBRES CORROMPIDOS EN BD
    =============================================*/
    static public function mdlAutoRepararNombresGenericos() {
        try {
            $db = Conexion::conectar();
            // 1. Vincular clientes registrados por teléfono si id_cliente está vacío
            $db->exec("
                UPDATE whatsapp_chats c
                INNER JOIN clientes cl ON (c.telefono = cl.telefono OR c.telefono = CONCAT('57', cl.telefono))
                SET c.id_cliente = cl.id
                WHERE c.id_cliente IS NULL
            ");

            // 2. Corregir chats donde el nombre sea 'Usuario Whatsapp' o genérico
            $db->exec("
                UPDATE whatsapp_chats c
                LEFT JOIN clientes cl ON c.id_cliente = cl.id
                SET c.nombre = CASE 
                    WHEN cl.nombre IS NOT NULL AND TRIM(cl.nombre) != '' AND LOWER(cl.nombre) NOT LIKE '%usuario whatsapp%' AND LOWER(cl.nombre) != 'no registra' THEN cl.nombre
                    ELSE CONCAT('+', c.telefono)
                END
                WHERE c.nombre LIKE '%Usuario Whatsapp%' OR c.nombre LIKE '%Usuario WhatsApp%' OR c.nombre = 'Usuario Whatsapp'
            ");

            // 3. Corregir etiquetas de medios [Archivo: audioMessage] e [Archivo: imageMessage]
            $db->exec("UPDATE whatsapp_mensajes SET tipo = 'audio', mensaje = '🎤 Mensaje de voz' WHERE mensaje LIKE '%audioMessage%' OR tipo = 'audioMessage'");
            $db->exec("UPDATE whatsapp_mensajes SET tipo = 'image', mensaje = '' WHERE (mensaje LIKE '%imageMessage%' OR tipo = 'imageMessage') AND (mensaje = '[Archivo: imageMessage]' OR mensaje = 'imageMessage')");
            $db->exec("UPDATE whatsapp_chats SET ultimo_mensaje = '🎤 Mensaje de voz' WHERE ultimo_mensaje LIKE '%audioMessage%'");
            $db->exec("UPDATE whatsapp_chats SET ultimo_mensaje = '📷 Foto' WHERE ultimo_mensaje LIKE '%imageMessage%'");
        } catch (Exception $e) {}
    }

    /*=============================================
    ASEGURAR TABLAS EN BD (SOLO UNA VEZ)
    =============================================*/
    private static $tablasAseguradas = false;

    static public function mdlAsegurarTablas() {
        if (self::$tablasAseguradas) {
            return;
        }
        self::$tablasAseguradas = true;

        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['wa_tablas_creadas'])) {
            return;
        }

        $db = Conexion::conectar();
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `whatsapp_chats` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `telefono` VARCHAR(50) NOT NULL,
                    `nombre` VARCHAR(150) NULL DEFAULT NULL,
                    `ultimo_mensaje` TEXT NULL,
                    `fecha_ultimo_mensaje` DATETIME NULL,
                    `no_leidos` INT(11) NOT NULL DEFAULT 0,
                    `id_cliente` INT(11) NULL DEFAULT NULL,
                    `origen` VARCHAR(50) NULL DEFAULT 'waflow',
                    `estado` VARCHAR(20) NOT NULL DEFAULT 'activo',
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_telefono` (`telefono`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS `whatsapp_mensajes` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `id_chat` INT(11) NOT NULL,
                    `waflow_message_id` VARCHAR(150) NULL DEFAULT NULL,
                    `direccion` ENUM('inbound', 'outbound') NOT NULL,
                    `remitente` VARCHAR(50) NOT NULL DEFAULT 'cliente',
                    `mensaje` TEXT NULL,
                    `tipo` VARCHAR(30) NOT NULL DEFAULT 'text',
                    `media_url` MEDIUMTEXT NULL,
                    `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `leido` TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `idx_chat` (`id_chat`),
                    KEY `idx_waflow_id` (`waflow_message_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['wa_tablas_creadas'] = true;
            }
        } catch (Exception $e) {}
    }

    /*=============================================
    SINCRONIZAR LEADS DE WHATSAPP Y CLIENTES EXISTENTES EN CRM
    =============================================*/
    static public function mdlSincronizarLeadsCRMExistentes($forzar = false) {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();
        $insertados = 0;
        $actualizados = 0;
        $totalProcesados = 0;

        try {
            // 1. Obtener leads del CRM que tengan origen WhatsApp O cliente con teléfono
            $stmt = $db->query("
                SELECT l.id, l.id_cliente, l.origen, l.resumen_ia, 
                       COALESCE(l.fecha_ultima_interaccion, l.fecha_creacion) as fecha_lead,
                       c.nombre as cliente_nombre, c.telefono as cliente_telefono
                FROM crm_leads l
                LEFT JOIN clientes c ON l.id_cliente = c.id
                WHERE (l.origen LIKE '%whatsapp%' OR (c.telefono IS NOT NULL AND TRIM(c.telefono) != ''))
                  AND (l.estado IS NULL OR l.estado != 'eliminado')
                ORDER BY fecha_lead ASC
            ");
            $leadsCRM = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($leadsCRM)) {
                foreach ($leadsCRM as $lead) {
                    $totalProcesados++;
                    $telRaw = !empty($lead['cliente_telefono']) ? trim($lead['cliente_telefono']) : '';
                    if (empty($telRaw) && preg_match('/\d{7,}/', $lead['origen'] ?? '', $m)) {
                        $telRaw = $m[0];
                    }

                    $telNorm = self::normalizarTelefono($telRaw);
                    $telLimpio = preg_replace('/\D/', '', $telRaw);

                    if (empty($telNorm) && empty($telLimpio)) {
                        continue;
                    }

                    $telPrincipal = !empty($telNorm) ? $telNorm : $telLimpio;
                    $tel10 = substr($telPrincipal, -10);

                    $nombre = (!empty($lead['cliente_nombre']) && !self::esNombreGenerico($lead['cliente_nombre'])) 
                        ? trim($lead['cliente_nombre']) 
                        : ('+' . $telPrincipal);
                    $mensaje = !empty($lead['resumen_ia']) ? trim($lead['resumen_ia']) : 'Lead registrado desde CRM / WhatsApp';
                    $fecha = !empty($lead['fecha_lead']) ? $lead['fecha_lead'] : date('Y-m-d H:i:s');

                    // Verificar si ya existe el chat
                    $chk = $db->prepare("
                        SELECT id, id_cliente, nombre 
                        FROM whatsapp_chats 
                        WHERE telefono = :tel 
                           OR telefono = :telNorm 
                           OR telefono LIKE :telLike 
                        LIMIT 1
                    ");
                    $telLike = '%' . $tel10;
                    $chk->bindParam(':tel', $telPrincipal, PDO::PARAM_STR);
                    $chk->bindParam(':telNorm', $telLimpio, PDO::PARAM_STR);
                    $chk->bindParam(':telLike', $telLike, PDO::PARAM_STR);
                    $chk->execute();
                    $chatExistente = $chk->fetch(PDO::FETCH_ASSOC);

                    if (!$chatExistente) {
                        $ins = $db->prepare("
                            INSERT INTO whatsapp_chats (telefono, nombre, ultimo_mensaje, fecha_ultimo_mensaje, no_leidos, id_cliente, origen)
                            VALUES (:tel, :nom, :msg, :fec, 0, :idCli, 'crm_leads')
                        ");
                        $ins->bindParam(':tel', $telPrincipal, PDO::PARAM_STR);
                        $ins->bindParam(':nom', $nombre, PDO::PARAM_STR);
                        $ins->bindParam(':msg', $mensaje, PDO::PARAM_STR);
                        $ins->bindParam(':fec', $fecha, PDO::PARAM_STR);
                        $ins->bindParam(':idCli', $lead['id_cliente'], PDO::PARAM_INT);
                        $ins->execute();
                        $idChat = $db->lastInsertId();

                        // Insertar mensaje inicial
                        $insMsg = $db->prepare("
                            INSERT INTO whatsapp_mensajes (id_chat, direccion, remitente, mensaje, tipo, fecha_hora, leido)
                            VALUES (:idChat, 'inbound', 'cliente', :msg, 'text', :fec, 1)
                        ");
                        $insMsg->bindParam(':idChat', $idChat, PDO::PARAM_INT);
                        $insMsg->bindParam(':msg', $mensaje, PDO::PARAM_STR);
                        $insMsg->bindParam(':fec', $fecha, PDO::PARAM_STR);
                        $insMsg->execute();
                        $insertados++;
                    } else {
                        // Actualizar cliente vinculado si no estaba asignado
                        if (empty($chatExistente['id_cliente']) && !empty($lead['id_cliente'])) {
                            $upd = $db->prepare("UPDATE whatsapp_chats SET id_cliente = :idCli WHERE id = :id");
                            $upd->bindParam(':idCli', $lead['id_cliente'], PDO::PARAM_INT);
                            $upd->bindParam(':id', $chatExistente['id'], PDO::PARAM_INT);
                            $upd->execute();
                            $actualizados++;
                        }
                        // Si el nombre guardado en la BD es genérico, restaurar nombre válido del lead
                        if (self::esNombreGenerico($chatExistente['nombre']) && !self::esNombreGenerico($nombre)) {
                            $updNom = $db->prepare("UPDATE whatsapp_chats SET nombre = :nom WHERE id = :id");
                            $updNom->bindParam(':nom', $nombre, PDO::PARAM_STR);
                            $updNom->bindParam(':id', $chatExistente['id'], PDO::PARAM_INT);
                            $updNom->execute();
                            $actualizados++;
                        }
                    }
                }
            }

            // 2. Si se forzó la sincronización, sincronizar también clientes con teléfono celular
            if ($forzar) {
                $stmtCli = $db->query("
                    SELECT id, nombre, telefono 
                    FROM clientes 
                    WHERE telefono IS NOT NULL AND TRIM(telefono) != '' 
                    ORDER BY id DESC LIMIT 200
                ");
                $clientes = $stmtCli->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($clientes)) {
                    foreach ($clientes as $cli) {
                        $telNorm = self::normalizarTelefono($cli['telefono']);
                        if (empty($telNorm)) continue;
                        $tel10 = substr($telNorm, -10);

                        $chk = $db->prepare("SELECT id FROM whatsapp_chats WHERE telefono = :tel OR telefono LIKE :telLike LIMIT 1");
                        $telLike = '%' . $tel10;
                        $chk->bindParam(':tel', $telNorm, PDO::PARAM_STR);
                        $chk->bindParam(':telLike', $telLike, PDO::PARAM_STR);
                        $chk->execute();

                        if ($chk->rowCount() == 0) {
                            $ins = $db->prepare("
                                INSERT INTO whatsapp_chats (telefono, nombre, ultimo_mensaje, fecha_ultimo_mensaje, no_leidos, id_cliente, origen)
                                VALUES (:tel, :nom, 'Contacto registrado en clientes', NOW(), 0, :idCli, 'clientes')
                            ");
                            $ins->bindParam(':tel', $telNorm, PDO::PARAM_STR);
                            $ins->bindParam(':nom', $cli['nombre'], PDO::PARAM_STR);
                            $ins->bindParam(':idCli', $cli['id'], PDO::PARAM_INT);
                            $ins->execute();
                            $insertados++;
                        }
                    }
                }
            }

            return [
                "success" => true,
                "insertados" => $insertados,
                "actualizados" => $actualizados,
                "total" => $totalProcesados
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    /*=============================================
    MOSTRAR CHATS
    =============================================*/
    static public function mdlMostrarChats($busqueda = null) {
        try {
            self::mdlAsegurarTablas();
            $db = Conexion::conectar();

            $sql = "
                SELECT c.*, cl.nombre as cliente_nombre_registrado, cl.email as cliente_email
                FROM whatsapp_chats c
                LEFT JOIN clientes cl ON c.id_cliente = cl.id
                WHERE c.estado = 'activo'
            ";

            if (!empty($busqueda)) {
                $sql .= " AND (c.nombre LIKE :busq OR c.telefono LIKE :busq OR c.ultimo_mensaje LIKE :busq)";
            }

            $sql .= " ORDER BY COALESCE(c.fecha_ultimo_mensaje, c.created_at) DESC";

            $stmt = $db->prepare($sql);

            if (!empty($busqueda)) {
                $busqParam = "%" . $busqueda . "%";
                $stmt->bindParam(":busq", $busqParam, PDO::PARAM_STR);
            }

            $stmt->execute();
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$chats) {
                return [];
            }

            // Sanear y asegurar nombres correctos permanentes
            foreach ($chats as &$chat) {
                $nomReg = !empty($chat['cliente_nombre_registrado']) ? trim($chat['cliente_nombre_registrado']) : '';
                $nomChat = !empty($chat['nombre']) ? trim($chat['nombre']) : '';

                if (self::esNombreGenerico($nomChat)) {
                    if (!empty($nomReg) && !self::esNombreGenerico($nomReg)) {
                        $chat['nombre'] = $nomReg;
                    } else {
                        $chat['nombre'] = '+' . $chat['telefono'];
                    }
                } elseif (!empty($nomReg) && !self::esNombreGenerico($nomReg) && (strpos(strtolower($nomChat), 'cliente ') === 0 || $nomChat === ('+' . $chat['telefono']))) {
                    $chat['nombre'] = $nomReg;
                }
            }
            unset($chat);

            return $chats;
        } catch (Exception $e) {
            return [];
        }
    }

    /*=============================================
    OBTENER UN CHAT POR ID
    =============================================*/
    static public function mdlObtenerChatPorId($idChat) {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();

        $stmt = $db->prepare("
            SELECT c.*, cl.nombre as cliente_nombre_registrado, cl.email as cliente_email, cl.documento as cliente_documento
            FROM whatsapp_chats c
            LEFT JOIN clientes cl ON c.id_cliente = cl.id
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->bindParam(":id", $idChat, PDO::PARAM_INT);
        $stmt->execute();
        $chat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chat) {
            $nomReg = !empty($chat['cliente_nombre_registrado']) ? trim($chat['cliente_nombre_registrado']) : '';
            $nomChat = !empty($chat['nombre']) ? trim($chat['nombre']) : '';

            if (self::esNombreGenerico($nomChat)) {
                if (!empty($nomReg) && !self::esNombreGenerico($nomReg)) {
                    $chat['nombre'] = $nomReg;
                } else {
                    $chat['nombre'] = '+' . $chat['telefono'];
                }
            } elseif (!empty($nomReg) && !self::esNombreGenerico($nomReg) && (strpos(strtolower($nomChat), 'cliente ') === 0 || $nomChat === ('+' . $chat['telefono']))) {
                $chat['nombre'] = $nomReg;
            }
        }

        return $chat;
    }

    /*=============================================
    MOSTRAR MENSAJES DE UN CHAT
    =============================================*/
    static public function mdlMostrarMensajes($idChat, $limite = 100) {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();

        $limiteInt = intval($limite);
        if ($limiteInt <= 0) $limiteInt = 100;

        $stmt = $db->prepare("
            SELECT *
            FROM whatsapp_mensajes
            WHERE id_chat = :idChat
            ORDER BY fecha_hora ASC, id ASC
            LIMIT {$limiteInt}
        ");
        $stmt->bindParam(":idChat", $idChat, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    REGISTRAR MENSAJE SALIENTE
    =============================================*/
    static public function mdlRegistrarMensajeSaliente($idChat, $mensaje, $waflowMsgId = null, $remitente = 'operador', $tipo = 'text', $mediaUrl = null) {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();

        $fechaHora = date('Y-m-d H:i:s');
        $direccion = 'outbound';
        $leido = 1;

        $stmt = $db->prepare("
            INSERT INTO whatsapp_mensajes (id_chat, waflow_message_id, direccion, remitente, mensaje, tipo, media_url, fecha_hora, leido)
            VALUES (:idChat, :wId, :dir, :rem, :msg, :tip, :mUrl, :fec, :lei)
        ");
        $stmt->bindParam(":idChat", $idChat, PDO::PARAM_INT);
        $stmt->bindParam(":wId", $waflowMsgId, PDO::PARAM_STR);
        $stmt->bindParam(":dir", $direccion, PDO::PARAM_STR);
        $stmt->bindParam(":rem", $remitente, PDO::PARAM_STR);
        $stmt->bindParam(":msg", $mensaje, PDO::PARAM_STR);
        $stmt->bindParam(":tip", $tipo, PDO::PARAM_STR);
        $stmt->bindParam(":mUrl", $mediaUrl, PDO::PARAM_STR);
        $stmt->bindParam(":fec", $fechaHora, PDO::PARAM_STR);
        $stmt->bindParam(":lei", $leido, PDO::PARAM_INT);
        $stmt->execute();

        $nuevoId = $db->lastInsertId();

        // Actualizar chat
        $stmtChat = $db->prepare("
            UPDATE whatsapp_chats
            SET ultimo_mensaje = :msg,
                fecha_ultimo_mensaje = :fec,
                no_leidos = 0
            WHERE id = :idChat
        ");
        $stmtChat->bindParam(":msg", $mensaje, PDO::PARAM_STR);
        $stmtChat->bindParam(":fec", $fechaHora, PDO::PARAM_STR);
        $stmtChat->bindParam(":idChat", $idChat, PDO::PARAM_INT);
        $stmtChat->execute();

        return [
            "id" => $nuevoId,
            "id_chat" => $idChat,
            "direccion" => $direccion,
            "remitente" => $remitente,
            "mensaje" => $mensaje,
            "fecha_hora" => $fechaHora
        ];
    }

    /*=============================================
    MARCAR CHAT COMO LEÍDO
    =============================================*/
    static public function mdlMarcarLeidos($idChat) {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();

        $stmt1 = $db->prepare("UPDATE whatsapp_chats SET no_leidos = 0 WHERE id = :id");
        $stmt1->bindParam(":id", $idChat, PDO::PARAM_INT);
        $stmt1->execute();

        $stmt2 = $db->prepare("UPDATE whatsapp_mensajes SET leido = 1 WHERE id_chat = :id");
        $stmt2->bindParam(":id", $idChat, PDO::PARAM_INT);
        $stmt2->execute();

        return true;
    }

    /*=============================================
    CREAR O BUSCAR CHAT POR TELÉFONO
    =============================================*/
    static public function mdlCrearOBuscarChat($telefono, $nombre = null) {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();

        $telNorm = self::normalizarTelefono($telefono);
        $telLimpio = preg_replace('/\D/', '', $telefono);
        $telPrincipal = !empty($telNorm) ? $telNorm : $telLimpio;
        $tel10 = substr($telPrincipal, -10);

        // Buscar existente
        $stmt = $db->prepare("
            SELECT * 
            FROM whatsapp_chats 
            WHERE telefono = :tel 
               OR telefono = :telLimpio 
               OR telefono LIKE :telLike 
            LIMIT 1
        ");
        $telLike = '%' . $tel10;
        $stmt->bindParam(":tel", $telPrincipal, PDO::PARAM_STR);
        $stmt->bindParam(":telLimpio", $telLimpio, PDO::PARAM_STR);
        $stmt->bindParam(":telLike", $telLike, PDO::PARAM_STR);
        $stmt->execute();
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            // Si el nombre existente es genérico y ahora nos pasan un nombre válido, actualizarlo
            if (!empty($nombre) && !self::esNombreGenerico($nombre) && self::esNombreGenerico($existente['nombre'])) {
                $upd = $db->prepare("UPDATE whatsapp_chats SET nombre = :nom WHERE id = :id");
                $upd->bindParam(':nom', $nombre, PDO::PARAM_STR);
                $upd->bindParam(':id', $existente['id'], PDO::PARAM_INT);
                $upd->execute();
                $existente['nombre'] = $nombre;
            }
            return $existente;
        }

        // Buscar si existe en clientes
        $stmtCli = $db->prepare("SELECT id, nombre FROM clientes WHERE telefono LIKE :tel LIMIT 1");
        $stmtCli->bindParam(':tel', $telLike, PDO::PARAM_STR);
        $stmtCli->execute();
        $cliente = $stmtCli->fetch(PDO::FETCH_ASSOC);

        $idCliente = $cliente ? $cliente['id'] : null;
        $nombreCliente = ($cliente && !empty($cliente['nombre']) && !self::esNombreGenerico($cliente['nombre'])) ? $cliente['nombre'] : null;

        if (!empty($nombre) && !self::esNombreGenerico($nombre)) {
            $nombreFinal = $nombre;
        } elseif ($nombreCliente) {
            $nombreFinal = $nombreCliente;
        } else {
            $nombreFinal = '+' . $telPrincipal;
        }

        $stmtInsert = $db->prepare("
            INSERT INTO whatsapp_chats (telefono, nombre, id_cliente, fecha_ultimo_mensaje, no_leidos)
            VALUES (:tel, :nom, :idCli, NOW(), 0)
        ");
        $stmtInsert->bindParam(":tel", $telPrincipal, PDO::PARAM_STR);
        $stmtInsert->bindParam(":nom", $nombreFinal, PDO::PARAM_STR);
        $stmtInsert->bindParam(":idCli", $idCliente, PDO::PARAM_INT);
        $stmtInsert->execute();

        $nuevoId = $db->lastInsertId();
        return self::mdlObtenerChatPorId($nuevoId);
    }

    /*=============================================
    CONTEO TOTAL NO LEÍDOS
    =============================================*/
    static public function mdlConteoNoLeidosTotal() {
        self::mdlAsegurarTablas();
        $db = Conexion::conectar();
        $stmt = $db->query("SELECT SUM(no_leidos) as total FROM whatsapp_chats WHERE estado = 'activo'");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($res['total'] ?? 0);
    }
}
