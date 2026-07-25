<?php

require_once "conexion.php";

class ModeloNotificaciones
{

	/*=============================================
	CREAR NOTIFICACIÓN
	=============================================*/

	static public function mdlCrearNotificacion($datos)
	{

		$stmt = Conexion::conectar()->prepare("INSERT INTO notificaciones(tipo, titulo, mensaje, referencia_tipo, referencia_id, id_bodega)
												VALUES (:tipo, :titulo, :mensaje, :referencia_tipo, :referencia_id, :id_bodega)");

		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":titulo", $datos["titulo"], PDO::PARAM_STR);
		$stmt->bindParam(":mensaje", $datos["mensaje"], PDO::PARAM_STR);
		$stmt->bindParam(":referencia_tipo", $datos["referencia_tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":referencia_id", $datos["referencia_id"], PDO::PARAM_INT);
		$stmt->bindParam(":id_bodega", $datos["id_bodega"], PDO::PARAM_INT);

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

		$idUsuario = $idUsuario ? intval($idUsuario) : 0;

		$sql = "SELECT n.*, 
                CASE WHEN nl.id_usuario IS NOT NULL THEN 1 ELSE 0 END as leida
                FROM notificaciones n
                CROSS JOIN configuracion c
                LEFT JOIN notificaciones_leidas nl ON n.id = nl.id_notificacion AND nl.id_usuario = :id_usuario
                LEFT JOIN notificaciones_eliminadas ne ON n.id = ne.id_notificacion AND ne.id_usuario = :id_usuario_elim
                LEFT JOIN usuarios u_viewer ON u_viewer.id = :id_usuario_viewer
                WHERE n.eliminada = 0
                  AND ne.id_usuario IS NULL
                  AND (n.tipo NOT IN ('orden_agente_ia', 'orden_creada') OR c.notif_orden_agente_ia = 1)
                  AND (n.referencia_tipo IS NULL OR n.referencia_tipo != 'pago_bold' OR c.notif_transaccion_bold = 1)
                  AND (n.referencia_tipo IS NULL OR n.referencia_tipo != 'solicitud_edicion' OR c.notif_solicitud_edicion = 1)
                  AND (n.referencia_tipo IS NULL OR n.referencia_tipo != 'solicitud_eliminacion' OR c.notif_solicitud_eliminacion = 1)
                  AND (n.tipo NOT IN ('stock_bajo', 'stock_agotado') OR n.id_bodega IS NULL OR u_viewer.id_bodega = n.id_bodega)";

		if ($soloNoLeidas) {
			// Si solo queremos no leídas, filtramos donde NO haya registro en la tabla de lectura
			$sql .= " AND nl.id_usuario IS NULL";
		}

		$sql .= " ORDER BY n.fecha DESC";

		if ($cantidad) {
			$sql .= " LIMIT :cantidad";
		}

		$stmt = Conexion::conectar()->prepare($sql);

		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario_elim", $idUsuario, PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario_viewer", $idUsuario, PDO::PARAM_INT);

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

		$idUsuario = intval($idUsuario);

		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total 
                                               FROM notificaciones n
                                               CROSS JOIN configuracion c
                                               LEFT JOIN notificaciones_leidas nl ON n.id = nl.id_notificacion AND nl.id_usuario = :id_usuario
                                               LEFT JOIN notificaciones_eliminadas ne ON n.id = ne.id_notificacion AND ne.id_usuario = :id_usuario_elim
                                               LEFT JOIN usuarios u_viewer ON u_viewer.id = :id_usuario_viewer
                                               WHERE n.eliminada = 0
                                                 AND ne.id_usuario IS NULL
                                                 AND nl.id_usuario IS NULL
                                                 AND (n.tipo NOT IN ('orden_agente_ia', 'orden_creada') OR c.notif_orden_agente_ia = 1)
                                                 AND (n.referencia_tipo IS NULL OR n.referencia_tipo != 'pago_bold' OR c.notif_transaccion_bold = 1)
                                                 AND (n.referencia_tipo IS NULL OR n.referencia_tipo != 'solicitud_edicion' OR c.notif_solicitud_edicion = 1)
                                                 AND (n.referencia_tipo IS NULL OR n.referencia_tipo != 'solicitud_eliminacion' OR c.notif_solicitud_eliminacion = 1)
                                                 AND (n.tipo NOT IN ('stock_bajo', 'stock_agotado') OR n.id_bodega IS NULL OR u_viewer.id_bodega = n.id_bodega)");

		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario_elim", $idUsuario, PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario_viewer", $idUsuario, PDO::PARAM_INT);
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

		// Insertamos todas las notificaciones que no estén ya leídas por este usuario y que no estén eliminadas
		$stmt = Conexion::conectar()->prepare("INSERT IGNORE INTO notificaciones_leidas (id_notificacion, id_usuario)
                                               SELECT id, :id_usuario FROM notificaciones WHERE eliminada = 0");

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
	ELIMINAR NOTIFICACIÓN (POR USUARIO)
	=============================================*/

	static public function mdlEliminarNotificacion($id, $idUsuario = null)
	{
		if (!$idUsuario && isset($_SESSION["id"])) {
			$idUsuario = $_SESSION["id"];
		}

		if (!$idUsuario) {
			return "error";
		}

		$stmt = Conexion::conectar()->prepare("INSERT IGNORE INTO notificaciones_eliminadas (id_notificacion, id_usuario) VALUES (:id, :id_usuario)");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);

		if ($stmt->execute()) {
			self::mdlMarcarComoLeida($id, $idUsuario);
			return "ok";
		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}


	/*=============================================
	ELIMINAR MÚLTIPLES NOTIFICACIONES (POR USUARIO)
	=============================================*/

	static public function mdlEliminarNotificaciones($idsJson, $idUsuario = null)
	{
		if (!$idUsuario && isset($_SESSION["id"])) {
			$idUsuario = $_SESSION["id"];
		}

		if (!$idUsuario) {
			return "error";
		}

		$ids = is_array($idsJson) ? $idsJson : json_decode($idsJson, true);

		if (empty($ids) || !is_array($ids)) {
			return "error";
		}

		$db = Conexion::conectar();
		$stmt = $db->prepare("INSERT IGNORE INTO notificaciones_eliminadas (id_notificacion, id_usuario) VALUES (:id, :id_usuario)");

		foreach ($ids as $id) {
			$idInt = intval($id);
			$stmt->bindParam(":id", $idInt, PDO::PARAM_INT);
			$stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
			$stmt->execute();

			self::mdlMarcarComoLeida($idInt, $idUsuario);
		}

		return "ok";
	}


	/*=============================================
	VERIFICAR SI YA EXISTE NOTIFICACIÓN DE STOCK
	=============================================*/

	static public function mdlExisteNotificacionStock($tipo, $idProducto, $idBodega = null, $referenciaTipo = 'producto')
	{

		// Verificar si existe la notificación independientemente de si está leída o no
		// Esto evita duplicar notificaciones de stock bajo/agotado aunque se marquen como leídas

		$condicionBodega = $idBodega !== null ? "AND id_bodega = :id_bodega" : "AND id_bodega IS NULL";

		$stmt = Conexion::conectar()->prepare("SELECT id FROM notificaciones
												WHERE tipo = :tipo
												AND referencia_tipo = :referencia_tipo
												AND referencia_id = :referencia_id
												$condicionBodega
												LIMIT 1");

		$stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
		$stmt->bindParam(":referencia_tipo", $referenciaTipo, PDO::PARAM_STR);
		$stmt->bindParam(":referencia_id", $idProducto, PDO::PARAM_INT);
		if ($idBodega !== null) {
			$stmt->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);
		}

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

	/*=============================================
	SINCRONIZAR NOTIFICACIONES DE LEADS WHATSAPP
	=============================================*/
	static public function mdlSincronizarLeadsWhatsApp()
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT l.id, l.titulo, l.origen, l.etapa, c.nombre as nombre_cliente 
			FROM crm_leads l 
			LEFT JOIN clientes c ON l.id_cliente = c.id 
			WHERE l.origen LIKE '%whatsapp%'
		");
		$stmt->execute();
		$leads = $stmt->fetchAll();

		if ($leads) {
			foreach ($leads as $lead) {
				$idLead = $lead["id"];
				$tipo = "lead_whatsapp";
				$referenciaTipo = "crm_lead";

				$existe = self::mdlExisteNotificacion($tipo, $idLead, $referenciaTipo);

				if (!$existe) {
					$nombreCliente = !empty($lead["nombre_cliente"]) ? $lead["nombre_cliente"] : "Cliente sin registrar";
					$tituloLead = !empty($lead["titulo"]) ? $lead["titulo"] : "Oportunidad Comercial";
					$mensaje = "Se ha registrado la oportunidad CRM: '" . $tituloLead . "' (" . $nombreCliente . ") desde WhatsApp.";

					$datosNotif = array(
						"tipo" => $tipo,
						"titulo" => "Nuevo Lead WhatsApp",
						"mensaje" => $mensaje,
						"referencia_tipo" => $referenciaTipo,
						"referencia_id" => $idLead,
						"id_bodega" => null
					);

					self::mdlCrearNotificacion($datosNotif);
				}
			}
		}

		return "ok";
	}

}