<?php
require_once "modelos/session-manager.php";
SessionManager::startSecure();
echo json_encode($_SESSION, JSON_PRETTY_PRINT);
