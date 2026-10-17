<?php
date_default_timezone_set('America/Bogota');

require_once __DIR__ . "/../modelos/conexion.php";

class ControladorGoogle {

    /*=============================================
    OBTENER CREDENCIALES GLOBALES
    =============================================*/
    public static function ctrObtenerCredenciales() {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT google_client_id, google_client_secret FROM configuracion WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        $clientId = !empty($res["google_client_id"]) ? trim($res["google_client_id"]) : "";
        $clientSecret = !empty($res["google_client_secret"]) ? trim($res["google_client_secret"]) : "";

        return [
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        ];
    }

    /*=============================================
    OBTENER REDIRECT URI DINÁMICA
    =============================================*/
    public static function ctrObtenerRedirectUri() {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443 
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Determinar la ruta base de la instalación (eliminando subcarpetas internas)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $baseDir = preg_replace('#/(ajax|vistas|controladores|modelos)(/.*)?$#i', '', $scriptDir);
        if ($baseDir === '/' || $baseDir === '\\') {
            $baseDir = '';
        }

        return $protocol . $host . $baseDir . "/google-callback.php";
    }

    /*=============================================
    OBTENER URL DE AUTORIZACIÓN OAUTH 2.0
    =============================================*/
    public static function ctrObtenerUrlAutorizacion($idUsuario) {
        $credenciales = self::ctrObtenerCredenciales();
        $redirectUri = self::ctrObtenerRedirectUri();

        $scopes = [
            "https://www.googleapis.com/auth/calendar.events",
            "https://www.googleapis.com/auth/tasks",
            "https://www.googleapis.com/auth/userinfo.email"
        ];

        $params = [
            "client_id" => $credenciales["client_id"],
            "redirect_uri" => $redirectUri,
            "response_type" => "code",
            "scope" => implode(" ", $scopes),
            "access_type" => "offline",
            "prompt" => "consent",
            "state" => (string)$idUsuario
        ];

        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
    }

    /*=============================================
    PROCESAR CALLBACK Y GUARDAR TOKENS
    =============================================*/
    public static function ctrProcesarCallback($code, $idUsuario) {
        $credenciales = self::ctrObtenerCredenciales();
        $redirectUri = self::ctrObtenerRedirectUri();

        $postData = [
            "code" => $code,
            "client_id" => $credenciales["client_id"],
            "client_secret" => $credenciales["client_secret"],
            "redirect_uri" => $redirectUri,
            "grant_type" => "authorization_code"
        ];

        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resRaw = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($resRaw, true);

        if (empty($res["access_token"])) {
            return ["status" => "error", "message" => "No se pudo obtener el token de acceso de Google: " . ($res["error_description"] ?? $resRaw)];
        }

        $accessToken = $res["access_token"];
        $refreshToken = $res["refresh_token"] ?? null;
        $expiresIn = intval($res["expires_in"] ?? 3600);
        $expiresAt = date("Y-m-d H:i:s", time() + $expiresIn - 60);

        // Obtener email del usuario conectado
        $emailCuenta = null;
        $chInfo = curl_init("https://www.googleapis.com/oauth2/v2/userinfo");
        curl_setopt($chInfo, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chInfo, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        curl_setopt($chInfo, CURLOPT_SSL_VERIFYPEER, false);
        $resInfoRaw = curl_exec($chInfo);
        curl_close($chInfo);

        $resInfo = json_decode($resInfoRaw, true);
        if (!empty($resInfo["email"])) {
            $emailCuenta = $resInfo["email"];
        }

        // Guardar en BD
        $db = Conexion::conectar();

        if ($refreshToken) {
            $stmt = $db->prepare("UPDATE usuarios SET google_access_token = :at, google_refresh_token = :rt, google_token_expires = :exp, google_account_email = :email WHERE id = :id");
            $stmt->execute([
                ":at" => $accessToken,
                ":rt" => $refreshToken,
                ":exp" => $expiresAt,
                ":email" => $emailCuenta,
                ":id" => $idUsuario
            ]);
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET google_access_token = :at, google_token_expires = :exp, google_account_email = :email WHERE id = :id");
            $stmt->execute([
                ":at" => $accessToken,
                ":exp" => $expiresAt,
                ":email" => $emailCuenta,
                ":id" => $idUsuario
            ]);
        }

        // Importar automáticamente las tareas y eventos de la nueva cuenta conectada
        try {
            self::ctrImportarNovedadesGoogle($idUsuario);
        } catch (Throwable $e) {
            // Continuar si hay algún fallo secundario en la importación inicial
        }

        return ["status" => "ok", "email" => $emailCuenta];
    }

    /*=============================================
    OBTENER TOKEN DE ACCESO VÁLIDO (REFRESCO AUTOMÁTICO)
    =============================================*/
    public static function ctrObtenerAccessTokenValido($idUsuario) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT google_access_token, google_refresh_token, google_token_expires, google_account_email FROM usuarios WHERE id = :id");
        $stmt->execute([":id" => $idUsuario]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($u) || empty($u["google_access_token"])) {
            return null;
        }

        $now = date("Y-m-d H:i:s");

        // Si el token aún es válido, retornar directamente
        if (!empty($u["google_token_expires"]) && $u["google_token_expires"] > $now) {
            return $u["google_access_token"];
        }

        // Si expiró y tenemos refresh_token, refrescarlo
        if (!empty($u["google_refresh_token"])) {
            $credenciales = self::ctrObtenerCredenciales();

            $postData = [
                "client_id" => $credenciales["client_id"],
                "client_secret" => $credenciales["client_secret"],
                "refresh_token" => $u["google_refresh_token"],
                "grant_type" => "refresh_token"
            ];

            $ch = curl_init("https://oauth2.googleapis.com/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $resRaw = curl_exec($ch);
            curl_close($ch);

            $res = json_decode($resRaw, true);

            if (!empty($res["access_token"])) {
                $newAccessToken = $res["access_token"];
                $expiresIn = intval($res["expires_in"] ?? 3600);
                $newExpiresAt = date("Y-m-d H:i:s", time() + $expiresIn - 60);

                $stmtUp = $db->prepare("UPDATE usuarios SET google_access_token = :at, google_token_expires = :exp WHERE id = :id");
                $stmtUp->execute([
                    ":at" => $newAccessToken,
                    ":exp" => $newExpiresAt,
                    ":id" => $idUsuario
                ]);

                return $newAccessToken;
            }
        }

        return null;
    }

    /*=============================================
    DESCONECTAR GOOGLE CALENDAR DEL USUARIO
    =============================================*/
    public static function ctrDesconectarGoogle($idUsuario) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE usuarios SET google_access_token = NULL, google_refresh_token = NULL, google_token_expires = NULL, google_account_email = NULL WHERE id = :id");
        $res = $stmt->execute([":id" => $idUsuario]);

        // Vaciar por completo todas las tareas y eventos del calendario al desconectar la cuenta
        $stmtDel = $db->prepare("DELETE FROM actividades");
        $stmtDel->execute();

        return $res;
    }

    /*=============================================
    SINCRONIZAR ACTIVIDAD (CREAR, EDITAR, ELIMINAR) CON GOOGLE
    =============================================*/
    public static function ctrSincronizarActividad($datosActividad, $accion = "crear") {
        $idUsuario = $_SESSION["id"] ?? ($datosActividad["id_user"] ?? null);
        $accessToken = self::ctrObtenerAccessTokenValido($idUsuario);

        if (!$accessToken && !empty($datosActividad["id_user"])) {
            $idUsuario = $datosActividad["id_user"];
            $accessToken = self::ctrObtenerAccessTokenValido($idUsuario);
        }

        if (!$accessToken) {
            return ["status" => "skipped", "reason" => "no_conectado"];
        }

        $categoria = strtolower($datosActividad["categoria_google"] ?? "");
        if (empty($categoria)) {
            $tipo = strtolower($datosActividad["tipo"] ?? "");
            $categoria = ($tipo === "tarea") ? "tarea" : "evento";
        }

        $googleEventId = $datosActividad["google_event_id"] ?? null;

        if ($categoria === "tarea") {
            return self::ctrSincronizarTareaGoogle($accessToken, $datosActividad, $accion, $googleEventId);
        } else {
            return self::ctrSincronizarEventoGoogle($accessToken, $datosActividad, $accion, $googleEventId);
        }
    }

    /*=============================================
    EXTRAER LINK DE GOOGLE MEET
    =============================================*/
    private static function extraerGoogleMeetLink($res) {
        if (!empty($res["hangoutLink"]) && (strpos($res["hangoutLink"], "meet.google.com") !== false || strpos($res["hangoutLink"], "hangouts") !== false)) {
            return $res["hangoutLink"];
        }
        if (!empty($res["conferenceData"]["entryPoints"]) && is_array($res["conferenceData"]["entryPoints"])) {
            foreach ($res["conferenceData"]["entryPoints"] as $ep) {
                $uri = $ep["uri"] ?? '';
                $type = $ep["entryPointType"] ?? '';
                if ($type === 'video' || strpos($uri, 'meet.google.com') !== false || strpos($uri, 'hangouts') !== false) {
                    return $uri;
                }
            }
        }
        return null;
    }

    /*=============================================
    SINCRONIZAR EVENTO DE GOOGLE CALENDAR
    =============================================*/
    private static function ctrSincronizarEventoGoogle($accessToken, $datos, $accion, $googleEventId) {
        $urlBase = "https://www.googleapis.com/calendar/v3/calendars/primary/events";

        if ($accion === "eliminar") {
            if (!$googleEventId) return ["status" => "ok"];

            $url = $urlBase . "/" . urlencode($googleEventId);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
            return ["status" => "ok"];
        }

        // Formatear fechas
        $fechaInicio = $datos["fecha"] ?? date("Y-m-d H:i:s");
        $titulo = !empty($datos["descripcion"]) ? $datos["descripcion"] : "Cita POS";
        $notas = !empty($datos["observacion"]) ? $datos["observacion"] : "";

        if (!empty($datos["archivo"])) {
            if (strpos($datos["archivo"], "http://") === 0 || strpos($datos["archivo"], "https://") === 0) {
                $fileUrl = $datos["archivo"];
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                if (strpos($requestUri, '/pos') !== false || $host == 'localhost') {
                    $fileUrl = $protocol . $host . "/pos/" . ltrim($datos["archivo"], '/');
                } else {
                    $fileUrl = $protocol . $host . "/" . ltrim($datos["archivo"], '/');
                }
            }
            if (strpos($notas, $fileUrl) === false && strpos($notas, $datos["archivo"]) === false) {
                $notas .= ($notas !== "" ? "\n\n" : "") . "📎 Archivo adjunto: " . $fileUrl;
            }
        }
        $notas = self::limpiarNotasDuplicadas($notas);

        $isAllDay = !empty($datos["todo_dia"]) || (strlen($fechaInicio) <= 10) || (substr($fechaInicio, 11, 8) === "00:00:00" && (empty($datos["fecha_fin"]) || substr($datos["fecha_fin"], 11, 8) === "23:59:59" || substr($datos["fecha_fin"], 11, 8) === "00:00:00"));

        // Formatear estado y título para Google Calendar
        $estadoLocal = strtolower($datos["estado"] ?? "");
        $tituloClean = trim(preg_replace('/^(?:✔\s*|\[COMPLETAD[AO]\]\s*|❌\s*|\[CANCELAD[AO]\]\s*)/iu', '', $titulo));

        $colorId = null;
        if ($estadoLocal === "completada" || $estadoLocal === "finalizada") {
            $tituloFinal = "✔ " . $tituloClean;
            $colorId = "8";
        } else if ($estadoLocal === "cancelada" || $estadoLocal === "cancelado") {
            $tituloFinal = "❌ " . $tituloClean;
            $colorId = "11";
        } else {
            $tituloFinal = $tituloClean;
            $colorId = null;
        }

        if ($isAllDay) {
            $startDateOnly = substr($fechaInicio, 0, 10);
            $endDateOnly = !empty($datos["fecha_fin"]) ? substr($datos["fecha_fin"], 0, 10) : date("Y-m-d", strtotime($startDateOnly . " +1 day"));
            if ($endDateOnly === $startDateOnly) {
                $endDateOnly = date("Y-m-d", strtotime($startDateOnly . " +1 day"));
            }

            $payload = [
                "summary" => $tituloFinal,
                "description" => $notas,
                "start" => ["date" => $startDateOnly, "dateTime" => null],
                "end" => ["date" => $endDateOnly, "dateTime" => null],
                "colorId" => $colorId
            ];
        } else {
            $tz = date_default_timezone_get() ?: "America/Bogota";
            $startISO = date("Y-m-d\TH:i:sP", strtotime($fechaInicio));
            $endISO = !empty($datos["fecha_fin"]) ? date("Y-m-d\TH:i:sP", strtotime($datos["fecha_fin"])) : date("Y-m-d\TH:i:sP", strtotime($fechaInicio) + 3600);

            $payload = [
                "summary" => $tituloFinal,
                "description" => $notas,
                "start" => ["dateTime" => $startISO, "timeZone" => $tz, "date" => null],
                "end" => ["dateTime" => $endISO, "timeZone" => $tz, "date" => null],
                "colorId" => $colorId
            ];
        }

        if (!empty($datos["ubicacion"])) {
            $payload["location"] = $datos["ubicacion"];
        }

        // Invitados
        if (!empty($datos["invitados_email"])) {
            $emails = array_filter(array_map('trim', explode(',', $datos["invitados_email"])));
            if (!empty($emails)) {
                $attendees = [];
                foreach ($emails as $em) {
                    if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                        $attendees[] = ["email" => $em];
                    }
                }
                if (!empty($attendees)) {
                    $payload["attendees"] = $attendees;
                }
            }
        }

        // Solicitud de Google Meet (NO colocar "conferenceData": null si no se requiere Meet)
        if (!empty($datos["generar_meet"]) && $datos["generar_meet"] == 1) {
            $payload["conferenceData"] = [
                "createRequest" => [
                    "requestId" => "pos_" . uniqid(),
                    "conferenceSolutionKey" => ["type" => "hangoutsMeet"]
                ]
            ];
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ];

        if ($accion !== "crear" && $googleEventId) {
            $url = $urlBase . "/" . urlencode($googleEventId) . "?conferenceDataVersion=1&sendUpdates=all";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $resRaw = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $res = json_decode($resRaw, true);
            if ($httpCode >= 200 && $httpCode < 300 && !empty($res["id"])) {
                $meetLink = self::extraerGoogleMeetLink($res);
                return [
                    "status" => "ok",
                    "google_event_id" => $res["id"],
                    "google_meet_link" => $meetLink
                ];
            } else if ($httpCode == 404) {
                // Si el evento no se encontró en Calendar (404), pudo ser una Tarea de Tasks convertida
                $chT = curl_init("https://www.googleapis.com/tasks/v1/lists/@default/tasks/" . urlencode($googleEventId));
                curl_setopt($chT, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($chT, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chT, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
                curl_setopt($chT, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($chT);
                curl_close($chT);
            } else {
                // En cualquier otro error en la edición, NO duplicar creando por POST
                return ["status" => "error", "raw" => $resRaw];
            }
        }

        // Crear nuevo evento en Calendar
        $url = $urlBase . "?conferenceDataVersion=1&sendUpdates=all";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $resRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($resRaw, true);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($res["id"])) {
            $meetLink = self::extraerGoogleMeetLink($res);
            return [
                "status" => "ok",
                "google_event_id" => $res["id"],
                "google_meet_link" => $meetLink
            ];
        }

        return ["status" => "error", "raw" => $resRaw];
    }

    /*=============================================
    SINCRONIZAR TAREA EN GOOGLE TASKS
    =============================================*/
    private static function ctrSincronizarTareaGoogle($accessToken, $datos, $accion, $googleEventId) {
        $urlBase = "https://www.googleapis.com/tasks/v1/lists/@default/tasks";

        if ($accion === "eliminar") {
            if (!$googleEventId) return ["status" => "ok"];

            $url = $urlBase . "/" . urlencode($googleEventId);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);

            return ["status" => "ok"];
        }

        $titulo = !empty($datos["descripcion"]) ? $datos["descripcion"] : "Tarea POS";
        $notas = !empty($datos["observacion"]) ? $datos["observacion"] : "";

        if (!empty($datos["archivo"])) {
            if (strpos($datos["archivo"], "http://") === 0 || strpos($datos["archivo"], "https://") === 0) {
                $fileUrl = $datos["archivo"];
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                if (strpos($requestUri, '/pos') !== false || $host == 'localhost') {
                    $fileUrl = $protocol . $host . "/pos/" . ltrim($datos["archivo"], '/');
                } else {
                    $fileUrl = $protocol . $host . "/" . ltrim($datos["archivo"], '/');
                }
            }
            if (strpos($notas, $fileUrl) === false && strpos($notas, $datos["archivo"]) === false) {
                $notas .= ($notas !== "" ? "\n\n" : "") . "📎 Archivo adjunto: " . $fileUrl;
            }
        }
        // Limpiar cualquier línea de "Hora programada" previa en $notas para actualizarla con la hora actual.
        $notas = preg_replace('/(?:⏰\s*)?Hora programada:\s*\d{1,2}:\d{2}\s*(?:AM|PM)?\s*/iu', '', $notas);
        $notas = self::limpiarNotasDuplicadas($notas);

        $fechaInicio = $datos["fecha"] ?? null;
        $isAllDay = !empty($datos["todo_dia"]) || (empty($fechaInicio) || strlen($fechaInicio) <= 10 || substr($fechaInicio, 11, 8) === "00:00:00");

        $fechaDue = !empty($fechaInicio) ? substr($fechaInicio, 0, 10) . "T00:00:00.000Z" : null;

        if (!$isAllDay && !empty($fechaInicio) && strlen($fechaInicio) > 10 && substr($fechaInicio, 11, 8) !== "00:00:00") {
            $horaF = date("h:i A", strtotime($fechaInicio));
            $notas = "⏰ Hora programada: " . $horaF . ($notas !== "" ? "\n\n" . $notas : "");
        }

        // Limpiar prefijo previo de reloj si existía
        $tituloClean = trim(preg_replace('/^\[(?:TAREA|EVENTO|REUNIÓN)\]\s*/iu', '', $titulo));
        $tituloClean = trim(preg_replace('/^⏰\s*\d{1,2}:\d{2}\s*(?:AM|PM)?\s*-\s*/iu', '', $tituloClean));
        $tituloClean = trim(preg_replace('/^⏰\s*/iu', '', $tituloClean));

        $payload = [
            "title" => $tituloClean,
            "notes" => $notas
        ];

        $estadoLocal = strtolower($datos["estado"] ?? "");
        if ($estadoLocal === "completada" || $estadoLocal === "finalizada") {
            $payload["status"] = "completed";
        } else if (!empty($datos["estado"])) {
            $payload["status"] = "needsAction";
            $payload["completed"] = null;
        }

        if ($fechaDue) {
            $payload["due"] = $fechaDue;
        }

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ];

        if ($accion !== "crear" && $googleEventId) {
            $url = $urlBase . "/" . urlencode($googleEventId);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $resRaw = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $res = json_decode($resRaw, true);

            if ($httpCode >= 200 && $httpCode < 300 && !empty($res["id"])) {
                return [
                    "status" => "ok",
                    "google_event_id" => $res["id"],
                    "google_meet_link" => null
                ];
            } else {
                // Si el PATCH falló porque el ID pertenecía a Google Calendar, eliminar evento de Calendar
                $chC = curl_init("https://www.googleapis.com/calendar/v3/calendars/primary/events/" . urlencode($googleEventId));
                curl_setopt($chC, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($chC, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chC, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
                curl_setopt($chC, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($chC);
                curl_close($chC);
            }
        }

        // Crear nueva tarea en Google Tasks
        $ch = curl_init($urlBase);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $resRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($resRaw, true);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($res["id"])) {
            return [
                "status" => "ok",
                "google_event_id" => $res["id"],
                "google_meet_link" => null
            ];
        }

        return ["status" => "error", "raw" => $resRaw];
    }

    /*=============================================
    IMPORTAR NOVEDADES (EVENTOS Y TAREAS) DESDE GOOGLE ➔ POS
    =============================================*/
    public static function ctrImportarNovedadesGoogle($idUsuario) {
        $accessToken = self::ctrObtenerAccessTokenValido($idUsuario);
        if (!$accessToken) {
            return ["status" => "error", "message" => "Su cuenta no está conectada o el token de acceso venció. Por favor haga clic en 'Desconectar' y vuelva a pulsar 'Conectar con Google Calendar' en este servidor."];
        }

        $db = Conexion::conectar();

        // Obtener bodega por defecto
        $idBodega = isset($_SESSION["id_bodega"]) ? intval($_SESSION["id_bodega"]) : 1;

        $totalImportados = 0;
        $totalActualizados = 0;
        $totalEliminados = 0;

        $googleActiveEvents = [];
        $googleActiveTasks = [];

        // Map para guardar las horas exactas de inicio de tareas obtenidas de los eventos espejo de Google Calendar
        $googleTaskTimesMap = [];

        // 1. IMPORTAR EVENTOS DE GOOGLE CALENDAR (incluyendo eliminados)
        $timeMin = date("c", strtotime("-1 year"));
        $timeMax = date("c", strtotime("+1 year"));
        $urlEvents = "https://www.googleapis.com/calendar/v3/calendars/primary/events?timeMin=" . urlencode($timeMin) . "&timeMax=" . urlencode($timeMax) . "&singleEvents=true&showDeleted=true&orderBy=startTime&maxResults=250&supportsAttachments=true";

        $ch = curl_init($urlEvents);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resEventsRaw = curl_exec($ch);
        curl_close($ch);

        $resEvents = json_decode($resEventsRaw, true);

        if (!empty($resEvents["error"])) {
            $errDesc = is_array($resEvents["error"]) ? ($resEvents["error"]["message"] ?? json_encode($resEvents["error"])) : $resEvents["error"];
            return ["status" => "error", "message" => "Google API devolvió un error: " . $errDesc . ". Por favor desconecte y reconecte su cuenta de Google."];
        }

        if (!empty($resEvents["items"]) && is_array($resEvents["items"])) {
            foreach ($resEvents["items"] as $item) {
                $gId = $item["id"] ?? null;
                if (!$gId) continue;

                // Manejo de eventos cancelados o eliminados en Google Calendar
                $isCancelled = (isset($item["status"]) && $item["status"] === "cancelled") || !empty($item["deleted"]);
                if ($isCancelled) {
                    $stmtDel = $db->prepare("DELETE FROM actividades WHERE (google_event_id = :gid OR google_event_id = :gid_b64) AND id_user = :uid");
                    $stmtDel->execute([
                        ":gid" => $gId,
                        ":gid_b64" => base64_encode($gId),
                        ":uid" => $idUsuario
                    ]);
                    if ($stmtDel->rowCount() > 0) {
                        $totalEliminados += $stmtDel->rowCount();
                    }
                    continue;
                }

                $googleActiveEvents[] = $gId;

                $summary = !empty($item["summary"]) ? trim($item["summary"]) : "Evento de Google";
                $notes = !empty($item["description"]) ? trim($item["description"]) : "";
                $location = !empty($item["location"]) ? trim($item["location"]) : null;
                $meetLink = self::extraerGoogleMeetLink($item);

                // Procesar adjuntos nativos de Google Calendar
                $archivoUrl = null;
                if (!empty($item["attachments"]) && is_array($item["attachments"])) {
                    foreach ($item["attachments"] as $att) {
                        $attUrl = $att["fileUrl"] ?? "";
                        $attTitle = $att["title"] ?? "";
                        if (!empty($attUrl)) {
                            if (!$archivoUrl) {
                                $archivoUrl = $attUrl;
                            }
                            if (strpos($notes, $attUrl) === false) {
                                $labelText = !empty($attTitle) ? ($attTitle . ": " . $attUrl) : $attUrl;
                                $notes .= ($notes !== "" ? "\n\n" : "") . "📎 Archivo adjunto: " . $labelText;
                            }
                        }
                    }
                }

                // Extraer primera URL de notas si no hay adjunto explicito (soporta http, https, www, drive.google.com, docs.google.com)
                if (!$archivoUrl && !empty($notes)) {
                    if (preg_match('/(?:https?:\/\/|www\.|drive\.google\.com|docs\.google\.com)[^\s<"\']+/i', $notes, $mUrl)) {
                        $cleanUrl = rtrim($mUrl[0], '.,;:)"\'');
                        if (strpos($cleanUrl, 'tasks.google.com/task/') === false) {
                            if (!preg_match('/^https?:\/\//i', $cleanUrl)) {
                                $cleanUrl = 'https://' . $cleanUrl;
                            }
                            $archivoUrl = $cleanUrl;
                        }
                    }
                }

                // Omitir reflejos automáticos de Google Tasks creados por Google Calendar y capturar su hora de inicio exacta
                if (!empty($notes) && preg_match('/tasks\.google\.com\/task\/([a-zA-Z0-9_\-]+)/i', $notes, $m)) {
                    $rawTaskId = $m[1];
                    if (!empty($item["start"]["dateTime"])) {
                        $fDateTask = date("Y-m-d H:i:s", strtotime($item["start"]["dateTime"]));
                        $googleTaskTimesMap[$rawTaskId] = $fDateTask;
                        $googleTaskTimesMap[base64_encode($rawTaskId)] = $fDateTask;
                        $googleTaskTimesMap[rtrim(base64_encode($rawTaskId), '=')] = $fDateTask;
                    }
                    continue;
                }
                if (isset($item["eventType"]) && in_array($item["eventType"], ["focusTime", "outOfOffice", "workingLocation"])) {
                    continue;
                }

                // Parsear fecha inicio y fin para eventos de Google Calendar
                $isAllDay = 0;
                if (!empty($item["start"]["date"])) {
                    $isAllDay = 1;
                    $dateStartOnly = $item["start"]["date"];
                    $fechaInicio = $dateStartOnly . " 00:00:00";
                    if (!empty($item["end"]["date"])) {
                        $dateEndExclusive = $item["end"]["date"];
                        if ($dateEndExclusive > $dateStartOnly) {
                            $dateEndInclusive = date("Y-m-d", strtotime($dateEndExclusive . " -1 day"));
                            $fechaFin = $dateEndInclusive . " 23:59:59";
                        } else {
                            $fechaFin = $dateStartOnly . " 23:59:59";
                        }
                    } else {
                        $fechaFin = $dateStartOnly . " 23:59:59";
                    }
                } else if (!empty($item["start"]["dateTime"])) {
                    $fechaInicio = date("Y-m-d H:i:s", strtotime($item["start"]["dateTime"]));
                    $endRaw = $item["end"]["dateTime"] ?? null;
                    $fechaFin = $endRaw ? date("Y-m-d H:i:s", strtotime($endRaw)) : date("Y-m-d H:i:s", strtotime($fechaInicio . " +1 hour"));
                    if (substr($fechaInicio, 11, 8) === "00:00:00" && (substr($fechaFin, 11, 8) === "23:59:59" || substr($fechaFin, 11, 8) === "00:00:00")) {
                        $isAllDay = 1;
                    }
                } else {
                    continue;
                }

                // Verificar si ya existe por google_event_id
                $stmtCheck = $db->prepare("SELECT id, estado FROM actividades WHERE google_event_id = :gid LIMIT 1");
                $stmtCheck->execute([":gid" => $gId]);
                $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if (!$existente && !empty($summary)) {
                    $summaryClean = trim(preg_replace('/^\[(?:TAREA|EVENTO|REUNIÓN)\]\s*/iu', '', $summary));
                    $summaryClean = trim(preg_replace('/^⏰\s*\d{1,2}:\d{2}\s*(?:AM|PM)?\s*-\s*/iu', '', $summaryClean));
                    $summaryClean = trim(preg_replace('/^⏰\s*/iu', '', $summaryClean));

                    if (!empty($summaryClean) && strtolower($summaryClean) !== "evento de google" && strtolower($summaryClean) !== "tarea de google") {
                        $fDate = substr($fechaInicio, 0, 10);
                        $stmtCheckAlt = $db->prepare("SELECT id, estado FROM actividades WHERE id_user = :uid AND (google_event_id IS NULL OR google_event_id = '') AND (descripcion = :desc OR descripcion = :desc_clean OR REPLACE(descripcion, '[TAREA] ', '') = :desc_clean) AND DATE(fecha) = :fdate LIMIT 1");
                        $stmtCheckAlt->execute([
                            ":uid" => $idUsuario,
                            ":desc" => $summary,
                            ":desc_clean" => $summaryClean,
                            ":fdate" => $fDate
                        ]);
                        $existente = $stmtCheckAlt->fetch(PDO::FETCH_ASSOC);
                    }
                }

                // Extraer correos de invitados (attendees) de Google Calendar
                $invitadosEmailsStr = null;
                if (!empty($item["attendees"]) && is_array($item["attendees"])) {
                    $emailsList = [];
                    foreach ($item["attendees"] as $att) {
                        $em = trim($att["email"] ?? "");
                        if (!empty($em) && filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            $emailsList[] = $em;
                        }
                    }
                    if (!empty($emailsList)) {
                        $invitadosEmailsStr = implode(", ", array_unique($emailsList));
                    }
                }

                $notes = self::limpiarNotasDuplicadas($notes);

                $isCompletedEvent = (!empty($item["colorId"]) && $item["colorId"] == "8") || preg_match('/^(?:✔|\[COMPLETAD[AO]\])/iu', $summary);

                if ($existente) {
                    if ($isCompletedEvent) {
                        $estadoCalculado = "completada";
                    } else if (!empty($existente["estado"]) && $existente["estado"] === "completada") {
                        $estadoCalculado = "programada";
                    } else if (!empty($existente["estado"])) {
                        $estadoCalculado = $existente["estado"];
                    } else {
                        $estadoCalculado = "programada";
                    }

                    $stmtUp = $db->prepare("UPDATE actividades SET descripcion = :desc, fecha = :fecha, fecha_fin = :fecha_fin, todo_dia = :todo_dia, estado = :estado, observacion = :obs, ubicacion = :ubic, invitados_email = :invitados, google_meet_link = :meet, google_event_id = :gid, categoria_google = 'evento', archivo = COALESCE(NULLIF(:archivo, ''), archivo) WHERE id = :id");
                    $stmtUp->execute([
                        ":desc" => $summary,
                        ":fecha" => $fechaInicio,
                        ":fecha_fin" => $fechaFin,
                        ":todo_dia" => $isAllDay,
                        ":estado" => $estadoCalculado,
                        ":obs" => $notes,
                        ":ubic" => $location,
                        ":invitados" => $invitadosEmailsStr,
                        ":meet" => $meetLink,
                        ":gid" => $gId,
                        ":archivo" => $archivoUrl,
                        ":id" => $existente["id"]
                    ]);
                    $totalActualizados++;
                } else {
                    $estadoCalculado = $isCompletedEvent ? "completada" : "programada";
                    $stmtIns = $db->prepare("INSERT INTO actividades (descripcion, tipo, id_user, fecha, fecha_fin, todo_dia, estado, observacion, id_bodega, categoria_google, google_event_id, google_meet_link, ubicacion, invitados_email, archivo) VALUES (:desc, 'Reunión', :uid, :fecha, :fecha_fin, :todo_dia, :estado, :obs, :bodega, 'evento', :gid, :meet, :ubic, :invitados, :archivo)");
                    $stmtIns->execute([
                        ":desc" => $summary,
                        ":uid" => $idUsuario,
                        ":fecha" => $fechaInicio,
                        ":fecha_fin" => $fechaFin,
                        ":todo_dia" => $isAllDay,
                        ":estado" => $estadoCalculado,
                        ":obs" => $notes,
                        ":bodega" => $idBodega,
                        ":gid" => $gId,
                        ":meet" => $meetLink,
                        ":ubic" => $location,
                        ":invitados" => $invitadosEmailsStr,
                        ":archivo" => $archivoUrl
                    ]);
                    $totalImportados++;
                }
            }
        }

        // 2. IMPORTAR TAREAS DE GOOGLE TASKS (incluyendo completadas, ocultas y eliminadas)
        $urlTasks = "https://www.googleapis.com/tasks/v1/lists/@default/tasks?showCompleted=true&showHidden=true&showDeleted=true&maxResults=100";

        $chT = curl_init($urlTasks);
        curl_setopt($chT, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chT, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        curl_setopt($chT, CURLOPT_SSL_VERIFYPEER, false);
        $resTasksRaw = curl_exec($chT);
        curl_close($chT);

        $resTasks = json_decode($resTasksRaw, true);

        if (!empty($resTasks["error"])) {
            $errDescT = is_array($resTasks["error"]) ? ($resTasks["error"]["message"] ?? json_encode($resTasks["error"])) : $resTasks["error"];
            return ["status" => "error", "message" => "Google Tasks API devolvió un error: " . $errDescT];
        }

        if (!empty($resTasks["items"]) && is_array($resTasks["items"])) {
            foreach ($resTasks["items"] as $tItem) {
                $gId = $tItem["id"] ?? null;
                if (!$gId) continue;

                // Manejo de tareas eliminadas o canceladas en Google Tasks
                $isTaskDeleted = !empty($tItem["deleted"]) || (isset($tItem["status"]) && $tItem["status"] === "cancelled");
                if ($isTaskDeleted) {
                    $stmtDelT = $db->prepare("DELETE FROM actividades WHERE (google_event_id = :gid OR google_event_id = :gid_b64) AND id_user = :uid");
                    $stmtDelT->execute([
                        ":gid" => $gId,
                        ":gid_b64" => base64_encode($gId),
                        ":uid" => $idUsuario
                    ]);
                    if ($stmtDelT->rowCount() > 0) {
                        $totalEliminados += $stmtDelT->rowCount();
                    }
                    continue;
                }

                $googleActiveTasks[] = $gId;

                $title = !empty($tItem["title"]) ? trim($tItem["title"]) : "Tarea de Google";
                $title = preg_replace('/^⏰\s*\d{1,2}:\d{2}\s*(?:AM|PM)?\s*-\s*/iu', '', $title);
                $title = preg_replace('/^⏰\s*/iu', '', $title);
                $notes = !empty($tItem["notes"]) ? trim($tItem["notes"]) : "";

                $archivoTaskUrl = null;
                if (!empty($notes)) {
                    if (preg_match('/(?:https?:\/\/|www\.|drive\.google\.com|docs\.google\.com)[^\s<"\']+/i', $notes, $mUrl)) {
                        $cleanUrl = rtrim($mUrl[0], '.,;:)"\'');
                        if (!preg_match('/^https?:\/\//i', $cleanUrl)) {
                            $cleanUrl = 'https://' . $cleanUrl;
                        }
                        $archivoTaskUrl = $cleanUrl;
                    }
                }
                
                $gStatus = strtolower($tItem["status"] ?? "");
                $isCompleted = ($gStatus === "completed") || !empty($tItem["completed"]);
                $status = $isCompleted ? "completada" : "pendiente";

                $dueRaw = $tItem["due"] ?? null;
                $gIdDecoded = @base64_decode($gId);
                $timeFound = $googleTaskTimesMap[$gId] ?? ($googleTaskTimesMap[$gIdDecoded] ?? null);

                if ($timeFound) {
                    // Si el evento espejo en Google Calendar especificó una hora precisa
                    $fechaDue = $timeFound;
                    $fechaFinDue = date("Y-m-d H:i:s", strtotime($fechaDue . " +1 hour"));
                    $isTaskAllDay = 0;
                } else if (!empty($notes) && preg_match('/(?:⏰\s*)?(?:Hora(?:\s*programada)?:\s*)?(\d{1,2}:\d{2}(?:\s*[AP]M)?)/i', $notes, $mHora) && strtotime(trim($mHora[1])) !== false) {
                    $parsedTimeStr = trim($mHora[1]);
                    $dateOnlyDue = !empty($dueRaw) ? substr($dueRaw, 0, 10) : date("Y-m-d");
                    $timeFormatted = date("H:i:s", strtotime($parsedTimeStr));
                    $fechaDue = $dateOnlyDue . " " . $timeFormatted;
                    $fechaFinDue = date("Y-m-d H:i:s", strtotime($fechaDue . " +1 hour"));
                    $isTaskAllDay = 0;
                } else if (!empty($dueRaw)) {
                    // Extraer fecha YYYY-MM-DD sin sufrir desplazamiento por zona horaria Z de Google Tasks
                    $dateOnlyDue = substr($dueRaw, 0, 10);
                    $fechaDue = $dateOnlyDue . " 00:00:00";
                    $fechaFinDue = $dateOnlyDue . " 23:59:59";
                    $isTaskAllDay = 1;
                } else {
                    $fechaDue = date("Y-m-d 00:00:00");
                    $fechaFinDue = date("Y-m-d 23:59:59");
                    $isTaskAllDay = 1;
                }

                // Verificar si ya existe por google_event_id
                $stmtCheckT = $db->prepare("SELECT id, fecha, fecha_fin, todo_dia, estado FROM actividades WHERE google_event_id = :gid LIMIT 1");
                $stmtCheckT->execute([":gid" => $gId]);
                $existenteT = $stmtCheckT->fetch(PDO::FETCH_ASSOC);

                if (!$existenteT && !empty($title)) {
                    $titleClean = trim(preg_replace('/^\[(?:TAREA|EVENTO|REUNIÓN)\]\s*/iu', '', $title));
                    $titleClean = trim(preg_replace('/^⏰\s*\d{1,2}:\d{2}\s*(?:AM|PM)?\s*-\s*/iu', '', $titleClean));
                    $titleClean = trim(preg_replace('/^⏰\s*/iu', '', $titleClean));

                    if (!empty($titleClean) && strtolower($titleClean) !== "tarea de google" && strtolower($titleClean) !== "evento de google") {
                        $fDateT = substr($fechaDue, 0, 10);
                        $stmtCheckAltT = $db->prepare("SELECT id, fecha, fecha_fin, todo_dia, estado FROM actividades WHERE id_user = :uid AND (google_event_id IS NULL OR google_event_id = '') AND (descripcion = :desc OR descripcion = :desc_clean OR REPLACE(descripcion, '[TAREA] ', '') = :desc_clean) AND DATE(fecha) = :fdate LIMIT 1");
                        $stmtCheckAltT->execute([
                            ":uid" => $idUsuario,
                            ":desc" => $title,
                            ":desc_clean" => $titleClean,
                            ":fdate" => $fDateT
                        ]);
                        $existenteT = $stmtCheckAltT->fetch(PDO::FETCH_ASSOC);
                    }
                }

                $notes = self::limpiarNotasDuplicadas($notes);

                if ($existenteT) {
                    if ($isCompleted) {
                        $statusFinal = "completada";
                    } else if (!empty($existenteT["estado"]) && $existenteT["estado"] === "completada") {
                        $statusFinal = "pendiente";
                    } else if (!empty($existenteT["estado"])) {
                        $statusFinal = $existenteT["estado"];
                    } else {
                        $statusFinal = "pendiente";
                    }

                    // Si no se extrajo una hora nueva de las notas de Google, pero la tarea en POS ya tenía una hora válida, preservarla
                    if ($isTaskAllDay == 1 && isset($existenteT["todo_dia"]) && $existenteT["todo_dia"] == 0 && !empty($existenteT["fecha"])) {
                        $timePartInicio = substr($existenteT["fecha"], 11, 8);
                        $timePartFin = !empty($existenteT["fecha_fin"]) ? substr($existenteT["fecha_fin"], 11, 8) : "23:59:59";
                        if ($timePartInicio !== "00:00:00") {
                            $newDateOnly = substr($fechaDue, 0, 10);
                            $fechaDue = $newDateOnly . " " . $timePartInicio;
                            $fechaFinDue = $newDateOnly . " " . $timePartFin;
                            $isTaskAllDay = 0;
                        }
                    }

                    $stmtUpT = $db->prepare("UPDATE actividades SET descripcion = :desc, tipo = 'Tarea', fecha = :fecha, fecha_fin = :fecha_fin, todo_dia = :todo_dia, observacion = :obs, estado = :est, google_event_id = :gid, categoria_google = 'tarea', archivo = COALESCE(NULLIF(:archivo, ''), archivo) WHERE id = :id");
                    $stmtUpT->execute([
                        ":desc" => $title,
                        ":fecha" => $fechaDue,
                        ":fecha_fin" => $fechaFinDue,
                        ":todo_dia" => $isTaskAllDay,
                        ":obs" => $notes,
                        ":est" => $statusFinal,
                        ":gid" => $gId,
                        ":archivo" => $archivoTaskUrl,
                        ":id" => $existenteT["id"]
                    ]);
                    $totalActualizados++;
                } else {
                    $stmtInsT = $db->prepare("INSERT INTO actividades (descripcion, tipo, id_user, fecha, fecha_fin, todo_dia, estado, observacion, id_bodega, categoria_google, google_event_id, archivo) VALUES (:desc, 'Tarea', :uid, :fecha, :fecha_fin, :todo_dia, :est, :obs, :bodega, 'tarea', :gid, :archivo)");
                    $stmtInsT->execute([
                        ":desc" => $title,
                        ":uid" => $idUsuario,
                        ":fecha" => $fechaDue,
                        ":fecha_fin" => $fechaFinDue,
                        ":todo_dia" => $isTaskAllDay,
                        ":est" => $status,
                        ":obs" => $notes,
                        ":bodega" => $idBodega,
                        ":gid" => $gId,
                        ":archivo" => $archivoTaskUrl
                    ]);
                    $totalImportados++;
                }
            }
        }

        // 3. PURGA DE RECONCILIACIÓN PARA ACTIVIDADES ELIMINADAS EN GOOGLE (Y NO DEVUELTAS O ELIMINADAS PERMANENTEMENTE)
        $fMinPurge = date("Y-m-d H:i:s", strtotime("-1 year"));
        $fMaxPurge = date("Y-m-d H:i:s", strtotime("+1 year"));

        $stmtLocal = $db->prepare("SELECT id, google_event_id, categoria_google FROM actividades WHERE id_user = :uid AND google_event_id IS NOT NULL AND google_event_id != '' AND fecha >= :fmin AND fecha <= :fmax");
        $stmtLocal->execute([
            ":uid" => $idUsuario,
            ":fmin" => $fMinPurge,
            ":fmax" => $fMaxPurge
        ]);
        $localItems = $stmtLocal->fetchAll(PDO::FETCH_ASSOC);

        foreach ($localItems as $lItem) {
            $gIdLoc = $lItem["google_event_id"];
            $catLoc = strtolower($lItem["categoria_google"] ?? "evento");

            $inActiveEvents = in_array($gIdLoc, $googleActiveEvents);
            $inActiveTasks = in_array($gIdLoc, $googleActiveTasks);

            if (!$inActiveEvents && !$inActiveTasks) {
                $stmtPurge = $db->prepare("DELETE FROM actividades WHERE id = :id");
                $stmtPurge->execute([":id" => $lItem["id"]]);
                if ($stmtPurge->rowCount() > 0) {
                    $totalEliminados += $stmtPurge->rowCount();
                }
            }
        }

        return [
            "status" => "ok",
            "importados" => $totalImportados,
            "actualizados" => $totalActualizados,
            "eliminados" => $totalEliminados,
            "total" => $totalImportados + $totalActualizados + $totalEliminados
        ];
    }

    public static function limpiarNotasDuplicadas($texto) {
        if (empty($texto)) return "";
        $lines = explode("\n", $texto);
        $cleanLines = [];
        $seenAttachments = [];
        $seenTimeNote = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                $cleanLines[] = "";
                continue;
            }

            if (preg_match('/(?:⏰\s*)?Hora programada:\s*\d{1,2}:\d{2}\s*(?:AM|PM)?/i', $trimmed)) {
                if ($seenTimeNote) {
                    continue;
                }
                $seenTimeNote = true;
            }

            if (preg_match('/(?:📎\s*Archivo adjunto:|[?]+ Archivo adjunto:)\s*(.+)$/i', $trimmed, $m)) {
                $attTarget = trim($m[1]);
                if (isset($seenAttachments[$attTarget])) {
                    continue;
                }
                $seenAttachments[$attTarget] = true;
            }
            $cleanLines[] = $line;
        }

        $result = implode("\n", $cleanLines);
        $result = preg_replace("/\n{3,}/", "\n\n", trim($result));
        return $result;
    }
}
