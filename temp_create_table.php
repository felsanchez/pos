<?php
require_once "modelos/conexion.php";

try {
    $conn = Conexion::conectar();
    $sql = "CREATE TABLE IF NOT EXISTS pagos_bold (
        id INT(11) NOT NULL AUTO_INCREMENT,
        fecha DATETIME NOT NULL,
        cuenta VARCHAR(255) NOT NULL,
        correo VARCHAR(255) NOT NULL,
        monto FLOAT NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;";

    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        echo "Tabla pagos_bold creada correctamente.";
    } else {
        print_r($stmt->errorInfo());
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>