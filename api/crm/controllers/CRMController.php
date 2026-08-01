<?php

require_once __DIR__ . '/../models/ClienteCRMModel.php';
require_once __DIR__ . '/../models/LeadModel.php';

class CRMController
{

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

            "etapa" => $datos["etapa"] ?? "Contactado",

            "prioridad" => $prioridad,

            "resumen_ia" => $datos["resumen_ia"] ?? "",

            "productos_interes" => $datos["productos_interes"] ?? [],

            "cambios" => $datos["cambios"] ?? []

        ];

        // 4. Procesar Lead
        $lead = LeadModel::procesarLead($leadData);

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

}