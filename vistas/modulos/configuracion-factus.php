<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"] != "jumperadmindev") {
    include "404.php";
    exit;
}

$configuracion = ControladorFactus::ctrObtenerConfiguracion();
$configuracionGlobal = ControladorConfiguracion::ctrObtenerConfiguracion();

// Si no existe la configuración, mostrar valores por defecto
if (!$configuracion) {
	$configuracion = array(
		"api_url" => "https://sandbox-api.factus.com.co",
		"client_id" => "",
		"client_secret" => "",
		"ambiente" => "sandbox",
		"activo" => 0,
		"access_token" => null,
		"token_expiracion" => null
	);
}

?>

<div class="content-wrapper">

	<section class="content-header">
		<h1>
			Configuración de Factus
			<small>Facturación Electrónica</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
			<li><a href="configuracion"><i class="fa fa-cog"></i> Configuración</a></li>
			<li class="active">Factus</li>
		</ol>
	</section>

	<section class="content">

		<div class="box box-primary">

			<div class="box-header with-border">
				<h3 class="box-title">
					<i class="fa fa-cloud"></i> Credenciales de la API de Factus
				</h3>
			</div>

			<form role="form" method="post">
				<?php CSRF::insertToken(); ?>

				<div class="box-body">

					<!-- Información sobre Factus -->
					<div class="alert alert-info">
						<h4><i class="icon fa fa-info-circle"></i> Información</h4>
						<p>
							Factus es el proveedor de servicios de facturación electrónica para Colombia (DIAN).
							Para obtener tus credenciales de API, debes registrarte en
							<a href="https://factus.com.co" target="_blank">https://factus.com.co</a>
						</p>
						<p class="mb-0">
							<strong>Documentación de la API:</strong>
							<a href="https://developers.factus.com.co" target="_blank">https://developers.factus.com.co</a>
						</p>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="checkbox">
								<label>
									<?php 
										$bloqueoActual = isset($configuracion['bloqueo_datos_emisor']) ? $configuracion['bloqueo_datos_emisor'] : 1;
										$checkedBloqueo = ($bloqueoActual == 0) ? 'checked' : '';
									?>
									<input type="checkbox" name="habilitarEdicionFactusGlobal" <?php echo $checkedBloqueo; ?>> <b>Habilitar Edición de Datos del Emisor en Configuración General</b>
								</label>
								<p class="help-block">Si activas esto, podrás editar los sgtes datos del emisor en Configuración. Estos datos deben coincidir con los registrados en Factus. 
									(-Tipo de persona, -Razon social o nombre de Empresa, -Nombre Comercial, -El NIT no se edita ni en Factus, -Responsabilidades Fiscales, -Responsabilidad tributaria, -Actividad Económica).</p>
							</div>
						</div>
					</div>

					<!-- Control de Caja y Vistas -->
					<h3 class="text-primary" style="margin-top: 20px;"><i class="fa fa-unlock-alt"></i> Control de Vistas</h3>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="controlCaja" value="1" <?php echo (!empty($configuracionGlobal["control_caja"]) && $configuracionGlobal["control_caja"] == 1) ? "checked" : ""; ?>>
										<strong>Activar Control de Apertura y Cierre de Caja (Arqueo)</strong>
									</label>
								</div>
								<p class="help-block">Si está activo, se restringirá la creación en Ventas, Órdenes, Facturación Electrónica, Notas de Crédito/Ajuste, Documento Soporte y Gastos hasta que el cajero abra su turno de caja.</p>
							</div>
							
							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="consultaVentas" value="1" <?php echo (!isset($configuracionGlobal["consulta_ventas"]) || $configuracionGlobal["consulta_ventas"] == 1) ? "checked" : ""; ?>>
										<strong>Activar "Consulta de Ventas"</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará la opción "Consulta de ventas" del menú lateral y de la matriz de permisos.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="activarSucursales" value="1" <?php echo (!isset($configuracionGlobal["activar_sucursales"]) || $configuracionGlobal["activar_sucursales"] == 1) ? "checked" : ""; ?>>
										<strong>Activar Sucursales</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultarán las opciones de "Sucursales" y "Traslados" del menú lateral, -El modulo "Traslados" en la matriz de permisos. -El filtro en las sgtes vistas: Ventas, Órdenes, Facturación Electrónica, Notas de Crédito/Ajuste, Doc Soporte, Gastos, Reportes e Historial stock. -La columna "Sucursal" en la vista Usuarios. -En el campo sucursal del modal para agregar y editar usuarios.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="crmActivo" value="1" <?php echo (!isset($configuracionGlobal["crm_activo"]) || $configuracionGlobal["crm_activo"] == 1) ? "checked" : ""; ?>>
										<strong>Activar CRM</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará el módulo "CRM / Pipeline" del menú lateral y de la matriz de permisos.</p>
							</div>


							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="graficaOrdenesManuales" value="1" <?php echo (!isset($configuracionGlobal["grafica_ordenes_manuales_activa"]) || $configuracionGlobal["grafica_ordenes_manuales_activa"] == 1) ? "checked" : ""; ?>>
										<strong>Activar gráfica de Órdenes manuales en Reportes</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará la gráfica "Análisis de Órdenes de Venta" en la vista de reportes.</p>
							</div>


							<br><h4>FACTURACIÓN ELECTRÓNICA:</h4>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="documentoSoporte" value="1" <?php echo (!isset($configuracionGlobal["documento_soporte_activo"]) || $configuracionGlobal["documento_soporte_activo"] == 1) ? "checked" : ""; ?>>
										<strong>Activar "Documento Soporte" y "Notas de Ajuste"</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultarán las opciones de "Documento Soporte" y "Notas de Ajuste" del menú lateral y de la matriz de permisos.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="facturacionElectronica" value="1" <?php echo (!isset($configuracionGlobal["facturacion_electronica_activa"]) || $configuracionGlobal["facturacion_electronica_activa"] == 1) ? "checked" : ""; ?>>
										<strong>Activar "Facturación Electrónica" y "Notas Crédito"</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultarán las opciones de "Facturas Electrónicas" y "Notas Crédito" del menú lateral y de la matriz de permisos y la gráfica de FE.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="botonConvertirFE" value="1" <?php echo (!isset($configuracionGlobal["boton_convertir_fe_activo"]) || $configuracionGlobal["boton_convertir_fe_activo"] == 1) ? "checked" : ""; ?>>
										<strong>Activar botón "Convertir a FE" en Órdenes</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará el botón "Convertir a FE" en la columna "Convertir" de la tabla de administrar órdenes de venta.</p>
							</div>

							<br><h4>IA:</h4>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="botonActualizarProducto" value="1" <?php echo (!isset($configuracionGlobal["boton_actualizar_producto_activo"]) || $configuracionGlobal["boton_actualizar_producto_activo"] == 1) ? "checked" : ""; ?>>
										<strong>Activar botón "Actualizar" en Productos</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará el botón "Actualizar" en la parte superior del listado de productos.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="seguimientoLeads" value="1" <?php echo (!isset($configuracionGlobal["seguimiento_leads_activo"]) || $configuracionGlobal["seguimiento_leads_activo"] == 1) ? "checked" : ""; ?>>
										<strong>Activar "Seguimiento a Leads"</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará la opción "Seguimiento a leads" del menú lateral y de la matriz de permisos.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="graficaAnalisisOrdenes" value="1" <?php echo (!isset($configuracionGlobal["grafica_analisis_ordenes_activa"]) || $configuracionGlobal["grafica_analisis_ordenes_activa"] == 1) ? "checked" : ""; ?>>
										<strong>Activar gráfica "Análisis de Órdenes manuales y con IA" en Reportes</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará la gráfica "Análisis de Órdenes de Venta" en la vista de reportes.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="columnaSeguimiento" value="1" <?php echo (!isset($configuracionGlobal["columna_seguimiento_activa"]) || $configuracionGlobal["columna_seguimiento_activa"] == 1) ? "checked" : ""; ?>>
										<strong>Activar columna "Seguimiento" en Órdenes</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará la columna de seguimiento en la tabla de administrar órdenes de venta.</p>
							</div>

							<div class="form-group">
								<div class="checkbox">
									<label style="font-weight: normal; cursor: pointer; font-size: 16px;">
										<input type="checkbox" name="columnaNotasClienteActiva" value="1" <?php echo (!isset($configuracionGlobal["columna_notas_cliente_activa"]) || $configuracionGlobal["columna_notas_cliente_activa"] == 1) ? "checked" : ""; ?>>
										<strong>Activar la columna "Notas del cliente" en Ordenes, Ventas y FE</strong>
									</label>
								</div>
								<p class="help-block">Si está desactivado, se ocultará la columna "Notas del cliente" en las vistas de órdenes, ventas y facturas electrónicas.</p>
							</div>

							<div class="box box-warning" style="margin-top: 15px;">
								<div class="box-header with-border">
									<h3 class="box-title" style="font-size: 16px;"><i class="fa fa-bell"></i> Visibilidad de Notificaciones</h3>
								</div>
								<div class="box-body">
									<div class="checkbox">
										<label style="font-weight: normal; cursor: pointer;">
											<input type="checkbox" name="notif_orden_agente_ia" value="1" <?php echo (!isset($configuracionGlobal["notif_orden_agente_ia"]) || $configuracionGlobal["notif_orden_agente_ia"] == 1) ? "checked" : ""; ?>>
											<strong>Mostrar notificación: "Orden Agente IA"</strong>
										</label>
									</div>
									<div class="checkbox">
										<label style="font-weight: normal; cursor: pointer;">
											<input type="checkbox" name="notif_transaccion_bold" value="1" <?php echo (!isset($configuracionGlobal["notif_transaccion_bold"]) || $configuracionGlobal["notif_transaccion_bold"] == 1) ? "checked" : ""; ?>>
											<strong>Mostrar notificación: "Transacción Bold"</strong>
										</label>
									</div>
									<div class="checkbox">
										<label style="font-weight: normal; cursor: pointer;">
											<input type="checkbox" name="notif_solicitud_edicion" value="1" <?php echo (!isset($configuracionGlobal["notif_solicitud_edicion"]) || $configuracionGlobal["notif_solicitud_edicion"] == 1) ? "checked" : ""; ?>>
											<strong>Mostrar notificación: "Solicitud edición de pedido"</strong>
										</label>
									</div>
									<div class="checkbox">
										<label style="font-weight: normal; cursor: pointer;">
											<input type="checkbox" name="notif_solicitud_eliminacion" value="1" <?php echo (!isset($configuracionGlobal["notif_solicitud_eliminacion"]) || $configuracionGlobal["notif_solicitud_eliminacion"] == 1) ? "checked" : ""; ?>>
											<strong>Mostrar notificación: "Solicitud eliminación de pedido"</strong>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<hr>

					<!-- Estado de conexión -->
					<?php if ($configuracion['activo'] == 1): ?>
						<div class="alert alert-success">
							<h4><i class="icon fa fa-check"></i> Estado: Activo</h4>
							<p class="mb-0">La integración con Factus está activa y funcionando.</p>
							<?php if ($configuracion['access_token']): ?>
								<p class="mb-0">
									<small>
										<strong>Token válido hasta:</strong>
										<?php echo $configuracion['token_expiracion'] ? date('d/m/Y H:i:s', strtotime($configuracion['token_expiracion'])) : 'No disponible'; ?>
									</small>
								</p>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<div class="alert alert-warning">
							<h4><i class="icon fa fa-warning"></i> Estado: Inactivo</h4>
							<p class="mb-0">La integración con Factus está desactivada. Actívala para usar facturación electrónica.</p>
						</div>
					<?php endif; ?>

					<div class="row">

						<!-- Ambiente -->
						<div class="col-md-6">
							<div class="form-group">
								<label>Ambiente de Trabajo *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-server"></i></span>
									<select class="form-control" name="ambiente" id="ambiente" required>
										<option value="sandbox" <?php echo ($configuracion['ambiente'] == 'sandbox') ? 'selected' : ''; ?>>
											Sandbox (Pruebas)
										</option>
										<option value="produccion" <?php echo ($configuracion['ambiente'] == 'produccion') ? 'selected' : ''; ?>>
											Producción
										</option>
									</select>
								</div>
								<p class="help-block">
									<strong>Sandbox:</strong> Para pruebas y desarrollo<br>
									<strong>Producción:</strong> Para facturación real ante la DIAN
								</p>
							</div>
						</div>

						<!-- URL de la API -->
						<div class="col-md-6">
							<div class="form-group">
								<label>URL de la API *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-link"></i></span>
									<input type="url" class="form-control" name="apiUrl" id="apiUrl"
										value="<?php echo $configuracion['api_url']; ?>"
										placeholder="https://api.factus.com.co" required>
								</div>
								<p class="help-block">
									<strong>Sandbox:</strong> https://api-sandbox.factus.com.co<br>
									<strong>Producción:</strong> https://api.factus.com.co
								</p>
							</div>
						</div>

					</div>

					<div class="row">

						<!-- Client ID -->
						<div class="col-md-6">
							<div class="form-group">
								<label>Client ID *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user"></i></span>
									<input type="text" class="form-control" name="clientId"
										value="<?php echo $configuracion['client_id']; ?>"
										placeholder="Tu Client ID de Factus" required>
								</div>
								<p class="help-block">El Client ID para autenticación OAuth2</p>
							</div>
						</div>

						<!-- Client Secret -->
						<div class="col-md-6">
							<div class="form-group">
								<label>Client Secret *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-key"></i></span>
									<input type="password" class="form-control" name="clientSecret"
										value="<?php echo $configuracion['client_secret']; ?>"
										placeholder="Tu Client Secret de Factus" required>
								</div>
								<p class="help-block">El Client Secret para autenticación OAuth2</p>
							</div>
						</div>

					</div>

					<div class="row">
						<!-- Email (Username) -->
						<div class="col-md-6">
							<div class="form-group">
								<label>Email (Username) </label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
									<input type="email" class="form-control" name="username"
										value="<?php echo $configuracion['username']; ?>"
										placeholder="Email de acceso a Factus">
								</div>
								<p class="help-block">Requerido para Sandbox (Grant Type: Password)</p>
							</div>
						</div>

						<!-- Password -->
						<div class="col-md-6">
							<div class="form-group">
								<label>Contraseña </label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-lock"></i></span>
									<input type="password" class="form-control" name="password"
										value="<?php echo $configuracion['password']; ?>"
										placeholder="Contraseña de acceso a Factus">
								</div>
								<p class="help-block">Requerido para Sandbox (Grant Type: Password)</p>
							</div>
						</div>
					</div>





					<!-- Rango de Numeración por Defecto -->
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Rango de Numeración por Defecto</label>
								<select class="form-control" name="rangoNumeracionId">
									<option value="">-- Seleccionar Rango --</option>
									<?php
									$rangos = ModeloFactus::mdlObtenerRangos();
									foreach ($rangos as $rango) {
										$selected = ($configuracion['rango_numeracion_id'] == $rango['id_factus']) ? 'selected' : '';
										$estado = $rango['estado'] ? '' : ' (Inactivo)';
										echo "<option value='{$rango['id_factus']}' $selected>{$rango['prefijo']} ({$rango['numero_desde']}-{$rango['numero_hasta']}){$estado}</option>";
									}
									?>
								</select>
								<p class="help-block">
									<i class="fa fa-info-circle"></i>
									Este rango se usará automáticamente al generar facturas electrónicas.
									Prefijo actual: <strong><?php 
									if (!empty($configuracion['rango_numeracion_id'])) {
										$rangoActual = array_filter($rangos, fn($r) => $r['id_factus'] == $configuracion['rango_numeracion_id']);
										$rangoActual = reset($rangoActual);
										echo $rangoActual ? $rangoActual['prefijo'] : 'No configurado';
									} else {
										echo 'No configurado';
									}
									?></strong>
								</p>
							</div>
						</div>
					</div>

					<!-- Activar/Desactivar Factus -->
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="factusActivo" value="1"
											<?php echo ($configuracion['activo'] == 1) ? 'checked' : ''; ?>>
										<strong>Activar integración con Factus</strong>
									</label>
								</div>
								<p class="help-block">
									<i class="fa fa-info-circle"></i>
									Al activar, el sistema usará Factus para la facturación electrónica.
									Asegúrate de que las credenciales sean correctas antes de activar.
								</p>
							</div>
						</div>
					</div>

					<!-- Botón de prueba de conexión -->
					<div class="row">
						<div class="col-md-12">
							<div class="callout callout-info">
								<h4><i class="fa fa-lightbulb-o"></i> Probar Conexión</h4>
								<p>Antes de guardar, puedes probar la conexión con la API de Factus usando tus credenciales.</p>
								<button type="button" class="btn btn-info" id="btnProbarConexion">
									<i class="fa fa-refresh"></i> Probar Conexión
								</button>

								<style>
									#btnAutenticar {
										background-color: #10b981 !important;
										border-color: #059669 !important;
										font-weight: bold;
										padding: 10px 20px;
										font-size: 13px;
										border-radius: 4px;
										transition: background-color 0.2s ease, transform 0.1s ease;
										margin-left: 10px;
									}
									#btnAutenticar:hover, #btnAutenticar:focus, #btnAutenticar:active {
										background-color: #059669 !important;
										border-color: #047857 !important;
										color: #fff !important;
									}
									#btnAutenticar:active {
										transform: scale(0.98);
									}
								</style>
								<button type="button" class="btn btn-success" id="btnAutenticar">
									<i class="fa fa-key"></i> Autenticar y Obtener Tokens
								</button>

								<div id="resultadoPrueba" style="margin-top: 15px;"></div>
							</div>
						</div>
					</div>

				</div>

				<div class="box-footer">
					<button type="submit" class="btn btn-primary btn-lg" style="padding: 10px 30px; font-size: 18px;">
						<i class="fa fa-save"></i> Guardar Configuración
					</button>
				</div>

			</form>

		</div>

		<!-- Sección de Sincronización de Datos -->
		<?php if ($configuracion['activo'] == 1): ?>
		<div class="box box-success">
			<div class="box-header with-border">
				<h3 class="box-title">
					<i class="fa fa-refresh"></i> Sincronización de Datos de Referencia
				</h3>
			</div>

			<div class="box-body">

				<div class="alert alert-info">
					<h4><i class="icon fa fa-info-circle"></i> Información</h4>
					<p>
						Sincroniza los datos de referencia desde Factus para mantener actualizados los catálogos
						de municipios, tributos y unidades de medida según los estándares de la DIAN.
					</p>
				</div>

				<div class="row">

					<!-- Sincronizar Municipios -->
					<div class="col-md-6">
						<div class="box box-widget">
							<div class="box-header with-border">
								<h3 class="box-title"><i class="fa fa-map-marker"></i> Municipios</h3>
							</div>
							<div class="box-body">
								<p>Sincroniza el catálogo de municipios de Colombia con sus códigos DIAN.</p>
								<?php
								$ultimaSincMunicipios = ModeloFactus::mdlObtenerUltimaSincronizacion('municipios');
								if ($ultimaSincMunicipios):
								?>
								<p class="text-muted">
									<small>
										<i class="fa fa-clock-o"></i> Última sincronización:
										<?php echo date('d/m/Y H:i:s', strtotime($ultimaSincMunicipios['fecha_sincronizacion'])); ?>
										<br>
										<i class="fa fa-check"></i>
										<?php echo $ultimaSincMunicipios['registros_insertados']; ?> insertados,
										<?php echo $ultimaSincMunicipios['registros_actualizados']; ?> actualizados
									</small>
								</p>
								<?php else: ?>
								<p class="text-warning"><small><i class="fa fa-warning"></i> No se ha sincronizado aún</small></p>
								<?php endif; ?>
								<button type="button" class="btn btn-success btn-block" id="btnSincronizarMunicipios">
									<i class="fa fa-refresh"></i> Sincronizar Municipios
								</button>
								<div id="resultadoMunicipios" style="margin-top: 10px;"></div>
							</div>
						</div>
					</div>

					<!-- Sincronizar Tributos -->
					<div class="col-md-6">
						<div class="box box-widget">
							<div class="box-header with-border">
								<h3 class="box-title"><i class="fa fa-percent"></i> Tributos</h3>
							</div>
							<div class="box-body">
								<p>Sincroniza el catálogo de tributos e impuestos (IVA, INC, etc).</p>
								<?php
								$ultimaSincTributos = ModeloFactus::mdlObtenerUltimaSincronizacion('tributos');
								if ($ultimaSincTributos):
								?>
								<p class="text-muted">
									<small>
										<i class="fa fa-clock-o"></i> Última sincronización:
										<?php echo date('d/m/Y H:i:s', strtotime($ultimaSincTributos['fecha_sincronizacion'])); ?>
										<br>
										<i class="fa fa-check"></i>
										<?php echo $ultimaSincTributos['registros_insertados']; ?> insertados,
										<?php echo $ultimaSincTributos['registros_actualizados']; ?> actualizados
									</small>
								</p>
								<?php else: ?>
								<p class="text-warning"><small><i class="fa fa-warning"></i> No se ha sincronizado aún</small></p>
								<?php endif; ?>
								<button type="button" class="btn btn-success btn-block" id="btnSincronizarTributos">
									<i class="fa fa-refresh"></i> Sincronizar Tributos
								</button>
								<div id="resultadoTributos" style="margin-top: 10px;"></div>
							</div>
						</div>
					</div>

					<!-- Sincronizar Unidades -->
					<div class="col-md-6">
						<div class="box box-widget">
							<div class="box-header with-border">
								<h3 class="box-title"><i class="fa fa-balance-scale"></i> Unidades de Medida</h3>
							</div>
							<div class="box-body">
								<p>Sincroniza el catálogo de unidades de medida (kg, m, und, etc).</p>
								<?php
								$ultimaSincUnidades = ModeloFactus::mdlObtenerUltimaSincronizacion('unidades');
								if ($ultimaSincUnidades):
								?>
								<p class="text-muted">
									<small>
										<i class="fa fa-clock-o"></i> Última sincronización:
										<?php echo date('d/m/Y H:i:s', strtotime($ultimaSincUnidades['fecha_sincronizacion'])); ?>
										<br>
										<i class="fa fa-check"></i>
										<?php echo $ultimaSincUnidades['registros_insertados']; ?> insertados,
										<?php echo $ultimaSincUnidades['registros_actualizados']; ?> actualizados
									</small>
								</p>
								<?php else: ?>
								<p class="text-warning"><small><i class="fa fa-warning"></i> No se ha sincronizado aún</small></p>
								<?php endif; ?>
								<button type="button" class="btn btn-success btn-block" id="btnSincronizarUnidades">
									<i class="fa fa-refresh"></i> Sincronizar Unidades
								</button>
								<div id="resultadoUnidades" style="margin-top: 10px;"></div>
							</div>
						</div>
					</div>

				</div>

				<!-- Nueva Fila: Rangos de Numeración -->
				<div class="row" style="margin-top: 20px;">
					<div class="col-md-12">
						<div class="box box-primary">
							<div class="box-header with-border">
								<h3 class="box-title"><i class="fa fa-list-ol"></i> Rangos de Numeración</h3>
							</div>
							<div class="box-body">
								<div class="row">
									<div class="col-md-4">
										<p>Sincroniza los rangos de numeración autorizados y asociados a tu cuenta de Factus.</p>
										<?php
										$ultimaSincRangos = ModeloFactus::mdlObtenerUltimaSincronizacion('rangos');
										if ($ultimaSincRangos):
										?>
										<p class="text-muted">
											<small>
												<i class="fa fa-clock-o"></i> Última sincronización:
												<?php echo date('d/m/Y H:i:s', strtotime($ultimaSincRangos['fecha_sincronizacion'])); ?>
											</small>
										</p>
										<?php else: ?>
										<p class="text-warning"><small><i class="fa fa-warning"></i> No se ha sincronizado aún</small></p>
										<?php endif; ?>
										
										<button type="button" class="btn btn-primary btn-block" id="btnSincronizarRangos">
											<i class="fa fa-refresh"></i> Sincronizar Rangos
										</button>
										<div id="resultadoRangos" style="margin-top: 10px;"></div>
									</div>
									
									<div class="col-md-8">
										<h4>Rangos Disponibles:</h4>
										<div class="table-responsive">
											<table class="table table-bordered table-striped">
												<thead>
													<tr>
														<th>Prefijo</th>
														<th>Desde</th>
														<th>Hasta</th>
														<th>Resolución</th>
														<th>Estado</th>
														<th>ID Factus</th>
													</tr>
												</thead>
												<tbody>
													<?php
													$rangos = ModeloFactus::mdlObtenerRangos();
													if (count($rangos) > 0) {
														foreach ($rangos as $rango) {
															$estado = $rango['estado'] ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>';
															echo "<tr>
																<td><b>{$rango['prefijo']}</b></td>
																<td>{$rango['numero_desde']}</td>
																<td>{$rango['numero_hasta']}</td>
																<td><small>{$rango['resolucion']}</small></td>
																<td>{$estado}</td>
																<td><code>{$rango['id_factus']}</code></td>
															</tr>";
														}
													} else {
														echo "<tr><td colspan='6' class='text-center'>No hay rangos sincronizados. Haz clic en 'Sincronizar Rangos'.</td></tr>";
													}
													?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>


			</div>
		</div>
		<?php endif; ?>

	</section>

</div>

<!-- Script para cambiar automáticamente la URL según el ambiente -->
<script>
$(document).ready(function() {

	// Cambiar URL automáticamente según el ambiente seleccionado
	$('#ambiente').on('change', function() {
		var ambiente = $(this).val();
		var urlInput = $('#apiUrl');

		if (ambiente === 'sandbox') {
			urlInput.val('https://api-sandbox.factus.com.co');
		} else if (ambiente === 'produccion') {
			urlInput.val('https://api.factus.com.co');
		}
	});

	// Probar conexión con Factus
	$('#btnProbarConexion').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoPrueba');

		// Obtener valores actuales del formulario
		var apiUrl = $('#apiUrl').val();
		var clientId = $('input[name="clientId"]').val();
		var clientSecret = $('input[name="clientSecret"]').val();

		// Validar que los campos estén completos
		if (!apiUrl || !clientId || !clientSecret) {
			resultado.html('<div class="alert alert-warning">' +
				'<i class="fa fa-warning"></i> Por favor, completa todos los campos antes de probar la conexión.' +
				'</div>');
			return;
		}

		// Deshabilitar botón y mostrar loading
		btn.prop('disabled', true);
		btn.html('<i class="fa fa-spinner fa-spin"></i> Probando...');
		resultado.html('<div class="alert alert-info">' +
			'<i class="fa fa-spinner fa-spin"></i> Conectando con Factus...' +
			'</div>');

		// Realizar petición AJAX
		$.ajax({
			url: 'ajax/factus.ajax.php',
			method: 'POST',
			data: {
				accion: 'probarConexion',
				apiUrl: apiUrl,
				clientId: clientId,
				clientSecret: clientSecret,
				csrf_token: $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function(response) {
				if (response.error) {
					resultado.html('<div class="alert alert-danger">' +
						'<i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje +
						(response.detalles ? '<br><small>' + response.detalles + '</small>' : '') +
						'</div>');
				} else {
					resultado.html('<div class="alert alert-success">' +
						'<i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
						'</div>');
				}
			},
			error: function() {
				resultado.html('<div class="alert alert-danger">' +
					'<i class="fa fa-times"></i> Error al conectar con el servidor. Por favor, intenta de nuevo.' +
					'</div>');
			},
			complete: function() {
				// Restaurar botón
				btn.prop('disabled', false);
				btn.html('<i class="fa fa-refresh"></i> Probar Conexión');
			}
		});
	});

	// Autenticar y obtener tokens
	$('#btnAutenticar').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoPrueba');
		// Obtener valores actuales del formulario
		var apiUrl = $('#apiUrl').val();
		var clientId = $('input[name="clientId"]').val();
		var clientSecret = $('input[name="clientSecret"]').val();
		var username = $('input[name="username"]').val();
		var password = $('input[name="password"]').val();
		var ambiente = $('#ambiente').val();
		var rangoNumeracionId = $('select[name="rangoNumeracionId"]').val();

		// Validar que los campos estén completos
		if (!apiUrl || !clientId || !clientSecret) {
			resultado.html('<div class="alert alert-warning">' +
				'<i class="fa fa-warning"></i> Por favor, completa todos los campos (Client ID, Secret y URL) antes de autenticar.' +
				'</div>');
			return;
		}

		// Confirmar acción
		swal({
			title: "¿Autenticar con Factus?",
			text: "Esto obtendrá nuevos tokens de acceso y los guardará en el sistema.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3c8dbc",
			confirmButtonText: "Sí, autenticar",
			cancelButtonText: "Cancelar"
		}).then(function(result) {
			if (result.value) {
				// Deshabilitar botón y mostrar loading
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Autenticando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Obteniendo tokens de Factus...' +
					'</div>');

				// Realizar petición AJAX
				$.ajax({
					url: 'ajax/factus.ajax.php',
					method: 'POST',
					data: {
						accion: 'autenticar',
						apiUrl: apiUrl,
						clientId: clientId,
						clientSecret: clientSecret,
						username: username,
						password: password,
						ambiente: ambiente,
						rangoNumeracionId: rangoNumeracionId,
						csrf_token: $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function(response) {
						if (response.error) {
							resultado.html('<div class="alert alert-danger">' +
								'<i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje +
								(response.detalles ? '<br><small>' + response.detalles + '</small>' : '') +
								'</div>');
						} else {
							resultado.html('<div class="alert alert-success">' +
								'<i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
								'<br><small>Token válido hasta: ' + response.expiracion + '</small>' +
								'</div>');
							// Recargar página después de 2 segundos
							setTimeout(function() {
								location.reload();
							}, 2000);
						}
					},
					error: function() {
						resultado.html('<div class="alert alert-danger">' +
							'<i class="fa fa-times"></i> Error al conectar con el servidor. Por favor, intenta de nuevo.' +
							'</div>');
					},
					complete: function() {
						// Restaurar botón
						btn.prop('disabled', false);
						btn.html('<i class="fa fa-key"></i> Autenticar y Obtener Tokens');
					}
				});
			}
		});
	});

	// Sincronizar Municipios
	$('#btnSincronizarMunicipios').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoMunicipios');

		// Confirmar acción
		swal({
			title: "¿Sincronizar Municipios?",
			text: "Esto descargará y actualizará el catálogo de municipios desde Factus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3c8dbc",
			confirmButtonText: "Sí, sincronizar",
			cancelButtonText: "Cancelar"
		}).then(function(result) {
			if (result.value) {
				// Deshabilitar botón
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando municipios desde Factus...' +
					'</div>');

				// Realizar petición AJAX
				$.ajax({
					url: 'ajax/factus.ajax.php',
					method: 'POST',
					data: { 
						accion: 'sincronizarMunicipios',
						csrf_token: $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function(response) {
						if (response.error) {
							var errorHtml = '<div class="alert alert-danger">' +
								'<i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje;
							if (response.detalles) {
								errorHtml += '<br><small><strong>Detalles:</strong> ' + response.detalles + '</small>';
							}
							if (response.codigo_http) {
								errorHtml += '<br><small><strong>Código HTTP:</strong> ' + response.codigo_http + '</small>';
							}
							errorHtml += '</div>';
							resultado.html(errorHtml);
						} else {
							resultado.html('<div class="alert alert-success">' +
								'<i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
								'<br><small>' + response.insertados + ' insertados, ' +
								response.actualizados + ' actualizados</small>' +
								'</div>');
							// Recargar página después de 2 segundos
							setTimeout(function() {
								location.reload();
							}, 2000);
						}
					},
					error: function() {
						resultado.html('<div class="alert alert-danger">' +
							'<i class="fa fa-times"></i> Error al conectar con el servidor.' +
							'</div>');
					},
					complete: function() {
						btn.prop('disabled', false);
						btn.html('<i class="fa fa-refresh"></i> Sincronizar Municipios');
					}
				});
			}
		});
	});

	// Sincronizar Tributos
	$('#btnSincronizarTributos').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoTributos');

		// Confirmar acción
		swal({
			title: "¿Sincronizar Tributos?",
			text: "Esto descargará y actualizará el catálogo de tributos desde Factus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3c8dbc",
			confirmButtonText: "Sí, sincronizar",
			cancelButtonText: "Cancelar"
		}).then(function(result) {
			if (result.value) {
				// Deshabilitar botón
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando tributos desde Factus...' +
					'</div>');

				// Realizar petición AJAX
				$.ajax({
					url: 'ajax/factus.ajax.php',
					method: 'POST',
					data: { 
						accion: 'sincronizarTributos',
						csrf_token: $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function(response) {
						if (response.error) {
							var errorHtml = '<div class="alert alert-danger">' +
								'<i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje;
							if (response.detalles) {
								errorHtml += '<br><small><strong>Detalles:</strong> ' + response.detalles + '</small>';
							}
							if (response.codigo_http) {
								errorHtml += '<br><small><strong>Código HTTP:</strong> ' + response.codigo_http + '</small>';
							}
							errorHtml += '</div>';
							resultado.html(errorHtml);
						} else {
							resultado.html('<div class="alert alert-success">' +
								'<i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
								'<br><small>' + response.insertados + ' insertados, ' +
								response.actualizados + ' actualizados</small>' +
								'</div>');
							// Recargar página después de 2 segundos
							setTimeout(function() {
								location.reload();
							}, 2000);
						}
					},
					error: function() {
						resultado.html('<div class="alert alert-danger">' +
							'<i class="fa fa-times"></i> Error al conectar con el servidor.' +
							'</div>');
					},
					complete: function() {
						btn.prop('disabled', false);
						btn.html('<i class="fa fa-refresh"></i> Sincronizar Tributos');
					}
				});
			}
		});
	});

	// Sincronizar Unidades
	$('#btnSincronizarUnidades').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoUnidades');

		// Confirmar acción
		swal({
			title: "¿Sincronizar Unidades?",
			text: "Esto descargará y actualizará el catálogo de unidades de medida desde Factus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3c8dbc",
			confirmButtonText: "Sí, sincronizar",
			cancelButtonText: "Cancelar"
		}).then(function(result) {
			if (result.value) {
				// Deshabilitar botón
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando unidades desde Factus...' +
					'</div>');

				// Realizar petición AJAX
				$.ajax({
					url: 'ajax/factus.ajax.php',
					method: 'POST',
					data: { 
						accion: 'sincronizarUnidades',
						csrf_token: $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function(response) {
						if (response.error) {
							var errorHtml = '<div class="alert alert-danger">' +
								'<i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje;
							if (response.detalles) {
								errorHtml += '<br><small><strong>Detalles:</strong> ' + response.detalles + '</small>';
							}
							if (response.codigo_http) {
								errorHtml += '<br><small><strong>Código HTTP:</strong> ' + response.codigo_http + '</small>';
							}
							errorHtml += '</div>';
							resultado.html(errorHtml);
						} else {
							resultado.html('<div class="alert alert-success">' +
								'<i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
								'<br><small>' + response.insertados + ' insertados, ' +
								response.actualizados + ' actualizados</small>' +
								'</div>');
							// Recargar página después de 2 segundos
							setTimeout(function() {
								location.reload();
							}, 2000);
						}
					},
					error: function() {
						resultado.html('<div class="alert alert-danger">' +
							'<i class="fa fa-times"></i> Error al conectar con el servidor.' +
							'</div>');
					},
					complete: function() {
						btn.prop('disabled', false);
						btn.html('<i class="fa fa-refresh"></i> Sincronizar Unidades');
					}
				});
			}
		});
	});

	// Sincronizar Rangos
	$('#btnSincronizarRangos').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoRangos');

		// Confirmar acción
		swal({
			title: "¿Sincronizar Rangos?",
			text: "Esto descargará los rangos de numeración autorizados desde Factus.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3c8dbc",
			confirmButtonText: "Sí, sincronizar",
			cancelButtonText: "Cancelar"
		}).then(function(result) {
			if (result.value) {
				// Deshabilitar botón
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando rangos...' +
					'</div>');

				// Realizar petición AJAX
				$.ajax({
					url: 'ajax/factus.ajax.php',
					method: 'POST',
					data: { 
						accion: 'sincronizarRangos',
						csrf_token: $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function(response) {
						if (response.error) {
							var errorHtml = '<div class="alert alert-danger">' +
								'<i class="fa fa-times"></i> <strong>Error:</strong> ' + response.mensaje;
							if (response.detalles) {
								errorHtml += '<br><small><strong>Detalles:</strong> ' + response.detalles + '</small>';
							}
							errorHtml += '</div>';
							resultado.html(errorHtml);
						} else {
							resultado.html('<div class="alert alert-success">' +
								'<i class="fa fa-check"></i> <strong>¡Éxito!</strong> ' + response.mensaje +
								'<br><small>' + response.insertados + ' insertados, ' +
								response.actualizados + ' actualizados</small>' +
								'</div>');
							// Recargar página después de 2 segundos
							setTimeout(function() {
								location.reload();
							}, 2000);
						}
					},
					error: function() {
						resultado.html('<div class="alert alert-danger">' +
							'<i class="fa fa-times"></i> Error al conectar con el servidor.' +
							'</div>');
					},
					complete: function() {
						btn.prop('disabled', false);
						btn.html('<i class="fa fa-refresh"></i> Sincronizar Rangos');
					}
				});
			}
		});
	});

});
</script>

<?php

$actualizar = new ControladorFactus();
$actualizar->ctrActualizarConfiguracion();

?>
