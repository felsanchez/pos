<?php

require_once "modelos/session-manager.php";

// Destruir sesión de forma segura
SessionManager::destroy();

echo '<script>
	window.location = "login";
</script>';

?>