<?php

require_once __DIR__ . '/../database.php';

class ClienteModel
{

    public static function buscarPorTelefono($telefono)
    {
        $db = Database::conectar();
    
        // Eliminar código de país (57) si viene desde WhatsApp
        if (substr($telefono, 0, 2) === "57") {
            $telefono = substr($telefono, 2);
        }
    
       // Conservar únicamente números
        $telefono = preg_replace('/\D/', '', $telefono);
        
        // Si viene con código de país (57), eliminarlo
        if (strlen($telefono) === 12 && substr($telefono, 0, 2) === '57') {
            $telefono = substr($telefono, 2);
        }
    
        $sql = "
            SELECT
                id,
                nombre
            FROM clientes
            WHERE
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(telefono,'(', ''),
                        ')', ''),
                    '-', ''),
                ' ', '') = ?
            LIMIT 1
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            errorResponse("Error preparando consulta.", 500);
        }
    
        $stmt->bind_param("s", $telefono);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        if ($resultado->num_rows === 0) {
            return null;
        }
    
        return $resultado->fetch_assoc();
    }

}