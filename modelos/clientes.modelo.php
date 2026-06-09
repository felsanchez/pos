<?php

require_once "conexion.php";

class ModeloClientes
{

	/*=============================================
	CREAR CLIENTE
	=============================================*/

	static public function mdlIngresarCliente($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, documento, email, telefono, departamento, ciudad, direccion, estatus, notas, fecha_nacimiento, tipo_documento_id, digito_verificacion, tipo_persona, regimen_tributario, responsabilidades_fiscales, responsabilidad_tributaria, municipio_id, codigo_postal, nombre_comercial, razon_social) VALUES (:nombre, :documento, :email, :telefono, :departamento, :ciudad, :direccion, :estatus, :notas, :fecha_nacimiento, :tipo_documento_id, :digito_verificacion, :tipo_persona, :regimen_tributario, :responsabilidades_fiscales, :responsabilidad_tributaria, :municipio_id, :codigo_postal, :nombre_comercial, :razon_social)");

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
		$stmt->bindParam(":responsabilidad_tributaria", $datos["responsabilidad_tributaria"], PDO::PARAM_STR);
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
					WHERE c.eliminado = 0
					ORDER BY c.id DESC";

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
	MOSTRAR CLIENTES SERVER-SIDE
	=============================================*/
	static public function mdlMostrarClientesServerSide($tabla, $where, $order, $limit)
	{
		if (empty(trim($where))) {
			$where = "WHERE c.eliminado = 0";
		} else {
			$where .= " AND c.eliminado = 0";
		}

		$sql = "SELECT c.*, 
				COALESCE(NULLIF(c.ciudad, ''), m.nombre) as ciudad_real,
				m.nombre as nombre_municipio,
				m.departamento as nombre_departamento
				FROM $tabla c 
				LEFT JOIN factus_municipios m ON (c.municipio_id = m.codigo OR c.municipio_id = m.id_factus)
				$where $order $limit";

		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->execute();
		$resultados = $stmt->fetchAll();

		foreach ($resultados as &$res) {
			if (!empty($res['nombre_municipio'])) {
				$res['ciudad'] = $res['nombre_municipio'];
			}
		}

		return $resultados;
	}

	/*=============================================
	OBTENER TOTAL CLIENTES (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalClientes($tabla, $where)
	{
		if (empty(trim($where))) {
			$where = "WHERE c.eliminado = 0";
		} else {
			$where .= " AND c.eliminado = 0";
		}

		$sql = "SELECT COUNT(*) 
				FROM $tabla c 
				LEFT JOIN factus_municipios m ON (c.municipio_id = m.codigo OR c.municipio_id = m.id_factus) 
				$where";
				
		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->execute();
		return $stmt->fetchColumn();
	}


	/*=============================================
	EDITAR CLIENTE
	=============================================*/

	static public function mdlEditarCliente($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, documento = :documento, email = :email, telefono = :telefono, departamento = :departamento, ciudad = :ciudad, direccion = :direccion, estatus = :estatus, notas = :notas, fecha_nacimiento = :fecha_nacimiento, tipo_documento_id = :tipo_documento_id, digito_verificacion = :digito_verificacion, tipo_persona = :tipo_persona, regimen_tributario = :regimen_tributario, responsabilidades_fiscales = :responsabilidades_fiscales, responsabilidad_tributaria = :responsabilidad_tributaria, municipio_id = :municipio_id, codigo_postal = :codigo_postal, nombre_comercial = :nombre_comercial, razon_social = :razon_social WHERE id = :id");

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
		$stmt->bindParam(":responsabilidad_tributaria", $datos["responsabilidad_tributaria"], PDO::PARAM_STR);
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


	/*=============================================
	IMPORTAR CLIENTES MASIVOS
	=============================================*/
	static public function mdlImportarClientesMasivos($tabla, $clientesInsertar, $clientesActualizar = array())
	{
		$db = Conexion::conectar();
		$successInsert = 0;
		$successUpdate = 0;
		$errors = [];

		try {
			$db->beginTransaction();

			// 1. INSERCIÓN DE NUEVOS CLIENTES
			if (count($clientesInsertar) > 0) {
				$stmtInsert = $db->prepare("INSERT INTO $tabla(nombre, documento, email, telefono, departamento, ciudad, direccion, estatus, notas, fecha_nacimiento, tipo_documento_id, digito_verificacion, tipo_persona, regimen_tributario, responsabilidades_fiscales, municipio_id, codigo_postal, nombre_comercial, razon_social, eliminado) 
                        VALUES (:nombre, :documento, :email, :telefono, :departamento, :ciudad, :direccion, :estatus, :notas, :fecha_nacimiento, :tipo_documento_id, :digito_verificacion, :tipo_persona, :regimen_tributario, :responsabilidades_fiscales, :municipio_id, :codigo_postal, :nombre_comercial, :razon_social, 0)");

				foreach ($clientesInsertar as $fila) {
					$stmtInsert->bindParam(":nombre", $fila["nombre"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":documento", $fila["documento"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":email", $fila["email"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":telefono", $fila["telefono"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":departamento", $fila["departamento"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":ciudad", $fila["ciudad"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":direccion", $fila["direccion"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":estatus", $fila["estatus"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":notas", $fila["notas"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":fecha_nacimiento", $fila["fecha_nacimiento"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":tipo_documento_id", $fila["tipo_documento_id"], PDO::PARAM_INT);
					$stmtInsert->bindParam(":digito_verificacion", $fila["digito_verificacion"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":tipo_persona", $fila["tipo_persona"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":regimen_tributario", $fila["regimen_tributario"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":responsabilidades_fiscales", $fila["responsabilidades_fiscales"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":municipio_id", $fila["municipio_id"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":codigo_postal", $fila["codigo_postal"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":nombre_comercial", $fila["nombre_comercial"], PDO::PARAM_STR);
					$stmtInsert->bindParam(":razon_social", $fila["razon_social"], PDO::PARAM_STR);

					try {
						if ($stmtInsert->execute()) {
							$successInsert++;
						} else {
							$errors[] = "Error al insertar fila con documento " . $fila["documento"];
						}
					} catch (PDOException $e) {
						if ($e->getCode() == '23000' && strpos($e->getMessage(), 'unique_telefono') !== false) {
							$errors[] = "Excepción al insertar documento " . $fila["documento"] . ": El número de teléfono ya se encuentra registrado para otro cliente.";
						} else {
							$errors[] = "Excepción al insertar documento " . $fila["documento"] . ": " . $e->getMessage();
						}
					}
				}
			}

			// 2. ACTUALIZACIÓN DE CLIENTES EXISTENTES
			if (count($clientesActualizar) > 0) {
				$stmtUpdate = $db->prepare("UPDATE $tabla SET nombre = :nombre, documento = :documento, email = :email, telefono = :telefono, departamento = :departamento, ciudad = :ciudad, direccion = :direccion, estatus = :estatus, notas = :notas, fecha_nacimiento = :fecha_nacimiento, tipo_documento_id = :tipo_documento_id, digito_verificacion = :digito_verificacion, tipo_persona = :tipo_persona, regimen_tributario = :regimen_tributario, responsabilidades_fiscales = :responsabilidades_fiscales, municipio_id = :municipio_id, codigo_postal = :codigo_postal, nombre_comercial = :nombre_comercial, razon_social = :razon_social, eliminado = 0 WHERE id = :id");

				foreach ($clientesActualizar as $fila) {
					$stmtUpdate->bindParam(":id", $fila["id"], PDO::PARAM_INT);
					$stmtUpdate->bindParam(":nombre", $fila["nombre"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":documento", $fila["documento"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":email", $fila["email"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":telefono", $fila["telefono"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":departamento", $fila["departamento"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":ciudad", $fila["ciudad"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":direccion", $fila["direccion"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":estatus", $fila["estatus"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":notas", $fila["notas"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":fecha_nacimiento", $fila["fecha_nacimiento"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":tipo_documento_id", $fila["tipo_documento_id"], PDO::PARAM_INT);
					$stmtUpdate->bindParam(":digito_verificacion", $fila["digito_verificacion"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":tipo_persona", $fila["tipo_persona"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":regimen_tributario", $fila["regimen_tributario"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":responsabilidades_fiscales", $fila["responsabilidades_fiscales"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":municipio_id", $fila["municipio_id"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":codigo_postal", $fila["codigo_postal"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":nombre_comercial", $fila["nombre_comercial"], PDO::PARAM_STR);
					$stmtUpdate->bindParam(":razon_social", $fila["razon_social"], PDO::PARAM_STR);

					try {
						if ($stmtUpdate->execute()) {
							$successUpdate++;
						} else {
							$errors[] = "Error al actualizar fila con documento " . $fila["documento"];
						}
					} catch (PDOException $e) {
						if ($e->getCode() == '23000' && strpos($e->getMessage(), 'unique_telefono') !== false) {
							$errors[] = "Excepción al actualizar documento " . $fila["documento"] . ": El número de teléfono ya se encuentra registrado para otro cliente.";
						} else {
							$errors[] = "Excepción al actualizar documento " . $fila["documento"] . ": " . $e->getMessage();
						}
					}
				}
			}

			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			return [
				"estado"  => "error",
				"ingresados"  => 0,
				"actualizados" => 0,
				"exitos" => 0,
				"errores" => ["Error crítico en la transacción: " . $e->getMessage()]
			];
		}

		$totalExitos = $successInsert + $successUpdate;

		return [
			"estado"  => count($errors) === 0 ? "ok" : ($totalExitos > 0 ? "parcial" : "error"),
			"ingresados"  => $successInsert,
			"actualizados" => $successUpdate,
			"exitos" => $totalExitos,
			"errores" => $errors
		];
	}
}