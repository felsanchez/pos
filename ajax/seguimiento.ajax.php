<?php

require_once "../controladores/seguimiento.controlador.php";
require_once "../modelos/seguimiento.modelo.php";

class AjaxSeguimiento
{

    /*=============================================
    ELIMINAR SEGUIMIENTOS MASIVO
    =============================================*/

    public $idsEliminar;

    public function ajaxEliminarSeguimientos()
    {

        $tabla = "seguimiento_leads";
        $ids = $this->idsEliminar;

        $respuesta = ModeloSeguimiento::mdlEliminarSeguimientosMasivo($tabla, $ids);

        echo $respuesta;

    }

}

/*=============================================
ELIMINAR SEGUIMIENTOS MASIVO
=============================================*/
if (isset($_POST["idsEliminar"])) {

    $eliminar = new AjaxSeguimiento();
    $eliminar->idsEliminar = $_POST["idsEliminar"];
    $eliminar->ajaxEliminarSeguimientos();

}
