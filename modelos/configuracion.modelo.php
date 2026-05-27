<?php

require_once __DIR__ . "/conexion.php";

if (!class_exists('ModeloConfiguracion')) {

class ModeloConfiguracion
{

	/*=============================================
	OBTENER CONFIGURACIÓN
	=============================================*/

	static public function mdlObtenerConfiguracion()
	{

		$stmt = Conexion::conectar()->prepare("SELECT * FROM configuracion WHERE id = 1");

		$stmt->execute();

		return $stmt->fetch();

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR CONFIGURACIÓN
	=============================================*/

	static public function mdlActualizarConfiguracion($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre_empresa = :nombre_empresa, nit = :nit, direccion = :direccion, telefono = :telefono, correo = :correo, logo = :logo, impuesto_defecto = :impuesto_defecto, moneda = :moneda, formato_codigo_venta = :formato_codigo_venta, medios_pago = :medios_pago, tipo_codigo_producto = :tipo_codigo_producto, alerta_stock_bajo = :alerta_stock_bajo, umbral_stock_minimo = :umbral_stock_minimo, alerta_stock_agotado = :alerta_stock_agotado, alerta_actividades_pendientes = :alerta_actividades_pendientes, dias_antes_actividad = :dias_antes_actividad, alerta_gastos_proximos = :alerta_gastos_proximos, dias_antes_gasto = :dias_antes_gasto, mensaje_ticket = :mensaje_ticket, color_principal = :color_principal, color_secundario = :color_secundario, alerta_agente_ia = :alerta_agente_ia, control_caja = :control_caja, mensaje_recibido = :mensaje_recibido, mensaje_procesado = :mensaje_procesado, mensaje_confirmado = :mensaje_confirmado WHERE id = 1");

		$stmt->bindParam(":nombre_empresa", $datos["nombre_empresa"], PDO::PARAM_STR);
		$stmt->bindParam(":nit", $datos["nit"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":correo", $datos["correo"], PDO::PARAM_STR);
		$stmt->bindParam(":logo", $datos["logo"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto_defecto", $datos["impuesto_defecto"], PDO::PARAM_STR);
		$stmt->bindParam(":moneda", $datos["moneda"], PDO::PARAM_STR);
		$stmt->bindParam(":formato_codigo_venta", $datos["formato_codigo_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":medios_pago", $datos["medios_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_codigo_producto", $datos["tipo_codigo_producto"], PDO::PARAM_STR);
		$stmt->bindParam(":alerta_stock_bajo", $datos["alerta_stock_bajo"], PDO::PARAM_INT);
		$stmt->bindParam(":umbral_stock_minimo", $datos["umbral_stock_minimo"], PDO::PARAM_INT);
		$stmt->bindParam(":alerta_stock_agotado", $datos["alerta_stock_agotado"], PDO::PARAM_INT);
		$stmt->bindParam(":alerta_actividades_pendientes", $datos["alerta_actividades_pendientes"], PDO::PARAM_INT);
		$stmt->bindParam(":dias_antes_actividad", $datos["dias_antes_actividad"], PDO::PARAM_INT);
		$stmt->bindParam(":alerta_gastos_proximos", $datos["alerta_gastos_proximos"], PDO::PARAM_INT);
		$stmt->bindParam(":dias_antes_gasto", $datos["dias_antes_gasto"], PDO::PARAM_INT);
		$stmt->bindParam(":mensaje_ticket", $datos["mensaje_ticket"], PDO::PARAM_STR);
		$stmt->bindParam(":color_principal", $datos["color_principal"], PDO::PARAM_STR);
		$stmt->bindParam(":color_secundario", $datos["color_secundario"], PDO::PARAM_STR);
		$stmt->bindParam(":alerta_agente_ia", $datos["alerta_agente_ia"], PDO::PARAM_INT);
		$stmt->bindParam(":control_caja", $datos["control_caja"], PDO::PARAM_INT);
		$stmt->bindParam(":mensaje_recibido", $datos["mensaje_recibido"], PDO::PARAM_STR);
		$stmt->bindParam(":mensaje_procesado", $datos["mensaje_procesado"], PDO::PARAM_STR);
		$stmt->bindParam(":mensaje_confirmado", $datos["mensaje_confirmado"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return "ok";

		} else {
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

}

}