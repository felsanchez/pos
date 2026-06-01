<?php
session_start();
$_SESSION["perfil"] = "Administrador";

// Cargar variables de entorno
require_once "config.php";

// Cargar todos los controladores
require_once "controladores/factus.controlador.php";
require_once "controladores/configuracion.controlador.php";
require_once "controladores/cajas.controlador.php";

// Cargar todos los modelos
require_once "modelos/conexion.php";
require_once "modelos/factus.modelo.php";
require_once "modelos/configuracion.modelo.php";
require_once "modelos/cajas.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/clientes.modelo.php";

try {
    $db = Conexion::conectar();
    
    // Obtener un ID de usuario válido de la base de datos
    $stmtUser = $db->prepare("SELECT id FROM usuarios LIMIT 1");
    $stmtUser->execute();
    $userId = $stmtUser->fetchColumn();
    if (!$userId) {
        die("No hay usuarios registrados en la base de datos para realizar el test.\n");
    }
    $_SESSION["id"] = intval($userId);
    $_SESSION["id_bodega"] = 1; // Opcional
    
    // Usar la venta con ID 12
    $idVenta = 12;
    
    $venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);
    if (!$venta) {
        die("Venta 12 no existe para el test\n");
    }
    
    $listaProductos = json_decode($venta["productos"], true);

    $motivos = ["1", "2", "3", "4", "5", "6"];

    foreach ($motivos as $motivo) {
        echo "\n--- Probando motivo '$motivo' ---\n";
        $res = ControladorFactus::ctrGenerarNotaCredito(
            $idVenta,
            $motivo,
            $listaProductos,
            $venta["id_cliente"],
            "Test motivo $motivo",
            "Efectivo",
            "Observacion motivo $motivo"
        );
        
        if (isset($res['error']) && $res['error']) {
            echo "FAIL: " . $res['mensaje'] . "\n";
            continue;
        }

        // Verificar en la BD
        $stmt = $db->prepare("SELECT id, tipo_nota, motivo FROM notas_credito ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $nc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($nc) {
            echo "INSERTADO CON ÉXITO -> ID: {$nc['id']} | tipo_nota (BD): '{$nc['tipo_nota']}' | motivo (BD): '{$nc['motivo']}'\n";
            
            // Limpiar
            $stmtDelete = $db->prepare("DELETE FROM notas_credito WHERE id = :id");
            $stmtDelete->execute([':id' => $nc['id']]);
        } else {
            echo "FAIL: No se insertó en la base de datos\n";
        }
    }

} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage() . "\n";
}
