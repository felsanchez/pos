<?php

class ControladorTraslados
{

    /*=============================================
    MOSTRAR TRASLADOS
    =============================================*/
    static public function ctrMostrarTraslados($item, $valor)
    {
        $tabla = "traslados";

        // Limpieza automática de registros más antiguos de 3 meses
        ModeloTraslados::mdlLimpiarTrasladosAntiguos($tabla);

        return ModeloTraslados::mdlMostrarTraslados($tabla, $item, $valor);
    }

    /*=============================================
    CREAR TRASLADO
    =============================================*/
    static public function ctrCrearTraslado()
    {
        if (isset($_POST["nuevaBodegaDestino"])) {

            if ($_POST["nuevaBodegaOrigen"] == $_POST["nuevaBodegaDestino"]) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "La bodega de origen y destino no pueden ser la misma",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        window.location = "traslados";
                    })
                </script>';
                return;
            }

            $tabla = "traslados";
            $listaProductos = json_decode($_POST["listaProductos"], true);
            $totalItems = 0;
            foreach ($listaProductos as $producto) {
                $totalItems += $producto["cantidad"];
            }

            $datos = array(
                "codigo" => $_POST["nuevoCodigoTraslado"],
                "id_bodega_origen" => $_POST["nuevaBodegaOrigen"],
                "id_bodega_destino" => $_POST["nuevaBodegaDestino"],
                "id_usuario" => $_SESSION["id"],
                "total_items" => $totalItems,
                "notas" => $_POST["nuevasNotas"],
                "estado" => "pendiente"
            );

            $idTraslado = ModeloTraslados::mdlIngresarTraslado($tabla, $datos);

            if (is_numeric($idTraslado)) {

                $tablaItems = "traslados_items";
                foreach ($listaProductos as $value) {
                    
                    $datosItem = array(
                        "id_traslado" => $idTraslado,
                        "tipo_producto" => (isset($value["esVariante"]) && $value["esVariante"] == "1") ? "variante" : "producto",
                        "id_producto" => $value["id"],
                        "id_variante" => (isset($value["idVariante"]) ? $value["idVariante"] : null),
                        "cantidad" => $value["cantidad"]
                    );

                    ModeloTraslados::mdlIngresarItemTraslado($tablaItems, $datosItem);
                }

                echo '<script>
                    swal({
                        type: "success",
                        title: "El traslado ha sido registrado correctamente como PENDIENTE",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(() => {
                        window.location = "traslados";
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
        $traslado = ModeloTraslados::mdlMostrarTraslados("traslados", "id", $idTraslado);
        
        if ($traslado["estado"] != "pendiente") {
            return "El traslado ya no está pendiente";
        }

        $items = ModeloTraslados::mdlMostrarItemsTraslado("traslados_items", $idTraslado);
        $idOrigen = $traslado["id_bodega_origen"];
        $idDestino = $traslado["id_bodega_destino"];

        foreach ($items as $item) {
            
            if ($item["tipo_producto"] == "variante") {
                
                // 1. Origen: Descontar
                $traerVarianteO = ModeloProductos::mdlObtenerVariantePorId($item["id_variante"], $idOrigen);
                $nuevoStockO = $traerVarianteO["stock"] - $item["cantidad"];
                ModeloProductos::mdlActualizarStockVarianteBodega($item["id_variante"], $idOrigen, $nuevoStockO);
                
                ControladorMovimientos::ctrRegistrarMovimiento("variante", $item["id_producto"], $item["id_variante"], $item["nombre_variante"], "traslado_salida", -$item["cantidad"], $traerVarianteO["stock"], $nuevoStockO, "Traslado #" . $traslado["codigo"], "", $idOrigen);

                // 2. Destino: Aumentar
                $traerVarianteD = ModeloProductos::mdlObtenerVariantePorId($item["id_variante"], $idDestino);
                $nuevoStockD = $traerVarianteD["stock"] + $item["cantidad"];
                ModeloProductos::mdlActualizarStockVarianteBodega($item["id_variante"], $idDestino, $nuevoStockD);

                ControladorMovimientos::ctrRegistrarMovimiento("variante", $item["id_producto"], $item["id_variante"], $item["nombre_variante"], "traslado_entrada", $item["cantidad"], $traerVarianteD["stock"], $nuevoStockD, "Traslado #" . $traslado["codigo"], "", $idDestino);

            } else {
                
                // 1. Origen: Descontar
                $traerProductoO = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id_producto"], "id", $idOrigen);
                $nuevoStockO = $traerProductoO["stock"] - $item["cantidad"];
                ModeloProductos::mdlActualizarStockBodega($item["id_producto"], $idOrigen, $nuevoStockO);

                ControladorMovimientos::ctrRegistrarMovimiento("producto", $item["id_producto"], null, $traerProductoO["descripcion"], "traslado_salida", -$item["cantidad"], $traerProductoO["stock"], $nuevoStockO, "Traslado #" . $traslado["codigo"], "", $idOrigen);

                // 2. Destino: Aumentar
                $traerProductoD = ModeloProductos::mdlMostrarProductos("productos", "id", $item["id_producto"], "id", $idDestino);
                $nuevoStockD = $traerProductoD["stock"] + $item["cantidad"];
                ModeloProductos::mdlActualizarStockBodega($item["id_producto"], $idDestino, $nuevoStockD);

                ControladorMovimientos::ctrRegistrarMovimiento("producto", $item["id_producto"], null, $traerProductoD["descripcion"], "traslado_entrada", $item["cantidad"], $traerProductoD["stock"], $nuevoStockD, "Traslado #" . $traslado["codigo"], "", $idDestino);
            }
        }

        return ModeloTraslados::mdlActualizarTraslado("traslados", "estado", "completado", "id", $idTraslado);
    }

    /*=============================================
    CANCELAR TRASLADO
    =============================================*/
    static public function ctrCancelarTraslado($idTraslado)
    {
        return ModeloTraslados::mdlActualizarTraslado("traslados", "estado", "cancelado", "id", $idTraslado);
    }
}
