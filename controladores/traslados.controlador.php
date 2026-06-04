<?php

class ControladorTraslados
{

    /*=============================================
    MOSTRAR TRASLADOS
    =============================================*/
    static public function ctrMostrarTraslados($item, $valor, $fechaInicial = null, $fechaFinal = null)
    {
        $tabla = "traslados";

        // Limpieza automática de registros más antiguos de 3 meses
        ModeloTraslados::mdlLimpiarTrasladosAntiguos($tabla);

        return ModeloTraslados::mdlMostrarTraslados($tabla, $item, $valor, $fechaInicial, $fechaFinal);
    }    /*=============================================
    CREAR TRASLADO
    =============================================*/
    static public function ctrCrearTraslado()
    {
        if (isset($_POST["nuevaBodegaDestino"])) {

            $paginaDestino = puedeVer("traslados") ? "traslados" : "crear-traslado";

            if ($_POST["nuevaBodegaOrigen"] == $_POST["nuevaBodegaDestino"]) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "La bodega de origen y destino no pueden ser la misma",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        window.location = "' . $paginaDestino . '";
                    })
                </script>';
                return;
            }

            $db = Conexion::conectar();

            try {
                $db->beginTransaction();

                $tabla = "traslados";
                $listaProductos = json_decode($_POST["listaProductos"], true);
                if (empty($listaProductos)) {
                    throw new Exception("Debe seleccionar al menos un producto para realizar el traslado.");
                }
                $totalItems = 0;
                foreach ($listaProductos as $producto) {
                    $totalItems += $producto["cantidad"];
                }

                $idOrigen = $_POST["nuevaBodegaOrigen"];
                $idDestino = $_POST["nuevaBodegaDestino"];
                $codigoTraslado = $_POST["nuevoCodigoTraslado"];

                $datos = array(
                    "codigo" => $codigoTraslado,
                    "id_bodega_origen" => $idOrigen,
                    "id_bodega_destino" => $idDestino,
                    "id_usuario" => $_SESSION["id"],
                    "total_items" => $totalItems,
                    "notas" => $_POST["nuevasNotas"],
                    "estado" => "completado"
                );

                $idTraslado = ModeloTraslados::mdlIngresarTraslado($tabla, $datos);

                if (!is_numeric($idTraslado)) {
                    throw new Exception("Error al registrar la cabecera del traslado.");
                }

                $tablaItems = "traslados_items";
                foreach ($listaProductos as $value) {
                    
                    $esVariante = (isset($value["esVariante"]) && $value["esVariante"] == "1") ? "variante" : "producto";
                    $idProducto = $value["id"];
                    $idVariante = (isset($value["idVariante"]) && !empty($value["idVariante"])) ? $value["idVariante"] : null;
                    $cantidad = floatval($value["cantidad"]);

                    $datosItem = array(
                        "id_traslado" => $idTraslado,
                        "tipo_producto" => $esVariante,
                        "id_producto" => $idProducto,
                        "id_variante" => $idVariante,
                        "cantidad" => $cantidad
                    );

                    $respuestaItem = ModeloTraslados::mdlIngresarItemTraslado($tablaItems, $datosItem);
                    if ($respuestaItem != "ok") {
                        throw new Exception("Error al registrar uno de los productos en el traslado.");
                    }

                    // --- ACTUALIZACIÓN DE INVENTARIO ---
                    if ($esVariante == "variante") {
                        
                        // 1. Origen: Descontar
                        $traerVarianteO = ModeloProductos::mdlObtenerVariantePorId($idVariante, $idOrigen);
                        if (!$traerVarianteO) {
                            throw new Exception("No se encontró la variante en la bodega de origen.");
                        }
                        $nuevoStockO = $traerVarianteO["stock"] - $cantidad;
                        
                        $res1 = ModeloProductos::mdlActualizarStockVarianteBodega($idVariante, $idOrigen, $nuevoStockO);
                        if ($res1 != "ok") {
                            throw new Exception("Error al actualizar el stock de la variante en origen.");
                        }

                        // Sincronizar el stock del producto base en la bodega de origen
                        $stmtSincO = $db->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id WHERE pv.id_producto = :id_producto AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
                        $stmtSincO->execute([":id_producto" => $idProducto, ":id_bodega" => $idOrigen]);
                        $resSincO = $stmtSincO->fetch();
                        $stockBaseO = ($resSincO && $resSincO["total"]) ? intval($resSincO["total"]) : 0;
                        ModeloProductos::mdlActualizarStockBodega($idProducto, $idOrigen, $stockBaseO);
                        
                        ControladorMovimientos::ctrRegistrarMovimiento("variante", $idProducto, $idVariante, $value["descripcion"], "traslado_salida", -$cantidad, $traerVarianteO["stock"], $nuevoStockO, "Traslado #" . $codigoTraslado, "", $idOrigen);

                        // 2. Destino: Aumentar
                        $traerVarianteD = ModeloProductos::mdlObtenerVariantePorId($idVariante, $idDestino);
                        $stockActualD = (is_array($traerVarianteD) && isset($traerVarianteD["stock"])) ? $traerVarianteD["stock"] : 0;
                        $nuevoStockD = $stockActualD + $cantidad;
                        
                        $res2 = ModeloProductos::mdlActualizarStockVarianteBodega($idVariante, $idDestino, $nuevoStockD);
                        if ($res2 != "ok") {
                            throw new Exception("Error al actualizar el stock de la variante en destino.");
                        }

                        // Sincronizar el stock del producto base en la bodega de destino
                        $stmtSincD = $db->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id WHERE pv.id_producto = :id_producto AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
                        $stmtSincD->execute([":id_producto" => $idProducto, ":id_bodega" => $idDestino]);
                        $resSincD = $stmtSincD->fetch();
                        $stockBaseD = ($resSincD && $resSincD["total"]) ? intval($resSincD["total"]) : 0;
                        ModeloProductos::mdlActualizarStockBodega($idProducto, $idDestino, $stockBaseD);

                        ControladorMovimientos::ctrRegistrarMovimiento("variante", $idProducto, $idVariante, $value["descripcion"], "traslado_entrada", $cantidad, $stockActualD, $nuevoStockD, "Traslado #" . $codigoTraslado, "", $idDestino);

                    } else {
                        
                        // 1. Origen: Descontar
                        $traerProductoO = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id", $idOrigen);
                        if (!$traerProductoO) {
                            throw new Exception("No se encontró el producto en la bodega de origen.");
                        }
                        $nuevoStockO = $traerProductoO["stock"] - $cantidad;
                        
                        $res1 = ModeloProductos::mdlActualizarStockBodega($idProducto, $idOrigen, $nuevoStockO);
                        if ($res1 != "ok") {
                            throw new Exception("Error al actualizar el stock del producto en origen.");
                        }

                        ControladorMovimientos::ctrRegistrarMovimiento("producto", $idProducto, null, $value["descripcion"], "traslado_salida", -$cantidad, $traerProductoO["stock"], $nuevoStockO, "Traslado #" . $codigoTraslado, "", $idOrigen);

                        // 2. Destino: Aumentar
                        $traerProductoD = ModeloProductos::mdlMostrarProductos("productos", "id", $idProducto, "id", $idDestino);
                        $stockActualD = (is_array($traerProductoD) && isset($traerProductoD["stock"])) ? $traerProductoD["stock"] : 0;
                        $nuevoStockD = $stockActualD + $cantidad;
                        
                        $res2 = ModeloProductos::mdlActualizarStockBodega($idProducto, $idDestino, $nuevoStockD);
                        if ($res2 != "ok") {
                            throw new Exception("Error al actualizar el stock del producto en destino.");
                        }

                        ControladorMovimientos::ctrRegistrarMovimiento("producto", $idProducto, null, $value["descripcion"], "traslado_entrada", $cantidad, $stockActualD, $nuevoStockD, "Traslado #" . $codigoTraslado, "", $idDestino);
                    }
                }

                $db->commit();

                echo '<script>
                    swal({
                        type: "success",
                        title: "El traslado ha sido registrado y completado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        window.location = "' . $paginaDestino . '";
                    })
                </script>';

            } catch (Exception $e) {
                $db->rollBack();
                Logger::error("Error al crear y completar traslado: " . $e->getMessage());

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al registrar el traslado",
                        text: "' . addslashes($e->getMessage()) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        window.location = "' . $paginaDestino . '";
                    })
                </script>';
            }
        }
    }

    /*=============================================
    EJECUTAR TRASLADO (COMPLETAR)
    =============================================*/
    static public function ctrCompletarTraslado($idTraslado)
    {
        $db = Conexion::conectar();
        
        try {
            $db->beginTransaction();

            $traslado = ModeloTraslados::mdlMostrarTraslados("traslados", "id", $idTraslado);
            
            if (!$traslado || $traslado["estado"] != "pendiente") {
                throw new Exception("El traslado ya no está pendiente o no existe.");
            }

            $items = ModeloTraslados::mdlMostrarItemsTraslado("traslados_items", $idTraslado);
            $idOrigen = $traslado["id_bodega_origen"];
            $idDestino = $traslado["id_bodega_destino"];

            foreach ($items as $item) {
                
                if ($item["tipo_producto"] == "variante") {
                    
                    // 1. Origen: Descontar
                    $traerVarianteO = ModeloProductos::mdlObtenerVariantePorId($item["id_variante"], $idOrigen);
                    if (!$traerVarianteO) {
                        throw new Exception("No se encontró la variante ID " . $item["id_variante"] . " en la bodega de origen.");
                    }
                    $nuevoStockO = $traerVarianteO["stock"] - $item["cantidad"];
                    
                    $res1 = ModeloProductos::mdlActualizarStockVarianteBodega($item["id_variante"], $idOrigen, $nuevoStockO);
                    if ($res1 != "ok") {
                        throw new Exception("Error al actualizar el stock de la variante ID " . $item["id_variante"] . " en origen.");
                    }

                    // Sincronizar el stock del producto base en la bodega de origen
                    $stmtSincO = $db->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id WHERE pv.id_producto = :id_producto AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
                    $stmtSincO->execute([":id_producto" => $item["id_producto"], ":id_bodega" => $idOrigen]);
                    $resSincO = $stmtSincO->fetch();
                    $stockBaseO = ($resSincO && $resSincO["total"]) ? intval($resSincO["total"]) : 0;
                    ModeloProductos::mdlActualizarStockBodega($item["id_producto"], $idOrigen, $stockBaseO);
                    
                    $nombreCompletoVariante = $item["nombre_producto"] . (!empty($item["nombre_variante"]) ? " - " . $item["nombre_variante"] : "");
                    ControladorMovimientos::ctrRegistrarMovimiento("variante", $item["id_producto"], $item["id_variante"], $nombreCompletoVariante, "traslado_salida", -$item["cantidad"], $traerVarianteO["stock"], $nuevoStockO, "Traslado #" . $traslado["codigo"], "", $idOrigen);

                    // 2. Destino: Aumentar
                    $traerVarianteD = ModeloProductos::mdlObtenerVariantePorId($item["id_variante"], $idDestino);
                    $stockActualD = (is_array($traerVarianteD) && isset($traerVarianteD["stock"])) ? $traerVarianteD["stock"] : 0;
                    $nuevoStockD = $stockActualD + $item["cantidad"];
                    
                    $res2 = ModeloProductos::mdlActualizarStockVarianteBodega($item["id_variante"], $idDestino, $nuevoStockD);
                    if ($res2 != "ok") {
                        throw new Exception("Error al actualizar el stock de la variante ID " . $item["id_variante"] . " en destino.");
                    }

                    // Sincronizar el stock del producto base en la bodega de destino
                    $stmtSincD = $db->prepare("SELECT SUM(pvb.stock) as total FROM productos_variantes_bodegas pvb INNER JOIN productos_variantes pv ON pvb.id_variante = pv.id WHERE pv.id_producto = :id_producto AND pvb.id_bodega = :id_bodega AND pv.estado = 1");
                    $stmtSincD->execute([":id_producto" => $item["id_producto"], ":id_bodega" => $idDestino]);
                    $resSincD = $stmtSincD->fetch();
                    $stockBaseD = ($resSincD && $resSincD["total"]) ? intval($resSincD["total"]) : 0;
                    ModeloProductos::mdlActualizarStockBodega($item["id_producto"], $idDestino, $stockBaseD);

                    ControladorMovimientos::ctrRegistrarMovimiento("variante", $item["id_producto"], $item["id_variante"], $nombreCompletoVariante, "traslado_entrada", $item["cantidad"], $stockActualD, $nuevoStockD, "Traslado #" . $traslado["codigo"], "", $idDestino);

                } else {
                    
                    // 1. Origen: Descontar
                    $traerProductoO = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id_producto"], "id", $idOrigen);
                    if (!$traerProductoO) {
                        throw new Exception("No se encontró el producto ID " . $item["id_producto"] . " en la bodega de origen.");
                    }
                    $nuevoStockO = $traerProductoO["stock"] - $item["cantidad"];
                    
                    $res1 = ModeloProductos::mdlActualizarStockBodega($item["id_producto"], $idOrigen, $nuevoStockO);
                    if ($res1 != "ok") {
                        throw new Exception("Error al actualizar el stock del producto ID " . $item["id_producto"] . " en origen.");
                    }

                    ControladorMovimientos::ctrRegistrarMovimiento("producto", $item["id_producto"], null, $item["nombre_producto"], "traslado_salida", -$item["cantidad"], $traerProductoO["stock"], $nuevoStockO, "Traslado #" . $traslado["codigo"], "", $idOrigen);

                    // 2. Destino: Aumentar
                    $traerProductoD = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id_producto"], "id", $idDestino);
                    $stockActualD = (is_array($traerProductoD) && isset($traerProductoD["stock"])) ? $traerProductoD["stock"] : 0;
                    $nuevoStockD = $stockActualD + $item["cantidad"];
                    
                    $res2 = ModeloProductos::mdlActualizarStockBodega($item["id_producto"], $idDestino, $nuevoStockD);
                    if ($res2 != "ok") {
                        throw new Exception("Error al actualizar el stock del producto ID " . $item["id_producto"] . " en destino.");
                    }

                    ControladorMovimientos::ctrRegistrarMovimiento("producto", $item["id_producto"], null, $item["nombre_producto"], "traslado_entrada", $item["cantidad"], $stockActualD, $nuevoStockD, "Traslado #" . $traslado["codigo"], "", $idDestino);
                }
            }

            $resActualizar = ModeloTraslados::mdlActualizarTraslado("traslados", "estado", "completado", "id", $idTraslado);
            if ($resActualizar != "ok") {
                throw new Exception("Error al actualizar el estado del traslado.");
            }

            $db->commit();
            return "ok";

        } catch (Exception $e) {
            $db->rollBack();
            Logger::error("Error al completar traslado ID " . $idTraslado . ": " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /*=============================================
    CANCELAR TRASLADO
    =============================================*/
    static public function ctrCancelarTraslado($idTraslado)
    {
        return ModeloTraslados::mdlActualizarTraslado("traslados", "estado", "cancelado", "id", $idTraslado);
    }
}
