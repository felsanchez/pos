<?php

require_once "conexion.php";

class ModeloEstadosClientes
{

	/*============================================
	MOSTRAR ESTADOS DE CLIENTES
	=============================================*/

	static public function mdlMostrarEstadosClientes($tabla, $item, $valor)
	{

		if ($item != null) {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY orden ASC");
			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch();

		} else {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE activo = 1 ORDER BY orden ASC");
			$stmt->execute();
			return $stmt->fetchAll();
		}

		$stmt->close();
		$stmt = null;
	}


	/*=============================================
	CREAR ESTADO
	=============================================*/

	static public function mdlCrearEstado($tabla, $datos)
	{

		// Verificar si el nombre ya existe (activo o inactivo por la restricción UNIQUE key)
		$stmtNombre = Conexion::conectar()->prepare("SELECT id, activo FROM $tabla WHERE LOWER(nombre) = LOWER(:nombre)");
		$stmtNombre->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmtNombre->execute();
		$nombreExiste = $stmtNombre->fetch();

		if ($nombreExiste) {
			if ($nombreExiste["activo"] == 1) {
				return "duplicado";
			} else {
				// Reactivar registro inactivo existente para no violar UNIQUE key
				$stmtReactivar = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 1, color = :color, orden = :orden WHERE id = :id");
				$stmtReactivar->bindParam(":color", $datos["color"], PDO::PARAM_STR);
				$stmtReactivar->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
				$stmtReactivar->bindParam(":id", $nombreExiste["id"], PDO::PARAM_INT);
				if ($stmtReactivar->execute()) {
					return "ok";
				} else {
					return "error";
				}
			}
		}

		// Verificar si el orden ya existe
		$stmtCheck = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden");
		$stmtCheck->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmtCheck->execute();
		$existe = $stmtCheck->fetch();

		// Si existe, mover todos los órdenes mayores o iguales
		if ($existe) {
			$stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden");
			$stmtUpdate->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
			$stmtUpdate->execute();
		}

		// Insertar el nuevo registro
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, color, orden, activo) VALUES (:nombre, :color, :orden, 1)");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	EDITAR ESTADO
	=============================================*/

	static public function mdlEditarEstado($tabla, $datos)
	{

		// Verificar si el nombre ya existe en otro registro (activo o inactivo por UNIQUE key)
		$stmtNombre = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE LOWER(nombre) = LOWER(:nombre) AND id != :id");
		$stmtNombre->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmtNombre->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmtNombre->execute();
		$nombreExiste = $stmtNombre->fetch();

		if ($nombreExiste) {
			return "duplicado";
		}

		// Verificar si el orden ya existe en otro registro
		$stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden AND id != :id");
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->execute();
		$existe = $stmt->fetch();

		// Si existe, ajustar el orden de los demás
		if ($existe) {
			// Mover todos los órdenes mayores o iguales
			$stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden AND id != :id");
			$stmtUpdate->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
			$stmtUpdate->bindParam(":id", $datos["id"], PDO::PARAM_INT);
			$stmtUpdate->execute();
		}

		// Actualizar el registro
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, color = :color, orden = :orden WHERE id = :id");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}


	/*=============================================
	ELIMINAR ESTADO (DESACTIVAR)
	=============================================*/

	static public function mdlEliminarEstado($tabla, $id)
	{

		// No eliminar físicamente, solo desactivar y liberar el nombre único
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 0, nombre = CONCAT(nombre, '_deleted_', id) WHERE id = :id");


		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	VERIFICAR SI ESTADO ESTÁ EN USO
	=============================================*/

	static public function mdlVerificarEstadoEnUso($nombreEstado)
	{

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM clientes WHERE estatus = :estatus");
		$stmt->bindParam(":estatus", $nombreEstado, PDO::PARAM_STR);
		$stmt->execute();
		$resultado = $stmt->fetch();

		return $resultado["total"];

		$stmt->close();
		$stmt = null;
	}



	/*=============================================
	ACTUALIZAR NOMBRE ESTADO EN CLIENTES
	=============================================*/

	static public function mdlActualizarNombreEstadoClientes($viejoNombre, $nuevoNombre)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE clientes SET estatus = :nuevoNombre WHERE estatus = :viejoNombre");

		$stmt->bindParam(":nuevoNombre", $nuevoNombre, PDO::PARAM_STR);
		$stmt->bindParam(":viejoNombre", $viejoNombre, PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

}