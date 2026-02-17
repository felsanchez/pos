<?php
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/conexion.php";

echo "<h1>Corrección de Numeración y Duplicados</h1>";

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();

    // 1. Corregir el rango en factus_rangos
    // Establecer numero_actual a 9 (ya que la última factura real es la 9)
    // Esto hará que la próxima sea la 10.
    echo "<h2>1. Actualizando Rango en BD</h2>";
    $stmt = $pdo->prepare("UPDATE factus_rangos SET numero_actual = 9 WHERE prefijo = 'FEFG' AND estado = 1");
    if ($stmt->execute()) {
        echo "<p class='text-success'>✅ Rango actualizado: numero_actual = 9</p>";
    } else {
        echo "<p class='text-danger'>❌ Error al actualizar rango</p>";
    }

    // 2. Analizar y corregir duplicado FEFG8
    echo "<h2>2. Corrigiendo Duplicado FEFG8</h2>";
    $stmt = $pdo->prepare("SELECT * FROM ventas WHERE numero_factura = 'FEFG8' ORDER BY id ASC");
    $stmt->execute();
    $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($duplicados) > 1) {
        // Estrategia: Mantener la que tenga más información (cufe/xml) o la más reciente si son iguales
        $idConservar = 0;
        $idEliminar = 0;

        $f1 = $duplicados[0]; // La más vieja (ID menor)
        $f2 = $duplicados[1]; // La más nueva (ID mayor)

        echo "<p>Se encontraron 2 facturas FEFG8:</p>";
        echo "<ul>";
        echo "<li>ID: " . $f1['id'] . " | Total: " . $f1['total'] . " | CUFE: " . ($f1['cufe'] ? 'SI' : 'NO') . "</li>";
        echo "<li>ID: " . $f2['id'] . " | Total: " . $f2['total'] . " | CUFE: " . ($f2['cufe'] ? 'SI' : 'NO') . "</li>";
        echo "</ul>";

        // Lógica de decisión
        if (!empty($f1['cufe']) && empty($f2['cufe'])) {
            $idConservar = $f1['id'];
            $idEliminar = $f2['id'];
        } elseif (empty($f1['cufe']) && !empty($f2['cufe'])) {
            $idConservar = $f2['id'];
            $idEliminar = $f1['id'];
        } else {
            // Si ambas tienen o no tienen cufe, conservamos la más reciente (suposición habitual) 
            // O conservamos la primera si queremos preservar historial.
            // Dado que IDs son 404 y 406, y 408 es la FEFG9.
            // Probablemente 406 sea un reintento.
            // Eliminaremos la más vieja (404) para evitar conflictos, asumiendo 406 es la buena.
            $idConservar = $f2['id'];
            $idEliminar = $f1['id'];
        }

        echo "<p><strong>Decisión:</strong> Conservar ID $idConservar, Eliminar ID $idEliminar</p>";

        // Ejecutar eliminación segura (solo actualizar numero_factura a NULL para no perder la venta, o borrar?)
        // Factus dice: Si quiero corregir "facturación", el duplicado es lo que molesta. 
        // Mejor quitar el numero_factura de la venta fallida para que quede como una venta "sin facturar" 
        // o borrarla si es basura.
        // Vamos a poner numero_factura = NULL y 'Error Duplicado' en observacion para no destruir datos de venta.

        $stmtDelete = $pdo->prepare("UPDATE ventas SET numero_factura = NULL, observacion = CONCAT(observacion, ' [DUPLICADO FEFG8 CORREGIDO]') WHERE id = :id");
        $stmtDelete->bindParam(":id", $idEliminar);

        if ($stmtDelete->execute()) {
            echo "<p class='text-success'>✅ ID $idEliminar corregido (se removió numero_factura)</p>";
        } else {
            echo "<p class='text-danger'>❌ Error al corregir ID $idEliminar</p>";
        }

    } else {
        echo "<p>No se encontraron duplicados o ya fueron corregidos.</p>";
    }

    $pdo->commit();
    echo "<h1>✅ PROCESO COMPLETADO EXITOSAMENTE</h1>";
    echo "<p>Ahora el sistema debería sugerir el consecutivo 10.</p>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h1>❌ ERROR FATAL</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>