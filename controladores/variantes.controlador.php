<?php

class ControladorVariantes{

	/*=============================================
	MOSTRAR TIPOS DE VARIANTES
	=============================================*/

	static public function ctrMostrarTiposVariantes($item, $valor){

		$tabla = "tipos_variantes";

		$respuesta = ModeloVariantes::mdlMostrarTiposVariantes($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR TIPOS DE VARIANTES SERVER-SIDE
	=============================================*/
	static public function ctrMostrarTiposVariantesServerSide($params)
	{
		$tabla = "tipos_variantes";

		// Columnas para ordenar
		$columnsMap = array(
			0 => 'nombre',
			1 => 'estado',
			2 => 'id' // Acciones
		);

		$where = " WHERE 1=1 ";

		// Búsqueda global (DataTables)
		if (!empty($params['search']['value'])) {
			$searchValue = $params['search']['value'];
			$where .= " AND nombre LIKE '%$searchValue%' ";
		}

		// Ordenar
		$order = "";
		if (isset($params['order'][0]['column'])) {
			$colIdx = $params['order'][0]['column'];
			$colName = isset($columnsMap[$colIdx]) ? $columnsMap[$colIdx] : 'nombre';
			$order = " ORDER BY " . $colName . " " . $params['order'][0]['dir'];
		} else {
			$order = " ORDER BY nombre ASC";
		}

		// Paginación
		$limit = "";
		if ($params['length'] != -1) {
			$limit = " LIMIT " . $params['start'] . ", " . $params['length'];
		}

		// Obtener datos
		$variantes = ModeloVariantes::mdlMostrarTiposVariantesServerSide($tabla, $where, $order, $limit);
		$totalData = ModeloVariantes::mdlGetTotalTiposVariantes($tabla, " WHERE 1=1 ");
		$totalFiltered = ModeloVariantes::mdlGetTotalTiposVariantes($tabla, $where);

		$data = array();

		foreach ($variantes as $key => $value) {
			
			$nestedData = array();

			// 0: Nombre
			$nestedData[] = e($value["nombre"]);

			// 1: Estado
			$estadoHtml = "";
			if (puedeAccion('variantes', 'editar')) {
				if ($value["estado"] != 0) {
					$estadoHtml = '<button class="btn btn-success btn-xs btnActivarTipo" idTipo="' . $value["id"] . '" estadoTipo="0">Activado</button>';
				} else {
					$estadoHtml = '<button class="btn btn-danger btn-xs btnActivarTipo" idTipo="' . $value["id"] . '" estadoTipo="1">Desactivado</button>';
				}
			} else {
				if ($value["estado"] != 0) {
					$estadoHtml = '<button class="btn btn-success btn-xs">Activado</button>';
				} else {
					$estadoHtml = '<button class="btn btn-danger btn-xs">Desactivado</button>';
				}
			}
			$nestedData[] = $estadoHtml;

			// 2: Acciones
			$botonesAcciones = '<div class="btn-group">';
			if (puedeAccion('variantes', 'editar')) {
				$botonesAcciones .= '<button class="btn btn-warning btnEditarTipoVariante" idTipo="' . $value["id"] . '" data-toggle="modal" data-target="#modalEditarTipoVariante" title="Editar tipo"><i class="fa fa-pencil"></i></button>';
			}
			$botonesAcciones .= '<button class="btn btn-info btnVerOpciones" idTipo="' . $value["id"] . '" nombreTipo="' . e($value["nombre"]) . '" title="Ver opciones"><i class="fa fa-list"></i> Opciones</button>';
			if (puedeAccion('variantes', 'eliminar')) {
				$botonesAcciones .= '<button class="btn btn-danger btnEliminarTipo" idTipo="' . $value["id"] . '" nombreTipo="' . e($value["nombre"]) . '" title="Eliminar tipo"><i class="fa fa-times"></i></button>';
			}
			$botonesAcciones .= '</div>';
			$nestedData[] = $botonesAcciones;

			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval($params['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);

		return $json_data;
	}

	/*=============================================
	CREAR TIPO DE VARIANTE
	=============================================*/

	static public function ctrCrearTipoVariante(){

		if(isset($_POST["nuevoTipoVariante"])){

			if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoTipoVariante"])){

				$tabla = "tipos_variantes";

				$datos = array("nombre" => $_POST["nuevoTipoVariante"],
				               "orden" => $_POST["nuevoOrdenTipo"]);

				$respuesta = ModeloVariantes::mdlIngresarTipoVariante($tabla, $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "El tipo de variante ha sido guardado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
								window.location = "variantes";
								})

					</script>';

				}


			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡El tipo de variante no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(() => {
								window.location = "variantes";
						})

			  	</script>';

			}

		}

	}

	/*=============================================
	MOSTRAR OPCIONES DE VARIANTES
	=============================================*/

	static public function ctrMostrarOpcionesVariantes($item, $valor){

		$tabla = "opciones_variantes";

		$respuesta = ModeloVariantes::mdlMostrarOpcionesVariantes($tabla, $item, $valor);

		return $respuesta;

	}

	
    /*=============================================
    CREAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function ctrCrearOpcionVariante(){

        if(isset($_POST["nuevaOpcion"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevaOpcion"])){

                $tabla = "opciones_variantes";

                $datos = array("id_tipo_variante" => $_POST["idTipoVarianteOpcion"],
                            "nombre" => $_POST["nuevaOpcion"],
                            "orden" => $_POST["nuevoOrdenOpcion"]);

                $respuesta = ModeloVariantes::mdlIngresarOpcionVariante($tabla, $datos);

                if($respuesta == "ok"){

                    echo'<script>

                    swal({
                        type: "success",
                        title: "La opción ha sido guardada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(() => {
                                    window.location = "variantes";
                                })

                    </script>';

                }


            }else{

                echo'<script>

                    swal({
                        type: "error",
                        title: "¡La opción no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {

                            window.location = "variantes";

                            }
                        })

                </script>';

            }

        }

    }



    /*=============================================
    EDITAR TIPO DE VARIANTE
    =============================================*/

    static public function ctrEditarTipoVariante(){

        if(isset($_POST["editarTipoVariante"])){

            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarTipoVariante"])){

                $tabla = "tipos_variantes";

                $datos = array("nombre" => $_POST["editarTipoVariante"],
                            "orden" => $_POST["editarOrdenTipo"],
                            "id" => $_POST["idTipo"]);

                $respuesta = ModeloVariantes::mdlEditarTipoVariante($tabla, $datos);

                if($respuesta == "ok"){

                    echo'<script>

                    swal({
                        type: "success",
                        title: "El tipo de variante ha sido actualizado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(() => {
                                    window.location = "variantes";
                                })

                    </script>';

                }

            }else{

                echo'<script>

                    swal({
                        type: "error",
                        title: "¡El tipo de variante no puede ir vacío o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(() => {
                                window.location = "variantes";
                        })

                </script>';

            }

        }

    }


    
    /*=============================================
    EDITAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function ctrEditarOpcionVariante(){

        if(isset($_POST["editarOpcion"])){

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarOpcion"])){

                $tabla = "opciones_variantes";

                $datos = array("nombre" => $_POST["editarOpcion"],
                            "orden" => $_POST["editarOrdenOpcion"],
                            "id" => $_POST["idOpcion"]);

                $respuesta = ModeloVariantes::mdlEditarOpcionVariante($tabla, $datos);

                if($respuesta == "ok"){

                    echo'<script>

                    swal({
                        type: "success",
                        title: "La opción ha sido actualizada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(() => {
                                    window.location = "variantes";
                                })

                    </script>';

                }

            }else{

                echo'<script>

                    swal({
                        type: "error",
                        title: "¡La opción no puede ir vacía o llevar caracteres especiales!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {

                            window.location = "variantes";

                            }
                        })

                </script>';

            }

        }

    }


    /*=============================================
    ELIMINAR TIPO DE VARIANTE
    =============================================*/ 

    static public function ctrEliminarTipoVariante($idTipo){ 

        // Verificar si el tipo tiene opciones asociadas
        $tabla = "opciones_variantes";
        $item = "id_tipo_variante";
        $valor = $idTipo;

        $opciones = ModeloVariantes::mdlMostrarOpcionesVariantes($tabla, $item, $valor); 

        if(count($opciones) > 0){
            return "error_opciones";

        } 

        // Verificar si el tipo está siendo usado en productos
        $tabla2 = "productos_variantes_opciones";
        $checkUso = ModeloVariantes::mdlVerificarUsoTipoVariante($idTipo);

         if($checkUso > 0){
            return "error_uso";

        }

         // Si no tiene opciones ni está en uso, eliminar
        $tabla3 = "tipos_variantes";
        $respuesta = ModeloVariantes::mdlEliminarTipoVariante($tabla3, $idTipo);

        return $respuesta; 
    }

     /*=============================================
    ELIMINAR OPCIÓN DE VARIANTE
    =============================================*/

     static public function ctrEliminarOpcionVariante($idOpcion){ 

        // Verificar si la opción está siendo usada en productos activos
        $usoGlobal = ModeloVariantes::mdlContarUsoGlobalOpcion($idOpcion); 

        if($usoGlobal > 0){
            $usoLocal = ModeloVariantes::mdlContarUsoLocalOpcion($idOpcion);
            if ($usoLocal == 0) {
                return "error_productos_asociados_otra_sucursal";
            }
            return "error";
        }

        // Si no está en uso, eliminar
        $tabla2 = "opciones_variantes";
        $respuesta = ModeloVariantes::mdlEliminarOpcionVariante($tabla2, $idOpcion);

         return $respuesta;
    }

}