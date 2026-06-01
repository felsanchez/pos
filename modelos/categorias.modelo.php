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

		$query = "SELECT COUNT(DISTINCT p.id) as total FROM productos p ";
		
		$idBodega = isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;
		
		$query .= " INNER JOIN productos_bodegas pb ON p.id = pb.id_producto AND pb.id_bodega = $idBodega ";
		$query .= " WHERE p.id_categoria = :id_categoria AND p.eliminado = 0 AND pb.estado = 1";

		$stmt = Conexion::conectar()->prepare($query);
		$stmt -> bindParam(":id_categoria", $idCategoria, PDO::PARAM_INT); 
		$stmt -> execute();

		$resultado = $stmt -> fetch(); 
		
		error_log("CAT_DEBUG: cat=$idCategoria, bodega=" . (isset($_SESSION['id_bodega']) ? $_SESSION['id_bodega'] : 'NOT_SET') . ", total=" . $resultado["total"]);

		return $resultado["total"];

		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	CONTAR PRODUCTOS ACTIVOS GLOBALES (SIN IMPORTAR SUCURSAL)
	=============================================*/
	static public function mdlContarProductosActivosGlobales($idCategoria){ 
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(DISTINCT p.id) 
			FROM productos p 
			JOIN productos_bodegas pb ON p.id = pb.id_producto 
			JOIN bodegas b ON pb.id_bodega = b.id
			WHERE p.id_categoria = :id_categoria AND p.eliminado = 0 AND pb.estado = 1 AND b.estado = 1
		");
		$stmt -> bindParam(":id_categoria", $idCategoria, PDO::PARAM_INT); 
		$stmt -> execute();
		return $stmt -> fetchColumn();
	}
	
}