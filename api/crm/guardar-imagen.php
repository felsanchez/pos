<?php

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| API KEY
|--------------------------------------------------------------------------
*/

define('CRM_API_KEY', 'kontrolpos_2026');


/*
|--------------------------------------------------------------------------
| CARGAR ARCHIVOS NECESARIOS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/models/LeadModel.php';


/*
|--------------------------------------------------------------------------
| RESPUESTA DE ERROR
|--------------------------------------------------------------------------
*/

function responderError($mensaje, $codigo = 400)
{

    http_response_code($codigo);

    echo json_encode(
        [
            "success" => false,
            "error" => $mensaje
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDAR API KEY
|--------------------------------------------------------------------------
*/

$apiKey = $_SERVER["HTTP_X_API_KEY"] ?? "";

if ($apiKey !== CRM_API_KEY) {

    responderError(
        "API Key inválida.",
        401
    );

}


/*
|--------------------------------------------------------------------------
| VALIDAR OWNER PHONE
|--------------------------------------------------------------------------
|
| El owner_phone corresponde al dueño del negocio,
| NO al cliente que envió la imagen.
|
*/

$ownerPhone = $_POST["owner_phone"] ?? "";

$ownerPhone = preg_replace(
    '/\D/',
    '',
    trim($ownerPhone)
);


if (empty($ownerPhone)) {

    responderError(
        "El owner_phone es obligatorio."
    );

}


/*
|--------------------------------------------------------------------------
| SELECCIONAR TENANT
|--------------------------------------------------------------------------
*/

Database::setOwnerPhone(
    $ownerPhone
);


/*
|--------------------------------------------------------------------------
| VALIDAR TELÉFONO DEL CLIENTE
|--------------------------------------------------------------------------
*/

$telefono = $_POST["telefono"] ?? "";

$telefono = preg_replace(
    '/\D/',
    '',
    trim($telefono)
);


if (empty($telefono)) {

    responderError(
        "El teléfono del cliente es obligatorio."
    );

}


/*
|--------------------------------------------------------------------------
| VALIDAR ARCHIVO
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES["imagen"]) ||
    $_FILES["imagen"]["error"] !== UPLOAD_ERR_OK
) {

    responderError(
        "No se recibió correctamente la imagen."
    );

}


$archivo = $_FILES["imagen"];


/*
|--------------------------------------------------------------------------
| VALIDAR TAMAÑO
|--------------------------------------------------------------------------
|
| Máximo: 10 MB
|
*/

$maximoBytes = 10 * 1024 * 1024;


if ($archivo["size"] > $maximoBytes) {

    responderError(
        "La imagen supera el tamaño máximo permitido de 10 MB."
    );

}


/*
|--------------------------------------------------------------------------
| DETECTAR TIPO MIME REAL
|--------------------------------------------------------------------------
|
| No confiamos únicamente en la extensión enviada
| por el cliente.
|
*/

$finfo = new finfo(
    FILEINFO_MIME_TYPE
);

$mimeType = $finfo->file(
    $archivo["tmp_name"]
);


/*
|--------------------------------------------------------------------------
| TIPOS PERMITIDOS
|--------------------------------------------------------------------------
*/

$tiposPermitidos = [

    "image/jpeg" => "jpg",

    "image/png" => "png",

    "image/webp" => "webp",

    "image/gif" => "gif"

];


if (!isset($tiposPermitidos[$mimeType])) {

    responderError(
        "El archivo recibido no es un formato de imagen permitido."
    );

}


$extension = $tiposPermitidos[$mimeType];


/*
|--------------------------------------------------------------------------
| CARPETA DE DESTINO
|--------------------------------------------------------------------------
*/

$carpetaImagenes =
    __DIR__ .
    '/../../uploads/crm/imagenes/';


/*
|--------------------------------------------------------------------------
| CREAR CARPETA SI NO EXISTE
|--------------------------------------------------------------------------
*/

if (!is_dir($carpetaImagenes)) {

    if (
        !mkdir(
            $carpetaImagenes,
            0755,
            true
        )
    ) {

        responderError(
            "No fue posible crear la carpeta de imágenes.",
            500
        );

    }

}


/*
|--------------------------------------------------------------------------
| GENERAR NOMBRE ÚNICO
|--------------------------------------------------------------------------
*/

$nombreArchivo =
    'lead_' .
    date('Ymd_His') .
    '_' .
    bin2hex(
        random_bytes(8)
    ) .
    '.' .
    $extension;


/*
|--------------------------------------------------------------------------
| RUTA FINAL
|--------------------------------------------------------------------------
*/

$rutaArchivo =
    $carpetaImagenes .
    $nombreArchivo;


/*
|--------------------------------------------------------------------------
| MOVER ARCHIVO
|--------------------------------------------------------------------------
*/

if (
    !move_uploaded_file(
        $archivo["tmp_name"],
        $rutaArchivo
    )
) {

    responderError(
        "No fue posible guardar físicamente la imagen en el servidor.",
        500
    );

}


/*
|--------------------------------------------------------------------------
| URL PÚBLICA
|--------------------------------------------------------------------------
*/

$urlImagen =
    'https://kontrolpos.com/gestion/uploads/crm/imagenes/' .
    $nombreArchivo;


/*
|--------------------------------------------------------------------------
| GUARDAR REGISTRO EN CRM
|--------------------------------------------------------------------------
|
| LeadModel se encargará de:
|
| 1. Buscar el cliente por teléfono.
| 2. Buscar su último Lead.
| 3. Obtener el id_lead.
| 4. Guardar la URL propia de Kontrol POS.
|
*/

try {

    $resultado =
        LeadModel::guardarImagenPorTelefono(
            $telefono,
            $urlImagen
        );


    /*
     * Si todo salió correctamente,
     * devolvemos la información.
     */

    echo json_encode(

        [

            "success" => true,

            "accion" => "imagen_guardada",

            "archivo" => $nombreArchivo,

            "url_imagen" => $urlImagen,

            "lead" => $resultado

        ],

        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES

    );


} catch (Exception $e) {


    /*
     * Si MySQL falla después de guardar
     * el archivo, eliminamos el archivo
     * para evitar archivos huérfanos.
     */

    if (file_exists($rutaArchivo)) {

        unlink($rutaArchivo);

    }


    responderError(
        $e->getMessage()
    );

}