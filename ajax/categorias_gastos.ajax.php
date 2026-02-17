<?php

require_once "../controladores/categorias_gastos.controlador.php";
require_once "../modelos/categorias_gastos.modelo.php";

class AjaxCategoriasGastos{

	/*=============================================
	EDITAR CATEGORÍA DE GASTO
	=============================================*/

	public $idCategoria;

	public function ajaxEditarCategoriaGasto(){

		$item = "id";
		$valor = $this->idCategoria;

		$respuesta = ControladorCategoriasGastos::ctrMostrarCategoriasGastos($item, $valor); 

		echo json_encode($respuesta);
	}

	/*=============================================
	OBTENER TODAS LAS CATEGORÍAS
	=============================================*/ 

	public function ajaxObtenerCategoriasGastos(){ 

		$respuesta = ControladorCategoriasGastos::ctrMostrarCategoriasGastos(null, null);

		echo json_encode($respuesta);
	}

}

/*=============================================
EDITAR CATEGORÍA DE GASTO
=============================================*/

if(isset($_POST["idCategoria"])){

	$categoria = new AjaxCategoriasGastos();
	$categoria -> idCategoria = $_POST["idCategoria"];
	$categoria -> ajaxEditarCategoriaGasto();
}

/*=============================================
OBTENER TODAS LAS CATEGORÍAS
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerCategorias"){

	$categorias = new AjaxCategoriasGastos();
	$categorias -> ajaxObtenerCategoriasGastos();
}