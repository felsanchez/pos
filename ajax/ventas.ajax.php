<?php

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

class AjaxVentas
{

    /*=============================================
    EDITAR IMAGEN DE VENTA
    =============================================*/
    public $idVentaImagen;

    public function ajaxEditarImagenVenta()
    {

        if (isset($_FILES["nuevaImagenVenta"]["tmp_name"]) && !empty($_FILES["nuevaImagenVenta"]["tmp_name"])) {

            list($ancho, $alto) = getimagesize($_FILES["nuevaImagenVenta"]["tmp_name"]);

            $nuevoAncho = 500;
            $nuevoAlto = 500;

            /*=============================================
            CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA IMAGEN
            =============================================*/
            $directorio = "../vistas/img/ventas/" . $this->idVentaImagen;

            if (!file_exists($directorio)) {
                mkdir($directorio, 0755);
            }

            /*=============================================
            DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
            =============================================*/
            if ($_FILES["nuevaImagenVenta"]["type"] == "image/jpeg") {

                $aleatorio = mt_rand(100, 999);
                $ruta = "vistas/img/ventas/" . $this->idVentaImagen . "/" . $aleatorio . ".jpg";
                $origen = imagecreatefromjpeg($_FILES["nuevaImagenVenta"]["tmp_name"]);
                $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

                imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                imagejpeg($destino, "../" . $ruta);

            }

            if ($_FILES["nuevaImagenVenta"]["type"] == "image/png") {

                $aleatorio = mt_rand(100, 999);
                $ruta = "vistas/img/ventas/" . $this->idVentaImagen . "/" . $aleatorio . ".png";
                $origen = imagecreatefrompng($_FILES["nuevaImagenVenta"]["tmp_name"]);
                $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

                imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                imagepng($destino, "../" . $ruta);

            }

        } else {
            echo json_encode("error_imagen");
            return;
        }

        $datos = array(
            "id" => $this->idVentaImagen,
            "imagen" => $ruta
        );

        $respuesta = ControladorVentas::ctrEditarImagenVenta($datos);

        echo json_encode($respuesta);

    }

    /*=============================================
    ACTUALIZAR SEGUIMIENTO
    =============================================*/
    public function ajaxActualizarSeguimiento()
    {

        $datos = array(
            "id" => $_POST["idVentaSeguimiento"],
            "columna" => $_POST["columna"],
            "valor" => $_POST["valor"]
        );

        $respuesta = ControladorVentas::ctrActualizarSeguimiento($datos);

        echo json_encode($respuesta);
    }

}

/*=============================================
EDITAR IMAGEN DE VENTA
=============================================*/
if (isset($_POST["idVentaImagen"])) {
    $editarImagen = new AjaxVentas();
    $editarImagen->idVentaImagen = $_POST["idVentaImagen"];
    $editarImagen->ajaxEditarImagenVenta();
}

/*=============================================
ACTUALIZAR SEGUIMIENTO
=============================================*/
if (isset($_POST["idVentaSeguimiento"])) {
    $seguimiento = new AjaxVentas();
    $seguimiento->ajaxActualizarSeguimiento();
}


?>