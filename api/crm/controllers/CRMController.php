<?php

require_once __DIR__ . '/../models/ClienteCRMModel.php';
require_once __DIR__ . '/../models/LeadModel.php';

class CRMController
{

    /*
    |--------------------------------------------------------------------------
    | PROCESAR CRM
    |--------------------------------------------------------------------------
    |
    | Procesamiento normal del Lead.
    |
    */
    public static function procesar(array $datos)
    {

        // 1. Buscar o crear cliente
        $cliente = ClienteCRMModel::procesarCliente(

            $datos["telefono"] ?? "",
            $datos["nombre"] ?? null,
            $datos["direccion"] ?? null

        );


        // 2. Determinar la prioridad según la etapa
        switch ($datos["etapa"] ?? "Contactado") {

            case "Cotizado":

                $prioridad = "tibio";

                break;


            case "Confirmado":

                $prioridad = "caliente";

                break;


            case "Contactado":

            default:

                $prioridad = "frio";

                break;

        }


        // 3. Preparar datos del Lead
        $leadData = [

            "id_cliente" => $cliente["id"],

            "telefono" => $datos["telefono"] ?? "",

            "owner_phone" => $datos["owner_phone"] ?? "",

            "etapa" => $datos["etapa"] ?? "Contactado",

            "prioridad" => $prioridad,

            "resumen_ia" => $datos["resumen_ia"] ?? "",

            "productos_interes" => $datos["productos_interes"] ?? [],

            "url_imagen" => $datos["imagen_url"] ?? null,

            "cambios" => $datos["cambios"] ?? []

        ];


        // 4. Procesar Lead
        $lead = LeadModel::procesarLead(
            $leadData
        );


        // 5. Respuesta
        return [

            "success" => true,

            "cliente" => [

                "id" => $cliente["id"],

                "nuevo" => $cliente["nuevo"]

            ],

            "lead" => $lead

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR IMAGEN INMEDIATAMENTE
    |--------------------------------------------------------------------------
    |
    | Este método se utilizará cuando llegue una imagen
    | desde WhatsApp.
    |
    | La imagen se guardará directamente en:
    |
    | crm_lead_imagenes
    |
    | sin esperar al CRM Scheduler.
    |
    */
    public static function guardarImagen(array $datos)
    {

        // Validar teléfono
        if (empty($datos["telefono"])) {

            throw new Exception(
                "El teléfono es obligatorio para guardar la imagen."
            );

        }


        // Validar URL de imagen
        if (empty($datos["url_imagen"])) {

            throw new Exception(
                "La URL de la imagen es obligatoria."
            );

        }


        /*
         * Guardar la imagen.
         *
         * LeadModel se encargará de:
         *
         * 1. Buscar el cliente por teléfono.
         * 2. Buscar su último Lead.
         * 3. Obtener el id_lead.
         * 4. Insertar la imagen en crm_lead_imagenes.
         */
        $resultado = LeadModel::guardarImagenPorTelefono(

            $datos["telefono"],

            $datos["url_imagen"],

            $datos["owner_phone"] ?? ""

        );


        return [

            "success" => true,

            "accion" => "imagen_guardada",

            "lead" => $resultado

        ];

    }

}