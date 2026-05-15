<?php

require_once "../controladores/traslados.controlador.php";
require_once "../modelos/traslados.modelo.php";
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../controladores/movimientos.controlador.php";
require_once "../modelos/movimientos.modelo.php";

class AjaxTraslados
{
    public $idTraslado;

    public function ajaxObtenerDetalle()
    {
        $header = ControladorTraslados::ctrMostrarTraslados("id", $this->idTraslado);
        $items = ModeloTraslados::mdlMostrarItemsTraslado("traslados_items", $this->idTraslado);

        $respuesta = array(
            "header" => $header,
            "items" => $items
        );

        echo json_encode($respuesta);
    }

    public function ajaxCompletarTraslado()
    {
        $respuesta = ControladorTraslados::ctrCompletarTraslado($this->idTraslado);
        echo $respuesta;
    }

    public function ajaxActualizarNotas()
    {
        $tabla = "traslados";
        $item1 = "notas";
        $valor1 = $_POST["notas"];
        $item2 = "id";
        $valor2 = $this->idTraslado;
        $respuesta = ModeloTraslados::mdlActualizarTraslado($tabla, $item1, $valor1, $item2, $valor2);
        echo $respuesta;
    }
}

if (isset($_POST["idTraslado"])) {

    $traslado = new AjaxTraslados();
    $traslado->idTraslado = $_POST["idTraslado"];

    if ($_POST["tipo"] == "obtenerDetalle") {
        $traslado->ajaxObtenerDetalle();
    }

    if ($_POST["tipo"] == "completar") {
        $traslado->ajaxCompletarTraslado();
    }

    if ($_POST["tipo"] == "actualizarNotas") {
        $traslado->ajaxActualizarNotas();
    }
}
