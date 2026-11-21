<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ControladorCorreo
{

    /*=============================================
    OBTENER RUTA DEL LOGO PARA CORREOS
    (Prioridad: Datos para la Factura -> Kontrol Pos)
    =============================================*/
    static public function ctrObtenerRutaLogo()
    {
        $raizPos = dirname(__DIR__);
        $rutaLogo = "";

        // 1. Prioridad: Logo guardado en "Datos para la Factura" (factus_config -> logo_empresa)
        try {
            require_once __DIR__ . "/../modelos/conexion.php";
            require_once __DIR__ . "/../modelos/factus.modelo.php";
            $configFactus = ModeloFactus::mdlObtenerConfiguracion();
            if (!empty($configFactus['logo_empresa'])) {
                $limpia = ltrim(str_replace('\\', '/', $configFactus['logo_empresa']), '/');
                $candidata = $raizPos . '/' . $limpia;
                if (file_exists($candidata)) {
                    $rutaLogo = $candidata;
                } elseif (file_exists($configFactus['logo_empresa'])) {
                    $rutaLogo = $configFactus['logo_empresa'];
                }
            }
        } catch (Throwable $e) {}

        // 2. Si no hay logo en factus_config, probar con configuración general
        if (empty($rutaLogo)) {
            try {
                require_once __DIR__ . "/../modelos/configuracion.modelo.php";
                $configGeneral = ModeloConfiguracion::mdlObtenerConfiguracion("configuracion");
                if (!empty($configGeneral['logo'])) {
                    $limpia = ltrim(str_replace('\\', '/', $configGeneral['logo']), '/');
                    $candidata = $raizPos . '/' . $limpia;
                    if (file_exists($candidata)) {
                        $rutaLogo = $candidata;
                    } elseif (file_exists($configGeneral['logo'])) {
                        $rutaLogo = $configGeneral['logo'];
                    }
                }
            } catch (Throwable $e) {}
        }

        // 3. Si no está guardado ninguno, usar el logo de Kontrol Pos por defecto
        if (empty($rutaLogo)) {
            $defaultLogo = $raizPos . '/vistas/img/plantilla/logo-ppal.png';
            if (file_exists($defaultLogo)) {
                $rutaLogo = $defaultLogo;
            }
        }

        return $rutaLogo;
    }

    /*=============================================
    ARMAR PLANTILLA HTML DEL CORREO CON LOGO
    =============================================*/
    static public function ctrArmarPlantillaCorreo($contenidoHtml, $nombreEmpresa = "")
    {
        // Si el contenido ya tiene estructura HTML completa o ya incluye el contenedor/logo, no duplicar
        if (stripos($contenidoHtml, '<html') !== false || stripos($contenidoHtml, 'id="contenedor-correo"') !== false || stripos($contenidoHtml, 'cid:logo_correo') !== false) {
            return $contenidoHtml;
        }

        // Limpiar pequeños footers repetidos si ya venían en el texto
        $contenidoLimpio = preg_replace('/<br\s*\/?>\s*<hr\s*\/?>\s*<small>.*?<\/small>/is', '', $contenidoHtml);

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($nombreEmpresa) . '</title>
</head>
<body style="margin:0; padding:20px 10px; background-color:#f4f6f9; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width:600px; margin:0 auto;">
        <tr>
            <td>
                <div id="contenedor-correo" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:1px solid #e1e6eb;">
                    <!-- Cabecera con Logo -->
                    <div style="background-color:#ffffff; padding:25px 20px 20px 20px; text-align:center; border-bottom:3px solid #3c8dbc;">
                        <img src="cid:logo_correo" alt="' . htmlspecialchars($nombreEmpresa) . '" style="max-height:75px; max-width:240px; height:auto; width:auto; object-fit:contain; display:inline-block;" />
                    </div>
                    <!-- Cuerpo del Mensaje -->
                    <div style="padding:30px 25px; color:#333333; font-size:14px; line-height:1.6;">
                        ' . $contenidoLimpio . '
                    </div>
                    <!-- Pie de Página Institucional -->
                    <div style="background-color:#f9fafb; padding:15px 20px; text-align:center; font-size:11px; color:#888888; border-top:1px solid #eef0f2;">
                        Este es un correo automático generado por el sistema de facturación. Por favor no responda a este mensaje.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>';

        return $html;
    }

    /*=============================================
    ENVIAR CORREO
    =============================================*/
    static public function ctrEnviarCorreo($destinatario, $asunto, $mensaje, $adjunto = null)
    {

        // Cargar las clases de PHPMailer manualmente
        require_once __DIR__ . "/../extensiones/phpmailer/src/Exception.php";
        require_once __DIR__ . "/../extensiones/phpmailer/src/PHPMailer.php";
        require_once __DIR__ . "/../extensiones/phpmailer/src/SMTP.php";

        $mail = new PHPMailer(true);

        // Cargar configuración de SMTP de la base de datos (factus_config)
        $smtpHost = 'smtp.gmail.com';
        $smtpUsuario = 'kontrolpos01@gmail.com';
        $smtpPassword = 'jnjs tvux pfwd aghm';
        $smtpPort = 587;
        $smtpSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $fromName = "Comprobante de Facturacion";

        try {
            require_once __DIR__ . "/../modelos/conexion.php";
            require_once __DIR__ . "/../modelos/factus.modelo.php";
            $configFactus = ModeloFactus::mdlObtenerConfiguracion();
            
            if ($configFactus) {
                // Configuración dinámica de SMTP
                if (!empty($configFactus['smtp_correo'])) {
                    $smtpUsuario = $configFactus['smtp_correo'];
                }
                if (!empty($configFactus['smtp_password'])) {
                    $smtpPassword = $configFactus['smtp_password'];
                }
                if (!empty($configFactus['smtp_host'])) {
                    $smtpHost = $configFactus['smtp_host'];
                }
                if (!empty($configFactus['smtp_port'])) {
                    $smtpPort = intval($configFactus['smtp_port']);
                }
                if (!empty($configFactus['smtp_secure'])) {
                    $secureConfig = strtolower($configFactus['smtp_secure']);
                    if ($secureConfig == 'ssl') {
                        $smtpSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else if ($secureConfig == 'none') {
                        $smtpSecure = '';
                    } else {
                        $smtpSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    }
                }
                
                // Nombre de la empresa
                if (!empty($configFactus['nombre_empresa'])) {
                    $fromName = "Comprobante de Facturacion - " . trim($configFactus['nombre_empresa']);
                }
            }
        } catch (Throwable $eConfig) {
            // En caso de error, mantenemos los valores por defecto
        }

        try {
            // Configuración del servidor
            $mail->SMTPDebug = 0;                      // Habilitar salida de depuración detallada (0 = off)
            $mail->isSMTP();                           // Enviar usando SMTP
            $mail->Host = $smtpHost;                   // Servidor SMTP dinámico
            $mail->SMTPAuth = true;                    // Habilitar autenticación SMTP
            $mail->Username = $smtpUsuario;            // Nombre de usuario SMTP
            $mail->Password = $smtpPassword;            // Contraseña SMTP (App Password)
            $mail->SMTPSecure = $smtpSecure;           // Habilitar encriptación
            $mail->Port = $smtpPort;                   // Puerto TCP

            // Destinatarios
            $mail->setFrom($smtpUsuario, $fromName);
            $mail->addAddress($destinatario);     // Añadir un destinatario

            // Adjuntos
            if ($adjunto != null && file_exists($adjunto)) {
                $mail->addAttachment($adjunto);
            }

            // Incrustar Logo (Datos para la Factura -> Kontrol Pos)
            $rutaLogo = self::ctrObtenerRutaLogo();
            if (!empty($rutaLogo) && file_exists($rutaLogo)) {
                $ext = strtolower(pathinfo($rutaLogo, PATHINFO_EXTENSION));
                $mimeType = ($ext == 'jpg' || $ext == 'jpeg') ? 'image/jpeg' : 'image/png';
                $mail->addEmbeddedImage($rutaLogo, 'logo_correo', basename($rutaLogo), 'base64', $mimeType);
            }

            // Armar cuerpo HTML con el diseño y logo
            $nombreEmpresaHeader = !empty($configFactus['nombre_empresa']) ? trim($configFactus['nombre_empresa']) : $fromName;
            $cuerpoFinal = self::ctrArmarPlantillaCorreo($mensaje, $nombreEmpresaHeader);

            // Contenido
            $mail->CharSet  = 'UTF-8';                             // Codificación de caracteres
            $mail->Encoding = 'base64';                            // Encoding seguro para UTF-8
            $mail->isHTML(true);                                  // Formato de correo HTML
            $mail->Subject = $asunto;
            $mail->Body = $cuerpoFinal;

            // Texto plano para clientes que no soportan HTML
            $mail->AltBody = strip_tags($mensaje);

            $mail->send();
            return "ok";
        } catch (Exception $e) {
            return "error: " . $mail->ErrorInfo;
        }
    }
}
