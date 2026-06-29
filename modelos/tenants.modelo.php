<?php

require_once "conexion.php";

class ModeloTenants {

	/*=============================================
	MOSTRAR INQUILINOS (TENANTS)
	=============================================*/
	static public function mdlMostrarTenants($tabla, $item, $valor) {

		if ($item != null) {
			$stmt = Conexion::conectarMaster()->prepare("SELECT * FROM $tabla WHERE $item = :$item LIMIT 1");
			$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch();
		} else {
			$stmt = Conexion::conectarMaster()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
			$stmt->execute();
			return $stmt->fetchAll();
		}
		
		$stmt = null;
	}

	/*=============================================
	CREAR INQUILINO (TENANT)
	=============================================*/
	static public function mdlCrearTenant($tabla, $datos) {

		$stmt = Conexion::conectarMaster()->prepare("INSERT INTO $tabla(subdominio, db_name, db_user, db_pass, db_host, estado) VALUES (:subdominio, :db_name, :db_user, :db_pass, :db_host, :estado)");

		$stmt->bindParam(":subdominio", $datos["subdominio"], PDO::PARAM_STR);
		$stmt->bindParam(":db_name", $datos["db_name"], PDO::PARAM_STR);
		$stmt->bindParam(":db_user", $datos["db_user"], PDO::PARAM_STR);
		$stmt->bindParam(":db_pass", $datos["db_pass"], PDO::PARAM_STR);
		$stmt->bindParam(":db_host", $datos["db_host"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}

	/*=============================================
	EDITAR INQUILINO (TENANT)
	=============================================*/
	static public function mdlEditarTenant($tabla, $datos) {

		$stmt = Conexion::conectarMaster()->prepare("UPDATE $tabla SET subdominio = :subdominio, db_name = :db_name, db_user = :db_user, db_pass = :db_pass, db_host = :db_host, estado = :estado WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":subdominio", $datos["subdominio"], PDO::PARAM_STR);
		$stmt->bindParam(":db_name", $datos["db_name"], PDO::PARAM_STR);
		$stmt->bindParam(":db_user", $datos["db_user"], PDO::PARAM_STR);
		$stmt->bindParam(":db_pass", $datos["db_pass"], PDO::PARAM_STR);
		$stmt->bindParam(":db_host", $datos["db_host"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}

	/*=============================================
	ELIMINAR INQUILINO (TENANT)
	=============================================*/
	static public function mdlEliminarTenant($tabla, $id) {

		$stmt = Conexion::conectarMaster()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}
}
