<?php

require_once __DIR__ . '/../api/whatsapp/config.php';
require_once __DIR__ . '/../modelos/whatsapp-chats.modelo.php';

class ControladorWhatsAppChats {

    /*=============================================
    MOSTRAR CHATS
    =============================================*/
    static public function ctrMostrarChats($busqueda = null) {
        return ModeloWhatsAppChats::mdlMostrarChats($busqueda);
    }

    /*=============================================
    OBTENER CHAT POR ID
    =============================================*/
    static public function ctrObtenerChat($idChat) {
        return ModeloWhatsAppChats::mdlObtenerChatPorId($idChat);
    }

    /*=============================================
    MOSTRAR MENSAJES DE UN CHAT
    =============================================*/
    static public function ctrMostrarMensajes($idChat) {
        return ModeloWhatsAppChats::mdlMostrarMensajes($idChat);
    }

    /*=============================================
    MARCAR COMO LEÍDOS
    =============================================*/
    static public function ctrMarcarLeidos($idChat) {
        return ModeloWhatsAppChats::mdlMarcarLeidos($idChat);
    }

    /*=============================================
    CREAR O BUSCAR CHAT
    =============================================*/
    static public function ctrCrearOBuscarChat($telefono, $nombre = null) {
        return ModeloWhatsAppChats::mdlCrearOBuscarChat($telefono, $nombre);
    }

    /*=============================================
    SINCRONIZAR LEADS DE CRM
    =============================================*/
    static public function ctrSincronizarCRM() {
        return ModeloWhatsAppChats::mdlSincronizarLeadsCRMExistentes(true);
    }

    /*=============================================
    ENVIAR MENSAJE VÍA WAFLOW (TEXTO O CON ARCHIVO)
    =============================================*/
    static public function ctrEnviarMensaje($idChat, $telefono, $mensaje, $archivo = null) {
        $mensaje = trim($mensaje ?? '');
        if (empty($idChat) || empty($telefono) || (empty($mensaje) && empty($archivo))) {
            return [
                "success" => false,
                "error" => "Debes ingresar un mensaje o adjuntar un archivo para enviar."
            ];
        }

        // 1. Procesar archivo adjunto si existe
        $tipoMensaje = 'text';
        $mediaUrlRelativa = null;
        $mediaUrlPublica = null;
        $nombreOriginal = null;

        if (!empty($archivo) && isset($archivo['tmp_name']) && is_uploaded_file($archivo['tmp_name'])) {
            $nombreOriginal = $archivo['name'] ?? 'archivo';
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $tamanio = $archivo['size'] ?? 0;

            if ($tamanio > 16 * 1024 * 1024) {
                return [
                    "success" => false,
                    "error" => "El archivo supera el tamaño máximo permitido de 16 MB."
                ];
            }

            $extPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
            if (!in_array($ext, $extPermitidas)) {
                return [
                    "success" => false,
                    "error" => "Formato de archivo no permitido (.{$ext}). Solo imágenes y documentos (PDF, Word, Excel, TXT)."
                ];
            }

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $tipoMensaje = 'image';
            } else {
                $tipoMensaje = 'document';
            }

            $dirDestino = dirname(__DIR__) . '/vistas/img/whatsapp_media';
            if (!file_exists($dirDestino)) {
                @mkdir($dirDestino, 0777, true);
            }

            $nombreGuardado = 'wa_out_' . date('Ymd_His') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8) . '.' . $ext;
            $rutaFisica = $dirDestino . '/' . $nombreGuardado;

            if (move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
                $mediaUrlRelativa = 'vistas/img/whatsapp_media/' . $nombreGuardado;

                // Construir URL pública completa para WaFloW
                $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                         (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $baseDir = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
                $mediaUrlPublica = $proto . '://' . $host . ($baseDir !== '' ? $baseDir : '') . '/' . $mediaUrlRelativa;
            } else {
                return [
                    "success" => false,
                    "error" => "No se pudo guardar el archivo adjunto en el servidor."
                ];
            }
        }

        // 2. Normalizar teléfono destino (ej: 573218142039 -> +573218142039)
        $telDestino = ModeloWhatsAppChats::normalizarTelefono($telefono);
        if (empty($telDestino)) {
            $telDestino = preg_replace('/\D/', '', $telefono);
        }
        $telConPlus = (substr($telDestino, 0, 1) === '+') ? $telDestino : ('+' . $telDestino);
        $telSinPlus = ltrim($telDestino, '+');

        $remitente = 'operador';
        $waflowMsgId = null;
        $errorEnvio = null;
        $httpExito = false;
        $ultimoHttpCode = 0;

        // 3. Parámetros de WaFloW (leídos dinámicamente de BD con fallback a constantes)
        $configWa = ModeloWhatsAppChats::mdlObtenerConfiguracion();
        $apiKey = !empty($configWa['api_key']) ? trim($configWa['api_key']) : (defined('WAFLOW_API_KEY') ? trim(WAFLOW_API_KEY) : '');
        $locationId = !empty($configWa['location_id']) ? trim($configWa['location_id']) : (defined('WAFLOW_LOCATION_ID') ? trim(WAFLOW_LOCATION_ID) : 'loc_e1033bf5-06be-40b1-9a36-8b60e9d3165b');
        $slotId = isset($configWa['slot_id']) ? intval($configWa['slot_id']) : (defined('WAFLOW_SLOT_ID') ? intval(WAFLOW_SLOT_ID) : 1);
        $apiUrl = !empty($configWa['api_url']) ? trim($configWa['api_url']) : (defined('WAFLOW_API_URL') ? trim(WAFLOW_API_URL) : 'https://wa.waflow.ai/agency/n8n/send-message');

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        $logEnvio = __DIR__ . '/../api/whatsapp/log_envios.txt';

        if (!empty($apiKey)) {
            // Intentar envío con formato internacional +57... y fallback sin +
            $telefonosAProbar = [$telConPlus, $telSinPlus];

            // Formatear texto para WhatsApp: WaFloW /agency/n8n/send-message despacha texto.
            // Si hay un archivo adjunto, incluimos el enlace directo de descarga pública del archivo.
            if (!empty($mediaUrlPublica)) {
                $icono = ($tipoMensaje === 'image') ? '📷' : '📄';
                $nombreMostrar = !empty($nombreOriginal) ? $nombreOriginal : ($tipoMensaje === 'image' ? 'Foto' : 'Documento');

                $partesMensaje = [];
                if (!empty($mensaje)) {
                    $partesMensaje[] = $mensaje;
                }
                $partesMensaje[] = "{$icono} {$nombreMostrar}:";
                $partesMensaje[] = $mediaUrlPublica;

                $textoParaWhatsApp = implode("\n", $partesMensaje);
            } else {
                $textoParaWhatsApp = $mensaje;
            }

            foreach ($telefonosAProbar as $dest) {
                $payload = [
                    "locationId" => $locationId,
                    "slotId" => $slotId,
                    "to" => $dest,
                    "message" => $textoParaWhatsApp
                ];

                if (!empty($mediaUrlPublica)) {
                    $payload["mediaUrl"] = $mediaUrlPublica;
                    $payload["fileUrl"] = $mediaUrlPublica;
                    $payload["type"] = $tipoMensaje;
                    if (!empty($nombreOriginal)) {
                        $payload["fileName"] = $nombreOriginal;
                    }
                }

                try {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $apiUrl,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 20,
                        CURLOPT_HTTPHEADER => $headers
                    ]);

                    $res = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlErr = curl_error($ch);
                    curl_close($ch);

                    $ultimoHttpCode = $httpCode;

                    // Log para auditoría
                    $fechaLog = date('Y-m-d H:i:s');
                    file_put_contents(
                        $logEnvio,
                        "[{$fechaLog}] Enviar a {$dest} (slot: {$slotId}) | tipo: {$tipoMensaje} | HTTP {$httpCode} | Res: " . substr($res, 0, 250) . " | Error: {$curlErr}\n",
                        FILE_APPEND
                    );

                    if ($httpCode >= 200 && $httpCode < 300) {
                        $httpExito = true;
                        if ($res) {
                            $jsonRes = json_decode($res, true);
                            if ($jsonRes && isset($jsonRes['id'])) {
                                $waflowMsgId = $jsonRes['id'];
                            } elseif ($jsonRes && isset($jsonRes['message_id'])) {
                                $waflowMsgId = $jsonRes['message_id'];
                            }
                        }
                        break;
                    } else {
                        $errorEnvio = "WaFloW respondió HTTP {$httpCode}: " . substr(strip_tags($res), 0, 200);
                    }
                } catch (Exception $e) {
                    $errorEnvio = $e->getMessage();
                }
            }
        }

        // 4. Registrar mensaje en la base de datos local de Kontrol POS
        $textoFinal = !empty($mensaje) ? $mensaje : ($tipoMensaje === 'image' ? '📷 Foto' : ('📄 ' . ($nombreOriginal ?? 'Documento')));

        $registro = ModeloWhatsAppChats::mdlRegistrarMensajeSaliente(
            $idChat,
            $textoFinal,
            $waflowMsgId,
            $remitente,
            $tipoMensaje,
            $mediaUrlRelativa
        );

        if ($httpExito) {
            return [
                "success" => true,
                "mensaje" => $registro,
                "waflow_id" => $waflowMsgId,
                "http_code" => $ultimoHttpCode,
                "enviado_real" => true
            ];
        } else {
            return [
                "success" => false,
                "mensaje" => $registro,
                "waflow_id" => null,
                "http_code" => $ultimoHttpCode,
                "enviado_real" => false,
                "error" => !empty($errorEnvio) ? $errorEnvio : "WaFloW no pudo despachar el mensaje (código HTTP {$ultimoHttpCode}). Verifica tu Slot ID y API Key."
            ];
        }
    }

    /*=============================================
    CONTEO NO LEÍDOS GLOBAL
    =============================================*/
    static public function ctrConteoNoLeidosTotal() {
        return ModeloWhatsAppChats::mdlConteoNoLeidosTotal();
    }

    /*=============================================
    OBTENER CONFIGURACIÓN DE CONEXIÓN WAFLOW
    =============================================*/
    static public function ctrObtenerConfiguracion() {
        return ModeloWhatsAppChats::mdlObtenerConfiguracion();
    }

    /*=============================================
    GUARDAR CONFIGURACIÓN DE CONEXIÓN WAFLOW
    (EXCLUSIVO PERFIL _SystemMaster_)
    =============================================*/
    static public function ctrGuardarConfiguracion($datos) {
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "_SystemMaster_") {
            return [
                "success" => false,
                "error" => "No tienes permisos de _SystemMaster_ para modificar esta configuración."
            ];
        }

        $apiKey = trim($datos["api_key"] ?? '');
        $locationId = trim($datos["location_id"] ?? '');
        $slotId = intval($datos["slot_id"] ?? 1);
        $agencyId = trim($datos["agency_id"] ?? 'AG-1793500451718');
        $apiUrl = trim($datos["api_url"] ?? 'https://wa.waflow.ai/agency/n8n/send-message');
        $telefonoConectado = trim($datos["telefono_conectado"] ?? '');

        if (empty($apiKey) || empty($locationId)) {
            return [
                "success" => false,
                "error" => "Los campos API Key y Location ID son obligatorios."
            ];
        }

        $datosLimpios = [
            "api_key" => $apiKey,
            "agency_id" => $agencyId,
            "location_id" => $locationId,
            "slot_id" => $slotId > 0 ? $slotId : 1,
            "api_url" => !empty($apiUrl) ? $apiUrl : 'https://wa.waflow.ai/agency/n8n/send-message',
            "telefono_conectado" => $telefonoConectado
        ];

        $guardado = ModeloWhatsAppChats::mdlGuardarConfiguracion($datosLimpios);

        if (is_array($guardado)) {
            return $guardado;
        }

        return [
            "success" => (bool)$guardado,
            "error" => $guardado ? null : "Error al guardar en la base de datos."
        ];
    }

    /*=============================================
    PROBAR CONEXIÓN CON WAFLOW
    =============================================*/
    static public function ctrProbarConexion($datos) {
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "_SystemMaster_") {
            return [
                "success" => false,
                "error" => "Acceso no autorizado."
            ];
        }

        $apiKey = trim($datos["api_key"] ?? '');
        $locationId = trim($datos["location_id"] ?? '');
        $slotId = intval($datos["slot_id"] ?? 1);
        $apiUrl = trim($datos["api_url"] ?? 'https://wa.waflow.ai/agency/n8n/send-message');

        if (empty($apiKey) || empty($locationId)) {
            return [
                "success" => false,
                "error" => "Por favor ingresa la API Key y el Location ID para probar la conexión."
            ];
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        // Se envía un ping de verificación al endpoint de WaFloW
        $payload = [
            "locationId" => $locationId,
            "slotId" => $slotId,
            "ping" => true
        ];

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_HTTPHEADER => $headers
            ]);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            // Intentar consultar catálogo de slots para orientar al usuario sobre qué slotId usar
            $infoSlots = "";
            try {
                $uCat = 'https://wa.waflow.ai/agency/n8n/slot-catalog?locationId=' . urlencode($locationId);
                $chCat = curl_init();
                curl_setopt_array($chCat, [
                    CURLOPT_URL => $uCat,
                    CURLOPT_HTTPGET => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 6,
                    CURLOPT_HTTPHEADER => $headers
                ]);
                $resCat = curl_exec($chCat);
                $codeCat = curl_getinfo($chCat, CURLINFO_HTTP_CODE);
                curl_close($chCat);

                if ($codeCat === 200 && !empty($resCat)) {
                    $jsonCat = json_decode($resCat, true);
                    $lista = isset($jsonCat['slots']) ? $jsonCat['slots'] : (isset($jsonCat['data']) ? $jsonCat['data'] : (is_array($jsonCat) ? $jsonCat : []));
                    if (is_array($lista) && !empty($lista)) {
                        $lineasTxt = [];
                        foreach ($lista as $item) {
                            if (is_array($item)) {
                                $sId = $item['id'] ?? ($item['slotId'] ?? '?');
                                $sPhone = $item['phone'] ?? ($item['phone_number'] ?? ($item['name'] ?? ''));
                                $sStatus = $item['status'] ?? ($item['connected'] ? 'online' : '');
                                $lineasTxt[] = "Slot {$sId}" . (!empty($sPhone) ? " ({$sPhone})" : "") . (!empty($sStatus) ? " [{$sStatus}]" : "");
                            }
                        }
                        if (!empty($lineasTxt)) {
                            $infoSlots = "<br><br><strong>Líneas detectadas en WaFloW:</strong><br>• " . implode("<br>• ", $lineasTxt);
                        }
                    }
                }
            } catch (Exception $eCat) {}

            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    "success" => true,
                    "http_code" => $httpCode,
                    "mensaje" => "¡Conexión exitosa con WaFloW! La API Key y el endpoint responden correctamente (HTTP {$httpCode})." . $infoSlots
                ];
            } elseif ($httpCode === 401 || $httpCode === 403) {
                return [
                    "success" => false,
                    "http_code" => $httpCode,
                    "mensaje" => "Error de autenticación (HTTP {$httpCode}): La API Key ingresada no es válida o fue revocada en WaFloW."
                ];
            } elseif ($httpCode === 400) {
                // WaFloW responde 400 cuando la autenticación pasó pero faltan campos de mensaje ("to", "message")
                return [
                    "success" => true,
                    "http_code" => $httpCode,
                    "mensaje" => "¡Conexión verificada! El servidor de WaFloW validó las credenciales con éxito." . $infoSlots
                ];
            } elseif ($httpCode === 404) {
                return [
                    "success" => false,
                    "http_code" => $httpCode,
                    "mensaje" => "Error 404: La URL del endpoint de WaFloW no existe o cambió."
                ];
            } else {
                return [
                    "success" => false,
                    "http_code" => $httpCode,
                    "mensaje" => !empty($curlErr) ? "Error cURL: {$curlErr}" : "WaFloW respondió HTTP {$httpCode}: " . substr(strip_tags($res), 0, 150)
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "mensaje" => "Excepción al intentar conectar: " . $e->getMessage()
            ];
        }
    }
}
