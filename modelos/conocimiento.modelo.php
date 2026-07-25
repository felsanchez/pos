<?php

require_once "conexion.php";

class ModeloConocimiento
{
	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/
	static public function mdlMostrarCategorias($tabla, $item, $valor)
	{
		if ($item != null) {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY nombre ASC");
			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} else {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY nombre ASC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		$stmt = null;
	}

	/*=============================================
	INGRESAR CATEGORIA
	=============================================*/
	static public function mdlIngresarCategoria($tabla, $datos)
	{
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, estado) VALUES (:nombre, 1)");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	EDITAR CATEGORIA
	=============================================*/
	static public function mdlEditarCategoria($tabla, $datos)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre WHERE id = :id");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR CATEGORIA (ESTADO / TOGGLE)
	=============================================*/
	static public function mdlActualizarCategoria($tabla, $item1, $valor1, $item2, $valor2)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");
		$stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	ELIMINAR CATEGORIA
	=============================================*/
	static public function mdlEliminarCategoria($tabla, $id)
	{
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	MOSTRAR ARTICULOS
	=============================================*/
	static public function mdlMostrarArticulos($tabla, $item, $valor)
	{
		if ($item != null) {
			$stmt = Conexion::conectar()->prepare("
				SELECT a.*, c.nombre as nombre_categoria 
				FROM $tabla a
				INNER JOIN empresa_conocimiento_categorias c ON a.id_categoria = c.id
				WHERE a.$item = :$item
				ORDER BY a.id DESC
			");
			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} else {
			$stmt = Conexion::conectar()->prepare("
				SELECT a.*, c.nombre as nombre_categoria 
				FROM $tabla a
				INNER JOIN empresa_conocimiento_categorias c ON a.id_categoria = c.id
				ORDER BY a.id DESC
			");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		$stmt = null;
	}

	/*=============================================
	MOSTRAR ARTICULOS SERVER-SIDE
	=============================================*/
	static public function mdlMostrarArticulosServerSide($tabla, $where, $order, $limit)
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT a.*, c.nombre as nombre_categoria 
			FROM $tabla a
			INNER JOIN empresa_conocimiento_categorias c ON a.id_categoria = c.id 
			$where 
			$order 
			$limit
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/*=============================================
	OBTENER TOTAL ARTICULOS (SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalArticulos($tabla, $where)
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(*) 
			FROM $tabla a
			INNER JOIN empresa_conocimiento_categorias c ON a.id_categoria = c.id
			$where
		");
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/*=============================================
	INGRESAR ARTICULO
	=============================================*/
	static public function mdlIngresarArticulo($tabla, $datos)
	{
		$pdo = Conexion::conectar();

		$stmt = $pdo->prepare("
			INSERT INTO $tabla(id_categoria, titulo, contenido, palabras_clave, estado) 
			VALUES (:id_categoria, :titulo, :contenido, :palabras_clave, 1)
		");
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
		$stmt->bindParam(":contenido", $datos["contenido"], PDO::PARAM_STR);
		$stmt->bindParam(":palabras_clave", $datos["palabras_clave"], PDO::PARAM_STR);

		if ($stmt->execute()) {

		return array(
			"ok" => true,
			"id" => $pdo->lastInsertId()
		);

	} else {

		return array(
			"ok" => false
		);

	}
		$stmt = null;
	}

	/*=============================================
	EDITAR ARTICULO
	=============================================*/
	static public function mdlEditarArticulo($tabla, $datos)
	{
		$stmt = Conexion::conectar()->prepare("
			UPDATE $tabla 
			SET id_categoria = :id_categoria, 
				titulo = :titulo, 
				contenido = :contenido, 
				palabras_clave = :palabras_clave 
			WHERE id = :id
		");
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
		$stmt->bindParam(":contenido", $datos["contenido"], PDO::PARAM_STR);
		$stmt->bindParam(":palabras_clave", $datos["palabras_clave"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR ARTICULO (ESTADO / TOGGLE)
	=============================================*/
	static public function mdlActualizarArticulo($tabla, $item1, $valor1, $item2, $valor2)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");
		$stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":" . $item2, $valor2, PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	ELIMINAR ARTICULO
	=============================================*/
	static public function mdlEliminarArticulo($tabla, $id)
	{
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
		$stmt = null;
	}
}
