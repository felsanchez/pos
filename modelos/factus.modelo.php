<?php

require_once __DIR__ . "/conexion.php";

if (!class_exists('ModeloFactus')) {

class ModeloFactus
{

    /*=============================================
    OBTENER CONFIGURACIÓN DE FACTUS
    =============================================*/
    static public function mdlObtenerConfiguracion()
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_config WHERE id = 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    ACTUALIZAR CONFIGURACIÓN DE FACTUS
    =============================================*/
    static public function mdlActualizarConfiguracion($datos)
    {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE factus_config
			SET api_url = :api_url,
				client_id = :client_id,
				client_secret = :client_secret,
				username = :username,
				password = :password,
				ambiente = :ambiente,
				activo = :activo,
				rango_numeracion_id = :rango_numeracion_id,
				nombre_empresa = :nombre_empresa,
				nit_empresa = :nit_empresa,
				direccion_empresa = :direccion_empresa,
				telefono_empresa = :telefono_empresa,
				email_empresa = :email_empresa,
				municipio_id = :municipio_id,
				tributo_emisor = :tributo_emisor,
				actividad_economica = :actividad_economica,
				registro_mercantil = :registro_mercantil,
				dv = :dv,
				responsabilidades_fiscales = :responsabilidades_fiscales,
				tipo_persona = :tipo_persona,
				bloqueo_datos_emisor = :bloqueo_datos_emisor,
				logo_empresa = :logo_empresa
			WHERE id = 1"
        );

        $stmt->bindParam(":api_url", $datos["api_url"], PDO::PARAM_STR);
        $stmt->bindParam(":client_id", $datos["client_id"], PDO::PARAM_STR);
        $stmt->bindParam(":client_secret", $datos["client_secret"], PDO::PARAM_STR);
        $stmt->bindParam(":username", $datos["username"], PDO::PARAM_STR);
        $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
        $stmt->bindParam(":ambiente", $datos["ambiente"], PDO::PARAM_STR);
        $stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
        $stmt->bindParam(":rango_numeracion_id", $datos["rango_numeracion_id"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre_empresa", $datos["nombre_empresa"], PDO::PARAM_STR);
        $stmt->bindParam(":nit_empresa", $datos["nit_empresa"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion_empresa", $datos["direccion_empresa"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono_empresa", $datos["telefono_empresa"], PDO::PARAM_STR);
        $stmt->bindParam(":email_empresa", $datos["email_empresa"], PDO::PARAM_STR);
        $stmt->bindParam(":municipio_id", $datos["municipio_id"], PDO::PARAM_STR);
        $stmt->bindParam(":tributo_emisor", $datos["tributo_emisor"], PDO::PARAM_STR);
        $stmt->bindParam(":actividad_economica", $datos["actividad_economica"], PDO::PARAM_STR);
        $stmt->bindParam(":registro_mercantil", $datos["registro_mercantil"], PDO::PARAM_STR);
        $stmt->bindParam(":dv", $datos["dv"], PDO::PARAM_STR);
        $stmt->bindParam(":responsabilidades_fiscales", $datos["responsabilidades_fiscales"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_persona", $datos["tipo_persona"], PDO::PARAM_STR);
        $stmt->bindParam(":bloqueo_datos_emisor", $datos["bloqueo_datos_emisor"], PDO::PARAM_INT);
        $stmt->bindParam(":logo_empresa", $datos["logo_empresa"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ACTUALIZAR TOKENS DE AUTENTICACIÓN
    =============================================*/
    static public function mdlActualizarTokens($datos)
    {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE factus_config
			SET access_token = :access_token,
				refresh_token = :refresh_token,
				token_expiracion = :token_expiracion
			WHERE id = 1"
        );

        $stmt->bindParam(":access_token", $datos["access_token"], PDO::PARAM_STR);
        $stmt->bindParam(":refresh_token", $datos["refresh_token"], PDO::PARAM_STR);
        $stmt->bindParam(":token_expiracion", $datos["token_expiracion"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    VERIFICAR SI LA CONFIGURACIÓN ESTÁ ACTIVA
    =============================================*/
    static public function mdlEstaActivo()
    {
        $stmt = Conexion::conectar()->prepare("SELECT activo FROM factus_config WHERE id = 1");
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? (bool) $resultado['activo'] : false;
    }

    /*=============================================
    VERIFICAR SI EL TOKEN HA EXPIRADO
    =============================================*/
    static public function mdlTokenExpirado()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT token_expiracion FROM factus_config WHERE id = 1"
        );
        $stmt->execute();
        $resultado = $stmt->fetch();

        if (!$resultado || !$resultado['token_expiracion']) {
            return true; // Si no hay token, está expirado
        }

        $expiracion = new DateTime($resultado['token_expiracion']);
        $ahora = new DateTime();

        return $ahora >= $expiracion;
    }

    /*=============================================
    OBTENER ACCESS TOKEN
    =============================================*/
    static public function mdlObtenerAccessToken()
    {
        $stmt = Conexion::conectar()->prepare("SELECT access_token FROM factus_config WHERE id = 1");
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? $resultado['access_token'] : null;
    }

    /*=============================================
    OBTENER REFRESH TOKEN
    =============================================*/
    static public function mdlObtenerRefreshToken()
    {
        $stmt = Conexion::conectar()->prepare("SELECT refresh_token FROM factus_config WHERE id = 1");
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado ? $resultado['refresh_token'] : null;
    }

    /*=============================================
    GARANTIZAR TOKEN VALIDO (REFRESH SI ES NECESARIO)
    =============================================*/
    static public function mdlGarantizarTokenValido()
    {
        if (!self::mdlTokenExpirado()) {
            return true;
        }

        $config = self::mdlObtenerConfiguracion();
        $refreshToken = $config['refresh_token'];
        $url = $config['api_url'] . '/oauth/token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                // Calcular nueva fecha de expiración
                $expiresIn = $data['expires_in'] ?? 3600;
                $expirationDate = date('Y-m-d H:i:s', time() + $expiresIn);

                return self::mdlActualizarTokens([
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                    'token_expiracion' => $expirationDate
                ]) === "ok";
            }
        }

        // LOG ERROR
        file_put_contents("debug_token_refresh_error.txt", date('Y-m-d H:i:s') . " - HTTP: $httpCode - Response: $response\n", FILE_APPEND);

        return false;
    }

    /*=============================================
    GUARDAR MUNICIPIOS
    =============================================*/
    static public function mdlGuardarMunicipios($municipios)
    {
        $db = Conexion::conectar();
        $insertados = 0;
        $actualizados = 0;

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                "INSERT INTO factus_municipios (id_factus, codigo, nombre, departamento, codigo_departamento)
				VALUES (:id_factus, :codigo, :nombre, :departamento, :codigo_departamento)
				ON DUPLICATE KEY UPDATE
					id_factus = VALUES(id_factus),
					nombre = VALUES(nombre),
					departamento = VALUES(departamento),
					codigo_departamento = VALUES(codigo_departamento),
					fecha_sincronizacion = CURRENT_TIMESTAMP"
            );

            foreach ($municipios as $municipio) {
                $stmt->execute([
                    ':id_factus' => $municipio['id'],
                    ':codigo' => $municipio['code'] ?? $municipio['codigo'],
                    ':nombre' => $municipio['name'] ?? $municipio['nombre'],
                    ':departamento' => $municipio['department'] ?? $municipio['departamento'],
                    ':codigo_departamento' => $municipio['codigo_departamento'] ?? ''
                ]);

                if ($stmt->rowCount() > 0) {
                    $insertados++;
                } else {
                    $actualizados++;
                }
            }

            $db->commit();
            return ['insertados' => $insertados, 'actualizados' => $actualizados];

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /*=============================================
    CALCULAR DÍGITO DE VERIFICACIÓN (DIAN)
    =============================================*/
    static public function mdlCalcularDV($nit)
    {
        if (!is_numeric($nit)) {
            return 0;
        }

        $arr = array(
            1 => 3,
            4 => 17,
            7 => 29,
            10 => 43,
            13 => 59,
            2 => 7,
            5 => 19,
            8 => 37,
            11 => 47,
            14 => 67,
            3 => 13,
            6 => 23,
            9 => 41,
            12 => 53,
            15 => 71
        );
        $x = 0;
        $y = 0;
        $z = strlen($nit);
        $dv = '';

        for ($i = 0; $i < $z; $i++) {
            $y = substr($nit, $i, 1);
            $x += ($y * $arr[$z - $i]);
        }

        $y = $x % 11;

        if ($y > 1) {
            $dv = 11 - $y;
            return $dv;
        } else {
            $dv = $y;
            return $dv;
        }
    }

    /*=============================================
    GUARDAR TRIBUTOS
    =============================================*/
    static public function mdlGuardarTributos($tributos)
    {
        $db = Conexion::conectar();
        $insertados = 0;
        $actualizados = 0;

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                "INSERT INTO factus_tributos (codigo, nombre, descripcion, porcentaje_defecto)
				VALUES (:codigo, :nombre, :descripcion, :porcentaje_defecto)
				ON DUPLICATE KEY UPDATE
					nombre = VALUES(nombre),
					descripcion = VALUES(descripcion),
					porcentaje_defecto = VALUES(porcentaje_defecto),
					fecha_sincronizacion = CURRENT_TIMESTAMP"
            );

            foreach ($tributos as $tributo) {
                $stmt->execute([
                    ':codigo' => $tributo['codigo'],
                    ':nombre' => $tributo['nombre'],
                    ':descripcion' => $tributo['descripcion'] ?? null,
                    ':porcentaje_defecto' => $tributo['porcentaje'] ?? null
                ]);

                if ($stmt->rowCount() > 0) {
                    $insertados++;
                } else {
                    $actualizados++;
                }
            }

            $db->commit();
            return ['insertados' => $insertados, 'actualizados' => $actualizados];

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /*=============================================
    REGISTRAR LOG DE SINCRONIZACIÓN
    =============================================*/
    static public function mdlRegistrarSincronizacion($datos)
    {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO factus_sincronizaciones
			(tipo_dato, registros_insertados, registros_actualizados, estado, mensaje, usuario_id)
			VALUES (:tipo_dato, :insertados, :actualizados, :estado, :mensaje, :usuario_id)"
        );

        $stmt->bindParam(":tipo_dato", $datos["tipo_dato"], PDO::PARAM_STR);
        $stmt->bindParam(":insertados", $datos["insertados"], PDO::PARAM_INT);
        $stmt->bindParam(":actualizados", $datos["actualizados"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje", $datos["mensaje"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*=============================================
    OBTENER MUNICIPIOS
    =============================================*/
    static public function mdlObtenerMunicipios()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
				id_factus,
				codigo,
				nombre,
				departamento,
				codigo_departamento
			FROM factus_municipios 
			WHERE activo = 1 
			ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    OBTENER TRIBUTOS
    =============================================*/
    static public function mdlObtenerTributos()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_tributos WHERE activo = 1 ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    MOSTRAR TRIBUTO POR ID
    =============================================*/
    static public function mdlMostrarTributo($id)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_tributos WHERE id = :id"
        );

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    MOSTRAR MUNICIPIO POR ID
    =============================================*/
    static public function mdlMostrarMunicipioPorId($id)
    {
        $stmt = Conexion::conectar()->prepare("SELECT nombre FROM factus_municipios WHERE id_factus = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    OBTENER UNIDADES DE MEDIDA
    =============================================*/
    static public function mdlObtenerUnidadesMedida()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_unidades_medida WHERE activo = 1 ORDER BY nombre ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    GUARDAR UNIDADES DE MEDIDA
    =============================================*/
    static public function mdlGuardarUnidadesMedida($unidades)
    {
        $db = Conexion::conectar();
        $insertados = 0;
        $actualizados = 0;

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                "INSERT INTO factus_unidades_medida (codigo, nombre, codigo_dian)
				VALUES (:codigo, :nombre, :codigo_dian)
				ON DUPLICATE KEY UPDATE
					nombre = VALUES(nombre),
					codigo_dian = VALUES(codigo_dian),
					fecha_sincronizacion = CURRENT_TIMESTAMP"
            );

            foreach ($unidades as $unidad) {
                $stmt->execute([
                    ':codigo' => $unidad['id'], // ID de Factus
                    ':nombre' => $unidad['name'],
                    ':codigo_dian' => $unidad['code']
                ]);

                if ($stmt->rowCount() > 0) {
                    $insertados++;
                } else {
                    $actualizados++;
                }
            }

            $db->commit();
            return ['insertados' => $insertados, 'actualizados' => $actualizados];

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /*=============================================
    OBTENER ÚLTIMA SINCRONIZACIÓN
    =============================================*/
    static public function mdlObtenerUltimaSincronizacion($tipoDato)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_sincronizaciones
			WHERE tipo_dato = :tipo_dato
			ORDER BY fecha_sincronizacion DESC
			LIMIT 1"
        );
        $stmt->bindParam(":tipo_dato", $tipoDato, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    CREAR FACTURA ELECTRÓNICA EN FACTUS
    =============================================*/
    static public function mdlCrearFacturaElectronica($token, $datosFactura)
    {
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/bills/validate';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        // DEBUG: Log request payload
        $debugFile = "debug_factus_api.txt";
        $logMsg = "=== API REQUEST [" . date('Y-m-d H:i:s') . "] ===\n";
        $logMsg .= "URL: " . $url . "\n";
        $logMsg .= "Payload: " . json_encode($datosFactura, JSON_PRETTY_PRINT) . "\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosFactura));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // DEBUG: Log API response
        $logMsg = "=== API RESPONSE (Code: $httpCode) ===\n";
        $logMsg .= "Error CURL: " . $curlError . "\n";
        $logMsg .= "Response: " . $respuesta . "\n\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        return array(
            'http_code' => $httpCode,
            'respuesta' => $respuesta,
            'error_curl' => $curlError
        );
    }

    /*=============================================
    OBTENER FACTURA DESDE FACTUS
    =============================================*/
    static public function mdlObtenerFactura($token, $invoiceId)
    {
        return self::mdlObtenerDetalleDocumento($token, "bills", $invoiceId);
    }

    /*=============================================
    OBTENER DETALLE DE CUALQUIER DOCUMENTO (GENÉRICO)
    =============================================*/
    static public function mdlObtenerDetalleDocumento($token, $endpoint, $id)
    {
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/' . $endpoint . '/' . $id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return array(
            'http_code' => $httpCode,
            'respuesta' => $respuesta,
            'error_curl' => $curlError
        );
    }

    /*=============================================
    ACTUALIZAR DATOS DE FACTURA ELECTRÓNICA EN VENTA
    =============================================*/
    static public function mdlActualizarDatosFactura($idVenta, $datos)
    {
        // Si viene el numero_factura, agregarlo al update
        $sql = "UPDATE ventas SET
				estado_dian = :estado_dian,
				cufe = :cufe,
				qr_data = :qr_data,
				xml_dian = :xml_dian,
				pdf_dian = :pdf_dian,
				mensaje_dian = :mensaje_dian,
				fecha_envio_dian = :fecha_envio_dian";

        if (isset($datos["numero_factura"])) {
            $sql .= ", numero_factura = :numero_factura";
        }

        if (isset($datos["codigo"])) {
            $sql .= ", codigo = :codigo";
        }

        if (isset($datos["factus_bill_id"])) {
            $sql .= ", factus_bill_id = :factus_bill_id";
        }

        $sql .= " WHERE id = :id";

        $stmt = Conexion::conectar()->prepare($sql);

        // Bind parameters with proper NULL handling
        $stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":cufe", $datos["cufe"], PDO::PARAM_STR);
        $stmt->bindParam(":qr_data", $datos["qr_data"], PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian", $datos["xml_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian", $datos["pdf_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);

        // Handle NULL for fecha_envio_dian
        if ($datos["fecha_envio_dian"] === null) {
            $stmt->bindValue(":fecha_envio_dian", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":fecha_envio_dian", $datos["fecha_envio_dian"], PDO::PARAM_STR);
        }

        $stmt->bindParam(":id", $idVenta, PDO::PARAM_INT);

        if (isset($datos["numero_factura"])) {
            $stmt->bindParam(":numero_factura", $datos["numero_factura"], PDO::PARAM_STR);
        }

        if (isset($datos["codigo"])) {
            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
        }

        if (isset($datos["factus_bill_id"])) {
            $stmt->bindParam(":factus_bill_id", $datos["factus_bill_id"], PDO::PARAM_INT);
        }

        return $stmt->execute();
    }
    /*=============================================
    CONSULTAR MUNICIPIOS API FACTUS
    =============================================*/
    static public function mdlConsultarMunicipiosAPI($token)
    {
        $config = self::mdlObtenerConfiguracion();
        // Nota: La API retorna paginado, idealmente deberíamos recorrer todas las páginas.
        // Para esta primera versión pediremos una lista grande o iteraremos si es necesario.
        // Según tests, retorna todos o una lista grande por defecto.
        $url = $config['api_url'] . '/v1/municipalities';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ));

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($respuesta, true);
            return $data['data'] ?? [];
        } else {
            return null;
        }
    }

    /*=============================================
    CONSULTAR UNIDADES DE MEDIDA API FACTUS
    =============================================*/
    static public function mdlConsultarUnidadesAPI($token)
    {
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/measurement-units';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($respuesta, true);
            return $data['data'] ?? [];
        } else {
            return null;
        }
    }

    /*=============================================
    ACTUALIZAR NÚMERO ACTUAL DEL RANGO
    =============================================*/
    static public function mdlActualizarNumeroActualRango($rangoId, $nuevoNumero)
    {
        $stmt = Conexion::conectar()->prepare("
			UPDATE factus_rangos 
			SET numero_actual = :numero 
			WHERE id_factus = :id
		");
        $stmt->bindParam(":numero", $nuevoNumero, PDO::PARAM_INT);
        $stmt->bindParam(":id", $rangoId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /*=============================================
    OBTENER ID UNIDAD MEDIDA (Mapeo Local -> Factus)
    =============================================*/
    static public function mdlObtenerIdUnidadMedida($unidadCodigo)
    {
        // Si es numérico y ya es un ID de Factus conocido, devolverlo
        if (is_numeric($unidadCodigo) && in_array($unidadCodigo, [70, 414, 449, 499, 512, 874])) {
            return intval($unidadCodigo);
        }

        // Unidades comunes (Hardcoded por seguridad)
        $mapa = [
            '70' => 70, // Unidad (Standard)
            '94' => 70, // Unidad (Legacy)
            'KGM' => 449, // Kilogramo
            'LTR' => 512, // Litro
            'MTR' => 499, // Metro
            'H87' => 70, // Pieza (Unidad)
        ];

        if (isset($mapa[$unidadCodigo])) {
            return $mapa[$unidadCodigo];
        }

        // Fallback: Buscar en BD por código DIAN
        $stmt = Conexion::conectar()->prepare("SELECT codigo FROM factus_unidades_medida WHERE codigo_dian = :codigo LIMIT 1");
        $stmt->bindParam(":codigo", $unidadCodigo, PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch();

        if ($res) {
            return intval($res['codigo']);
        }

        return 70; // Default Unidad
    }

    /*=============================================
    OBTENER CÓDIGO MEDIO PAGO
    =============================================*/
    static public function mdlObtenerCodigoMedioPago($nombreMetodo)
    {
        // 1. Convertir a minúsculas
        $nombre = mb_strtolower(trim($nombreMetodo), 'UTF-8');

        // 2. Reemplazar tildes de forma segura para UTF-8
        $buscado = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'];
        $reemplazo = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'];
        $nombreNorm = str_replace($buscado, $reemplazo, $nombre);

        // DEBUG (Opcional, se puede quitar en producción)
        // file_put_contents("debug_payment_norm.txt", "Original: $nombreMetodo | Norm: $nombreNorm\n", FILE_APPEND);

        // 3. Mapeo estricto según DIAN/Factus

        // Efectivo (10)
        if (strpos($nombreNorm, 'efectivo') !== false || strpos($nombreNorm, 'contado') !== false)
            return "10";

        // Cheque (20)
        if (strpos($nombreNorm, 'cheque') !== false)
            return "20";

        // Consignación (42) -> Para Factus suele ser 42 (Consignación bancaria)
        if (strpos($nombreNorm, 'consignacion') !== false)
            return "42";

        // Transferencia (47) -> Incluye Nequi, Daviplata, PSE, Bancolombia
        if (
            strpos($nombreNorm, 'transferencia') !== false ||
            strpos($nombreNorm, 'nequi') !== false ||
            strpos($nombreNorm, 'daviplata') !== false ||
            strpos($nombreNorm, 'pse') !== false ||
            strpos($nombreNorm, 'bancolombia') !== false
        )
            return "47";

        // Tarjeta Crédito (48) -> Visa, Mastercard, Amex
        if (
            strpos($nombreNorm, 'credito') !== false ||
            strpos($nombreNorm, 'visa') !== false ||
            strpos($nombreNorm, 'mastercard') !== false ||
            strpos($nombreNorm, 'amex') !== false
        )
            return "48";

        // Tarjeta Débito (49) -> confirmado por respuesta API Factus
        if (strpos($nombreNorm, 'debito') !== false || strpos($nombreNorm, 'maestro') !== false)
            return "49";

        // Bonos (71)
        if (strpos($nombreNorm, 'bono') !== false)
            return "71";

        // Vales (72)
        if (strpos($nombreNorm, 'vale') !== false)
            return "72";

        // Otros -> usar Efectivo (10) como fallback ya que 'ZZ' es rechazado por Factus
        if (strpos($nombreNorm, 'otro') !== false || strpos($nombreNorm, 'definido') !== false)
            return "10";

        // Default si no coincide nada (Efectivo)
        return "10";
    }

    /*=============================================
    CONSULTAR RANGOS DE NUMERACIÓN API FACTUS
    =============================================*/
    static public function mdlConsultarRangosAPI($token)
    {
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/numbering-ranges';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($respuesta, true);
            // La API puede retornar { data: [ ... ] } o { data: { data: [ ... ] } } si es paginado
            if (isset($data['data']['data']) && is_array($data['data']['data'])) {
                return $data['data']['data'];
            }
            return $data['data'] ?? [];
        } else {
            return null;
        }
    }

    /*=============================================
    GUARDAR RANGOS DE NUMERACIÓN
    =============================================*/
    static public function mdlGuardarRangos($rangos)
    {
        $db = Conexion::conectar();
        $insertados = 0;
        $actualizados = 0;

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                "INSERT INTO factus_rangos 
				(id_factus, documento, prefijo, numero_desde, numero_hasta, numero_actual, resolucion, fecha_resolucion, llave_tecnica, estado)
				VALUES (:id_factus, :documento, :prefijo, :numero_desde, :numero_hasta, :numero_actual, :resolucion, :fecha_resolucion, :llave_tecnica, :estado)
				ON DUPLICATE KEY UPDATE
					documento = VALUES(documento),
					prefijo = VALUES(prefijo),
					numero_desde = VALUES(numero_desde),
					numero_hasta = VALUES(numero_hasta),
					numero_actual = VALUES(numero_actual),
					resolucion = VALUES(resolucion),
					fecha_resolucion = VALUES(fecha_resolucion),
					llave_tecnica = VALUES(llave_tecnica),
					estado = VALUES(estado),
					fecha_sincronizacion = CURRENT_TIMESTAMP"
            );

            foreach ($rangos as $rango) {
                $stmt->execute([
                    ':id_factus' => $rango['id'],
                    ':documento' => $rango['document'],
                    ':prefijo' => $rango['prefix'],
                    ':numero_desde' => $rango['from'],
                    ':numero_hasta' => $rango['to'],
                    ':numero_actual' => $rango['current'] ?? 0, // Campo 'current' de la API
                    ':resolucion' => $rango['resolution_number'],
                    ':fecha_resolucion' => $rango['resolution_date'] ?? null,
                    ':llave_tecnica' => $rango['technical_key'] ?? null,
                    ':estado' => ($rango['is_active'] ?? 1) ? 1 : 0
                ]);

                if ($stmt->rowCount() > 0) {
                    // rowCount puede ser 1 (insert), 2 (update) o 0 (sin cambios)
                    // Simplificamos conteo
                    $actualizados++;
                }
            }

            $db->commit();
            return ['insertados' => $insertados, 'actualizados' => $actualizados];

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }



    /*=============================================
    OBTENER RANGOS DE NUMERACIÓN
    =============================================*/
    static public function mdlObtenerRangos()
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_rangos ORDER BY prefijo ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    OBTENER RANGO ACTIVO (CONFIGURADO)
    =============================================*/
    static public function mdlObtenerRangoActivo()
    {
        // 1. Obtener el ID del rango configurado
        $config = self::mdlObtenerConfiguracion();

        if (!$config || empty($config['rango_numeracion_id'])) {
            // Fallback: Si no hay configurado, tomar el último activo
            $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE estado = 1 ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            return $stmt->fetch();
        }

        // 2. Obtener el rango específico por ID
        $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE id_factus = :id");
        $stmt->bindParam(":id", $config['rango_numeracion_id'], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /*=============================================
    OBTENER SIGUIENTE CONSECUTIVO FACTUS
    =============================================*/
    static public function mdlObtenerSiguienteConsecutivoFactus($omitirApi = false)
    {
        $rango = self::mdlObtenerRangoActivo();

        if (!$rango) {
            return 1;
        }

        $rangoId = $rango["id_factus"];
        $numeroDesde = $rango["numero_desde"];
        $numeroActualApi = isset($rango["numero_actual"]) ? $rango["numero_actual"] : 0;
        $prefijo = $rango["prefijo"];

        // Buscar todas las facturas con este prefijo y extraer el número en PHP
        // Usamos un LIKE más amplio para asegurar encontrar el prefijo
        $prefijoLike = $prefijo . '%';

        $stmt = Conexion::conectar()->prepare("
			SELECT numero_factura, codigo, resolucion_id
			FROM ventas 
			WHERE (numero_factura IS NOT NULL AND numero_factura != '' AND numero_factura LIKE :prefijo)
            OR (resolucion_id IS NOT NULL AND resolucion_id != 0)
			ORDER BY id DESC
		");

        $stmt->bindParam(":prefijo", $prefijoLike, PDO::PARAM_STR);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        // Extraer el número máximo en PHP
        $ultimoLocal = 0;
        foreach ($facturas as $factura) {
            $numeroFactura = !empty($factura["numero_factura"]) ? $factura["numero_factura"] : $factura["codigo"];

            if (empty($numeroFactura))
                continue;

            // 1. Quitar el prefijo para dejar solo la parte numérica (o con guiones)
            // Si el número empieza con el prefijo, lo quitamos. 
            // Si es un borrador (codigo), probablemente ya sea solo el número.
            if (strpos($numeroFactura, $prefijo) === 0) {
                $soloParteNumerica = substr($numeroFactura, strlen($prefijo));
            } else {
                $soloParteNumerica = $numeroFactura;
            }

            // 2. Limpiar caracteres no numéricos
            $soloNumeros = preg_replace('/[^0-9]/', '', $soloParteNumerica);

            if (!empty($soloNumeros)) {
                $numero = intval($soloNumeros);
                if ($numero > $ultimoLocal) {
                    $ultimoLocal = $numero;
                }
            }
        }

        // El siguiente será el mayor entre lo que dice la API y lo que tenemos en BD
        $numeroApiReal = $numeroActualApi;
        $json = null;

        if (!$omitirApi) {
            // 1. Consultar a la API el estado REAL del rango para evitar conflictos (409)
            $token = self::mdlObtenerAccessToken();

            if ($token && !empty($rangoId)) { // Si tenemos token y rango ID
                $config = self::mdlObtenerConfiguracion();
                $url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
                ));
                $res = curl_exec($ch);
                $json = json_decode($res, true);
                curl_close($ch);

                if (isset($json['data']['current'])) {
                    $numeroApiReal = intval($json['data']['current']);
                } elseif (isset($json['data']['current_number'])) {
                    $numeroApiReal = intval($json['data']['current_number']);
                }
            }
        }

        // Calculamos el siguiente propuesto por cada fuente
        $siguienteLocal = $ultimoLocal + 1;
        $siguienteApiCached = $numeroActualApi;
        $siguienteApiLive = $numeroApiReal; // Ya tiene el +1 si vino de la API

        // El siguiente real será el mayor de los siguientes propuestos
        // Priorizamos la API REAL si la tenemos (Live)
        if ($json !== null && (isset($json['data']['current']) || isset($json['data']['current_number']))) {
            $ultimoSugerido = max($siguienteLocal, $siguienteApiLive);
        } else {
            $ultimoSugerido = max($siguienteLocal, $siguienteApiCached);
        }

        $logMsg = date("Y-m-d H:i:s") . " | RangoId: " . ($rango['id_factus'] ?? 'N/A') . " | Prefijo: $prefijo | Local: $ultimoLocal | NextLocal: $siguienteLocal | ApiLive: $siguienteApiLive | Desde: $numeroDesde | Siguiente: $ultimoSugerido\n";
        file_put_contents(__DIR__ . "/../tmp/log_numbering.txt", $logMsg, FILE_APPEND);

        // Si el sugerido es menor que el "desde", forzamos el "desde"
        if ($ultimoSugerido < $numeroDesde) {
            $ultimoSugerido = $numeroDesde;
        }

        return $ultimoSugerido;
    }

    /*=============================================
    OBTENER SIGUIENTE CONSECUTIVO NOTA CRÉDITO
    =============================================*/
    static public function mdlObtenerSiguienteConsecutivoNC()
    {
        $rango = self::mdlObtenerRangoNC();

        if (!$rango) {
            return 1;
        }

        $rangoId = $rango["id_factus"];
        $numeroDesde = $rango["numero_desde"];
        $numeroActualApi = isset($rango["numero_actual"]) ? $rango["numero_actual"] : 0;
        $prefijo = $rango["prefijo"];

        // Buscar el número máximo en la tabla local notas_credito
        $stmt = Conexion::conectar()->prepare("
            SELECT numero_nota_credito 
            FROM notas_credito 
            WHERE numero_nota_credito LIKE :prefijo 
            ORDER BY CAST(REPLACE(numero_nota_credito, :prefijo2, '') AS UNSIGNED) DESC 
            LIMIT 1
        ");
        $prefijoLike = $prefijo . '%';
        $stmt->bindParam(":prefijo", $prefijoLike, PDO::PARAM_STR);
        $stmt->bindParam(":prefijo2", $prefijo, PDO::PARAM_STR);
        $stmt->execute();
        $ultimaNota = $stmt->fetch();

        $ultimoLocal = 0;
        if ($ultimaNota && !empty($ultimaNota["numero_nota_credito"])) {
            $soloNumeros = preg_replace('/[^0-9]/', '', str_replace($prefijo, '', $ultimaNota["numero_nota_credito"]));
            $ultimoLocal = intval($soloNumeros);
        }

        // Consultar a la API el estado REAL del rango
        $token = self::mdlObtenerAccessToken();
        $numeroApiReal = $numeroActualApi;

        if ($token && !empty($rangoId)) {
            $config = self::mdlObtenerConfiguracion();
            $url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ));
            $res = curl_exec($ch);
            $json = json_decode($res, true);
            curl_close($ch);

            if (isset($json['data']['current'])) {
                $numeroApiReal = intval($json['data']['current']);
            } elseif (isset($json['data']['current_number'])) {
                $numeroApiReal = intval($json['data']['current_number']);
            }
        }

        $siguienteLocal = $ultimoLocal + 1;
        $siguienteApiCached = $numeroActualApi + 1;
        // El campo 'current' de Factus ya representa el PRÓXIMO número a asignar (no el último usado)
        // Por lo tanto NO se suma +1
        $siguienteApiLive = $numeroApiReal;

        if (isset($json['data']['current']) || isset($json['data']['current_number'])) {
            // Tenemos dato en vivo: la API es la fuente de verdad del consecutivo real
            // NO tomamos max() con el local porque el local puede tener borradores no enviados
            $ultimoSugerido = $siguienteApiLive;
        } else {
            // Sin respuesta de API, usar el mayor entre local y cache
            $ultimoSugerido = max($siguienteLocal, $siguienteApiCached);
        }

        if ($ultimoSugerido < $numeroDesde) {
            $ultimoSugerido = $numeroDesde;
        }

        return $ultimoSugerido;
    }

    /*=============================================
    OBTENER SIGUIENTE CONSECUTIVO DOCUMENTO SOPORTE
    =============================================*/
    static public function mdlObtenerSiguienteConsecutivoDS()
    {
        // Obtener el rango activo de Factura para saber el ambiente, 
        // pero necesitamos el rango de Documento Soporte
        $stmtRango = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE documento = 'Documento Soporte' AND estado = 1 LIMIT 1");
        $stmtRango->execute();
        $rango = $stmtRango->fetch();

        if (!$rango) {
            return 1;
        }

        $rangoId = $rango["id_factus"];
        $numeroDesde = $rango["numero_desde"];
        $numeroActualApi = isset($rango["numero_actual"]) ? $rango["numero_actual"] : 0;
        $prefijo = $rango["prefijo"];

        // Buscar el número máximo en la tabla local documentos_soporte
        $stmt = Conexion::conectar()->prepare("
            SELECT numero_ds as numero_factura 
            FROM documentos_soporte 
            WHERE numero_ds LIKE :prefijo 
            ORDER BY id DESC 
            LIMIT 20
        ");
        $prefijoLike = $prefijo . '%';
        $stmt->bindParam(":prefijo", $prefijoLike, PDO::PARAM_STR);
        $stmt->execute();
        $documentos = $stmt->fetchAll();

        $ultimoLocal = 0;
        foreach ($documentos as $doc) {
            $soloNumeros = preg_replace('/[^0-9]/', '', str_replace($prefijo, '', $doc["numero_factura"]));
            $num = intval($soloNumeros);
            if ($num > $ultimoLocal) {
                $ultimoLocal = $num;
            }
        }

        // Consultar a la API el estado REAL del rango
        $token = self::mdlObtenerAccessToken();
        $numeroApiReal = $numeroActualApi;

        if ($token && !empty($rangoId)) {
            $config = self::mdlObtenerConfiguracion();
            $url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ));
            $res = curl_exec($ch);
            $json = json_decode($res, true);
            curl_close($ch);

            if (isset($json['data']['current'])) {
                $numeroApiReal = intval($json['data']['current']);
            } elseif (isset($json['data']['current_number'])) {
                $numeroApiReal = intval($json['data']['current_number']);
            }
        }

        $siguienteLocal = $ultimoLocal + 1;
        $siguienteApiCached = $numeroActualApi + 1;
        $siguienteApiLive = $numeroApiReal;

        if (isset($json['data']['current']) || isset($json['data']['current_number'])) {
            $ultimoSugerido = max($siguienteLocal, $siguienteApiLive);
        } else {
            $ultimoSugerido = max($siguienteLocal, $siguienteApiCached);
        }

        if ($ultimoSugerido < $numeroDesde) {
            $ultimoSugerido = $numeroDesde;
        }

        return $ultimoSugerido;
    }

    /*=============================================
    OBTENER SIGUIENTE CONSECUTIVO NOTA AJUSTE DS
    =============================================*/
    static public function mdlObtenerSiguienteConsecutivoNotaAjusteDS($incluirBorradores = true)
    {
        $rango = self::mdlObtenerRangoAjusteDS();

        if (!$rango) {
            return 1;
        }

        $rangoId = $rango["id_factus"];
        $numeroDesde = $rango["numero_desde"];
        $numeroActualApi = isset($rango["numero_actual"]) ? $rango["numero_actual"] : 0;
        $prefijo = $rango["prefijo"];

        // Buscar el número máximo en la tabla local notas_ajuste_ds
        $sql = "SELECT numero_nota_ajuste FROM notas_ajuste_ds WHERE numero_nota_ajuste LIKE :prefijo";

        if (!$incluirBorradores) {
            $sql .= " AND estado_dian != 'borrador'";
        }

        $sql .= " ORDER BY id DESC LIMIT 20";

        $stmt = Conexion::conectar()->prepare($sql);
        $prefijoLike = $prefijo . '%';
        $stmt->bindParam(":prefijo", $prefijoLike, PDO::PARAM_STR);
        $stmt->execute();
        $notas = $stmt->fetchAll();

        $ultimoLocal = 0;
        foreach ($notas as $nota) {
            $soloNumeros = preg_replace('/[^0-9]/', '', str_replace($prefijo, '', $nota["numero_nota_ajuste"]));
            $num = intval($soloNumeros);
            if ($num > $ultimoLocal) {
                $ultimoLocal = $num;
            }
        }

        // Consultar a la API el estado REAL del rango
        $token = self::mdlObtenerAccessToken();
        $numeroApiReal = $numeroActualApi;

        if ($token && !empty($rangoId)) {
            $config = self::mdlObtenerConfiguracion();
            $url = $config['api_url'] . '/v1/numbering-ranges/' . $rangoId;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ));
            $res = curl_exec($ch);
            $json = json_decode($res, true);
            curl_close($ch);

            if (isset($json['data']['current'])) {
                $numeroApiReal = intval($json['data']['current']);
            } elseif (isset($json['data']['current_number'])) {
                $numeroApiReal = intval($json['data']['current_number']);
            }
        }

        $siguienteLocal = $ultimoLocal + 1;
        $siguienteApiCached = $numeroActualApi + 1;
        $siguienteApiLive = $numeroApiReal;

        if (isset($json['data']['current']) || isset($json['data']['current_number'])) {
            $ultimoSugerido = max($siguienteLocal, $siguienteApiLive);
        } else {
            $ultimoSugerido = max($siguienteLocal, $siguienteApiCached);
        }

        if ($ultimoSugerido < $numeroDesde) {
            $ultimoSugerido = $numeroDesde;
        }

        return $ultimoSugerido;
    }

    /*=============================================
    MOSTRAR TIPOS DE DOCUMENTO (DESDE BASE DE DATOS LOCAL)
    =============================================*/
    static public function mdlMostrarTiposDocumento()
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_tipos_documento WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    MOSTRAR TODOS LOS RANGOS DE NUMERACIÓN
    =============================================*/
    static public function mdlMostrarRangos()
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    CREAR NOTA CRÉDITO EN FACTUS API
    =============================================*/
    static public function mdlCrearNotaCredito($token, $datosNC)
    {
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/credit-notes/validate';

        $debugFile = 'debug_nota_credito_' . date('Y-m-d_His') . '.txt';
        $logMsg = "=== SOLICITUD NOTA CRÉDITO ===\n";
        $logMsg .= "URL: $url\n";
        $logMsg .= "Datos NC: " . json_encode($datosNC, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosNC));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // DEBUG: Log API response
        $logMsg = "=== API RESPONSE (Code: $httpCode) ===\n";
        $logMsg .= "Error CURL: " . $curlError . "\n";
        $logMsg .= "Response: " . $respuesta . "\n\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        return array(
            'http_code' => $httpCode,
            'respuesta' => $respuesta,
            'error_curl' => $curlError
        );
    }

    /*=============================================
    CREAR NOTA DE AJUSTE DS EN FACTUS API
    =============================================*/
    static public function mdlCrearNotaAjusteDS($token, $datosNota)
    {
        $apiUrl = self::mdlObtenerConfiguracion()['api_url'];
        $url = $apiUrl . "/v1/adjustment-notes/validate";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosNota));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: application/json'
        ));

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // DEBUG: Log API response
        $debugFile = __DIR__ . '/../ajax/debug_nota_ajuste_' . date('Y-m-d_His') . '.txt';
        $logMsg = "=== API REQUEST AJUSTE ===\n";
        $logMsg .= "URL: " . $url . "\n";
        $logMsg .= "Payload: " . json_encode($datosNota) . "\n\n";
        $logMsg .= "=== API RESPONSE AJUSTE (Code: $httpCode) ===\n";
        $logMsg .= "Error CURL: " . $curlError . "\n";
        $logMsg .= "Response: " . $respuesta . "\n\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        return array(
            "respuesta" => $respuesta,
            "http_code" => $httpCode
        );
    }

    /*=============================================
    GUARDAR NOTA CRÉDITO EN BASE DE DATOS
    =============================================*/
    static public function mdlGuardarNotaCredito($datos)
    {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO notas_credito (
				id_venta_original, numero_factura_original, tipo_nota, motivo,
				productos, monto_total, estado_dian, numero_nota_credito,
				cufe_nc, qr_data_nc, xml_dian_nc, pdf_dian_nc, mensaje_dian,
				fecha_envio_dian, id_usuario, id_cliente, observacion, metodo_pago
			) VALUES (
				:id_venta, :num_factura, :tipo, :motivo,
				:productos, :monto, :estado, :num_nc,
				:cufe, :qr, :xml, :pdf, :mensaje,
				:fecha_envio, :usuario, :id_cliente, :observacion, :metodo_pago
			)"
        );

        $stmt->bindParam(":id_venta", $datos["id_venta_original"], PDO::PARAM_INT);
        $stmt->bindParam(":num_factura", $datos["numero_factura_original"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo", $datos["tipo_nota"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
        $stmt->bindParam(":monto", $datos["monto_total"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":num_nc", $datos["numero_nota_credito"], PDO::PARAM_STR);
        $stmt->bindParam(":cufe", $datos["cufe_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":qr", $datos["qr_data_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":xml", $datos["xml_dian_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf", $datos["pdf_dian_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje", $datos["mensaje_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_envio", $datos["fecha_envio_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    GUARDAR NOTA DE AJUSTE DS EN BASE DE DATOS
    =============================================*/
    static public function mdlGuardarNotaAjusteDS($datos)
    {
        $con = Conexion::conectar();
        $stmt = $con->prepare("INSERT INTO notas_ajuste_ds
			(id_ds_original, numero_ds_original, tipo_nota, motivo, productos, monto_total, estado_dian, numero_nota_ajuste, cuds_ajuste, qr_data, xml_dian, pdf_dian, mensaje_dian, fecha_envio_dian, id_usuario, id_proveedor, observacion, metodo_pago)
			VALUES
			(:id_ds_original, :numero_ds_original, :tipo_nota, :motivo, :productos, :monto_total, :estado_dian, :numero_nota_ajuste, :cuds_ajuste, :qr_data, :xml_dian, :pdf_dian, :mensaje_dian, :fecha_envio_dian, :id_usuario, :id_proveedor, :observacion, :metodo_pago)");

        $stmt->bindParam(":id_ds_original", $datos["id_ds_original"], PDO::PARAM_INT);
        $stmt->bindParam(":numero_ds_original", $datos["numero_ds_original"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_nota", $datos["tipo_nota"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_total", $datos["monto_total"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":numero_nota_ajuste", $datos["numero_nota_ajuste"], PDO::PARAM_STR);
        $stmt->bindParam(":cuds_ajuste", $datos["cuds_ajuste"], PDO::PARAM_STR);
        $stmt->bindParam(":qr_data", $datos["qr_data"], PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian", $datos["xml_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian", $datos["pdf_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_envio_dian", $datos["fecha_envio_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
        $stmt->bindParam(":observacion", $datos["observacion"], PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $con->lastInsertId();
        } else {
            return "error";
        }
    }

    /*=============================================
    ACTUALIZAR DATOS DE NOTA DE AJUSTE DS
    =============================================*/
    static public function mdlActualizarDatosNotaAjusteDS($idNota, $datos)
    {
        $sql = "UPDATE notas_ajuste_ds SET
                estado_dian = :estado_dian,
                cuds_ajuste = :cuds_ajuste,
                qr_data = :qr_data,
                xml_dian = :xml_dian,
                pdf_dian = :pdf_dian,
                mensaje_dian = :mensaje_dian,
                fecha_envio_dian = :fecha_envio_dian";

        if (isset($datos["numero_nota_ajuste"]) && !empty($datos["numero_nota_ajuste"])) {
            $sql .= ", numero_nota_ajuste = :numero_nota_ajuste";
        }

        $sql .= " WHERE id = :id";

        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":cuds_ajuste", $datos["cuds_ajuste"], PDO::PARAM_STR);
        $stmt->bindParam(":qr_data", $datos["qr_data"], PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian", $datos["xml_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian", $datos["pdf_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_envio_dian", $datos["fecha_envio_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $idNota, PDO::PARAM_INT);

        if (isset($datos["numero_nota_ajuste"]) && !empty($datos["numero_nota_ajuste"])) {
            $stmt->bindParam(":numero_nota_ajuste", $datos["numero_nota_ajuste"], PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    OBTENER RANGO DE NOTAS CRÉDITO
    =============================================*/
    static public function mdlObtenerRangoNC()
    {
        // Obtener el rango de factura configurado para saber en qué ambiente estamos
        $config = self::mdlObtenerConfiguracion();
        $rangoFacturaId = $config['rango_numeracion_id'] ?? null;

        if ($rangoFacturaId) {
            // Buscar el rango de factura configurado para obtener su id_factus
            $stmtFactura = Conexion::conectar()->prepare(
                "SELECT id_factus FROM factus_rangos WHERE id_factus = :rango_id LIMIT 1"
            );
            $stmtFactura->bindParam(":rango_id", $rangoFacturaId, PDO::PARAM_INT);
            $stmtFactura->execute();
            $rangoFactura = $stmtFactura->fetch();

            if ($rangoFactura) {
                $facturaIdFactus = intval($rangoFactura['id_factus']);
                // Los rangos del mismo ambiente tienen id_factus consecutivos
                // El rango NC suele ser facturaId + 1
                // Buscar el rango NC más cercano al rango de factura configurado
                $stmt = Conexion::conectar()->prepare(
                    "SELECT * FROM factus_rangos 
                 WHERE documento = 'Nota Crédito' 
                 AND estado = 1 
                 AND ABS(id_factus - :factura_id) <= 5
                 ORDER BY ABS(id_factus - :factura_id2) ASC
                 LIMIT 1"
                );
                $stmt->bindParam(":factura_id", $facturaIdFactus, PDO::PARAM_INT);
                $stmt->bindParam(":factura_id2", $facturaIdFactus, PDO::PARAM_INT);
                $stmt->execute();
                $resultado = $stmt->fetch();
                if ($resultado) {
                    return $resultado;
                }
            }
        }

        // Fallback: devolver el último rango NC activo (el más reciente sincronizado)
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_rangos 
         WHERE documento = 'Nota Crédito' 
         AND estado = 1 
         ORDER BY id DESC
         LIMIT 1"
        );
        $stmt->execute();
        return $stmt->fetch();
    }


    /*=============================================
    VERIFICAR SI UNA VENTA YA TIENE NOTA CRÉDITO
    =============================================*/
    static public function mdlTieneNotaCredito($idVenta)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) as total FROM notas_credito 
			 WHERE id_venta_original = :id_venta
			 AND estado_dian IN ('enviada', 'aceptada')"
        );
        $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'] > 0;
    }

    /*=============================================
    OBTENER VENTAS CON NOTA CRÉDITO (BULK)
    =============================================*/
    static public function mdlObtenerVentasConNotaCredito($idsVentas)
    {
        if (empty($idsVentas)) {
            return [];
        }

        // Crear placeholders para la consulta IN (?, ?, ?)
        $placeholders = str_repeat('?,', count($idsVentas) - 1) . '?';

        $stmt = Conexion::conectar()->prepare(
            "SELECT DISTINCT id_venta_original FROM notas_credito 
             WHERE id_venta_original IN ($placeholders)
             AND estado_dian IN ('enviada', 'aceptada')"
        );

        $stmt->execute($idsVentas);
        $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $resultados;
    }

    /*=============================================
    OBTENER NOTA CRÉDITO POR VENTA
    =============================================*/
    static public function mdlObtenerNotaCredito($idVenta)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM notas_credito 
             WHERE id_venta_original = :id_venta 
             AND estado_dian IN ('enviada', 'aceptada') 
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    OBTENER TODAS LAS NOTAS CRÉDITO POR VENTA
    =============================================*/
    static public function mdlObtenerNotasCreditoPorVenta($idVenta)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM notas_credito 
             WHERE id_venta_original = :id_venta 
             ORDER BY id DESC"
        );
        $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    ACTUALIZAR NÚMERO ACTUAL DEL RANGO NC
    =============================================*/
    static public function mdlActualizarNumeroActualRangoNC($rangoId, $nuevoNumero)
    {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE factus_rangos 
			 SET numero_actual = :numero 
			 WHERE id_factus = :id"
        );
        $stmt->bindParam(":numero", $nuevoNumero, PDO::PARAM_INT);
        $stmt->bindParam(":id", $rangoId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    OBTENER RANGO DE DOCUMENTO SOPORTE
    =============================================*/
    static public function mdlObtenerRangoDS()
    {
        $config = self::mdlObtenerConfiguracion();
        $rangoFacturaId = $config['rango_numeracion_id'] ?? null;

        if ($rangoFacturaId) {
            $stmt = Conexion::conectar()->prepare(
                "SELECT * FROM factus_rangos 
                 WHERE documento LIKE '%Documento Soporte%' 
                 AND documento NOT LIKE '%Nota de Ajuste%'
                 AND estado = 1 
                 AND ABS(id_factus - :factura_id) <= 10
                 ORDER BY ABS(id_factus - :factura_id2) ASC
                 LIMIT 1"
            );
            $stmt->bindParam(":factura_id", $rangoFacturaId, PDO::PARAM_INT);
            $stmt->bindParam(":factura_id2", $rangoFacturaId, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch();
            if ($resultado) {
                return $resultado;
            }
        }

        // Fallback: último activo
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM factus_rangos 
             WHERE documento LIKE '%Documento Soporte%' 
             AND documento NOT LIKE '%Nota de Ajuste%'
             AND estado = 1 
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    CREAR DOCUMENTO SOPORTE EN FACTUS API
    =============================================*/
    static public function mdlCrearDocumentoSoporte($token, $datosDS)
    {
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/support-documents/validate';

        $debugFile = __DIR__ . '/../ajax/debug_ds_' . date('Y-m-d_His') . '.txt';
        $logMsg = "=== SOLICITUD DOCUMENTO SOPORTE ===\n";
        $logMsg .= "URL: $url\n";
        $logMsg .= "Datos DS: " . json_encode($datosDS, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosDS));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $logMsg = "=== API RESPONSE (Code: $httpCode) ===\n";
        $logMsg .= "Error CURL: " . $curlError . "\n";
        $logMsg .= "Response: " . $respuesta . "\n\n";
        file_put_contents($debugFile, $logMsg, FILE_APPEND);

        return array(
            'http_code' => $httpCode,
            'respuesta' => $respuesta,
            'error_curl' => $curlError
        );
    }

    /*=============================================
    GUARDAR DOCUMENTO SOPORTE EN BASE DE DATOS
    =============================================*/
    static public function mdlGuardarDocumentoSoporte($datos)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "INSERT INTO documentos_soporte (
				numero_ds, id_proveedor, fecha_emision, metodo_pago,
				productos, monto_total, estado_dian, cuds,
				qr_data, pdf_dian, xml_dian, mensaje_dian,
				factus_id, id_usuario, tipo_descuento, valor_descuento,
				monto_descuento, retenciones
			) VALUES (
				:numero, :proveedor, :fecha, :metodo,
				:productos, :monto, :estado, :cuds,
				:qr, :pdf, :xml, :mensaje,
				:factus_id, :usuario, :tipo_desc, :valor_desc,
				:monto_desc, :retenciones
			)"
        );

        $stmt->bindParam(":numero", $datos["numero_ds"], PDO::PARAM_STR);
        $stmt->bindParam(":proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $datos["fecha_emision"], PDO::PARAM_STR);
        $stmt->bindParam(":metodo", $datos["metodo_pago"], PDO::PARAM_STR);
        $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
        $stmt->bindParam(":monto", $datos["monto_total"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":cuds", $datos["cuds"], PDO::PARAM_STR);
        $stmt->bindParam(":qr", $datos["qr_data"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf", $datos["pdf_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":xml", $datos["xml_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje", $datos["mensaje_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":factus_id", $datos["factus_id"], PDO::PARAM_INT);
        $stmt->bindParam(":usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":tipo_desc", $datos["tipo_descuento"], PDO::PARAM_STR);
        $stmt->bindParam(":valor_desc", $datos["valor_descuento"], PDO::PARAM_STR);
        $stmt->bindParam(":monto_desc", $datos["monto_descuento"], PDO::PARAM_STR);
        $stmt->bindParam(":retenciones", $datos["retenciones"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        } else {
            return "error";
        }
    }

    /*=============================================
    MOSTRAR DOCUMENTOS SOPORTE
    =============================================*/
    static public function mdlMostrarDocumentosSoporte($item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM documentos_soporte WHERE $item = :$item ORDER BY id DESC");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM documentos_soporte ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /*=============================================
    MOSTRAR ÚLTIMO DOCUMENTO SOPORTE
    =============================================*/
    static public function mdlMostrarUltimoDocumentoSoporte()
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM documentos_soporte ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    ELIMINAR DOCUMENTO SOPORTE
    =============================================*/
    static public function mdlEliminarDocumentoSoporte($id)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM documentos_soporte WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    OBTENER RANGO PARA NOTA DE AJUSTE DS
    =============================================*/
    static public function mdlObtenerRangoAjusteDS()
    {
        // Buscar específicamente el de Documento Soporte, priorizando el ID más alto (generalmente el más reciente/válido)
        $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE documento = 'Nota de Ajuste Documento Soporte' AND estado = 1 ORDER BY id_factus DESC LIMIT 1");
        $stmt->execute();
        $rango = $stmt->fetch();

        if (!$rango) {
            // Fallback por si el nombre varía ligeramente
            $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE documento LIKE 'Nota de Ajuste%Soporte%' AND estado = 1 ORDER BY id_factus DESC LIMIT 1");
            $stmt->execute();
            $rango = $stmt->fetch();
        }

        if (!$rango) {
            // Si no hay específico, buscamos el de Nota Crédito como fallback extremo
            $stmt = Conexion::conectar()->prepare("SELECT * FROM factus_rangos WHERE documento LIKE '%credit-note%' AND estado = 1 LIMIT 1");
            $stmt->execute();
            $rango = $stmt->fetch();
        }

        return $rango;
    }

    /*=============================================
    VERIFICAR SI UN DOCUMENTO SOPORTE TIENE NOTA DE AJUSTE
    =============================================*/
    static public function mdlObtenerNotaAjusteDS($idDS)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM notas_ajuste_ds WHERE id_ds_original = :id_ds");
        $stmt->bindParam(":id_ds", $idDS, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    VERIFICAR SI UN DOCUMENTO SOPORTE TIENE ALGUNA NOTA DE AJUSTE
    =============================================*/
    static public function mdlTieneNotaAjusteDS($idDS)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) as total FROM notas_ajuste_ds 
             WHERE id_ds_original = :id_ds
             AND estado_dian IN ('enviada', 'aceptada')"
        );
        $stmt->bindParam(":id_ds", $idDS, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'] > 0;
    }

    /*=============================================
    OBTENER TODAS LAS NOTAS DE AJUSTE POR DOCUMENTO SOPORTE
    =============================================*/
    static public function mdlObtenerNotasAjusteDSPorDS($idDS)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM notas_ajuste_ds 
             WHERE id_ds_original = :id_ds 
             ORDER BY id DESC"
        );
        $stmt->bindParam(":id_ds", $idDS, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    MOSTRAR NOTAS DE AJUSTE DS
    =============================================*/
    static public function mdlMostrarNotasAjusteDS($item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM notas_ajuste_ds WHERE $item = :$item");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM notas_ajuste_ds ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /*=============================================
    ACTUALIZAR DATOS DE DOCUMENTO SOPORTE
    =============================================*/
    static public function mdlActualizarDatosDocumentoSoporte($idDS, $datos)
    {
        $sql = "UPDATE documentos_soporte SET
                estado_dian = :estado_dian,
                cuds = :cuds,
                qr_data = :qr_data,
                xml_dian = :xml_dian,
                pdf_dian = :pdf_dian,
                mensaje_dian = :mensaje_dian,
                factus_id = :factus_id";

        if (isset($datos["numero_ds"])) {
            $sql .= ", numero_ds = :numero_ds";
        }

        $sql .= " WHERE id = :id";

        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":cuds", $datos["cuds"], PDO::PARAM_STR);
        $stmt->bindParam(":qr_data", $datos["qr_data"], PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian", $datos["xml_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian", $datos["pdf_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":factus_id", $datos["factus_id"], PDO::PARAM_INT);
        $stmt->bindParam(":id", $idDS, PDO::PARAM_INT);

        if (isset($datos["numero_ds"])) {
            $stmt->bindParam(":numero_ds", $datos["numero_ds"], PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ELIMINAR NOTA DE AJUSTE DS
    =============================================*/
    static public function mdlEliminarNotaAjusteDS($id)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM notas_ajuste_ds WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    MOSTRAR NOTAS CREDITO
    =============================================*/
    static public function mdlMostrarNotasCredito($tabla, $item, $valor)
    {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /*=============================================
    MOSTRAR NOTAS CREDITO SERVER-SIDE
    =============================================*/
    static public function mdlMostrarNotasCreditoServerSide($where, $order, $limit)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT nc.*,
                    c.nombre AS cliente_nombre,
                    c.email  AS cliente_email
             FROM notas_credito nc
             LEFT JOIN clientes c ON nc.id_cliente = c.id
             $where $order $limit"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    TOTAL NOTAS CREDITO SERVER-SIDE
    =============================================*/
    static public function mdlGetTotalNotasCredito($where)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) FROM notas_credito nc
             LEFT JOIN clientes c ON nc.id_cliente = c.id
             $where"
        );
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /*=============================================
    ACTUALIZAR DATOS DE NOTA CREDITO
    =============================================*/
    static public function mdlActualizarNotaCredito($idNota, $datos)
    {
        $sql = "UPDATE notas_credito SET
                estado_dian = :estado_dian,
                cufe_nc = :cufe_nc,
                qr_data_nc = :qr_data_nc,
                xml_dian_nc = :xml_dian_nc,
                pdf_dian_nc = :pdf_dian_nc,
                mensaje_dian = :mensaje_dian,
                fecha_envio_dian = :fecha_envio_dian";

        if (isset($datos["numero_nota_credito"])) {
            $sql .= ", numero_nota_credito = :numero_nota_credito";
        }

        $sql .= " WHERE id = :id";

        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(":estado_dian", $datos["estado_dian"], PDO::PARAM_STR);
        $stmt->bindParam(":cufe_nc", $datos["cufe_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":qr_data_nc", $datos["qr_data_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":xml_dian_nc", $datos["xml_dian_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":pdf_dian_nc", $datos["pdf_dian_nc"], PDO::PARAM_STR);
        $stmt->bindParam(":mensaje_dian", $datos["mensaje_dian"], PDO::PARAM_STR);

        if ($datos["fecha_envio_dian"] === null) {
            $stmt->bindValue(":fecha_envio_dian", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":fecha_envio_dian", $datos["fecha_envio_dian"], PDO::PARAM_STR);
        }

        $stmt->bindParam(":id", $idNota, PDO::PARAM_INT);

        if (isset($datos["numero_nota_credito"])) {
            $stmt->bindParam(":numero_nota_credito", $datos["numero_nota_credito"], PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ELIMINAR NOTA CREDITO
    =============================================*/
    static public function mdlEliminarNotaCredito($id)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM notas_credito WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    MOSTRAR ÚLTIMA NOTA DE AJUSTE DS
    =============================================*/
    static public function mdlMostrarUltimaNotaAjusteDS($tabla)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    MOSTRAR ÚLTIMA NOTA CRÉDITO
    =============================================*/
    static public function mdlMostrarUltimaNotaCredito($tabla)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    OBTENER KPIs PARA REPORTES
    =============================================*/
    static public function mdlObtenerKPIsReporte($fechaInicial, $fechaFinal, $categoria, $tercero = "todos", $idUsuario = "todos")
    {
        $db = Conexion::conectar();
        
        if ($fechaInicial == null || $fechaInicial == "") {
            $fechaInicial = "2000-01-01";
            $fechaFinal = "2100-12-31";
        }

        // Ajustar fechas para incluir todo el día
        $inicio = $fechaInicial . " 00:00:00";
        $fin = $fechaFinal . " 23:59:59";

        $filtroCliente = ($tercero != "todos" && ($categoria == "todos" || $categoria == "facturas" || $categoria == "nc")) ? " AND id_cliente = :tc " : "";
        $filtroProveedor = ($tercero != "todos" && ($categoria == "ds" || $categoria == "na")) ? " AND id_proveedor = :tp " : "";
        $filtroVendedor = ($idUsuario != "todos") ? " AND id_vendedor = :uidv " : "";
        $filtroUsuario  = ($idUsuario != "todos") ? " AND id_usuario = :uidu " : "";

        // 1. VENTAS (Facturadas y aceptadas)
        $stmtVentas = $db->prepare("SELECT SUM(total) as t, SUM(impuesto) as i, COUNT(*) as c 
                                    FROM ventas 
                                    WHERE estado_dian IN ('aceptada', 'enviada') 
                                    AND (fecha BETWEEN :s1 AND :e1 OR fecha_envio_dian BETWEEN :s2 AND :e2)" . $filtroCliente . $filtroVendedor);
        $stmtVentas->bindParam(":s1", $inicio, PDO::PARAM_STR);
        $stmtVentas->bindParam(":e1", $fin, PDO::PARAM_STR);
        $stmtVentas->bindParam(":s2", $inicio, PDO::PARAM_STR);
        $stmtVentas->bindParam(":e2", $fin, PDO::PARAM_STR);
        if ($filtroCliente != "")
            $stmtVentas->bindParam(":tc", $tercero, PDO::PARAM_INT);
        if ($filtroVendedor != "")
            $stmtVentas->bindParam(":uidv", $idUsuario, PDO::PARAM_INT);
        $stmtVentas->execute();
        $resVentas = $stmtVentas->fetch();

        // 2. NOTAS CRÉDITO (Reducciones de venta)
        $stmtNC = $db->prepare("SELECT SUM(monto_total) as t, COUNT(*) as c 
                                FROM notas_credito 
                                WHERE estado_dian IN ('aceptada', 'enviada') 
                                AND IFNULL(fecha_envio_dian, fecha_creacion) BETWEEN :s3 AND :e3" . $filtroCliente . $filtroUsuario);
        $stmtNC->bindParam(":s3", $inicio, PDO::PARAM_STR);
        $stmtNC->bindParam(":e3", $fin, PDO::PARAM_STR);
        if ($filtroCliente != "")
            $stmtNC->bindParam(":tc", $tercero, PDO::PARAM_INT);
        if ($filtroUsuario != "")
            $stmtNC->bindParam(":uidu", $idUsuario, PDO::PARAM_INT);
        $stmtNC->execute();
        $resNC = $stmtNC->fetch();

        // 3. DOCUMENTOS SOPORTE (Gastos/Compras)
        $stmtDS = $db->prepare("SELECT SUM(monto_total) as t, COUNT(*) as c 
                                FROM documentos_soporte 
                                WHERE estado_dian IN ('aceptada', 'enviada') 
                                AND fecha_emision BETWEEN :s4 AND :e4" . $filtroProveedor . $filtroUsuario);
        $stmtDS->bindParam(":s4", $inicio, PDO::PARAM_STR);
        $stmtDS->bindParam(":e4", $fin, PDO::PARAM_STR);
        if ($filtroProveedor != "")
            $stmtDS->bindParam(":tp", $tercero, PDO::PARAM_INT);
        if ($filtroUsuario != "")
            $stmtDS->bindParam(":uidu", $idUsuario, PDO::PARAM_INT);
        $stmtDS->execute();
        $resDS = $stmtDS->fetch();

        // 4. NOTAS DE AJUSTE DS (Reducciones de DS)
        $stmtNA = $db->prepare("SELECT SUM(monto_total) as t, COUNT(*) as c 
                                FROM notas_ajuste_ds 
                                WHERE estado_dian IN ('aceptada', 'enviada') 
                                AND IFNULL(fecha_envio_dian, fecha_registro) BETWEEN :s5 AND :e5" . $filtroProveedor . $filtroUsuario);
        $stmtNA->bindParam(":s5", $inicio, PDO::PARAM_STR);
        $stmtNA->bindParam(":e5", $fin, PDO::PARAM_STR);
        if ($filtroProveedor != "")
            $stmtNA->bindParam(":tp", $tercero, PDO::PARAM_INT);
        if ($filtroUsuario != "")
            $stmtNA->bindParam(":uidu", $idUsuario, PDO::PARAM_INT);
        $stmtNA->execute();
        $resNA = $stmtNA->fetch();

        $totalVentasLiquido = 0;
        $totalIva = 0;
        $totalDSLiquido = 0;
        $totalDocs = 0;

        // Venta (Solo Facturas)
        if ($categoria == "todos" || $categoria == "facturas") {
            $totalVentasLiquido += ($resVentas["t"] ?? 0);
            $totalIva += ($resVentas["i"] ?? 0);
            $totalDocs += ($resVentas["c"] ?? 0);
        }

        // Notas Crédito (Restan a venta)
        if ($categoria == "todos" || $categoria == "nc") {
            $totalVentasLiquido -= ($resNC["t"] ?? 0);
            $totalDocs += ($resNC["c"] ?? 0);
        }

        // Doc Soporte (Solo DS)
        if (($categoria == "todos" && $tercero == "todos") || $categoria == "ds") {
            $totalDSLiquido += ($resDS["t"] ?? 0);
            $totalDocs += ($resDS["c"] ?? 0);
        }

        // Notas Ajuste (Restan a DS)
        if (($categoria == "todos" && $tercero == "todos") || $categoria == "na") {
            $totalDSLiquido -= ($resNA["t"] ?? 0);
            $totalDocs += ($resNA["c"] ?? 0);
        }

        return [
            "totalVentas" => $totalVentasLiquido,
            "totalIva" => $totalIva,
            "totalDS" => $totalDSLiquido,
            "totalDocs" => $totalDocs
        ];
    }

    /*=============================================
    OBTENER DATOS PARA GRÁFICO DE VENTAS
    =============================================*/
    static public function mdlObtenerVentasGrafico($fechaInicial, $fechaFinal, $categoria, $tercero = "todos", $idUsuario = "todos")
    {
        $db = Conexion::conectar();
        $inicio = $fechaInicial . " 00:00:00";
        $fin = $fechaFinal . " 23:59:59";

        $filtroCliente = ($tercero != "todos" && ($categoria == "todos" || $categoria == "facturas" || $categoria == "nc")) ? " AND id_cliente = :tc " : "";
        $filtroProveedor = ($tercero != "todos" && ($categoria == "ds" || $categoria == "na")) ? " AND id_proveedor = :tp " : "";
        $filtroVendedor = ($idUsuario != "todos") ? " AND id_vendedor = :uidv " : "";
        $filtroUsuario  = ($idUsuario != "todos") ? " AND id_usuario = :uidu " : "";

        if ($categoria == "ds") {
            $stmt = $db->prepare("SELECT DATE(fecha_emision) as dia, SUM(monto_total) as total 
                                  FROM documentos_soporte 
                                  WHERE estado_dian IN ('aceptada', 'enviada') 
                                  AND fecha_emision BETWEEN :s1 AND :e1 " . $filtroProveedor . $filtroUsuario . "
                                  GROUP BY DATE(fecha_emision) 
                                  ORDER BY DATE(fecha_emision) ASC");
        } else if ($categoria == "nc") {
            $stmt = $db->prepare("SELECT DATE(IFNULL(fecha_envio_dian, fecha_creacion)) as dia, SUM(monto_total) as total 
                                  FROM notas_credito 
                                  WHERE estado_dian IN ('aceptada', 'enviada') 
                                  AND IFNULL(fecha_envio_dian, fecha_creacion) BETWEEN :s1 AND :e1 " . $filtroCliente . $filtroUsuario . "
                                  GROUP BY DATE(IFNULL(fecha_envio_dian, fecha_creacion)) 
                                  ORDER BY DATE(IFNULL(fecha_envio_dian, fecha_creacion)) ASC");
        } else if ($categoria == "na") {
            $stmt = $db->prepare("SELECT DATE(IFNULL(fecha_envio_dian, fecha_registro)) as dia, SUM(monto_total) as total 
                                  FROM notas_ajuste_ds 
                                  WHERE estado_dian IN ('aceptada', 'enviada') 
                                  AND IFNULL(fecha_envio_dian, fecha_registro) BETWEEN :s1 AND :e1 " . $filtroProveedor . $filtroUsuario . "
                                  GROUP BY DATE(IFNULL(fecha_envio_dian, fecha_registro)) 
                                  ORDER BY DATE(IFNULL(fecha_envio_dian, fecha_registro)) ASC");
        } else {
            // Para "todos" o "facturas", mostramos tendencia de ventas facturadas
            $stmt = $db->prepare("SELECT DATE(fecha) as dia, SUM(total) as total 
                                  FROM ventas 
                                  WHERE estado_dian IN ('aceptada', 'enviada') 
                                  AND fecha BETWEEN :s1 AND :e1 " . $filtroCliente . $filtroVendedor . "
                                  GROUP BY DATE(fecha) 
                                  ORDER BY DATE(fecha) ASC");
        }

        $stmt->bindParam(":s1", $inicio, PDO::PARAM_STR);
        $stmt->bindParam(":e1", $fin, PDO::PARAM_STR);

        if ($categoria == "ds" || $categoria == "na") {
            if ($filtroProveedor != "")
                $stmt->bindParam(":tp", $tercero, PDO::PARAM_INT);
            if ($filtroUsuario != "")
                $stmt->bindParam(":uidu", $idUsuario, PDO::PARAM_INT);
        } else {
            if ($filtroCliente != "")
                $stmt->bindParam(":tc", $tercero, PDO::PARAM_INT);
            if ($categoria == "nc") {
                if ($filtroUsuario != "")
                    $stmt->bindParam(":uidu", $idUsuario, PDO::PARAM_INT);
            } else {
                if ($filtroVendedor != "")
                    $stmt->bindParam(":uidv", $idUsuario, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    MOSTRAR REPORTE DETALLADO (LISTADO CONSOLIDADO)
    =============================================*/
    static public function mdlMostrarReporteDetallado($fechaInicial, $fechaFinal, $categoria, $tercero = "todos", $idUsuario = "todos")
    {
        $db = Conexion::conectar();
        
        if ($fechaInicial == null || $fechaInicial == "") {
            $fechaInicial = "2000-01-01";
            $fechaFinal = "2100-12-31";
        }
        
        $inicio = $fechaInicial . " 00:00:00";
        $fin = $fechaFinal . " 23:59:59";

        $filtroCliente = ($tercero != "todos" && ($categoria == "todos" || $categoria == "facturas" || $categoria == "nc")) ? " AND id_cliente = :tc " : "";
        $filtroProveedor = ($tercero != "todos" && ($categoria == "ds" || $categoria == "na")) ? " AND id_proveedor = :tp " : "";
        $filtroVendedor = ($idUsuario != "todos") ? " AND v.id_vendedor = :uidv " : "";
        $filtroNCUsuario = ($idUsuario != "todos") ? " AND nc.id_usuario = :uidu1 " : "";
        $filtroDSUsuario = ($idUsuario != "todos") ? " AND ds.id_usuario = :uidu2 " : "";
        $filtroNAUsuario = ($idUsuario != "todos") ? " AND na.id_usuario = :uidu3 " : "";

        $queryVentas = "(SELECT 'Factura' as tipo, numero_factura as numero, IFNULL(cl.nombre, 'Venta General') as tercero, IFNULL(us.nombre, 'Sistema/Varios') as vendedor, v.fecha as fecha, total as monto, estado_dian as estado, v.id as id_doc
             FROM ventas v
             LEFT JOIN clientes cl ON v.id_cliente = cl.id
             LEFT JOIN usuarios us ON v.id_vendedor = us.id
             WHERE v.estado_dian IN ('aceptada', 'enviada')
             AND (v.fecha BETWEEN :i1 AND :f1 OR v.fecha_envio_dian BETWEEN :i2 AND :f2) " . str_replace(":tc", ":tc1", $filtroCliente) . $filtroVendedor . ")";

        $queryNC = "(SELECT 'Nota Crédito' as tipo, numero_nota_credito as numero, IFNULL(cl.nombre, 'Sin Cliente') as tercero, IFNULL(us.nombre, 'Sistema') as vendedor, IFNULL(fecha_envio_dian, fecha_creacion) as fecha, monto_total as monto, estado_dian as estado, nc.id as id_doc
             FROM notas_credito nc
             LEFT JOIN clientes cl ON nc.id_cliente = cl.id
             LEFT JOIN usuarios us ON nc.id_usuario = us.id
             WHERE nc.estado_dian IN ('aceptada', 'enviada')
             AND IFNULL(nc.fecha_envio_dian, nc.fecha_creacion) BETWEEN :i3 AND :f3 " . str_replace(":tc", ":tc2", $filtroCliente) . $filtroNCUsuario . ")";

        $queryDS = "(SELECT 'Doc. Soporte' as tipo, numero_ds as numero, IFNULL(pr.nombre, 'Empresa General') as tercero, IFNULL(us.nombre, 'Admin') as vendedor, fecha_emision as fecha, monto_total as monto, estado_dian as estado, ds.id as id_doc
             FROM documentos_soporte ds
             LEFT JOIN proveedores pr ON ds.id_proveedor = pr.id
             LEFT JOIN usuarios us ON ds.id_usuario = us.id
             WHERE ds.estado_dian IN ('aceptada', 'enviada')
             AND ds.fecha_emision BETWEEN :i4 AND :f4 " . str_replace(":tp", ":tp1", $filtroProveedor) . $filtroDSUsuario . ")";

        $queryNA = "(SELECT 'Nota Ajuste DS' as tipo, numero_nota_ajuste as numero, IFNULL(pr.nombre, 'Sin Proveedor') as tercero, IFNULL(us.nombre, 'Admin') as vendedor, IFNULL(fecha_envio_dian, fecha_registro) as fecha, monto_total as monto, estado_dian as estado, na.id as id_doc
             FROM notas_ajuste_ds na
             LEFT JOIN proveedores pr ON na.id_proveedor = pr.id
             LEFT JOIN usuarios us ON na.id_usuario = us.id
             WHERE na.estado_dian IN ('aceptada', 'enviada')
             AND IFNULL(na.fecha_envio_dian, na.fecha_registro) BETWEEN :i5 AND :f5 " . str_replace(":tp", ":tp2", $filtroProveedor) . $filtroNAUsuario . ")";

        $uniones = [];
        if ($categoria == "todos" || $categoria == "facturas") {
            $uniones[] = $queryVentas;
        }

        if ($categoria == "todos" || $categoria == "nc") {
            $uniones[] = $queryNC;
        }

        if (!($categoria == "todos" && $tercero != "todos")) {
            if ($categoria == "todos" || $categoria == "ds") {
                $uniones[] = $queryDS;
            }

            if ($categoria == "todos" || $categoria == "na") {
                $uniones[] = $queryNA;
            }
        }

        $sql = implode(" UNION ALL ", $uniones) . " ORDER BY fecha DESC";
        $stmt = $db->prepare($sql);

        if ($categoria == "todos" || $categoria == "facturas") {
            $stmt->bindParam(":i1", $inicio, PDO::PARAM_STR);
            $stmt->bindParam(":f1", $fin, PDO::PARAM_STR);
            $stmt->bindParam(":i2", $inicio, PDO::PARAM_STR);
            $stmt->bindParam(":f2", $fin, PDO::PARAM_STR);
            if ($filtroCliente != "")
                $stmt->bindParam(":tc1", $tercero, PDO::PARAM_INT);
            if ($filtroVendedor != "")
                $stmt->bindParam(":uidv", $idUsuario, PDO::PARAM_INT);
        }

        if ($categoria == "todos" || $categoria == "nc") {
            $stmt->bindParam(":i3", $inicio, PDO::PARAM_STR);
            $stmt->bindParam(":f3", $fin, PDO::PARAM_STR);
            if ($filtroCliente != "")
                $stmt->bindParam(":tc2", $tercero, PDO::PARAM_INT);
            if ($filtroNCUsuario != "")
                $stmt->bindParam(":uidu1", $idUsuario, PDO::PARAM_INT);
        }

        if (!($categoria == "todos" && $tercero != "todos")) {
            if ($categoria == "todos" || $categoria == "ds") {
                $stmt->bindParam(":i4", $inicio, PDO::PARAM_STR);
                $stmt->bindParam(":f4", $fin, PDO::PARAM_STR);
                if ($filtroProveedor != "")
                    $stmt->bindParam(":tp1", $tercero, PDO::PARAM_INT);
                if ($filtroDSUsuario != "")
                    $stmt->bindParam(":uidu2", $idUsuario, PDO::PARAM_INT);
            }

            if ($categoria == "todos" || $categoria == "na") {
                $stmt->bindParam(":i5", $inicio, PDO::PARAM_STR);
                $stmt->bindParam(":f5", $fin, PDO::PARAM_STR);
                if ($filtroProveedor != "")
                    $stmt->bindParam(":tp2", $tercero, PDO::PARAM_INT);
                if ($filtroNAUsuario != "")
                    $stmt->bindParam(":uidu3", $idUsuario, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }
    /*=============================================
    MOSTRAR DOCUMENTOS SOPORTE SERVER-SIDE
    =============================================*/
    static public function mdlMostrarDocumentosSoporteServerSide($where, $order, $limit)
    {
        $sql = "SELECT ds.*, p.nombre as nombre_proveedor,
                (SELECT 1 FROM notas_ajuste_ds WHERE id_ds_original = ds.id LIMIT 1) as tiene_nota,
                (SELECT COUNT(*) FROM documentos_soporte WHERE id < ds.id AND (numero_ds IS NULL OR numero_ds = '')) as rank_borrador
                FROM documentos_soporte ds
                LEFT JOIN proveedores p ON ds.id_proveedor = p.id
                $where $order $limit";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    OBTENER TOTAL DOCUMENTOS SOPORTE
    =============================================*/
    static public function mdlGetTotalDocumentosSoporte($where = "")
    {
        $sql = "SELECT COUNT(*) as total 
                FROM documentos_soporte ds
                LEFT JOIN proveedores p ON ds.id_proveedor = p.id
                $where";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res ? $res["total"] : 0;
    }

    /*=============================================
    MOSTRAR NOTAS DE AJUSTE DS SERVER-SIDE
    =============================================*/
    static public function mdlMostrarNotasAjusteDSServerSide($where, $order, $limit)
    {
        $sql = "SELECT na.*, p.nombre as nombre_proveedor, p.correo as correo_proveedor,
                (SELECT COUNT(*) FROM notas_ajuste_ds WHERE id < na.id AND (numero_nota_ajuste IS NULL OR numero_nota_ajuste = '')) as rank_borrador
                FROM notas_ajuste_ds na
                LEFT JOIN proveedores p ON na.id_proveedor = p.id
                $where $order $limit";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    OBTENER TOTAL NOTAS DE AJUSTE DS
    =============================================*/
    static public function mdlGetTotalNotasAjusteDS($where = "")
    {
        $sql = "SELECT COUNT(*) as total 
                FROM notas_ajuste_ds na
                LEFT JOIN proveedores p ON na.id_proveedor = p.id
                $where";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res ? $res["total"] : 0;
    }

}

}
