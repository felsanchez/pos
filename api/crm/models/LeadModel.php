<?php

require_once __DIR__ . '/../../database.php';

class LeadModel
{
    
    /**
     * Obtiene el último Lead del cliente.
     *
     * Retorna:
     * - Array con el Lead.
     * - null si no existe.
     */
    private static function buscarUltimoLead($idCliente)
    {
    
        $db = Database::conectar();
    
        $sql = "
            SELECT
                id,
                id_cliente,
                titulo,
                etapa,
                resumen_ia,
                productos_interes,
                origen,
                codigo_orden,
                notas,
                fecha_ultima_interaccion,
                fecha_cierre,
                fecha_creacion,
                fecha_actualizacion
            FROM crm_leads
            WHERE id_cliente = ?
            ORDER BY id DESC
            LIMIT 1
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la consulta del Lead.");
        }
    
        $stmt->bind_param("i", $idCliente);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        if ($resultado->num_rows === 0) {
            return null;
        }
    
        $lead = $resultado->fetch_assoc();
    
        // Decodificar el JSON para trabajar siempre con un array
        if (!empty($lead["productos_interes"])) {
    
            $lead["productos_interes"] = json_decode(
                $lead["productos_interes"],
                true
            );
    
        } else {
    
            $lead["productos_interes"] = [];
    
        }
    
        return $lead;
    
    }
    
    
    /**
     * Genera automáticamente el título del Lead.
     */
    private static function generarTitulo($prioridad)
    {
    
        switch (mb_strtolower(trim($prioridad))) {
    
            case "frio":
                return "Consulta general";
    
            case "tibio":
                return "Interés en productos";
    
            case "caliente":
                return "Pedido confirmado";
    
            default:
                return "Nuevo Lead";
    
        }
    
    }
    
    
    /**
     * Crea un nuevo Lead.
     *
     * Retorna el ID del Lead creado.
     */
    private static function crearLead(array $datos)
    {
    
        $db = Database::conectar();
    
        $titulo = self::generarTitulo($datos["prioridad"]);
    
        $productos = json_encode(
            $datos["productos_interes"] ?? [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    
        $fechaCierre = date("Y-m-d");
    
        $codigoOrden = $datos["codigo_orden"] ?? null;
    
        $orden = $datos["orden"] ?? 0;
    
        $notas = $datos["notas"] ?? null;
    
        $sql = "
            INSERT INTO crm_leads (
    
                id_cliente,
                titulo,
                prioridad,
                etapa,
                fecha_cierre,
                notas,
                resumen_ia,
                productos_interes,
                codigo_orden,
                orden,
                origen,
                fecha_ultima_interaccion
    
            )
            VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?
            )
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la creación del Lead.");
        }
    
        $telefono = preg_replace('/\D/', '', $datos["telefono"] ?? "");

        $origen = "WhatsApp";
        
        if (!empty($telefono)) {
        
            $origen .= ": " . $telefono;
        
        }

        $fechaUltimaInteraccion = date("Y-m-d H:i:s");
        
        $stmt->bind_param(
            "issssssssiss",
    
            $datos["id_cliente"],
            $titulo,
            $datos["prioridad"],
            $datos["etapa"],
            $fechaCierre,
            $notas,
            $datos["resumen_ia"],
            $productos,
            $codigoOrden,
            $orden,
            $origen,
            $fechaUltimaInteraccion,
    
        );
    
        if (!$stmt->execute()) {
            throw new Exception("No fue posible crear el Lead. Error: " . $stmt->error);
        }
        
        return $db->insert_id;
    
        return $db->insert_id;
    
    }
    
    
    /**
     * Actualiza un Lead existente.
     *
     * Retorna true si la actualización fue correcta.
     */
    private static function actualizarLead($idLead, array $datos)
    {
    
        $db = Database::conectar();
    
        $titulo = self::generarTitulo($datos["prioridad"]);
    
        $productos = json_encode(
            $datos["productos_interes"] ?? [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    
        $fechaCierre = date("Y-m-d");
    
        $codigoOrden = $datos["codigo_orden"] ?? null;
    
        $orden = $datos["orden"] ?? 0;
    
        $notas = $datos["notas"] ?? null;
    
        $sql = "
            UPDATE crm_leads
            SET
                titulo = ?,
                prioridad = ?,
                etapa = ?,
                fecha_cierre = ?,
                notas = ?,
                resumen_ia = ?,
                productos_interes = ?,
                codigo_orden = ?,
                orden = ?,
                origen = ?,
                fecha_ultima_interaccion = ?
            WHERE id = ?
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Error preparando la actualización del Lead.");
        }
    
        $telefono = preg_replace('/\D/', '', $datos["telefono"] ?? "");

        $origen = "WhatsApp";
        
        if (!empty($telefono)) {
        
            $origen .= ": " . $telefono;
        
        }

        $fechaUltimaInteraccion = date("Y-m-d H:i:s");
        
        $stmt->bind_param(
            "ssssssssissi",
    
            $titulo,
            $datos["prioridad"],
            $datos["etapa"],
            $fechaCierre,
            $notas,
            $datos["resumen_ia"],
            $productos,
            $codigoOrden,
            $orden,
            $origen,
            $fechaUltimaInteraccion,
            $idLead
    
        );
    
        if (!$stmt->execute()) {
            throw new Exception("No fue posible actualizar el Lead.");
        }
    
        return true;
    
    }
    
    
    /**
     * Procesa un Lead.
     *
     * Reglas:
     * - Si no existe un Lead, lo crea.
     * - Si el último Lead está en Facturado o Perdido, crea uno nuevo.
     * - En cualquier otra etapa, actualiza el Lead existente.
     */
    /**
     * Procesa un Lead.
     *
     * Reglas:
     * - Si el cliente no tiene Leads, crea uno.
     * - Si el último Lead está en Facturado o Perdido, crea uno nuevo.
     * - En cualquier otra etapa, actualiza el Lead existente.
     */
    public static function procesarLead(array $datos)
    {
    
        // Validaciones obligatorias
        if (empty($datos["id_cliente"])) {
            throw new Exception("id_cliente es obligatorio.");
        }
    
        if (empty($datos["etapa"])) {
            throw new Exception("La etapa es obligatoria.");
        }
    
        if (empty($datos["prioridad"])) {
            throw new Exception("La prioridad es obligatoria.");
        }
    
        // Buscar el último Lead del cliente
        $lead = self::buscarUltimoLead($datos["id_cliente"]);
    
        // El cliente nunca ha tenido Leads
        if (!$lead) {
    
            $idLead = self::crearLead($datos);
    
            return [
                "success" => true,
                "accion"  => "lead_creado",
                "id_lead" => $idLead
            ];
    
        }
    
        // Etapa del último Lead
        $etapaActual = mb_strtolower(trim($lead["etapa"]));
    
        // Si el Lead ya finalizó, crear uno nuevo
        if (
            $etapaActual === "facturado" ||
            $etapaActual === "perdido"
        ) {
    
            $idLead = self::crearLead($datos);
    
            return [
                "success" => true,
                "accion"  => "lead_creado",
                "id_lead" => $idLead
            ];
    
        }
        
        // Si no hubo cambios relevantes, no actualizar
        if (empty($datos["cambios"])) {
        
            return [
        
                "success" => true,
        
                "accion" => "sin_cambios",
        
                "id_lead" => $lead["id"]
        
            ];
        
        }
    
        // En cualquier otra etapa se actualiza el Lead existente
        self::actualizarLead(
            $lead["id"],
            $datos
        );
    
        return [
            "success" => true,
            "accion"  => "lead_actualizado",
            "id_lead" => $lead["id"]
        ];
    
    }


}