<?php
// Evitar iniciar sesión real si ya existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "modelos/session-manager.php";
require_once "modelos/sanitizer.php";
require_once "modelos/helpers.php";

require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
require_once "controladores/productos.controlador.php";
require_once "modelos/productos.modelo.php";
require_once "controladores/clientes.controlador.php";
require_once "modelos/clientes.modelo.php";
require_once "controladores/usuarios.controlador.php";
require_once "modelos/usuarios.modelo.php";
require_once "controladores/notificaciones.controlador.php";
require_once "modelos/notificaciones.modelo.php";
require_once "controladores/configuracion.controlador.php";
require_once "modelos/configuracion.modelo.php";
require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";
require_once "controladores/movimientos.controlador.php";
require_once "modelos/movimientos.modelo.php";
require_once "controladores/bodegas.controlador.php";
require_once "modelos/bodegas.modelo.php";

function runTest($scenarioName, $sessionProfile, $sessionIdBodega, $paramBodegaId) {
    // Configurar sesión
    $_SESSION["iniciarSesion"] = "ok";
    $_SESSION["perfil"] = $sessionProfile;
    $_SESSION["id_bodega"] = $sessionIdBodega;
    
    $params = [
        "draw" => 1,
        "start" => 0,
        "length" => 10,
        "search" => ["value" => ""],
        "bodegaId" => $paramBodegaId
    ];
    
    $response = ControladorVentas::ctrMostrarOrdenesServerSide($params);
    
    echo "==================================================\n";
    echo "ESCENARIO: $scenarioName\n";
    echo "Sesion: Perfil = $sessionProfile, id_bodega = " . ($sessionIdBodega ?? 'NULL') . "\n";
    echo "Parametro bodegaId enviado: '" . $paramBodegaId . "'\n";
    echo "RESULTADO:\n";
    echo "  Total Records (recordsTotal): " . $response["recordsTotal"] . "\n";
    echo "  Filtered Records (recordsFiltered): " . $response["recordsFiltered"] . "\n";
    echo "  Registros devueltos: " . count($response["data"]) . "\n";
    if (count($response["data"]) > 0) {
        $codes = [];
        foreach ($response["data"] as $row) {
            // Extraer el código limpio de la primera celda
            $codes[] = trim(strip_tags($row[0]));
        }
        echo "  Codigos: " . implode(", ", $codes) . "\n";
    } else {
        echo "  Codigos: (ninguno)\n";
    }
    echo "==================================================\n\n";
}

// Escenario 1: Super Administrador (id_bodega = NULL)
runTest("Super Admin - Mostrar Todas", "Administrador", null, "");
runTest("Super Admin - Filtrar Bodega 1", "Administrador", null, "1");
runTest("Super Admin - Filtrar Bodega 2", "Administrador", null, "2");

// Escenario 2: Admin Bodega 1 (id_bodega = 1)
runTest("Admin Bodega 1 - Mostrar Todas", "Administrador", 1, "");
runTest("Admin Bodega 1 - Filtrar Bodega 1", "Administrador", 1, "1");

// Escenario 3: Admin Bodega 2 (id_bodega = 2)
runTest("Admin Bodega 2 - Mostrar Todas (Debe restringir a 2)", "Administrador", 2, "");
runTest("Admin Bodega 2 - Filtrar Bodega 1 (Debe permitir ver 1)", "Administrador", 2, "1");

// Escenario 4: Vendedor Bodega 2 (id_bodega = 2)
runTest("Vendedor Bodega 2 - Sin filtro (Debe restringir a 2)", "Vendedor", 2, "");

// Escenario 5: Vendedor sin bodega (id_bodega = NULL)
runTest("Vendedor sin bodega - Sin filtro (Debe fallback a Bodega 1)", "Vendedor", null, "");
