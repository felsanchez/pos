<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/response.php';

class Database
{
    private static $conexion = null;

    private static $ownerPhone = null;

    public static function setOwnerPhone($phone)
    {
        self::$ownerPhone = preg_replace('/\D/', '', $phone);
    }

   public static function conectar()
    {
        if (self::$conexion === null) {
    
            // ==========================================================
            // Datos por defecto (Base de datos por defecto)
            // ==========================================================
            $host   = DB_HOST;
            $user   = DB_USER;
            $pass   = DB_PASS;
            $dbname = DB_NAME;
    
            // ==========================================================
            // Si se recibió owner_phone, buscar el tenant
            // ==========================================================
            if (!empty(self::$ownerPhone)) {
    
                // Conexión temporal a la Base Master
                $master = new mysqli(
                    MASTER_DB_HOST,
                    MASTER_DB_USER,
                    MASTER_DB_PASS,
                    MASTER_DB_NAME
                );
    
                if ($master->connect_error) {
    
                    errorResponse(
                        'Error conectando a la Base Master: ' . $master->connect_error,
                        500
                    );
    
                }
    
                $master->set_charset(DB_CHARSET);
    
                $sql = "
                    SELECT
                        id,
                        subdominio,
                        db_host,
                        db_name,
                        db_user,
                        db_pass,
                        estado
                    FROM clientes_tenants
                    WHERE celular = ?
                    LIMIT 1
                ";
    
                $stmt = $master->prepare($sql);
    
                if (!$stmt) {
    
                    $master->close();
    
                    errorResponse(
                        'Error preparando la consulta del tenant.',
                        500
                    );
    
                }
    
                $stmt->bind_param("s", self::$ownerPhone);
    
                $stmt->execute();
    
                $resultado = $stmt->get_result();
    
                if (!$tenant = $resultado->fetch_assoc()) {
    
                    $stmt->close();
                    $master->close();
    
                    errorResponse(
                        "No existe ningún tenant asociado al número: " . self::$ownerPhone,
                        404
                    );
    
                }
    
                // Validar estado del tenant
                if (strtolower(trim($tenant["estado"])) !== "activo") {
    
                    $stmt->close();
                    $master->close();
    
                    errorResponse(
                        "La cuenta del cliente está suspendida.",
                        403
                    );
    
                }
    
                // Reemplazar los datos de conexión con los del tenant
                $host   = $tenant["db_host"];
                $user   = $tenant["db_user"];
                $pass   = $tenant["db_pass"];
                $dbname = $tenant["db_name"];
    
                $stmt->close();
                $master->close();
    
            }
    
            // ==========================================================
            // Conexión final (Cliente o Base por defecto)
            // ==========================================================
            self::$conexion = new mysqli(
                $host,
                $user,
                $pass,
                $dbname
            );
    
            if (self::$conexion->connect_error) {
    
                errorResponse(
                    'Error de conexión: ' . self::$conexion->connect_error,
                    500
                );
    
            }
    
            self::$conexion->set_charset(DB_CHARSET);
    
        }
    
        return self::$conexion;
    }

}