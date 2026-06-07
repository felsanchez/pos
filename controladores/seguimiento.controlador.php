<?php

class ControladorSeguimiento
{

    /*=============================================
    MOSTRAR SEGUIMIENTOS
    =============================================*/

    static public function ctrMostrarSeguimientos($item, $valor)
    {
        $tabla = "seguimiento_leads";
        $respuesta = ModeloSeguimiento::mdlMostrarSeguimientos($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    MOSTRAR SEGUIMIENTOS SERVER-SIDE
    =============================================*/
    static public function ctrMostrarSeguimientosServerSide($params)
    {
        $tabla = "seguimiento_leads";

        // Mapeo de columnas para ordenamiento
        // 0=Check, 1=Fecha, 2=Nombre, 3=Celular, 4=Contexto, 5=Estado, 6=Seg1, 7=Seg2, 8=Seg3, 9=Pedido
        $columnsMap = [
            1 => 'fecha',
            2 => 'nombre',
            3 => 'celular',
            4 => 'contexto',
            5 => 'estado',
        ];

        // Filtro por búsqueda global
        $where = "";
        if (!empty($params['search']['value'])) {
            $s = addslashes($params['search']['value']);
            $where = "WHERE (nombre LIKE '%$s%' OR celular LIKE '%$s%' OR contexto LIKE '%$s%' OR estado LIKE '%$s%' OR fecha LIKE '%$s%')";
        }

        // Orden: por defecto id DESC para colocar los registros más nuevos arriba
        $order = "ORDER BY id DESC";
        if (isset($params['order'][0]['column'])) {
            $colIdx = (int)$params['order'][0]['column'];
            if (isset($columnsMap[$colIdx])) {
                $dir = $params['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
                if ($columnsMap[$colIdx] === 'fecha') {
                    $order = "ORDER BY id $dir";
                } else {
                    $order = "ORDER BY " . $columnsMap[$colIdx] . " $dir, id DESC";
                }
            }
        }

        // Paginación
        $limit = "";
        if (isset($params['length']) && $params['length'] != -1) {
            $limit = "LIMIT " . intval($params['start']) . ", " . intval($params['length']);
        }

        $seguimientos  = ModeloSeguimiento::mdlMostrarSeguimientosServerSide($tabla, $where, $order, $limit);
        $totalData     = ModeloSeguimiento::mdlGetTotalSeguimientos($tabla, "");
        $totalFiltered = ModeloSeguimiento::mdlGetTotalSeguimientos($tabla, $where);

        $data = [];
        foreach ($seguimientos as $value) {
            // Badge de estado con color
            $estado = $value["estado"];
            $colorEstado = "#d2d6de";
            if (stripos($estado, "frio")     !== false) $colorEstado = "#3c8dbc";
            elseif (stripos($estado, "tibio")    !== false) $colorEstado = "#f39c12";
            elseif (stripos($estado, "caliente") !== false) $colorEstado = "#2ecc71";
            $estadoBadge = '<span class="badge" style="background-color:' . $colorEstado . '">' . e($estado) . '</span>';

            // Badges de seguimiento
            $seg1   = !empty($value["seguimiento1"]) ? '<span class="badge" style="background-color:#28a745">' . e($value["seguimiento1"])  . '</span>' : '';
            $seg2   = !empty($value["seguimiento2"]) ? '<span class="badge" style="background-color:#28a745">' . e($value["seguimiento2"])  . '</span>' : '';
            $seg3   = !empty($value["seguimiento3"]) ? '<span class="badge" style="background-color:#28a745">' . e($value["seguimiento3"])  . '</span>' : '';
            $pedido = !empty($value["hizo_pedido"])  ? '<span class="badge" style="background-color:#006400">' . e($value["hizo_pedido"]) . '</span>' : '';

            // Checkbox (deshabilitado si no tiene permisos para eliminar)
            $puedoEliminar = function_exists('puedeAccion') ? puedeAccion('seguimiento_leads', 'eliminar') : true;
            if ($puedoEliminar) {
                $checkbox = '<input type="checkbox" class="checkItem" value="' . $value["id"] . '">';
            } else {
                $checkbox = '<input type="checkbox" class="checkItem" disabled style="cursor: not-allowed;" title="No tiene permisos para eliminar" value="' . $value["id"] . '">';
            }

            $data[] = [
                $checkbox,
                e($value["fecha"]),
                e($value["nombre"]),
                e($value["celular"]),
                e($value["contexto"]),
                $estadoBadge,
                $seg1,
                $seg2,
                $seg3,
                $pedido,
            ];
        }

        return [
            "draw"            => intval($params['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
        ];
    }

}
