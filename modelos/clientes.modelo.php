<?php

require_once "conexion.php";

class ModeloClientes
{

	/*=============================================
	CREAR CLIENTE
	=============================================*/

	static public function mdlIngresarCliente($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, documento, email, telefono, departamento, ciudad, direccion, estatus, notas, fecha_nacimiento, tipo_documento_id, digito_verificacion, tipo_persona, regimen_tributario, responsabilidades_fiscales, municipio_id, codigo_postal, nombre_comercial, razon_social) VALUES (:nombre, :documento, :email, :telefono, :departamento, :ciudad, :direccion, :estatus, :notas, :fecha_nacimiento, :tipo_documento_id, :digito_verificacion, :tipo_persona, :regimen_tributario, :responsabilidades_fiscales, :municipio_id, :codigo_postal, :nombre_comercial, :razon_social)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":departamento", $datos["departamento"], PDO::PARAM_STR);
		$stmt->bindParam(":ciudad", $datos["ciudad"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":estatus", $datos["estatus"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);

		// Campos de facturación electrónica
		$stmt->bindParam(":tipo_documento_id", $datos["tipo_documento_id"], PDO::PARAM_INT);
		$stmt->bindParam(":digito_verificacion", $datos["digito_verificacion"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_persona", $datos["tipo_persona"], PDO::PARAM_STR);
		$stmt->bindParam(":regimen_tributario", $datos["regimen_tributario"], PDO::PARAM_STR);
		$stmt->bindParam(":responsabilidades_fiscales", $datos["responsabilidades_fiscales"], PDO::PARAM_STR);
		$stmt->bindParam(":municipio_id", $datos["municipio_id"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo_postal", $datos["codigo_postal"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_comercial", $datos["nombre_comercial"], PDO::PARAM_STR);
		$stmt->bindParam(":razon_social", $datos["razon_social"], PDO::PARAM_STR);

		try {
			if ($stmt->execute()) {
				return "ok";
			} else {
				return "error";
			}
		} catch (PDOException $e) {
			if ($e->getCode() == '23000') {
				return "error_duplicado";
			}
			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/

	static public function mdlMostrarClientes($tabla, $item, $valor)
	{

		if ($item != null) {

			// 🔹 MODIFICACIÓN: JOIN con factus_municipios
			// IMPORTANTE: Usar alias 'c' para evitar ambigüedad en columna id si $item es 'id'
			$columnaBusqueda = ($item == 'id') ? 'c.id' : 'c.' . $item;

			// 🔹 MODIFICACIÓN ROBUSTA: JOIN flexible para soportar tanto código DANE como ID de Factus
			// Algunos clientes tienen guardado el código (ej: 05001) y otros el ID interno (ej: 169)
			$sql = "SELECT c.*, 
					COALESCE(NULLIF(c.ciudad, ''), m.nombre) as ciudad_real, 
					m.nombre as nombre_municipio,
					m.departamento as nombre_departamento
					FROM $tabla c 
					LEFT JOIN factus_municipios m ON (c.municipio_id = m.codigo OR c.municipio_id = m.id_factus)
					WHERE $columnaBusqueda = :$item";

			$stmt = Conexion::conectar()->prepare($sql);

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			$res = $stmt->fetch();

			// 🔹 MODIFICACIÓN ROBUSTA: Si hay un municipio vinculado (factus_municipios), USARLO SIEMPRE.
			// Esto corrige casos donde 'ciudad' tiene basura, espacios, o datos desactualizados.
			if ($res && !empty($res['nombre_municipio'])) {
				$res['ciudad'] = $res['nombre_municipio'];
			}

			return $res;

		} else {

			// 🔹 MODIFICACIÓN: JOIN con factus_municipios para obtener el nombre real de la ciudad/municipio
			// Si el campo 'ciudad' está vacío o es un ID, intentamos obtener el nombre desde factus_municipios
			// 🔹 MODIFICACIÓN ROBUSTA: JOIN flexible
			$sql = "SELECT c.*, 
					COALESCE(NULLIF(c.ciudad, ''), m.nombre) as ciudad_real,
					m.nombre as nombre_municipio,
					m.departamento as nombre_departamento
					FROM $tabla c 
					LEFT JOIN factus_municipios m ON (c.municipio_id = m.codigo OR c.municipio_id = m.id_factus)
					ORDER BY id DESC";

			$stmt = Conexion::conectar()->prepare($sql);

			$stmt->execute();

			$resultados = $stmt->fetchAll();

			// Post-procesamiento para asegurar que 'ciudad' tenga el valor correcto
			foreach ($resultados as &$res) {
				// 🔹 MODIFICACIÓN ROBUSTA: Si hay un municipio vinculado, USARLO.
				if (!empty($res['nombre_municipio'])) {
					$res['ciudad'] = $res['nombre_municipio'];
				}
			}

			return $resultados;
		}

		$stmt->close();

		$stmt = null;

	}


	/*=============================================
	EDITAR CLIENTE
	=============================================*/

	static public function mdlEditarCliente($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, documento = :documento, email = :email, telefono = :telefono, departamento = :departamento, ciudad = :ciudad, direccion = :direccion, estatus = :estatus, notas = :notas, fecha_nacimiento = :fecha_nacimiento, tipo_documento_id = :tipo_documento_id, digito_verificacion = :digito_verificacion, tipo_persona = :tipo_persona, regimen_tributario = :regimen_tributario, responsabilidades_fiscales = :responsabilidades_fiscales, municipio_id = :municipio_id, codigo_postal = :codigo_postal, nombre_comercial = :nombre_comercial, razon_social = :razon_social WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":departamento", $datos["departamento"], PDO::PARAM_STR);
		$stmt->bindParam(":ciudad", $datos["ciudad"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":estatus", $datos["estatus"], PDO::PARAM_STR);
		$stmt->bindParam(":notas", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);

		// Campos de facturación electrónica
		$stmt->bindParam(":tipo_documento_id", $datos["tipo_documento_id"], PDO::PARAM_INT);
		$stmt->bindParam(":digito_verificacion", $datos["digito_verificacion"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_persona", $datos["tipo_persona"], PDO::PARAM_STR);
		$stmt->bindParam(":regimen_tributario", $datos["regimen_tributario"], PDO::PARAM_STR);
		$stmt->bindParam(":responsabilidades_fiscales", $datos["responsabilidades_fiscales"], PDO::PARAM_STR);
		$stmt->bindParam(":municipio_id", $datos["municipio_id"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo_postal", $datos["codigo_postal"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_comercial", $datos["nombre_comercial"], PDO::PARAM_STR);
		$stmt->bindParam(":razon_social", $datos["razon_social"], PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	ELIMINAR CLIENTE
	=============================================*/

	static public function mdlEliminarCliente($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

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
	ACTUALIZAR CLIENTE
	=============================================*/

	static public function mdlActualizarCliente($tabla, $item1, $valor1, $valor)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":id", $valor, PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	ACTUALIZAR estatus
	=============================================*/
	static public function mdlActualizarEstatusCliente($tabla, $datos)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET estatus = :estatus WHERE id = :id");

		$stmt->bindParam(":estatus", $datos["estatus"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error"; // o usa: return $stmt->errorInfo();
		}

		//$stmt->close();
		$stmt = null;
	}


	/*=============================================
  ACTUALIZAR notas
  =============================================*/
	static public function mdlActualizarNota($tabla, $id, $nota)
	{
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET notas = :nota WHERE id = :id");
		$stmt->bindParam(":nota", $nota, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;
	}


}