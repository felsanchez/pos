<?php
// Script de prueba manual para verificar la corrección
// No podemos ejecutar el controlador completo fácilmente sin sesión POST, 
// pero podemos verificar la lógica del modelo que es la base.

require_once "modelos/conexion.php";
require_once "modelos/ventas.modelo.php";

echo "<h2>Prueba de Lógica de Consecutivo</h2>";

// 1. Obtener última venta real
$stmt = Conexion::conectar()->prepare("SELECT MAX(codigo) as max FROM ventas");
$stmt->execute();
$max = $stmt->fetch()['max'];
echo "Último código en BD: <b>$max</b><br>";

// 2. Simular que el frontend envía ese mismo código (Usuario A)
$codigoFrontend = $max;
echo "Código enviado por Frontend (simulado duplicado): <b>$codigoFrontend</b><br>";

// 3. Ejecutar la validación que pusimos en el controlador
$tabla = "ventas";
$ventaExistente = ModeloVentas::mdlMostrarVentas($tabla, "codigo", $codigoFrontend);

if ($ventaExistente) {
    echo "✅ DETECCIÓN EXITOSA: El sistema detectó que $codigoFrontend ya existe.<br>";

    // 4. Calcular nuevo código
    $nuevoCodigoReal = ModeloVentas::mdlObtenerSiguienteConsecutivo($tabla);
    echo "🔄 NUEVO CÓDIGO CALCULADO: <b>$nuevoCodigoReal</b> (Debería ser " . ($max + 1) . ")<br>";

    if ($nuevoCodigoReal == $max + 1) {
        echo "🎉 <b>PRUEBA PASADA:</b> El sistema corregiría automáticamente el duplicado.";
    } else {
        echo "❌ <b>FALLO:</b> El cálculo del nuevo código es incorrecto.";
    }

} else {
    echo "❌ <b>FALLO:</b> El sistema no detectó que el código ya existía (¿Tal vez la BD está vacía?).<br>";
}
?>