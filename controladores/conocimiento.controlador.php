<?php

class ControladorConocimiento
{
	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/
	static public function ctrMostrarCategorias($item, $valor)
	{
		$tabla = "empresa_conocimiento_categorias";
		return ModeloConocimiento::mdlMostrarCategorias($tabla, $item, $valor);
	}

	/*=============================================
	CREAR CATEGORIA
	=============================================*/
	static public function ctrCrearCategoria()
	{
		if (isset($_POST["nuevaCategoriaNombre"])) {

			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaCategoriaNombre"])) {

				$tabla = "empresa_conocimiento_categorias";
				$datos = array("nombre" => $_POST["nuevaCategoriaNombre"]);

				$respuesta = ModeloConocimiento::mdlIngresarCategoria($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "¡La categoría ha sido guardada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "conocimiento";
						});
					</script>';
				} else {
					echo '<script>
						swal({
							type: "error",
							title: "¡Error!",
							text: "No se pudo guardar la categoría.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "La categoría no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	EDITAR CATEGORIA
	=============================================*/
	static public function ctrEditarCategoria()
	{
		if (isset($_POST["editarCategoriaNombre"])) {

			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
				return;
			}

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCategoriaNombre"])) {

				$tabla = "empresa_conocimiento_categorias";
				$datos = array(
					"nombre" => $_POST["editarCategoriaNombre"],
					"id" => $_POST["idCategoria"]
				);

				$respuesta = ModeloConocimiento::mdlEditarCategoria($tabla, $datos);

				if ($respuesta == "ok") {
					echo '<script>
						swal({
							type: "success",
							title: "¡La categoría ha sido editada correctamente!",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						}).then(() => {
							window.location = "conocimiento";
						});
					</script>';
				} else {
					echo '<script>
						swal({
							type: "error",
							title: "¡Error!",
							text: "No se pudo actualizar la categoría.",
							showConfirmButton: true,
							confirmButtonText: "Cerrar"
						});
					</script>';
				}
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "El nombre de la categoría no puede llevar caracteres especiales.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR CATEGORIA
	=============================================*/
	static public function ctrEliminarCategoria()
	{
		if (isset($_GET["idCategoriaEliminar"])) {

			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
				return;
			}

			$tabla = "empresa_conocimiento_categorias";
			$id = $_GET["idCategoriaEliminar"];

			// Verificar si existen artículos asociados a esta categoría
			$tablaArticulos = "empresa_conocimiento";
			$articulosAsociados = ModeloConocimiento::mdlMostrarArticulos($tablaArticulos, "id_categoria", $id);

			if ($articulosAsociados) {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "No se puede eliminar la categoría porque tiene artículos asociados.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
				return;
			}

			$respuesta = ModeloConocimiento::mdlEliminarCategoria($tabla, $id);

			if ($respuesta == "ok") {
				echo '<script>
					swal({
						type: "success",
						title: "¡La categoría ha sido eliminada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "No se pudo eliminar la categoría.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR ARTICULOS
	=============================================*/
	static public function ctrMostrarArticulos($item, $valor)
	{
		$tabla = "empresa_conocimiento";
		return ModeloConocimiento::mdlMostrarArticulos($tabla, $item, $valor);
	}

	/*=============================================
	MOSTRAR ARTICULOS SERVER-SIDE
	=============================================*/
	static public function ctrMostrarArticulosServerSide($params)
	{
		$tabla = "empresa_conocimiento";

		$columns = array(
			0 => 'a.titulo',
			1 => 'c.nombre',
			2 => 'a.palabras_clave',
			3 => 'a.created_at',
			4 => 'a.id'
		);

		$where = " WHERE 1=1 ";

		// Filtro de búsqueda (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND (a.titulo LIKE '%$searchValue%' OR c.nombre LIKE '%$searchValue%' OR a.palabras_clave LIKE '%$searchValue%') ";
		}

		// Filtro por Categoría
		if (!empty($params['categoriaFiltro'])) {
			$catFiltro = intval($params['categoriaFiltro']);
			$where .= " AND a.id_categoria = $catFiltro ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$order = " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY a.id DESC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . intval($params['start']) . ", " . intval($params['length']);
		}

		// Obtener datos
		$articulos = ModeloConocimiento::mdlMostrarArticulosServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloConocimiento::mdlGetTotalArticulos($tabla, " WHERE 1=1 ");
		$totalFiltered = ModeloConocimiento::mdlGetTotalArticulos($tabla, $where);

		$data = array();

		foreach ($articulos as $key => $value) {
			$nestedData = array();

			$nestedData[] = e($value["titulo"]);
			$nestedData[] = e($value["nombre_categoria"]);
			$nestedData[] = e($value["palabras_clave"] ?? '');
			$nestedData[] = date("d-m-Y H:i:s", strtotime($value["created_at"]));

			// Acciones
			$acciones = '<div class="btn-group">';
			$acciones .= '<button class="btn btn-info btnVerArticulo" idArticulo="' . $value["id"] . '" data-toggle="modal" data-target="#modalVerArticulo" title="Ver Artículo"><i class="fa fa-eye"></i></button>';

			if (puedeAccion('conocimiento', 'editar')) {
				$acciones .= '<button class="btn btn-warning btnEditarArticulo" idArticulo="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarArticulo" title="Editar"><i class="fa fa-pencil"></i></button>';
			}
			if (puedeAccion('conocimiento', 'eliminar')) {
				$acciones .= '<button class="btn btn-danger btnEliminarArticulo" idArticulo="' . $value["id"] . '" title="Eliminar"><i class="fa fa-times"></i></button>';
			}
			$acciones .= '</div>';

			$nestedData[] = $acciones;
			$nestedData[] = $value["id"];

			$data[] = $nestedData;
		}

		return array(
			"draw" => intval($params['draw']),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data
		);
	}

	/*=============================================
    CREAR ARTICULO
    =============================================*/
    static public function ctrCrearArticulo()
    {
    	if (isset($_POST["nuevoArticuloTitulo"])) {
    
    		if (!CSRF::validateToken()) {
    			echo '<script>
    				swal({
    					type: "error",
    					title: "Error de seguridad",
    					text: "Token CSRF inválido. Recarga la página.",
    					showConfirmButton: true,
    					confirmButtonText: "Cerrar"
    				}).then(() => {
    					window.location = "conocimiento";
    				});
    			</script>';
    			return;
    		}
    
    		if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\.\,\#\-\_\(\) ]+$/', $_POST["nuevoArticuloTitulo"])) {
    
    			$tabla = "empresa_conocimiento";
    
    			$datos = array(
    				"id_categoria"   => $_POST["nuevoArticuloCategoria"],
    				"titulo"         => $_POST["nuevoArticuloTitulo"],
    				"contenido"      => $_POST["nuevoArticuloContenido"],
    				"palabras_clave" => !empty($_POST["nuevoArticuloKeywords"]) ? $_POST["nuevoArticuloKeywords"] : null
    			);
    
    			$respuesta = ModeloConocimiento::mdlIngresarArticulo($tabla, $datos);
    
    			if ($respuesta["ok"]) {
    
    				$idArticulo = $respuesta["id"];

                    self::sincronizarQdrant("crear", $idArticulo);
    
    				echo '<script>
    					swal({
    						type: "success",
    						title: "¡El artículo ha sido guardado correctamente!",
    						showConfirmButton: true,
    						confirmButtonText: "Cerrar"
    					}).then(() => {
    						window.location = "conocimiento";
    					});
    				</script>';
    
    			} else {
    
    				echo '<script>
    					swal({
    						type: "error",
    						title: "¡Error!",
    						text: "No se pudo guardar el artículo.",
    						showConfirmButton: true,
    						confirmButtonText: "Cerrar"
    					});
    				</script>';
    
    			}
    
    		} else {
    
    			echo '<script>
    				swal({
    					type: "error",
    					title: "¡Error!",
    					text: "El título no puede llevar caracteres especiales.",
    					showConfirmButton: true,
    					confirmButtonText: "Cerrar"
    				});
    			</script>';
    
    		}
    
    	}
    }

	/*=============================================
    EDITAR ARTICULO
    =============================================*/
    static public function ctrEditarArticulo()
    {
    	if (isset($_POST["editarArticuloTitulo"])) {
    
    		if (!CSRF::validateToken()) {
    			echo '<script>
    				swal({
    					type: "error",
    					title: "Error de seguridad",
    					text: "Token CSRF inválido. Recarga la página.",
    					showConfirmButton: true,
    					confirmButtonText: "Cerrar"
    				}).then(() => {
    					window.location = "conocimiento";
    				});
    			</script>';
    			return;
    		}
    
    		if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\.\,\#\-\_\(\) ]+$/', $_POST["editarArticuloTitulo"])) {
    
    			$tabla = "empresa_conocimiento";
    
    			$datos = array(
    				"id"              => $_POST["idArticulo"],
    				"id_categoria"    => $_POST["editarArticuloCategoria"],
    				"titulo"          => $_POST["editarArticuloTitulo"],
    				"contenido"       => $_POST["editarArticuloContenido"],
    				"palabras_clave"  => !empty($_POST["editarArticuloKeywords"]) ? $_POST["editarArticuloKeywords"] : null
    			);
    
    			$respuesta = ModeloConocimiento::mdlEditarArticulo($tabla, $datos);
    
    			if ($respuesta == "ok") {
    
    				// Sincronizar el artículo actualizado con Qdrant
    				self::sincronizarQdrant("actualizar", $_POST["idArticulo"]);
    
    				echo '<script>
    					swal({
    						type: "success",
    						title: "¡El artículo ha sido editado correctamente!",
    						showConfirmButton: true,
    						confirmButtonText: "Cerrar"
    					}).then(() => {
    						window.location = "conocimiento";
    					});
    				</script>';
    
    			} else {
    
    				echo '<script>
    					swal({
    						type: "error",
    						title: "¡Error!",
    						text: "No se pudo actualizar el artículo.",
    						showConfirmButton: true,
    						confirmButtonText: "Cerrar"
    					});
    				</script>';
    
    			}
    
    		} else {
    
    			echo '<script>
    				swal({
    					type: "error",
    					title: "¡Error!",
    					text: "El título no puede llevar caracteres especiales.",
    					showConfirmButton: true,
    					confirmButtonText: "Cerrar"
    				});
    			</script>';
    
    		}
    
    	}
    }

	/*=============================================
	ELIMINAR ARTICULO
	=============================================*/
	static public function ctrEliminarArticulo()
	{
		if (isset($_GET["idArticuloEliminar"])) {

			if (!CSRF::validateToken()) {
				echo '<script>
					swal({
						type: "error",
						title: "Error de seguridad",
						text: "Token CSRF inválido. Recarga la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
				return;
			}

			$tabla = "empresa_conocimiento";
			$id = $_GET["idArticuloEliminar"];

			$respuesta = ModeloConocimiento::mdlEliminarArticulo($tabla, $id);

			if ($respuesta == "ok") {
			    
			    self::sincronizarQdrant("eliminar", $id);
				echo '<script>
					swal({
						type: "success",
						title: "¡El artículo ha sido eliminado correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(() => {
						window.location = "conocimiento";
					});
				</script>';
			} else {
				echo '<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "No se pudo eliminar el artículo.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}
	
	
    /*=============================================
    SINCRONIZAR CON QDRANT (N8N)
    =============================================*/
    public static function sincronizarQdrant($accion, $idArticulo)
    {
    
        // Si es eliminar, solo enviamos el ID
        if ($accion == "eliminar") {
    
            $datos = array(
                "accion" => "eliminar",
                "registro" => array(
                    "id" => (int)$idArticulo
                )
            );
    
        } else {
    
            // Obtener el artículo completo desde la BD
            $articulo = ModeloConocimiento::mdlMostrarArticulos(
                "empresa_conocimiento",
                "id",
                $idArticulo
            );
    
            if (!$articulo) {
                return;
            }
    
            $datos = array(
                "accion" => $accion,
                "registro" => array(
                    "id" => (int)$articulo["id"],
                    "categoria" => $articulo["nombre_categoria"],
                    "titulo" => $articulo["titulo"],
                    "contenido" => $articulo["contenido"],
                    "palabras_clave" => $articulo["palabras_clave"],
                    "estado" => $articulo["estado"]
                )
            );
        }
    
        $url = "https://master-n8n.la6x8e.easypanel.host/webhook/qdrant-sync";
    
        $curl = curl_init($url);
    
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($datos, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
            CURLOPT_TIMEOUT => 2
        ));
    
        curl_exec($curl);
        curl_close($curl);
    }
    
    
}
