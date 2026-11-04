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
    ENVIAR MENSAJE VÍA WAFLOW
    =============================================*/
    static public function ctrEnviarMensaje($idChat, $telefono, $mensaje) {
        if (empty($mensaje) || empty($telefono) || empty($idChat)) {
            return [
                "success" => false,
                "error" => "Parámetros incompletos (idChat, telefono o mensaje)."
            ];
        }

        // 1. Normalizar teléfono destino (ej: 573218142039 -> +573218142039)
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

        // 2. Parámetros oficiales de WaFloW
        $apiKey = defined('WAFLOW_API_KEY') ? trim(WAFLOW_API_KEY) : '';
        $locationId = defined('WAFLOW_LOCATION_ID') ? trim(WAFLOW_LOCATION_ID) : 'loc_e1033bf5-06be-40b1-9a36-8b60e9d3165b';
        $slotId = defined('WAFLOW_SLOT_ID') ? intval(WAFLOW_SLOT_ID) : 1;
        $apiUrl = defined('WAFLOW_API_URL') ? trim(WAFLOW_API_URL) : 'https://wa.waflow.ai/agency/n8n/send-message';

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        $logEnvio = __DIR__ . '/../api/whatsapp/log_envios.txt';

        if (!empty($apiKey)) {
            // Intentar envío con formato internacional +57... y fallback sin +
            $telefonosAProbar = [$telConPlus, $telSinPlus];

            foreach ($telefonosAProbar as $dest) {
                $payload = [
                    "locationId" => $locationId,
                    "slotId" => $slotId,
                    "to" => $dest,
                    "message" => $mensaje
                ];

                try {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $apiUrl,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => json_encode($payload),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 10,
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
                        "[{$fechaLog}] Enviar a {$dest} (slot: {$slotId}) | HTTP {$httpCode} | Res: " . substr($res, 0, 250) . " | Error: {$curlErr}\n",
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
                        $errorEnvio = "WaFloW respondió HTTP {$httpCode}: " . substr(strip_tags($res), 0, 150);
                    }
                } catch (Exception $e) {
                    $errorEnvio = $e->getMessage();
                }
            }
        }

        // 3. Registrar mensaje en la base de datos local de Kontrol POS
        $registro = ModeloWhatsAppChats::mdlRegistrarMensajeSaliente(
            $idChat,
            $mensaje,
            $waflowMsgId,
            $remitente
        );

        return [
            "success" => true,
            "mensaje" => $registro,
            "waflow_id" => $waflowMsgId,
            "http_code" => $ultimoHttpCode,
            "enviado_real" => $httpExito,
            "advertencia_api" => $errorEnvio
        ];
    }

    /*=============================================
    CONTEO NO LEÍDOS GLOBAL
    =============================================*/
    static public function ctrConteoNoLeidosTotal() {
        return ModeloWhatsAppChats::mdlConteoNoLeidosTotal();
    }
}
