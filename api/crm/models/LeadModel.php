<?php

require_once __DIR__ . '/../../database.php';


class LeadModel
{


    /**
     * =========================================================
     * BUSCAR ÚLTIMO LEAD DEL CLIENTE
     * =========================================================
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
                prioridad,
                resumen_ia,
                productos_interes,
                origen,
                codigo_orden,
                notas,
                fecha_ultima_interaccion,
                fecha_cierre,
                fecha_creacion,
                fecha_actualizacion,
                url_imagen
            FROM crm_leads
            WHERE id_cliente = ?
            ORDER BY id DESC
            LIMIT 1
        ";


        $stmt = $db->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "Error preparando la consulta del Lead."
            );

        }


        $stmt->bind_param(
            "i",
            $idCliente
        );


        $stmt->execute();


        $resultado = $stmt->get_result();


        if ($resultado->num_rows === 0) {

            return null;

        }


        $lead = $resultado->fetch_assoc();


        /*
         * Decodificar productos_interes
         */
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
     * =========================================================
     * GENERAR TÍTULO DEL LEAD
     * =========================================================
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
     * =========================================================
     * GUARDAR IMAGEN POR ID DEL LEAD
     * =========================================================
     *
     * Esta es la función interna.
     *
     * Recibe:
     *
     * - ID del Lead
     * - URL de la imagen
     *
     * Guarda cada imagen como un registro independiente.
     *
     * IMPORTANTE:
     *
     * Verifica primero si la misma imagen ya existe
     * para evitar duplicados.
     *
     */
    private static function guardarImagen(
        $idLead,
        $urlImagen
    )
    {

        /*
         * No guardar si no existe URL
         */
        if (empty($urlImagen)) {

            return;

        }


        $db = Database::conectar();


        /*
         * =====================================================
         * VERIFICAR SI LA IMAGEN YA EXISTE
         * =====================================================
         */

        $sqlExiste = "
            SELECT id
            FROM crm_lead_imagenes
            WHERE id_lead = ?
            AND url_imagen = ?
            LIMIT 1
        ";


        $stmtExiste = $db->prepare(
            $sqlExiste
        );


        if (!$stmtExiste) {

            throw new Exception(
                "Error preparando la verificación de la imagen."
            );

        }


        $stmtExiste->bind_param(
            "is",
            $idLead,
            $urlImagen
        );


        $stmtExiste->execute();


        $resultadoExiste =
            $stmtExiste->get_result();


        /*
         * La imagen ya existe.
         *
         * No volver a insertarla.
         */
        if ($resultadoExiste->num_rows > 0) {

            return;

        }


        /*
         * =====================================================
         * INSERTAR NUEVA IMAGEN
         * =====================================================
         */

        $sql = "
            INSERT INTO crm_lead_imagenes (
                id_lead,
                url_imagen,
                fecha_creacion
            )
            VALUES (
                ?,
                ?,
                NOW()
            )
        ";


        $stmt = $db->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "Error preparando el guardado de la imagen."
            );

        }


        $stmt->bind_param(
            "is",
            $idLead,
            $urlImagen
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "No fue posible guardar la imagen. Error: " .
                $stmt->error
            );

        }

    }



    /**
     * =========================================================
     * GUARDAR IMAGEN PENDIENTE
     * =========================================================
     *
     * Se utiliza cuando todavía no existe cliente o Lead.
     *
     * La imagen ya fue guardada físicamente en el servidor.
     * Aquí se guarda temporalmente la referencia hasta que
     * exista un Lead al cual asociarla.
     */
    private static function guardarImagenPendiente(
    $ownerPhone,
    $telefono,
    $urlImagen
)
{
    $db = Database::conectar();

    /*
     * Normalizar teléfono del propietario.
     */
    $ownerPhone = preg_replace(
        '/\D/',
        '',
        trim($ownerPhone)
    );

    /*
     * Normalizar teléfono del cliente.
     */
    $telefono = preg_replace(
        '/\D/',
        '',
        trim($telefono)
    );


        /*
         * Evitar duplicar la misma imagen pendiente.
         */
        $sqlExiste = "
            SELECT id
            FROM crm_imagenes_pendientes
            WHERE owner_phone = ?
            AND telefono = ?
            AND url_imagen = ?
            AND procesada = 0
            LIMIT 1
        ";


        $stmtExiste = $db->prepare(
            $sqlExiste
        );


        if (!$stmtExiste) {

            throw new Exception(
                "Error preparando la verificación de imagen pendiente."
            );

        }


        $stmtExiste->bind_param(
            "sss",
            $ownerPhone,
            $telefono,
            $urlImagen
        );


        $stmtExiste->execute();


        $resultadoExiste =
            $stmtExiste->get_result();


        if ($resultadoExiste->num_rows > 0) {

            $fila =
                $resultadoExiste->fetch_assoc();


            return [

                "accion" => "imagen_pendiente_ya_existia",

                "id_pendiente" => $fila["id"]

            ];

        }


        /*
         * Insertar imagen pendiente.
         */
        $sql = "
            INSERT INTO crm_imagenes_pendientes (
                owner_phone,
                telefono,
                url_imagen,
                fecha_creacion,
                procesada,
                id_lead
            )
            VALUES (
                ?,
                ?,
                ?,
                NOW(),
                0,
                NULL
            )
        ";


        $stmt = $db->prepare(
            $sql
        );


        if (!$stmt) {

            throw new Exception(
                "Error preparando el guardado de la imagen pendiente."
            );

        }


        $stmt->bind_param(
            "sss",
            $ownerPhone,
            $telefono,
            $urlImagen
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "No fue posible guardar la imagen pendiente. Error: " .
                $stmt->error
            );

        }


        return [

            "accion" => "imagen_pendiente_guardada",

            "id_pendiente" => $db->insert_id

        ];

    }



    /**
     * =========================================================
     * ASOCIAR IMÁGENES PENDIENTES AL LEAD
     * =========================================================
     *
     * Se ejecuta cuando finalmente se crea un Lead.
     */
    private static function asociarImagenesPendientes(
        $idLead,
        $ownerPhone,
        $telefono
    )
    {

        if (
            empty($idLead) ||
            empty($ownerPhone) ||
            empty($telefono)
        ) {
            return 0;
        }

        $db = Database::conectar();

        /*
        * Normalizar teléfono del propietario.
        */
        $ownerPhone = preg_replace(
            '/\D/',
            '',
            $ownerPhone
        );

        /*
        * Normalizar teléfono del cliente.
        */
        $telefono = preg_replace(
            '/\D/',
            '',
            $telefono
        );


        if (
            strlen($telefono) === 12 &&
            substr($telefono, 0, 2) === '57'
        ) {

            $telefono = substr(
                $telefono,
                2
            );

        }


        /*
         * Buscar imágenes pendientes del cliente.
         */
        $sql = "
            SELECT
                id,
                url_imagen
            FROM crm_imagenes_pendientes
            WHERE owner_phone = ?
            AND RIGHT(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(telefono, ' ', ''),
                                '(', ''
                            ),
                            ')', ''
                        ),
                        '-', ''
                    ),
                    '.', ''
                ),
                10
            ) = RIGHT(?, 10)
            AND procesada = 0
            ORDER BY fecha_creacion ASC, id ASC
        ";


        $stmt = $db->prepare(
            $sql
        );


        if (!$stmt) {

            throw new Exception(
                "Error preparando la búsqueda de imágenes pendientes."
            );

        }


        $stmt->bind_param(
            "ss",
            $ownerPhone,
            $telefono
        );


        $stmt->execute();


        $resultado =
            $stmt->get_result();


        $cantidad = 0;


        while (
            $pendiente =
            $resultado->fetch_assoc()
        ) {

            /*
             * Guardar en el historial normal.
             *
             * guardarImagen() evita duplicados.
             */
            self::guardarImagen(
                $idLead,
                $pendiente["url_imagen"]
            );


            /*
             * Marcar como procesada.
             */
            $sqlActualizar = "
                UPDATE crm_imagenes_pendientes
                SET
                    procesada = 1,
                    id_lead = ?
                WHERE id = ?
            ";


            $stmtActualizar =
                $db->prepare(
                    $sqlActualizar
                );


            if (!$stmtActualizar) {

                throw new Exception(
                    "Error preparando la actualización de imagen pendiente."
                );

            }


            $stmtActualizar->bind_param(
                "ii",
                $idLead,
                $pendiente["id"]
            );


            if (!$stmtActualizar->execute()) {

                throw new Exception(
                    "No fue posible marcar la imagen pendiente como procesada."
                );

            }


            $cantidad++;

        }


        return $cantidad;

    }



    /**
     * =========================================================
     * CREAR NUEVO LEAD
     * =========================================================
     */
    private static function crearLead(array $datos)
    {

        $db = Database::conectar();


        $titulo = self::generarTitulo(
            $datos["prioridad"]
        );


        $productos = json_encode(
            $datos["productos_interes"] ?? [],
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


        $fechaCierre = date("Y-m-d");


        $codigoOrden =
            $datos["codigo_orden"] ?? null;


        $orden =
            $datos["orden"] ?? 0;


        $notas =
            $datos["notas"] ?? null;


        /*
         * Última imagen.
         *
         * Se mantiene en crm_leads para
         * conservar compatibilidad con
         * el sistema actual.
         */
        $urlImagen =
            $datos["url_imagen"] ?? null;


        /*
         * Teléfono para identificar el origen
         */
        $telefono = preg_replace(
            '/\D/',
            '',
            $datos["telefono"] ?? ""
        );


        /*
         * Owner del tenant.
         */
        $ownerPhone = preg_replace(
            '/\D/',
            '',
            $datos["owner_phone"] ?? ""
        );


        $origen = "WhatsApp";


        if (!empty($telefono)) {

            $origen .= ": " . $telefono;

        }


        $fechaUltimaInteraccion =
            date("Y-m-d H:i:s");


        /*
         * =====================================================
         * INSERTAR LEAD
         * =====================================================
         */

        $sql = "
            INSERT INTO crm_leads (
                id_cliente,
                titulo,
                prioridad,
                etapa,
                fecha_cierre,
                notas,
                resumen_ia,
                url_imagen,
                productos_interes,
                codigo_orden,
                orden,
                origen,
                fecha_ultima_interaccion
            )
            VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?
            )
        ";


        $stmt = $db->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "Error preparando la creación del Lead."
            );

        }


        $stmt->bind_param(
            "isssssssssiss",

            $datos["id_cliente"],
            $titulo,
            $datos["prioridad"],
            $datos["etapa"],
            $fechaCierre,
            $notas,
            $datos["resumen_ia"],
            $urlImagen,
            $productos,
            $codigoOrden,
            $orden,
            $origen,
            $fechaUltimaInteraccion

        );


        if (!$stmt->execute()) {

            throw new Exception(
                "No fue posible crear el Lead. Error: " .
                $stmt->error
            );

        }


        $idLead = $db->insert_id;


        /*
         * =====================================================
         * GUARDAR IMAGEN EN HISTORIAL
         * =====================================================
         */

        if (!empty($urlImagen)) {

            self::guardarImagen(
                $idLead,
                $urlImagen
            );

        }


        /*
         * =====================================================
         * ASOCIAR IMÁGENES PENDIENTES
         * =====================================================
         */

        if (
            !empty($ownerPhone) &&
            !empty($telefono)
        ) {

            self::asociarImagenesPendientes(
                $idLead,
                $ownerPhone,
                $telefono
            );

        }


        return $idLead;

    }



    /**
     * =========================================================
     * ACTUALIZAR LEAD EXISTENTE
     * =========================================================
     */
    private static function actualizarLead(
        $idLead,
        array $datos
    )
    {

        $db = Database::conectar();


        $titulo = self::generarTitulo(
            $datos["prioridad"]
        );


        $productos = json_encode(
            $datos["productos_interes"] ?? [],
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


        $fechaCierre = date("Y-m-d");


        $codigoOrden =
            $datos["codigo_orden"] ?? null;


        $orden =
            $datos["orden"] ?? 0;


        $notas =
            $datos["notas"] ?? null;


        /*
         * Nueva imagen.
         *
         * Representa la última imagen recibida.
         * Se mantiene en crm_leads para
         * compatibilidad.
         */
        $urlImagen =
            $datos["url_imagen"] ?? null;


        /*
         * Teléfono para identificar el origen
         */
        $telefono = preg_replace(
            '/\D/',
            '',
            $datos["telefono"] ?? ""
        );


        $origen = "WhatsApp";


        if (!empty($telefono)) {

            $origen .= ": " . $telefono;

        }


        $fechaUltimaInteraccion =
            date("Y-m-d H:i:s");


        /*
         * =====================================================
         * ACTUALIZAR LEAD
         * =====================================================
         */

        $sql = "
            UPDATE crm_leads
            SET
                titulo = ?,
                prioridad = ?,
                etapa = ?,
                fecha_cierre = ?,
                notas = ?,
                resumen_ia = ?,
                url_imagen = COALESCE(
                    NULLIF(?, ''),
                    url_imagen
                ),
                productos_interes = ?,
                codigo_orden = ?,
                orden = ?,
                origen = ?,
                fecha_ultima_interaccion = ?

            WHERE id = ?
        ";


        $stmt = $db->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "Error preparando la actualización del Lead."
            );

        }


        $stmt->bind_param(
            "sssssssssissi",

            $titulo,
            $datos["prioridad"],
            $datos["etapa"],
            $fechaCierre,
            $notas,
            $datos["resumen_ia"],
            $urlImagen,
            $productos,
            $codigoOrden,
            $orden,
            $origen,
            $fechaUltimaInteraccion,
            $idLead

        );


        if (!$stmt->execute()) {

            throw new Exception(
                "No fue posible actualizar el Lead. Error: " .
                $stmt->error
            );

        }


        /*
         * =====================================================
         * GUARDAR NUEVA IMAGEN EN HISTORIAL
         * =====================================================
         *
         * Si existe una nueva imagen:
         *
         * - se mantiene la última en crm_leads
         * - se agrega al historial
         *
         * La función guardarImagen()
         * evita duplicados.
         */

        if (!empty($urlImagen)) {

            self::guardarImagen(
                $idLead,
                $urlImagen
            );

        }


        return true;

    }



    /**
     * =========================================================
     * PROCESAR LEAD
     * =========================================================
     *
     * Reglas:
     *
     * - Si no existe un Lead, crea uno.
     * - Si el último Lead está Facturado o Perdido,
     *   crea uno nuevo.
     * - En cualquier otra etapa, actualiza el Lead.
     * - Si llega una nueva imagen, la guarda.
     *
     */
    public static function procesarLead(
        array $datos
    )
    {

        /*
         * =====================================================
         * VALIDACIONES
         * =====================================================
         */

        if (empty($datos["id_cliente"])) {

            throw new Exception(
                "id_cliente es obligatorio."
            );

        }


        if (empty($datos["etapa"])) {

            throw new Exception(
                "La etapa es obligatoria."
            );

        }


        if (empty($datos["prioridad"])) {

            throw new Exception(
                "La prioridad es obligatoria."
            );

        }


        /*
         * =====================================================
         * BUSCAR ÚLTIMO LEAD
         * =====================================================
         */

        $lead = self::buscarUltimoLead(
            $datos["id_cliente"]
        );


        /*
         * =====================================================
         * CLIENTE SIN LEADS
         * =====================================================
         */

        if (!$lead) {

            $idLead = self::crearLead(
                $datos
            );


            return [

                "success" => true,

                "accion" => "lead_creado",

                "id_lead" => $idLead

            ];

        }


        /*
         * =====================================================
         * ETAPA ACTUAL
         * =====================================================
         */

        $etapaActual = mb_strtolower(
            trim($lead["etapa"])
        );


        /*
         * =====================================================
         * SI EL LEAD FINALIZÓ
         * =====================================================
         */

        if (
            $etapaActual === "facturado" ||
            $etapaActual === "perdido"
        ) {

            $idLead = self::crearLead(
                $datos
            );


            return [

                "success" => true,

                "accion" => "lead_creado",

                "id_lead" => $idLead

            ];

        }


        /*
         * =====================================================
         * DETECTAR NUEVA IMAGEN
         * =====================================================
         */

        $hayNuevaImagen = !empty(
            $datos["url_imagen"]
        );


        /*
         * =====================================================
         * SIN CAMBIOS
         * =====================================================
         *
         * Si no hubo cambios comerciales
         * y tampoco llegó una imagen,
         * no actualizar.
         */

        if (
            empty($datos["cambios"]) &&
            !$hayNuevaImagen
        ) {

            return [

                "success" => true,

                "accion" => "sin_cambios",

                "id_lead" => $lead["id"]

            ];

        }


        /*
         * =====================================================
         * ACTUALIZAR LEAD
         * =====================================================
         */

        self::actualizarLead(
            $lead["id"],
            $datos
        );


        return [

            "success" => true,

            "accion" => "lead_actualizado",

            "id_lead" => $lead["id"]

        ];

    }



    /**
     * =========================================================
     * GUARDAR IMAGEN POR TELÉFONO
     * =========================================================
     *
     * Esta es la función que utilizará n8n
     * para guardar inmediatamente una imagen.
     *
     * Recibe:
     *
     * - teléfono del cliente
     * - URL de la imagen
     *
     * Busca:
     *
     * teléfono
     *      ↓
     * cliente
     *      ↓
     * último Lead
     *      ↓
     * imagen
     *
     */
    public static function guardarImagenPorTelefono(
        $telefono,
        $urlImagen,
        $ownerPhone = ""
    )
    {

        $db = Database::conectar();
        

        /*
         * =====================================================
         * LIMPIAR TELÉFONO
         * =====================================================
         */

        $telefono = preg_replace('/\D/', '', $telefono);

        /*
         * Normalizar owner_phone.
         */
        $ownerPhone = preg_replace(
            '/\D/',
            '',
            $ownerPhone
        );

        // Si viene con código de país Colombia (57),
        // convertirlo al formato utilizado en la tabla clientes.
        if (strlen($telefono) === 12 && substr($telefono, 0, 2) === '57') {
            $telefono = substr($telefono, 2);
        }


        /*
         * =====================================================
         * VALIDACIONES
         * =====================================================
         */

        if (empty($telefono)) {

            throw new Exception(
                "El teléfono es obligatorio."
            );

        }


        if (empty($urlImagen)) {

            throw new Exception(
                "La URL de la imagen es obligatoria."
            );

        }


        /*
         * =====================================================
         * 1. BUSCAR CLIENTE
         * =====================================================
         */

        $sqlCliente = "
            SELECT id
            FROM clientes
            WHERE RIGHT(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(telefono, ' ', ''),
                                '(', ''
                            ),
                            ')', ''
                        ),
                        '-', ''
                    ),
                    '.', ''
                ),
                10
            ) = RIGHT(?, 10)
            LIMIT 1
        ";


        $stmtCliente = $db->prepare(
            $sqlCliente
        );


        if (!$stmtCliente) {

            throw new Exception(
                "Error preparando la búsqueda del cliente."
            );

        }


        $stmtCliente->bind_param(
            "s",
            $telefono
        );


        $stmtCliente->execute();


        $resultadoCliente =
            $stmtCliente->get_result();


        if ($resultadoCliente->num_rows === 0) {

            /*
             * El cliente todavía no existe.
             *
             * La imagen ya fue guardada físicamente en el servidor,
             * por lo que la dejamos pendiente de asociación.
             */

            if (empty($ownerPhone)) {

                throw new Exception(
                    "No se puede guardar la imagen pendiente porque falta owner_phone."
                );

            }


            $resultadoPendiente =
                self::guardarImagenPendiente(
                    $ownerPhone,
                    $telefono,
                    $urlImagen
                );


            return [

                "success" => true,

                "accion" => $resultadoPendiente["accion"],

                "id_lead" => null,

                "id_imagen" => null,

                "id_pendiente" =>
                    $resultadoPendiente["id_pendiente"],

                "url_imagen" => $urlImagen

            ];

        }


        $cliente =
            $resultadoCliente->fetch_assoc();


        $idCliente =
            $cliente["id"];


        /*
         * =====================================================
         * 2. BUSCAR ÚLTIMO LEAD
         * =====================================================
         */

        $lead = self::buscarUltimoLead(
            $idCliente
        );


        if (!$lead) {

            /*
             * El cliente existe, pero todavía no tiene Lead.
             *
             * La imagen queda pendiente de asociación.
             */

            if (empty($ownerPhone)) {

                throw new Exception(
                    "No se puede guardar la imagen pendiente porque falta owner_phone."
                );

            }


            $resultadoPendiente =
                self::guardarImagenPendiente(
                    $ownerPhone,
                    $telefono,
                    $urlImagen
                );


            return [

                "success" => true,

                "accion" => $resultadoPendiente["accion"],

                "id_lead" => null,

                "id_imagen" => null,

                "id_pendiente" =>
                    $resultadoPendiente["id_pendiente"],

                "url_imagen" => $urlImagen

            ];

        }


        $idLead =
            $lead["id"];


        /*
         * =====================================================
         * 3. GUARDAR IMAGEN
         * =====================================================
         *
         * guardarImagen() ya verifica si la URL
         * existe para ese Lead.
         */

        self::guardarImagen(
            $idLead,
            $urlImagen
        );


        /*
         * =====================================================
         * 4. ACTUALIZAR ÚLTIMA IMAGEN EN crm_leads
         * =====================================================
         *
         * Esto permite mantener la compatibilidad
         * con el sistema actual.
         */

        $sqlActualizar = "
            UPDATE crm_leads
            SET
                url_imagen = ?,
                fecha_ultima_interaccion = NOW()

            WHERE id = ?
        ";


        $stmtActualizar =
            $db->prepare(
                $sqlActualizar
            );


        if (!$stmtActualizar) {

            throw new Exception(
                "Error preparando la actualización de la imagen."
            );

        }


        $stmtActualizar->bind_param(
            "si",
            $urlImagen,
            $idLead
        );


        if (!$stmtActualizar->execute()) {

            throw new Exception(
                "No fue posible actualizar la imagen del Lead. Error: " .
                $stmtActualizar->error
            );

        }


        /*
         * =====================================================
         * RESPUESTA
         * =====================================================
         */

        return [

            "success" => true,

            "id_lead" => $idLead,

            "url_imagen" => $urlImagen,

            "accion" => "imagen_guardada"

        ];

    }


}