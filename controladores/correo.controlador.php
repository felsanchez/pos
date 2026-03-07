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

        try {
            // Configuración del servidor
            $mail->SMTPDebug = 0;                      // Habilitar salida de depuración detallada (0 = off)
            $mail->isSMTP();                           // Enviar usando SMTP
            $mail->Host = 'smtp.gmail.com';      // Configurar el servidor SMTP para enviar a través de Gmail
            $mail->SMTPAuth = true;                  // Habilitar autenticación SMTP
            $mail->Username = 'kontrolpos01@gmail.com'; // Nombre de usuario SMTP
            $mail->Password = 'jnjs tvux pfwd aghm';    // Contraseña SMTP (App Password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar encriptación TLS implícita
            $mail->Port = 587;                   // Puerto TCP

            // Destinatarios
            $mail->setFrom('kontrolpos01@gmail.com', 'Sistema POS');
            $mail->addAddress($destinatario);     // Añadir un destinatario

            // Adjuntos
            if ($adjunto != null && file_exists($adjunto)) {
                $mail->addAttachment($adjunto);
            }

            // Contenido
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
