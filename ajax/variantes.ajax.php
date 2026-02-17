<?php

require_once "../controladores/variantes.controlador.php";
require_once "../modelos/variantes.modelo.php";

class AjaxVariantes{

	/*=============================================
	CARGAR OPCIONES DE UN TIPO DE VARIANTE
	=============================================*/

	public $idTipoVariante;

	public function ajaxCargarOpciones(){

		$item = "id_tipo_variante";
		$valor = $this->idTipoVariante;

		$respuesta = ControladorVariantes::ctrMostrarOpcionesVariantes($item, $valor);

		echo json_encode($respuesta);

	}

	/*=============================================
	EDITAR TIPO DE VARIANTE
	=============================================*/

	public $idTipo;

	public function ajaxEditarTipo(){

		$item = "id";
		$valor = $this->idTipo;

		$respuesta = ControladorVariantes::ctrMostrarTiposVariantes($item, $valor);

		echo json_encode($respuesta);

	}


    /*=============================================
    EDITAR OPCIÓN DE VARIANTE
    =============================================*/

    public $idOpcionEditar;

    public function ajaxEditarOpcion(){

        $tabla = "opciones_variantes";
        $item = "id";
        $valor = $this->idOpcionEditar;

        $respuesta = ControladorVariantes::ctrMostrarOpcionesVariantes($item, $valor);

        echo json_encode($respuesta[0]);

    }

}

/*=============================================
CARGAR OPCIONES
=============================================*/

if(isset($_POST["idTipoVariante"])){

	$opciones = new AjaxVariantes();
	$opciones -> idTipoVariante = $_POST["idTipoVariante"];
	$opciones -> ajaxCargarOpciones();

}

/*=============================================
EDITAR TIPO
=============================================*/

if(isset($_POST["idTipo"])){

	$editarTipo = new AjaxVariantes();
	$editarTipo -> idTipo = $_POST["idTipo"];
	$editarTipo -> ajaxEditarTipo();

}

/*=============================================
ACTIVAR/DESACTIVAR TIPO
=============================================*/

if(isset($_POST["activarTipo"])){

	$tabla = "tipos_variantes";

	$item1 = "estado";
	$valor1 = $_POST["estadoTipo"];

	$item2 = "id";
	$valor2 = $_POST["activarTipo"];

	$respuesta = ModeloVariantes::mdlActualizarTipoVariante($tabla, $item1, $valor1, $item2, $valor2);

	echo $respuesta;

}

/*=============================================
ACTIVAR/DESACTIVAR OPCIÓN
=============================================*/

if(isset($_POST["activarOpcion"])){

	$tabla = "opciones_variantes";

	$item1 = "estado";
	$valor1 = $_POST["estadoOpcion"];

	$item2 = "id";
	$valor2 = $_POST["activarOpcion"];

	$respuesta = ModeloVariantes::mdlActualizarOpcionVariante($tabla, $item1, $valor1, $item2, $valor2);

	echo $respuesta;

}


/*=============================================
EDITAR OPCIÓN
=============================================*/

if(isset($_POST["idOpcionEditar"])){

	$editarOpcion = new AjaxVariantes();
	$editarOpcion -> idOpcionEditar = $_POST["idOpcionEditar"];
	$editarOpcion -> ajaxEditarOpcion();

}


/*=============================================
OBTENER SIGUIENTE ORDEN DISPONIBLE PARA TIPO
=============================================*/

if(isset($_POST["obtenerSiguienteOrdenTipo"])){

	$stmt = Conexion::conectar()->prepare("SELECT MAX(orden) as max_orden FROM tipos_variantes");
	$stmt -> execute();
	$resultado = $stmt -> fetch();
	
	$siguienteOrden = ($resultado["max_orden"] != null) ? $resultado["max_orden"] + 1 : 1;
	
	echo json_encode($siguienteOrden);

}

/*=============================================
OBTENER SIGUIENTE ORDEN DISPONIBLE PARA OPCIÓN
=============================================*/

if(isset($_POST["obtenerSiguienteOrdenOpcion"])){

	$idTipo = $_POST["obtenerSiguienteOrdenOpcion"];
	
	$stmt = Conexion::conectar()->prepare("SELECT MAX(orden) as max_orden FROM opciones_variantes WHERE id_tipo_variante = :id_tipo");
	$stmt -> bindParam(":id_tipo", $idTipo, PDO::PARAM_INT);
	$stmt -> execute();
	$resultado = $stmt -> fetch();
	
	$siguienteOrden = ($resultado["max_orden"] != null) ? $resultado["max_orden"] + 1 : 1;
	
	echo json_encode($siguienteOrden);

}

/*=============================================
VALIDAR SI ORDEN YA EXISTE EN TIPOS
=============================================*/

if(isset($_POST["validarOrdenTipo"])){

	$orden = $_POST["validarOrdenTipo"];
	$idActual = $_POST["idTipoActual"];
	
	$stmt = Conexion::conectar()->prepare("SELECT id, nombre FROM tipos_variantes WHERE orden = :orden AND id != :id");
	$stmt -> bindParam(":orden", $orden, PDO::PARAM_INT);
	$stmt -> bindParam(":id", $idActual, PDO::PARAM_INT);
	$stmt -> execute();
	
	$resultado = $stmt -> fetch();
	
	if($resultado){
		echo json_encode(array("existe" => true, "nombre" => $resultado["nombre"], "id" => $resultado["id"]));
	} else {
		echo json_encode(array("existe" => false));
	}

}

/*=============================================
VALIDAR SI ORDEN YA EXISTE EN OPCIONES
=============================================*/

if(isset($_POST["validarOrdenOpcion"])){

	$orden = $_POST["validarOrdenOpcion"];
	$idActual = $_POST["idOpcionActual"];
	$idTipo = $_POST["idTipoVariante"];
	
	$stmt = Conexion::conectar()->prepare("SELECT id, nombre FROM opciones_variantes WHERE orden = :orden AND id != :id AND id_tipo_variante = :id_tipo");
	$stmt -> bindParam(":orden", $orden, PDO::PARAM_INT);
	$stmt -> bindParam(":id", $idActual, PDO::PARAM_INT);
	$stmt -> bindParam(":id_tipo", $idTipo, PDO::PARAM_INT);
	$stmt -> execute();

	$resultado = $stmt -> fetch(); 

	if($resultado){
		echo json_encode(array("existe" => true, "nombre" => $resultado["nombre"], "id" => $resultado["id"]));

	} else {
		echo json_encode(array("existe" => false));
	}

 }

/*=============================================
ELIMINAR TIPO DE VARIANTE
=============================================*/

if(isset($_POST["idEliminarTipo"])){ 

	require_once "../controladores/variantes.controlador.php";
	require_once "../modelos/variantes.modelo.php";

	$idTipo = $_POST["idEliminarTipo"];

	$respuesta = ControladorVariantes::ctrEliminarTipoVariante($idTipo); 

	echo json_encode($respuesta); 

}
 

/*=============================================
ELIMINAR OPCIÓN DE VARIANTE
=============================================*/

if(isset($_POST["idEliminarOpcion"])){ 

	require_once "../controladores/variantes.controlador.php";
	require_once "../modelos/variantes.modelo.php";

 	$idOpcion = $_POST["idEliminarOpcion"]; 

	$respuesta = ControladorVariantes::ctrEliminarOpcionVariante($idOpcion);

	echo json_encode($respuesta);

}