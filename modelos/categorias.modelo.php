<?php

require_once "conexion.php";

class ModeloCategorias{

	/*=============================================
	CREAR CATEGORIA
	=============================================*/

	static public function mdlIngresarCategoria($tabla, $datos){

		if (is_array($datos)) {
			$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(categoria, prefijo) VALUES (:categoria, :prefijo)");
			$stmt -> bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
			$stmt -> bindParam(":prefijo", $datos["prefijo"], PDO::PARAM_STR);
		} else {
			$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(categoria) VALUES (:categoria)");
			$stmt -> bindParam(":categoria", $datos, PDO::PARAM_STR);
		}

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
	MOSTRAR CATEGORIAS
	=============================================*/

	static public function mdlMostrarCategorias($tabla, $item, $valor){

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
	MOSTRAR CATEGORIAS SERVER-SIDE
	=============================================*/
	static public function mdlMostrarCategoriasServerSide($tabla, $where, $order, $limit)
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla $where $order $limit");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/*=============================================
	OBTENER TOTAL CATEGORIAS (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalCategorias($tabla, $where)
	{
		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM $tabla $where");
		$stmt->execute();
		return $stmt->fetchColumn();
	}


	/*=============================================
	EDITAR CATEGORIA
	=============================================*/

	static public function mdlEditarCategoria($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET categoria = :categoria, prefijo = :prefijo WHERE id = :id");
			
		$stmt -> bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
		$stmt -> bindParam(":prefijo", $datos["prefijo"], PDO::PARAM_STR);
		$stmt -> bindParam(":id", $datos["id"], PDO::PARAM_STR);

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
	BORRAR CATEGORIA
	=============================================*/

	static public function mdlBorrarCategoria($tabla, $datos){

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
	CONTAR PRODUCTOS POR CATEGORÍA
	=============================================*/ 

	static public function mdlContarProductosPorCategoria($idCategoria){ 

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM productos WHERE id_categoria = :id_categoria");
		$stmt -> bindParam(":id_categoria", $idCategoria, PDO::PARAM_INT); 
		$stmt -> execute();

		$resultado = $stmt -> fetch(); 

		return $resultado["total"];

		$stmt -> close();
		$stmt = null;
	}

	
}