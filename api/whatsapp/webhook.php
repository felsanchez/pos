<?php
/**
 * ============================================================
 * WEBHOOK RECEPTOR DE MENSAJES DE WHATSAPP (WAFLOW.AI)
 * KONTROL POS
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Manejo de preflight / CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handshake de verificación si WaFloW envía GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Si envían challenge o verify token (estilo Meta)
    if (isset($_GET['hub_challenge'])) {
        echo $_GET['hub_challenge'];
        exit();
    }
    echo json_encode(["status" => "active", "service" => "Kontrol POS WhatsApp Webhook"]);
    exit();
}

// Leer payload entrante
$rawInput = file_get_contents('php://input');

// Registrar log para depuración e inspección de la estructura exacta de WaFloW
$logFile = __DIR__ . '/log_webhook.txt';
$fechaLog = date('Y-m-d H:i:s');
file_put_contents(
    $logFile,
    "=== [{$fechaLog}] Evento Recibido ===\n" . $rawInput . "\n\n",
    FILE_APPEND
);

$data = json_decode($rawInput, true);

if (!$data) {
    // Responder 200 para evitar que WaFloW reintente infinitamente
    http_response_code(200);
    echo json_encode(["success" => false, "message" => "Payload vacío o JSON no válido"]);
    exit();
}

try {
    // Conectar a la base de datos de Kontrol POS
    require_once __DIR__ . '/../../modelos/conexion.php';
    $db = Conexion::conectar();
    try {
        $db->exec("SET NAMES utf8mb4");
    } catch (Exception $e) {}

    // 1. Asegurar que existan las tablas necesarias
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

    try {
        $db->exec("ALTER TABLE `whatsapp_mensajes` MODIFY `media_url` MEDIUMTEXT NULL");
    } catch (Exception $e) {}

function normalizarTelefonoWebhook($telefono) {
    $limpio = preg_replace('/\D/', '', $telefono ?? '');
    if (empty($limpio)) return '';
    if (strlen($limpio) === 10 && substr($limpio, 0, 1) === '3') {
        return '57' . $limpio;
    }
    if (strlen($limpio) === 12 && substr($limpio, 0, 2) === '57') {
        return $limpio;
    }
    return $limpio;
}

function esNombreGenericoWebhook($nom) {
    if (empty($nom)) return true;
    $t = trim(mb_strtolower($nom, 'UTF-8'));
    if ($t === '' || $t === 'null' || $t === 'undefined') return true;
    if (strpos($t, 'usuario whatsapp') !== false || strpos($t, 'usuario whats app') !== false) return true;
    if ($t === 'no registra' || $t === 'no_registra' || $t === 'cliente' || strpos($t, 'cliente ') === 0) return true;
    // Si no contiene ninguna letra (ej: sólo números, signos +, espacios)
    if (!preg_match('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $t)) return true;
    return false;
}

// Funciones para extracción y descarga local de archivos multimedia (Audio, Imagen, Video, Documento)
function esUrlOMediaValidaWebhook($str) {
    if (empty($str) || !is_string($str)) return false;
    $s = trim($str);
    if (preg_match('#^https?://#i', $s)) {
        // Ignorar endpoints de control de la propia app
        if (stripos($s, 'webhook.php') !== false || stripos($s, 'test_conexion.php') !== false) {
            return false;
        }
        return true;
    }
    if (preg_match('#^data:(image|audio|video|application)/#i', $s)) {
        return true;
    }
    // Base64 largo sin espacios
    if (strlen($s) > 120 && !preg_match('#\s#', $s) && preg_match('#^[A-Za-z0-9+/=]+$#', $s)) {
        return true;
    }
    return false;
}

function buscarMediaRecursivaWebhook($arr, $profundidad = 0) {
    if ($profundidad > 6 || !is_array($arr)) return null;
    foreach ($arr as $key => $val) {
        if (is_string($val) && esUrlOMediaValidaWebhook($val)) {
            return trim($val);
        }
        if (is_array($val)) {
            $encontrado = buscarMediaRecursivaWebhook($val, $profundidad + 1);
            if ($encontrado) return $encontrado;
        }
    }
    return null;
}

function extraerMediaUrlWebhook($data) {
    if (!is_array($data)) return null;
    $candidatos = [];

    $clavesPrioritarias = [
        'mediaUrl', 'media_url', 'url', 'fileUrl', 'file_url', 'audioUrl', 'imageUrl', 
        'videoUrl', 'downloadUrl', 'download_url', 'directPath', 'attachment_url', 
        'attachmentUrl', 'link', 'base64', 'file', 'attachment'
    ];

    // 1. Campos directos en la raíz
    foreach ($clavesPrioritarias as $k) {
        if (!empty($data[$k]) && is_string($data[$k])) {
            $candidatos[] = $data[$k];
        }
    }

    // 2. Si hay subobjeto 'message' o 'data'
    $contenedores = [];
    if (isset($data['message']) && is_array($data['message'])) $contenedores[] = $data['message'];
    if (isset($data['data']['message']) && is_array($data['data']['message'])) $contenedores[] = $data['data']['message'];
    if (isset($data['data']) && is_array($data['data'])) $contenedores[] = $data['data'];

    foreach ($contenedores as $sub) {
        foreach ($clavesPrioritarias as $k) {
            if (!empty($sub[$k]) && is_string($sub[$k])) {
                $candidatos[] = $sub[$k];
            }
        }

        // Subestructuras Baileys / WhatsApp: audioMessage, imageMessage, videoMessage, documentMessage, stickerMessage
        foreach (['audioMessage', 'imageMessage', 'videoMessage', 'documentMessage', 'stickerMessage', 'audio', 'image', 'video', 'document', 'file', 'attachment', 'media'] as $subKey) {
            if (isset($sub[$subKey]) && is_array($sub[$subKey])) {
                foreach ($clavesPrioritarias as $k) {
                    if (!empty($sub[$subKey][$k]) && is_string($sub[$subKey][$k])) {
                        $candidatos[] = $sub[$subKey][$k];
                    }
                }
            } elseif (isset($sub[$subKey]) && is_string($sub[$subKey])) {
                $candidatos[] = $sub[$subKey];
            }
        }
    }

    // 3. Si hay lista de adjuntos (attachments o files como array)
    foreach (['attachments', 'files', 'media'] as $listKey) {
        if (isset($data[$listKey]) && is_array($data[$listKey])) {
            foreach ($data[$listKey] as $item) {
                if (is_string($item)) {
                    $candidatos[] = $item;
                } elseif (is_array($item)) {
                    foreach ($clavesPrioritarias as $k) {
                        if (!empty($item[$k]) && is_string($item[$k])) {
                            $candidatos[] = $item[$k];
                        }
                    }
                }
            }
        }
    }

    // 4. Si hay 'entry' (estilo Meta Cloud API)
    if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
        $metaMsg = $data['entry'][0]['changes'][0]['value']['messages'][0];
        foreach (['image', 'audio', 'video', 'document', 'voice'] as $mKey) {
            if (isset($metaMsg[$mKey]) && is_array($metaMsg[$mKey])) {
                if (!empty($metaMsg[$mKey]['url'])) $candidatos[] = $metaMsg[$mKey]['url'];
                if (!empty($metaMsg[$mKey]['mediaUrl'])) $candidatos[] = $metaMsg[$mKey]['mediaUrl'];
            }
        }
    }

    // Validar el primer candidato prioritario válido
    foreach ($candidatos as $cand) {
        if (esUrlOMediaValidaWebhook($cand)) {
            return trim($cand);
        }
    }

    // 5. Búsqueda profunda recursiva en todo el payload
    return buscarMediaRecursivaWebhook($data);
}

function determinarTipoMensajeWebhook($data, $mediaUrl, $mensajeTexto) {
    $tipoRaw = strtolower(trim($data['message']['type'] ?? ($data['type'] ?? '')));
    if (strpos($tipoRaw, 'audio') !== false || strpos($tipoRaw, 'voice') !== false || strpos($tipoRaw, 'ptt') !== false) {
        return 'audio';
    }
    if (strpos($tipoRaw, 'image') !== false || strpos($tipoRaw, 'foto') !== false) {
        return 'image';
    }
    if (strpos($tipoRaw, 'video') !== false) {
        return 'video';
    }
    if (strpos($tipoRaw, 'document') !== false || strpos($tipoRaw, 'file') !== false) {
        return 'document';
    }

    if (stripos($mensajeTexto, 'audioMessage') !== false) {
        return 'audio';
    }
    if (stripos($mensajeTexto, 'imageMessage') !== false) {
        return 'image';
    }
    if (stripos($mensajeTexto, 'videoMessage') !== false) {
        return 'video';
    }
    if (stripos($mensajeTexto, 'documentMessage') !== false) {
        return 'document';
    }

    if (!empty($mediaUrl)) {
        if (preg_match('#\.(ogg|opus|mp3|wav|m4a|aac)($|\?)#i', $mediaUrl) || stripos($mediaUrl, 'data:audio/') === 0) {
            return 'audio';
        }
        if (preg_match('#\.(jpg|jpeg|png|gif|webp|bmp)($|\?)#i', $mediaUrl) || stripos($mediaUrl, 'data:image/') === 0) {
            return 'image';
        }
        if (preg_match('#\.(mp4|webm|avi|mov)($|\?)#i', $mediaUrl) || stripos($mediaUrl, 'data:video/') === 0) {
            return 'video';
        }
        if (preg_match('#\.(pdf|docx?|xlsx?|txt|zip)($|\?)#i', $mediaUrl) || stripos($mediaUrl, 'data:application/') === 0) {
            return 'document';
        }
    }

    return 'text';
}

function descargarMediaLocalWhatsApp($mediaUrl, $tipo, $msgId) {
    if (empty($mediaUrl)) return null;

    if (strpos($mediaUrl, 'vistas/img/whatsapp_media/') !== false) {
        return $mediaUrl;
    }

    $dirDestino = dirname(dirname(__DIR__)) . '/vistas/img/whatsapp_media';
    if (!file_exists($dirDestino)) {
        @mkdir($dirDestino, 0777, true);
    }

    $ext = 'bin';
    if ($tipo === 'audio') $ext = 'ogg';
    elseif ($tipo === 'image') $ext = 'jpg';
    elseif ($tipo === 'video') $ext = 'mp4';
    elseif ($tipo === 'document') $ext = 'pdf';

    if (preg_match('#\.(ogg|opus|mp3|wav|m4a|aac|jpg|jpeg|png|gif|webp|mp4|pdf|docx?|xlsx?)($|\?)#i', $mediaUrl, $mExt)) {
        $ext = strtolower($mExt[1]);
        if ($ext === 'jpeg') $ext = 'jpg';
    }

    $hash = substr(md5($mediaUrl . $msgId), 0, 10);
    $nombreArchivo = 'wa_' . date('Ymd_His') . '_' . $hash . '.' . $ext;
    $rutaFisica = $dirDestino . '/' . $nombreArchivo;
    $rutaWeb = 'vistas/img/whatsapp_media/' . $nombreArchivo;

    // Caso 1: Data URI con prefijo
    if (preg_match('#^data:([^;]+);base64,(.+)$#s', $mediaUrl, $m)) {
        $bin = base64_decode($m[2]);
        if ($bin !== false && strlen($bin) > 0) {
            @file_put_contents($rutaFisica, $bin);
            return $rutaWeb;
        }
    }

    // Caso 2: Base64 puro sin prefijo
    if (strlen($mediaUrl) > 100 && !preg_match('#^https?://#i', $mediaUrl) && preg_match('#^[A-Za-z0-9+/=]+$#', $mediaUrl)) {
        $bin = base64_decode($mediaUrl);
        if ($bin !== false && strlen($bin) > 0) {
            @file_put_contents($rutaFisica, $bin);
            return $rutaWeb;
        }
    }

    // Caso 3: URL HTTP/HTTPS
    if (preg_match('#^https?://#i', $mediaUrl)) {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $mediaUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) KontrolPOS/1.0'
            ]);
            $contenido = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300 && !empty($contenido)) {
                if ($contentType) {
                    if (strpos($contentType, 'audio/ogg') !== false || strpos($contentType, 'audio/opus') !== false) $ext = 'ogg';
                    elseif (strpos($contentType, 'audio/mpeg') !== false || strpos($contentType, 'audio/mp3') !== false) $ext = 'mp3';
                    elseif (strpos($contentType, 'image/jpeg') !== false) $ext = 'jpg';
                    elseif (strpos($contentType, 'image/png') !== false) $ext = 'png';
                    elseif (strpos($contentType, 'image/webp') !== false) $ext = 'webp';

                    $nombreArchivo = 'wa_' . date('Ymd_His') . '_' . $hash . '.' . $ext;
                    $rutaFisica = $dirDestino . '/' . $nombreArchivo;
                    $rutaWeb = 'vistas/img/whatsapp_media/' . $nombreArchivo;
                }

                @file_put_contents($rutaFisica, $contenido);
                return $rutaWeb;
            }
        } catch (Exception $e) {}
    }

    return $mediaUrl;
}

// Auto-reparar nombres corrompidos y sanear etiquetas de audio/imagen previas
try {
    $db->exec("
        UPDATE whatsapp_chats c
        LEFT JOIN clientes cl ON c.id_cliente = cl.id
        SET c.nombre = CASE 
            WHEN cl.nombre IS NOT NULL AND TRIM(cl.nombre) != '' AND LOWER(cl.nombre) NOT LIKE '%usuario whatsapp%' AND LOWER(cl.nombre) != 'no registra' THEN cl.nombre
            ELSE CONCAT('+', c.telefono)
        END
        WHERE c.nombre LIKE '%Usuario Whatsapp%' OR c.nombre LIKE '%Usuario WhatsApp%' OR c.nombre = 'Usuario Whatsapp'
    ");
    $db->exec("
        UPDATE whatsapp_chats c
        INNER JOIN clientes cl ON (
            c.telefono = cl.telefono 
            OR c.telefono = CONCAT('57', cl.telefono) 
            OR cl.telefono LIKE CONCAT('%', RIGHT(c.telefono, 8))
        )
        SET c.id_cliente = cl.id
        WHERE c.id_cliente IS NULL
    ");

    // Limpieza retroactiva de textos de medios WaFloW
    $db->exec("UPDATE whatsapp_mensajes SET tipo = 'audio', mensaje = '🎤 Mensaje de voz' WHERE mensaje LIKE '%audioMessage%' OR tipo = 'audioMessage'");
    $db->exec("UPDATE whatsapp_mensajes SET tipo = 'image', mensaje = '' WHERE (mensaje LIKE '%imageMessage%' OR tipo = 'imageMessage') AND (mensaje = '[Archivo: imageMessage]' OR mensaje = 'imageMessage')");
    $db->exec("UPDATE whatsapp_chats SET ultimo_mensaje = '🎤 Mensaje de voz' WHERE ultimo_mensaje LIKE '%audioMessage%'");
    $db->exec("UPDATE whatsapp_chats SET ultimo_mensaje = '📷 Foto' WHERE ultimo_mensaje LIKE '%imageMessage%'");
} catch (Exception $e) {}

// 2. Extraer datos con soporte para múltiples variantes de payloads de WaFloW
$evento = $data['event'] ?? $data['type'] ?? '';

// Determinar dirección: inbound (cliente nos escribe) vs outbound (escribimos nosotros desde el celular / bot)
$direccion = 'inbound';
$remitente = 'cliente';

if (
    (isset($data['direction']) && strtolower($data['direction']) === 'outbound') ||
    (isset($data['type']) && strtolower($data['type']) === 'outbound') ||
    (isset($data['event']) && stripos($data['event'], 'outbound') !== false) ||
    (isset($data['is_from_me']) && ($data['is_from_me'] == true || $data['is_from_me'] === 'true')) ||
    (isset($data['from_me']) && ($data['from_me'] == true || $data['from_me'] === 'true'))
) {
    $direccion = 'outbound';
    $remitente = 'agente_ia';
}

// Extraer teléfono del interlocutor (el contacto con quien se chatea)
if ($direccion === 'outbound') {
    // Si el mensaje salió desde nuestro celular, el cliente es el destinatario (to / recipient)
    $telefonoRaw = $data['to'] 
        ?? $data['recipient'] 
        ?? ($data['contact']['phone'] ?? '')
        ?? ($data['data']['to'] ?? '')
        ?? ($data['phone'] ?? '') 
        ?? ($data['from'] ?? '');
} else {
    // Si el mensaje entró desde el cliente, el cliente es el remitente (from / sender)
    $telefonoRaw = $data['from'] 
        ?? $data['sender'] 
        ?? ($data['contact']['phone'] ?? '')
        ?? ($data['data']['from'] ?? '')
        ?? ($data['phone'] ?? '') 
        ?? ($data['to'] ?? '');
}

$telefono = preg_replace('/\D/', '', $telefonoRaw);
$telNormalizado = normalizarTelefonoWebhook($telefonoRaw);
$telPrincipal = !empty($telNormalizado) ? $telNormalizado : $telefono;
$tel10 = substr($telPrincipal, -10);

// Extraer nombre del payload si viene
$nombrePayload = null;
if (!empty($data['contact']['name'])) {
    $nombrePayload = trim($data['contact']['name']);
} elseif (!empty($data['name'])) {
    $nombrePayload = trim($data['name']);
} elseif (!empty($data['data']['name'])) {
    $nombrePayload = trim($data['data']['name']);
}

// Extraer contenido del mensaje o caption
$caption = $data['message']['caption'] 
    ?? ($data['caption'] ?? '')
    ?? ($data['message']['imageMessage']['caption'] ?? '')
    ?? ($data['message']['videoMessage']['caption'] ?? '')
    ?? ($data['data']['message']['caption'] ?? '');

$mensajeTexto = $data['message']['body'] 
    ?? $data['message']['text'] 
    ?? $data['body'] 
    ?? $data['text'] 
    ?? $data['content'] 
    ?? ($data['data']['message'] ?? '');

if (is_array($mensajeTexto)) {
    $mensajeTexto = json_encode($mensajeTexto, JSON_UNESCAPED_UNICODE);
}

// ID único del mensaje
$msgId = $data['message_id'] 
    ?? $data['id'] 
    ?? ($data['message']['id'] ?? null)
    ?? ($data['data']['id'] ?? null)
    ?? uniqid();

// Extraer media URL de cualquier variante WaFloW
$mediaUrlRaw = extraerMediaUrlWebhook($data);

// Determinar tipo real de mensaje (audio, image, video, document, text)
$tipo = determinarTipoMensajeWebhook($data, $mediaUrlRaw, $mensajeTexto);

// Descargar y cachear archivo multimedia localmente
$mediaUrl = !empty($mediaUrlRaw) ? descargarMediaLocalWhatsApp($mediaUrlRaw, $tipo, $msgId) : null;

// Preparar el mensaje que se guardará y el snippet para la lista de chats
$captionLimpio = (!empty($caption) && stripos($caption, 'Message') === false) ? trim($caption) : '';

if ($tipo === 'audio') {
    $mensaje = 'Mensaje de voz';
    $snippetUltimoMensaje = '🎤 Mensaje de voz';
} elseif ($tipo === 'image') {
    $mensaje = !empty($captionLimpio) ? $captionLimpio : 'Foto';
    $snippetUltimoMensaje = !empty($captionLimpio) ? ('📷 ' . $captionLimpio) : '📷 Foto';
} elseif ($tipo === 'video') {
    $mensaje = !empty($captionLimpio) ? $captionLimpio : 'Video';
    $snippetUltimoMensaje = !empty($captionLimpio) ? ('🎥 ' . $captionLimpio) : '🎥 Video';
} elseif ($tipo === 'document') {
    $mensaje = !empty($captionLimpio) ? $captionLimpio : 'Documento';
    $snippetUltimoMensaje = !empty($captionLimpio) ? ('📄 ' . $captionLimpio) : '📄 Documento';
} else {
    // Si viene texto pero tiene '[Archivo: ...]', sanearlo
    if (stripos($mensajeTexto, 'audioMessage') !== false) {
        $tipo = 'audio';
        $mensaje = 'Mensaje de voz';
        $snippetUltimoMensaje = '🎤 Mensaje de voz';
    } elseif (stripos($mensajeTexto, 'imageMessage') !== false) {
        $tipo = 'image';
        $mensaje = 'Foto';
        $snippetUltimoMensaje = '📷 Foto';
    } elseif (stripos($mensajeTexto, 'videoMessage') !== false) {
        $tipo = 'video';
        $mensaje = 'Video';
        $snippetUltimoMensaje = '🎥 Video';
    } elseif (stripos($mensajeTexto, 'documentMessage') !== false) {
        $tipo = 'document';
        $mensaje = 'Documento';
        $snippetUltimoMensaje = '📄 Documento';
    } else {
        $mensaje = $mensajeTexto;
        $snippetUltimoMensaje = $mensaje;
    }
}

    // Fecha del mensaje
    $fechaHora = date('Y-m-d H:i:s');
    if (!empty($data['timestamp'])) {
        $ts = is_numeric($data['timestamp']) ? intval($data['timestamp']) : strtotime($data['timestamp']);
        if ($ts > 0) {
            // Si el timestamp viene en milisegundos
            if ($ts > 9999999999) {
                $ts = intval($ts / 1000);
            }
            $fechaHora = date('Y-m-d H:i:s', $ts);
        }
    }

    if (!empty($telefono)) {
        // 3. Buscar si el cliente ya está registrado en la tabla `clientes` de Kontrol POS
        $telLike = "%" . substr($telefono, -8);
        $stmtCli = $db->prepare("
            SELECT id, nombre, telefono 
            FROM clientes 
            WHERE telefono LIKE :tel 
               OR telefono LIKE :telRaw 
               OR telefono LIKE :telLike
            ORDER BY CASE 
                WHEN nombre IS NOT NULL AND TRIM(nombre) != '' AND LOWER(nombre) != 'no registra' THEN 1 
                ELSE 2 
            END ASC 
            LIMIT 1
        ");
        $stmtCli->bindParam(':tel', $telPrincipal, PDO::PARAM_STR);
        $stmtCli->bindParam(':telRaw', $telefonoRaw, PDO::PARAM_STR);
        $stmtCli->bindParam(':telLike', $telLike, PDO::PARAM_STR);
        $stmtCli->execute();
        $clienteEncontrado = $stmtCli->fetch(PDO::FETCH_ASSOC);

        $idCliente = $clienteEncontrado ? $clienteEncontrado['id'] : null;
        $nombreClientePOS = ($clienteEncontrado && !empty($clienteEncontrado['nombre']) && !esNombreGenericoWebhook($clienteEncontrado['nombre']))
            ? trim($clienteEncontrado['nombre'])
            : null;

        // Si no se encontró en clientes con nombre real, buscar en leads de CRM
        $nombreCRMLead = null;
        if (!$nombreClientePOS) {
            $stmtLead = $db->prepare("
                SELECT l.id_cliente, c.nombre as cliente_nombre 
                FROM crm_leads l
                LEFT JOIN clientes c ON l.id_cliente = c.id
                WHERE (l.origen LIKE :telLike OR c.telefono LIKE :telLike)
                  AND c.nombre IS NOT NULL AND TRIM(c.nombre) != ''
                LIMIT 1
            ");
            $stmtLead->bindParam(':telLike', $telLike, PDO::PARAM_STR);
            $stmtLead->execute();
            $lead = $stmtLead->fetch(PDO::FETCH_ASSOC);
            if ($lead && !empty($lead['cliente_nombre']) && !esNombreGenericoWebhook($lead['cliente_nombre'])) {
                $nombreCRMLead = trim($lead['cliente_nombre']);
                if (!$idCliente && !empty($lead['id_cliente'])) {
                    $idCliente = $lead['id_cliente'];
                }
            }
        }

        // 4. Buscar o crear el chat en `whatsapp_chats`
        $stmtChat = $db->prepare("
            SELECT id, no_leidos, nombre, id_cliente 
            FROM whatsapp_chats 
            WHERE telefono = :tel 
               OR telefono = :telNorm 
               OR telefono LIKE :telLike 
            LIMIT 1
        ");
        $stmtChat->bindParam(':tel', $telPrincipal, PDO::PARAM_STR);
        $stmtChat->bindParam(':telNorm', $telefono, PDO::PARAM_STR);
        $stmtChat->bindParam(':telLike', $telLike, PDO::PARAM_STR);
        $stmtChat->execute();
        $chat = $stmtChat->fetch(PDO::FETCH_ASSOC);

        $idChat = null;
        $noLeidos = 0;

        if ($chat) {
            $idChat = $chat['id'];
            $noLeidos = ($direccion === 'inbound') ? ($chat['no_leidos'] + 1) : 0;
            $idCliFinal = $idCliente ?? $chat['id_cliente'];

            $nombreExistente = trim($chat['nombre'] ?? '');
            $actualizarNombre = false;
            $nombreNuevo = $nombreExistente;

            if ($direccion === 'inbound') {
                // Mensaje ENTRANTE del cliente:
                // 1) Prioridad máxima: Si existe en clientes POS con nombre registrado real
                if ($nombreClientePOS && $nombreExistente !== $nombreClientePOS) {
                    $nombreNuevo = $nombreClientePOS;
                    $actualizarNombre = true;
                }
                // 2) Segunda prioridad: Nombre push de WhatsApp del cliente
                elseif (!empty($nombrePayload) && !esNombreGenericoWebhook($nombrePayload)) {
                    if (esNombreGenericoWebhook($nombreExistente) || $nombreExistente === ('+' . $telPrincipal)) {
                        $nombreNuevo = $nombrePayload;
                        $actualizarNombre = true;
                    }
                }
                // 3) Si el chat tiene un nombre genérico corrompido (ej: "Usuario Whatsapp"), sanearlo
                elseif (esNombreGenericoWebhook($nombreExistente)) {
                    $nombreNuevo = $nombreCRMLead ?: ('+' . $telPrincipal);
                    $actualizarNombre = true;
                }
            } else {
                // Mensaje SALIENTE (operador/móvil responde):
                // NUNCA sobreescribir el nombre del cliente con el nombre del remitente/operador ("Usuario Whatsapp")
                if ($nombreClientePOS && (esNombreGenericoWebhook($nombreExistente) || $nombreExistente !== $nombreClientePOS)) {
                    $nombreNuevo = $nombreClientePOS;
                    $actualizarNombre = true;
                } elseif (esNombreGenericoWebhook($nombreExistente)) {
                    $nombreNuevo = $nombreCRMLead ?: ('+' . $telPrincipal);
                    $actualizarNombre = true;
                }
            }

            if ($actualizarNombre) {
                $stmtUpdateChat = $db->prepare("
                    UPDATE whatsapp_chats 
                    SET ultimo_mensaje = :msg, 
                        fecha_ultimo_mensaje = :fec, 
                        no_leidos = :nl,
                        id_cliente = COALESCE(:idCli, id_cliente),
                        nombre = :nom
                    WHERE id = :id
                ");
                $stmtUpdateChat->bindParam(':nom', $nombreNuevo, PDO::PARAM_STR);
            } else {
                $stmtUpdateChat = $db->prepare("
                    UPDATE whatsapp_chats 
                    SET ultimo_mensaje = :msg, 
                        fecha_ultimo_mensaje = :fec, 
                        no_leidos = :nl,
                        id_cliente = COALESCE(:idCli, id_cliente)
                    WHERE id = :id
                ");
            }
            $stmtUpdateChat->bindParam(':msg', $snippetUltimoMensaje, PDO::PARAM_STR);
            $stmtUpdateChat->bindParam(':fec', $fechaHora, PDO::PARAM_STR);
            $stmtUpdateChat->bindParam(':nl', $noLeidos, PDO::PARAM_INT);
            $stmtUpdateChat->bindParam(':idCli', $idCliFinal, PDO::PARAM_INT);
            $stmtUpdateChat->bindParam(':id', $idChat, PDO::PARAM_INT);
            $stmtUpdateChat->execute();

        } else {
            // NUEVO CHAT
            $noLeidos = ($direccion === 'inbound') ? 1 : 0;

            if ($nombreClientePOS) {
                $nombreInsert = $nombreClientePOS;
            } elseif ($direccion === 'inbound' && !empty($nombrePayload) && !esNombreGenericoWebhook($nombrePayload)) {
                $nombreInsert = $nombrePayload;
            } elseif ($nombreCRMLead) {
                $nombreInsert = $nombreCRMLead;
            } else {
                $nombreInsert = '+' . $telPrincipal;
            }

            $stmtInsertChat = $db->prepare("
                INSERT INTO whatsapp_chats (telefono, nombre, ultimo_mensaje, fecha_ultimo_mensaje, no_leidos, id_cliente) 
                VALUES (:tel, :nom, :msg, :fec, :nl, :idCli)
            ");
            $stmtInsertChat->bindParam(':tel', $telPrincipal, PDO::PARAM_STR);
            $stmtInsertChat->bindParam(':nom', $nombreInsert, PDO::PARAM_STR);
            $stmtInsertChat->bindParam(':msg', $snippetUltimoMensaje, PDO::PARAM_STR);
            $stmtInsertChat->bindParam(':fec', $fechaHora, PDO::PARAM_STR);
            $stmtInsertChat->bindParam(':nl', $noLeidos, PDO::PARAM_INT);
            $stmtInsertChat->bindParam(':idCli', $idCliente, PDO::PARAM_INT);
            $stmtInsertChat->execute();
            $idChat = $db->lastInsertId();
        }

        // 5. Insertar el mensaje si tenemos texto, media o tipo multimedia reconocido
        if (!empty($mensaje) || !empty($mediaUrl) || in_array($tipo, ['image', 'audio', 'video', 'document'])) {
            // Verificar idempotencia (evitar duplicados por reintentos de webhook)
            $duplicado = false;
            if (!empty($msgId)) {
                $stmtCheck = $db->prepare("SELECT id FROM whatsapp_mensajes WHERE waflow_message_id = :mid LIMIT 1");
                $stmtCheck->bindParam(':mid', $msgId, PDO::PARAM_STR);
                $stmtCheck->execute();
                if ($stmtCheck->rowCount() > 0) {
                    $duplicado = true;
                }
            }

            if (!$duplicado && $idChat) {
                $stmtMsg = $db->prepare("
                    INSERT INTO whatsapp_mensajes (id_chat, waflow_message_id, direccion, remitente, mensaje, tipo, media_url, fecha_hora)
                    VALUES (:idChat, :mid, :dir, :rem, :msg, :tip, :murl, :fec)
                ");
                $stmtMsg->bindParam(':idChat', $idChat, PDO::PARAM_INT);
                $stmtMsg->bindParam(':mid', $msgId, PDO::PARAM_STR);
                $stmtMsg->bindParam(':dir', $direccion, PDO::PARAM_STR);
                $stmtMsg->bindParam(':rem', $remitente, PDO::PARAM_STR);
                $stmtMsg->bindParam(':msg', $mensaje, PDO::PARAM_STR);
                $stmtMsg->bindParam(':tip', $tipo, PDO::PARAM_STR);
                $stmtMsg->bindParam(':murl', $mediaUrl, PDO::PARAM_STR);
                $stmtMsg->bindParam(':fec', $fechaHora, PDO::PARAM_STR);
                $stmtMsg->execute();
            }
        }
    }

    // Responder siempre 200 OK con JSON
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Evento procesado correctamente",
        "chat_id" => $idChat ?? null
    ]);

} catch (Exception $e) {
    // Registrar error pero devolver 200 a WaFloW para que no bloquee el webhook
    file_put_contents(
        $logFile,
        "[ERROR " . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n\n",
        FILE_APPEND
    );

    http_response_code(200);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
