<?php
/**
 * Script para limpiar sesiones antiguas y forzar el uso de POS_SESSION
 */

// Destruir cualquier sesión activa
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Eliminar cookie PHPSESSID si existe
if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time() - 3600, '/');
    unset($_COOKIE['PHPSESSID']);
}

// Eliminar cookie POS_SESSION si existe
if (isset($_COOKIE['POS_SESSION'])) {
    setcookie('POS_SESSION', '', time() - 3600, '/');
    unset($_COOKIE['POS_SESSION']);
}

echo '<!DOCTYPE html>
<html>
<head>
    <title>Limpiar Sesiones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #28a745;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Sesiones Limpiadas</h1>
        <p>Se han eliminado todas las cookies de sesión antiguas.</p>
        
        <div class="info">
            <strong>Próximos pasos:</strong>
            <ol>
                <li>Haz clic en el botón de abajo para ir al login</li>
                <li>Inicia sesión normalmente</li>
                <li>Abre la consola (F12) y ejecuta: <code>document.cookie</code></li>
                <li>Deberías ver <strong>POS_SESSION</strong> en lugar de PHPSESSID</li>
            </ol>
        </div>
        
        <a href="login" class="btn">Ir al Login</a>
    </div>
</body>
</html>';
?>