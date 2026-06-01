<?php

require_once "conexion.php";

class ModeloVariantes{

	/*=============================================
	MOSTRAR TIPOS DE VARIANTES
	=============================================*/

	static public function mdlMostrarTiposVariantes($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY orden ASC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY orden ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

	}

	/*=============================================
	MOSTRAR TIPOS DE VARIANTES SERVER-SIDE
	=============================================*/
	static public function mdlMostrarTiposVariantesServerSide($tabla, $where, $order, $limit)
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla $where $order $limit");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	/*=============================================
	OBTENER TOTAL TIPOS DE VARIANTES (PARA SERVER-SIDE)
	=============================================*/
	static public function mdlGetTotalTiposVariantes($tabla, $where)
	{
		$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM $tabla $where");
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	
    /*=============================================
    REGISTRO DE TIPO DE VARIANTE
    =============================================*/

    static public function mdlIngresarTipoVariante($tabla, $datos){

        // Verificar si el orden ya existe
        $stmtCheck = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden");
        $stmtCheck -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
        $stmtCheck -> execute();
        $existe = $stmtCheck -> fetch();

        // Si existe, mover todos los órdenes mayores o iguales
        if($existe){
            $stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden");
            $stmtUpdate -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
            $stmtUpdate -> execute();
        }

        // Insertar el nuevo registro
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, orden) VALUES (:nombre, :orden)");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);

        if($stmt->execute()){

            return "ok";

        }else{

            return "error";
        
        }

        $stmt->close();
        $stmt = null;

    }

	/*=============================================
	MOSTRAR OPCIONES DE VARIANTES
	=============================================*/

	static public function mdlMostrarOpcionesVariantes($tabla, $item, $valor){

		$idBodega = isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;
		error_log("VARIANTE DEBUG - Session Bodega: " . (isset($_SESSION["id_bodega"]) ? $_SESSION["id_bodega"] : 'no-set') . ", resolved idBodega: " . $idBodega);

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT ov.*, (SELECT COUNT(DISTINCT pvo.id_producto_variante) FROM productos_variantes_opciones pvo JOIN productos_variantes pv ON pvo.id_producto_variante = pv.id JOIN productos p ON pv.id_producto = p.id INNER JOIN productos_bodegas pb ON p.id = pb.id_producto AND pb.id_bodega = $idBodega INNER JOIN productos_variantes_bodegas pvb ON pv.id = pvb.id_variante AND pvb.id_bodega = $idBodega WHERE pvo.id_opcion_variante = ov.id AND p.eliminado = 0 AND pb.estado = 1 AND pv.estado = 1 AND pvb.estado = 1) as productos_asociados FROM $tabla ov WHERE ov.$item = :$item ORDER BY ov.orden ASC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT ov.*, (SELECT COUNT(DISTINCT pvo.id_producto_variante) FROM productos_variantes_opciones pvo JOIN productos_variantes pv ON pvo.id_producto_variante = pv.id JOIN productos p ON pv.id_producto = p.id INNER JOIN productos_bodegas pb ON p.id = pb.id_producto AND pb.id_bodega = $idBodega INNER JOIN productos_variantes_bodegas pvb ON pv.id = pvb.id_variante AND pvb.id_bodega = $idBodega WHERE pvo.id_opcion_variante = ov.id AND p.eliminado = 0 AND pb.estado = 1 AND pv.estado = 1 AND pvb.estado = 1) as productos_asociados FROM $tabla ov ORDER BY ov.orden ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

    
    /*=============================================
    REGISTRO DE OPCIÓN DE VARIANTE
    =============================================*/

    static public function mdlIngresarOpcionVariante($tabla, $datos){

        // Verificar si el orden ya existe en el mismo tipo de variante
        $stmtCheck = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden AND id_tipo_variante = :id_tipo_variante");
        $stmtCheck -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
        $stmtCheck -> bindParam(":id_tipo_variante", $datos["id_tipo_variante"], PDO::PARAM_INT);
        $stmtCheck -> execute();
        $existe = $stmtCheck -> fetch();

        // Si existe, mover todos los órdenes mayores o iguales del mismo tipo
        if($existe){
            $stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden AND id_tipo_variante = :id_tipo_variante");
            $stmtUpdate -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
            $stmtUpdate -> bindParam(":id_tipo_variante", $datos["id_tipo_variante"], PDO::PARAM_INT);
            $stmtUpdate -> execute();
        }

        // Insertar el nuevo registro
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_tipo_variante, nombre, orden) VALUES (:id_tipo_variante, :nombre, :orden)");

        $stmt->bindParam(":id_tipo_variante", $datos["id_tipo_variante"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);

        if($stmt->execute()){

            return "ok";

        }else{

            return "error";
        
        }

        $stmt->close();
        $stmt = null;

    }


    /*=============================================
    ACTUALIZAR TIPO DE VARIANTE
    =============================================*/

    static public function mdlActualizarTipoVariante($tabla, $item1, $valor1, $item2, $valor2){

        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

        $stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
        $stmt -> bindParam(":".$item2, $valor2, PDO::PARAM_STR);

        if($stmt -> execute()){

            return "ok";
            
        }else{

            return "error";	

        }

        $stmt -> close();

        $stmt = null;

    }

    /*=============================================
    ACTUALIZAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function mdlActualizarOpcionVariante($tabla, $item1, $valor1, $item2, $valor2){

        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

        $stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
        $stmt -> bindParam(":".$item2, $valor2, PDO::PARAM_STR);

        if($stmt -> execute()){

            return "ok";
            
        }else{

            return "error";	

        }

        $stmt -> close();

        $stmt = null;

    }


    /*=============================================
    EDITAR TIPO DE VARIANTE
    =============================================*/

    static public function mdlEditarTipoVariante($tabla, $datos){

        // Verificar si el orden ya existe en otro registro
        $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden AND id != :id");
        $stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
        $stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt -> execute();
        $existe = $stmt -> fetch();

        // Si existe, ajustar el orden de los demás
        if($existe){
            // Mover todos los órdenes mayores o iguales
            $stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden AND id != :id");
            $stmtUpdate -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
            $stmtUpdate -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
            $stmtUpdate -> execute();
        }

        // Actualizar el registro
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, orden = :orden WHERE id = :id");

        $stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
        $stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt -> execute()){
            return "ok";

        }else{
            return "error";
        
        }

        $stmt -> close();
        $stmt = null;
    }

    
    /*=============================================
    EDITAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function mdlEditarOpcionVariante($tabla, $datos){

        // Obtener el id_tipo_variante de la opción que se está editando
        $stmtTipo = Conexion::conectar()->prepare("SELECT id_tipo_variante FROM $tabla WHERE id = :id");
        $stmtTipo -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmtTipo -> execute();
        $tipoData = $stmtTipo -> fetch();

        // Verificar si el orden ya existe en otro registro del mismo tipo
        $stmt = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE orden = :orden AND id != :id AND id_tipo_variante = :id_tipo");
        $stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
        $stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
        $stmt -> bindParam(":id_tipo", $tipoData["id_tipo_variante"], PDO::PARAM_INT);
        $stmt -> execute();
        $existe = $stmt -> fetch();

        // Si existe, ajustar el orden de los demás
        if($existe){
            // Mover todos los órdenes mayores o iguales del mismo tipo
            $stmtUpdate = Conexion::conectar()->prepare("UPDATE $tabla SET orden = orden + 1 WHERE orden >= :orden AND id != :id AND id_tipo_variante = :id_tipo");
            $stmtUpdate -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
            $stmtUpdate -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
            $stmtUpdate -> bindParam(":id_tipo", $tipoData["id_tipo_variante"], PDO::PARAM_INT);
            $stmtUpdate -> execute();
        }

        // Actualizar el registro
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, orden = :orden WHERE id = :id");

        $stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
        $stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt -> execute()){
            return "ok";

        }else{
            return "error";
        
        }

        $stmt -> close();
        $stmt = null;
    }


    /*=============================================
    VERIFICAR SI TIPO DE VARIANTE ESTÁ EN USO (ACTIVO)
    =============================================*/ 

    static public function mdlVerificarUsoTipoVariante($idTipo){ 

        $stmt = Conexion::conectar()->prepare("
            SELECT COUNT(DISTINCT pvo.id_producto_variante) as total
            FROM productos_variantes_opciones pvo
            INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
            JOIN productos_variantes pv ON pvo.id_producto_variante = pv.id
            JOIN productos p ON pv.id_producto = p.id
            JOIN productos_bodegas pb ON p.id = pb.id_producto
            WHERE ov.id_tipo_variante = :id_tipo
            AND p.eliminado = 0 AND pb.estado = 1 AND pv.estado = 1
        ");

        $stmt->bindParam(":id_tipo", $idTipo, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado["total"]; 

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    CONTAR USO GLOBAL DE OPCIÓN (PRODUCTOS ACTIVOS)
    =============================================*/ 
    static public function mdlContarUsoGlobalOpcion($idOpcion){ 
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(DISTINCT pvo.id_producto_variante) 
			FROM productos_variantes_opciones pvo 
			JOIN productos_variantes pv ON pvo.id_producto_variante = pv.id 
			JOIN productos p ON pv.id_producto = p.id 
			JOIN productos_bodegas pb ON p.id = pb.id_producto 
            JOIN productos_variantes_bodegas pvb ON pv.id = pvb.id_variante AND pb.id_bodega = pvb.id_bodega
            JOIN bodegas b ON pb.id_bodega = b.id
			WHERE pvo.id_opcion_variante = :id_opcion 
			AND p.eliminado = 0 AND pb.estado = 1 AND pv.estado = 1 AND pvb.estado = 1 AND b.estado = 1
		");
		$stmt->bindParam(":id_opcion", $idOpcion, PDO::PARAM_INT); 
		$stmt->execute();
		return $stmt->fetchColumn();
    }

    /*=============================================
    CONTAR USO LOCAL DE OPCIÓN (PRODUCTOS ACTIVOS)
    =============================================*/ 
    static public function mdlContarUsoLocalOpcion($idOpcion){ 
		$idBodega = isset($_SESSION["id_bodega"]) && !empty($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;
		$stmt = Conexion::conectar()->prepare("
			SELECT COUNT(DISTINCT pvo.id_producto_variante) 
			FROM productos_variantes_opciones pvo 
			JOIN productos_variantes pv ON pvo.id_producto_variante = pv.id 
			JOIN productos p ON pv.id_producto = p.id 
			INNER JOIN productos_bodegas pb ON p.id = pb.id_producto AND pb.id_bodega = :id_bodega
			INNER JOIN productos_variantes_bodegas pvb ON pv.id = pvb.id_variante AND pvb.id_bodega = :id_bodega
			WHERE pvo.id_opcion_variante = :id_opcion 
			AND p.eliminado = 0 AND pb.estado = 1 AND pv.estado = 1 AND pvb.estado = 1
		");
		$stmt->bindParam(":id_opcion", $idOpcion, PDO::PARAM_INT); 
		$stmt->bindParam(":id_bodega", $idBodega, PDO::PARAM_INT); 
		$stmt->execute();
		return $stmt->fetchColumn();
    } 

    /*=============================================
    ELIMINAR TIPO DE VARIANTE
    =============================================*/ 

    static public function mdlEliminarTipoVariante($tabla, $id){ 

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id"); 

        $stmt->bindParam(":id", $id, PDO::PARAM_INT); 

        if($stmt->execute()){
            return "ok"; 

        }else{
            return "error";
        } 

        $stmt->close();
        $stmt = null;

    }

     /*=============================================
    ELIMINAR OPCIÓN DE VARIANTE
    =============================================*/

    static public function mdlEliminarOpcionVariante($tabla, $id){ 

        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id"); 

        $stmt->bindParam(":id", $id, PDO::PARAM_INT); 

        if($stmt->execute()){ 
            return "ok";
 
        }else{
            return "error";
        } 

        $stmt->close();
        $stmt = null; 
    }
    

}