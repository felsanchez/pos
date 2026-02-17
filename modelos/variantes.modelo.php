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

		$stmt = null;

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

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY orden ASC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY orden ASC");

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
    VERIFICAR SI TIPO DE VARIANTE ESTÁ EN USO
    =============================================*/ 

    static public function mdlVerificarUsoTipoVariante($idTipo){ 

        $stmt = Conexion::conectar()->prepare("
            SELECT COUNT(*) as total
            FROM productos_variantes_opciones pvo
            INNER JOIN opciones_variantes ov ON pvo.id_opcion_variante = ov.id
            WHERE ov.id_tipo_variante = :id_tipo
        ");

        $stmt->bindParam(":id_tipo", $idTipo, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado["total"]; 

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    VERIFICAR SI OPCIÓN DE VARIANTE ESTÁ EN USO
    =============================================*/ 

    static public function mdlVerificarUsoOpcionVariante($tabla, $item, $valor){ 

        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla WHERE $item = :$item"); 

        $stmt->bindParam(":".$item, $valor, PDO::PARAM_INT);

        $stmt->execute(); 

        $resultado = $stmt->fetch(); 

        return $resultado["total"]; 

        $stmt->close();

        $stmt = null;

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