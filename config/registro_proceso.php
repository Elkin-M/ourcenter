<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


/**
 * registro.php - Formulario de registro de usuario con activación por email
 * 
 * Este archivo maneja tanto la visualización del formulario como el procesamiento
 * del registro, incluyendo el envío de email de activación.
 */

// Incluir archivos de configuración y conexión a la base de datos
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../server/config.php';

// Definir variables para almacenar errores y datos del formulario
$errores = [];
$form_data = [
    'nombre' => '',
    'apellido' => '',
    'tipo_documento' => '',
    'documento_identidad' => '',
    'fecha_nacimiento' => '',
    'email' => '',
    'telefono' => '',
    'password' => '',
    'confirm_password' => '',
    'accept_terms' => ''
];

// Cargar errores y datos del formulario desde la sesión si existen
if (isset($_SESSION['form_errors'])) {
    $errores = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

if (isset($_SESSION['form_data'])) {
    $form_data = array_merge($form_data, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
}

// Si se envía el formulario (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $form_data = [
        'nombre' => trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING)),
        'apellido' => trim(filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING)),
        'tipo_documento' => trim(filter_input(INPUT_POST, 'tipo_documento', FILTER_SANITIZE_STRING)),
        'documento_identidad' => trim(filter_input(INPUT_POST, 'documento_identidad', FILTER_SANITIZE_STRING)),
        'fecha_nacimiento' => trim(filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_STRING)),
        'email' => trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)),
        'telefono' => trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING)),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'accept_terms' => isset($_POST['accept_terms'])
    ];

    // Validar los datos del formulario
    
    // Validar nombre
    if (empty($form_data['nombre'])) {
        $errores[] = "El nombre es requerido.";
    } elseif (strlen($form_data['nombre']) < 2) {
        $errores[] = "El nombre debe tener al menos 2 caracteres.";
    }

    // Validar apellido
    if (empty($form_data['apellido'])) {
        $errores[] = "El apellido es requerido.";
    } elseif (strlen($form_data['apellido']) < 2) {
        $errores[] = "El apellido debe tener al menos 2 caracteres.";
    }

    // Validar tipo de documento
    if (empty($form_data['tipo_documento'])) {
        $errores[] = "El tipo de documento es requerido.";
    }

    // Validar documento de identidad
    if (empty($form_data['documento_identidad'])) {
        $errores[] = "El número de documento es requerido.";
    } elseif (!preg_match('/^[0-9]{8,12}$/', $form_data['documento_identidad'])) {
        $errores[] = "El número de documento debe contener entre 8 y 12 dígitos.";
    }

    // Validar fecha de nacimiento
    if (empty($form_data['fecha_nacimiento'])) {
        $errores[] = "La fecha de nacimiento es requerida.";
    } else {
        // Verificar que la persona sea mayor de 18 años
        $fecha_nacimiento = new DateTime($form_data['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento)->y;
        if ($edad < 18) {
            $errores[] = "Debes ser mayor de 18 años para registrarte.";
        }
    }

    // Validar email
    if (empty($form_data['email'])) {
        $errores[] = "El correo electrónico es requerido.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    } else {
        // Verificar si el correo ya está registrado
        try {
            $sql_check_email = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
            $stmt_check_email = $pdo->prepare($sql_check_email);
            $stmt_check_email->bindParam(':email', $form_data['email']);
            $stmt_check_email->execute();
            $email_count = $stmt_check_email->fetchColumn();

            if ($email_count > 0) {
                $errores[] = "El correo electrónico ya está registrado. Por favor, utiliza otro.";
            }
        } catch (PDOException $e) {
            error_log('Error verificando email: ' . $e->getMessage());
            $errores[] = "Error al verificar el correo electrónico.";
        }
    }

    // Validar teléfono
    if (empty($form_data['telefono'])) {
        $errores[] = "El teléfono es requerido.";
    } elseif (!preg_match('/^[0-9]{8,15}$/', $form_data['telefono'])) {
        $errores[] = "El teléfono debe contener entre 8 y 15 dígitos.";
    }

    // Validar contraseña
    if (empty($form_data['password'])) {
        $errores[] = "La contraseña es requerida.";
    } elseif (strlen($form_data['password']) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $form_data['password'])) {
        $errores[] = "La contraseña debe contener al menos una mayúscula, una minúscula y un número.";
    }

    // Validar confirmación de contraseña
    if (empty($form_data['confirm_password'])) {
        $errores[] = "La confirmación de la contraseña es requerida.";
    } elseif ($form_data['password'] !== $form_data['confirm_password']) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Validar términos y condiciones
    if (!$form_data['accept_terms']) {
        $errores[] = "Debes aceptar los términos y condiciones.";
    }

    // Si hay errores, guardar datos y mostrar errores
    if (!empty($errores)) {
        $_SESSION['form_errors'] = $errores;
        $_SESSION['form_data'] = $form_data;
        // No redirigir, simplemente mostrar el formulario con errores
    } else {
        // Si no hay errores, procesar el registro
        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // Preparar datos para inserción
            $nombre = $form_data['nombre'];
            $apellido = $form_data['apellido'];
            $tipo_documento = $form_data['tipo_documento'];
            $documento_identidad = $form_data['documento_identidad'];
            $fecha_nacimiento = $form_data['fecha_nacimiento'];
            $email = $form_data['email'];
            $telefono = $form_data['telefono'];
            $password = password_hash($form_data['password'], PASSWORD_BCRYPT);
            $rol_id = 2; // Rol de usuario regular (estudiante)
            $estado = 'inactivo'; // La cuenta inicia inactiva hasta confirmar email

            // Insertar usuario en la base de datos
            $sql = "INSERT INTO usuarios (nombre, apellido, tipo_documento, documento_identidad, fecha_nacimiento, email, telefono, password, rol_id, estado) 
                    VALUES (:nombre, :apellido, :tipo_documento, :documento_identidad, :fecha_nacimiento, :email, :telefono, :password, :rol_id, :estado)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':tipo_documento' => $tipo_documento,
                ':documento_identidad' => $documento_identidad,
                ':fecha_nacimiento' => $fecha_nacimiento,
                ':email' => $email,
                ':telefono' => $telefono,
                ':password' => $password,
                ':rol_id' => $rol_id,
                ':estado' => $estado
            ]);

            // Obtener el ID del usuario recién insertado
            $usuario_id = $pdo->lastInsertId();

            // Generar token de activación
            $token = bin2hex(random_bytes(32));
            $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Insertar token de activación
            $sql_token = "INSERT INTO tokens_activacion (usuario_id, token, fecha_expiracion) VALUES (:usuario_id, :token, :fecha_expiracion)";
            $stmt_token = $pdo->prepare($sql_token);
            $stmt_token->execute([
                ':usuario_id' => $usuario_id,
                ':token' => $token,
                ':fecha_expiracion' => $fecha_expiracion
            ]);

            // Confirmar transacción
            $pdo->commit();

            // Enviar email de activación
            $emailSent = sendActivationEmail($email, $nombre, $token);

            if ($emailSent) {
    $_SESSION['success_message'] = "Tu cuenta ha sido creada exitosamente. Hemos enviado un enlace de activación a tu correo electrónico. Por favor, revisa tu bandeja de entrada (y la carpeta de spam) y haz clic en el enlace para activar tu cuenta.";

    // Registrar en logs
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() AND table_name = 'logs_actividad'
    ");
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $log = $pdo->prepare("
            INSERT INTO logs_actividad (
                usuario_id, accion, tabla_afectada, registro_afectado_id, 
                detalles, ip_usuario, user_agent
            ) VALUES (
                ?, 'registro', 'usuarios', ?, ?, ?, ?
            )
        ");
        $log->execute([
            $usuario_id,
            $usuario_id,
            'Registro de nuevo usuario con envío de email de activación',
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    }

    header('Location: ../mail/registro_confirmacion.php');
    exit;
} else {
    $errores[] = "Tu cuenta fue creada pero hubo un problema al enviar el correo de activación. Puedes solicitar un nuevo correo en la página de reenvío de activación.";
}

            
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            error_log('Error en registro: ' . $e->getMessage());
            $errores[] = "Hubo un error al registrar el usuario. Por favor, intente nuevamente.";
        }
    }
}

// Función para enviar email de activación
// function sendActivationEmail($email, $nombre, $token) {
//     try {
//         // Cargar configuración
//         $config = require __DIR__ . '/../server/config.php';
        
//         // Verificar si PHPMailer está disponible
//         if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
//             error_log('PHPMailer no está instalado. Usando función mail() de PHP.');
//             return sendActivationEmailBasic($email, $nombre, $token, $config);
//         }
    
//         $mail = new PHPMailer(true);
        
//         // Configuración del servidor SMTP
//         $mail->isSMTP();
//         $mail->Host = $config['mail']['host'];
//         $mail->SMTPAuth = true;
//         $mail->Username = $config['mail']['username'];
//         $mail->Password = $config['mail']['password'];
//         $mail->SMTPSecure = ($config['mail']['encryption'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
//         $mail->Port = $config['mail']['port'];
        
//         // Codificación
//         $mail->CharSet = 'UTF-8';
        
//         // Remitente y destinatario
//         $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
//         $mail->addAddress($email, $nombre);
//         $mail->addReplyTo($config['mail']['from_email'], $config['mail']['from_name']);
        
//         // Contenido del email
//         $mail->isHTML(true);
//         $mail->Subject = 'Activa tu cuenta - ' . $config['app_name'];
        
//         // Generar enlace de activación
//         $activationLink = $config['base_url'] . "public/activar_cuenta.php?token=" . $token;
        
//         // Template del email
//         $mail->Body = generateActivationEmailTemplate($nombre, $activationLink, $config['app_name']);
        
//         // Versión de texto plano
//         $mail->AltBody = "Hola $nombre,\n\n" .
//             "Gracias por registrarte en " . $config['app_name'] . ". " .
//             "Para activar tu cuenta, copia y pega el siguiente enlace en tu navegador:\n\n" .
//             $activationLink . "\n\n" .
//             "Este enlace expirará en 24 horas.\n\n" .
//             "Si no solicitaste esta cuenta, puedes ignorar este email.\n\n" .
//             "Saludos,\nEl equipo de " . $config['app_name'];
        
//         $mail->send();
//         return true;
        
//     } catch (Exception $e) {
//         error_log('Error enviando email con PHPMailer: ' . $e->getMessage());
        
//         // Intentar con la función mail() básica como fallback
//         try {
//             $config = require __DIR__ . '/../server/config.php';
//             return sendActivationEmailBasic($email, $nombre, $token, $config);
//         } catch (Exception $fallbackError) {
//             error_log('Error enviando email con función básica: ' . $fallbackError->getMessage());
//             return false;
//         }
//     }
// }

// Función fallback para enviar email usando mail() básica de PHP
function sendActivationEmailBasic($email, $nombre, $token, $config) {
    try {
        $subject = "Activa tu cuenta - " . $config['app_name'];
        $activationLink = $config['base_url'] . "public/activar_cuenta.php?token=" . $token;
        
        // Template del email
        $message = generateActivationEmailTemplate($nombre, $activationLink, $config['app_name']);
        
        // Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $config['mail']['from_name'] . " <" . $config['mail']['from_email'] . ">" . "\r\n";
        $headers .= "Reply-To: " . $config['mail']['from_email'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return mail($email, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log('Error en sendActivationEmailBasic: ' . $e->getMessage());
        return false;
    }
}

// Función para generar el template del email de activación
function generateActivationEmailTemplate($nombre, $activationLink, $appName) {
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Activación de cuenta - $appName</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background-color: #f8f9fa; }
            .button { display: inline-block; background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
            .footer { padding: 20px; text-align: center; background-color: #e9ecef; color: #6c757d; font-size: 14px; }
            .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Bienvenido a $appName!</h1>
            </div>
            <div class='content'>
                <h2>Hola $nombre,</h2>
                <p>Gracias por registrarte en nuestra plataforma educativa. Estamos emocionados de tenerte como parte de nuestra comunidad de aprendizaje.</p>
                
                <p>Para completar tu registro y comenzar a explorar nuestros cursos, necesitas activar tu cuenta haciendo clic en el siguiente botón:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$activationLink' class='button'>Activar mi cuenta</a>
                </div>
                
                <p>O si prefieres, puedes copiar y pegar el siguiente enlace en tu navegador:</p>
                <p style='word-break: break-all; background-color: #f1f1f1; padding: 10px; border-radius: 3px;'>$activationLink</p>
                
                <div class='warning'>
                    <strong>Importante:</strong> Este enlace expirará en 24 horas por razones de seguridad. Si no activas tu cuenta dentro de este tiempo, podrás solicitar un nuevo enlace de activación.
                </div>
                
                <p>Una vez activada tu cuenta, podrás:</p>
                <ul>
                    <li>Explorar nuestro catálogo completo de cursos</li>
                    <li>Inscribirte en los cursos de tu interés</li>
                    <li>Acceder a contenido exclusivo</li>
                    <li>Seguir tu progreso de aprendizaje</li>
                </ul>
                
                <p>Si no solicitaste esta cuenta, puedes ignorar este correo de forma segura.</p>
            </div>
            <div class='footer'>
                <p><strong>El equipo de $appName</strong></p>
                <p>¿Necesitas ayuda? Contáctanos y estaremos encantados de asistirte.</p>
                <p><small>Este es un email automático, por favor no respondas a este mensaje.</small></p>
            </div>
        </div>
    </body>
    </html>";
}

// Incluir la plantilla de cabecera
include __DIR__ . '/../templates/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Registro de Usuario</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger" role="alert">
                            <h6>Por favor, corrige los siguientes errores:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errores as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($form_data['nombre']); ?>" required>
                            </div>

                            <!-- Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       value="<?php echo htmlspecialchars($form_data['apellido']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo de documento -->
                            <div class="col-md-4 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="DNI" <?php echo $form_data['tipo_documento'] === 'DNI' ? 'selected' : ''; ?>>DNI</option>
                                    <option value="cedula" <?php echo $form_data['tipo_documento'] === 'cedula' ? 'selected' : ''; ?>>Cédula</option>
                                    <option value="pasaporte" <?php echo $form_data['tipo_documento'] === 'pasaporte' ? 'selected' : ''; ?>>Pasaporte</option>
                                </select>
                            </div>

                            <!-- Documento de identidad -->
                            <div class="col-md-4 mb-3">
                                <label for="documento_identidad" class="form-label">Número de Documento *</label>
                                <input type="text" class="form-control" id="documento_identidad" name="documento_identidad" 
                                       value="<?php echo htmlspecialchars($form_data['documento_identidad']); ?>" required>
                            </div>

                            <!-- Fecha de nacimiento -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                       value="<?php echo htmlspecialchars($form_data['fecha_nacimiento']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono *</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($form_data['telefono']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Contraseña -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Mínimo 8 caracteres, debe incluir mayúscula, minúscula y número.</div>
                            </div>

                            <!-- Confirmar contraseña -->
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <!-- Términos y condiciones -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" 
                                       <?php echo $form_data['accept_terms'] ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="accept_terms">
                                    Acepto los <a href="terminos.php" target="_blank">términos y condiciones</a> 
                                    y la <a href="privacidad.php" target="_blank">política de privacidad</a> *
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Crear Cuenta</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para validación del lado del cliente -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    // Validar que las contraseñas coincidan
    function validatePasswordMatch() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    password.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);

    // Validar formato de contraseña
    password.addEventListener('input', function() {
        const value = this.value;
        const minLength = value.length >= 8;
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);
        const hasNumber = /\d/.test(value);

        if (!minLength || !hasUpper || !hasLower || !hasNumber) {
            this.setCustomValidity('La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula y número');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php
// Incluir la plantilla de pie de página
include __DIR__ . '/../templates/footer.php';
?>