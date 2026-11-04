<?php

ob_start();
session_start();

require_once "../controladores/whatsapp-chats.controlador.php";
require_once "../modelos/whatsapp-chats.modelo.php";

class AjaxWhatsAppChats {

    public $idChat;
    public $telefono;
    public $nombre;
    public $mensaje;
    public $busqueda;

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
            $respuesta = ControladorWhatsAppChats::ctrEnviarMensaje(
                $this->idChat,
                $this->telefono,
                $this->mensaje
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
    }
}
