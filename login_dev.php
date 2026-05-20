<?php
require_once "modelos/session-manager.php";
require_once "modelos/perfiles.modelo.php";
SessionManager::startSecure();
$_SESSION["iniciarSesion"] = "ok";
$_SESSION["id"] = 43;
$_SESSION["nombre"] = "pipez lopez";
$_SESSION["usuario"] = "pipez";
$_SESSION["perfil"] = "Administrador";
$_SESSION["id_bodega"] = 1;
header("Location: gastos");
exit;
