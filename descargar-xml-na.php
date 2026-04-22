<?php

require_once "controladores/factus.controlador.php";
require_once "modelos/factus.modelo.php";

if (isset($_GET["id"])) {

    $idNota = $_GET["id"];

    // Buscar la nota por su ID interno
    $nota = ControladorFactus::ctrMostrarNotasAjusteDS("id", $idNota);

    if ($nota) {
        
        // Verificar si es un borrador
        if ($nota["estado_dian"] == "borrador" || empty($nota["numero_nota_ajuste"])) {
            echo "La nota es un borrador y aún no ha sido procesada por la DIAN. No hay XML disponible.";
            exit;
        }

        if (!empty($nota["xml_dian"])) {
            
            $urlXml = $nota["xml_dian"];
            $nombreArchivo = $nota["numero_nota_ajuste"] . ".xml";
            
            // Intentar obtener el contenido del XML desde la URL externa
            $contenido = @file_get_contents($urlXml);

            if ($contenido !== false) {
                // Forzar descarga del archivo
                header('Content-Description: File Transfer');
                header('Content-Type: text/xml');
                header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($contenido));
                echo $contenido;
                exit;
            } else {
                // Si no se puede leer (ej. allow_url_fopen desactivado), redirigir directamente
                header("Location: " . $urlXml);
                exit;
            }

        } else {
            echo "El enlace oficial del XML está vacío en la base de datos. Es posible que el mensaje de la DIAN indique el motivo.";
        }

    } else {
        echo "No se encontró el registro de la nota de ajuste con ID: " . htmlspecialchars($idNota);
    }

} else {
    echo "Falta el parámetro ID para la descarga.";
}
