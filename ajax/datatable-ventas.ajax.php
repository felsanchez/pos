<?php

// Desactivar display de errores para no corromper JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class tablaProductos{

	/*=============================================
	MOSTRAR LA TABLA DE PRODUCTOS
	=============================================*/

	public function mostrarTabla(){

		$item = null;
		$valor = null;
		$orden = "id";

		$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

		$data = array(); 

		for($i = 0; $i < count($productos); $i++){ 

			// Verificar si el producto tiene variantes
			$tieneVariantes = ModeloProductos::mdlContarVariantesProducto($productos[$i]["id"]); 

			$data[] = array(

				($i+1),
				$productos[$i]["imagen"],
				$productos[$i]["codigo"],
				$productos[$i]["descripcion"],
				$productos[$i]["stock"],
				$productos[$i]["id"],
				$tieneVariantes > 0 ? "1" : "0" // Nueva columna: tiene variantes
			);

		}
		echo json_encode(array("data" => $data));
    exit; // Terminar ejecución aquí

	}


	/*=============================================
    EDITAR IMAGEN DE VENTA
    =============================================*/
    public $idVentaImagen;
    public $nuevaImagenVenta;

    public function ajaxEditarImagenVenta(){

        if(isset($_FILES["nuevaImagenVenta"]["tmp_name"]) && !empty($_FILES["nuevaImagenVenta"]["tmp_name"])){

            list($ancho, $alto) = getimagesize($_FILES["nuevaImagenVenta"]["tmp_name"]);

            $nuevoAncho = 500;
            $nuevoAlto = 500;

            /*=============================================
            CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA IMAGEN
            =============================================*/
            $directorio = "../vistas/img/ventas/".$this->idVentaImagen;

            if(!file_exists($directorio)){
                mkdir($directorio, 0755);
            }

            /*=============================================
            DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
            =============================================*/
            if($_FILES["nuevaImagenVenta"]["type"] == "image/jpeg"){

                $aleatorio = mt_rand(100,999);
                $ruta = $directorio."/".$aleatorio.".jpg";
                $origen = imagecreatefromjpeg($_FILES["nuevaImagenVenta"]["tmp_name"]);
                $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

                imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                imagejpeg($destino, $ruta);

            }

            if($_FILES["nuevaImagenVenta"]["type"] == "image/png"){

                $aleatorio = mt_rand(100,999);
                $ruta = $directorio."/".$aleatorio.".png";
                $origen = imagecreatefrompng($_FILES["nuevaImagenVenta"]["tmp_name"]);
                $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

                imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                imagepng($destino, $ruta);

            }

			}else{
				$ruta = "";
			}

			$datos = array("id" => $this->idVentaImagen,
						"imagen" => $ruta);

			$respuesta = ControladorVentas::ctrEditarImagenVenta($datos);

			echo json_encode($respuesta);

    }



}


//Guardar Notas
if (isset($_POST["idVentaNota"])) {
  require_once "../controladores/ventas.controlador.php";
  require_once "../modelos/ventas.modelo.php";

  $datos = [
    "id" => $_POST["idVentaNota"],
    "notas" => $_POST["nuevaNota"]
  ];

  $respuesta = ControladorVentas::ctrActualizarNotaVenta($datos);
  echo json_encode($respuesta);
  exit;
}

//Guardar Observaciones
if (isset($_POST["idVentaObservacion"])) {
  require_once "../controladores/ventas.controlador.php";
  require_once "../modelos/ventas.modelo.php";

  $datos = [
    "id" => $_POST["idVentaObservacion"],
    "observacion" => $_POST["nuevaObservacion"]
  ];

  $respuesta = ControladorVentas::ctrActualizarObservacionVenta($datos);
  echo json_encode($respuesta);
  exit;
}


/*=============================================
EDITAR IMAGEN DE VENTA
=============================================*/
if(isset($_POST["idVentaImagen"])){
    require_once "../controladores/ventas.controlador.php";
    require_once "../modelos/ventas.modelo.php";

    $editarImagen = new TablaProductos();
    $editarImagen -> idVentaImagen = $_POST["idVentaImagen"];
    $editarImagen -> nuevaImagenVenta = $_FILES["nuevaImagenVenta"];
    $editarImagen -> ajaxEditarImagenVenta();
    exit;
}


/*=============================================
	ACTIVAR TABLA DE PRODUCTOS
=============================================*/

$activar = new TablaProductos();
$activar -> mostrarTabla();