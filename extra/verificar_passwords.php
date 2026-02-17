<?php
/**
 * Script de Verificación de Migración de Contraseñas
 * 
 * Este script verifica el formato de las contraseñas en la base de datos
 * para confirmar que la migración se está realizando correctamente.
 */

require_once "../modelos/conexion.php";

echo "\n=== VERIFICACIÓN DE MIGRACIÓN DE CONTRASEÑAS ===\n\n";

try {
    $db = Conexion::conectar();

    // Obtener todos los usuarios
    $stmt = $db->prepare("SELECT id, usuario, password FROM usuarios ORDER BY id");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalUsuarios = count($usuarios);
    $passwordsNuevas = 0;
    $passwordsAntiguas = 0;

    echo "Total de usuarios: $totalUsuarios\n\n";
    echo "Formato de contraseñas:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-20s %-15s %s\n", "ID", "Usuario", "Formato", "Hash (primeros 30 caracteres)");
    echo str_repeat("-", 80) . "\n";

    foreach ($usuarios as $usuario) {
        $hash = $usuario['password'];
        $formato = "DESCONOCIDO";

        // Verificar formato del hash
        if (substr($hash, 0, 4) === '$2y$') {
            $formato = "NUEVO (bcrypt)";
            $passwordsNuevas++;
        } elseif (substr($hash, 0, 4) === '$2a$') {
            $formato = "ANTIGUO (crypt)";
            $passwordsAntiguas++;
        }

        printf(
            "%-5d %-20s %-15s %s...\n",
            $usuario['id'],
            $usuario['usuario'],
            $formato,
            substr($hash, 0, 30)
        );
    }

    echo str_repeat("-", 80) . "\n\n";
    echo "RESUMEN:\n";
    echo "  Contraseñas con formato NUEVO (seguro):   $passwordsNuevas\n";
    echo "  Contraseñas con formato ANTIGUO:          $passwordsAntiguas\n";
    echo "\n";

    if ($passwordsAntiguas > 0) {
        echo "⚠️  NOTA: Hay $passwordsAntiguas usuario(s) con contraseñas antiguas.\n";
        echo "   Se migrarán automáticamente cuando inicien sesión.\n";
    } else {
        echo "✅ ¡Todos los usuarios tienen contraseñas con formato seguro!\n";
    }

    echo "\n=== FIN DE LA VERIFICACIÓN ===\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
