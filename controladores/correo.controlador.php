<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ControladorCorreo
{

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

            // Contenido
            $mail->CharSet  = 'UTF-8';                             // Codificación de caracteres
            $mail->Encoding = 'base64';                            // Encoding seguro para UTF-8
            $mail->isHTML(true);                                  // Formato de correo HTML
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;

            // Texto plano para clientes que no soportan HTML
            $mail->AltBody = strip_tags($mensaje);

            $mail->send();
            return "ok";
        } catch (Exception $e) {
            return "error: " . $mail->ErrorInfo;
        }
    }
}
