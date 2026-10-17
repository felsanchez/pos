<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "modelos/conexion.php";
require_once "controladores/google.controlador.php";

$code = $_GET["code"] ?? null;
$state = $_GET["state"] ?? null;
$error = $_GET["error"] ?? null;

$idUsuario = !empty($state) ? intval($state) : ($_SESSION["id"] ?? null);

$resultado = null;
if ($code && $idUsuario) {
    $resultado = ControladorGoogle::ctrProcesarCallback($code, $idUsuario);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Conectando Google Calendar...</title>
    <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="vistas/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .card-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="card-box">
        <h3 style="color: #4285F4;"><i class="fa fa-google"></i> Google Calendar</h3>
        
        <?php if ($resultado && $resultado["status"] === "ok"): ?>
            <div class="alert alert-success" style="margin-top: 15px;">
                <h4><i class="icon fa fa-check"></i> ¡Cuenta Conectada!</h4>
                <p>Se ha vinculado exitosamente: <strong><?php echo htmlspecialchars($resultado["email"] ?? ""); ?></strong></p>
                <small>Redirigiendo al calendario...</small>
            </div>
            <script>
                setTimeout(function() {
                    window.location = "calendario";
                }, 1500);
            </script>
        <?php elseif ($error): ?>
            <div class="alert alert-warning" style="margin-top: 15px;">
                <h4><i class="icon fa fa-warning"></i> Autorización Cancelada</h4>
                <p>No se otorgaron los permisos para vincular la cuenta.</p>
                <a href="calendario" class="btn btn-warning">Volver al Calendario</a>
            </div>
            <script>
                setTimeout(function() {
                    window.location = "calendario";
                }, 2000);
            </script>
        <?php else: ?>
            <div class="alert alert-danger" style="margin-top: 15px;">
                <h4><i class="icon fa fa-ban"></i> Error de Conexión</h4>
                <p><?php echo htmlspecialchars($resultado["message"] ?? "No se pudieron obtener los datos de la cuenta."); ?></p>
                <a href="calendario" class="btn btn-default">Volver al Calendario</a>
            </div>
            <script>
                setTimeout(function() {
                    window.location = "calendario";
                }, 2500);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
