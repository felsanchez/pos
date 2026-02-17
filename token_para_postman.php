<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Token para Postman</h2>";

$db = Conexion::conectar();
$stmt = $db->query("SELECT access_token, token_expiracion FROM factus_config LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

$token = $config['access_token'];
$expiracion = $config['token_expiracion'];

echo "<h3>Token de Acceso:</h3>";
echo "<textarea style='width:100%; height:150px; font-family:monospace;'>$token</textarea>";

echo "<h3>Expira:</h3>";
echo "<p>$expiracion</p>";

if (strtotime($expiracion) < time()) {
    echo "<p style='color:red;'><strong>⚠️ El token expiró. Genere uno nuevo:</strong></p>";
    echo "<a href='generar_token_postman.php' style='background:blue; color:white; padding:10px; text-decoration:none;'>Generar Nuevo Token</a>";
}

echo "<hr>";
echo "<h3>Instrucciones para Postman:</h3>";
echo "<ol>";
echo "<li><strong>Método:</strong> DELETE</li>";
echo "<li><strong>URL:</strong> <code>https://api.factus.com.co/v1/bills/destroy/reference/VENTA-10</code></li>";
echo "<li><strong>Headers:</strong>";
echo "<ul>";
echo "<li>Content-Type: application/json</li>";
echo "<li>Accept: application/json</li>";
echo "<li>Authorization: Bearer [copie el token de arriba]</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Body:</strong> Vacío (no se necesita)</li>";
echo "<li>Click en <strong>Send</strong></li>";
echo "</ol>";

echo "<h3>Respuesta Esperada:</h3>";
echo "<pre>";
echo '{ "status": "OK", "message": "Documento con código de referencia VENTA-10 eliminado con éxito"}';
echo "</pre>";
