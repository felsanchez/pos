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
		<h1 style="font-weight: 700; color: #1e293b;">
			Configuración General del Sistema
			<small>Facturación Electrónica y Módulos</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
			<li><a href="configuracion"><i class="fa fa-cog"></i> Configuración</a></li>
			<li class="active">Factus & Parámetros</li>
		</ol>
	</section>

	<section class="content" style="padding-top: 15px;">

		<form role="form" method="post">
			<?php CSRF::insertToken(); ?>

			<!-- ===================================================
			SECCIÓN 1: CONTROL DE VISTAS Y MÓDULOS DEL SISTEMA
			=================================================== -->
			<div class="box box-primary" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 4px solid #3c8dbc; margin-bottom: 25px;">
				<div class="box-header with-border" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 8px 8px 0 0;">
					<h3 class="box-title" style="font-weight: 700; color: #1e293b; font-size: 17px; display: flex; align-items: center; gap: 8px;">
						<i class="fa fa-sliders text-primary"></i> 1. Control de Vistas y Módulos del Sistema
					</h3>
				</div>

				<div class="box-body" style="padding: 20px;">
					
					<div class="well well-sm" style="background: #f1f5f9; border-color: #cbd5e1; margin-bottom: 20px; border-radius: 6px;">
						<div class="checkbox" style="margin: 5px 0;">
							<label style="font-weight: 700; font-size: 15px; color: #0f172a; cursor: pointer;">
								<?php 
									$bloqueoActual = isset($configuracion['bloqueo_datos_emisor']) ? $configuracion['bloqueo_datos_emisor'] : 1;
									$checkedBloqueo = ($bloqueoActual == 0) ? 'checked' : '';
								?>
								<input type="checkbox" name="habilitarEdicionFactusGlobal" <?php echo $checkedBloqueo; ?>> 
								Habilitar Edición de Datos del Emisor en Configuración General
							</label>
							<p class="help-block" style="margin-top: 5px; color: #64748b;">
								Si activas esto, podrás editar los datos del emisor en Configuración (Tipo de persona, Razón social, Nombre comercial, Responsabilidades fiscales, etc.).
							</p>
						</div>
					</div>

					<div class="row">
						<!-- Columna Izquierda: Vistas y Navegación -->
						<div class="col-md-6">
							<div class="panel panel-default" style="border-radius: 6px; border-color: #e2e8f0;">
								<div class="panel-heading" style="background: #fafafa; font-weight: 700; color: #334155;">
									<i class="fa fa-desktop text-primary"></i> Vistas y Módulos Principales
								</div>
								<div class="panel-body">
									
									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="controlCaja" value="1" <?php echo (!empty($configuracionGlobal["control_caja"]) && $configuracionGlobal["control_caja"] == 1) ? "checked" : ""; ?>>
												Activar Control de Apertura y Cierre de Caja (Arqueo)
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Restringe la creación en Ventas, Órdenes, FE, etc. hasta que el cajero abra turno.</p>
									</div>

									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="consultaVentas" value="1" <?php echo (!isset($configuracionGlobal["consulta_ventas"]) || $configuracionGlobal["consulta_ventas"] == 1) ? "checked" : ""; ?>>
												Activar "Consulta de Ventas"
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Visibilidad de "Consulta de Ventas" en menú lateral y matriz de permisos.</p>
									</div>

									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="activarSucursales" value="1" <?php echo (!isset($configuracionGlobal["activar_sucursales"]) || $configuracionGlobal["activar_sucursales"] == 1) ? "checked" : ""; ?>>
												Activar Sucursales
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Muestra/oculta opciones de Sucursales, Traslados y filtros correspondientes.</p>
									</div>

									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="crmActivo" value="1" <?php echo (!isset($configuracionGlobal["crm_activo"]) || $configuracionGlobal["crm_activo"] == 1) ? "checked" : ""; ?>>
												Activar CRM / Pipeline
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Visibilidad del módulo "CRM" en el menú lateral y permisos.</p>
									</div>

									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="baseConocimientoActiva" value="1" <?php echo (!isset($configuracionGlobal["base_conocimiento_activa"]) || $configuracionGlobal["base_conocimiento_activa"] == 1) ? "checked" : ""; ?>>
												Activar Base de Conocimiento
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Visibilidad del módulo "Base de Conocimiento" en el menú lateral.</p>
									</div>

									<div class="form-group" style="margin-bottom: 0;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="leadsWhatsappActivos" value="1" <?php echo (!isset($configuracionGlobal["leads_whatsapp_activos"]) || $configuracionGlobal["leads_whatsapp_activos"] == 1) ? "checked" : ""; ?>>
												Activar Leads Whatsapp por Agente IA (CRM)
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Muestra/oculta opciones y etiquetas de WhatsApp en CRM y Notificaciones.</p>
									</div>

								</div>
							</div>
						</div>

						<!-- Columna Derecha: Facturación y Notificaciones -->
						<div class="col-md-6">
							<div class="panel panel-default" style="border-radius: 6px; border-color: #e2e8f0;">
								<div class="panel-heading" style="background: #fafafa; font-weight: 700; color: #334155;">
									<i class="fa fa-file-text-o text-success"></i> Facturación Electrónica y Notificaciones
								</div>
								<div class="panel-body">
									
									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="documentoSoporte" value="1" <?php echo (!isset($configuracionGlobal["documento_soporte_activo"]) || $configuracionGlobal["documento_soporte_activo"] == 1) ? "checked" : ""; ?>>
												Activar "Documento Soporte" y "Notas de Ajuste"
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Opciones de Documento Soporte en menú lateral y matriz de permisos.</p>
									</div>

									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="facturacionElectronica" value="1" <?php echo (!isset($configuracionGlobal["facturacion_electronica_activa"]) || $configuracionGlobal["facturacion_electronica_activa"] == 1) ? "checked" : ""; ?>>
												Activar "Facturación Electrónica" y "Notas Crédito"
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Opciones de Facturas Electrónicas, Notas Crédito y gráfica en inicio.</p>
									</div>

									<div class="form-group" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
										<div class="checkbox">
											<label style="font-weight: 600; cursor: pointer; font-size: 15px;">
												<input type="checkbox" name="botonConvertirFE" value="1" <?php echo (!isset($configuracionGlobal["boton_convertir_fe_activo"]) || $configuracionGlobal["boton_convertir_fe_activo"] == 1) ? "checked" : ""; ?>>
												Activar botón "Convertir a FE" en Órdenes
											</label>
										</div>
										<p class="help-block" style="margin-left: 20px; font-size: 12px;">Muestra/oculta el botón "Convertir a FE" en la tabla de Órdenes.</p>
									</div>

									<div class="well well-sm" style="background: #fffbebf5; border-color: #fef3c7; margin-bottom: 0; border-radius: 6px;">
										<h5 style="font-weight: 700; color: #b45309; margin-top: 0; font-size: 13px;">
											<i class="fa fa-bell"></i> Visibilidad de Notificaciones
										</h5>
										<div class="checkbox" style="margin-bottom: 0;">
											<label style="font-weight: 600; cursor: pointer; font-size: 14px;">
												<input type="checkbox" name="notif_transaccion_bold" value="1" <?php echo (!isset($configuracionGlobal["notif_transaccion_bold"]) || $configuracionGlobal["notif_transaccion_bold"] == 1) ? "checked" : ""; ?>>
												Mostrar notificación: "Transacción Bold"
											</label>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- ===================================================
			SECCIÓN 2: INTEGRACIÓN AGENTE IA (BASE DE CONOCIMIENTO)
			=================================================== -->
			<div class="box box-info" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 4px solid #0073b7; margin-bottom: 25px;">
				<div class="box-header with-border" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 8px 8px 0 0;">
					<h3 class="box-title" style="font-weight: 700; color: #0284c7; font-size: 17px; display: flex; align-items: center; gap: 8px;">
						<i class="fa fa-robot text-info"></i> 2. Sincronización Agente IA (Base de Conocimiento)
					</h3>
				</div>

				<div class="box-body" style="padding: 20px;">
					<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
						<div>
							<p style="font-size: 14px; color: #334155; margin: 0 0 4px 0; font-weight: 600;">
								Sincronización manual de contenidos para el Agente IA
							</p>
							<p class="help-block" style="margin: 0; font-size: 13px;">
								Envía los artículos vigentes de la Base de Conocimiento hacia el Agente mediante Webhook.
							</p>
						</div>
						<div>
							<button type="button" class="btn btn-info btn-sm btnEjecutarWebhookConocimiento" id="btnEjecutarWebhookConocimiento" style="font-weight: 600; padding: 7px 18px; border-radius: 4px; background-color: #0284c7; border-color: #0369a1;">
								<i class="fa fa-cloud-upload"></i> Montar artículos para el Agente IA
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- ===================================================
			SECCIÓN 3: CONFIGURACIÓN DEL CORREO EMISOR (SMTP)
			=================================================== -->
			<div class="box box-warning" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 4px solid #f39c12; margin-bottom: 25px;">
				<div class="box-header with-border" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 8px 8px 0 0;">
					<h3 class="box-title" style="font-weight: 700; color: #d97706; font-size: 17px; display: flex; align-items: center; gap: 8px;">
						<i class="fa fa-envelope text-warning"></i> 3. Configuración del Correo Emisor (SMTP)
					</h3>
				</div>

				<div class="box-body" style="padding: 20px;">
					<div class="row">
						<!-- Correo Emisor -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Correo Emisor (SMTP Username)</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-envelope text-muted"></i></span>
									<input type="email" class="form-control" name="smtpCorreo"
										value="<?php echo isset($configuracion['smtp_correo']) ? $configuracion['smtp_correo'] : 'kontrolpos01@gmail.com'; ?>"
										placeholder="ejemplo@gmail.com" required>
								</div>
								<p class="help-block" style="font-size: 12px;">Correo electrónico para enviar facturas y notificaciones.</p>
							</div>
						</div>

						<!-- Contraseña SMTP -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Contraseña SMTP (App Password)</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-lock text-muted"></i></span>
									<input type="password" class="form-control" name="smtpPassword"
										value="<?php echo isset($configuracion['smtp_password']) ? $configuracion['smtp_password'] : 'jnjs tvux pfwd aghm'; ?>"
										placeholder="Contraseña de aplicación o clave SMTP" required>
								</div>
								<p class="help-block" style="font-size: 12px;">Contraseña de aplicación de Gmail o clave SMTP de tu servidor.</p>
							</div>
						</div>
					</div>

					<div class="row">
						<!-- Servidor Host -->
						<div class="col-md-4">
							<div class="form-group">
								<label style="font-weight: 600;">Servidor SMTP (Host)</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-server text-muted"></i></span>
									<input type="text" class="form-control" name="smtpHost"
										value="<?php echo isset($configuracion['smtp_host']) ? $configuracion['smtp_host'] : 'smtp.gmail.com'; ?>"
										placeholder="smtp.gmail.com" required>
								</div>
								<p class="help-block" style="font-size: 12px;">Dirección del servidor SMTP (ej: smtp.gmail.com).</p>
							</div>
						</div>

						<!-- Puerto SMTP -->
						<div class="col-md-4">
							<div class="form-group">
								<label style="font-weight: 600;">Puerto SMTP</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-plug text-muted"></i></span>
									<input type="number" class="form-control" name="smtpPort"
										value="<?php echo isset($configuracion['smtp_port']) ? $configuracion['smtp_port'] : 587; ?>"
										placeholder="587" required>
								</div>
								<p class="help-block" style="font-size: 12px;">Puerto SMTP (ej: 587 TLS, 465 SSL).</p>
							</div>
						</div>

						<!-- Seguridad SMTP -->
						<div class="col-md-4">
							<div class="form-group">
								<label style="font-weight: 600;">Seguridad SMTP</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-shield text-muted"></i></span>
									<select class="form-control" name="smtpSecure">
										<?php
										$secureVal = isset($configuracion['smtp_secure']) ? $configuracion['smtp_secure'] : 'tls';
										?>
										<option value="tls" <?php echo ($secureVal == 'tls') ? 'selected' : ''; ?>>TLS (Recomendado)</option>
										<option value="ssl" <?php echo ($secureVal == 'ssl') ? 'selected' : ''; ?>>SSL</option>
										<option value="none" <?php echo ($secureVal == 'none' || $secureVal == '') ? 'selected' : ''; ?>>Ninguna</option>
									</select>
								</div>
								<p class="help-block" style="font-size: 12px;">Protocolo de cifrado.</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- ===================================================
			SECCIÓN 4: API DE FACTURACIÓN ELECTRÓNICA (FACTUS)
			=================================================== -->
			<div class="box box-success" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 4px solid #00a65a; margin-bottom: 25px;">
				<div class="box-header with-border" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 8px 8px 0 0;">
					<h3 class="box-title" style="font-weight: 700; color: #16a34a; font-size: 17px; display: flex; align-items: center; gap: 8px;">
						<i class="fa fa-cloud text-success"></i> 4. Conexión y Credenciales API Factus
					</h3>
				</div>

				<div class="box-body" style="padding: 20px;">

					<!-- Información sobre Factus -->
					<div class="alert alert-info" style="border-radius: 6px; background-color: #f0f9ff; border-color: #bae6fd; color: #0369a1;">
						<h4 style="font-weight: 700; font-size: 15px; margin-top: 0;"><i class="icon fa fa-info-circle"></i> Información del Proveedor</h4>
						<p style="margin-bottom: 5px;">
							Factus es el proveedor de servicios de facturación electrónica para Colombia (DIAN).
							Obtén tus credenciales registrándote en <a href="https://factus.com.co" target="_blank" style="font-weight: bold; text-decoration: underline;">https://factus.com.co</a>.
						</p>
						<p style="margin-bottom: 0;">
							<strong>Documentación oficial de la API:</strong>
							<a href="https://developers.factus.com.co" target="_blank" style="font-weight: bold; text-decoration: underline;">https://developers.factus.com.co</a>
						</p>
					</div>

					<!-- Estado de conexión -->
					<?php if ($configuracion['activo'] == 1): ?>
						<div class="alert alert-success" style="border-radius: 6px;">
							<h4 style="margin-top: 0; font-weight: 700;"><i class="icon fa fa-check"></i> Estado: Activo</h4>
							<p class="mb-0">La integración con Factus está activa y en funcionamiento.</p>
							<?php if ($configuracion['access_token']): ?>
								<p class="mb-0" style="margin-top: 5px;">
									<small>
										<strong>Token válido hasta:</strong>
										<?php echo $configuracion['token_expiracion'] ? date('d/m/Y H:i:s', strtotime($configuracion['token_expiracion'])) : 'No disponible'; ?>
									</small>
								</p>
							<?php endif; ?>
						</div>
					<?php else: ?>
						<div class="alert alert-warning" style="border-radius: 6px;">
							<h4 style="margin-top: 0; font-weight: 700;"><i class="icon fa fa-warning"></i> Estado: Inactivo</h4>
							<p class="mb-0">La integración con Factus está desactivada. Actívala para emitir facturas electrónicas ante la DIAN.</p>
						</div>
					<?php endif; ?>

					<div class="row">
						<!-- Ambiente -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Ambiente de Trabajo *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-server text-muted"></i></span>
									<select class="form-control" name="ambiente" id="ambiente" required>
										<option value="sandbox" <?php echo ($configuracion['ambiente'] == 'sandbox') ? 'selected' : ''; ?>>
											Sandbox (Pruebas)
										</option>
										<option value="produccion" <?php echo ($configuracion['ambiente'] == 'produccion') ? 'selected' : ''; ?>>
											Producción
										</option>
									</select>
								</div>
								<p class="help-block" style="font-size: 12px;">
									<strong>Sandbox:</strong> Pruebas y desarrollo | <strong>Producción:</strong> Emisión real ante DIAN
								</p>
							</div>
						</div>

						<!-- URL de la API -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">URL de la API *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-link text-muted"></i></span>
									<input type="url" class="form-control" name="apiUrl" id="apiUrl"
										value="<?php echo $configuracion['api_url']; ?>"
										placeholder="https://api.factus.com.co" required>
								</div>
								<p class="help-block" style="font-size: 12px;">
									https://api-sandbox.factus.com.co o https://api.factus.com.co
								</p>
							</div>
						</div>
					</div>

					<div class="row">
						<!-- Client ID -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Client ID *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user text-muted"></i></span>
									<input type="text" class="form-control" name="clientId"
										value="<?php echo $configuracion['client_id']; ?>"
										placeholder="Tu Client ID de Factus" required>
								</div>
								<p class="help-block" style="font-size: 12px;">Client ID asignado para autenticación OAuth2.</p>
							</div>
						</div>

						<!-- Client Secret -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Client Secret *</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-key text-muted"></i></span>
									<input type="password" class="form-control" name="clientSecret"
										value="<?php echo $configuracion['client_secret']; ?>"
										placeholder="Tu Client Secret de Factus" required>
								</div>
								<p class="help-block" style="font-size: 12px;">Client Secret de tu cuenta Factus.</p>
							</div>
						</div>
					</div>

					<div class="row">
						<!-- Email (Username) -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Email (Username)</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-envelope text-muted"></i></span>
									<input type="email" class="form-control" name="username"
										value="<?php echo $configuracion['username']; ?>"
										placeholder="Email de acceso a Factus">
								</div>
								<p class="help-block" style="font-size: 12px;">Requerido para Sandbox (Grant Type: Password).</p>
							</div>
						</div>

						<!-- Password -->
						<div class="col-md-6">
							<div class="form-group">
								<label style="font-weight: 600;">Contraseña</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-lock text-muted"></i></span>
									<input type="password" class="form-control" name="password"
										value="<?php echo $configuracion['password']; ?>"
										placeholder="Contraseña de acceso a Factus">
								</div>
								<p class="help-block" style="font-size: 12px;">Requerido para Sandbox (Grant Type: Password).</p>
							</div>
						</div>
					</div>

					<!-- Rango de Numeración por Defecto -->
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label style="font-weight: 600;">Rango de Numeración por Defecto</label>
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
								<p class="help-block" style="font-size: 12px;">
									Prefijo actual activo: <strong><?php 
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
							<div class="well well-sm" style="background: #f8fafc; border-color: #cbd5e1; border-radius: 6px;">
								<div class="checkbox" style="margin: 0;">
									<label style="font-weight: 700; cursor: pointer; font-size: 15px; color: #0f172a;">
										<input type="checkbox" name="factusActivo" value="1"
											<?php echo ($configuracion['activo'] == 1) ? 'checked' : ''; ?>>
										Activar integración con Factus (Emisión DIAN)
									</label>
								</div>
								<p class="help-block" style="margin-top: 5px; margin-bottom: 0; font-size: 12px;">
									Al activar, las facturas electrónicas se emitirán automáticamente utilizando las credenciales configuradas.
								</p>
							</div>
						</div>
					</div>

					<!-- Botones de prueba y autenticación -->
					<div class="row" style="margin-top: 15px;">
						<div class="col-md-12">
							<div class="well well-sm" style="background: #f0fdf4; border-color: #bbf7d0; border-radius: 6px; padding: 15px;">
								<h5 style="font-weight: 700; color: #15803d; margin-top: 0;"><i class="fa fa-lightbulb-o"></i> Prueba de Conexión y Autenticación</h5>
								<p style="font-size: 13px; color: #334155; margin-bottom: 12px;">
									Verifica tus credenciales con el servidor de Factus antes de guardar.
								</p>
								
								<div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
									<button type="button" class="btn btn-info btn-sm" id="btnProbarConexion" style="font-weight: 600;">
										<i class="fa fa-refresh"></i> Probar Conexión
									</button>

									<button type="button" class="btn btn-success btn-sm" id="btnAutenticar" style="font-weight: 600; background-color: #10b981; border-color: #059669;">
										<i class="fa fa-key"></i> Autenticar y Obtener Tokens
									</button>
								</div>

								<div id="resultadoPrueba" style="margin-top: 12px;"></div>
							</div>
						</div>
					</div>

				</div>

				<div class="box-footer" style="padding: 15px 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
					<button type="submit" class="btn btn-primary btn-lg" style="padding: 10px 30px; font-weight: 700; font-size: 16px; border-radius: 6px;">
						<i class="fa fa-save"></i> Guardar Configuración General
					</button>
				</div>

			</div>
		</form>

		<!-- ===================================================
		SECCIÓN 5: TABLAS MAESTRAS Y PARÁMETROS DIAN / FACTUS
		=================================================== -->
		<?php if ($configuracion['activo'] == 1): ?>
		<div class="box box-default" style="border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 4px solid #64748b; margin-top: 30px;">
			<div class="box-header with-border" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 8px 8px 0 0;">
				<h3 class="box-title" style="font-weight: 700; color: #334155; font-size: 17px; display: flex; align-items: center; gap: 8px;">
					<i class="fa fa-database text-muted"></i> 5. Tablas Maestras y Parámetros DIAN / Factus
				</h3>
			</div>

			<div class="box-body" style="padding: 20px;">

				<div class="alert alert-info" style="border-radius: 6px; background-color: #f0f9ff; border-color: #bae6fd; color: #0369a1;">
					<h4 style="font-weight: 700; font-size: 15px; margin-top: 0;"><i class="icon fa fa-info-circle"></i> Catálogos de Referencia DIAN</h4>
					<p style="margin-bottom: 0;">
						Sincroniza los datos de referencia desde Factus para mantener actualizados los catálogos oficiales de municipios, tributos, unidades de medida y rangos de numeración según la DIAN.
					</p>
				</div>

				<div class="row">

					<!-- Municipios -->
					<div class="col-md-4">
						<div class="panel panel-default" style="border-radius: 6px; border-color: #cbd5e1;">
							<div class="panel-heading" style="font-weight: 700; background: #f8fafc;">
								<i class="fa fa-map-marker text-danger"></i> Municipios
							</div>
							<div class="panel-body">
								<p style="font-size: 12px; color: #64748b;">Catálogo de municipios de Colombia con códigos DIAN.</p>
								<?php
								$ultimaSincMunicipios = ModeloFactus::mdlObtenerUltimaSincronizacion('municipios');
								if ($ultimaSincMunicipios):
								?>
								<p class="text-muted" style="margin-bottom: 10px;">
									<small>
										<i class="fa fa-clock-o"></i> Última sincronización:<br>
										<strong><?php echo date('d/m/Y H:i:s', strtotime($ultimaSincMunicipios['fecha_sincronizacion'])); ?></strong><br>
										<i class="fa fa-check text-success"></i> <?php echo $ultimaSincMunicipios['registros_insertados']; ?> insertados, <?php echo $ultimaSincMunicipios['registros_actualizados']; ?> actualizados
									</small>
								</p>
								<?php else: ?>
								<p class="text-warning"><small><i class="fa fa-warning"></i> No sincronizado aún</small></p>
								<?php endif; ?>
								<button type="button" class="btn btn-success btn-block btn-sm" id="btnSincronizarMunicipios" style="font-weight: 600;">
									<i class="fa fa-refresh"></i> Sincronizar Municipios
								</button>
								<div id="resultadoMunicipios" style="margin-top: 10px;"></div>
							</div>
						</div>
					</div>

					<!-- Tributos -->
					<div class="col-md-4">
						<div class="panel panel-default" style="border-radius: 6px; border-color: #cbd5e1;">
							<div class="panel-heading" style="font-weight: 700; background: #f8fafc;">
								<i class="fa fa-percent text-warning"></i> Tributos
							</div>
							<div class="panel-body">
								<p style="font-size: 12px; color: #64748b;">Catálogo de tributos e impuestos (IVA, INC, etc).</p>
								<?php
								$ultimaSincTributos = ModeloFactus::mdlObtenerUltimaSincronizacion('tributos');
								if ($ultimaSincTributos):
								?>
								<p class="text-muted" style="margin-bottom: 10px;">
									<small>
										<i class="fa fa-clock-o"></i> Última sincronización:<br>
										<strong><?php echo date('d/m/Y H:i:s', strtotime($ultimaSincTributos['fecha_sincronizacion'])); ?></strong><br>
										<i class="fa fa-check text-success"></i> <?php echo $ultimaSincTributos['registros_insertados']; ?> insertados, <?php echo $ultimaSincTributos['registros_actualizados']; ?> actualizados
									</small>
								</p>
								<?php else: ?>
								<p class="text-warning"><small><i class="fa fa-warning"></i> No sincronizado aún</small></p>
								<?php endif; ?>
								<button type="button" class="btn btn-warning btn-block btn-sm" id="btnSincronizarTributos" style="font-weight: 600;">
									<i class="fa fa-refresh"></i> Sincronizar Tributos
								</button>
								<div id="resultadoTributos" style="margin-top: 10px;"></div>
							</div>
						</div>
					</div>

					<!-- Unidades -->
					<div class="col-md-4">
						<div class="panel panel-default" style="border-radius: 6px; border-color: #cbd5e1;">
							<div class="panel-heading" style="font-weight: 700; background: #f8fafc;">
								<i class="fa fa-balance-scale text-info"></i> Unidades de Medida
							</div>
							<div class="panel-body">
								<p style="font-size: 12px; color: #64748b;">Catálogo de unidades de medida (kg, m, und, etc).</p>
								<?php
								$ultimaSincUnidades = ModeloFactus::mdlObtenerUltimaSincronizacion('unidades');
								if ($ultimaSincUnidades):
								?>
								<p class="text-muted" style="margin-bottom: 10px;">
									<small>
										<i class="fa fa-clock-o"></i> Última sincronización:<br>
										<strong><?php echo date('d/m/Y H:i:s', strtotime($ultimaSincUnidades['fecha_sincronizacion'])); ?></strong><br>
										<i class="fa fa-check text-success"></i> <?php echo $ultimaSincUnidades['registros_insertados']; ?> insertados, <?php echo $ultimaSincUnidades['registros_actualizados']; ?> actualizados
									</small>
								</p>
								<?php else: ?>
								<p class="text-warning"><small><i class="fa fa-warning"></i> No sincronizado aún</small></p>
								<?php endif; ?>
								<button type="button" class="btn btn-info btn-block btn-sm" id="btnSincronizarUnidades" style="font-weight: 600;">
									<i class="fa fa-refresh"></i> Sincronizar Unidades
								</button>
								<div id="resultadoUnidades" style="margin-top: 10px;"></div>
							</div>
						</div>
					</div>

				</div>

				<!-- Rangos de Numeración -->
				<div class="row" style="margin-top: 10px;">
					<div class="col-md-12">
						<div class="panel panel-default" style="border-radius: 6px; border-color: #cbd5e1;">
							<div class="panel-heading" style="font-weight: 700; background: #f8fafc;">
								<i class="fa fa-list-ol text-primary"></i> Rangos de Numeración Autorizados
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-md-4">
										<p style="font-size: 13px; color: #475569;">Sincroniza los rangos de numeración autorizados y asociados a tu cuenta de Factus.</p>
										<?php
										$ultimaSincRangos = ModeloFactus::mdlObtenerUltimaSincronizacion('rangos');
										if ($ultimaSincRangos):
										?>
										<p class="text-muted">
											<small>
												<i class="fa fa-clock-o"></i> Última sincronización:<br>
												<strong><?php echo date('d/m/Y H:i:s', strtotime($ultimaSincRangos['fecha_sincronizacion'])); ?></strong>
											</small>
										</p>
										<?php else: ?>
										<p class="text-warning"><small><i class="fa fa-warning"></i> No sincronizado aún</small></p>
										<?php endif; ?>
										
										<button type="button" class="btn btn-primary btn-block btn-sm" id="btnSincronizarRangos" style="font-weight: 600;">
											<i class="fa fa-refresh"></i> Sincronizar Rangos
										</button>
										<div id="resultadoRangos" style="margin-top: 10px;"></div>
									</div>
									
									<div class="col-md-8">
										<h5 style="font-weight: 700; color: #1e293b; margin-top: 0;">Rangos Sincronizados Disponibles:</h5>
										<div class="table-responsive">
											<table class="table table-bordered table-striped" style="font-size: 13px;">
												<thead>
													<tr style="background: #f1f5f9; color: #334155;">
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

<!-- Script para cambiar automáticamente la URL según el ambiente y manejar peticiones -->
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

		var apiUrl = $('#apiUrl').val();
		var clientId = $('input[name="clientId"]').val();
		var clientSecret = $('input[name="clientSecret"]').val();

		if (!apiUrl || !clientId || !clientSecret) {
			resultado.html('<div class="alert alert-warning">' +
				'<i class="fa fa-warning"></i> Por favor, completa todos los campos antes de probar la conexión.' +
				'</div>');
			return;
		}

		btn.prop('disabled', true);
		btn.html('<i class="fa fa-spinner fa-spin"></i> Probando...');
		resultado.html('<div class="alert alert-info">' +
			'<i class="fa fa-spinner fa-spin"></i> Conectando con Factus...' +
			'</div>');

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
				btn.prop('disabled', false);
				btn.html('<i class="fa fa-refresh"></i> Probar Conexión');
			}
		});
	});

	// Autenticar y obtener tokens
	$('#btnAutenticar').on('click', function() {
		var btn = $(this);
		var resultado = $('#resultadoPrueba');
		var apiUrl = $('#apiUrl').val();
		var clientId = $('input[name="clientId"]').val();
		var clientSecret = $('input[name="clientSecret"]').val();
		var username = $('input[name="username"]').val();
		var password = $('input[name="password"]').val();
		var ambiente = $('#ambiente').val();
		var rangoNumeracionId = $('select[name="rangoNumeracionId"]').val();

		if (!apiUrl || !clientId || !clientSecret) {
			resultado.html('<div class="alert alert-warning">' +
				'<i class="fa fa-warning"></i> Por favor, completa todos los campos (Client ID, Secret y URL) antes de autenticar.' +
				'</div>');
			return;
		}

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
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Autenticando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Obteniendo tokens de Factus...' +
					'</div>');

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
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando municipios desde Factus...' +
					'</div>');

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
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando tributos desde Factus...' +
					'</div>');

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
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando unidades desde Factus...' +
					'</div>');

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
				btn.prop('disabled', true);
				btn.html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
				resultado.html('<div class="alert alert-info">' +
					'<i class="fa fa-spinner fa-spin"></i> Descargando rangos...' +
					'</div>');

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

	/*=============================================
	EJECUTAR WEBHOOK BASE DE CONOCIMIENTO (AGENTE IA)
	=============================================*/
	$(document).on("click", "#btnEjecutarWebhookConocimiento", function (e) {
		e.preventDefault();
		var btn = $(this);
		var originalHtml = btn.html();

		function ejecutarProcesoWebhook() {
			btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...');
			
			$.ajax({
				url: "ajax/factus.ajax.php",
				method: "POST",
				data: {
					accion: "ejecutarWebhookConocimiento",
					csrf_token: $('meta[name="csrf-token"]').attr('content')
				},
				dataType: "json",
				success: function (respuesta) {
					btn.prop("disabled", false).html(originalHtml);
					if (respuesta.status === "ok") {
						swal({
							title: "¡Sincronización Exitosa!",
							text: "Se han montado los artículos de la Base de conocimiento para el Agente IA correctamente.",
							type: "success"
						});
					} else {
						swal({
							title: "Atención",
							text: respuesta.mensaje || "Ocurrió un inconveniente al conectar con el webhook.",
							type: "warning"
						});
					}
				},
				error: function (xhr, status, error) {
					btn.prop("disabled", false).html(originalHtml);
					swal({
						title: "Error de conexión",
						text: "No se pudo comunicar con el servidor para activar el webhook.",
						type: "error"
					});
				}
			});
		}

		var swalRes = swal({
			title: "¿Desea sincronizar la Base de Conocimiento?",
			text: "Se activará el webhook para enviar los artículos al Agente IA.",
			type: "info",
			showCancelButton: true,
			confirmButtonColor: "#0073b7",
			cancelButtonText: "Cancelar",
			confirmButtonText: "Sí, montar artículos"
		}, function (isConfirm) {
			if (isConfirm) {
				ejecutarProcesoWebhook();
			}
		});

		if (swalRes && typeof swalRes.then === "function") {
			swalRes.then(function (result) {
				if (result && (result.value || result === true)) {
					ejecutarProcesoWebhook();
				}
			});
		}
	});

});
</script>

<?php

$actualizar = new ControladorFactus();
$actualizar->ctrActualizarConfiguracion();

?>
