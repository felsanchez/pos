<?php

require_once "conexion.php";

class ModeloNotificaciones
{

	/*=============================================
	CREAR NOTIFICACIÓN
	=============================================*/

	static public function mdlCrearNotificacion($datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO notificaciones(tipo, titulo, mensaje, referencia_tipo, referencia_id)
												VALUES (:tipo, :titulo, :mensaje, :referencia_tipo, :referencia_id)");

		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
		$stmt->bindParam(":mensaje", $datos["mensaje"], PDO::PARAM_STR);
		$stmt->bindParam(":referencia_tipo", $datos["referencia_tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":referencia_id", $datos["referencia_id"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	OBTENER NOTIFICACIONES
	=============================================*/

	static public function mdlObtenerNotificaciones($cantidad = null, $soloNoLeidas = false, $idUsuario = null)
	{

		$sql = "SELECT n.*, 
                CASE WHEN nl.id_usuario IS NOT NULL THEN 1 ELSE 0 END as leida
                FROM notificaciones n
                CROSS JOIN configuracion c
                LEFT JOIN notificaciones_leidas nl ON n.id = nl.id_notificacion AND nl.id_usuario = :id_usuario
                WHERE (n.tipo NOT IN ('orden_agente_ia', 'orden_creada') OR c.notif_orden_agente_ia = 1)
                  AND (n.referencia_tipo != 'pago_bold' OR c.notif_transaccion_bold = 1)
                  AND (n.referencia_tipo != 'solicitud_edicion' OR c.notif_solicitud_edicion = 1)
                  AND (n.referencia_tipo != 'solicitud_eliminacion' OR c.notif_solicitud_eliminacion = 1)";

		if ($soloNoLeidas) {
			// Si solo queremos no leídas, filtramos donde NO haya registro en la tabla de lectura
			$sql .= " AND nl.id_usuario IS NULL";
		}

		$sql .= " ORDER BY n.fecha DESC";

		if ($cantidad) {
			$sql .= " LIMIT :cantidad";
		}

		$stmt = Conexion::conectar()->prepare($sql);

		// Asignar el ID de usuario (si es null, usamos 0 para que no coincida con nada)
		$idUsuario = $idUsuario ? $idUsuario : 0;
		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);

		if ($cantidad) {
			$stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
		}

		$stmt->execute();

		if ($cantidad == 1) {
			return $stmt->fetch();
		} else {
			return $stmt->fetchAll();
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	CONTAR NOTIFICACIONES NO LEÍDAS
	=============================================*/

	static public function mdlContarNoLeidas($idUsuario)
	{

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total 
                                               FROM notificaciones n
                                               CROSS JOIN configuracion c
                                               LEFT JOIN notificaciones_leidas nl ON n.id = nl.id_notificacion AND nl.id_usuario = :id_usuario
                                               WHERE nl.id_usuario IS NULL
                                                 AND (n.tipo NOT IN ('orden_agente_ia', 'orden_creada') OR c.notif_orden_agente_ia = 1)
                                                 AND (n.referencia_tipo != 'pago_bold' OR c.notif_transaccion_bold = 1)
                                                 AND (n.referencia_tipo != 'solicitud_edicion' OR c.notif_solicitud_edicion = 1)
                                                 AND (n.referencia_tipo != 'solicitud_eliminacion' OR c.notif_solicitud_eliminacion = 1)");

		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
		$stmt->execute();

		$resultado = $stmt->fetch();

		return $resultado["total"];

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	MARCAR NOTIFICACIÓN COMO LEÍDA
	=============================================*/

	static public function mdlMarcarComoLeida($idNotificacion, $idUsuario)
	{

		// Usamos INSERT IGNORE para evitar error si ya existe
		$stmt = Conexion::conectar()->prepare("INSERT IGNORE INTO notificaciones_leidas (id_notificacion, id_usuario) VALUES (:id_notificacion, :id_usuario)");

		$stmt->bindParam(":id_notificacion", $idNotificacion, PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	MARCAR TODAS COMO LEÍDAS
	=============================================*/

	static public function mdlMarcarTodasComoLeidas($idUsuario)
	{

		// Insertamos todas las notificaciones que no estén ya leídas por este usuario
		$stmt = Conexion::conectar()->prepare("INSERT IGNORE INTO notificaciones_leidas (id_notificacion, id_usuario)
                                               SELECT id, :id_usuario FROM notificaciones");

		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR NOTIFICACIÓN
	=============================================*/

	/*=============================================
	ELIMINAR NOTIFICACIÓN
	=============================================*/

	static public function mdlEliminarNotificacion($id)
	{

		// 1. Obtener datos de la notificación antes de eliminarla para borrado en cascada
		$stmtConsulta = Conexion::conectar()->prepare("SELECT referencia_tipo, referencia_id FROM notificaciones WHERE id = :id");
		$stmtConsulta->bindParam(":id", $id, PDO::PARAM_INT);
		$stmtConsulta->execute();
		$notificacion = $stmtConsulta->fetch();

		if ($notificacion) {
			if ($notificacion["referencia_tipo"] == "solicitud_edicion") {
				// Eliminar de edicion_pedido
				$stmtBorrarOrigen = Conexion::conectar()->prepare("DELETE FROM edicion_pedido WHERE id = :id_origen");
				$stmtBorrarOrigen->bindParam(":id_origen", $notificacion["referencia_id"], PDO::PARAM_INT);
				$stmtBorrarOrigen->execute();
			} else if ($notificacion["referencia_tipo"] == "solicitud_eliminacion") {
				// Eliminar de eliminacion_pedido
				$stmtBorrarOrigen = Conexion::conectar()->prepare("DELETE FROM eliminacion_pedido WHERE id = :id_origen");
				$stmtBorrarOrigen->bindParam(":id_origen", $notificacion["referencia_id"], PDO::PARAM_INT);
				$stmtBorrarOrigen->execute();
			} else if ($notificacion["referencia_tipo"] == "pago_bold") {
				// Eliminar de pagos_bold
				$stmtBorrarOrigen = Conexion::conectar()->prepare("DELETE FROM pagos_bold WHERE id = :id_origen");
				$stmtBorrarOrigen->bindParam(":id_origen", $notificacion["referencia_id"], PDO::PARAM_INT);
				$stmtBorrarOrigen->execute();
			}
		}

		// Primero eliminamos de la tabla de leídas
		$stmt = Conexion::conectar()->prepare("DELETE FROM notificaciones_leidas WHERE id_notificacion = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();

		$stmt = Conexion::conectar()->prepare("DELETE FROM notificaciones WHERE id = :id");

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
	ELIMINAR MÚLTIPLES NOTIFICACIONES
	=============================================*/

	/*=============================================
	ELIMINAR MÚLTIPLES NOTIFICACIONES
	=============================================*/

	static public function mdlEliminarNotificaciones($idsJson)
	{

		$ids = json_decode($idsJson, true);

		if (empty($ids) || !is_array($ids)) {
			return "error";
		}

		// Crear placeholders para el query
		$placeholders = implode(',', array_fill(0, count($ids), '?'));

		// 1. Borrado en Cascada: Obtener referencias antes de eliminar
		$stmtConsulta = Conexion::conectar()->prepare("SELECT referencia_tipo, referencia_id FROM notificaciones WHERE id IN ($placeholders)");
		foreach ($ids as $index => $id) {
			$stmtConsulta->bindValue($index + 1, $id, PDO::PARAM_INT);
		}
		$stmtConsulta->execute();
		$notificaciones = $stmtConsulta->fetchAll();

		foreach ($notificaciones as $notif) {
			if ($notif["referencia_tipo"] == "solicitud_edicion") {
				// Eliminar de edicion_pedido
				$stmtBorrarOrigen = Conexion::conectar()->prepare("DELETE FROM edicion_pedido WHERE id = :id_origen");
				$stmtBorrarOrigen->bindParam(":id_origen", $notif["referencia_id"], PDO::PARAM_INT);
				$stmtBorrarOrigen->execute();
			} else if ($notif["referencia_tipo"] == "solicitud_eliminacion") {
				// Eliminar de eliminacion_pedido
				$stmtBorrarOrigen = Conexion::conectar()->prepare("DELETE FROM eliminacion_pedido WHERE id = :id_origen");
				$stmtBorrarOrigen->bindParam(":id_origen", $notif["referencia_id"], PDO::PARAM_INT);
				$stmtBorrarOrigen->execute();
			} else if ($notif["referencia_tipo"] == "pago_bold") {
				// Eliminar de pagos_bold
				$stmtBorrarOrigen = Conexion::conectar()->prepare("DELETE FROM pagos_bold WHERE id = :id_origen");
				$stmtBorrarOrigen->bindParam(":id_origen", $notif["referencia_id"], PDO::PARAM_INT);
				$stmtBorrarOrigen->execute();
			}
		}

		// Primero eliminamos de leídas
		$stmt = Conexion::conectar()->prepare("DELETE FROM notificaciones_leidas WHERE id_notificacion IN ($placeholders)");
		foreach ($ids as $index => $id) {
			$stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
		}
		$stmt->execute();

		$stmt = Conexion::conectar()->prepare("DELETE FROM notificaciones WHERE id IN ($placeholders)");

		// Bind de cada ID
		foreach ($ids as $index => $id) {
			$stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
		}

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}


	/*=============================================
	VERIFICAR SI YA EXISTE NOTIFICACIÓN DE STOCK
	=============================================*/

	static public function mdlExisteNotificacionStock($tipo, $idProducto)
	{

		// Verificar si existe la notificación independientemente de si está leída o no
		// Esto evita duplicar notificaciones de stock bajo/agotado aunque se marquen como leídas

		$stmt = Conexion::conectar()->prepare("SELECT id FROM notificaciones
												WHERE tipo = :tipo
												AND referencia_tipo = 'producto'
												AND referencia_id = :referencia_id
												LIMIT 1");

		$stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
		$stmt->bindParam(":referencia_id", $idProducto, PDO::PARAM_INT);

		$stmt->execute();

		$resultado = $stmt->fetch();

		return $resultado ? true : false;

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	VERIFICAR SI EXISTE NOTIFICACIÓN (GENÉRICA)
	=============================================*/

	static public function mdlExisteNotificacion($tipo, $referenciaId, $referenciaTipo)
	{


		// Para notificaciones de tipo "orden_agente_ia", verificar si existe independientemente de si está leída o no
		// Para otros tipos, solo verificar las no leídas

		$sql = "SELECT id FROM notificaciones
				WHERE tipo = :tipo
				AND referencia_tipo = :referencia_tipo
				AND referencia_id = :referencia_id";

		// El chequeo de 'leida' ya no aplica globalmente,
		// así que solo verificamos si EXISTE la notificación.
		// Esto evita duplicados globales.
		// Si el usuario quiere volver a verla, tendría que haber una lógica de "reactivación", 
		// pero por ahora el requerimiento es "no volver a aparecer si existe".
		// Así que simplemente verificamos si existe.

		$sql .= " LIMIT 1";

		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
		$stmt->bindParam(":referencia_tipo", $referenciaTipo, PDO::PARAM_STR);
		$stmt->bindParam(":referencia_id", $referenciaId, PDO::PARAM_INT);

		$stmt->execute();

		$resultado = $stmt->fetch();

		return $resultado ? true : false;

		$stmt->close();
		$stmt = null;

	}
	/*=============================================
	OBTENER SOLICITUDES DE EDICIÓN (AGENTE IA)
	=============================================*/

	static public function mdlObtenerSolicitudesEdicion()
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM edicion_pedido");
		$stmt->execute();
		return $stmt->fetchAll();
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	OBTENER SOLICITUDES DE ELIMINACIÓN (AGENTE IA)
	=============================================*/

	static public function mdlObtenerSolicitudesEliminacion()
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM eliminacion_pedido");
		$stmt->execute();
		return $stmt->fetchAll();
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	SINCRONIZAR PAGOS BOLD A NOTIFICACIONES
	=============================================*/
	static public function mdlSincronizarPagosBold()
	{
		// Insertamos en notificaciones, seleccionando de pagos_bold
		// Solo insertamos si NO existe ya una notificación con referencia_tipo='pago_bold' y referencia_id = id del pago
		$sql = "INSERT INTO notificaciones (tipo, titulo, mensaje, fecha, referencia_tipo, referencia_id)
				SELECT 
					'Transaccion de BOLD',
					CONCAT(cuenta, ' - $', FORMAT(monto, 0)),
					CONCAT('Transaccion recibida de: ', correo),
					fecha,
					'pago_bold',
					id
				FROM pagos_bold
				WHERE id NOT IN (SELECT referencia_id FROM notificaciones WHERE referencia_tipo = 'pago_bold')";

		$stmt = Conexion::conectar()->prepare($sql);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

}