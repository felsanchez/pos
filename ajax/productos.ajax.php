<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxProductos{

	
	/*=============================================
	GENERAR CODIGO A PARTIR DE ID CATEGORIA
	=============================================*/
	public $idCategoria;

	public function ajaxCrearCodigoProducto(){

		$item = "id_categoria";
		$valor = $this->idCategoria;
		$orden = "id";

		$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

		echo json_encode($respuesta);

	}


	/*=============================================
	EDITAR PRODUCTO
	=============================================*/

	public $idProducto;
	public $traerProductos;
	public $nombreProducto;

	public function ajaxEditarProducto(){

		if($this->traerProductos == "ok"){

			$item = null;
			$valor = null;
			$orden = "id";

			$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

			echo json_encode($respuesta);
		}
		
		else if($this->nombreProducto != ""){

			$item = "descripcion";
			$valor = $this->nombreProducto;
			$orden = "id";

			$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

			echo json_encode($respuesta);
		}

		else{
			$item = "id";
			$valor = $this->idProducto;
			$orden = "id";

			$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

			echo json_encode($respuesta);
		}
	}


	/*=============================================
	HPM VALIDAR NO REPETIR PRODUCTO
	=============================================*/

	public $validarDescripcion;
	public function ajaxValidarDescripcion(){

		$item = "descripcion";
		$valor = $this->validarDescripcion;
		$orden = "id";

		$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

		echo json_encode($respuesta);
	}

	
}

    /*=============================================
	GENERAR CODIGO A PARTIR DE ID CATEGORIA
	=============================================*/

	if(isset($_POST["idCategoria"])){

		$codigoProducto = new AjaxProductos();
		$codigoProducto -> idCategoria = $_POST["idCategoria"];
		$codigoProducto -> ajaxCrearCodigoProducto();
	}


    /*=============================================
	EDITAR PRODUCTO
	=============================================*/

	if(isset($_POST["idProducto"])){

		$editarProducto = new AjaxProductos();
		$editarProducto -> idProducto = $_POST["idProducto"];
		$editarProducto -> ajaxEditarProducto();
	}


	/*=============================================
	TRAER PRODUCTOS (dispositivos)
	=============================================*/

	if(isset($_POST["traerProductos"])){

		$traerProductos = new AjaxProductos();
		$traerProductos -> traerProductos = $_POST["traerProductos"];
		$traerProductos -> ajaxEditarProducto();
	}


	/*=============================================
	TRAER PRODUCTOS nombre(dispositivos)
	=============================================*/

	if(isset($_POST["nombreProducto"])){

		$traerProductos = new AjaxProductos();
		$traerProductos -> nombreProducto = $_POST["nombreProducto"];
		$traerProductos -> ajaxEditarProducto();
	}



/*=============================================
HPM VALIDAR NO REPETIR PRODUCTO
=============================================*/

if(isset($_POST["validarDescripcion"])){

	$valProducto = new AjaxProductos();
	$valProducto -> validarDescripcion = $_POST["validarDescripcion"];
	$valProducto -> ajaxValidarDescripcion();
}