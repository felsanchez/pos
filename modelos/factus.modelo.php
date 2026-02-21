<?php

require_once __DIR__ . "/conexion.php";

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
                // expires_in suele ser segundos (ej: 86400)
                $expiresIn = $data['expires_in'] ?? 3600;
                $expirationDate = date('Y-m-d H:i:s', time() + $expiresIn);

                return self::mdlActualizarTokens([
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? $refreshToken, // A veces rota, a veces no
                    'token_expiracion' => $expirationDate
                ]) === "ok";
            }
        }

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
        $config = self::mdlObtenerConfiguracion();
        $url = $config['api_url'] . '/v1/bills/' . $invoiceId;

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

        // Tarjeta Débito (49) -> Maestro
        if (strpos($nombreNorm, 'debito') !== false || strpos($nombreNorm, 'maestro') !== false)
            return "49";

        // Bonos (71)
        if (strpos($nombreNorm, 'bono') !== false)
            return "71";

        // Vales (72)
        if (strpos($nombreNorm, 'vale') !== false)
            return "72";

        // Otros (1) -> Instrumento no definido
        if (strpos($nombreNorm, 'otro') !== false)
            return "1";

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
    static public function mdlObtenerSiguienteConsecutivoFactus()
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
			SELECT numero_factura
			FROM ventas 
			WHERE numero_factura IS NOT NULL 
			AND numero_factura != ''
			AND numero_factura LIKE :prefijo
			ORDER BY id DESC
		");

        $stmt->bindParam(":prefijo", $prefijoLike, PDO::PARAM_STR);
        $stmt->execute();
        $facturas = $stmt->fetchAll();

        // Extraer el número máximo en PHP
        $ultimoLocal = 0;
        foreach ($facturas as $factura) {
            $numeroFactura = $factura["numero_factura"];

            // 1. Quitar el prefijo para dejar solo la parte numérica (o con guiones)
            // Usamos str_replace limitando a 1 reemplazo para evitar quitar partes internas
            $soloParteNumerica = substr($numeroFactura, strlen($prefijo));

            // 2. Limpiar caracteres no numéricos si quedan (ej: guiones extra)
            // Aunque idealmente el número debería ser limpio.
            // Si el formato es PREFIJO-NUMERO, al quitar PREFIJO queda -NUMERO
            $soloNumeros = preg_replace('/[^0-9]/', '', $soloParteNumerica);

            if (!empty($soloNumeros)) {
                $numero = intval($soloNumeros);
                if ($numero > $ultimoLocal) {
                    $ultimoLocal = $numero;
                }
            }
        }

        // El siguiente será el mayor entre lo que dice la API y lo que tenemos en BD
        // 1. Consultar a la API el estado REAL del rango para evitar conflictos (409)
        $token = self::mdlObtenerAccessToken();
        $numeroApiReal = $numeroActualApi;

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

            if (isset($json['data']['current'])) { // Nota: API doc dice 'current' o 'current_number', verificamos 'current' segun mdlGuardarRangos
                $numeroApiReal = intval($json['data']['current']);
            } elseif (isset($json['data']['current_number'])) {
                $numeroApiReal = intval($json['data']['current_number']);
            }
        }

        // Usamos el MAYOR entre: Local (BD), Config (Cached), y API Live
        $ultimoUsado = max($ultimoLocal, $numeroActualApi, $numeroApiReal);

        // Si el ultimo usado es menor que el "desde", forzamos el "desde" - 1
        if ($ultimoUsado < $numeroDesde) {
            $ultimoUsado = $numeroDesde - 1;
        }

        return $ultimoUsado + 1;
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

}
