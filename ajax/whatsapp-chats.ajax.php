<?php

ob_start();

// Iniciar sesión con SessionManager (el sistema usa sesiones personalizadas con cookie POS_SESSION)
if (file_exists("../modelos/session-manager.php")) {
    require_once "../modelos/session-manager.php";
    SessionManager::startSecure();
} else {
    session_name('POS_SESSION');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

require_once "../controladores/whatsapp-chats.controlador.php";
require_once "../modelos/whatsapp-chats.modelo.php";

class AjaxWhatsAppChats {

    public $idChat;
    public $telefono;
    public $nombre;
    public $mensaje;
    public $busqueda;
    public $archivo;

    /*=============================================
    OBTENER LISTA DE CHATS
    =============================================*/
    public function ajaxObtenerChats() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $chats = ControladorWhatsAppChats::ctrMostrarChats($this->busqueda);
            echo json_encode($chats ? array_values($chats) : []);
        } catch (Throwable $e) {
            echo json_encode([]);
        }
        exit();
    }

    /*=============================================
    OBTENER MENSAJES DE UN CHAT
    =============================================*/
    public function ajaxObtenerMensajes() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!empty($this->idChat)) {
                ControladorWhatsAppChats::ctrMarcarLeidos($this->idChat);
                $mensajes = ControladorWhatsAppChats::ctrMostrarMensajes($this->idChat);
                echo json_encode($mensajes ? array_values($mensajes) : []);
            } else {
                echo json_encode([]);
            }
        } catch (Throwable $e) {
            echo json_encode([]);
        }
        exit();
    }

    /*=============================================
    OBTENER DETALLES DE UN CHAT
    =============================================*/
    public function ajaxObtenerChatInfo() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!empty($this->idChat)) {
                $info = ControladorWhatsAppChats::ctrObtenerChat($this->idChat);
                echo json_encode($info ?: null);
            } else {
                echo json_encode(null);
            }
        } catch (Throwable $e) {
            echo json_encode(null);
        }
        exit();
    }

    /*=============================================
    ENVIAR MENSAJE
    =============================================*/
    public function ajaxEnviarMensaje() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $archivo = $_FILES["archivo"] ?? $this->archivo ?? null;
            $respuesta = ControladorWhatsAppChats::ctrEnviarMensaje(
                $this->idChat,
                $this->telefono,
                $this->mensaje,
                $archivo
            );
            echo json_encode($respuesta);
        } catch (Throwable $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        exit();
    }

    /*=============================================
    MARCAR COMO LEÍDOS
    =============================================*/
    public function ajaxMarcarLeidos() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $res = ControladorWhatsAppChats::ctrMarcarLeidos($this->idChat);
            echo json_encode(["success" => $res]);
        } catch (Throwable $e) {
            echo json_encode(["success" => false]);
        }
        exit();
    }

    /*=============================================
    CREAR O BUSCAR CHAT
    =============================================*/
    public function ajaxCrearChat() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $chat = ControladorWhatsAppChats::ctrCrearOBuscarChat($this->telefono, $this->nombre);
            echo json_encode($chat);
        } catch (Throwable $e) {
            echo json_encode(null);
        }
        exit();
    }

    /*=============================================
    SINCRONIZAR CONVERSACIONES DESDE EL CRM
    =============================================*/
    public function ajaxSincronizarCRM() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $res = ControladorWhatsAppChats::ctrSincronizarCRM();
            echo json_encode($res);
        } catch (Throwable $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        exit();
    }

    /*=============================================
    OBTENER CONFIGURACIÓN (SOLO _SystemMaster_)
    =============================================*/
    public function ajaxObtenerConfiguracion() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "_SystemMaster_") {
            echo json_encode(["success" => false, "error" => "Acceso restringido a _SystemMaster_."]);
            exit();
        }

        try {
            $config = ControladorWhatsAppChats::ctrObtenerConfiguracion();

            // Calcular URL pública del webhook
            $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                         (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseDir = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
            $webhookUrl = $protocolo . '://' . $host . ($baseDir !== '' ? $baseDir : '') . '/api/whatsapp/webhook.php';

            echo json_encode([
                "success" => true,
                "config" => $config,
                "webhook_url" => $webhookUrl
            ]);
        } catch (Throwable $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        exit();
    }

    /*=============================================
    GUARDAR CONFIGURACIÓN (SOLO _SystemMaster_)
    =============================================*/
    public function ajaxGuardarConfiguracion() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "_SystemMaster_") {
            echo json_encode(["success" => false, "error" => "Acceso restringido a _SystemMaster_."]);
            exit();
        }

        try {
            $res = ControladorWhatsAppChats::ctrGuardarConfiguracion($_POST);
            echo json_encode($res);
        } catch (Throwable $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        exit();
    }

    /*=============================================
    PROBAR CONEXIÓN (SOLO _SystemMaster_)
    =============================================*/
    public function ajaxProbarConexion() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] !== "_SystemMaster_") {
            echo json_encode(["success" => false, "error" => "Acceso restringido a _SystemMaster_."]);
            exit();
        }

        try {
            $res = ControladorWhatsAppChats::ctrProbarConexion($_POST);
            echo json_encode($res);
        } catch (Throwable $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        exit();
    }
}

// Enrutador de acciones AJAX
if (isset($_POST["accion"])) {
    $ajax = new AjaxWhatsAppChats();

    switch ($_POST["accion"]) {
        case "obtenerChats":
            $ajax->busqueda = $_POST["busqueda"] ?? null;
            $ajax->ajaxObtenerChats();
            break;

        case "obtenerMensajes":
            $ajax->idChat = $_POST["idChat"] ?? 0;
            $ajax->ajaxObtenerMensajes();
            break;

        case "obtenerChatInfo":
            $ajax->idChat = $_POST["idChat"] ?? 0;
            $ajax->ajaxObtenerChatInfo();
            break;

        case "enviarMensaje":
            $ajax->idChat = $_POST["idChat"] ?? 0;
            $ajax->telefono = $_POST["telefono"] ?? '';
            $ajax->mensaje = $_POST["mensaje"] ?? '';
            $ajax->ajaxEnviarMensaje();
            break;

        case "marcarLeidos":
            $ajax->idChat = $_POST["idChat"] ?? 0;
            $ajax->ajaxMarcarLeidos();
            break;

        case "crearChat":
            $ajax->telefono = $_POST["telefono"] ?? '';
            $ajax->nombre = $_POST["nombre"] ?? null;
            $ajax->ajaxCrearChat();
            break;

        case "sincronizarCRM":
            $ajax->ajaxSincronizarCRM();
            break;

        case "obtenerConfiguracion":
            $ajax->ajaxObtenerConfiguracion();
            break;

        case "guardarConfiguracion":
            $ajax->ajaxGuardarConfiguracion();
            break;

        case "probarConexion":
            $ajax->ajaxProbarConexion();
            break;
    }
}
