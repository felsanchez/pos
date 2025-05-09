<?php

require_once "conexion.php";

class ModeloActividades{

	/*=============================================
	CREAR ACTIVIDAD
	=============================================*/

	static public function mdlIngresarActividad($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(descripcion, tipo, id_user, fecha, estado, id_cliente, observacion) VALUES (:descripcion, :tipo, :id_user, :fecha, :estado, :id_cliente, :observacion)");

		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":id_user", $datos["id_user"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
		$stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);

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
	MOSTRAR actividades
	=============================================*/

	static public function mdlMostrarActividades($tabla, $item, $valor){

		//var_dump($item, $valor); 

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

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


	/*=============================================
	EDITAR actividades
	=============================================*/

	static public function mdlEditarActividad($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET descripcion = :descripcion, tipo = :tipo, id_user = :id_user, fecha = :fecha, estado = :estado, id_cliente = :id_cliente, observacion = :observacion WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":id_user", $datos["id_user"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
		$stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);

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
	ELIMINAR Actividad
	=============================================*/

	static public function mdlEliminarActividad($tabla, $datos){

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
	ACTUALIZAR actividad
	=============================================*/

	static public function mdlActualizarActividad($tabla, $item1, $valor1, $valor){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		}
		else{

			return "error";
		}

		$stmt -> close();
		$stmt = null;

	}


	/*=============================================
	Guardar Tipo de Actividad
	=============================================*/
	public static function mdlActualizarTipoActividad($tabla, $datos) {
		$conn = Conexion::conectar();
	
		if ($tabla !== 'actividades') {
			return ["status" => "error", "message" => "Tabla no permitida"];
		}
	
		$update = $conn->prepare("UPDATE actividades SET tipo = :tipo WHERE id = :id");
		$update->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$update->bindParam(":id", $datos["id"], PDO::PARAM_INT);
	
		if ($update->execute()) {
			$select = $conn->prepare("SELECT * FROM actividades WHERE id = :id");
			$select->bindParam(":id", $datos["id"], PDO::PARAM_INT);
			$select->execute();
			$actividad = $select->fetch(PDO::FETCH_ASSOC);
	
			return $actividad;
		} else {
			$errorInfo = $update->errorInfo();
			return ["status" => "error", "message" => $errorInfo[2]];
		}
	}
	
	

	/*=============================================
	Guardar Estado de Actividad
	=============================================*/
	/*public static function mdlActualizarEstadoActividad($tabla, $datos) {
		$conn = Conexion::conectar();
		
		// Validar la tabla
		if ($tabla !== 'actividades') {
			return ["status" => "error", "message" => "Tabla no permitida"];
		}
	
		// Preparar la consulta
		$update = $conn->prepare("UPDATE actividades SET estado = :estado WHERE id = :id");
		$update->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$update->bindParam(":id", $datos["id"], PDO::PARAM_INT);
	
		// Ejecutar la consulta
		if ($update->execute()) {
			// Si la actualización es exitosa, retornar un mensaje de éxito
			return ["status" => "ok", "id" => $datos["id"], "nuevoEstado" => $datos["estado"]];
		} else {
			// Si hubo un error, retornar el mensaje de error
			$errorInfo = $update->errorInfo();
			return ["status" => "error", "message" => $errorInfo[2]];
		}
	}
	*/
	

	/*=============================================
	ACTUALIZAR Estado
	=============================================*/
	static public function mdlActualizarEstadoActividad($tabla, $datos) {
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET estado = :estado WHERE id = :id");
	  
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
	  
		if($stmt->execute()) {
		  return "ok";
		} else {
		  return "error"; // o usa: return $stmt->errorInfo();
		}
	  
		//$stmt->close();
		$stmt = null;
	  }

	
	  	  /*=============================================
		ACTUALIZAR Observacion
		=============================================*/
		static public function mdlActualizarObservacion($tabla, $id, $observacion) {
			$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET observacion = :observacion WHERE id = :id");
			$stmt->bindParam(":observacion", $observacion, PDO::PARAM_STR);
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		
			if ($stmt->execute()) {
				return "ok";
			} else {
				return "error";
			}
		
			$stmt = null;
		}
			
	
	
	

}