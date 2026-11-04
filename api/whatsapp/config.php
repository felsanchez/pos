<?php
/**
 * ============================================================
 * CONFIGURACIÓN DE INTEGRACIÓN WAFLOW.AI WHATSAPP
 * KONTROL POS
 * ============================================================
 */

// Clave API de WaFloW
if (!defined('WAFLOW_API_KEY')) {
    define('WAFLOW_API_KEY', 'wf_node_73ddd265bbaa3603ef6fe56e8f8401f033902f2e10e49866');
}

// ID de Agencia
if (!defined('WAFLOW_AGENCY_ID')) {
    define('WAFLOW_AGENCY_ID', 'AG-1793500451718');
}

// Location ID de la cuenta en WaFloW (obtenido del webhook verificado)
if (!defined('WAFLOW_LOCATION_ID')) {
    define('WAFLOW_LOCATION_ID', 'loc_e1033bf5-06be-40b1-9a36-8b60e9d3165b');
}

// Slot ID de la línea de WhatsApp (Slot 1: "numero principal1")
if (!defined('WAFLOW_SLOT_ID')) {
    define('WAFLOW_SLOT_ID', 1);
}

// Endpoint oficial de envío de WaFloW
if (!defined('WAFLOW_API_URL')) {
    define('WAFLOW_API_URL', 'https://wa.waflow.ai/agency/n8n/send-message');
}
