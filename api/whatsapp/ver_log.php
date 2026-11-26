<?php
/**
 * VER LOG DE WEBHOOK - Solo accesible localmente o con clave
 * URL: https://gestion.kontrolpos.com/api/whatsapp/ver_log.php?clave=kpos2024
 */
$clave = $_GET['clave'] ?? '';
if ($clave !== 'kpos2024') {
    http_response_code(403);
    die(json_encode(['error' => 'Acceso denegado']));
}

$logFile = __DIR__ . '/log_webhook.txt';
$action  = $_GET['accion'] ?? 'ver';

if ($action === 'borrar') {
    file_put_contents($logFile, '');
    echo json_encode(['ok' => true, 'msg' => 'Log borrado']);
    exit();
}

header('Content-Type: text/html; charset=utf-8');

$contenido = file_exists($logFile) ? file_get_contents($logFile) : '(Log vacío o no existe)';
// Mostrar solo los últimos 50kb
if (strlen($contenido) > 50000) {
    $contenido = '... [TRUNCADO - solo últimos 50kb] ...' . "\n\n" . substr($contenido, -50000);
}

// Buscar entradas con @g.us para destacarlas
$entradas = explode('=== [', $contenido);
$gruposEncontrados = [];
$todasEntradas = [];

foreach ($entradas as $entrada) {
    if (empty(trim($entrada))) continue;
    $esGrupo = (stripos($entrada, '@g.us') !== false || stripos($entrada, 'isGroup') !== false || stripos($entrada, 'groupName') !== false);
    if ($esGrupo) {
        $gruposEncontrados[] = $entrada;
    }
    $todasEntradas[] = ['texto' => $entrada, 'esGrupo' => $esGrupo];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Log Webhook WhatsApp</title>
<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
h1 { color: #25D366; }
h2 { color: #4ec9b0; }
.btn { display: inline-block; padding: 8px 16px; border-radius: 6px; text-decoration: none; cursor: pointer; margin: 4px; font-weight: bold; }
.btn-red { background: #c0392b; color: #fff; }
.btn-blue { background: #2980b9; color: #fff; }
.entrada { background: #2d2d2d; border: 1px solid #444; border-radius: 6px; padding: 12px; margin: 10px 0; white-space: pre-wrap; word-break: break-all; font-size: 12px; }
.entrada.grupo { border-color: #25D366; border-width: 2px; background: #1a2e1a; }
.badge-grupo { background: #25D366; color: #000; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
.stats { background: #333; padding: 12px; border-radius: 6px; margin: 10px 0; }
</style>
</head>
<body>
<h1>🔍 Log Webhook WhatsApp - Kontrol POS</h1>

<div class="stats">
    <strong>Total entradas:</strong> <?= count($todasEntradas) ?> |
    <strong>Entradas de GRUPOS:</strong> <span style="color:#25D366;font-size:18px;"><?= count($gruposEncontrados) ?></span>
</div>

<a class="btn btn-red" href="?clave=kpos2024&accion=borrar" onclick="return confirm('¿Borrar log?')">🗑 Borrar Log</a>
<a class="btn btn-blue" href="?clave=kpos2024" onclick="location.reload()">🔄 Actualizar</a>

<?php if (count($gruposEncontrados) > 0): ?>
<h2>✅ ENTRADAS DE GRUPOS ENCONTRADAS (<?= count($gruposEncontrados) ?>)</h2>
<?php foreach (array_reverse($gruposEncontrados) as $g): ?>
<div class="entrada grupo">
<span class="badge-grupo">GRUPO</span>
=== [<?= htmlspecialchars($g) ?>
</div>
<?php endforeach; ?>
<hr style="border-color:#444;">
<?php else: ?>
<h2 style="color:#e74c3c;">❌ No se encontraron entradas de grupos todavía</h2>
<p style="color:#aaa;">Envía un mensaje desde un grupo de WhatsApp y recarga esta página.</p>
<?php endif; ?>

<h2>📋 Últimas 20 entradas Webhook (todas)</h2>
<?php
$recientes = array_slice(array_reverse($todasEntradas), 0, 20);
foreach ($recientes as $e):
    $cls = $e['esGrupo'] ? 'entrada grupo' : 'entrada';
?>
<div class="<?= $cls ?>">
<?php if ($e['esGrupo']): ?><span class="badge-grupo">GRUPO</span> <?php endif; ?>
=== [<?= htmlspecialchars($e['texto']) ?>
</div>
<?php endforeach; ?>

<?php
$logEnviosFile = __DIR__ . '/log_envios.txt';
$contenidoEnvios = file_exists($logEnviosFile) ? file_get_contents($logEnviosFile) : '(Sin registros de envíos salientes aún)';
if (strlen($contenidoEnvios) > 30000) {
    $contenidoEnvios = '... [TRUNCADO - últimos 30kb] ...' . "\n\n" . substr($contenidoEnvios, -30000);
}
?>
<h2>📤 Registro de Envíos Salientes a WaFloW (log_envios.txt)</h2>
<div class="entrada" style="background: #111; color: #a6e22e; max-height: 350px; overflow-y: auto; font-size: 11.5px; border-color: #25D366;">
<?= htmlspecialchars($contenidoEnvios) ?>
</div>

</body>
</html>
