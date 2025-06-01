<?php
$config = require __DIR__ . '/../server/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$base_url = $config['base_url'];
$app_name = $config['app_name'];
$mail_config = $config['mail'];

/**
 * Genera un token aleatorio
 * 
 * @param int $length Longitud del token (por defecto 32)
 * @return string Token generado
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Verifica el token CSRF
 * 
 * @param string $token Token enviado por el formulario
 * @return bool True si el token es válido, false en caso contrario
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Genera un nuevo token CSRF y lo guarda en la sesión
 * 
 * @return string Token CSRF generado
 */
function generateCSRFToken() {
    $token = generateToken();
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Limpia datos de entrada para prevenir XSS
 * 
 * @param string $data Datos a limpiar
 * @return string Datos limpios
 */
function sanitizeInput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Envía un email de activación de cuenta usando PHPMailer
 * 
 * @param string $email Email del destinatario
 * @param string $nombre Nombre del destinatario
 * @param string $token Token de activación
 * @param int $expiration_hours Horas hasta la expiración del token
 * @return bool True si se envió correctamente, false en caso contrario
 */
function sendActivationEmail($email, $nombre, $token, $expiration_hours = 24) {
    global $base_url, $app_name, $config;
    
    // URL de activación
    $activationUrl = $base_url . 'mail/activar_cuenta.php?token=' . $token;
    
    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = $config['mail']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['mail']['username'];
        $mail->Password   = $config['mail']['password'];
        $mail->SMTPSecure = $config['mail']['encryption'];
        $mail->Port       = $config['mail']['port'];
        $mail->CharSet    = 'UTF-8';
        
        // Remitentes y destinatarios
        $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
        $mail->addAddress($email, $nombre);
        
        // Incrustar imágenes embebidas
        $mail->addEmbeddedImage(__DIR__ . '/../images/logo.webp', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/../images/icons/facebook.png', 'facebookimg');
        $mail->addEmbeddedImage(__DIR__ . '/../images/icons/instagram.png', 'instagramimg');
        $mail->addEmbeddedImage(__DIR__ . '/../images/icons/linkedin.png', 'linkedinimg');
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Activación de cuenta - ' . $app_name;
        
        // Cuerpo del correo en formato HTML
        $mail->Body = '
<!DOCTYPE html>
<html>
<head>
    <title>Activación de cuenta en ' . $app_name . '</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Arial, sans-serif; background-color: #f5f5f5; color: #333333;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <!-- Encabezado con logo -->
        <div style="background-color: #122c8e; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
            <img src="cid:logoimg" alt="' . $app_name . '" style="max-height: 80px; max-width: 80%;">
        </div>
        
        <!-- Franja de color secundario -->
        <div style="height: 5px; background: linear-gradient(to right, #122c8e, #00a0e9);"></div>
        
        <!-- Contenido principal -->
        <div style="padding: 30px 25px; line-height: 1.5;">
            <h2 style="color: #122c8e; margin-top: 0;">¡Bienvenido a ' . $app_name . '!</h2>
            
            <p style="font-size: 16px;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
            
            <p style="font-size: 16px;">Gracias por registrarte en nuestra plataforma. Estamos emocionados de tenerte como parte de nuestra comunidad educativa.</p>
            
            <p style="font-size: 16px;">Para comenzar a disfrutar de todos nuestros cursos y recursos, por favor activa tu cuenta haciendo clic en el botón a continuación:</p>
            
            <!-- Botón de activación -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $activationUrl . '" style="display: inline-block; padding: 12px 30px; background-color: #00a0e9; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; transition: background-color 0.3s ease;">ACTIVAR MI CUENTA</a>
            </div>
            
            <p style="font-size: 14px; color: #666666;">Si el botón no funciona, puedes copiar y pegar la siguiente URL en tu navegador:</p>
            
            <div style="background-color: #f0f8ff; padding: 15px; border-radius: 5px; border-left: 4px solid #122c8e; word-break: break-all; margin: 15px 0;">
                <a href="' . $activationUrl . '" style="color: #122c8e; font-size: 14px;">' . $activationUrl . '</a>
            </div>
            
            <p style="font-size: 14px; color: #666666;">Este enlace expirará en <strong>' . $expiration_hours . ' horas</strong>.</p>
            
            <hr style="border: none; border-top: 1px solid #eeeeee; margin: 25px 0;">
            
            <p style="font-size: 14px; color: #666666;">Si no has solicitado este registro, puedes ignorar este correo.</p>
        </div>
        
        <!-- Información adicional -->
        <div style="background-color: #f0f8ff; padding: 20px 25px; border-top: 1px solid #eeeeee;">
            <p style="font-size: 14px; color: #555555; margin-top: 0;">¿Necesitas ayuda? <a href="' . $base_url . '/contacto.php" style="color: #122c8e; text-decoration: none;">Contáctanos</a></p>
        </div>
        
        <!-- Pie de página -->
        <div style="background-color: #122c8e; color: #ffffff; padding: 20px; text-align: center; border-radius: 0 0 5px 5px;">
            <p style="margin: 0 0 10px 0; font-size: 14px;">
                &copy; ' . date('Y') . ' ' . $app_name . '. Todos los derechos reservados.
            </p>
            <div style="margin-top: 15px;">
                <!-- Iconos de redes sociales - reemplazar URLs con las de la empresa -->
                <a href="https://www.facebook.com/CORPOURCENTER" style="display: inline-block; margin: 0 8px;"><img src="cid:facebookimg" alt="Facebook" width="24" height="24"></a>
                <a href="https://www.instagram.com/corporacionour/" style="display: inline-block; margin: 0 8px;"><img src="cid:instagramimg" alt="Instagram" width="24" height="24"></a>
                <a href="https://linkedin.com/company/tuempresa" style="display: inline-block; margin: 0 8px;"><img src="cid:linkedinimg" alt="LinkedIn" width="24" height="24"></a>
            </div>
        </div>
    </div>
    <!-- Mensaje informativo -->
    <div style="max-width: 600px; margin: 10px auto; text-align: center; color: #999999; font-size: 12px;">
        <p>Este es un correo automático, por favor no responda a este mensaje.</p>
    </div>
</body>
</html>';
        
        // Versión texto plano alternativa para clientes de correo que no soportan HTML
        $mail->AltBody = "Hola $nombre,\n\n".
                      "Gracias por registrarte en $app_name. Para activar tu cuenta, visita el siguiente enlace:\n\n".
                      "$activationUrl\n\n".
                      "Este enlace expirará en $expiration_hours horas.\n\n".
                      "Si no has solicitado este registro, puedes ignorar este correo.\n\n".
                      "Saludos,\n$app_name";
        
        // Enviar el correo
        $sent = $mail->send();
        
        // Registrar la acción
        error_log("Email de activación enviado a $email con token $token");
        
        return true;
        
    } catch (Exception $e) {
        // Registrar el error
        error_log("Error al enviar email de activación a $email: {$mail->ErrorInfo}");
        return false;
    }
}
