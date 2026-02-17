<?php
require_once "controladores/plantilla.controlador.php";
require_once "modelos/conexion.php";

echo "<h2>Verificar Estado del Sistema</h2>";

$db = Conexion::conectar();

// 1. Ver consecutivo actual
$stmt = $db->query("SELECT numero_actual FROM factus_rangos WHERE id_factus = 1040");
$rango = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>1. Consecutivo Actual:</h3>";
echo "<p><strong>numero_actual:</strong> {$rango['numero_actual']}</p>";
echo "<p><strong>Próxima factura:</strong> " . ($rango['numero_actual'] + 1) . "</p>";

// 2. Ver últimas ventas
echo "<h3>2. Últimas 5 Ventas:</h3>";
$stmt = $db->query("SELECT id, codigo, numero_factura, estado_dian, mensaje_dian, fecha_envio_dian 
                     FROM ventas 
                     ORDER BY id DESC 
                     LIMIT 5");
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Código</th><th>Número Factura</th><th>Estado DIAN</th><th>Mensaje</th></tr>";
foreach ($ventas as $v) {
    $mensaje = substr($v['mensaje_dian'], 0, 80);
    echo "<tr>";
    echo "<td>{$v['id']}</td>";
    echo "<td>{$v['codigo']}</td>";
    echo "<td>{$v['numero_factura']}</td>";
    echo "<td>{$v['estado_dian']}</td>";
    echo "<td>$mensaje...</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Recomendación
echo "<h3>3. Recomendación:</h3>";
echo "<div style='background:#fff3cd; border:1px solid #ffc107; padding:15px; margin:10px 0;'>";
echo "<h4>Opciones para Continuar:</h4>";
echo "<ol>";
echo "<li><strong>Intentar crear factura 11:</strong> Vaya a 'Crear Factura Electrónica' e intente crear una nueva venta. Si el error 409 persiste, es un bloqueo del servidor de Factus.</li>";
echo "<li><strong>Contactar a Factus:</strong> Si el error 409 continúa, solo el soporte de Factus puede liberar el bloqueo desde su servidor.</li>";
echo "<li><strong>Esperar:</strong> A veces el bloqueo se resuelve automáticamente en 24-48 horas.</li>";
echo "</ol>";
echo "</div>";

echo "<h3>4. Sobre el Error 401:</h3>";
echo "<p>El token obtenido con <code>client_credentials</code> no tiene permisos para <strong>listar</strong> facturas, pero SÍ tiene permisos para <strong>crear</strong> facturas (que es lo que necesitamos).</p>";
echo "<p>Esto es una limitación de la API de Factus - algunos endpoints requieren autenticación de usuario (OAuth) en lugar de autenticación de aplicación (client_credentials).</p>";

echo "<h3>5. Resumen de Correcciones Aplicadas:</h3>";
echo "<ul>";
echo "<li>✅ Corrección del tributo: Ahora usa el porcentaje correcto del tributo asignado</li>";
echo "<li>✅ Consecutivo actualizado: Está en 10, próxima factura será 11</li>";
echo "<li>✅ Actualización de estado: Ahora usa 'enviada' en lugar de 'Enviado'</li>";
echo "<li>✅ Extracción de número: Mejorada para formatos con y sin guión</li>";
echo "</ul>";
