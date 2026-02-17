<?php

class ControladorSeguimiento
{

    /*=============================================
    MOSTRAR SEGUIMIENTOS
    =============================================*/

    static public function ctrMostrarSeguimientos($item, $valor)
    {

        $tabla = "seguimiento_leads";

        $respuesta = ModeloSeguimiento::mdlMostrarSeguimientos($tabla, $item, $valor);

        return $respuesta;

    }

}
