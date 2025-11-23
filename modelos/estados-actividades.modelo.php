<?php

require_once "conexion.php";

class ModeloEstadosActividades{

	/*============================================
	MOSTRAR ESTADOS DE ACTIVIDADES
	=============================================*/

	static public function mdlMostrarEstadosActividades($tabla, $item, $valor){

		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY orden ASC");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetch();

		} else {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE activo = 1 ORDER BY orden ASC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}

		$stmt -> close();
		$stmt = null;
	}


	/*=============================================
	CREAR ESTADO
	=============================================*/

	static public function mdlCrearEstado($tabla, $datos){

        // Verificar si el nombre ya existe
		$stmtNombre = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE nombre = :nombre AND activo = 1");
		$stmtNombre -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmtNombre -> execute();
		$nombreExiste = $stmtNombre -> fetch();

		if($nombreExiste){
			return "duplicado";
		}

		// Verificar si el orden ya existe
		$stmtCheck = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden");
		$stmtCheck -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmtCheck -> execute();
		$existe = $stmtCheck -> fetch();

		// Si existe, mover todos los órdenes mayores o iguales
		if($existe){
			$stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden");
			$stmtUpdate -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
			$stmtUpdate -> execute();
		}

		// Insertar el nuevo registro
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, color, orden) VALUES (:nombre, :color, :orden)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);

		if($stmt->execute()){
			return "ok";
		}else{
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	EDITAR ESTADO
	=============================================*/

	static public function mdlEditarEstado($tabla, $datos){

        // PRIMERO: Obtener el nombre actual del estado antes de editarlo
		$stmtActual = Conexion::conectar()->prepare("SELECT nombre FROM $tabla WHERE id = :id");
		$stmtActual -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmtActual -> execute();
		$estadoActual = $stmtActual -> fetch();
		$nombreAntiguo = $estadoActual["nombre"];

        // Verificar si el nombre ya existe en otro registro
		$stmtNombre = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE nombre = :nombre AND id != :id AND activo = 1");
		$stmtNombre -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmtNombre -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmtNombre -> execute();
		$nombreExiste = $stmtNombre -> fetch();
		if($nombreExiste){
			return "duplicado";
		}

		// Verificar si el orden ya existe en otro registro
		$stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden AND id != :id");
		$stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt -> execute();
		$existe = $stmt -> fetch();

		// Si existe, ajustar el orden de los demás
		if($existe){
			$stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden AND id != :id");
			$stmtUpdate -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
			$stmtUpdate -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
			$stmtUpdate -> execute();
		}

		// Actualizar el registro del estado
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, color = :color, orden = :orden WHERE id = :id");

		$stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt -> bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if($stmt -> execute()){

			// IMPORTANTE: Si el nombre cambió, actualizar todas las actividades que usan ese estado
			if($nombreAntiguo != $datos["nombre"]){
				$stmtActividades = Conexion::conectar()->prepare("UPDATE actividades SET estado = :nombreNuevo WHERE LOWER(estado) = LOWER(:nombreAntiguo)");
				$stmtActividades -> bindParam(":nombreNuevo", $datos["nombre"], PDO::PARAM_STR);
				$stmtActividades -> bindParam(":nombreAntiguo", $nombreAntiguo, PDO::PARAM_STR);
				$stmtActividades -> execute();
			}

			return "ok";
		}else{
			return "error";
		}

		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	ELIMINAR ESTADO
	=============================================*/

	static public function mdlEliminarEstado($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 0 WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){
			return "ok";
		}else{
			return "error";
		}

		$stmt -> close();
		$stmt = null;
	}


    /*=============================================

	VERIFICAR SI ESTADO ESTÁ EN USO

	=============================================*/

    
	static public function mdlVerificarEstadoEnUso($nombreEstado){ 

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM actividades WHERE LOWER(estado) = LOWER(:estado)");
		$stmt -> bindParam(":estado", $nombreEstado, PDO::PARAM_STR);
		$stmt -> execute();
		$resultado = $stmt -> fetch(); 

		return $resultado["total"]; 

		$stmt -> close();
		$stmt = null;
	}



}