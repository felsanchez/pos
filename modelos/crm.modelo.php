<?php

require_once "conexion.php";

class ModeloCRM {

	/*=============================================
	MOSTRAR LEADS
	=============================================*/
	static public function mdlMostrarLeads($tabla, $item, $valor) {

		if($item != null) {

			$stmt = Conexion::conectar()->prepare("
				SELECT l.*, c.nombre as nombre_cliente, c.telefono as telefono_cliente, c.email as email_cliente, u.nombre as nombre_vendedor 
				FROM $tabla l
				INNER JOIN clientes c ON l.id_cliente = c.id
				INNER JOIN usuarios u ON l.id_vendedor = u.id
				WHERE l.$item = :$item
				ORDER BY l.orden ASC, l.fecha_creacion DESC
			");

			$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch();

		} else {

			$stmt = Conexion::conectar()->prepare("
				SELECT l.*, c.nombre as nombre_cliente, c.telefono as telefono_cliente, c.email as email_cliente, u.nombre as nombre_vendedor 
				FROM $tabla l
				INNER JOIN clientes c ON l.id_cliente = c.id
				INNER JOIN usuarios u ON l.id_vendedor = u.id
				ORDER BY l.orden ASC, l.fecha_creacion DESC
			");

			$stmt->execute();
			return $stmt->fetchAll();

		}

		$stmt = null;

	}

	/*=============================================
	CREAR LEAD
	=============================================*/
	static public function mdlCrearLead($tabla, $datos) {

		$stmt = Conexion::conectar()->prepare("
			INSERT INTO $tabla (id_cliente, titulo, valor_estimado, prioridad, etapa, id_vendedor, fecha_cierre, notas, codigo_orden, orden) 
			VALUES (:id_cliente, :titulo, :valor_estimado, :prioridad, :etapa, :id_vendedor, :fecha_cierre, :notes, :codigo_orden, :orden)
		");

		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
		$stmt->bindParam(":valor_estimado", $datos["valor_estimado"], PDO::PARAM_STR);
		$stmt->bindParam(":prioridad", $datos["prioridad"], PDO::PARAM_STR);
		$stmt->bindParam(":etapa", $datos["etapa"], PDO::PARAM_STR);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha_cierre", $datos["fecha_cierre"], PDO::PARAM_STR);
		$stmt->bindParam(":notes", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo_orden", $datos["codigo_orden"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	EDITAR LEAD
	=============================================*/
	static public function mdlEditarLead($tabla, $datos) {

		$stmt = Conexion::conectar()->prepare("
			UPDATE $tabla 
			SET id_cliente = :id_cliente, 
				titulo = :titulo, 
				valor_estimado = :valor_estimado, 
				prioridad = :prioridad, 
				etapa = :etapa, 
				id_vendedor = :id_vendedor, 
				fecha_cierre = :fecha_cierre, 
				notas = :notes 
			WHERE id = :id
		");

		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
		$stmt->bindParam(":valor_estimado", $datos["valor_estimado"], PDO::PARAM_STR);
		$stmt->bindParam(":prioridad", $datos["prioridad"], PDO::PARAM_STR);
		$stmt->bindParam(":etapa", $datos["etapa"], PDO::PARAM_STR);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha_cierre", $datos["fecha_cierre"], PDO::PARAM_STR);
		$stmt->bindParam(":notes", $datos["notas"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR ETAPA LEAD (DRAG & DROP / AUTO-MOVES)
	=============================================*/
	static public function mdlActualizarEtapa($tabla, $id, $etapa) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET etapa = :etapa WHERE id = :id");
		$stmt->bindParam(":etapa", $etapa, PDO::PARAM_STR);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR ETAPA Y ORDEN LEAD (DRAG & DROP ORDENADO)
	=============================================*/
	static public function mdlActualizarEtapaYOrden($tabla, $id, $etapa, $orden) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET etapa = :etapa, orden = :orden WHERE id = :id");
		$stmt->bindParam(":etapa", $etapa, PDO::PARAM_STR);
		$stmt->bindParam(":orden", $orden, PDO::PARAM_INT);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR ORDEN LEAD
	=============================================*/
	static public function mdlActualizarOrdenLead($tabla, $id, $orden) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET orden = :orden WHERE id = :id");
		$stmt->bindParam(":orden", $orden, PDO::PARAM_INT);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR ETAPA LEAD POR CODIGO ORDEN
	=============================================*/
	static public function mdlActualizarEtapaPorCodigoOrden($tabla, $codigoOrden, $etapa) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET etapa = :etapa WHERE codigo_orden = :codigo_orden");
		$stmt->bindParam(":etapa", $etapa, PDO::PARAM_STR);
		$stmt->bindParam(":codigo_orden", $codigoOrden, PDO::PARAM_STR);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR DATOS LEAD POR CODIGO ORDEN
	=============================================*/
	static public function mdlActualizarDatosLeadPorCodigoOrden($tabla, $codigoOrden, $datos) {

		$setQuery = [];
		foreach ($datos as $columna => $valor) {
			$setQuery[] = "$columna = :$columna";
		}
		$setString = implode(", ", $setQuery);

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $setString WHERE codigo_orden = :codigo_orden");
		
		foreach ($datos as $columna => $valor) {
			$stmt->bindValue(":$columna", $valor);
		}
		$stmt->bindValue(":codigo_orden", $codigoOrden, PDO::PARAM_STR);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ELIMINAR LEAD
	=============================================*/
	static public function mdlEliminarLead($tabla, $id) {

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ELIMINAR LEAD POR CODIGO ORDEN
	=============================================*/
	static public function mdlEliminarLeadPorCodigoOrden($tabla, $codigoOrden) {

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE codigo_orden = :codigo_orden");
		$stmt->bindParam(":codigo_orden", $codigoOrden, PDO::PARAM_STR);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	MOSTRAR ETAPAS (COLUMNAS KANBAN)
	=============================================*/
	static public function mdlMostrarEtapas($tabla, $item, $valor) {

		if($item != null) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY orden ASC");
			$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch();

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY orden ASC");
			$stmt->execute();
			return $stmt->fetchAll();

		}

		$stmt = null;

	}

	/*=============================================
	CREAR ETAPA
	=============================================*/
	static public function mdlCrearEtapa($tabla, $datos) {

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, color, orden, editable) VALUES (:nombre, :color, :orden, :editable)");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt->bindParam(":editable", $datos["editable"], PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	DESPLAZAR ETAPAS (INCREMENTAR ORDEN)
	=============================================*/
	static public function mdlDesplazarEtapas($tabla, $ordenDesde) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :ordenDesde");
		$stmt->bindParam(":ordenDesde", $ordenDesde, PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	EDITAR ETAPA
	=============================================*/
	static public function mdlEditarEtapa($tabla, $datos) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, color = :color, orden = :orden WHERE id = :id AND editable = 1");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	ELIMINAR ETAPA
	=============================================*/
	static public function mdlEliminarEtapa($tabla, $id) {

		// Solo eliminar si editable = 1 (columnas no del sistema)
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id AND editable = 1");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

	/*=============================================
	CONTAR LEADS EN ETAPA (VALIDAR ESCENARIO A)
	=============================================*/
	static public function mdlContarLeadsEnEtapa($tablaLeads, $nombreEtapa) {

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM $tablaLeads WHERE etapa = :etapa");
		$stmt->bindParam(":etapa", $nombreEtapa, PDO::PARAM_STR);
		$stmt->execute();
		$resultado = $stmt->fetch();
		return $resultado ? $resultado["total"] : 0;

	}

	/*=============================================
	DESPLAZAR ORDEN DE LEADS EN UNA ETAPA
	=============================================*/
	static public function mdlDesplazarLeadsEnEtapa($tabla, $etapa) {

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE etapa = :etapa");
		$stmt->bindParam(":etapa", $etapa, PDO::PARAM_STR);

		if($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt = null;

	}

}
