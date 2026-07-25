<?php
if (!class_exists("ModeloCRM")) {
	if (file_exists(__DIR__ . "/../modelos/crm.modelo.php")) {
		require_once __DIR__ . "/../modelos/crm.modelo.php";
	}
}

class ControladorCRM {

	/*=============================================
	MOSTRAR LEADS
	=============================================*/
	static public function ctrMostrarLeads($item, $valor) {

		$tabla = "crm_leads";
		$respuesta = ModeloCRM::mdlMostrarLeads($tabla, $item, $valor);
		return $respuesta;

	}

	/*=============================================
	CREAR LEAD
	=============================================*/
	public function ctrCrearLead() {

		if (isset($_POST["nuevoLeadTitulo"])) {

			if (!puedeAccion('crm', 'crear')) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error de permisos!",
						text: "No tienes permiso para crear leads.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			// VALIDAR CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\.\,\#\-\_\(\) ]+$/', $_POST["nuevoLeadTitulo"])) {

				$tabla = "crm_leads";
				
				// Desplazar leads existentes en la etapa de destino para colocar este al inicio
				ModeloCRM::mdlDesplazarLeadsEnEtapa($tabla, $_POST["nuevoLeadEtapa"]);

				$datos = array(
					"id_cliente" => $_POST["nuevoLeadCliente"],
					"titulo" => $_POST["nuevoLeadTitulo"],
					"valor_estimado" => $_POST["nuevoLeadValor"],
					"prioridad" => $_POST["nuevoLeadPrioridad"],
					"etapa" => $_POST["nuevoLeadEtapa"],
					"id_vendedor" => $_POST["nuevoLeadVendedor"],
					"fecha_cierre" => !empty($_POST["nuevoLeadFechaCierre"]) ? $_POST["nuevoLeadFechaCierre"] : null,
					"notas" => $_POST["nuevoLeadNotas"],
					"codigo_orden" => null,
					"orden" => 1
				);

				$respuesta = ModeloCRM::mdlCrearLead($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡El lead ha sido guardado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "crm";
							}
						});
					</script>';

				} else {

					echo '<script>
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo guardar el lead en la base de datos.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';

				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡Error de formato!",
						text: "El título no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';

			}

		}

	}

	/*=============================================
	EDITAR LEAD
	=============================================*/
	public function ctrEditarLead() {

		if (isset($_POST["editarLeadTitulo"])) {

			if (!puedeAccion('crm', 'editar')) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error de permisos!",
						text: "No tienes permiso para editar leads.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			// VALIDAR CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\.\,\#\-\_\(\) ]+$/', $_POST["editarLeadTitulo"])) {

				$tabla = "crm_leads";

				$datos = array(
					"id" => $_POST["editarLeadId"],
					"id_cliente" => $_POST["editarLeadCliente"],
					"titulo" => $_POST["editarLeadTitulo"],
					"valor_estimado" => $_POST["editarLeadValor"],
					"prioridad" => $_POST["editarLeadPrioridad"],
					"etapa" => $_POST["editarLeadEtapa"],
					"id_vendedor" => $_POST["editarLeadVendedor"],
					"fecha_cierre" => !empty($_POST["editarLeadFechaCierre"]) ? $_POST["editarLeadFechaCierre"] : null,
					"notas" => $_POST["editarLeadNotas"],
					"origen" => isset($_POST["editarLeadOrigen"]) ? $_POST["editarLeadOrigen"] : null,
					"resumen_ia" => isset($_POST["editarLeadResumenIA"]) ? $_POST["editarLeadResumenIA"] : null,
					"productos_interes" => isset($_POST["editarLeadProductosInteres"]) ? $_POST["editarLeadProductosInteres"] : null,
					"fecha_ultima_interaccion" => !empty($_POST["editarLeadFechaUltimaInteraccion"]) ? $_POST["editarLeadFechaUltimaInteraccion"] : null
				);

				$respuesta = ModeloCRM::mdlEditarLead($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡El lead ha sido actualizado correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "crm";
							}
						});
					</script>';

				} else {

					echo '<script>
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo actualizar el lead en la base de datos.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';

				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡Error de formato!",
						text: "El título no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';

			}

		}

	}

	/*=============================================
	ELIMINAR LEAD
	=============================================*/
	public function ctrEliminarLead() {

		if (isset($_GET["idLeadEliminar"])) {

			if (!puedeAccion('crm', 'eliminar')) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error de permisos!",
						text: "No tienes permiso para eliminar leads.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			$tabla = "crm_leads";
			$idLead = $_GET["idLeadEliminar"];

			$respuesta = ModeloCRM::mdlEliminarLead($tabla, $idLead);

			if ($respuesta == "ok") {

				echo '<script>
					swal({
						type: "success",
						title: "¡El lead ha sido eliminado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then((result)=>{
						if(result.value){
							window.location = "crm";
						}
					});
				</script>';

			}

		}

	}

	/*=============================================
	MOSTRAR ETAPAS (COLUMNAS KANBAN)
	=============================================*/
	static public function ctrMostrarEtapas($item, $valor) {

		$tabla = "crm_etapas";
		$respuesta = ModeloCRM::mdlMostrarEtapas($tabla, $item, $valor);
		return $respuesta;

	}

	/*=============================================
	CREAR ETAPA
	=============================================*/
	public function ctrCrearEtapa() {

		if (isset($_POST["nuevaEtapaNombre"])) {

			if (!puedeAccion('crm', 'editar')) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error de permisos!",
						text: "No tienes permiso para crear etapas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			// VALIDAR CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaEtapaNombre"])) {

				$tabla = "crm_etapas";
				
				// Determinar el orden según la posición seleccionada
				$posicion = isset($_POST["nuevaEtapaPosicion"]) ? $_POST["nuevaEtapaPosicion"] : "ultimo";
				
				if ($posicion === "primero") {
					$orden = 1;
					// Desplazar todos desde 1
					ModeloCRM::mdlDesplazarEtapas($tabla, 1);
				} elseif ($posicion === "ultimo") {
					$etapasExistentes = self::ctrMostrarEtapas(null, null);
					$orden = count($etapasExistentes) + 1;
				} else {
					// Es un orden numérico (después del orden de referencia)
					$ordenReferencia = intval($posicion);
					$orden = $ordenReferencia + 1;
					// Desplazar todos desde el nuevo orden
					ModeloCRM::mdlDesplazarEtapas($tabla, $orden);
				}

				$datos = array(
					"nombre" => $_POST["nuevaEtapaNombre"],
					"color" => isset($_POST["nuevaEtapaColor"]) ? $_POST["nuevaEtapaColor"] : "#3c8dbc",
					"orden" => $orden,
					"editable" => 1
				);

				$respuesta = ModeloCRM::mdlCrearEtapa($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡La etapa ha sido creada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "crm";
							}
						});
					</script>';

				} else {

					echo '<script>
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo registrar la etapa en la base de datos.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';

				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡Error de formato!",
						text: "El nombre de la etapa no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';

			}

		}

	}

	/*=============================================
	EDITAR ETAPA
	=============================================*/
	public function ctrEditarEtapa() {

		if (isset($_POST["editarEtapaNombre"])) {

			if (!puedeAccion('crm', 'editar')) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error de permisos!",
						text: "No tienes permiso para editar etapas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			// VALIDAR CSRF
			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEtapaNombre"])) {

				$tabla = "crm_etapas";

				$datos = array(
					"id" => $_POST["idEtapa"],
					"nombre" => $_POST["editarEtapaNombre"],
					"color" => $_POST["editarEtapaColor"],
					"orden" => $_POST["editarEtapaOrden"]
				);

				$respuesta = ModeloCRM::mdlEditarEtapa($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡La etapa ha sido actualizada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "crm";
							}
						});
					</script>';

				} else {

					echo '<script>
						swal({
							type: "error",
							title: "Error",
							text: "No se pudo actualizar la etapa en la base de datos.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';

				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "¡Error de formato!",
						text: "El nombre de la etapa no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';

			}

		}

	}

	/*=============================================
	ELIMINAR ETAPA (CON VALIDACIÓN DE ESCENARIO A)
	=============================================*/
	public function ctrEliminarEtapa() {

		if (isset($_GET["idEtapaEliminar"])) {

			if (!puedeAccion('crm', 'editar')) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error de permisos!",
						text: "No tienes permiso para eliminar etapas.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			$idEtapa = $_GET["idEtapaEliminar"];
			
			// 1. Obtener los detalles de la etapa para saber su nombre
			$etapa = self::ctrMostrarEtapas("id", $idEtapa);

			if ($etapa) {

				// 2. Contar los leads en esta etapa
				$totalLeads = ModeloCRM::mdlContarLeadsEnEtapa("crm_leads", $etapa["nombre"]);

				// 3. Bloquear si tiene leads (Escenario A)
				if ($totalLeads > 0) {

					echo '<script>
						swal({
							type: "warning",
							title: "¡Acción no permitida!",
							text: "La columna \'' . $etapa["nombre"] . '\' no se puede eliminar porque tiene ' . $totalLeads . ' leads activos. Por favor, reubícalos primero.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "crm";
							}
						});
					</script>';
					return;

				}

				// 4. Proceder a eliminar si está vacía
				$tabla = "crm_etapas";
				$respuesta = ModeloCRM::mdlEliminarEtapa($tabla, $idEtapa);

				if ($respuesta == "ok") {

					echo '<script>
						swal({
							type: "success",
							title: "¡La etapa ha sido eliminada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then((result)=>{
							if(result.value){
								window.location = "crm";
							}
						});
					</script>';

				}

			}

		}

	}

}
