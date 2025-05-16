<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

/**
 * registro_confirmacion.php - Página de confirmación tras el registro exitoso
 * 
 * Esta página muestra un mensaje de confirmación al usuario después de completar
 * el registro y le proporciona instrucciones sobre los siguientes pasos.
 */

// Iniciar sesión para acceder a los mensajes flash

// Si no hay mensaje de éxito en la sesión, redirigir al formulario de registro
if (!isset($_SESSION['success_message'])) {
    header('Location: registro.php');
    exit;
}

// Capturar el mensaje de éxito y eliminarlo de la sesión
$successMessage = $_SESSION['success_message'];
unset($_SESSION['success_message']);

// Incluir la plantilla de cabecera
include __DIR__ . '/../templates/header.php';?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">¡Registro Exitoso!</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <div class="alert alert-success" role="alert">
                        <?php echo $successMessage; ?>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Próximos pasos:</h5>
                        <ol class="list-group list-group-numbered mt-3">
                            <li class="list-group-item">Revisa tu correo electrónico (incluyendo la carpeta de spam) para encontrar el enlace de activación.</li>
                            <li class="list-group-item">Haz clic en el enlace para activar tu cuenta.</li>
                            <li class="list-group-item">Una vez activada, podrás iniciar sesión y explorar los cursos disponibles.</li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Si no recibes el correo de activación en los próximos 15 minutos, 
                        <a href="reenviar_activacion.php" class="alert-link">haz clic aquí para solicitar un nuevo correo</a>.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary">Volver a la página principal</a>
                    </div>
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