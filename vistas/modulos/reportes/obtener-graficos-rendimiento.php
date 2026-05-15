<?php
require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";
require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";
require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";
require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";
require_once "../../../controladores/proveedores.controlador.php";
require_once "../../../modelos/proveedores.modelo.php";
require_once "../../../modelos/conexion.php";

// Capturar parámetros
$idBodega = isset($_POST["idBodega"]) ? $_POST["idBodega"] : "todos";
$fechaInicial = isset($_POST["fechaInicial"]) ? $_POST["fechaInicial"] : null;
$fechaFinal = isset($_POST["fechaFinal"]) ? $_POST["fechaFinal"] : null;

// Estos archivos ahora leen directamente de $_POST o de las variables definidas arriba
?>
<div class="row">
    <div class="col-md-6 col-xs-12">
        <?php include "metodos-pago-mas-usados.php"; ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <?php include "productos-mas-vendidos.php"; ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <?php include "vendedores.php"; ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <?php include "compradores.php"; ?>
    </div>
</div>
