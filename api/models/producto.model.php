<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../config.php';

class ProductoModel
{
    public static function buscar($consulta)
    {
        $db = Database::conectar();
    
        // Limpiar consulta
        $consulta = mb_strtolower(trim($consulta), 'UTF-8');
    
        $ignorar = [
            'de','del','la','las','el','los',
            'para','por','con','sin',
            'un','una','unos','unas',
            'quiero','necesito','busco',
            'tienen','tiene','hay','que'
        ];
    
        $palabras = preg_split('/\s+/', $consulta);
    
        $palabras = array_values(array_filter($palabras, function ($p) use ($ignorar) {
            return strlen($p) > 1 && !in_array($p, $ignorar);
        }));
    
        // Si quedó vacío, usar la consulta original
        if (empty($palabras)) {
            $palabras[] = $consulta;
        }
    
        $where = [];
        $params = [];
        $types = "";
    
        foreach ($palabras as $palabra) {
    
            $where[] = "(
                p.codigo LIKE ?
                OR p.descripcion LIKE ?
                OR c.categoria LIKE ?
            )";
    
            $buscar = "%{$palabra}%";
    
            $params[] = $buscar;
            $params[] = $buscar;
            $params[] = $buscar;
    
            $types .= "sss";
        }
    
        $sql = "
            SELECT
                p.id,
                p.codigo,
                p.descripcion,
                p.precio_venta,
                p.tiene_variantes,
                p.imagen,
                c.categoria,
                COALESCE(SUM(pb.stock),0) AS stock_total
    
            FROM productos p
    
            LEFT JOIN categorias c
                ON c.id = p.id_categoria
    
            LEFT JOIN productos_bodegas pb
                ON pb.id_producto = p.id
                AND pb.estado = 1
    
            WHERE
                p.eliminado = 0
                AND p.estado = 1
                AND (
                    " . implode(" OR ", $where) . "
                )
    
            GROUP BY
                p.id,
                p.codigo,
                p.descripcion,
                p.precio_venta,
                p.tiene_variantes,
                p.imagen,
                c.categoria
    
            ORDER BY
                p.descripcion
    
            LIMIT ?
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            errorResponse("Error preparando consulta.", 500);
        }
    
        $limite = MAX_RESULTADOS;
    
        $params[] = $limite;
        $types .= "i";
    
        $stmt->bind_param($types, ...$params);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        $productos = [];
    
        while ($row = $resultado->fetch_assoc()) {
    
            $tieneVariantes = ((int)$row["tiene_variantes"] === 1);
            
            
            $imagen = null;

            if (
                !empty($row["imagen"]) &&
                strpos($row["imagen"], "default/anonymous.png") === false &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . ltrim($row["imagen"], "/"))
            ) {
            
                $imagen = APP_URL . ltrim($row["imagen"], "/");
            
            }
    
            $producto = [
    
                "id" => (int)$row["id"],
    
                "codigo" => $row["codigo"],
    
                "nombre" => $row["descripcion"],
    
                "categoria" => $row["categoria"],
                
                "imagen" => $imagen,

                "tiene_imagen" => $imagen !== null,
    
                "precio_base" => (float)$row["precio_venta"],
    
                "tiene_variantes" => $tieneVariantes
    
            ];
    
            if ($tieneVariantes) {
    
                $variantes = self::obtenerVariantes(
                    $row["id"],
                    (float)$row["precio_venta"]
                );
    
                // Si todas las variantes están agotadas,
                // no mostrar el producto.
                if (empty($variantes)) {
                    continue;
                }
    
                $producto["variantes"] = $variantes;

                $producto = array_merge(
                    $producto,
                    self::obtenerResumenPrecios($variantes)
                );
                
                $producto["inventario"] =
                    self::obtenerResumenInventario($variantes);
                
                $producto["opciones_disponibles"] =
                    self::obtenerOpcionesDisponibles($variantes);
    
            } else {
    
                // Producto simple sin stock
                if ((int)$row["stock_total"] <= 0) {
                    continue;
                }
    
                $producto["precio"] = (float)$row["precio_venta"];
    
                $producto["stock"] = (int)$row["stock_total"];
    
                $producto["disponibilidad"] = "Disponible";
    
            }
    
            $productos[] = $producto;
        }
    
        $stmt->close();
    
        return $productos;
    }
    
    
    private static function obtenerVariantes($idProducto, $precioBase)
    {
        $db = Database::conectar();
    
        // ============================
        // Obtener variantes disponibles
        // ============================
    
        $sql = "
            SELECT
                sku,
                precio_adicional,
                stock
            FROM productos_variantes
            WHERE
                id_producto = ?
                AND estado = 1
                AND stock > 0
            ORDER BY sku
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            errorResponse("Error preparando consulta de variantes.", 500);
        }
    
        $stmt->bind_param("i", $idProducto);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        $variantesDB = [];
    
        while ($row = $resultado->fetch_assoc()) {
            $variantesDB[] = $row;
        }
    
        $stmt->close();
    
        if (empty($variantesDB)) {
            return [];
        }
    
        // ============================
        // Obtener todas las opciones
        // ============================
    
        $sql = "
            SELECT
                ov.id,
                ov.nombre AS opcion,
                tv.nombre AS tipo
            FROM opciones_variantes ov
            INNER JOIN tipos_variantes tv
                ON tv.id = ov.id_tipo_variante
            WHERE
                ov.estado = 1
                AND tv.estado = 1
        ";
    
        $stmt = $db->prepare($sql);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        $opcionesMapa = [];
    
        while ($row = $resultado->fetch_assoc()) {
    
            $opcionesMapa[(int)$row["id"]] = [
    
                "tipo" => mb_convert_case(
                    $row["tipo"],
                    MB_CASE_TITLE,
                    "UTF-8"
                ),
    
                "opcion" => mb_convert_case(
                    $row["opcion"],
                    MB_CASE_TITLE,
                    "UTF-8"
                )
    
            ];
    
        }
    
        $stmt->close();
    
        // ============================
        // Construir respuesta
        // ============================
    
        $variantes = [];
    
        foreach ($variantesDB as $variante) {
    
            $opciones = [];
    
            $partes = explode("_", $variante["sku"]);
    
            array_shift($partes);
    
            foreach ($partes as $idOpcion) {
    
                $idOpcion = (int)$idOpcion;
    
                if (!isset($opcionesMapa[$idOpcion])) {
                    continue;
                }
    
                $opciones[
                    $opcionesMapa[$idOpcion]["tipo"]
                ] = $opcionesMapa[$idOpcion]["opcion"];
    
            }
    
            $variantes[] = [
    
                "sku" => $variante["sku"],
    
                "precio" => $precioBase +
                    (float)$variante["precio_adicional"],
    
                "stock" => (int)$variante["stock"],
    
                "disponibilidad" => "Disponible",
    
                "opciones" => $opciones
    
            ];
    
        }
    
        return $variantes;
    }


    private static function obtenerOpcionesDisponibles($variantes)
    {
        $resultado = [];
    
        foreach ($variantes as $variante) {
    
            foreach ($variante["opciones"] as $tipo => $opcion) {
    
                if (!isset($resultado[$tipo])) {
                    $resultado[$tipo] = [];
                }
    
                if (!in_array($opcion, $resultado[$tipo])) {
                    $resultado[$tipo][] = $opcion;
                }
    
            }
    
        }
    
        ksort($resultado);
    
        return $resultado;
    }
    
    
    private static function obtenerResumenInventario($variantes)
    {
        return [
    
            "variantes_totales" => count($variantes),
    
            "variantes_disponibles" => count($variantes),
    
            "variantes_agotadas" => 0
    
        ];
    }
    
    
    private static function obtenerResumenPrecios($variantes)
    {
        if (empty($variantes)) {
    
            return [
                "precio_min" => 0,
                "precio_max" => 0,
                "precio_mostrar" => ""
            ];
    
        }
    
        $precios = array_column($variantes, "precio");
    
        $precioMin = min($precios);
    
        $precioMax = max($precios);
    
        if ($precioMin == $precioMax) {
    
            $precioMostrar = "$" . number_format($precioMin, 0, ",", ".");
    
        } else {
    
            $precioMostrar = "Desde $" . number_format($precioMin, 0, ",", ".");
    
        }
    
        return [
    
            "precio_min" => $precioMin,
    
            "precio_max" => $precioMax,
    
            "precio_mostrar" => $precioMostrar
    
        ];
    }
    
    
    public static function masVendidos($limite = 20)
    {
        $db = Database::conectar();
    
        $sql = "
            SELECT
                p.id,
                p.codigo,
                p.descripcion,
                p.precio_venta,
                p.tiene_variantes,
                p.imagen,
                p.ventas,
                c.categoria,
                COALESCE(SUM(pb.stock),0) AS stock_total
    
            FROM productos p
    
            LEFT JOIN categorias c
                ON c.id = p.id_categoria
    
            LEFT JOIN productos_bodegas pb
                ON pb.id_producto = p.id
                AND pb.estado = 1
    
            WHERE
                p.eliminado = 0
                AND p.estado = 1
    
            GROUP BY
                p.id,
                p.codigo,
                p.descripcion,
                p.precio_venta,
                p.tiene_variantes,
                p.imagen,
                p.ventas,
                c.categoria
    
            ORDER BY
                p.ventas DESC,
                p.descripcion ASC
    
            LIMIT ?
        ";
    
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            errorResponse("Error preparando consulta.", 500);
        }
    
        $stmt->bind_param("i", $limite);
    
        $stmt->execute();
    
        $resultado = $stmt->get_result();
    
        $productos = [];
    
        while ($row = $resultado->fetch_assoc()) {
    
            $tieneVariantes = ((int)$row["tiene_variantes"] === 1);
    
            $imagen = null;
    
            if (
                !empty($row["imagen"]) &&
                strpos($row["imagen"], "default/anonymous.png") === false &&
                file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . ltrim($row["imagen"], "/"))
            ) {
    
                $imagen = APP_URL . ltrim($row["imagen"], "/");
    
            }
    
            $producto = [
    
                "id" => (int)$row["id"],
    
                "codigo" => $row["codigo"],
    
                "nombre" => $row["descripcion"],
    
                "categoria" => $row["categoria"],
    
                "imagen" => $imagen,
    
                "tiene_imagen" => $imagen !== null,
    
                "precio_base" => (float)$row["precio_venta"],
    
                "tiene_variantes" => $tieneVariantes
    
            ];
    
            if ($tieneVariantes) {
    
                $variantes = self::obtenerVariantes(
                    $row["id"],
                    (float)$row["precio_venta"]
                );
    
                if (empty($variantes)) {
                    continue;
                }
    
                $producto["variantes"] = $variantes;
    
                $producto = array_merge(
                    $producto,
                    self::obtenerResumenPrecios($variantes)
                );
    
                $producto["inventario"] =
                    self::obtenerResumenInventario($variantes);
    
                $producto["opciones_disponibles"] =
                    self::obtenerOpcionesDisponibles($variantes);
    
            } else {
    
                if ((int)$row["stock_total"] <= 0) {
                    continue;
                }
    
                $producto["precio"] = (float)$row["precio_venta"];
    
                $producto["stock"] = (int)$row["stock_total"];
    
                $producto["disponibilidad"] = "Disponible";
    
            }
    
            $productos[] = $producto;
        }
    
        $stmt->close();
        
        $productos = array_slice($productos, 0, $limite);
    
        return $productos;
    }


}