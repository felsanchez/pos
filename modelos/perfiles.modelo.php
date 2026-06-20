<?php

class ModeloPerfiles
{
    private static $modulos = [
        'inicio'               => 'Inicio / Dashboard',
        'usuarios'             => 'Usuarios',
        'productos'            => 'Productos',
        'proveedores'          => 'Proveedores',
        'clientes'             => 'Clientes',
        'actividades'          => 'Actividades',
        'ordenes'              => 'Órdenes',
        'ordenes-visita'       => 'Consulta de ventas',
        'ventas'               => 'Ventas',
        'factura_electronica'  => 'Factura Electrónica',
        'documento_soporte'    => 'Documento Soporte',
        'notas_credito'        => 'Notas Crédito',
        'notas_ajuste'         => 'Notas de Ajuste DS',
        'reporte_ventas'       => 'Reporte de Ventas',
        'seguimiento_leads'    => 'Seguimiento a Leads',
        'crm'                  => 'CRM',
        'historial_stock'      => 'Historial de Stock',
        'traslados'            => 'Traslados entre Bodegas',
        'gastos'               => 'Gastos',
        'cierres-caja'         => 'Cierres de Caja',
        'notificaciones'       => 'Notificaciones',
        'configuracion'        => 'Configuración',
    ];

    /** Retorna el listado de slugs → nombres de módulos */
    public static function mdlObtenerModulos(): array
    {
        return self::$modulos;
    }

    /** Lista todos los perfiles con conteo de usuarios asignados */
    public static function mdlObtenerPerfiles(): array
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("
            SELECT p.id, p.nombre, p.descripcion, p.es_sistema,
                   COUNT(u.id) as total_usuarios
            FROM perfiles p
            LEFT JOIN usuarios u ON u.perfil = p.nombre
            WHERE p.nombre != '_SystemMaster_'
            GROUP BY p.id, p.nombre, p.descripcion, p.es_sistema
            ORDER BY p.es_sistema DESC, p.nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Retorna los permisos de un perfil como array [modulo => [ver, crear, ...]] */
    public static function mdlObtenerPermisosDelPerfil(int $idPerfil): array
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("
            SELECT modulo, puede_ver, puede_crear, puede_editar,
                   puede_eliminar, puede_imprimir, puede_exportar
            FROM perfiles_permisos
            WHERE id_perfil = :id
        ");
        $stmt->bindValue(':id', $idPerfil, PDO::PARAM_INT);
        $stmt->execute();

        $permisos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permisos[$row['modulo']] = [
                'ver'      => (bool)$row['puede_ver'],
                'crear'    => (bool)$row['puede_crear'],
                'editar'   => (bool)$row['puede_editar'],
                'eliminar' => (bool)$row['puede_eliminar'],
                'imprimir' => (bool)$row['puede_imprimir'],
                'exportar' => (bool)$row['puede_exportar'],
            ];
        }
        return $permisos;
    }

    /**
     * Carga los permisos del perfil por nombre en $_SESSION["permisos"].
     * Llamar desde el controlador de login.
     * El Administrador no necesita consulta (puedeVer() ya lo maneja).
     */
    public static function mdlCargarPermisosEnSesion(string $nombrePerfil): array
    {
        if ($nombrePerfil === 'Administrador') {
            return []; // puedeVer() ya retorna true sin consultar
        }

        $conn = Conexion::conectar();
        $stmt = $conn->prepare("
            SELECT pp.modulo, pp.puede_ver, pp.puede_crear, pp.puede_editar,
                   pp.puede_eliminar, pp.puede_imprimir, pp.puede_exportar
            FROM perfiles_permisos pp
            INNER JOIN perfiles p ON p.id = pp.id_perfil
            WHERE p.nombre = :nombre
        ");
        $stmt->bindValue(':nombre', $nombrePerfil);
        $stmt->execute();

        $permisos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permisos[$row['modulo']] = [
                'ver'      => (bool)$row['puede_ver'],
                'crear'    => (bool)$row['puede_crear'],
                'editar'   => (bool)$row['puede_editar'],
                'eliminar' => (bool)$row['puede_eliminar'],
                'imprimir' => (bool)$row['puede_imprimir'],
                'exportar' => (bool)$row['puede_exportar'],
            ];
        }
        return $permisos;
    }

    /** Crea un nuevo perfil con sus permisos */
    public static function mdlCrearPerfil(string $nombre, string $descripcion, array $permisos): string
    {
        if (strtolower($nombre) === '_systemmaster_') {
            return 'error: Nombre de perfil reservado por el sistema.';
        }
        $conn = Conexion::conectar();
        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("INSERT INTO perfiles (nombre, descripcion, es_sistema) VALUES (:nombre, :desc, 0)");
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':desc', $descripcion);
            $stmt->execute();
            $idPerfil = $conn->lastInsertId();

            self::mdlGuardarPermisos($conn, $idPerfil, $permisos);

            $conn->commit();
            return 'ok';
        } catch (Exception $e) {
            $conn->rollBack();
            return 'error: ' . $e->getMessage();
        }
    }

    /** Actualiza nombre, descripción y permisos de un perfil */
    public static function mdlActualizarPerfil(int $id, string $nombre, string $descripcion, array $permisos): string
    {
        if (strtolower($nombre) === '_systemmaster_') {
            return 'error: Nombre de perfil reservado por el sistema.';
        }
        $conn = Conexion::conectar();
        try {
            $conn->beginTransaction();

            // Verificar que no es perfil de sistema (Administrador no se edita)
            $check = $conn->prepare("SELECT es_sistema FROM perfiles WHERE id = :id");
            $check->bindValue(':id', $id, PDO::PARAM_INT);
            $check->execute();
            $perfil = $check->fetch(PDO::FETCH_ASSOC);
            if ($perfil && $perfil['es_sistema']) {
                $conn->rollBack();
                return 'error_sistema';
            }

            $stmt = $conn->prepare("UPDATE perfiles SET nombre = :nombre, descripcion = :desc WHERE id = :id");
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':desc', $descripcion);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Borrar y re-insertar permisos
            $del = $conn->prepare("DELETE FROM perfiles_permisos WHERE id_perfil = :id");
            $del->bindValue(':id', $id, PDO::PARAM_INT);
            $del->execute();

            self::mdlGuardarPermisos($conn, $id, $permisos);

            $conn->commit();
            return 'ok';
        } catch (Exception $e) {
            $conn->rollBack();
            return 'error: ' . $e->getMessage();
        }
    }

    /** Elimina un perfil (solo si no tiene usuarios asignados y no es de sistema) */
    public static function mdlEliminarPerfil(int $id): string
    {
        $conn = Conexion::conectar();

        $check = $conn->prepare("SELECT es_sistema, nombre FROM perfiles WHERE id = :id");
        $check->bindValue(':id', $id, PDO::PARAM_INT);
        $check->execute();
        $perfil = $check->fetch(PDO::FETCH_ASSOC);

        if (!$perfil) return 'error_no_existe';
        if ($perfil['es_sistema']) return 'error_sistema';

        $checkUsr = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil = :nombre");
        $checkUsr->bindValue(':nombre', $perfil['nombre']);
        $checkUsr->execute();
        if ($checkUsr->fetchColumn() > 0) return 'error_usuarios';

        $stmt = $conn->prepare("DELETE FROM perfiles WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return 'ok';
    }

    /** Inserta los permisos de un perfil (uso interno) */
    private static function mdlGuardarPermisos($conn, int $idPerfil, array $permisos): void
    {
        $stmt = $conn->prepare("
            INSERT INTO perfiles_permisos
                (id_perfil, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar, puede_imprimir, puede_exportar)
            VALUES
                (:id_perfil, :modulo, :ver, :crear, :editar, :eliminar, :imprimir, :exportar)
        ");
        foreach ($permisos as $modulo => $acciones) {
            $stmt->bindValue(':id_perfil', $idPerfil, PDO::PARAM_INT);
            $stmt->bindValue(':modulo', $modulo);
            $stmt->bindValue(':ver',      (int)($acciones['ver']      ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':crear',    (int)($acciones['crear']    ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':editar',   (int)($acciones['editar']   ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':eliminar', (int)($acciones['eliminar'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':imprimir', (int)($acciones['imprimir'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':exportar', (int)($acciones['exportar'] ?? 0), PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}
