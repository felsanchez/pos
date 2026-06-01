<?php

require_once "conexion.php";

class ModeloProveedores
{

	/*=============================================
	CREAR PROVEEDOR
	=============================================*/

	static public function mdlIngresarProveedor($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, documento, tipo_documento_id, marca, celular, correo, direccion, municipio_id, organizacion_id) VALUES (:nombre, :documento, :tipo_documento_id, :marca, :celular, :correo, :direccion, :municipio_id, :organizacion_id)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_documento_id", $datos["tipo_documento_id"], PDO::PARAM_INT);
		$stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
		$stmt->bindParam(":celular", $datos["celular"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":municipio_id", $datos["municipio_id"], PDO::PARAM_STR);
		$stmt->bindParam(":organizacion_id", $datos["organizacion_id"], PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/

	static public function mdlMostrarProveedores($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE eliminado = 0 ORDER BY id DESC");

			$stmt->execute();

			return $stmt->fetchAll();
		}

		$stmt->close();

	}

	/*=============================================
	MOSTRAR PROVEEDORES SERVER-SIDE
	=============================================*/
	static public function mdlMostrarProveedoresServerSide($tabla, $where, $order, $limit)
	{
		if (empty(trim($where))) {
			$where = "WHERE eliminado = 0";
		} else {
			$where .= " AND eliminado = 0";
		}
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla $where $order $limit");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/*=============================================
	OBTENER TOTAL PROVEEDORES (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalProveedores($tabla, $where)
	{
		if (empty(trim($where))) {
			$where = "WHERE eliminado = 0";
		} else {
			$where .= " AND eliminado = 0";
		}
		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM $tabla $where");
		$stmt->execute();
		return $stmt->fetchColumn();
	}


	/*=============================================
	EDITAR PROVEEDORES
	=============================================*/

	static public function mdlEditarProveedor($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, documento = :documento, tipo_documento_id = :tipo_documento_id, marca = :marca, celular = :celular, correo = :correo, direccion = :direccion, municipio_id = :municipio_id, organizacion_id = :organizacion_id WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_documento_id", $datos["tipo_documento_id"], PDO::PARAM_INT);
		$stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
		$stmt->bindParam(":celular", $datos["celular"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":municipio_id", $datos["municipio_id"], PDO::PARAM_STR);
		$stmt->bindParam(":organizacion_id", $datos["organizacion_id"], PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	BORRAR PROVEEDORES
	=============================================*/

	static public function mdlBorrarProveedor($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET eliminado = 1 WHERE id = :id");

		$stmt->bindParam(":id", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;
	}


	/*=============================================
	CONTAR PRODUCTOS POR PROVEEDOR
	=============================================*/

	static public function mdlContarProductosPorProveedor($idProveedor)
	{

		$idBodega = isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;

		$query = "SELECT COUNT(DISTINCT p.id) as total FROM productos p ";
		$query .= " INNER JOIN productos_bodegas pb ON p.id = pb.id_producto AND pb.id_bodega = $idBodega ";
		$query .= " WHERE p.id_proveedor = :id_proveedor AND p.eliminado = 0 AND pb.estado = 1";

		$stmt = Conexion::conectar()->prepare($query);

		$stmt->bindParam(":id_proveedor", $idProveedor, PDO::PARAM_INT);
		$stmt->execute();
		$resultado = $stmt->fetch();

		return $resultado["total"];
	}

	/*=============================================
	CONTAR PRODUCTOS ACTIVOS GLOBALES (PROVEEDORES)
	=============================================*/
	static public function mdlContarProductosActivosGlobales($idProveedor){ 
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(DISTINCT p.id) 
			FROM productos p 
			JOIN productos_bodegas pb ON p.id = pb.id_producto 
			JOIN bodegas b ON pb.id_bodega = b.id
			WHERE p.id_proveedor = :id_proveedor AND p.eliminado = 0 AND pb.estado = 1 AND b.estado = 1
		");
		$stmt -> bindParam(":id_proveedor", $idProveedor, PDO::PARAM_INT); 
		$stmt -> execute();
		return $stmt -> fetchColumn();
	}


	/*=============================================
	ACTUALIZAR PROVEEDORES
	=============================================*/

	/*static public function mdlActualizarProveedor($tabla, $item1, $valor1, $valor){
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
	}  */


	/*=============================================
	ACTUALIZAR NOTAS
	=============================================*/

	static public function mdlActualizarNotas($tabla, $id, $notas)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET notas = :notas WHERE id = :id");
		$stmt->bindParam(":notas", $notas, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}
		$stmt = null;
	}


}