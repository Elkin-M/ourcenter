<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
$current_page = 'Activar cuenta'; // Nombre de la página actual
/**
 * activar_cuenta.php - Procesa la activación de cuentas de usuario
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../server/config.php';


$message = '';
$messageType = '';
$activationSuccess = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);

    // Validar formato del token
    if (!preg_match('/^[a-f0-9]{32,64}$/', $token)) {
        $message = 'El formato del token de activación no es válido.';
        $messageType = 'danger';
    } else {
        try {
            // Buscar token válido en la tabla de activación
            $stmt = $pdo->prepare("
    SELECT ta.usuario_id, u.nombre, u.email, u.estado
    FROM tokens_activacion ta
    JOIN usuarios u ON ta.usuario_id = u.id
    WHERE ta.token = ?
");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['estado'] === 'activo') {
                    $message = 'Tu cuenta ya está activada. Puedes iniciar sesión.';
                    $messageType = 'info';
                } else {
                    // Activar la cuenta del usuario
                    $stmt = $pdo->prepare("
                        UPDATE usuarios 
                        SET estado = 'activo'
                        WHERE id = ?
                    ");
                    $stmt->execute([$user['usuario_id']]);

                    // Eliminar el token de activación
                    $deleteStmt = $pdo->prepare("DELETE FROM tokens_activacion WHERE token = ?");
                    $deleteStmt->execute([$token]);

                    // Verificar que la cuenta haya sido activada
                    $check = $pdo->prepare("SELECT estado FROM usuarios WHERE id = ?");
                    $check->execute([$user['usuario_id']]);
                    $estadoActual = $check->fetchColumn();

                    if ($estadoActual === 'activo') {
                        $message = '¡Tu cuenta ha sido activada exitosamente! Ahora puedes iniciar sesión y acceder a nuestros cursos.';
                        $messageType = 'success';
                        $activationSuccess = true;

                        // Registrar en logs
                        $log = $pdo->prepare("
                            INSERT INTO logs_actividad (
                                usuario_id, accion, tabla_afectada, registro_afectado_id, 
                                detalles, ip_usuario, user_agent
                            ) VALUES (
                                ?, 'activacion', 'usuarios', ?, ?, ?, ?
                            )
                        ");
                        $log->execute([
                            $user['usuario_id'],
                            $user['usuario_id'],
                            'Activación de cuenta de usuario',
                            $_SERVER['REMOTE_ADDR'],
                            $_SERVER['HTTP_USER_AGENT']
                        ]);
                    } else {
                        $message = 'No se pudo activar la cuenta. Por favor, intenta nuevamente.';
                        $messageType = 'danger';
                    }
                }
            } else {
                $message = 'El enlace de activación es inválido o ha expirado. Por favor, solicita un nuevo enlace.';
                $messageType = 'warning';
            }
        } catch (PDOException $e) {
            error_log('Error en activar_cuenta.php: ' . $e->getMessage());
            $message = 'Ha ocurrido un error al procesar tu solicitud. Por favor, inténtalo nuevamente.';
            $messageType = 'danger';
        }
    }
} else {
    $message = 'No se proporcionó un token de activación válido.';
    $messageType = 'danger';
}

include __DIR__ . '/../templates/header2.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-<?php echo $messageType === 'success' ? 'success' : 'primary'; ?> text-white">
                    <h4 class="mb-0">Activación de Cuenta</h4>
                </div>
                <div class="card-body">
                    <?php if ($messageType === 'success'): ?>
                        <div class="text-center mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                    <?php elseif ($messageType === 'danger'): ?>
                        <div class="text-center mb-4">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                    <?php elseif ($messageType === 'warning'): ?>
                        <div class="text-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                        </div>
                    <?php elseif ($messageType === 'info'): ?>
                        <div class="text-center mb-4">
                            <i class="bi bi-info-circle-fill text-info" style="font-size: 4rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                    <?php if ($activationSuccess): ?>
                        <div class="mb-4">
                            <h5>¿Qué puedes hacer ahora?</h5>
                            <ul class="list-group mt-3">
                                <li class="list-group-item">
                                    <i class="bi bi-search me-2"></i> Explorar los cursos disponibles
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-journal-text me-2"></i> Completar tu perfil con información adicional
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-mortarboard me-2"></i> Inscribirte en los cursos de tu interés
                                </li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                            <a href="cursos.php" class="btn btn-outline-primary">Ver Cursos Disponibles</a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <?php if ($messageType === 'warning'): ?>
                                <a href="reenviar_activacion.php" class="btn btn-warning">Solicitar Nuevo Enlace de Activación</a>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-outline-primary">Volver a la Página Principal</a>
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

<?php include __DIR__ . '/../templates/footer.php'; ?>
<style>
    .main-content {
     margin-left: 0px;
    }
</style>