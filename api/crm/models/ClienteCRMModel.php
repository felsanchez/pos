<?php

require_once __DIR__ . '/../../database.php';

class ClienteCRMModel
{

    /**
     * Convierte cualquier formato de teléfono
     * al formato:
     *
     * 573005331111
     */
    private static function normalizarTelefono($telefono)
    {

        // Eliminar todo lo que no sea número
        $telefono = preg_replace('/\D/', '', $telefono);

        // Si viene con 10 dígitos, agregar código de país
        if (strlen($telefono) === 10) {

            $telefono = '57' . $telefono;

        }

        // Si tiene más de 12 dígitos, conservar los últimos 12
        if (strlen($telefono) > 12) {

            $telefono = substr($telefono, -12);

        }

        return $telefono;

    }
    
    
    /**
     * Convierte:
     *
     * 573013142899
     *
     * a:
     *
     * (301) 314-2899
     */
    private static function formatearTelefono($telefono)
    {
    
        $telefono = self::normalizarTelefono($telefono);
    
        // Conservar únicamente los últimos 10 dígitos
        $telefono = substr($telefono, -10);
    
        return sprintf(
            "(%s) %s-%s",
            substr($telefono, 0, 3),
            substr($telefono, 3, 3),
            substr($telefono, 6, 4)
        );
    
    }
    
    
    /**
     * Busca un cliente por teléfono.
     *
     * Retorna:
     * - Array con los datos del cliente.
     * - null si no existe.
     */
    private static function buscarCliente($telefono)
    {
    
        $db = Database::conectar();
    
        $telefono = self::normalizarTelefono($telefono);
    
        // Buscar utilizando únicamente los últimos 10 dígitos
        $telefonoBusqueda = substr($telefono, -10);
    
        $sql = "
            SELECT
                id,
                nombre,
                direccion,
                telefono,
                documento,
                estatus,
                notas,
                compras,
                eliminado
                
            FROM clientes
            WHERE
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(telefono,'(', ''),
                        ')',''),
                    '-',''),
                ' ','') = ?
            LIMIT 1
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la consulta.");
        }
    
        $stmt->bind_param("s", $telefonoBusqueda);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        if ($resultado->num_rows === 0) {
            return null;
        }
    
        return $resultado->fetch_assoc();
    
    }
    
    
    /**
     * Busca un cliente por su ID.
     *
     * Retorna:
     * - Array con los datos del cliente.
     * - null si no existe.
     */
    private static function buscarClientePorId($idCliente)
    {
    
        $db = Database::conectar();
    
        $sql = "
            SELECT
                id,
                nombre,
                direccion,
                telefono,
                documento,
                estatus,
                notas,
                compras
            FROM clientes
            WHERE id = ?
            LIMIT 1
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la consulta.");
        }
    
        $stmt->bind_param("i", $idCliente);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        if ($resultado->num_rows === 0) {
            return null;
        }
    
        return $resultado->fetch_assoc();
    
    }
        
    
    /**
     * Crea un nuevo cliente.
     *
     * Retorna el ID del cliente creado.
     */
    private static function crearCliente($telefono, $nombre = null, $direccion = null)
    {
    
        $db = Database::conectar();
    
        $telefonoNormalizado = self::normalizarTelefono($telefono);
    
        $telefonoFormateado = self::formatearTelefono($telefono);
    
        $documento = substr($telefonoNormalizado, -10);
    
        $nombre = !empty(trim((string)$nombre))
            ? trim($nombre)
            : "No registra";
    
        $direccion = !empty(trim((string)$direccion))
            ? trim($direccion)
            : "No registra";
    
        $estatus = "Leads";
    
        $notas = "Registrado por la IA";
    
        $compras = 0;
    
        $sql = "
            INSERT INTO clientes (
                nombre,
                documento,
                telefono,
                direccion,
                estatus,
                notas,
                compras
            )
            VALUES (?,?,?,?,?,?,?)
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la consulta.");
        }
    
        $stmt->bind_param(
            "ssssssi",
            $nombre,
            $documento,
            $telefonoFormateado,
            $direccion,
            $estatus,
            $notas,
            $compras
        );
    
        if (!$stmt->execute()) {
            throw new Exception("No fue posible crear el cliente.");
        }
        
        $idCliente = $db->insert_id;
        
        // Retornar toda la información del cliente recién creado
        return [
            "id" => $idCliente,
            "nombre" => $nombre,
            "direccion" => $direccion,
            "telefono" => $telefonoFormateado,
            "documento" => $documento,
            "estatus" => $estatus,
            "notas" => $notas,
            "compras" => $compras
        ];
    
    }
    
    
    /**
     * Reactiva un cliente eliminado.
     */
    private static function reactivarCliente($idCliente)
    {
    
        $db = Database::conectar();
    
        $sql = "
            UPDATE clientes
            SET eliminado = 0
            WHERE id = ?
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la actualización.");
        }
    
        $stmt->bind_param("i", $idCliente);
    
        if (!$stmt->execute()) {
            throw new Exception("No fue posible reactivar el cliente.");
        }
    
        return true;
    
    }


    /**
     * Actualiza únicamente la información faltante del cliente.
     *
     * Nunca reemplaza información válida.
     */
    private static function actualizarCliente($idCliente, $nombre = null, $direccion = null)
    {
    
        $db = Database::conectar();
    
        $sql = "
            SELECT
                nombre,
                direccion
            FROM clientes
            WHERE id = ?
            LIMIT 1
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la consulta.");
        }
    
        $stmt->bind_param("i", $idCliente);
    
        $stmt->execute();
    
        $cliente = $stmt->get_result()->fetch_assoc();
    
        if (!$cliente) {
            throw new Exception("Cliente no encontrado.");
        }
    
        $campos = [];
        $tipos = "";
        $valores = [];
    
        // Actualizar nombre únicamente si actualmente es "No registra"
        if (
            !empty($nombre) &&
            trim($cliente["nombre"]) === "No registra"
        ) {
    
            $campos[] = "nombre = ?";
            $tipos .= "s";
            $valores[] = trim($nombre);
    
        }
    
        // Actualizar dirección únicamente si actualmente es "No registra"
        if (
            !empty($direccion) &&
            trim($cliente["direccion"]) === "No registra"
        ) {
    
            $campos[] = "direccion = ?";
            $tipos .= "s";
            $valores[] = trim($direccion);
    
        }
    
        // No hay nada para actualizar
        if (empty($campos)) {
            return false;
        }
    
        $sql = "
            UPDATE clientes
            SET " . implode(", ", $campos) . "
            WHERE id = ?
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la actualización.");
        }
    
        $tipos .= "i";
        $valores[] = $idCliente;
    
        $stmt->bind_param($tipos, ...$valores);
    
        if (!$stmt->execute()) {
            throw new Exception("No fue posible actualizar el cliente.");
        }
    
        return true;
    
    }
    
    
    /**
     * Procesa un cliente para el CRM.
     *
     * Busca el cliente por teléfono.
     * Si no existe, lo crea.
     * Si existe, completa la información faltante.
     *
     * Retorna:
     * [
     *   success => true,
     *   accion => cliente_creado | cliente_existente | cliente_actualizado,
     *   id_cliente => 1
     * ]
     */
    public static function procesarCliente($telefono, $nombre = null, $direccion = null)
    {
    
        $cliente = self::buscarCliente($telefono);
        
        // El cliente existe pero está eliminado
        if ($cliente && (int)$cliente["eliminado"] === 1) {
        
            self::reactivarCliente($cliente["id"]);
        
            // Actualizar únicamente la información faltante
            self::actualizarCliente(
                $cliente["id"],
                $nombre,
                $direccion
            );
        
            // Reflejar el nuevo estado en la respuesta
            $cliente["eliminado"] = 0;
            $cliente["nuevo"] = true;
        
            return $cliente;
        
        }
    
        // No existe
        if (!$cliente) {
        
            $cliente = self::crearCliente(
                $telefono,
                $nombre,
                $direccion
            );
        
            $cliente["nuevo"] = true;
        
            return $cliente;
        
        }
    
        // Existe
        $actualizado = self::actualizarCliente(
            $cliente["id"],
            $nombre,
            $direccion
        );
    
       $cliente["nuevo"] = false;

        return $cliente;
    
    }


}