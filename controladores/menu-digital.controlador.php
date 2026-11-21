<?php

require_once __DIR__ . "/../modelos/menu-digital.modelo.php";

class ControladorMenuDigital
{
    /*=============================================
    MOSTRAR PRODUCTOS PARA EL MENÚ
    =============================================*/
    static public function ctrMostrarProductosMenu($soloActivos = false)
    {
        return ModeloMenuDigital::mdlMostrarProductosMenu($soloActivos);
    }

    /*=============================================
    ACTUALIZAR ESTADO EN MENÚ DIGITAL
    =============================================*/
    static public function ctrActualizarEstadoMenu($idProducto, $estado)
    {
        return ModeloMenuDigital::mdlActualizarEstadoMenu($idProducto, $estado);
    }

    /*=============================================
    ACTUALIZAR CARACTERÍSTICAS
    =============================================*/
    static public function ctrActualizarCaracteristicas($idProducto, $caracteristicas)
    {
        return ModeloMenuDigital::mdlActualizarCaracteristicas($idProducto, $caracteristicas);
    }

    /*=============================================
    PROCESAR PEDIDO DEL MENÚ DIGITAL
    =============================================*/
    static public function ctrProcesarPedido($datos)
    {
        return ModeloMenuDigital::mdlCrearOrdenMenuDigital($datos);
    }
}
