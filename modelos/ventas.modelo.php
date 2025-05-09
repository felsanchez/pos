<?php

require_once "conexion.php";

class ModeloVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/

	static public function mdlMostrarVentas($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}
		else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;
		
	}


	//OBTENER CODIGO DE VENTA
	static public function mdlObtenerUltimoCodigo($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT codigo FROM $tabla ORDER BY codigo DESC LIMIT 1");
	
		$stmt -> execute();
	
		$resultado = $stmt -> fetch();
	
		$stmt = null; // Correct way to close the statement
	
		if ($resultado) {
			return $resultado["codigo"];
		} else {
			return 0; // O null, dependiendo de cómo quieras manejar el inicio
		}
	
	}


	/*=============================================
	REGISTRO DE VENTAS
	=============================================*/

	static public function mdlIngresarVenta($tabla, $datos){

			$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_cliente, id_vendedor, productos, impuesto, neto, total, metodo_pago, estado) VALUES (:codigo, :id_cliente, :id_vendedor, :productos, :impuesto, :neto, :total, :metodo_pago, :estado)");

			$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
			$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
			$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_STR);
			$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
			$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
			$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
			$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
			$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
			$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

			if($stmt->execute()){

			return "ok";
			}
			else{

				return "error";
			}

			$stmt -> close();
			$stmt = null;


	}


	/*=============================================
	EDITAR VENTAS
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos){

			$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET codigo = :codigo, id_cliente = :id_cliente, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total = :total, metodo_pago = :metodo_pago, estado = :estado WHERE codigo = :codigo");

			$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
			$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
			$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_STR);
			$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
			$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
			$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
			$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
			$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
			$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

			if($stmt->execute()){

			return "ok";
			}
			else{

				return "error";
			}

			$stmt -> close();
			$stmt = null;

	}


	/*=============================================
	ELIMINAR VENTA
	=============================================*/

	static public function mdlEliminarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		}
		else{

			return "error";
		}

		$stmt -> close();
		$stmt = null;

	}


	/*=============================================
	RANGO FECHAS
	=============================================*/	
	/*
	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else if($fechaInicial == $fechaFinal){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE DATE(fecha) = :fecha");
			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetchAll();

		}else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
	}
	*/

	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){
		if ($fechaInicial == null) {
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
				$stmt->execute();
			} else {
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE DATE(fecha) BETWEEN :fechaInicial AND :fechaFinal ORDER BY id DESC");
				$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
				$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
				$stmt->execute();
			}
			return $stmt->fetchAll();
			
	}	


	/*=============================================
	SUMAR EL TOTAL DE VENTAS
	=============================================*/

	static public function mdlSumaTotalVentas($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	//Diferenciar entre venta y orden
	



	static public function mdlRangoFechasVentasPorEstado($tabla, $fechaInicial, $fechaFinal, $estado){

		if($fechaInicial == null){
	
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = :estado ORDER BY id DESC");
			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll();
	
		}else if($fechaInicial == $fechaFinal){
	
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE DATE(fecha) = :fecha AND estado = :estado ORDER BY id DESC");
			$stmt->bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);
			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll();
	
		}else{
	
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal AND estado = :estado ORDER BY id DESC");
			$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
			$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll();
		}
	
		$stmt = null;
	}
	
	
	



}