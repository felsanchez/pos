<?php
if ($_SESSION["perfil"] == "Visitante") {
    echo '<script>window.location = "inicio";</script>';
    return;
}

require_once "controladores/whatsapp-chats.controlador.php";
require_once "modelos/whatsapp-chats.modelo.php";
?>

<div class="content-wrapper" style="background-color: #f0f2f5;">

  <section class="content-header" style="padding: 12px 15px 0 15px;">
    <h1 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 8px;">
      <i class="fa fa-whatsapp text-success" style="font-size: 24px; color: #25D366;"></i> 
      Bandeja de Chats WhatsApp
      <span class="label" style="background-color: #25D366; color: white; font-size: 11px; padding: 3px 8px; border-radius: 12px; margin-left: 6px;">
        En Vivo (WaFloW.ai)
      </span>
    </h1>
    <ol class="breadcrumb" style="top: 10px;">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="crm">CRM</a></li>
      <li class="active">Chats WhatsApp</li>
    </ol>
  </section>

  <section class="content" style="padding: 12px 15px;">

    <!-- CONTENEDOR PRINCIPAL ESTILO WHATSAPP WEB -->
    <div class="whatsapp-app-container">

      <!-- ==============================================
           PANEL IZQUIERDO: LISTA DE CHATS
      =============================================== -->
      <div class="whatsapp-sidebar">
        
        <!-- Header de la barra lateral -->
        <div class="whatsapp-sidebar-header">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div class="whatsapp-user-avatar">
              <i class="fa fa-user"></i>
            </div>
            <div>
              <div style="font-weight: 700; font-size: 14px; color: #111b21;">Conversaciones</div>
              <small style="color: #667781; font-size: 11px;" id="contadorChatsTotal">Cargando chats...</small>
            </div>
          </div>
          <div style="display: flex; gap: 6px; align-items: center;">
            <?php if (isset($_SESSION["perfil"]) && $_SESSION["perfil"] === "_SystemMaster_"): ?>
            <button class="btn btn-sm btn-default" id="btnConfigWhatsApp" data-toggle="modal" data-target="#modalConfigWhatsApp" style="border-radius: 20px; font-weight: 600; font-size: 11px; padding: 5px 9px;" title="Configuración de Conexión WaFloW (Exclusivo _SystemMaster_)">
              <i class="fa fa-cog text-primary"></i> <span class="hidden-xs">Conexión</span>
            </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-default" id="btnSincronizarCRM" style="border-radius: 20px; font-weight: 600; font-size: 11px; padding: 5px 9px;" title="Sincronizar todos los contactos y leads del CRM">
              <i class="fa fa-refresh" id="iconoSyncCRM"></i> CRM
            </button>
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalNuevoChatWhatsApp" style="background-color: #25D366; border-color: #25D366; border-radius: 20px; font-weight: 600; font-size: 12px; padding: 5px 12px;" title="Iniciar nueva conversación">
              <i class="fa fa-plus"></i> Nuevo
            </button>
          </div>
        </div>

        <!-- Barra de búsqueda de contactos -->
        <div class="whatsapp-search-box">
          <div class="input-group" style="width: 100%;">
            <span class="input-group-addon" style="background: #f0f2f5; border: none; border-radius: 8px 0 0 8px; color: #54656f;">
              <i class="fa fa-search"></i>
            </span>
            <input type="text" id="buscarChatInput" class="form-control" placeholder="Buscar o iniciar un nuevo chat..." style="background: #f0f2f5; border: none; border-radius: 0 8px 8px 0; box-shadow: none; font-size: 13px;">
          </div>
        </div>

        <!-- Lista scrollable de conversaciones -->
        <div class="whatsapp-chat-list" id="listaChatsWhatsApp">
          <!-- Cargado dinámicamente vía AJAX -->
          <div class="text-center" style="padding: 40px 20px; color: #8696a0;">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p style="margin-top: 10px; font-size: 13px;">Cargando conversaciones...</p>
          </div>
        </div>

      </div>

      <!-- ==============================================
           PANEL DERECHO: CONVERSACIÓN ACTIVA
      =============================================== -->
      <div class="whatsapp-chat-panel">

        <!-- Estado Vacío (Sin chat seleccionado) -->
        <div id="panelChatVacio" class="whatsapp-empty-state">
          <div style="width: 80px; height: 80px; border-radius: 50%; background: #e8f5e9; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
            <i class="fa fa-whatsapp" style="font-size: 45px; color: #25D366;"></i>
          </div>
          <h3 style="color: #41525d; font-weight: 600; font-size: 20px; margin: 0 0 8px 0;">WhatsApp para Kontrol POS</h3>
          <p style="color: #667781; font-size: 14px; max-width: 420px; margin: 0 auto 15px auto; line-height: 1.5;">
            Envía y recibe mensajes en tiempo real a través de <b>WaFloW.ai</b>. Selecciona una conversación de la izquierda para comenzar a responder.
          </p>
          <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#modalNuevoChatWhatsApp" style="border-radius: 20px; font-weight: 600; color: #111b21;">
            <i class="fa fa-commenting-o text-success"></i> Iniciar nueva conversación
          </button>
        </div>

        <!-- Área de Conversación Activa -->
        <div id="panelChatActivo" style="display: none; height: 100%; flex-direction: column;">
          
          <!-- Encabezado del Chat Activo -->
          <div class="whatsapp-chat-header">
            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden; flex: 1;">
              
              <!-- Botón Volver a lista (Visible solo en móviles) -->
              <button type="button" class="btn btn-default btn-volver-chats-mobile" id="btnVolverChatsMobile" title="Volver a conversaciones">
                <i class="fa fa-arrow-left"></i>
              </button>

              <div class="whatsapp-chat-avatar" id="headerChatAvatar">
                <!-- Iniciales -->
              </div>
              <div style="overflow: hidden;">
                <div style="font-weight: 700; font-size: 15px; color: #111b21; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" id="headerChatNombre">
                  Nombre Cliente
                </div>
                <div style="font-size: 12px; color: #667781;" id="headerChatTelefono">
                  +57 000 000 0000
                </div>
              </div>
            </div>

            <div style="display: flex; align-items: center; gap: 6px;">
              <span id="badgeClienteRegistrado" class="badge" style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 11px; font-weight: 600; padding: 4px 8px; display: none;">
                <i class="fa fa-user"></i> <span class="btn-pipeline-text">Cliente POS</span>
              </span>
              <a href="crm" class="btn btn-default btn-xs" style="border-radius: 4px; font-weight: 600;" title="Ir al Tablero CRM">
                <i class="fa fa-filter text-primary"></i> <span class="btn-pipeline-text">Pipeline CRM</span>
              </a>
              <button class="btn btn-default btn-xs" id="btnRefrescarMensajes" title="Actualizar conversación">
                <i class="fa fa-refresh"></i>
              </button>
            </div>
          </div>

          <!-- Mensajes: Área de Burbujas Scrollable -->
          <div class="whatsapp-messages-container" id="contenedorMensajesChat">
            <!-- Burbujas cargadas vía AJAX -->
          </div>

          <!-- Previsualización de Archivo Adjunto -->
          <div id="previewArchivoAdjuntoWA" style="display: none; background: #ffffff; border-top: 1px solid #e2e8f0; padding: 10px 18px; border-left: 3px solid #25D366; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 -2px 10px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; gap: 12px; overflow: hidden;">
              <div id="previewThumbWA" style="width: 44px; height: 44px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; border: 1px solid rgba(0,0,0,0.08);">
                <i class="fa fa-file-text-o text-muted" style="font-size: 20px;"></i>
              </div>
              <div style="overflow: hidden;">
                <div id="previewNombreWA" style="font-weight: 600; font-size: 13px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px;">archivo.pdf</div>
                <small id="previewTamanoWA" style="color: #64748b; font-size: 11px;">0 KB</small>
              </div>
            </div>
            <button type="button" class="btn btn-default btn-xs" id="btnQuitarAdjuntoWA" title="Quitar archivo adjunto" style="border-radius: 50%; width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; color: #64748b;">
              <i class="fa fa-times"></i>
            </button>
          </div>

          <!-- Pie: Barra de Envío de Mensaje -->
          <div class="whatsapp-chat-input-bar">
            <input type="hidden" id="activoIdChat" value="">
            <input type="hidden" id="activoTelefonoChat" value="">
            <input type="file" id="inputAdjuntarArchivoWA" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt" style="display: none;">

            <button type="button" class="btn btn-default" id="btnAdjuntarArchivoWA" title="Adjuntar archivo o imagen" style="border: none; background: transparent; color: #54656f; font-size: 18px; padding: 6px 10px; border-radius: 50%; outline: none;">
              <i class="fa fa-paperclip"></i>
            </button>

            <div style="flex: 1; position: relative;">
              <textarea id="inputMensajeWhatsApp" class="form-control" rows="1" placeholder="Escribe un mensaje aquí... (Presiona Enter para enviar)" style="resize: none; border-radius: 20px; padding: 10px 16px; border: 1px solid #e2e8f0; font-size: 13px; line-height: 1.4; max-height: 100px;"></textarea>
            </div>

            <button class="whatsapp-btn-send" id="btnEnviarMensajeWhatsApp" title="Enviar mensaje">
              <i class="fa fa-paper-plane"></i>
            </button>
          </div>

        </div>

      </div>

    </div>

  </section>

</div>

<!-- ==============================================
     MODAL: NUEVA CONVERSACIÓN
=============================================== -->
<div class="modal fade" id="modalNuevoChatWhatsApp" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border-radius: 10px; overflow: hidden;">
      
      <div class="modal-header" style="background: #25D366; color: white; padding: 12px 16px;">
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.9;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i class="fa fa-whatsapp"></i> Iniciar Nueva Conversación
        </h4>
      </div>

      <div class="modal-body" style="padding: 16px;">
        <div class="form-group">
          <label style="font-weight: 600; font-size: 13px; color: #334155;">Número de Teléfono *</label>
          <div class="input-group">
            <span class="input-group-addon" style="background: #f8fafc;"><i class="fa fa-phone text-muted"></i></span>
            <input type="text" id="nuevoChatTelefono" class="form-control" placeholder="Ej: 573001234567" required>
          </div>
          <small style="color: #64748b; font-size: 11px;">Incluye el código de país (ej. 57 para Colombia).</small>
        </div>

        <div class="form-group">
          <label style="font-weight: 600; font-size: 13px; color: #334155;">Nombre del Contacto (Opcional)</label>
          <div class="input-group">
            <span class="input-group-addon" style="background: #f8fafc;"><i class="fa fa-user text-muted"></i></span>
            <input type="text" id="nuevoChatNombre" class="form-control" placeholder="Ej: Juan Pérez">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label style="font-weight: 600; font-size: 13px; color: #334155;">Primer Mensaje (Opcional)</label>
          <textarea id="nuevoChatPrimerMensaje" class="form-control" rows="2" placeholder="Hola, me comunico desde..."></textarea>
        </div>
      </div>

      <div class="modal-footer" style="background: #f8fafc; padding: 10px 16px;">
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success btn-sm" id="btnCrearNuevoChat" style="background-color: #25D366; border-color: #25D366; font-weight: 600;">
          <i class="fa fa-commenting"></i> Abrir Chat
        </button>
      </div>

    </div>
  </div>
</div>

<?php if (isset($_SESSION["perfil"]) && $_SESSION["perfil"] === "_SystemMaster_"): 
  if (!class_exists('ControladorWhatsAppChats')) {
    require_once "controladores/whatsapp-chats.controlador.php";
  }
  if (!class_exists('ModeloWhatsAppChats')) {
    require_once "modelos/whatsapp-chats.modelo.php";
  }

  $protoWA = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
  $hostWA = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
  $baseDirWA = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/\\');
  $webhookUrlPHP = $protoWA . '://' . $hostWA . $baseDirWA . '/api/whatsapp/webhook.php';

  $confActualWA = ControladorWhatsAppChats::ctrObtenerConfiguracion();
?>
<!-- ==============================================
     MODAL: CONFIGURACIÓN DE CONEXIÓN WAFLOW
     (EXCLUSIVO _SystemMaster_)
=============================================== -->
<div class="modal fade" id="modalConfigWhatsApp" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
      
      <div class="modal-header" style="background: linear-gradient(135deg, #075e54, #128c7e); color: white; padding: 14px 18px;">
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.9;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
          <i class="fa fa-whatsapp"></i> Configuración de Conexión WaFloW
          <span class="label label-warning" style="font-size: 10px; padding: 2px 6px;">_SystemMaster_</span>
        </h4>
      </div>

      <form id="formConfigWhatsApp">
        <div class="modal-body" style="padding: 18px;">

          <!-- Alerta con URL de Webhook para WaFloW -->
          <div class="alert alert-info" style="margin-bottom: 16px; background: #f0fdf4; border-color: #bbf7d0; color: #166534; font-size: 12px; border-radius: 8px;">
            <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
              <i class="fa fa-link text-success" style="font-size: 14px;"></i> URL de Webhook para WaFloW:
            </div>
            <p style="margin-bottom: 6px; color: #374151;">Pega esta URL en tu instancia o webhook de WaFloW para recibir los mensajes entrantes en el POS:</p>
            <div class="input-group input-group-sm">
              <input type="text" id="waWebhookUrlInput" class="form-control" readonly value="<?php echo htmlspecialchars($webhookUrlPHP); ?>" style="background: white; font-family: monospace; font-size: 11.5px; color: #166534; font-weight: 600;">
              <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="btnCopiarWebhookWA" title="Copiar URL al portapapeles" style="font-weight: 600;">
                  <i class="fa fa-copy"></i> Copiar
                </button>
              </span>
            </div>
          </div>

          <!-- API Key -->
          <div class="form-group">
            <label style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><i class="fa fa-key text-warning"></i> WaFloW API Key *</label>
            <div class="input-group">
              <span class="input-group-addon" style="background: #f8fafc;"><i class="fa fa-shield text-muted"></i></span>
              <input type="password" id="waApiKeyInput" class="form-control" value="<?php echo htmlspecialchars($confActualWA['api_key'] ?? ''); ?>" placeholder="wf_node_..." required autocomplete="off">
              <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="btnToggleApiKeyWA" title="Ver / Ocultar API Key">
                  <i class="fa fa-eye" id="iconoToggleApiKey"></i>
                </button>
              </span>
            </div>
            <small style="color: #64748b; font-size: 11px;">Clave Bearer Token generada en tu panel de WaFloW.ai.</small>
          </div>

          <!-- Location ID y Slot ID -->
          <div class="row">
            <div class="col-md-7">
              <div class="form-group">
                <label style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><i class="fa fa-map-marker text-danger"></i> Location ID *</label>
                <input type="text" id="waLocationIdInput" class="form-control" value="<?php echo htmlspecialchars($confActualWA['location_id'] ?? 'loc_e1033bf5-06be-40b1-9a36-8b60e9d3165b'); ?>" placeholder="loc_..." required>
                <small style="color: #64748b; font-size: 11px; display: block;">Identificador de la subcuenta/locación en WaFloW.</small>
                <small style="color: #0284c7; font-size: 11px; font-weight: 600; display: block; margin-top: 2px;"><i class="fa fa-map-signs"></i> Ruta: Configuracion/integraciones/OpenAI</small>
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><i class="fa fa-hashtag text-primary"></i> Slot ID (Línea) *</label>
                <input type="number" id="waSlotIdInput" class="form-control" value="<?php echo htmlspecialchars($confActualWA['slot_id'] ?? 1); ?>" placeholder="1" min="1" required style="font-weight: 700;">
                <small style="color: #0284c7; font-size: 11px;"><strong>Cambia aquí de número:</strong> Si vinculas una nueva línea en WaFloW, ingresa aquí su Slot ID.</small>
              </div>
            </div>
          </div>

          <!-- Teléfono Conectado y Agency ID -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><i class="fa fa-phone text-success"></i> Número de WhatsApp Conectado</label>
                <input type="text" id="waTelefonoConectadoInput" class="form-control" value="<?php echo htmlspecialchars($confActualWA['telefono_conectado'] ?? ''); ?>" placeholder="Ej: 573013142899">
                <small style="color: #64748b; font-size: 11px;">Referencia visual del número activo actualmente.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><i class="fa fa-building-o text-muted"></i> Agency ID (Opcional)</label>
                <input type="text" id="waAgencyIdInput" class="form-control" value="<?php echo htmlspecialchars($confActualWA['agency_id'] ?? 'AG-1793500451718'); ?>" placeholder="AG-1793500451718">
                <small style="color: #64748b; font-size: 11px;">ID de tu agencia en WaFloW.</small>
              </div>
            </div>
          </div>

          <!-- Endpoint API -->
          <div class="form-group" style="margin-bottom: 10px;">
            <label style="font-weight: 600; font-size: 12.5px; color: #1e293b;"><i class="fa fa-send-o text-info"></i> Endpoint Oficial de Envío (API URL)</label>
            <input type="url" id="waApiUrlInput" class="form-control" value="<?php echo htmlspecialchars($confActualWA['api_url'] ?? 'https://wa.waflow.ai/agency/n8n/send-message'); ?>" required>
            <small style="color: #64748b; font-size: 11px;">URL oficial para despachar mensajes salientes vía n8n/WaFloW.</small>
          </div>

          <!-- Contenedor para resultado de prueba de conexión -->
          <div id="alertaTestConexionWA" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: 8px; font-size: 12.5px;"></div>

        </div>

        <div class="modal-footer" style="background: #f8fafc; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
          <button type="button" class="btn btn-info btn-sm" id="btnTestConexionWA" style="font-weight: 600; border-radius: 6px;">
            <i class="fa fa-plug"></i> Probar Conexión
          </button>
          <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="border-radius: 6px;">Cerrar</button>
            <button type="submit" class="btn btn-success btn-sm" id="btnGuardarConfigWA" style="background-color: #25D366; border-color: #25D366; font-weight: 600; border-radius: 6px;">
              <i class="fa fa-save"></i> Guardar Cambios
            </button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>
<?php endif; ?>

<!-- ==============================================
     MODAL: VISTA PREVIA DE IMAGEN
=============================================== -->
<div class="modal fade" id="modalZoomImagenWhatsApp" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="background: transparent; box-shadow: none; border: none; text-align: center;">
      <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1; font-size: 30px; margin-bottom: 10px;">&times;</button>
      <img id="imgZoomWhatsApp" src="" style="max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 8px 30px rgba(0,0,0,0.5);">
    </div>
  </div>
</div>

<!-- ESTILOS ESPECÍFICOS DE WHATSAPP WEB -->
<style>
.whatsapp-app-container {
    display: flex;
    height: calc(100vh - 145px);
    min-height: 560px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

/* SIDEBAR */
.whatsapp-sidebar {
    width: 360px;
    min-width: 300px;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

.whatsapp-sidebar-header {
    padding: 12px 16px;
    background: #f0f2f5;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.whatsapp-user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #dfe5e7;
    color: #54656f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.whatsapp-search-box {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f2f5;
    background: #ffffff;
}

.whatsapp-chat-list {
    flex: 1;
    overflow-y: auto;
}

.whatsapp-chat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    transition: background 0.15s ease;
    user-select: none;
}

.whatsapp-chat-item:hover {
    background: #f5f6f6;
}

.whatsapp-chat-item.active {
    background: #ebebeb;
}

.whatsapp-item-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #25D366;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
}

.whatsapp-item-info {
    flex: 1;
    overflow: hidden;
}

.whatsapp-item-name {
    font-weight: 600;
    font-size: 14px;
    color: #111b21;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.whatsapp-item-time {
    font-size: 11px;
    color: #667781;
    font-weight: 400;
}

.whatsapp-item-lastmsg {
    font-size: 12px;
    color: #667781;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.whatsapp-badge-unread {
    background-color: #25D366;
    color: white;
    font-size: 10px;
    font-weight: 700;
    border-radius: 10px;
    padding: 2px 6px;
    min-width: 18px;
    text-align: center;
}

/* CHAT PANEL */
.whatsapp-chat-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #efeae2;
    position: relative;
}

.whatsapp-empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 30px;
    background: #f0f2f5;
}

.whatsapp-chat-header {
    padding: 10px 18px;
    background: #f0f2f5;
    border-bottom: 1px solid #d1d7db;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
}

.whatsapp-chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #25D366;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
}

.whatsapp-messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 16px 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background-color: #efeae2;
    background-image: radial-gradient(#d5cdc4 1px, transparent 1px);
    background-size: 20px 20px;
}

/* BURBUJAS DE MENSAJE */
.whatsapp-message-bubble {
    max-width: 68%;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13.5px;
    line-height: 1.45;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.whatsapp-bubble-inbound {
    align-self: flex-start;
    background: #ffffff;
    color: #111b21;
    border-top-left-radius: 0;
}

.whatsapp-bubble-outbound {
    align-self: flex-end;
    background: #d9fdd3;
    color: #111b21;
    border-top-right-radius: 0;
}

.whatsapp-bubble-sender {
    font-weight: 700;
    font-size: 11.5px;
    color: #128c7e;
    margin-bottom: 2px;
}

.whatsapp-bubble-time {
    font-size: 10px;
    color: #667781;
    text-align: right;
    margin-top: 3px;
    margin-left: 10px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
}

.whatsapp-chat-input-bar {
    padding: 10px 16px;
    background: #f0f2f5;
    border-top: 1px solid #d1d7db;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 2;
}

.whatsapp-btn-send {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #25D366;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.15s ease;
    flex-shrink: 0;
}

.whatsapp-btn-send:hover {
    background: #20ba5a;
    transform: scale(1.05);
}

.whatsapp-btn-send:active {
    transform: scale(0.95);
}

.whatsapp-date-divider {
    align-self: center;
    background: #ffffff;
    color: #54656f;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    margin: 8px 0;
}

/* =============================================
   BOTÓN VOLVER EN MÓVIL (Oculto en Desktop)
============================================= */
.btn-volver-chats-mobile {
    display: none;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #d1d7db;
    color: #54656f;
    flex-shrink: 0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.btn-volver-chats-mobile:hover,
.btn-volver-chats-mobile:focus {
    background: #e9edef;
    color: #111b21;
}

/* =============================================
   RESPONSIVE MÓVIL / CELULAR (PANTALLAS <= 768px)
============================================= */
@media (max-width: 768px) {
    .content-header {
        padding: 8px 10px 0 10px !important;
    }
    .content-header h1 {
        font-size: 16px !important;
    }
    .content-header .breadcrumb {
        display: none !important;
    }
    .content {
        padding: 6px 8px !important;
    }

    .whatsapp-app-container {
        height: calc(100vh - 120px) !important;
        min-height: 480px !important;
        position: relative !important;
        border-radius: 8px !important;
    }

    /* Estado inicial en móvil: Lista de chats ocupa el 100%, chat oculto */
    .whatsapp-sidebar {
        width: 100% !important;
        min-width: 100% !important;
        flex: 1 !important;
        display: flex !important;
        border-right: none !important;
    }

    .whatsapp-chat-panel {
        width: 100% !important;
        min-width: 100% !important;
        display: none !important;
    }

    /* Cuando se abre una conversación en móvil */
    .whatsapp-app-container.chat-activo-mobile .whatsapp-sidebar {
        display: none !important;
    }

    .whatsapp-app-container.chat-activo-mobile .whatsapp-chat-panel {
        display: flex !important;
        width: 100% !important;
        height: 100% !important;
        flex: 1 !important;
    }

    .whatsapp-app-container.chat-activo-mobile #panelChatActivo {
        display: flex !important;
        height: 100% !important;
        width: 100% !important;
        flex: 1 !important;
    }

    /* Mostrar botón Volver en el encabezado en móvil */
    .btn-volver-chats-mobile {
        display: inline-flex !important;
    }

    /* Encabezado más compacto en móvil */
    .whatsapp-chat-header {
        padding: 8px 10px !important;
    }
    .btn-pipeline-text {
        display: none !important;
    }
    #headerChatNombre {
        max-width: 140px !important;
        font-size: 14px !important;
    }

    /* Burbujas adaptadas a pantallas pequeñas */
    .whatsapp-messages-container {
        padding: 10px 8px !important;
    }
    .whatsapp-message-bubble {
        max-width: 88% !important;
        font-size: 13px !important;
    }

    /* Barra de envío adaptada */
    .whatsapp-chat-input-bar {
        padding: 8px 10px !important;
        gap: 6px !important;
    }
    .whatsapp-btn-send {
        width: 38px !important;
        height: 38px !important;
        font-size: 14px !important;
    }
}
</style>

<script src="vistas/js/whatsapp-chats.js?v=<?php echo time(); ?>"></script>
