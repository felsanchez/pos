<?php

require_once "conexion.php";

class ModeloActividades{

	/*=============================================
	CREAR ACTIVIDAD
	=============================================*/

	static public function mdlIngresarActividad($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(descripcion, tipo, id_user, fecha, estado, id_cliente, observacion, id_bodega) VALUES (:descripcion, :tipo, :id_user, :fecha, :estado, :id_cliente, :observacion, :id_bodega)");

		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":id_user", $datos["id_user"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
		$stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);
		$idBodega = isset($datos["id_bodega"]) ? intval($datos["id_bodega"]) : 1;
		$stmt->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";
		}
		else{

			return "error";
		}

		$stmt -> close();
		$stmt = null;

	}


	/*=============================================
	MOSTRAR actividades
	=============================================*/

	static public function mdlMostrarActividades($tabla, $item, $valor){

		if($item != null){

			// Si el item es un array (vinitiendo filtros combinados)
			if(is_array($item)){

				$query = "SELECT * FROM $tabla WHERE ";
				$condiciones = array();
				
				foreach($item as $key => $columna){
					$condiciones[] = "$columna = :$columna";
				}
				
				$query .= implode(" AND ", $condiciones) . " ORDER BY id DESC";
				
				$stmt = Conexion::conectar()->prepare($query);
				
				foreach($item as $key => $columna){
					$stmt->bindParam(":".$columna, $valor[$key], PDO::PARAM_STR);
				}
				
				$stmt->execute();
				return $stmt->fetchAll();

			} else {
				// Comportamiento original para un solo item
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");
				$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
				$stmt -> execute();

				// Si se busca por ID (usualmente para editar), se usa fetch(), sino fetchAll()
				if($item == "id"){
					return $stmt -> fetch();
				} else {
					return $stmt -> fetchAll();
				}
			}

		}
		else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}

		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	MOSTRAR ACTIVIDADES SERVER-SIDE
	=============================================*/
	static public function mdlMostrarActividadesServerSide($tabla, $where, $order, $limit)
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT a.*, 
				c.nombre as nombre_cliente,
				u.nombre as nombre_usuario
			FROM $tabla a
			LEFT JOIN clientes c ON a.id_cliente = c.id
			LEFT JOIN usuarios u ON a.id_user = u.id
			$where $order $limit
		");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/*=============================================
	OBTENER TOTAL ACTIVIDADES (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalActividades($tabla, $where)
	{
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(*) 
			FROM $tabla a
			LEFT JOIN clientes c ON a.id_cliente = c.id
			LEFT JOIN usuarios u ON a.id_user = u.id
			$where
		");
		$stmt->execute();
		return $stmt->fetchColumn();
	}


	/*=============================================
	EDITAR actividades
	=============================================*/

	static public function mdlEditarActividad($tabla, $datos){

		//var_dump($datos["fecha"]);

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET descripcion = :descripcion, tipo = :tipo, id_user = :id_user, estado = :estado, id_cliente = :id_cliente, observacion = :observacion WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":id_user", $datos["id_user"], PDO::PARAM_STR);
		//$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
		$stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";
		}
		else{

			return "error";
		}

		$stmt -> close();
		$stmt = null;

	}


	/*=============================================
	ELIMINAR Actividad
	=============================================*/

	static public function mdlEliminarActividad($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		}
		else{

			return "error";
		}

		$stmt -> close();
		$stmt = null;

	}


	/*=============================================
	ACTUALIZAR actividad
	=============================================*/

	static public function mdlActualizarActividad($tabla, $item1, $valor1, $valor){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		}
		else{

			return "error";
		}

		$stmt -> close();
		$stmt = null;

	}


	/*=============================================
	Guardar Tipo de Actividad
	=============================================*/
	public static function mdlActualizarTipoActividad($tabla, $datos) {
		$conn = Conexion::conectar();
	
		if ($tabla !== 'actividades') {
			return ["status" => "error", "message" => "Tabla no permitida"];
		}
	
		$update = $conn->prepare("UPDATE actividades SET tipo = :tipo WHERE id = :id");
		$update->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$update->bindParam(":id", $datos["id"], PDO::PARAM_INT);
	
		if ($update->execute()) {
			$select = $conn->prepare("SELECT * FROM actividades WHERE id = :id");
			$select->bindParam(":id", $datos["id"], PDO::PARAM_INT);
			$select->execute();
			$actividad = $select->fetch(PDO::FETCH_ASSOC);
	
			return $actividad;
		} else {
			$errorInfo = $update->errorInfo();
			return ["status" => "error", "message" => $errorInfo[2]];
		}
	}
	
	

	/*=============================================
	ACTUALIZAR Estado
	=============================================*/
	static public function mdlActualizarEstadoActividad($tabla, $datos) {
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET estado = :estado WHERE id = :id");
	  
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
	  
		if($stmt->execute()) {
		  return "ok";
		} else {
		  return "error"; // o usa: return $stmt->errorInfo();
		}
	  
		//$stmt->close();
		$stmt = null;
	  }

	
	  	  /*=============================================
		ACTUALIZAR Observacion
		=============================================*/
		static public function mdlActualizarObservacion($tabla, $id, $observacion) {
			$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET observacion = :observacion WHERE id = :id");
			$stmt->bindParam(":observacion", $observacion, PDO::PARAM_STR);
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		
			if ($stmt->execute()) {
				return "ok";
			} else {
				return "error";
			}
		
			$stmt = null;
		}





		


//CUADRO ACTIVIDADES CON CLIENTE********************************************************
		static public function mdlMostrarActividadesConCliente($tabla, $item, $valor, $idBodega = null){
    $bodegaFiltro = ($idBodega !== null) ? " AND a.id_bodega = " . intval($idBodega) : "";
    
    if($item != null){
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                a.*, 
                c.nombre as nombre_cliente,
                u.nombre as nombre_usuario
            FROM $tabla a
            LEFT JOIN clientes c ON a.id_cliente = c.id
            LEFT JOIN usuarios u ON a.id_user = u.id
            WHERE a.$item = :$item $bodegaFiltro
        ");
        $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
        $stmt->execute();
        
        if($item == "id"){
            return $stmt->fetch();
        } else {
            return $stmt->fetchAll();
        }
    } 
    else{
        $bodegaFiltroSolo = ($idBodega !== null) ? "WHERE a.id_bodega = " . intval($idBodega) : "";
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                a.*, 
                c.nombre as nombre_cliente,
                u.nombre as nombre_usuario
            FROM $tabla a
            LEFT JOIN clientes c ON a.id_cliente = c.id
            LEFT JOIN usuarios u ON a.id_user = u.id
            $bodegaFiltroSolo
            ORDER BY a.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(); 
    }
    $stmt->close();
    $stmt = null;
}


		static public function mdlMostrarUsuarios(){
			$stmt = Conexion::conectar()->prepare("
				SELECT id, nombre 
				FROM usuarios 
				ORDER BY nombre ASC
			");
			$stmt -> execute();
			return $stmt -> fetchAll();
			$stmt -> close();
			$stmt = null;
		}



}