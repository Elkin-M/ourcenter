<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
$current_page = 'Reenviar activacion'; // Nombre de la página actual

/**
 * reenviar_activacion.php - Reenvía el email de activación de cuenta
 * 
 * Este script permite a un usuario solicitar el reenvío del email
 * de activación en caso de que no lo haya recibido o haya expirado.
 */

// Iniciar sesión para manejar mensajes flash


// Incluir archivos de configuración y conexión a la base de datos
require_once __DIR__ . '/../server/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/security.php';

// Definir variables para almacenar mensajes de error y valores del formulario
$errors = [];
$formData = [
    'email' => ''
];

// Variable para controlar si mostrar el formulario o el mensaje de éxito
$showForm = true;

// Si se ha enviado el formulario (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanitizar el email del formulario
    $formData['email'] = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    
    // Validar el email (obligatorio, formato válido)
    if (empty($formData['email'])) {
        $errors['email'] = 'El email es obligatorio';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El formato del email no es válido';
    }
    
    // Si no hay errores, procesar la solicitud
    if (empty($errors)) {
        try {
            // Buscar el usuario por email
            $stmt = $pdo->prepare("SELECT id, nombre, email, estado FROM usuarios WHERE email = ?");
            $stmt->execute([$formData['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verificar si la cuenta ya está activada
                if ($user['estado'] === 'activo') {
                    $_SESSION['message'] = [
                        'type' => 'info',
                        'text' => 'Tu cuenta ya está activada. Puedes iniciar sesión ahora.'
                    ];
                } else {
                    // Generar nuevo token de activación
                    $token = bin2hex(random_bytes(32));
                    $expira = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    // Actualizar el token en la base de datos
                    $activationToken = generateToken();
                    $expirationDate = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $stmt = $pdo->prepare("INSERT INTO tokens_activacion (usuario_id, token, fecha_expiracion) VALUES (?, ?, ?)");
                    $stmt->execute([$user['nombre'], $activationToken, $expirationDate]);
                    
                    // Enviar email con el nuevo token de activación
                    sendActivationEmail($user['email'], $user['nombre'], $token);
                    
                    // Registrar la actividad
                    $stmt = $pdo->prepare("
                        INSERT INTO logs_actividad (
                            usuario_id, accion, tabla_afectada, registro_afectado_id, 
                            detalles, ip_usuario, user_agent
                        ) VALUES (
                            ?, 'reenvio_activacion', 'usuarios', ?, ?, ?, ?
                        )
                    ");
                    $stmt->execute([
                        $user['id'],
                        $user['id'],
                        'Reenvío de email de activación',
                        $_SERVER['REMOTE_ADDR'],
                        $_SERVER['HTTP_USER_AGENT']
                    ]);
                    
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => 'Hemos enviado un nuevo enlace de activación a tu correo electrónico. Por favor revisa tu bandeja de entrada.'
                    ];
                }
            } else {
                // No mostrar si el email existe o no por seguridad
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Si tu email está registrado en nuestro sistema, recibirás un enlace de activación. Por favor revisa tu bandeja de entrada.'
                ];
            }
            
            // Ya no mostrar el formulario
            $showForm = false;
            
        } catch (PDOException $e) {
            // Registrar el error (en un archivo de log real)
            error_log('Error en reenviar_activacion.php: ' . $e->getMessage());
            
            // Mostrar un mensaje de error general
            $generalError = 'Ha ocurrido un error al procesar tu solicitud. Por favor intenta nuevamente.';
        }
    }
}

// Incluir la plantilla de cabecera
include __DIR__ . '/../templates/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Reenviar Email de Activación</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($generalError)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $generalError; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>" role="alert">
                            <?php echo $_SESSION['message']['text']; ?>
                            <?php unset($_SESSION['message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($showForm): ?>
                        <p class="mb-4">
                            Si no has recibido el email de activación o el enlace ha expirado, 
                            puedes solicitar un nuevo email de activación. Ingresa tu dirección de correo electrónico a continuación.
                        </p>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Enviar Enlace de Activación</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <p>Revisa tu correo electrónico (incluyendo la carpeta de spam) para encontrar el enlace de activación.</p>
                            <div class="d-grid gap-2 mt-4">
                                <a href="login.php" class="btn btn-primary">Ir a Iniciar Sesión</a>
                                <a href="index.php" class="btn btn-outline-primary">Volver a la Página Principal</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">¿Necesitas ayuda? <a href="contacto.php">Contáctanos</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir la plantilla de pie de página
include __DIR__ . '/../templates/footer.php';
?>
<style>
    .main-content {
     margin-left: 0px;
    }
</style>
