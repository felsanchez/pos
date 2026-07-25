<?php
// Obtener configuración del sistema
$configuracion = ControladorConfiguracion::ctrObtenerConfiguracion();
$configFactus = ControladorFactus::ctrObtenerConfiguracion();

// Definir logo a usar
$logoEmpresa = "vistas/img/plantilla/logo-blanco-lineal.png"; // Logo por defecto
$logoMini = "vistas/img/plantilla/icono-blanco.png"; // Logo mini por defecto

// Priorizar Logo de Facturación Electrónica (Factus)
if (!empty($configFactus["logo_empresa"]) && file_exists($configFactus["logo_empresa"])) {
	$logoEmpresa = $configFactus["logo_empresa"];
	$logoMini = $configFactus["logo_empresa"];
} else if (!empty($configuracion["logo"]) && file_exists($configuracion["logo"])) {
	$logoEmpresa = $configuracion["logo"];
	$logoMini = $configuracion["logo"];
}

// Verificar actividades y gastos próximos (genera notificaciones automáticamente)
ControladorNotificaciones::ctrVerificarActividadesProximas();
ControladorNotificaciones::ctrVerificarGastosProximos();

// Verificar órdenes desde Agente IA (campo extra contiene 'n8n')
ControladorNotificaciones::ctrVerificarOrdenAgenteIA();

// Verificar órdenes automáticas de n8n
ControladorNotificaciones::ctrVerificarOrdenn8n();

// Verificar solicitudes de edición/eliminación Agente IA
ControladorNotificaciones::ctrVerificarSolicitudesAgenteIA();

// Verificar pagos de Bold (Sincronización)
ControladorNotificaciones::ctrVerificarPagosBold();

// Verificar leads de WhatsApp CRM
ControladorNotificaciones::ctrVerificarLeadsWhatsApp();

// Contar notificaciones no leídas
$totalNoLeidas = ControladorNotificaciones::ctrContarNoLeidas();
?>

<style>
	/* Fix para dropdown de notificaciones */
	.notifications-menu .dropdown-menu {
		width: 280px !important;
		padding: 0 !important;
		margin: 0 !important;
		top: 100% !important;
	}

	.notifications-menu .dropdown-menu>.header {
		padding: 10px;
		background-color: #ffffff;
		color: #444444;
		border-bottom: 1px solid #ddd;
	}

	.notifications-menu .dropdown-menu>li .menu {
		max-height: 200px;
		margin: 0;
		padding: 0;
		list-style: none;
		overflow-x: hidden;
	}

	.notifications-menu .dropdown-menu>li .menu>li>a {
		display: block;
		white-space: normal;
		border-bottom: 1px solid #e7e7e7;
		color: #444444;
		padding: 10px;
	}

	.notifications-menu .dropdown-menu>li .menu>li>a:hover {
		background: #f4f4f4;
		text-decoration: none;
	}

	.notifications-menu .dropdown-menu>li.footer>a {
		background-color: #ffffff;
		padding: 7px 10px;
		border-bottom: 1px solid #e7e7e7;
		color: #444444;
		text-align: center;
		display: block;
	}

	.notifications-menu .dropdown-menu>li.footer>a:hover {
		background: #f4f4f4;
		text-decoration: none;
	}

	/* Asegurar que el dropdown se muestre cuando está abierto */
	.notifications-menu.open .dropdown-menu {
		display: block !important;
	}

	/* Limitar altura del modal de perfil y agregar scroll */
	#modalPerfilUsuario .modal-body {
		max-height: 60vh;
		overflow-y: auto;
	}

	/* Asegurar que el footer del modal esté siempre visible */
	#modalPerfilUsuario .modal-footer {
		position: relative;
		z-index: 10;
	}

	/* Fix para asegurar que el modal se muestre por encima de todo */
	#modalPerfilUsuario {
		z-index: 10050 !important;
	}

	/* Limitar altura del modal de ampliar foto y agregar scroll */
	#modalAmpliarFotoPerfil .modal-body {
		max-height: 70vh;
		overflow-y: auto;
	}

	/* Asegurar que el footer del modal de foto esté siempre visible */
	#modalAmpliarFotoPerfil .modal-footer {
		position: relative;
		z-index: 10;
	}

	/* Fix para asegurar que el modal de foto se muestre por encima de todo */
	#modalAmpliarFotoPerfil {
		z-index: 10051 !important;
	}

	/* Fix para asegurar que las alertas (SweetAlert) se muestren por encima de los modales */
	.swal2-container,
	.sweet-alert,
	.swal-overlay {
		z-index: 20000 !important;
	}

	/* Mantener el logo en estado mini (sin expandirse) cuando el menú lateral está abierto en Desktop */
	@media (min-width: 768px) {
		body:not(.sidebar-collapse) .main-header .logo .logo-lg {
			display: none !important;
		}
		body:not(.sidebar-collapse) .main-header .logo .logo-mini {
			display: block !important;
			width: 50px !important;
			float: left;
		}
	}

	/* Optimización de cabecera móvil - Fila única y compacta */
	@media (max-width: 767px) {
		.main-header .logo {
			display: none !important;
		}

		.main-header .navbar {
			margin: 0 !important;
			height: 60px !important;
			min-height: 60px !important;
			display: flex !important;
			justify-content: space-between !important;
			align-items: center !important;
			padding: 0 5px !important;
		}

		.main-header .navbar .sidebar-toggle {
			padding: 0 12px !important;
			height: 60px !important;
			line-height: 60px !important;
			margin: 0 !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			float: none !important;
		}

		/* Contenedor central (Sucursal y Caja Chica) - Apilados verticalmente para evitar truncamientos */
		.navbar-custom-menu.pull-left {
			margin: 0 !important;
			padding: 0 !important;
			height: 60px !important;
			display: flex !important;
			flex-direction: column !important;
			justify-content: center !important;
			align-items: flex-start !important;
			gap: 3px !important;
			flex-wrap: nowrap !important;
			overflow: visible !important;
			flex-grow: 1 !important;
			max-width: calc(100% - 130px) !important;
		}

		/* Reducir etiquetas y botones para pantallas chicas */
		.navbar-custom-menu.pull-left .label,
		.navbar-custom-menu.pull-left .btn {
			font-size: 11px !important;
			padding: 3px 8px !important;
			white-space: nowrap !important;
			display: inline-flex !important;
			align-items: center !important;
			gap: 4px !important;
		}

		/* Ocultar etiquetas adicionales no indispensables en móvil */
		.navbar-custom-menu.pull-left .label-success {
			display: none !important; /* Oculta el estado de Caja Abierta con el balance base */
		}

		/* Menú de notificaciones y usuario en la derecha */
		.navbar-custom-menu:not(.pull-left) {
			margin: 0 !important;
			float: none !important;
			display: flex !important;
			align-items: center !important;
			height: 60px !important;
			flex-shrink: 0 !important;
		}

		.navbar-custom-menu:not(.pull-left) .navbar-nav {
			margin: 0 !important;
			display: flex !important;
			flex-direction: row !important;
			align-items: center !important;
			height: 60px !important;
		}

		.navbar-custom-menu:not(.pull-left) .navbar-nav > li {
			float: none !important;
			display: inline-block !important;
			height: 60px !important;
		}

		.navbar-custom-menu:not(.pull-left) .navbar-nav > li > a {
			height: 60px !important;
			padding: 0 10px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
		}

		.navbar-custom-menu:not(.pull-left) .navbar-nav > li.dropdown.user.user-menu > a {
			padding: 0 10px !important;
		}

		.navbar-custom-menu:not(.pull-left) .navbar-nav > li.dropdown.user.user-menu img.user-image {
			margin: 0 !important;
			float: none !important;
		}

		/* Posicionar correctamente los dropdowns de notificaciones en móvil */
		.navbar-custom-menu:not(.pull-left) .dropdown-menu {
			position: absolute !important;
			right: 0 !important;
			left: auto !important;
			background: #ffffff !important;
		}

		/* Evitar que la cabecera tape los botones superiores del menú lateral */
		.main-sidebar {
			padding-top: 60px !important;
		}
	}
</style>

<header class="main-header">

	<!--=====================================
LOGOTIPO
======================================-->
	<a href="inicio" class="logo">

		<!-- logo mini -->
		<span class="logo-mini">
			<img src="<?php echo $logoMini; ?>" class="img-responsive" style="padding: 10px">
		</span>

		<!-- logo normal -->
		<span class="logo-lg">
			<img src="<?php echo $logoEmpresa; ?>" class="img-responsive" style="padding: 10px 0px">
		</span>

	</a>


	<!--=====================================
BARRA DE NAVEGACION
======================================-->
	<nav class="navbar navbar-static-top" role="navigation">

		<!-- boton de navegacion -->
		<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
			<span class="sr-only">Toggle navigation</span>
		</a>

		<!-- Sucursal Activa y Módulo de Caja Chica -->
		<?php if ($_SESSION["perfil"] !== "Visitante"): ?>
		<div class="navbar-custom-menu pull-left" style="margin-left: 10px; margin-top: 10px; color: white; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
			<?php 
			$sucursalesActivas = !isset($configuracion["activar_sucursales"]) || $configuracion["activar_sucursales"] == 1;
			if ($sucursalesActivas): 
			?>
			<span class="label label-primary" style="font-size: 13px; padding: 5px 12px; border: 1px solid rgba(255,255,255,0.3); border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: inline-block;">
				<i class="fa fa-building"></i> 
				<span class="hidden-xs">Sucursal:</span> 
				<?php 
					if(empty($_SESSION["nombre_bodega"])){
						$idBodegaActiva = !empty($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 1;
						$bodegaActiva = ControladorBodegas::ctrMostrarBodegas("id", $idBodegaActiva);
						if(empty($bodegaActiva) && $idBodegaActiva != 1){
							$bodegaActiva = ControladorBodegas::ctrMostrarBodegas("id", 1);
						}
						$_SESSION["nombre_bodega"] = !empty($bodegaActiva["nombre"]) ? $bodegaActiva["nombre"] : "Principal";
						$_SESSION["id_bodega"] = $idBodegaActiva;
					}
					echo e($_SESSION["nombre_bodega"]);
				?>
			</span>
			<?php endif; ?>

			<?php 
			if (class_exists("ControladorCajas") && isset($configuracion["control_caja"]) && intval($configuracion["control_caja"]) === 1): 
				$cajaAbierta = ControladorCajas::ctrVerificarCajaAbierta();
				$moneda = !empty($configuracion["moneda"]) ? $configuracion["moneda"] : "$";
			?>
				<div class="btn-group-caja-header" style="display: inline-flex; gap: 6px; align-items: center;">
					<?php if (!$cajaAbierta): ?>
						<button class="btn btn-success btn-xs btnAbrirCajaModal" data-toggle="modal" data-target="#modalAperturaCaja" style="border-radius: 12px; padding: 3px 10px; font-weight: bold; border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
							<i class="fa fa-unlock"></i> Abrir Caja
						</button>
					<?php else: ?>
						<button class="btn btn-warning btn-xs btnMovimientoCajaModal" data-toggle="modal" data-target="#modalMovimientoCaja" style="border-radius: 12px; padding: 3px 10px; font-weight: bold; border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 2px 4px rgba(0,0,0,0.15); color: #fff;">
							<i class="fa fa-exchange"></i> Movimiento
						</button>
						<button class="btn btn-danger btn-xs btnCerrarCajaModal" data-toggle="modal" data-target="#modalCerrarCaja" style="border-radius: 12px; padding: 3px 10px; font-weight: bold; border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
							<i class="fa fa-lock"></i> Cerrar Caja
						</button>
						<span class="label label-success hidden-xs" style="font-size: 11px; padding: 4px 10px; border-radius: 12px; font-weight: 500; border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
							<i class="fa fa-check-circle"></i> Caja Abierta (Base: <?php echo $moneda . ' ' . number_format($cajaAbierta["monto_apertura"], 2); ?>)
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>


		<!-- perfi de usuario -->
		<div class="navbar-custom-menu">

			<ul class="nav navbar-nav">

				<!-- Notificaciones -->
				<?php if ($_SESSION["perfil"] !== "Visitante"): ?>
				<li class="dropdown notifications-menu">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-bell-o"></i>
						<?php if ($totalNoLeidas > 0): ?>
							<span class="label label-warning"><?php echo $totalNoLeidas; ?></span>
						<?php endif; ?>
					</a>
					<ul class="dropdown-menu">
						<li class="header">Tienes <?php echo $totalNoLeidas; ?> notificación(es)</li>
						<li>
							<!-- lista de notificaciones -->
							<ul class="menu">
								<?php
								$notificaciones = ControladorNotificaciones::ctrObtenerNotificaciones(5, true);

								if ($notificaciones && count($notificaciones) > 0) {
									foreach ($notificaciones as $notif) {
										// Determinar icono según tipo
										$icono = "fa-info-circle";
										$color = "text-blue";

										if ($notif["tipo"] == "stock_agotado") {
											$icono = "fa-times-circle";
											$color = "text-red";
										} else if ($notif["tipo"] == "stock_bajo") {
											$icono = "fa-exclamation-triangle";
											$color = "text-yellow";
										} else if ($notif["tipo"] == "actividad_proxima") {
											$icono = "fa-calendar";
											$color = "text-blue";
										} else if ($notif["tipo"] == "registro_usuario" || $notif["tipo"] == "registro_usuario_visitante") {
											$icono = "fa-user-plus";
											$color = "text-green";
										} else if ($notif["tipo"] == "gasto_proximo") {
											$icono = "fa-money";
											$color = "text-orange";
										} else if ($notif["tipo"] == "orden_agente_ia") {
											$icono = "fa-magic";
											$color = "text-green";
										} else if ($notif["tipo"] == "orden_creada") {
											$icono = "fa-shopping-cart";
											$color = "text-green";
										} else if (strpos($notif["tipo"], "Edicion de pedido") !== false) {
											$icono = "fa-pencil-square-o";
											$color = "text-orange";
										} else if (strpos($notif["tipo"], "Eliminacion de pedido") !== false) {
											$icono = "fa-trash";
											$color = "text-red";
										}

										echo '<li>
										<a href="notificaciones">
											<i class="fa ' . e($icono) . ' ' . e($color) . '"></i> ' . e($notif["titulo"]) . '
											<small class="text-muted"><br>' . $notif["mensaje"] . '</small>
										</a>
									</li>';
									}
								} else {
									echo '<li><a href="#"><i class="fa fa-check text-green"></i> No hay notificaciones nuevas</a></li>';
								}
								?>
							</ul>
						</li>
						<li class="footer"><a href="notificaciones">Ver todas las notificaciones</a></li>
					</ul>
				</li>
				<?php endif; ?>

				<li class="dropdown user user-menu">

					<a href="#" data-toggle="modal" data-target="#modalPerfilUsuario">

						<?php

						if ($_SESSION["foto"] != "") {

							echo '<img src="' . $_SESSION["foto"] . '" class="user-image">';
						} else {

							echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';
						}

						?>

						<span class="hidden-xs"><?php echo e($_SESSION["nombre"]); ?></span>

					</a>

				</li>

			</ul>

		</div>



	</nav>

</header>

<!-- Modal Perfil de Usuario -->
<div id="modalPerfilUsuario" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">

			<!-- Cabecera del Modal -->
			<div class="modal-header" style="background: #3c8dbc; color: white;">
				<button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
				<h4 class="modal-title">
					<i class="fa fa-user-circle"></i> Mi Perfil
				</h4>
			</div>

			<!-- Cuerpo del Modal -->
			<form id="formPerfilUsuario" method="post">

        <?php CSRF::insertToken(); ?>
				<div class="modal-body">

					<div class="row">

						<!-- Foto de Perfil -->
						<div class="col-xs-12 text-center" style="margin-bottom: 20px;">
							<?php
							if ($_SESSION["foto"] != "") {
								echo '<img src="' . $_SESSION["foto"] . '" class="img-circle img-perfil-clickeable" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #3c8dbc; cursor: pointer;" data-foto="' . $_SESSION["foto"] . '" data-idusuario="' . $_SESSION["id"] . '" data-usuario="' . $_SESSION["usuario"] . '">';
							} else {
								echo '<img src="vistas/img/usuarios/default/anonymous.png" class="img-circle img-perfil-clickeable" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #3c8dbc; cursor: pointer;" data-foto="vistas/img/usuarios/default/anonymous.png" data-idusuario="' . $_SESSION["id"] . '" data-usuario="' . $_SESSION["usuario"] . '">';
							}
							?>
						</div>

						<!-- Información del Usuario -->
						<div class="col-xs-12">

							<div class="box box-primary">
								<div class="box-body">

									<!-- Campo Nombre (Editable) -->
									<div class="form-group">
										<label><i class="fa fa-user"></i> Nombre Completo:</label>
										<input type="text" class="form-control" id="perfilNombre" name="perfilNombre"
											value="<?php echo e($_SESSION["nombre"]); ?>" required
											pattern="[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+"
											title="El nombre no puede llevar caracteres especiales">
									</div>

									<!-- Campo Email (Editable) -->
									<div class="form-group">
										<label><i class="fa fa-envelope"></i> Correo Electrónico:</label>
										<input type="email" class="form-control" id="perfilEmail" name="perfilEmail"
											value="<?php echo isset($_SESSION["email"]) ? e($_SESSION["email"]) : ''; ?>"
											required placeholder="nombre@correo.com">
									</div>

									<!-- Campo Usuario (Solo lectura) -->
									<div class="form-group">
										<label><i class="fa fa-id-badge"></i> Usuario:</label>
										<p class="form-control-static">
											<strong><?php echo e($_SESSION["usuario"]); ?></strong>
										</p>
									</div>

									<!-- Campo Perfil (Solo lectura) -->
									<div class="form-group">
										<label><i class="fa fa-shield"></i> Perfil:</label>
										<p class="form-control-static">
											<?php
											$perfilClass = "";
											$perfilIcon = "";

											switch ($_SESSION["perfil"]) {
												case "Administrador":
													$perfilClass = "label-danger";
													$perfilIcon = "fa-star";
													break;
												case "Especial":
													$perfilClass = "label-warning";
													$perfilIcon = "fa-certificate";
													break;
												case "Vendedor":
													$perfilClass = "label-success";
													$perfilIcon = "fa-shopping-cart";
													break;
												default:
													$perfilClass = "label-info";
													$perfilIcon = "fa-user";
											}

											echo '<span class="label ' . $perfilClass . '">
													<i class="fa ' . $perfilIcon . '"></i> ' . e($_SESSION["perfil"]) . '
												  </span>';
											?>
										</p>
									</div>

									<hr>

									<!-- Sección Cambiar Contraseña -->
									<h4 class="text-primary">
										<i class="fa fa-lock"></i> Cambiar Contraseña
										<small class="text-muted">(Opcional)</small>
									</h4>

									<!-- Nueva Contraseña -->
									<div class="form-group">
										<label><i class="fa fa-key"></i> Nueva Contraseña:</label>
										<input type="password" class="form-control" id="perfilPassword"
											name="perfilPassword" placeholder="Dejar en blanco para mantener la actual"
											pattern="[a-zA-Z0-9]+" minlength="6"
											title="Mínimo 6 caracteres, sin caracteres especiales">
										<small class="text-muted">
											<i class="fa fa-info-circle"></i> Mínimo 6 caracteres, solo letras y números
										</small>
									</div>

									<!-- Confirmar Contraseña -->
									<div class="form-group">
										<label><i class="fa fa-key"></i> Confirmar Contraseña:</label>
										<input type="password" class="form-control" id="perfilPasswordConfirm"
											name="perfilPasswordConfirm" placeholder="Confirmar nueva contraseña">
									</div>

									<!-- Campo oculto para el token CSRF -->
									<input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">
									<input type="hidden" name="actualizarPerfil" value="1">

								</div>
							</div>

						</div>

					</div>

				</div>

				<!-- Pie del Modal -->
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">
						<i class="fa fa-times"></i> Cerrar
					</button>
					<button type="submit" class="btn btn-primary" id="btnGuardarPerfil">
						<i class="fa fa-save"></i> Guardar Cambios
					</button>
					<a href="salir" class="btn btn-danger">
						<i class="fa fa-sign-out"></i> Cerrar Sesión
					</a>
				</div>
			</form>

		</div>
	</div>
</div>

<!-- Modal para ampliar/editar foto de perfil -->
<div class="modal fade" id="modalAmpliarFotoPerfil" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header" style="background: #3c8dbc; color: white;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title"><i class="fa fa-camera"></i> Foto de Perfil</h4>
			</div>
			<div class="modal-body text-center">
				<img id="imagenPerfilAmpliada" src="" class="img-responsive"
					style="max-width: 100%; margin: 0 auto; margin-bottom: 20px; border-radius: 8px;">
				<hr>
				<div class="form-group">
					<label><i class="fa fa-upload"></i> Cambiar Foto de Perfil</label>
					<input type="file" class="form-control nuevaImagenPerfil" accept="image/*">
					<p class="help-block"><i class="fa fa-info-circle"></i> Peso máximo de la imagen 2MB</p>
				</div>
				<input type="hidden" id="idUsuarioPerfil">
				<input type="hidden" id="usuarioNombrePerfil">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<i class="fa fa-times"></i> Cancelar
				</button>
				<button type="button" class="btn btn-primary btnGuardarImagenPerfil">
					<i class="fa fa-save"></i> Guardar Imagen
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	// Fix para dropdown de notificaciones
	$(document).ready(function () {
		// Mover los modales al body para evitar problemas de z-index y backdrop (AdminLTE wrapper conflict)
		$('#modalPerfilUsuario').appendTo("body");
		$('#modalAmpliarFotoPerfil').appendTo("body");

		// Forzar funcionalidad del dropdown de notificaciones
		$('.notifications-menu > a').on('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $parent = $(this).parent();

			// Cerrar otros dropdowns
			$('.dropdown.open').not($parent).removeClass('open');

			// Toggle este dropdown
			$parent.toggleClass('open');

			// Cerrar dropdown al hacer clic fuera
			if ($parent.hasClass('open')) {
				$(document).on('click.notif-dropdown', function (event) {
					if (!$(event.target).closest('.notifications-menu').length) {
						$parent.removeClass('open');
						$(document).off('click.notif-dropdown');
					}
				});
			} else {
				$(document).off('click.notif-dropdown');
			}
		});

		// Prevenir que se cierre al hacer clic dentro del dropdown
		$('.notifications-menu .dropdown-menu').on('click', function (e) {
			e.stopPropagation();
		});

		// =============================================
		// MANEJAR FORMULARIO DE PERFIL
		// =============================================
		$('#formPerfilUsuario').on('submit', function (e) {
			e.preventDefault();

			var nombre = $('#formPerfilUsuario #perfilNombre').val().trim();
			var email = $('#formPerfilUsuario #perfilEmail').val().trim();
			var password = $('#formPerfilUsuario #perfilPassword').val();
			var passwordConfirm = $('#formPerfilUsuario #perfilPasswordConfirm').val();

			// Validar nombre
			if (nombre === '') {
				swal({
					type: 'error',
					title: 'Error',
					text: 'El nombre no puede estar vacío'
				});
				return false;
			}

			// Validar que el nombre no tenga caracteres especiales
			var regexNombre = /^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/;
			if (!regexNombre.test(nombre)) {
				swal({
					type: 'error',
					title: 'Error',
					text: 'El nombre no puede llevar caracteres especiales'
				});
				return false;
			}

			// Validar email
			if (email === '') {
				swal({
					type: 'error',
					title: 'Error',
					text: 'El correo electrónico no puede estar vacío'
				});
				return false;
			}

			var regexEmail = /^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/;
			if (!regexEmail.test(email)) {
				swal({
					type: 'error',
					title: 'Error',
					text: 'El formato del correo electrónico no es correcto'
				});
				return false;
			}

			// Si se ingresó contraseña, validar
			if (password !== '') {

				// Validar longitud mínima
				if (password.length < 6) {
					swal({
						type: 'error',
						title: 'Error',
						text: 'La contraseña debe tener al menos 6 caracteres'
					});
					return false;
				}

				// Validar que no tenga caracteres especiales
				var regexPassword = /^[a-zA-Z0-9]+$/;
				if (!regexPassword.test(password)) {
					swal({
						type: 'error',
						title: 'Error',
						text: 'La contraseña no puede llevar caracteres especiales'
					});
					return false;
				}

				// Validar que las contraseñas coincidan
				if (password !== passwordConfirm) {
					swal({
						type: 'error',
						title: 'Error',
						text: 'Las contraseñas no coinciden'
					});
					return false;
				}
			}

			// Deshabilitar botón mientras se procesa
			var $btnGuardar = $('#btnGuardarPerfil');
			$btnGuardar.prop('disabled', true);
			$btnGuardar.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

			// Enviar formulario por AJAX
			$.ajax({
				url: 'ajax/usuarios.ajax.php',
				method: 'POST',
				data: $(this).serialize(),
				dataType: 'json',
				success: function (response) {

					$btnGuardar.prop('disabled', false);
					$btnGuardar.html('<i class="fa fa-save"></i> Guardar Cambios');

					if (response.status === 'success') {

						// Mostrar mensaje de éxito y recargar la página
						// Esta es la solución más robusta para evitar problemas de congelamiento del modal
						swal({
							type: 'success',
							title: '¡Éxito!',
							text: response.message,
							showConfirmButton: true,
							confirmButtonText: 'Cerrar'
						}).then(function (result) {
							if (result.value) {
								window.location.reload();
							}
						});

					} else {

						swal({
							type: 'error',
							title: 'Error',
							text: response.message
						});

					}
				},
				error: function (xhr, status, error) {

					$btnGuardar.prop('disabled', false);
					$btnGuardar.html('<i class="fa fa-save"></i> Guardar Cambios');

					swal({
						type: 'error',
						title: 'Error',
						text: 'Hubo un error al procesar la solicitud'
					});

					console.error('Error:', error);
				}
			});

		});

		// =============================================
		// AMPLIAR Y EDITAR FOTO DE PERFIL
		// =============================================

		// Ampliar foto de perfil al hacer clic
		$(document).on("click", ".img-perfil-clickeable", function () {
			var rutaImagen = $(this).attr("data-foto");
			var idUsuario = $(this).attr("data-idusuario");
			var usuario = $(this).attr("data-usuario");

			$("#imagenPerfilAmpliada").attr("src", rutaImagen);
			$("#idUsuarioPerfil").val(idUsuario);
			$("#usuarioNombrePerfil").val(usuario);
			$(".nuevaImagenPerfil").val("");
			$("#modalAmpliarFotoPerfil").modal("show");
		});

		// Previsualizar nueva imagen cuando se selecciona
		$(".nuevaImagenPerfil").change(function () {
			var imagen = this.files[0];

			if (imagen) {
				if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png") {
					$(".nuevaImagenPerfil").val("");
					swal({
						title: "Error al subir la imagen",
						text: "¡La imagen debe estar en formato JPG o PNG!",
						type: "error",
						confirmButtonText: "¡Cerrar!"
					});
				} else if (imagen["size"] > 2000000) {
					$(".nuevaImagenPerfil").val("");
					swal({
						title: "Error al subir la imagen",
						text: "¡La imagen no debe pesar más de 2MB!",
						type: "error",
						confirmButtonText: "¡Cerrar!"
					});
				} else {
					var datosImagen = new FileReader;
					datosImagen.readAsDataURL(imagen);

					$(datosImagen).on("load", function (event) {
						var rutaImagen = event.target.result;
						$("#imagenPerfilAmpliada").attr("src", rutaImagen);
					});
				}
			}
		});

		// Guardar la nueva imagen del perfil
		$(document).on("click", ".btnGuardarImagenPerfil", function () {

			var idUsuario = $("#idUsuarioPerfil").val();
			var usuario = $("#usuarioNombrePerfil").val();
			var imagen = $(".nuevaImagenPerfil")[0].files[0];

			if (!imagen) {
				swal({
					title: "Advertencia",
					text: "No has seleccionado ninguna imagen",
					type: "warning",
					confirmButtonText: "¡Cerrar!"
				});
				return;
			}

			if (!idUsuario || !usuario) {
				swal({
					title: "Error",
					text: "No se pudo obtener el ID o nombre del usuario",
					type: "error",
					confirmButtonText: "¡Cerrar!"
				});
				return;
			}

			var datos = new FormData();
			datos.append("idUsuarioImagen", idUsuario);
			datos.append("usuarioNombre", usuario);
			datos.append("nuevaImagenUsuario", imagen);

			// Mostrar loading
			swal({
				title: 'Cargando...',
				allowOutsideClick: false,
				onBeforeOpen: () => {
					swal.showLoading()
				}
			});

			$.ajax({
				url: "ajax/usuarios.ajax.php",
				method: "POST",
				data: datos,
				cache: false,
				contentType: false,
				processData: false,
				dataType: "json",
				success: function (respuesta) {

					if (respuesta == "ok") {
						swal({
							type: "success",
							title: "¡La imagen ha sido actualizada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(function (result) {
							if (result.value) {
								$("#modalAmpliarFotoPerfil").modal("hide");
								window.location.reload();
							}
						});
					} else {
						swal({
							type: "error",
							title: "Error al actualizar la imagen",
							text: JSON.stringify(respuesta),
							confirmButtonText: "Cerrar"
						});
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					swal({
						type: "error",
						title: "Error en la petición",
						text: "Por favor revisa la consola para más detalles",
						confirmButtonText: "Cerrar"
					});
					console.error("Error AJAX:", textStatus, errorThrown);
					console.error("Respuesta:", jqXHR.responseText);
				}
			});
		});
	});
</script>
