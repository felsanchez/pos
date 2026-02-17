<?php

require_once "conexion.php";

class ModeloProveedores{

	/*=============================================
	CREAR PROVEEDOR
	=============================================*/

	static public function mdlIngresarProveedor($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, marca, celular, correo, direccion) VALUES (:nombre, :marca, :celular, :correo, :direccion)");
			
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
		$stmt->bindParam(":celular", $datos["celular"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

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
	MOSTRAR PROVEEDORES
	=============================================*/

	static public function mdlMostrarProveedores($tabla, $item, $valor){

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
	EDITAR PROVEEDORES
	=============================================*/

	static public function mdlEditarProveedor($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, marca = :marca, celular = :celular, correo = :correo, direccion = :direccion WHERE id = :id");
			
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
		$stmt->bindParam(":celular", $datos["celular"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

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
	BORRAR PROVEEDORES
	=============================================*/

	static public function mdlBorrarProveedor($tabla, $datos){

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
	CONTAR PRODUCTOS POR PROVEEDOR
	=============================================*/ 

	static public function mdlContarProductosPorProveedor($idProveedor){ 

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM productos WHERE id_proveedor = :id_proveedor"); 

		$stmt -> bindParam(":id_proveedor", $idProveedor, PDO::PARAM_INT);
		$stmt -> execute();
		$resultado = $stmt -> fetch();

		return $resultado["total"]; 

		$stmt -> close();
		$stmt = null;
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

	static public function mdlActualizarNotas($tabla, $id, $notas) {
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