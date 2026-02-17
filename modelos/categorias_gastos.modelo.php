<?php

require_once "conexion.php";

class ModeloCategoriasGastos {

	/*=============================================
	MOSTRAR CATEGORÍAS DE GASTOS
	=============================================*/

	static public function mdlMostrarCategoriasGastos($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE activo = 1 ORDER BY nombre ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	CREAR CATEGORÍA DE GASTO
	=============================================*/

	static public function mdlIngresarCategoriaGasto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, color, descripcion) VALUES (:nombre, :color, :descripcion)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR CATEGORÍA DE GASTO
	=============================================*/

	static public function mdlEditarCategoriaGasto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, color = :color, descripcion = :descripcion WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR CATEGORÍA DE GASTO (SOFT DELETE)
	=============================================*/

	static public function mdlEliminarCategoriaGasto($tabla, $datos){

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
	CONTAR GASTOS POR CATEGORÍA
	=============================================*/

	static public function mdlContarGastosPorCategoria($idCategoria){

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM gastos WHERE id_categoria_gasto = :id");

		$stmt -> bindParam(":id", $idCategoria, PDO::PARAM_INT);

		$stmt -> execute();

		$resultado = $stmt -> fetch();

		return $resultado["total"];

		$stmt -> close();

		$stmt = null;

	}

}