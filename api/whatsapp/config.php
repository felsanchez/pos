<?php
/**
 * ============================================================
 * CONFIGURACIÓN DE INTEGRACIÓN WAFLOW.AI WHATSAPP
 * KONTROL POS
 * ============================================================
 */

// Intentar cargar configuración dinámica desde la base de datos
$configBd = null;
try {
    require_once __DIR__ . '/../../modelos/conexion.php';
    $dbConfig = Conexion::conectar();
    if ($dbConfig) {
        $stmtConf = $dbConfig->query("SELECT * FROM whatsapp_configuracion WHERE id = 1 LIMIT 1");
        if ($stmtConf) {
            $configBd = $stmtConf->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (Throwable $e) {}

$apiKeyDef = (!empty($configBd) && !empty($configBd['api_key'])) ? trim($configBd['api_key']) : 'wf_node_73ddd265bbaa3603ef6fe56e8f8401f033902f2e10e49866';
$agencyIdDef = (!empty($configBd) && !empty($configBd['agency_id'])) ? trim($configBd['agency_id']) : 'AG-1793500451718';
$locationIdDef = (!empty($configBd) && !empty($configBd['location_id'])) ? trim($configBd['location_id']) : 'loc_e1033bf5-06be-40b1-9a36-8b60e9d3165b';
$slotIdDef = (!empty($configBd) && isset($configBd['slot_id'])) ? intval($configBd['slot_id']) : 1;
$apiUrlDef = (!empty($configBd) && !empty($configBd['api_url'])) ? trim($configBd['api_url']) : 'https://wa.waflow.ai/agency/n8n/send-message';
$telefonoDef = (!empty($configBd) && !empty($configBd['telefono_conectado'])) ? trim($configBd['telefono_conectado']) : '573013142899';

// Clave API de WaFloW
if (!defined('WAFLOW_API_KEY')) {
    define('WAFLOW_API_KEY', $apiKeyDef);
}

// ID de Agencia
if (!defined('WAFLOW_AGENCY_ID')) {
    define('WAFLOW_AGENCY_ID', $agencyIdDef);
}

// Location ID de la cuenta en WaFloW
if (!defined('WAFLOW_LOCATION_ID')) {
    define('WAFLOW_LOCATION_ID', $locationIdDef);
}

// Slot ID de la línea de WhatsApp
if (!defined('WAFLOW_SLOT_ID')) {
    define('WAFLOW_SLOT_ID', $slotIdDef);
}

// Endpoint oficial de envío de WaFloW
if (!defined('WAFLOW_API_URL')) {
    define('WAFLOW_API_URL', $apiUrlDef);
}

// Teléfono de referencia de la línea
if (!defined('WAFLOW_TELEFONO_CONECTADO')) {
    define('WAFLOW_TELEFONO_CONECTADO', $telefonoDef);
}
